<?php
/**
 * Security Hardening
 *
 * Project-specific adaptation of the framework's security.php module —
 * same audited measures, lvjcb_ prefixed. See
 * kadence-child-framework/inc/security.php for the original rationale
 * behind each measure; not repeated here to avoid duplicating the same
 * documentation in two places.
 *
 * @package LVJCB
 * @since   0.1.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

remove_action( 'wp_head', 'wp_generator' );
add_filter( 'the_generator', '__return_empty_string' );

remove_action( 'wp_head', 'wlwmanifest_link' );
remove_action( 'wp_head', 'rsd_link' );
remove_action( 'wp_head', 'wp_shortlink_wp_head' );

add_filter( 'xmlrpc_enabled', '__return_false' );

add_filter( 'rest_endpoints', 'lvjcb_restrict_user_enumeration' );

/**
 * Remove the users REST endpoint for unauthenticated requests.
 *
 * @since 0.1.0
 *
 * @param array $endpoints Registered REST API endpoints.
 * @return array Filtered endpoints.
 */
function lvjcb_restrict_user_enumeration( $endpoints ) {

	if ( is_user_logged_in() ) {
		return $endpoints;
	}

	$user_routes = array(
		'/wp/v2/users',
		'/wp/v2/users/(?P<id>[\d]+)',
	);

	foreach ( $user_routes as $route ) {
		if ( isset( $endpoints[ $route ] ) ) {
			unset( $endpoints[ $route ] );
		}
	}

	return $endpoints;
}

add_filter( 'style_loader_src', 'lvjcb_remove_version_query', 10, 2 );
add_filter( 'script_loader_src', 'lvjcb_remove_version_query', 10, 2 );

/**
 * Strip the WordPress core version query string from asset URLs.
 *
 * @since 0.1.0
 *
 * @param string $src    The asset URL with query string.
 * @param string $handle The registered script or style handle.
 * @return string Cleaned URL.
 */
function lvjcb_remove_version_query( $src, $handle ) {

	if ( is_admin() || ! is_string( $src ) ) {
		return $src;
	}

	if ( false !== strpos( $src, '?ver=' . get_bloginfo( 'version' ) ) ) {
		$src = remove_query_arg( 'ver', $src );
	}

	return $src;
}
