<?php
/**
 * Frontend Performance Optimizations
 *
 * Project-specific adaptation of the framework's performance.php module —
 * same audited measures, lvjcb_ prefixed. See
 * kadence-child-framework/inc/performance.php for the original rationale
 * behind each measure.
 *
 * @package LVJCB
 * @since   0.1.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

add_action( 'init', 'lvjcb_disable_emojis' );

/**
 * Remove emoji-related scripts, styles, and filters.
 *
 * @since 0.1.0
 *
 * @return void
 */
function lvjcb_disable_emojis() {

	remove_action( 'wp_head', 'print_emoji_detection_script', 7 );
	remove_action( 'wp_print_styles', 'print_emoji_styles' );
	remove_action( 'admin_print_scripts', 'print_emoji_detection_script' );
	remove_action( 'admin_print_styles', 'print_emoji_styles' );
	remove_filter( 'the_content_feed', 'wp_staticize_emoji' );
	remove_filter( 'comment_text_rss', 'wp_staticize_emoji' );
	remove_filter( 'wp_mail', 'wp_staticize_emoji_for_email' );

	add_filter( 'tiny_mce_plugins', 'lvjcb_remove_tinymce_emoji' );
	add_filter( 'wp_resource_hints', 'lvjcb_remove_emoji_dns_prefetch', 10, 2 );
}

/**
 * Remove the TinyMCE emoji plugin.
 *
 * @since 0.1.0
 *
 * @param array $plugins List of TinyMCE plugin slugs.
 * @return array Filtered list.
 */
function lvjcb_remove_tinymce_emoji( $plugins ) {

	return is_array( $plugins ) ? array_diff( $plugins, array( 'wpemoji' ) ) : array();
}

/**
 * Remove the DNS prefetch hint for the WordPress emoji CDN.
 *
 * @since 0.1.0
 *
 * @param array  $urls          List of URLs for the given relation type.
 * @param string $relation_type The relation type.
 * @return array Filtered URL list.
 */
function lvjcb_remove_emoji_dns_prefetch( $urls, $relation_type ) {

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

add_action( 'wp_default_scripts', 'lvjcb_remove_jquery_migrate' );

/**
 * Dequeue jQuery Migrate from the frontend script stack.
 *
 * @since 0.1.0
 *
 * @param WP_Scripts $scripts The global WP_Scripts instance.
 * @return void
 */
function lvjcb_remove_jquery_migrate( $scripts ) {

	if ( is_admin() || ! isset( $scripts->registered['jquery'] ) ) {
		return;
	}

	$scripts->registered['jquery']->deps = array_diff(
		$scripts->registered['jquery']->deps,
		array( 'jquery-migrate' )
	);
}

add_action( 'pre_ping', 'lvjcb_remove_self_pingbacks' );

/**
 * Remove links pointing to this site from the outgoing pingback list.
 *
 * @since 0.1.0
 *
 * @param array $links Reference to the array of URLs WordPress will ping.
 * @return void
 */
function lvjcb_remove_self_pingbacks( &$links ) {

	$home_url = home_url();

	foreach ( $links as $key => $link ) {
		if ( 0 === strpos( $link, $home_url ) ) {
			unset( $links[ $key ] );
		}
	}
}
