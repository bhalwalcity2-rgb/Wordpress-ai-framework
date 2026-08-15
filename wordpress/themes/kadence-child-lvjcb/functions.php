<?php
/**
 * Kadence Child Theme — Bootstrap Loader
 *
 * Single bootstrap entry point for the Las Vegas Junk Car Buyers child
 * theme. Defines project constants and loads project modules only.
 *
 * This file must NEVER contain:
 * - Hook registrations (add_action / add_filter)
 * - Function definitions
 * - Business logic
 * - Asset enqueueing
 * - HTML, CSS, or JavaScript output
 *
 * All project functionality lives inside /inc.
 *
 * @package    LVJCB
 * @since      0.1.0
 * @license    GPL-2.0-or-later
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * -------------------------------------------------------------------------
 * Project Constants
 * -------------------------------------------------------------------------
 */

if ( ! defined( 'LVJCB_VERSION' ) ) {
	define( 'LVJCB_VERSION', '0.1.0' );
}

if ( ! defined( 'LVJCB_THEME_DIR' ) ) {
	define( 'LVJCB_THEME_DIR', get_stylesheet_directory() );
}

if ( ! defined( 'LVJCB_THEME_URI' ) ) {
	define( 'LVJCB_THEME_URI', get_stylesheet_directory_uri() );
}

if ( ! defined( 'LVJCB_INC_DIR' ) ) {
	define( 'LVJCB_INC_DIR', LVJCB_THEME_DIR . '/inc' );
}

if ( ! defined( 'LVJCB_ASSETS_DIR' ) ) {
	define( 'LVJCB_ASSETS_DIR', LVJCB_THEME_DIR . '/assets' );
}

if ( ! defined( 'LVJCB_ASSETS_URI' ) ) {
	define( 'LVJCB_ASSETS_URI', LVJCB_THEME_URI . '/assets' );
}

/**
 * -------------------------------------------------------------------------
 * Module Loader
 * -------------------------------------------------------------------------
 *
 * Load Order:
 *
 * 1. config.php        lvjcb_get_config() — loads business-config.php
 * 2. colors.php         Contrast-safe color derivation from config colors
 * 3. setup.php          Theme setup, supports, registered image sizes
 * 4. enqueue.php        CSS & JavaScript loading
 * 5. helpers.php        Reusable helper functions (business data accessors)
 * 6. icons.php          Inline icon sprite + lvjcb_icon() renderer
 * 7. slider.php         Global Slider/Carousel open/close helpers
 * 8. security.php       Security hardening
 * 9. performance.php    Performance optimizations
 * 10. seo.php            Title/meta description/canonical/JSON-LD schema
 * 11. content-loader.php Content JSON file reader for location/service pages
 * 12. cli.php            `wp lvjcb provision` — WP-CLI only, no-ops otherwise
 *
 * config.php loads first because every other module — colors, helpers,
 * even enqueue's homepage detection — reads business data through
 * lvjcb_get_config() rather than a hardcoded constant. To build a new
 * site from this theme, only inc/business-config.php changes; nothing
 * else in this list does.
 */

require_once LVJCB_INC_DIR . '/config.php';
require_once LVJCB_INC_DIR . '/colors.php';
require_once LVJCB_INC_DIR . '/setup.php';
require_once LVJCB_INC_DIR . '/enqueue.php';
require_once LVJCB_INC_DIR . '/helpers.php';
require_once LVJCB_INC_DIR . '/icons.php';
require_once LVJCB_INC_DIR . '/slider.php';
require_once LVJCB_INC_DIR . '/security.php';
require_once LVJCB_INC_DIR . '/performance.php';
require_once LVJCB_INC_DIR . '/seo.php';
require_once LVJCB_INC_DIR . '/content-loader.php';
require_once LVJCB_INC_DIR . '/cli.php';
