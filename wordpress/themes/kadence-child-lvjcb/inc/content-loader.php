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

		/*
		 * A malformed file used to return null without a word, and the
		 * page then rendered the generic templated fallback with nothing
		 * to say why. Reporting it here is what makes that failure
		 * detectable. The null return is deliberately kept, so a broken
		 * file still degrades to the fallback rather than fatalling a
		 * live page.
		 */
		lvjcb_content_error(
			sprintf(
				'Malformed JSON in content/%1$s/%2$s.json — %3$s. Page fell back to the templated layout.',
				$type,
				$slug,
				json_last_error_msg()
			)
		);

		$cache[ $key ] = null;
		return null;
	}

	$cache[ $key ] = lvjcb_resolve_content_tokens( $data );

	return $cache[ $key ];
}

/**
 * Get the homepage's written content, if it has any.
 *
 * Unlike a location or service file this one is additive: front-page.php
 * keeps its frozen section order and renders these sections into it,
 * rather than replacing the layout. The homepage is the primary city's
 * page and must not turn into a second location page, so there is no
 * fallback shape to build here — a site with no home.json simply renders
 * the configured homepage exactly as before.
 *
 * @since 0.5.0
 *
 * @return array|null Parsed content, or null when the file is absent.
 */
function lvjcb_get_home_content() {

	return lvjcb_load_content( 'pages', 'home' );
}

/**
 * Report a content-file problem everywhere someone might be looking.
 *
 * Content files are read at render time, so a bad one has no natural
 * failure surface — the page simply comes out generic. This routes the
 * problem to the error log, to the screen while debugging, and to the
 * terminal during provisioning.
 *
 * @since 0.5.0
 *
 * @param string $message Human-readable description of the problem.
 * @return void
 */
function lvjcb_content_error( $message ) {

	// phpcs:ignore WordPress.PHP.DevelopmentFunctions.error_log_error_log
	error_log( '[lvjcb] ' . $message );

	if ( defined( 'WP_DEBUG' ) && WP_DEBUG ) {
		// phpcs:ignore WordPress.PHP.DevelopmentFunctions.error_log_trigger_error
		trigger_error( esc_html( $message ), E_USER_WARNING );
	}

	if ( defined( 'WP_CLI' ) && WP_CLI ) {
		WP_CLI::warning( $message );
	}
}

/**
 * Replace business tokens throughout a decoded content file.
 *
 * Content files carry `{business}` and `{phone}` rather than the literal
 * name and number, so business-config.php stays the single source of
 * truth and a changed phone number cannot leave stale copies behind in
 * a dozen JSON files. Resolving here — once, at load — means every
 * consumer downstream (SEO titles, meta descriptions, Rank Math
 * provisioning, and the templates themselves) gets real values without
 * each having to know tokens exist.
 *
 * Recurses because the tokens appear at every depth: section
 * paragraphs, block paragraphs, table cells, FAQ answers, footnotes.
 *
 * @since 0.5.0
 *
 * @param mixed $value Decoded value — string, array, or scalar.
 * @return mixed The value with tokens resolved.
 */
function lvjcb_resolve_content_tokens( $value ) {

	static $tokens = null;

	if ( null === $tokens ) {
		$tokens = array(
			'{business}' => (string) lvjcb_get_config( 'business_name' ),
			'{phone}'    => (string) lvjcb_get_phone_number( 'display' ),
		);
	}

	if ( is_string( $value ) ) {
		return strtr( $value, $tokens );
	}

	if ( is_array( $value ) ) {
		foreach ( $value as $index => $item ) {
			$value[ $index ] = lvjcb_resolve_content_tokens( $item );
		}
	}

	return $value;
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
	);
}
