#!/bin/bash
#
# new-site.sh - Scaffold a new business's theme by cloning
# kadence-child-lvjcb and renaming its prefix throughout, then
# resetting business-config.php to a blank template.
#
# Usage:
#   ./scripts/new-site.sh <business-slug>
#
# Example:
#   ./scripts/new-site.sh phoenix-junk-cars
#
# This is the first step for a new business. After it runs:
#   1. Fill in wordpress/themes/kadence-child-<slug>/inc/business-config.php
#   2. ./scripts/setup.sh kadence-child-<slug>          (or install-wordpress.sh, for a brand new server)
#   3. ./scripts/deploy.sh kadence-child-<slug>
#
# The source theme (kadence-child-lvjcb) is never modified by this
# script - it only reads from it.
#
set -euo pipefail

SLUG="${1:-}"

if [ -z "$SLUG" ]; then
	echo "Usage: ./scripts/new-site.sh <business-slug>" >&2
	echo "Example: ./scripts/new-site.sh phoenix-junk-cars" >&2
	exit 1
fi

if ! [[ "$SLUG" =~ ^[a-z0-9]+(-[a-z0-9]+)*$ ]]; then
	echo "Slug must be lowercase letters, numbers, and hyphens only (e.g. phoenix-junk-cars)." >&2
	exit 1
fi

REPO_ROOT="$(cd "$(dirname "${BASH_SOURCE[0]}")/.." && pwd)"
SOURCE_DIR="${REPO_ROOT}/wordpress/themes/kadence-child-lvjcb"
NEW_THEME_SLUG="kadence-child-${SLUG}"
NEW_DIR="${REPO_ROOT}/wordpress/themes/${NEW_THEME_SLUG}"

if [ ! -d "$SOURCE_DIR" ]; then
	echo "Source theme not found: ${SOURCE_DIR}" >&2
	exit 1
fi

if [ -d "$NEW_DIR" ]; then
	echo "Target already exists: ${NEW_DIR}" >&2
	exit 1
fi

# Prefix forms derived from the slug.
PREFIX_HYPHEN="$SLUG"                                  # e.g. phoenix-junk-cars
PREFIX_LOWER="$(echo "$SLUG" | tr '-' '_')"             # e.g. phoenix_junk_cars
PREFIX_UPPER="$(echo "$PREFIX_LOWER" | tr '[:lower:]' '[:upper:]')"  # e.g. PHOENIX_JUNK_CARS

echo "==> Cloning ${SOURCE_DIR}"
echo "                -> ${NEW_DIR}"
cp -a "$SOURCE_DIR" "$NEW_DIR"

echo "==> Renaming prefix: lvjcb -> ${PREFIX_LOWER} (and LVJCB -> ${PREFIX_UPPER}, lvjcb- -> ${PREFIX_HYPHEN}-)"
find "$NEW_DIR" -type f \( -name "*.php" -o -name "*.css" -o -name "*.js" -o -name "*.md" \) -print0 \
	| xargs -0 sed -i \
		-e "s/lvjcb_/${PREFIX_LOWER}_/g" \
		-e "s/LVJCB_/${PREFIX_UPPER}_/g" \
		-e "s/lvjcb-/${PREFIX_HYPHEN}-/g" \
		-e "s/'lvjcb'/'${PREFIX_HYPHEN}'/g" \
		-e "s/\"lvjcb\"/\"${PREFIX_HYPHEN}\"/g" \
		-e "s/'lvjcb provision'/'${PREFIX_HYPHEN} provision'/g"

echo "==> Updating style.css theme header"
sed -i \
	-e "s/Theme Name:.*Las Vegas Junk Car Buyers.*/Theme Name:        ${SLUG}/" \
	-e "s/Las Vegas Junk Car Buyers/${SLUG}/g" \
	"${NEW_DIR}/style.css"

echo "==> Clearing content files (start fresh for new business)"
rm -rf "${NEW_DIR}/content/locations/"*.json "${NEW_DIR}/content/services/"*.json 2>/dev/null || true
mkdir -p "${NEW_DIR}/content/locations" "${NEW_DIR}/content/services"

