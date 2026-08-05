<?php
/**
 * Asset Enqueueing
 *
 * Registers and enqueues frontend and admin assets for the WAIF
 * framework. Assets are automatically versioned using their last
 * modified timestamp to ensure proper cache busting.
 *
 * @package WAIF_Child
 * @since   0.1.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Return the version number for a theme asset.
 *
 * Uses the asset's file modification time when available.
 * Falls back to WAIF_VERSION if the file does not exist.
 *
 * @since 0.1.0
 *
 * @param string $relative_path Asset path relative to the theme root.
 * @return string|int
 */
function waif_asset_version( $relative_path ) {

	$absolute_path = wp_normalize_path(
		WAIF_THEME_DIR . '/' . ltrim( $relative_path, '/' )
	);

	if ( file_exists( $absolute_path ) ) {
		clearstatcache( true, $absolute_path );

		return filemtime( $absolute_path );
	}

	return WAIF_VERSION;
}

add_action( 'wp_enqueue_scripts', 'waif_enqueue_assets' );

/**
 * Enqueue frontend assets.
 *
 * Loads the framework CSS and JavaScript.
 *
 * @since 0.1.0
 */
function waif_enqueue_assets() {

	wp_enqueue_style(
		'waif-global',
		WAIF_ASSETS_URI . '/css/global.css',
		array(),
		waif_asset_version( 'assets/css/global.css' )
	);

	wp_enqueue_script(
		'waif-global',
		WAIF_ASSETS_URI . '/js/global.js',
		array(),
		waif_asset_version( 'assets/js/global.js' ),
		true
	);
}

add_action( 'admin_enqueue_scripts', 'waif_enqueue_admin_assets' );

/**
 * Enqueue admin assets.
 *
 * @since 0.1.0
 */
function waif_enqueue_admin_assets() {

	$screen = get_current_screen();

	if ( ! $screen ) {
		return;
	}

	wp_enqueue_script(
		'waif-admin',
		WAIF_ASSETS_URI . '/js/admin.js',
		array(),
		waif_asset_version( 'assets/js/admin.js' ),
		true
	);
}