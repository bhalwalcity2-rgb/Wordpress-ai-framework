#!/bin/bash
#
# setup.sh — One-time server preparation before the first deploy.
#
# Usage:
#   ./scripts/setup.sh <theme-slug>
#
# Example:
#   ./scripts/setup.sh kadence-child-lvjcb
#
# Requires the same DEPLOY_HOST / DEPLOY_USER / DEPLOY_PATH /
# DEPLOY_WP_ROOT environment variables as deploy.sh. Run this once per
# server before the first ./scripts/deploy.sh call. Safe to re-run —
# every step checks before acting.
#
# What this does:
#   1. Verifies SSH connectivity.
#   2. Verifies DEPLOY_WP_ROOT looks like a real WordPress install
#      (checks for wp-config.php).
#   3. Installs WP-CLI to ~/bin/wp on the server if it isn't already
#      available, using WP-CLI's own official install method.
#   4. Creates the theme's target directory if it doesn't exist yet.
#
# What this does NOT do: install WordPress itself, create a database,
# or configure DNS/SSL — this assumes WordPress core is already live at
# DEPLOY_WP_ROOT, per the "I already have hosting set up" starting
# point this pipeline was designed around.
#
set -euo pipefail

THEME_SLUG="${1:-}"

if [ -z "$THEME_SLUG" ]; then
	echo "Usage: ./scripts/setup.sh <theme-slug>" >&2
	exit 1
fi

: "${DEPLOY_HOST:?Set DEPLOY_HOST before running this script}"
: "${DEPLOY_USER:?Set DEPLOY_USER before running this script}"
: "${DEPLOY_PATH:?Set DEPLOY_PATH before running this script}"
: "${DEPLOY_WP_ROOT:?Set DEPLOY_WP_ROOT before running this script}"
DEPLOY_PORT="${DEPLOY_PORT:-22}"

SSH_OPTS="-p ${DEPLOY_PORT} -o BatchMode=yes"
REMOTE="${DEPLOY_USER}@${DEPLOY_HOST}"

echo "==> Verifying SSH connectivity to ${REMOTE} ..."
ssh $SSH_OPTS "$REMOTE" "echo connected" > /dev/null
echo "    OK."

echo "==> Verifying ${DEPLOY_WP_ROOT} looks like a WordPress install ..."
if ! ssh $SSH_OPTS "$REMOTE" "[ -f '${DEPLOY_WP_ROOT}/wp-config.php' ]"; then
	echo "    No wp-config.php found at ${DEPLOY_WP_ROOT}." >&2
	echo "    This script does not install WordPress — set DEPLOY_WP_ROOT to an existing install." >&2
	exit 1
fi
echo "    OK."

echo "==> Checking for WP-CLI on the server ..."
if ssh $SSH_OPTS "$REMOTE" "command -v wp > /dev/null 2>&1 || [ -x \"\$HOME/bin/wp\" ]"; then
	echo "    WP-CLI already available."
else
	echo "    Not found — installing to ~/bin/wp (official WP-CLI method) ..."
	ssh $SSH_OPTS "$REMOTE" "
		mkdir -p \$HOME/bin
		curl -sSL -o \$HOME/bin/wp https://raw.githubusercontent.com/wp-cli/builds/gh-pages/phar/wp-cli.phar
		chmod +x \$HOME/bin/wp
		echo 'export PATH=\"\$HOME/bin:\$PATH\"' >> \$HOME/.bashrc
	"
	echo "    Installed. Note: PATH was updated in ~/.bashrc — deploy.sh checks for ~/bin/wp directly so this works even in non-interactive SSH sessions where .bashrc isn't sourced."
fi

echo "==> Ensuring theme target directory exists: ${DEPLOY_PATH} ..."
ssh $SSH_OPTS "$REMOTE" "mkdir -p '${DEPLOY_PATH}'"
echo "    OK."

echo "==> Setup complete for ${THEME_SLUG}. You can now run: ./scripts/deploy.sh ${THEME_SLUG}"
