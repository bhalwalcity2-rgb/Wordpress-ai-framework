#!/bin/bash
#
# deploy.sh — Deploy a theme's code to its live WordPress server.
#
# Generic across every project theme in this repo, not just one
# business — pass the theme folder name as the argument. Reusable for
# every future site built from this framework, not just Las Vegas
# Junk Car Buyers.
#
# Usage:
#   ./scripts/deploy.sh <theme-slug>
#
# Example:
#   ./scripts/deploy.sh kadence-child-lvjcb
#
# Required environment variables (export these in your shell, or put
# them in a local .env file and `source` it before running this):
#   DEPLOY_HOST     Server hostname or IP.
#   DEPLOY_USER     SSH username.
#   DEPLOY_PATH     Absolute path to this theme's folder on the server
#                   (e.g. /home/user/public_html/wp-content/themes/kadence-child-lvjcb).
#   DEPLOY_WP_ROOT  Absolute path to the WordPress root on the server
#                   (the folder containing wp-config.php).
#
# Optional:
#   DEPLOY_PORT     SSH port. Defaults to 22.
#
# What this script does NOT do: install WordPress, create the
# database, or set up SSH keys — see scripts/setup.sh for one-time
# server preparation. This script only ships code and re-provisions
# pages; it assumes WordPress and WP-CLI already exist on the target.
#
set -euo pipefail

THEME_SLUG="${1:-}"

if [ -z "$THEME_SLUG" ]; then
	echo "Usage: ./scripts/deploy.sh <theme-slug>" >&2
	echo "Example: ./scripts/deploy.sh kadence-child-lvjcb" >&2
	exit 1
fi

: "${DEPLOY_HOST:?Set DEPLOY_HOST before running this script}"
: "${DEPLOY_USER:?Set DEPLOY_USER before running this script}"
: "${DEPLOY_PATH:?Set DEPLOY_PATH before running this script}"
: "${DEPLOY_WP_ROOT:?Set DEPLOY_WP_ROOT before running this script}"
DEPLOY_PORT="${DEPLOY_PORT:-22}"

REPO_ROOT="$(cd "$(dirname "${BASH_SOURCE[0]}")/.." && pwd)"
THEME_DIR="${REPO_ROOT}/wordpress/themes/${THEME_SLUG}"

if [ ! -d "$THEME_DIR" ]; then
	echo "No such theme directory: ${THEME_DIR}" >&2
	exit 1
fi

SSH_OPTS="-p ${DEPLOY_PORT} -o BatchMode=yes"
REMOTE="${DEPLOY_USER}@${DEPLOY_HOST}"
TIMESTAMP="$(date -u +%Y%m%d-%H%M%S)"
BACKUP_DIR="${DEPLOY_PATH}.backups/${TIMESTAMP}"

echo "==> Verifying SSH connectivity to ${REMOTE} ..."
ssh $SSH_OPTS "$REMOTE" "echo connected" > /dev/null

echo "==> Verifying WP-CLI is available on the server ..."
if ! ssh $SSH_OPTS "$REMOTE" "command -v wp > /dev/null 2>&1 || [ -x \"\$HOME/bin/wp\" ]"; then
	echo "WP-CLI was not found on the server. Run ./scripts/setup.sh ${THEME_SLUG} first." >&2
	exit 1
fi

echo "==> Backing up current live theme to ${BACKUP_DIR} ..."
ssh $SSH_OPTS "$REMOTE" "
	if [ -d '${DEPLOY_PATH}' ]; then
		mkdir -p '$(dirname "$BACKUP_DIR")'
		cp -a '${DEPLOY_PATH}' '${BACKUP_DIR}'
		# Keep only the 5 most recent backups.
		cd '$(dirname "$BACKUP_DIR")' && ls -1t | tail -n +6 | xargs -r rm -rf
	fi
"

echo "==> Syncing theme files ..."
rsync -az --delete \
	-e "ssh ${SSH_OPTS}" \
	--exclude ".git" \
	--exclude "*.backups" \
	"${THEME_DIR}/" \
	"${REMOTE}:${DEPLOY_PATH}/"

# Non-interactive SSH sessions don't reliably source ~/.bashrc, so the
# PATH update setup.sh makes there can't be trusted here — resolve the
# wp binary explicitly on each remote call instead.
REMOTE_WP='WP=$(command -v wp || echo "$HOME/bin/wp")'

echo "==> Running wp lvjcb provision on the server ..."
ssh $SSH_OPTS "$REMOTE" "${REMOTE_WP}; \$WP --path='${DEPLOY_WP_ROOT}' lvjcb provision"

echo "==> Flushing cache ..."
ssh $SSH_OPTS "$REMOTE" "${REMOTE_WP}; \$WP --path='${DEPLOY_WP_ROOT}' cache flush" || true

echo "==> Deploy complete. Backup saved at ${BACKUP_DIR} (server-side) if a rollback is needed."
