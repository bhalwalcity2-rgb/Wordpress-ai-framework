<?php
/**
 * Content Loader
 *
 * Reads structured JSON content files from the theme's content/ directory
 * for location and service pages. Each page gets its own JSON file with
 * full SEO content, sections, FAQ, and internal links — far richer than
 * the short intro stored in business-config.php.
 *
 * If no content file exists for a given slug, templates fall back to the
 * config intro gracefully, so pages work immediately and content files
 * can be added incrementally.
 *
 * @package LVJCB
 * @since   0.4.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Load a content file for a location or service page.
 *
 * @since 0.4.0
 *
 * @param string $type 'locations' or 'services'.
 * @param string $slug Page slug matching the filename (without .json).
 * @return array|null Parsed content array, or null if file doesn't exist.
 */
function lvjcb_load_content( $type, $slug ) {

	static $cache = array();

	$key = $type . '/' . $slug;

	if ( array_key_exists( $key, $cache ) ) {
		return $cache[ $key ];
	}

	$file = LVJCB_THEME_DIR . '/content/' . $type . '/' . $slug . '.json';

	if ( ! file_exists( $file ) ) {
		$cache[ $key ] = null;
		return null;
	}

	$raw = file_get_contents( $file );
	$data = json_decode( $raw, true );

	if ( json_last_error() !== JSON_ERROR_NONE ) {
		$cache[ $key ] = null;
		return null;
	}

	$cache[ $key ] = $data;

	return $data;
}

/**
 * Get the full page content for a location page.
 *
 * Returns structured content if a JSON file exists, otherwise builds
 * a minimal fallback from the config intro so the page still renders.
 *
 * @since 0.4.0
 *
 * @param string $slug Location slug.
 * @return array Structured content array.
 */
function lvjcb_get_location_content( $slug ) {

	$content = lvjcb_load_content( 'locations', $slug );

	if ( $content ) {
		return $content;
	}

	$config    = lvjcb_get_config();
	$location  = lvjcb_find_by_slug( $config['service_areas']['items'], $slug );
	$city      = $location ? $location['city'] : ucwords( str_replace( '-', ' ', $slug ) );
	$state     = $location ? $location['state'] : '';
	$intro     = $location ? $location['intro'] : '';
	$label     = trim( $city . ( $state ? ', ' . $state : '' ) );

	return array(
		'slug'             => $slug,
		'city'             => $city,
		'state'            => $state,
		'seo_title'        => sprintf( 'We Buy Junk Cars in %s | %s', $label, $config['business_name'] ),
		'seo_description'  => $intro,
		'hero_heading'     => sprintf( 'We Buy Junk Cars in %s', $label ),
		'hero_description' => $intro,
		'sections'         => array(),
		'faq'              => array(),
		'internal_links'   => array(),
	);
}

/**
 * Get the full page content for a service page.
 *
 * @since 0.4.0
 *
 * @param string $slug Service slug.
 * @return array Structured content array.
 */
function lvjcb_get_service_content( $slug ) {

	$content = lvjcb_load_content( 'services', $slug );

	if ( $content ) {
		return $content;
	}

	$config  = lvjcb_get_config();
	$service = lvjcb_find_by_slug( $config['services']['cards'], $slug );
	$heading = $service ? $service['heading'] : ucwords( str_replace( '-', ' ', $slug ) );
	$intro   = $service ? ( $service['intro'] ?: $service['description'] ) : '';

	return array(
		'slug'             => $slug,
		'seo_title'        => sprintf( '%s | %s', $heading, $config['business_name'] ),
		'seo_description'  => $intro,
		'hero_heading'     => $heading,
		'hero_description' => $intro,
		'sections'         => array(),
		'faq'              => array(),
		'internal_links'   => array(),
	);
}

/**
 * Build internal link HTML for a list of related pages.
 *
 * Used by templates to render contextual internal links within content
 * sections. Links are generated from slugs, so they always stay in sync
 * with the config.
 *
 * @since 0.4.0
 *
 * @param array $links Array of ['label' => string, 'url' => string].
 * @return string HTML list of links.
 */
function lvjcb_render_internal_links( $links ) {

	if ( empty( $links ) ) {
		return '';
	}

	$items = array();

	foreach ( $links as $link ) {
		$items[] = sprintf(
			'<a href="%s">%s</a>',
			esc_url( $link['url'] ),
			esc_html( $link['label'] )
		);
	}

	return implode( ' · ', $items );
}

/**
 * Build internal link data for a location page — links to nearby
 * locations and all services.
 *
 * @since 0.4.0
 *
 * @param string $current_slug The slug of the current location page.
 * @return array{locations: array, services: array}
 */
function lvjcb_build_location_links( $current_slug ) {

	$config    = lvjcb_get_config();
	$locations = array();
	$services  = array();

	foreach ( $config['service_areas']['items'] as $loc ) {
		if ( $loc['slug'] === $current_slug ) {
			continue;
		}
		$label = trim( $loc['city'] . ( $loc['state'] ? ', ' . $loc['state'] : '' ) );
		$locations[] = array(
			'label' => $label,
			'url'   => home_url( '/service-areas/' . $loc['slug'] . '/' ),
		);
	}

	foreach ( $config['services']['cards'] as $svc ) {
		$services[] = array(
			'label' => $svc['heading'],
			'url'   => home_url( '/cash-for-junk-cars/' . $svc['slug'] . '/' ),
		);
	}

	return array(
		'locations' => $locations,
		'services'  => $services,
	);
}

/**
 * Build internal link data for a service page — links to all locations
 * and other services.
 *
 * @since 0.4.0
 *
 * @param string $current_slug The slug of the current service page.
 * @return array{locations: array, services: array}
 */
function lvjcb_build_service_links( $current_slug ) {

	$config    = lvjcb_get_config();
	$locations = array();
	$services  = array();

	foreach ( $config['service_areas']['items'] as $loc ) {
		$label = trim( $loc['city'] . ( $loc['state'] ? ', ' . $loc['state'] : '' ) );
		$locations[] = array(
			'label' => $label,
			'url'   => home_url( '/service-areas/' . $loc['slug'] . '/' ),
		);
	}

	foreach ( $config['services']['cards'] as $svc ) {
		if ( $svc['slug'] === $current_slug ) {
			continue;
		}
		$services[] = array(
			'label' => $svc['heading'],
			'url'   => home_url( '/cash-for-junk-cars/' . $svc['slug'] . '/' ),
		);
	}

	return array(
		'locations' => $locations,
		'services'  => $services,
	);
}
