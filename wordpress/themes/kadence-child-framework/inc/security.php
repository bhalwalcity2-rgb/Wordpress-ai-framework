<?php
/**
 * Security Hardening
 *
 * Applies safe, non-destructive security improvements that reduce
 * the WordPress attack surface without breaking core functionality,
 * plugins, or the block editor. Every measure in this file removes
 * information disclosure or disables features that are unused in
 * the WAIF framework's standard deployment profile.
 *
 * This file must NEVER:
 *  - Disable the REST API, Heartbeat, Gutenberg, RSS, or oEmbed
 *  - Modify wp-config.php or define constants
 *  - Change the login URL or block wp-admin access
 *  - Add HTTP security headers (handled at server level)
 *  - Force SSL (handled at server level)
 *  - Duplicate performance.php's job — a measure that trims payload
 *    weight but has no disclosure/attack-surface angle belongs there,
 *    not here (see performance.php for emoji assets and jQuery Migrate,
 *    both moved out of this file for that reason)
 *
 * @package    WAIF_Child
 * @subpackage Security
 * @since      0.1.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/*
|----------------------------------------------------------------------
| Version Disclosure
|----------------------------------------------------------------------
| WordPress exposes its version in a <meta> generator tag, in RSS
| feeds, in script query strings, and in HTTP headers. Attackers
| use this to match known vulnerabilities to specific versions.
| Removing these tags eliminates the lowest-hanging reconnaissance
| vector without affecting any functionality.
*/

remove_action( 'wp_head', 'wp_generator' );

add_filter( 'the_generator', 'waif_remove_wp_version' );

/**
 * Return an empty string wherever WordPress outputs a version.
 *
 * Covers the generator meta tag, RSS generator element, and any
 * other context that passes through the 'the_generator' filter.
 *
 * @since 0.1.0
 *
 * @return string Empty string.
 */
function waif_remove_wp_version() {
	return '';
}

/*
|----------------------------------------------------------------------
| Legacy Header Cleanup
|----------------------------------------------------------------------
| These wp_head actions output <link> tags for services that the
| framework does not use. Removing them shrinks the <head> and
| eliminates unnecessary external endpoint exposure.
|
| wlwmanifest — Windows Live Writer desktop publishing protocol.
|               Unused since WLW was discontinued in 2017.
|
| rsd_link    — Really Simple Discovery, an XML-RPC auto-discovery
|               mechanism. Redundant when XML-RPC is disabled.
|
| shortlink   — Outputs a ?p=123 shortlink. Superseded by pretty
|               permalinks and plugin-generated short URLs.
*/

remove_action( 'wp_head', 'wlwmanifest_link' );
remove_action( 'wp_head', 'rsd_link' );
remove_action( 'wp_head', 'wp_shortlink_wp_head' );

/*
|----------------------------------------------------------------------
| XML-RPC
|----------------------------------------------------------------------
| XML-RPC is a legacy remote publishing protocol superseded by the
| REST API. It is the most common vector for brute-force amplification
| attacks against WordPress because it allows hundreds of login
| attempts in a single HTTP request via system.multicall.
*/

add_filter( 'xmlrpc_enabled', '__return_false' );

/*
|----------------------------------------------------------------------
| File Editor Admin Notice
|----------------------------------------------------------------------
| The built-in Theme Editor and Plugin Editor allow anyone with
| admin access to execute arbitrary PHP on the server. Best practice
| is to define DISALLOW_FILE_EDIT in wp-config.php. If the constant
| is missing, this notice reminds the site administrator to set it.
|
| The constant is NOT defined here — wp-config.php is the only
| appropriate place for it, and modifying runtime constants from
| a theme is unreliable.
*/

add_action( 'admin_notices', 'waif_file_edit_notice' );

/**
 * Display an admin notice when DISALLOW_FILE_EDIT is not set.
 *
 * Only shown to users with the manage_options capability to avoid
 * alarming non-admin users who cannot act on the recommendation.
 *
 * @since 0.1.0
 *
 * @return void
 */
function waif_file_edit_notice() {

	if ( defined( 'DISALLOW_FILE_EDIT' ) && DISALLOW_FILE_EDIT ) {
		return;
	}

	if ( ! current_user_can( 'manage_options' ) ) {
		return;
	}

	printf(
		'<div class="notice notice-warning"><p>%s</p></div>',
		esc_html__(
			'WAIF Security: The DISALLOW_FILE_EDIT constant is not enabled. Add define( \'DISALLOW_FILE_EDIT\', true ); to wp-config.php to disable the theme and plugin file editors.',
			'waif-child'
		)
	);
}

/*
|----------------------------------------------------------------------
| REST API User Enumeration
|----------------------------------------------------------------------
| The /wp-json/wp/v2/users endpoint exposes usernames to any
| unauthenticated visitor. Attackers use this to harvest valid
| usernames for brute-force attacks. This filter blocks the
| endpoint for logged-out users while keeping it fully functional
| for authenticated requests, which plugins like Yoast and Rank
| Math depend on.
*/

add_filter( 'rest_endpoints', 'waif_restrict_user_enumeration' );

/**
 * Remove the users endpoint for unauthenticated requests.
 *
 * Checks authentication status at the endpoint registration
 * level rather than using a permission callback, because some
 * plugins re-register the endpoint with their own callbacks.
 *
 * @since 0.1.0
 *
 * @param array $endpoints Registered REST API endpoints.
 * @return array Filtered endpoints.
 */
function waif_restrict_user_enumeration( $endpoints ) {

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

/*
|----------------------------------------------------------------------
| Generator Tag Suppression
|----------------------------------------------------------------------
| Beyond the core generator removed above, WordPress appends its
| version as a ?ver= query string on every enqueued script and
| stylesheet. This leaks version information for core, plugins,
| and themes. Stripping the WordPress core version from these
| URLs eliminates this disclosure vector.
|
| Only the core WordPress version is stripped. Assets versioned
| with filemtime() via waif_asset_version() use a numeric
| timestamp that does not reveal software versions.
*/

add_filter( 'style_loader_src', 'waif_remove_version_query', 10, 2 );
add_filter( 'script_loader_src', 'waif_remove_version_query', 10, 2 );

/**
 * Strip the WordPress core version query string from asset URLs.
 *
 * Removes the ?ver= parameter only when its value matches the
 * current WordPress version. Third-party and filemtime-based
 * version strings are left intact for cache-busting purposes.
 * Only applies on frontend requests to avoid breaking admin UI.
 *
 * @since 0.1.0
 *
 * @param string $src    The asset URL with query string.
 * @param string $handle The registered script or style handle.
 * @return string Cleaned URL.
 */
function waif_remove_version_query( $src, $handle ) {

	if ( is_admin() ) {
		return $src;
	}

	if ( ! is_string( $src ) ) {
		return $src;
	}

	if ( false !== strpos( $src, '?ver=' . get_bloginfo( 'version' ) ) ) {
		$src = remove_query_arg( 'ver', $src );
	}

	return $src;
}