echo "==> Resetting business-config.php to a blank template"
cat > "${NEW_DIR}/inc/business-config.php" << PHPEOF
<?php
/**
 * Business Config - ${SLUG}
 *
 * Every piece of business-specific data for this site lives here, and
 * only here. Fill in every value below before running
 * "wp ${PREFIX_LOWER} provision".
 *
 * @package ${PREFIX_UPPER}
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

return array(

	'business_name' => 'TODO: Business Name',
	'tagline'       => 'TODO: One-line description',
	'address'       => 'TODO: Street address, City, State ZIP',
	'email'         => 'TODO: contact@example.com',
	'hours'         => 'TODO: business hours',
	'map_embed'     => '',

	'phone_e164'    => 'TODO: +1XXXXXXXXXX',
	'phone_display' => 'TODO: (XXX) XXX-XXXX',

	'colors' => array(
		'primary'   => '#TODO00',
		'accent'    => '#TODO00',
		'dark'      => '#TODO00',
		'neutral'   => '#TODO00',
		'secondary' => '#TODO00',
	),

	'nav' => array(
		// array( 'label' => 'TODO', 'slug' => 'TODO' ),
	),

	'trust_items' => array(
		// array( 'icon' => 'truck', 'label' => 'TODO' ),
	),

	'hero' => array(
		'heading'     => 'TODO',
		'description' => 'TODO',
		'eyebrow'     => 'TODO',
		'image_id'    => 0,
	),

	'services' => array(
		'eyebrow' => 'TODO',
		'heading' => 'TODO',
		'intro'   => 'TODO',
		'cards'   => array(
			// array( 'icon' => 'TODO', 'heading' => 'TODO', 'description' => 'TODO', 'slug' => 'TODO', 'intro' => 'TODO' ),
		),
	),

	'how_it_works' => array(
		'eyebrow' => 'TODO',
		'heading' => 'TODO',
		'intro'   => 'TODO',
		'steps'   => array(
			// array( 'icon' => 'TODO', 'heading' => 'TODO', 'description' => 'TODO' ),
		),
	),

	'why_choose_us' => array(
		'eyebrow' => 'TODO',
		'heading' => 'TODO',
		'intro'   => 'TODO',
		'cards'   => array(
			// array( 'icon' => 'TODO', 'heading' => 'TODO', 'description' => 'TODO' ),
		),
	),

	'vehicles' => array(
		'eyebrow' => 'TODO',
		'heading' => 'TODO',
		'intro'   => 'TODO',
		'items'   => array(
			// array( 'image_slug' => 'TODO', 'image_alt' => 'TODO', 'vehicle' => 'TODO', 'condition' => 'TODO' ),
		),
	),

	'testimonials' => array(
		'eyebrow' => 'TODO',
		'heading' => 'TODO',
		'items'   => array(
			// array( 'rating' => 5, 'quote' => 'TODO', 'customer_name' => 'TODO', 'customer_location' => 'TODO' ),
		),
	),

	'service_areas' => array(
		'eyebrow' => 'TODO',
		'heading' => 'TODO',
		'items'   => array(
			// array( 'city' => 'TODO', 'state' => 'TODO', 'slug' => 'TODO', 'intro' => 'TODO' ),
		),
	),

	'faq' => array(
		'eyebrow' => 'TODO',
		'heading' => 'TODO',
		'items'   => array(
			// array( 'id' => 'TODO', 'question' => 'TODO', 'answer' => 'TODO' ),
		),
	),

	'cta_banner' => array(
		'mid_page'  => array( 'heading' => 'TODO', 'intro' => 'TODO' ),
		'late_page' => array( 'heading' => 'TODO', 'intro' => 'TODO' ),
	),

	'contact' => array(
		'eyebrow' => 'TODO',
		'heading' => 'TODO',
	),

	'about' => array(
		'eyebrow' => 'TODO',
		'heading' => 'TODO',
		'body'    => 'TODO',
	),

);
PHPEOF

echo "==> Done."
echo ""
echo "New theme created at: wordpress/themes/${NEW_THEME_SLUG}"
echo "Function/constant/class prefix: ${PREFIX_LOWER}_ / ${PREFIX_UPPER}_"
echo "CSS/slug prefix: ${PREFIX_HYPHEN}-"
echo ""
echo "Next steps:"
echo "  1. Fill in wordpress/themes/${NEW_THEME_SLUG}/inc/business-config.php"
echo "  2. Generate content files: use ai/content-generation-prompt.md"
echo "     Save to: wordpress/themes/${NEW_THEME_SLUG}/content/locations/ and /services/"
echo "  3. ./scripts/setup.sh ${NEW_THEME_SLUG}   (existing WP)   OR   ./scripts/install-wordpress.sh ${NEW_THEME_SLUG}   (brand new server)"
echo "  4. ./scripts/deploy.sh ${NEW_THEME_SLUG}"
