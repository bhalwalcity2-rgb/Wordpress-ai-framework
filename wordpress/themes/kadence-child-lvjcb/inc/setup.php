<?php
/**
 * Theme Setup
 *
 * Registers theme supports, navigation menus, and custom image sizes
 * for the Las Vegas Junk Car Buyers child theme.
 *
 * @package LVJCB
 * @since   0.1.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

add_action( 'after_setup_theme', 'lvjcb_theme_setup' );

/**
 * Configure the child theme.
 *
 * @since 0.1.0
 *
 * @return void
 */
function lvjcb_theme_setup() {

	add_theme_support( 'title-tag' );
	add_theme_support( 'post-thumbnails' );
	add_theme_support( 'automatic-feed-links' );

	add_theme_support(
		'html5',
		array(
			'search-form',
			'comment-form',
			'comment-list',
			'gallery',
			'caption',
			'style',
			'script',
		)
	);

	add_theme_support(
		'custom-logo',
		array(
			'height'      => 44,
			'width'       => 220,
			'flex-height' => true,
			'flex-width'  => true,
		)
	);

	add_theme_support( 'responsive-embeds' );
	add_theme_support( 'wp-block-styles' );
	add_theme_support( 'align-wide' );
	add_theme_support( 'editor-styles' );

	register_nav_menus(
		array(
			'primary' => esc_html__( 'Primary Menu', 'lvjcb' ),
			'footer'  => esc_html__( 'Footer Menu', 'lvjcb' ),
		)
	);

	/**
	 * Registered image sizes.
	 *
	 * lvjcb-vehicle-card matches Component 06's frozen 4:3 contract —
	 * flagged as an outstanding dependency since that component's
	 * original build; registered here to finally close that gap.
	 */
	add_image_size( 'lvjcb-hero', 1600, 1200, true );
	add_image_size( 'lvjcb-vehicle-card', 800, 600, true );
}
