#!/bin/bash
#
# import-images.sh - Upload a local folder of images into the WordPress
# media library, named so business-config.php's image_slug references
# resolve automatically (see lvjcb_get_attachment_id_by_slug()).
#
# Usage:
#   ./scripts/import-images.sh <theme-slug> <local-images-folder>
#
# Example:
#   ./scripts/import-images.sh kadence-child-lvjcb ./assets-to-import
#
# Each file's slug is derived from its filename (extension stripped),
# so a file must be named to match what business-config.php expects -
# e.g. hero-homepage-01.jpg for 'image_slug' => 'hero-homepage-01', per
# the AI Image Production Guide's naming convention. This script does
# not generate images - it only gets already-produced files into the
# site and wired up, with no manual attachment-ID copying.
#
# Requires the same DEPLOY_HOST / DEPLOY_USER / DEPLOY_WP_ROOT
# environment variables as deploy.sh.
#
set -euo pipefail

THEME_SLUG="${1:-}"
IMAGES_DIR="${2:-}"

if [ -z "$THEME_SLUG" ] || [ -z "$IMAGES_DIR" ]; then
	echo "Usage: ./scripts/import-images.sh <theme-slug> <local-images-folder>" >&2
	exit 1
fi

if [ ! -d "$IMAGES_DIR" ]; then
	echo "No such folder: ${IMAGES_DIR}" >&2
	exit 1
fi

: "${DEPLOY_HOST:?Set DEPLOY_HOST}"
: "${DEPLOY_USER:?Set DEPLOY_USER}"
: "${DEPLOY_WP_ROOT:?Set DEPLOY_WP_ROOT}"
DEPLOY_PORT="${DEPLOY_PORT:-22}"

SSH_OPTS="-p ${DEPLOY_PORT} -o BatchMode=yes"
REMOTE="${DEPLOY_USER}@${DEPLOY_HOST}"
REMOTE_WP='WP=$(command -v wp || echo "$HOME/bin/wp")'
REMOTE_TMP="/tmp/lvjcb-image-import-$$"

shopt -s nullglob
FILES=("$IMAGES_DIR"/*.jpg "$IMAGES_DIR"/*.jpeg "$IMAGES_DIR"/*.png "$IMAGES_DIR"/*.webp)
shopt -u nullglob

if [ ${#FILES[@]} -eq 0 ]; then
	echo "No .jpg/.jpeg/.png/.webp files found in ${IMAGES_DIR}" >&2
	exit 1
fi

echo "==> Found ${#FILES[@]} image(s) to import."

echo "==> Uploading to a temporary folder on the server ..."
ssh $SSH_OPTS "$REMOTE" "mkdir -p '${REMOTE_TMP}'"
rsync -az -e "ssh ${SSH_OPTS}" "${FILES[@]}" "${REMOTE}:${REMOTE_TMP}/"

echo "==> Importing into the media library ..."
for FILE in "${FILES[@]}"; do
	BASENAME="$(basename "$FILE")"
	SLUG="${BASENAME%.*}"
	TITLE="$(echo "$SLUG" | tr '-' ' ')"

	echo "    ${BASENAME}  ->  slug: ${SLUG}"

	ssh $SSH_OPTS "$REMOTE" "
		${REMOTE_WP}
		EXISTING=\$(\$WP --path='${DEPLOY_WP_ROOT}' post list --post_type=attachment --name='${SLUG}' --field=ID --format=csv)
		if [ -n \"\$EXISTING\" ]; then
			echo '      Already imported as attachment ID' \$EXISTING '- skipping. Delete it first to re-import.'
		else
			\$WP --path='${DEPLOY_WP_ROOT}' media import '${REMOTE_TMP}/${BASENAME}' --title='${TITLE}' --alt='${TITLE}'
		fi
	"
done

echo "==> Cleaning up temporary folder on the server ..."
ssh $SSH_OPTS "$REMOTE" "rm -rf '${REMOTE_TMP}'"

echo "==> Done. Re-run 'wp ${THEME_SLUG#kadence-child-} provision' (or reload the page) to pick up the new images - lvjcb_get_attachment_id_by_slug() resolves them automatically by matching slug."
