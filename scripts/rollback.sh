#!/bin/bash
#
# rollback.sh — Restore the most recent backup created by deploy.sh.
#
# Usage:
#   ./scripts/rollback.sh <theme-slug> [backup-timestamp]
#
# Example:
#   ./scripts/rollback.sh kadence-child-lvjcb
#   ./scripts/rollback.sh kadence-child-lvjcb 20260101-120000
#
# With no timestamp, rolls back to the most recent backup. Requires the
# same DEPLOY_HOST / DEPLOY_USER / DEPLOY_PATH / DEPLOY_WP_ROOT
# environment variables as deploy.sh.
#
# IMPORTANT: this rolls back theme CODE only. If `wp lvjcb provision`
# ran again after the deploy you're rolling back from, any page/content
# changes it made are not reverted by this script — page content lives
# in the WordPress database, not in the theme's files.
#
set -euo pipefail

THEME_SLUG="${1:-}"
REQUESTED_TIMESTAMP="${2:-}"

if [ -z "$THEME_SLUG" ]; then
	echo "Usage: ./scripts/rollback.sh <theme-slug> [backup-timestamp]" >&2
	exit 1
fi

: "${DEPLOY_HOST:?Set DEPLOY_HOST before running this script}"
: "${DEPLOY_USER:?Set DEPLOY_USER before running this script}"
: "${DEPLOY_PATH:?Set DEPLOY_PATH before running this script}"
: "${DEPLOY_WP_ROOT:?Set DEPLOY_WP_ROOT before running this script}"
DEPLOY_PORT="${DEPLOY_PORT:-22}"

SSH_OPTS="-p ${DEPLOY_PORT} -o BatchMode=yes"
REMOTE="${DEPLOY_USER}@${DEPLOY_HOST}"
BACKUPS_DIR="${DEPLOY_PATH}.backups"

if [ -z "$REQUESTED_TIMESTAMP" ]; then
	echo "==> No timestamp given, finding most recent backup ..."
	REQUESTED_TIMESTAMP="$(ssh $SSH_OPTS "$REMOTE" "cd '${BACKUPS_DIR}' 2>/dev/null && ls -1t | head -n 1" || true)"
fi

if [ -z "$REQUESTED_TIMESTAMP" ]; then
	echo "No backups found at ${BACKUPS_DIR} on the server." >&2
	exit 1
fi

BACKUP_PATH="${BACKUPS_DIR}/${REQUESTED_TIMESTAMP}"

echo "==> Verifying backup exists: ${BACKUP_PATH} ..."
if ! ssh $SSH_OPTS "$REMOTE" "[ -d '${BACKUP_PATH}' ]"; then
	echo "No such backup: ${BACKUP_PATH}" >&2
	exit 1
fi

echo "==> Restoring ${BACKUP_PATH} to ${DEPLOY_PATH} ..."
ssh $SSH_OPTS "$REMOTE" "rm -rf '${DEPLOY_PATH}' && cp -a '${BACKUP_PATH}' '${DEPLOY_PATH}'"

echo "==> Flushing cache ..."
REMOTE_WP='WP=$(command -v wp || echo "$HOME/bin/wp")'
ssh $SSH_OPTS "$REMOTE" "${REMOTE_WP}; \$WP --path='${DEPLOY_WP_ROOT}' cache flush" || true

echo "==> Rolled back to ${REQUESTED_TIMESTAMP}. Remember: page content in the database was not reverted, only theme code."
