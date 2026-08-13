#!/bin/bash
#
# generate-content.sh — Validate and list content JSON files for a theme.
#
# Scans the theme's content/ directory to report which locations and
# services have content files vs. which are still using config fallback.
# Also validates JSON syntax so broken files don't deploy.
#
# Usage:
#   ./scripts/generate-content.sh <theme-slug>
#
# Example:
#   ./scripts/generate-content.sh kadence-child-lvjcb
#
set -euo pipefail

THEME_SLUG="${1:-}"

if [ -z "$THEME_SLUG" ]; then
	echo "Usage: ./scripts/generate-content.sh <theme-slug>" >&2
	exit 1
fi

REPO_ROOT="$(cd "$(dirname "${BASH_SOURCE[0]}")/.." && pwd)"
THEME_DIR="${REPO_ROOT}/wordpress/themes/${THEME_SLUG}"
CONTENT_DIR="${THEME_DIR}/content"
CONFIG_FILE="${THEME_DIR}/inc/business-config.php"

if [ ! -d "$THEME_DIR" ]; then
	echo "No such theme directory: ${THEME_DIR}" >&2
	exit 1
fi

# Ensure content directories exist.
mkdir -p "${CONTENT_DIR}/locations" "${CONTENT_DIR}/services"

echo "=== Content Status Report ==="
echo ""

# --- Locations ---
echo "LOCATIONS:"
echo "----------"

# Extract location slugs from config (simple grep approach).
LOCATION_SLUGS=$(grep -oP "'slug'\s*=>\s*'([^']+)'" "$CONFIG_FILE" | sed -n "/'slug'/s/.*'\(.*\)'.*/\1/p" || true)

# If grep didn't find any, check content dir directly.
if [ -z "$LOCATION_SLUGS" ]; then
	echo "  (Could not parse config — checking content directory directly)"
fi

shopt -s nullglob
LOCATION_FILES=("${CONTENT_DIR}/locations/"*.json)
shopt -u nullglob

if [ ${#LOCATION_FILES[@]} -eq 0 ]; then
	echo "  No content files yet. Use the content generation prompt in"
	echo "  ai/content-generation-prompt.md to create them."
else
	ERRORS=0
	for FILE in "${LOCATION_FILES[@]}"; do
		BASENAME="$(basename "$FILE" .json)"
		if python3 -c "import json; json.load(open('$FILE'))" 2>/dev/null || python -c "import json; json.load(open('$FILE'))" 2>/dev/null; then
			echo "  [OK] ${BASENAME}"
		else
			echo "  [!!] ${BASENAME} — INVALID JSON"
			ERRORS=$((ERRORS + 1))
		fi
	done
	echo ""
	echo "  ${#LOCATION_FILES[@]} location file(s) found."
	if [ $ERRORS -gt 0 ]; then
		echo "  WARNING: ${ERRORS} file(s) have invalid JSON."
	fi
fi

echo ""

# --- Services ---
echo "SERVICES:"
echo "---------"

shopt -s nullglob
SERVICE_FILES=("${CONTENT_DIR}/services/"*.json)
shopt -u nullglob

if [ ${#SERVICE_FILES[@]} -eq 0 ]; then
	echo "  No content files yet."
else
	ERRORS=0
	for FILE in "${SERVICE_FILES[@]}"; do
		BASENAME="$(basename "$FILE" .json)"
		if python3 -c "import json; json.load(open('$FILE'))" 2>/dev/null || python -c "import json; json.load(open('$FILE'))" 2>/dev/null; then
			echo "  [OK] ${BASENAME}"
		else
			echo "  [!!] ${BASENAME} — INVALID JSON"
			ERRORS=$((ERRORS + 1))
		fi
	done
	echo ""
	echo "  ${#SERVICE_FILES[@]} service file(s) found."
	if [ $ERRORS -gt 0 ]; then
		echo "  WARNING: ${ERRORS} file(s) have invalid JSON."
	fi
fi

echo ""
echo "=== Next Steps ==="
echo ""
echo "1. Generate missing content files using the prompt in:"
echo "   ai/content-generation-prompt.md"
echo ""
echo "2. Save JSON files to:"
echo "   content/locations/{slug}.json"
echo "   content/services/{slug}.json"
echo ""
echo "3. Push to GitHub — CI/CD deploys automatically."
echo "   Pages without content files still work (config fallback)."
