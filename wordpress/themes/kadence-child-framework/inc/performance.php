<?php
/**
 * Frontend Performance Optimizations
 *
 * Trims default WordPress Core behaviors that add frontend script or
 * request overhead. Every measure in this file is theme-level dead
 * weight removal or a targeted request-count reduction — nothing here
 * duplicates caching, minification, or asset delivery, which is WP
 * Rocket's responsibility.
 *
 * This file must NEVER:
 *  - Duplicate WP Rocket's job: caching, minification, CSS/JS delivery,
 *    lazy loading, preloading, database cleanup, heartbeat control, CDN
 *  - Duplicate Rank Math's job: schema, meta tags, sitemaps, redirects
 *  - Duplicate security.php's job: information disclosure or attack
 *    surface reduction — even when a security measure has a performance
 *    side effect, it stays in security.php
 *  - Register or enqueue scripts/styles — that is enqueue.php's job
 *  - Modify wp-config.php, define constants, or touch server config
 *  - Run must-always-active logic that should survive a theme switch
 *    (that belongs in wordpress/mu-plugins/ — a separate, open decision)
 *
 * Emoji asset removal and jQuery Migrate removal were moved here from
 * security.php: both are frontend payload-weight optimizations with no
 * information-disclosure component, so they belong in this file instead.
 *
 * @package    WAIF_Child
 * @subpackage Performance
 * @since      0.1.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/*
|----------------------------------------------------------------------
| Emoji Asset Removal
|----------------------------------------------------------------------
| WordPress loads an inline JavaScript detector and an external
| stylesheet on every page to render emoji as images in browsers
| that lack native support. All modern browsers render emoji
| natively since 2017. These assets add ~15 KB of blocking
| payload to every page load for no visible benefit.
|
| Moved from security.php — this concern is exclusively a payload
| weight optimization, not an information-disclosure measure.
*/

add_action( 'init', 'waif_disable_emojis' );

/**
 * Remove all emoji-related scripts, styles, and filters.
 *
 * Unhooks every emoji integration point in both the frontend and
 * the admin. The TinyMCE plugin removal prevents the classic
 * editor from loading its own emoji handler.
 *
 * @since 0.1.0
 *
 * @return void
 */
function waif_disable_emojis() {

	// Frontend.
	remove_action( 'wp_head', 'print_emoji_detection_script', 7 );
	remove_action( 'wp_print_styles', 'print_emoji_styles' );

	// Admin.
	remove_action( 'admin_print_scripts', 'print_emoji_detection_script' );
	remove_action( 'admin_print_styles', 'print_emoji_styles' );

	// RSS feeds.
	remove_filter( 'the_content_feed', 'wp_staticize_emoji' );
	remove_filter( 'comment_text_rss', 'wp_staticize_emoji' );

	// Email.
	remove_filter( 'wp_mail', 'wp_staticize_emoji_for_email' );

	// Classic editor plugin.
	add_filter( 'tiny_mce_plugins', 'waif_remove_tinymce_emoji' );

	// DNS prefetch for the emoji CDN.
	add_filter( 'wp_resource_hints', 'waif_remove_emoji_dns_prefetch', 10, 2 );
}

/**
 * Remove the TinyMCE emoji plugin from the classic editor.
 *
 * @since 0.1.0
 *
 * @param array $plugins List of TinyMCE plugin slugs.
 * @return array Filtered list without 'wpemoji'.
 */
function waif_remove_tinymce_emoji( $plugins ) {

	if ( ! is_array( $plugins ) ) {
		return array();
	}

	return array_diff( $plugins, array( 'wpemoji' ) );
}

/**
 * Remove the DNS prefetch hint for the WordPress emoji CDN.
 *
 * WordPress adds a dns-prefetch for s.w.org to speed up emoji
 * image loading. With emojis disabled, the prefetch is wasted
 * bandwidth and an unnecessary external connection.
 *
 * @since 0.1.0
 *
 * @param array  $urls          List of URLs for the given relation type.
 * @param string $relation_type The relation type: 'dns-prefetch', 'preconnect', etc.
 * @return array Filtered URL list.
 */
function waif_remove_emoji_dns_prefetch( $urls, $relation_type ) {

	if ( 'dns-prefetch' !== $relation_type ) {
		return $urls;
	}

	foreach ( $urls as $key => $url ) {
		if ( is_array( $url ) && isset( $url['href'] ) ) {
			$url = $url['href'];
		}

		if ( is_string( $url ) && false !== strpos( $url, 's.w.org' ) ) {
			unset( $urls[ $key ] );
		}
	}

	return array_values( $urls );
}

/*
|----------------------------------------------------------------------
| jQuery Migrate Removal (Frontend Only)
|----------------------------------------------------------------------
| WordPress loads jQuery Migrate to provide backward compatibility
| for deprecated jQuery methods used by legacy plugins. The WAIF
| framework uses vanilla JavaScript exclusively on the frontend,
| so Migrate is dead weight. It remains loaded in wp-admin where
| plugins and core UI may still depend on it.
|
| Moved from security.php — this concern is exclusively a frontend
| script-weight trim, not a security measure.
*/

add_action( 'wp_default_scripts', 'waif_remove_jquery_migrate' );

/**
 * Dequeue jQuery Migrate from the frontend script stack.
 *
 * Modifies the jquery script's dependency list to remove
 * jquery-migrate. Only runs on frontend requests to avoid
 * breaking admin screens or the block editor.
 *
 * @since 0.1.0
 *
 * @param WP_Scripts $scripts The global WP_Scripts instance.
 * @return void
 */
function waif_remove_jquery_migrate( $scripts ) {

	if ( is_admin() ) {
		return;
	}

	if ( ! isset( $scripts->registered['jquery'] ) ) {
		return;
	}

	$jquery_deps = $scripts->registered['jquery']->deps;

	$scripts->registered['jquery']->deps = array_diff(
		$jquery_deps,
		array( 'jquery-migrate' )
	);
}

/*
|----------------------------------------------------------------------
| Self-Pingback Prevention
|----------------------------------------------------------------------
| When a post links to another post on the same site, WordPress
| triggers an internal pingback request to itself on every save.
| This has no visible benefit on a single-site install and adds
| unnecessary HTTP request overhead. This does not disable pingbacks
| from other sites — only self-referential ones.
*/

add_action( 'pre_ping', 'waif_remove_self_pingbacks' );

/**
 * Remove links pointing to this site from the outgoing pingback list.
 *
 * @since 0.1.0
 *
 * @param array $links Reference to the array of URLs WordPress will ping.
 * @return void
 */
function waif_remove_self_pingbacks( &$links ) {

	$home_url = home_url();

	foreach ( $links as $key => $link ) {
		if ( 0 === strpos( $link, $home_url ) ) {
			unset( $links[ $key ] );
		}
	}
}
