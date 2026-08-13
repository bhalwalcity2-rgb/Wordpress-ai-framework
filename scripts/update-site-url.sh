#!/bin/bash
#
# update-site-url.sh - Point an existing WordPress install at its real
# domain, once DNS/SSL are confirmed working (see scripts/README.md's
# "Domain, DNS, and SSL" section for those manual steps - this script
# only handles the WordPress-side change, which is the one part of
# going live that actually is scriptable).
#
# Usage:
#   ./scripts/update-site-url.sh <old-url> <new-url>
#
# Example:
#   ./scripts/update-site-url.sh http://123.45.67.89 https://phoenixjunkcars.com
#
# Requires the same DEPLOY_HOST / DEPLOY_USER / DEPLOY_WP_ROOT
# environment variables as deploy.sh.
#
# Run this AFTER confirming https://your-new-domain resolves and shows
# the site (over a temporary IP/URL, or the host's default subdomain) -
# running it before DNS/SSL are ready will make the site briefly
# unreachable at the old address without being reachable at the new one.
#
set -euo pipefail

OLD_URL="${1:-}"
NEW_URL="${2:-}"

if [ -z "$OLD_URL" ] || [ -z "$NEW_URL" ]; then
	echo "Usage: ./scripts/update-site-url.sh <old-url> <new-url>" >&2
	echo "Example: ./scripts/update-site-url.sh http://123.45.67.89 https://phoenixjunkcars.com" >&2
	exit 1
fi

: "${DEPLOY_HOST:?Set DEPLOY_HOST}"
: "${DEPLOY_USER:?Set DEPLOY_USER}"
: "${DEPLOY_WP_ROOT:?Set DEPLOY_WP_ROOT}"
DEPLOY_PORT="${DEPLOY_PORT:-22}"

SSH_OPTS="-p ${DEPLOY_PORT} -o BatchMode=yes"
REMOTE="${DEPLOY_USER}@${DEPLOY_HOST}"
REMOTE_WP='WP=$(command -v wp || echo "$HOME/bin/wp")'

echo "==> Updating siteurl and home options ..."
ssh $SSH_OPTS "$REMOTE" "${REMOTE_WP}; \$WP --path='${DEPLOY_WP_ROOT}' option update siteurl '${NEW_URL}'"
ssh $SSH_OPTS "$REMOTE" "${REMOTE_WP}; \$WP --path='${DEPLOY_WP_ROOT}' option update home '${NEW_URL}'"

echo "==> Rewriting any stored references from the old URL to the new one ..."
ssh $SSH_OPTS "$REMOTE" "${REMOTE_WP}; \$WP --path='${DEPLOY_WP_ROOT}' search-replace '${OLD_URL}' '${NEW_URL}' --all-tables --precise"

echo "==> Flushing rewrite rules and cache ..."
ssh $SSH_OPTS "$REMOTE" "${REMOTE_WP}; \$WP --path='${DEPLOY_WP_ROOT}' rewrite flush"
ssh $SSH_OPTS "$REMOTE" "${REMOTE_WP}; \$WP --path='${DEPLOY_WP_ROOT}' cache flush" || true

echo "==> Done. Site now points at ${NEW_URL}."
echo "    Remember to also update SITE_URL in your local deploy environment variables"
echo "    if you use install-wordpress.sh again for a future site."
