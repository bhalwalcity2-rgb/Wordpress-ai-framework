<?php
/**
 * Kadence Child Theme — Bootstrap Loader
 *
 * This file is the single bootstrap entry point for the WAIF (WordPress AI Framework)
 * child theme. Its sole responsibility is to define framework constants and load
 * the framework modules.
 *
 * This file must NEVER contain:
 * - Hook registrations (add_action / add_filter)
 * - Function definitions
 * - Business logic
 * - Asset enqueueing
 * - HTML, CSS, or JavaScript output
 *
 * All framework functionality must live inside the /inc directory.
 *
 * @package    WAIF_Child
 * @since      0.1.0
 * @license    GPL-2.0-or-later
 */

/**
 * -------------------------------------------------------------------------
 * Security
 * -------------------------------------------------------------------------
 *
 * Prevent direct access to this file.
 */
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * -------------------------------------------------------------------------
 * Framework Constants
 * -------------------------------------------------------------------------
 *
 * Shared constants available throughout the framework.
 */

if ( ! defined( 'WAIF_VERSION' ) ) {
	define( 'WAIF_VERSION', '0.1.0' );
}

if ( ! defined( 'WAIF_THEME_DIR' ) ) {
	define( 'WAIF_THEME_DIR', get_stylesheet_directory() );
}

if ( ! defined( 'WAIF_THEME_URI' ) ) {
	define( 'WAIF_THEME_URI', get_stylesheet_directory_uri() );
}

if ( ! defined( 'WAIF_INC_DIR' ) ) {
	define( 'WAIF_INC_DIR', WAIF_THEME_DIR . '/inc' );
}

if ( ! defined( 'WAIF_ASSETS_DIR' ) ) {
	define( 'WAIF_ASSETS_DIR', WAIF_THEME_DIR . '/assets' );
}

if ( ! defined( 'WAIF_ASSETS_URI' ) ) {
	define( 'WAIF_ASSETS_URI', WAIF_THEME_URI . '/assets' );
}

/**
 * -------------------------------------------------------------------------
 * Module Loader
 * -------------------------------------------------------------------------
 *
 * Each module is responsible for a single concern.
 * A missing module should immediately stop execution, therefore
 * require_once is intentionally used.
 *
 * Load Order:
 *
 * 1. setup.php               Theme setup and supports
 * 2. enqueue.php             CSS & JavaScript loading
 * 3. helpers.php             Reusable helper functions
 * 4. security.php            Security hardening
 * 5. performance.php         Performance optimizations
 * 6. seo.php                 SEO and Schema functionality
 * 7. admin.php               WordPress Admin customizations
 * 8. shortcodes.php          Shortcode registrations
 * 9. custom-post-types.php   Custom Post Types & Taxonomies
 */

require_once WAIF_INC_DIR . '/setup.php';
require_once WAIF_INC_DIR . '/enqueue.php';
require_once WAIF_INC_DIR . '/helpers.php';
require_once WAIF_INC_DIR . '/security.php';
require_once WAIF_INC_DIR . '/performance.php';
require_once WAIF_INC_DIR . '/seo.php';
require_once WAIF_INC_DIR . '/admin.php';
require_once WAIF_INC_DIR . '/shortcodes.php';
require_once WAIF_INC_DIR . '/custom-post-types.php';