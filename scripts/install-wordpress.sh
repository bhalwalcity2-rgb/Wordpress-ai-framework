#!/bin/bash
#
# install-wordpress.sh - Install WordPress core from nothing on a fresh
# server, then activate a theme from this repo and provision its pages.
#
# Usage:
#   ./scripts/install-wordpress.sh <theme-slug>
#
# Example:
#   ./scripts/install-wordpress.sh kadence-child-lvjcb
#
# This is for a genuinely NEW site - no WordPress installed yet at
# DEPLOY_WP_ROOT. If WordPress already exists there, use setup.sh +
# deploy.sh instead; this script's `wp core install` step will simply
# refuse to run against an existing install rather than damage it.
#
# Required environment variables, in addition to the deploy.sh ones:
#   DEPLOY_HOST, DEPLOY_USER, DEPLOY_WP_ROOT   (same meaning as deploy.sh)
#   DB_NAME       Database name (must already exist, or DB_USER must
#                 have CREATE privileges - see notes below).
#   DB_USER       Database username.
#   DB_PASSWORD   Database password.
#   SITE_TITLE    The new site's title.
#   SITE_URL      Final public URL, e.g. https://example.com
#   ADMIN_USER    WordPress admin username to create.
#   ADMIN_PASSWORD WordPress admin password to create.
#   ADMIN_EMAIL   WordPress admin email.
#
# Optional:
#   DEPLOY_PATH   If set, deploy.sh runs automatically at the end to
#                 also ship the theme's code and provision its pages in
#                 one step. If unset, this script only installs
#                 WordPress core + activates the theme; run deploy.sh
#                 separately afterward.
#   DB_HOST       Defaults to localhost.
#   DEPLOY_PORT   SSH port. Defaults to 22.
#
# What this does NOT do: create the database itself (most shared hosts
# require that through a control panel, not SSH), configure DNS, or
# obtain an SSL certificate - see scripts/README.md for those manual
# steps.
#
set -euo pipefail

THEME_SLUG="${1:-}"

if [ -z "$THEME_SLUG" ]; then
	echo "Usage: ./scripts/install-wordpress.sh <theme-slug>" >&2
	exit 1
fi

: "${DEPLOY_HOST:?Set DEPLOY_HOST}"
: "${DEPLOY_USER:?Set DEPLOY_USER}"
: "${DEPLOY_WP_ROOT:?Set DEPLOY_WP_ROOT}"
: "${DB_NAME:?Set DB_NAME}"
: "${DB_USER:?Set DB_USER}"
: "${DB_PASSWORD:?Set DB_PASSWORD}"
: "${SITE_TITLE:?Set SITE_TITLE}"
: "${SITE_URL:?Set SITE_URL}"
: "${ADMIN_USER:?Set ADMIN_USER}"
: "${ADMIN_PASSWORD:?Set ADMIN_PASSWORD}"
: "${ADMIN_EMAIL:?Set ADMIN_EMAIL}"
DB_HOST="${DB_HOST:-localhost}"
DEPLOY_PORT="${DEPLOY_PORT:-22}"

SSH_OPTS="-p ${DEPLOY_PORT} -o BatchMode=yes"
REMOTE="${DEPLOY_USER}@${DEPLOY_HOST}"
REMOTE_WP='WP=$(command -v wp || echo "$HOME/bin/wp")'

echo "==> Verifying SSH connectivity to ${REMOTE} ..."
ssh $SSH_OPTS "$REMOTE" "echo connected" > /dev/null

echo "==> Checking for WP-CLI on the server ..."
if ssh $SSH_OPTS "$REMOTE" "command -v wp > /dev/null 2>&1 || [ -x \"\$HOME/bin/wp\" ]"; then
	echo "    Already available."
else
	echo "    Installing to ~/bin/wp ..."
	ssh $SSH_OPTS "$REMOTE" "
		mkdir -p \$HOME/bin
		curl -sSL -o \$HOME/bin/wp https://raw.githubusercontent.com/wp-cli/builds/gh-pages/phar/wp-cli.phar
		chmod +x \$HOME/bin/wp
	"
fi

echo "==> Checking whether WordPress already exists at ${DEPLOY_WP_ROOT} ..."
if ssh $SSH_OPTS "$REMOTE" "[ -f '${DEPLOY_WP_ROOT}/wp-config.php' ]"; then
	echo "wp-config.php already exists at ${DEPLOY_WP_ROOT} - refusing to overwrite an existing install." >&2
	echo "If this is intentional, use setup.sh + deploy.sh instead of this script." >&2
	exit 1
fi

echo "==> Creating ${DEPLOY_WP_ROOT} ..."
ssh $SSH_OPTS "$REMOTE" "mkdir -p '${DEPLOY_WP_ROOT}'"

echo "==> Downloading WordPress core ..."
ssh $SSH_OPTS "$REMOTE" "${REMOTE_WP}; \$WP core download --path='${DEPLOY_WP_ROOT}'"

echo "==> Writing wp-config.php ..."
ssh $SSH_OPTS "$REMOTE" "${REMOTE_WP}; \$WP config create \
	--path='${DEPLOY_WP_ROOT}' \
	--dbname='${DB_NAME}' \
	--dbuser='${DB_USER}' \
	--dbpass='${DB_PASSWORD}' \
	--dbhost='${DB_HOST}' \
	--skip-check"

echo "==> Running core install ..."
ssh $SSH_OPTS "$REMOTE" "${REMOTE_WP}; \$WP core install \
	--path='${DEPLOY_WP_ROOT}' \
	--url='${SITE_URL}' \
	--title='${SITE_TITLE}' \
	--admin_user='${ADMIN_USER}' \
	--admin_password='${ADMIN_PASSWORD}' \
	--admin_email='${ADMIN_EMAIL}' \
	--skip-email"

echo "==> Installing and activating the Kadence parent theme ..."
ssh $SSH_OPTS "$REMOTE" "${REMOTE_WP}; \$WP theme install kadence --path='${DEPLOY_WP_ROOT}'" || true

echo "==> Setting permalink structure ..."
ssh $SSH_OPTS "$REMOTE" "${REMOTE_WP}; \$WP rewrite structure '/%postname%/' --path='${DEPLOY_WP_ROOT}'"

echo "==> WordPress core install complete."

if [ -n "${DEPLOY_PATH:-}" ]; then
	echo "==> DEPLOY_PATH is set - handing off to deploy.sh to ship the theme and provision pages ..."
	"$(dirname "${BASH_SOURCE[0]}")/deploy.sh" "$THEME_SLUG"

	echo "==> Activating ${THEME_SLUG} ..."
	ssh $SSH_OPTS "$REMOTE" "${REMOTE_WP}; \$WP theme activate '${THEME_SLUG}' --path='${DEPLOY_WP_ROOT}'"
else
	echo "==> DEPLOY_PATH not set - run deploy.sh separately to ship the theme code, then:"
	echo "    wp theme activate ${THEME_SLUG} --path=${DEPLOY_WP_ROOT}"
fi

echo "==> Done. Site: ${SITE_URL}  Admin: ${SITE_URL}/wp-admin (user: ${ADMIN_USER})"
