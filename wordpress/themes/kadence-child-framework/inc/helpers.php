<?php
/**
 * Helper Functions
 *
 * Reusable utility functions for the WAIF Framework.
 * These functions are stateless and contain no side effects.
 *
 * @package WAIF_Child
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
function waif_get_asset_uri( $path ) {

	return WAIF_ASSETS_URI . '/' . ltrim( $path, '/' );
}

/**
 * Get the filesystem path for a theme asset.
 *
 * @since 0.1.0
 *
 * @param string $path Asset path relative to the assets directory.
 * @return string
 */
function waif_get_asset_path( $path ) {

	return wp_normalize_path(
		WAIF_ASSETS_DIR . '/' . ltrim( $path, '/' )
	);
}

/**
 * Determine whether the site is running in development mode.
 *
 * @since 0.1.0
 *
 * @return bool
 */
function waif_is_development() {

	return defined( 'WP_DEBUG' ) && WP_DEBUG;
}

/**
 * Get the current framework version.
 *
 * @since 0.1.0
 *
 * @return string
 */
function waif_get_theme_version() {

	return WAIF_VERSION;
}