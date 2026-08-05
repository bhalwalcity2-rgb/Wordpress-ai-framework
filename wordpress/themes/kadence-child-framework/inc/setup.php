<?php
/**
 * Theme Setup
 *
 * Registers theme supports, navigation menus, editor styles,
 * and custom image sizes for the WAIF Child Theme.
 *
 * @package WAIF_Child
 * @since   0.1.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

add_action( 'after_setup_theme', 'waif_theme_setup' );

/**
 * Configure the child theme.
 *
 * @since 0.1.0
 *
 * @return void
 */
function waif_theme_setup() {

	/**
	 * Theme Supports
	 */
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
			'height'      => 100,
			'width'       => 350,
			'flex-height' => true,
			'flex-width'  => true,
		)
	);

	add_theme_support( 'responsive-embeds' );
	add_theme_support( 'wp-block-styles' );
	add_theme_support( 'align-wide' );
	add_theme_support( 'editor-styles' );
	add_theme_support( 'editor-spacing-sizes' );

	/**
	 * Block Editor Styles
	 */
	add_editor_style( 'assets/css/editor.css' );

	/**
	 * Navigation Menus
	 */
	register_nav_menus(
		array(
			'primary' => esc_html__( 'Primary Menu', 'waif-child' ),
			'footer'  => esc_html__( 'Footer Menu', 'waif-child' ),
		)
	);

	/**
	 * Default Featured Image Size
	 */
	set_post_thumbnail_size( 1200, 675, true );

	/**
	 * Custom Image Sizes
	 */
	add_image_size( 'hero-large', 1920, 1080, true );
	add_image_size( 'service-thumb', 800, 600, true );
	add_image_size( 'team-member', 600, 600, true );
}