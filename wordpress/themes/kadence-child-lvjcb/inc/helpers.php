<?php
/**
 * Helper Functions
 *
 * Reusable, stateless utility functions. Business data (phone number,
 * trust items) is read from the constants defined in functions.php
 * through the accessors below — every component/section must call
 * these rather than hardcoding the value a second time.
 *
 * @package LVJCB
 * @since   0.1.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Get the URI for a theme asset.
 *
 * @since 0.1.0
 *
 * @param string $path Asset path relative to the assets directory.
 * @return string
 */
function lvjcb_get_asset_uri( $path ) {

	return LVJCB_ASSETS_URI . '/' . ltrim( $path, '/' );
}

/**
 * Return the version number for a theme asset.
 *
 * Uses the asset's last-modified timestamp for cache busting; falls
 * back to LVJCB_VERSION if the file does not exist.
 *
 * @since 0.1.0
 *
 * @param string $relative_path Asset path relative to the theme root.
 * @return string|int
 */
function lvjcb_asset_version( $relative_path ) {

	$absolute_path = wp_normalize_path(
		LVJCB_THEME_DIR . '/' . ltrim( $relative_path, '/' )
	);

	if ( file_exists( $absolute_path ) ) {
		clearstatcache( true, $absolute_path );

		return filemtime( $absolute_path );
	}

	return LVJCB_VERSION;
}

/**
 * Get the business phone number in a specific format.
 *
 * Single source of truth for the phone number, consumed by Header,
 * Hero, and Contact Information — never hardcoded a second time.
 * Reads from the business config, not a constant, so a new site only
 * needs inc/business-config.php changed.
 *
 * @since 0.1.0
 *
 * @param string $format 'display' for human-readable format, 'e164' for
 *                        the tel: link value. Default 'display'.
 * @return string
 */
function lvjcb_get_phone_number( $format = 'display' ) {

	if ( 'e164' === $format ) {
		return lvjcb_get_config( 'phone_e164' );
	}

	return lvjcb_get_config( 'phone_display' );
}

/**
 * Get the Trust Strip items.
 *
 * Single source of truth shared by Trust Strip and, in the future, any
 * other section that needs the same badge list (e.g. Hero's own trust
 * badges) — documented as a shared dependency rather than duplicated.
 * Reads from the business config; a different business's trust badges
 * (which are inherently business-type-specific, not just wording) are
 * set entirely in inc/business-config.php, not here.
 *
 * @since 0.1.0
 *
 * @return array[] List of ['icon' => string, 'label' => string].
 */
function lvjcb_get_trust_items() {

	return lvjcb_get_config( 'trust_items' ) ?? array();
}

/**
 * Resolve an image slug (e.g. "vehicle-sedan-01", matching the AI Image
 * Production Guide's file naming convention) to a media library
 * attachment ID.
 *
 * This is what lets business-config.php reference images by a stable,
 * human-readable name instead of a numeric ID that doesn't exist until
 * someone has actually uploaded the file — drop a correctly-named file
 * in via scripts/import-images.sh (which runs `wp media import`, and
 * WordPress derives the attachment's post_name from the filename), and
 * every place in the config referencing that slug resolves automatically
 * on the next page load. No attachment ID ever needs to be copied by
 * hand into the config.
 *
 * Component contracts are unaffected: Hero and Vehicle Card still take
 * a plain integer $image_id, exactly as originally built — this
 * resolution happens only in the templates that assemble their $args,
 * not inside the components themselves.
 *
 * @since 0.3.0
 *
 * @param string $slug Image slug, matching the attachment's post_name.
 * @return int Attachment ID, or 0 if no matching attachment exists yet.
 */
function lvjcb_get_attachment_id_by_slug( $slug ) {

	if ( ! $slug ) {
		return 0;
	}

	static $cache = array();

	if ( isset( $cache[ $slug ] ) ) {
		return $cache[ $slug ];
	}

	$attachment = get_page_by_path( $slug, OBJECT, 'attachment' );

	$cache[ $slug ] = $attachment ? (int) $attachment->ID : 0;

	return $cache[ $slug ];
}

/**
 * Build the standard Contact Information section args from config.
 *
 * Every page template needs the same contact block — this is the one
 * place that assembly happens, so a new page template never re-reads
 * six individual config keys itself.
 *
 * @since 0.2.0
 *
 * @return array Args ready to pass to template-parts/sections/contact-information.php.
 */
function lvjcb_get_contact_args() {

	$contact = lvjcb_get_config( 'contact' ) ?? array();

	return array(
		'eyebrow'        => $contact['eyebrow'] ?? '',
		'heading'        => $contact['heading'] ?? '',
		'business_name'  => lvjcb_get_config( 'business_name' ),
		'address'        => lvjcb_get_config( 'address' ),
		'phone'          => lvjcb_get_phone_number( 'display' ),
		'email'          => lvjcb_get_config( 'email' ),
		'business_hours' => lvjcb_get_config( 'hours' ),
		'map_embed'      => lvjcb_get_config( 'map_embed' ),
	);
}

/**
 * Build CTA Banner section args from config.
 *
 * @since 0.2.0
 *
 * @param string $id      Unique id for this banner instance on the page.
 * @param string $variant 'mid_page' or 'late_page' copy from config.
 * @return array Args ready to pass to template-parts/sections/cta-banner.php.
 */
function lvjcb_get_cta_banner_args( $id, $variant = 'mid_page' ) {

	$banner = lvjcb_get_config( 'cta_banner' )[ $variant ] ?? array();

	return array(
		'id'       => $id,
		'heading'  => $banner['heading'] ?? '',
		'intro'    => $banner['intro'] ?? '',
		'cta_text' => lvjcb_get_phone_number( 'display' ),
		'cta_url'  => 'tel:' . lvjcb_get_phone_number( 'e164' ),
	);
}
