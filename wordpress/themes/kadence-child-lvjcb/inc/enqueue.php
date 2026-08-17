<?php
/**
 * Asset Enqueueing
 *
 * Registers and enqueues frontend assets. Each component/section owns
 * its own CSS/JS file (per the Component/Section Architecture frozen
 * throughout this project); this file is the single registry of which
 * files exist and get loaded, so no template ever enqueues its own
 * assets directly.
 *
 * Extend LVJCB_COMPONENT_ASSETS / LVJCB_SECTION_ASSETS as each new
 * component/section is built — do not add ad hoc enqueue calls
 * elsewhere in the theme.
 *
 * @package LVJCB
 * @since   0.1.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Registry of component assets.
 *
 * Key = file slug (matches template-parts/components/{slug}.php).
 * Value = which of css/js exist for that component.
 */
if ( ! defined( 'LVJCB_COMPONENT_ASSETS' ) ) {
	define(
		'LVJCB_COMPONENT_ASSETS',
		array(
			'header'           => array( 'css' => true, 'js' => true ),
			'hero'             => array( 'css' => true, 'js' => true ),
			'trust-strip'      => array( 'css' => true, 'js' => false ),
			'service-card'     => array( 'css' => true, 'js' => false ),
			'location-card'    => array( 'css' => true, 'js' => false ),
			'vehicle-card'     => array( 'css' => true, 'js' => false ),
			'testimonial-card' => array( 'css' => true, 'js' => false ),
			'why-choose-card'  => array( 'css' => true, 'js' => false ),
			'step-card'        => array( 'css' => true, 'js' => false ),
			'faq-item'         => array( 'css' => true, 'js' => true ),
			'quote-form'       => array( 'css' => true, 'js' => true ),
			'slider'           => array( 'css' => true, 'js' => true ),
		)
	);
}

/**
 * Registry of section assets.
 *
 * Same shape as LVJCB_COMPONENT_ASSETS, for template-parts/sections/{slug}.php.
 */
if ( ! defined( 'LVJCB_SECTION_ASSETS' ) ) {
	define(
		'LVJCB_SECTION_ASSETS',
		array(
			'what-we-buy'                  => array( 'css' => false, 'js' => false ),
			'why-choose-us'                => array( 'css' => false, 'js' => false ),
			'how-it-works'                 => array( 'css' => true, 'js' => false ),
			'recently-purchased-vehicles'  => array( 'css' => false, 'js' => false ),
			'testimonials'                 => array( 'css' => false, 'js' => false ),
			'service-areas'                => array( 'css' => false, 'js' => false ),
			'faq'                          => array( 'css' => true, 'js' => false ),
			'cta-banner'                   => array( 'css' => true, 'js' => false ),
			'contact-information'          => array( 'css' => true, 'js' => false ),
			'instant-offer'                => array( 'css' => true, 'js' => false ),
			'footer'                       => array( 'css' => true, 'js' => false ),
			// 'get-cash-offer' is registered once built — not part of
			// the current homepage assembly (see Stage 6 order).
		)
	);
}

add_action( 'wp_enqueue_scripts', 'lvjcb_enqueue_assets', 20 );

/**
 * Enqueue frontend assets.
 *
 * Load order: design tokens first (every component CSS depends on the
 * custom properties it defines), then each registered component/section
 * file. All handles depend on 'lvjcb-tokens' so token values are always
 * available regardless of enqueue order changes later.
 *
 * @since 0.1.0
 *
 * @return void
 */
function lvjcb_enqueue_assets() {

	wp_enqueue_style(
		'lvjcb-tokens',
		lvjcb_get_asset_uri( 'css/tokens.css' ),
		array(),
		lvjcb_asset_version( 'assets/css/tokens.css' )
	);

	wp_enqueue_style(
		'lvjcb-buttons',
		lvjcb_get_asset_uri( 'css/buttons.css' ),
		array( 'lvjcb-tokens' ),
		lvjcb_asset_version( 'assets/css/buttons.css' )
	);

	wp_enqueue_style(
		'lvjcb-section-base',
		lvjcb_get_asset_uri( 'css/sections/section-base.css' ),
		array( 'lvjcb-tokens' ),
		lvjcb_asset_version( 'assets/css/sections/section-base.css' )
	);

	foreach ( LVJCB_COMPONENT_ASSETS as $slug => $files ) {
		lvjcb_enqueue_registered_asset( 'components', $slug, $files );
	}

	foreach ( LVJCB_SECTION_ASSETS as $slug => $files ) {
		lvjcb_enqueue_registered_asset( 'sections', $slug, $files );
	}
}

/**
 * Enqueue one registered component/section's CSS and/or JS.
 *
 * @since 0.1.0
 *
 * @param string $group 'components' or 'sections'.
 * @param string $slug  File slug.
 * @param array  $files array{css?: bool, js?: bool}.
 * @return void
 */
function lvjcb_enqueue_registered_asset( $group, $slug, $files ) {

	$handle = 'lvjcb-' . $slug;

	if ( ! empty( $files['css'] ) ) {
		$relative = 'assets/css/' . $group . '/' . $slug . '.css';

		wp_enqueue_style(
			$handle,
			lvjcb_get_asset_uri( 'css/' . $group . '/' . $slug . '.css' ),
			array( 'lvjcb-tokens' ),
			lvjcb_asset_version( $relative )
		);
	}

	if ( ! empty( $files['js'] ) ) {
		$relative = 'assets/js/' . $group . '/' . $slug . '.js';

		wp_enqueue_script(
			$handle,
			lvjcb_get_asset_uri( 'js/' . $group . '/' . $slug . '.js' ),
			array(),
			lvjcb_asset_version( $relative ),
			true
		);
	}
}

/**
 * -----------------------------------------------------------------------
 * Hero Image Preload — Homepage Only
 * -----------------------------------------------------------------------
 *
 * Per the locked asset-loading strategy: the Hero image uses
 * fetchpriority="high" and is preloaded only on the homepage. The image
 * URL is resolved via the 'lvjcb_hero_image_url' filter so this stays
 * correct once real Hero content is wired up on the front page; until
 * then it safely no-ops rather than preloading a placeholder.
 */

add_action( 'wp_head', 'lvjcb_preload_hero_image', 1 );

/**
 * Output a preload hint for the Hero image on the homepage only.
 *
 * @since 0.1.0
 *
 * @return void
 */
function lvjcb_preload_hero_image() {

	if ( ! is_front_page() ) {
		return;
	}

	$hero_image_url = apply_filters( 'lvjcb_hero_image_url', '' );

	if ( ! $hero_image_url ) {
		return;
	}

	printf(
		'<link rel="preload" as="image" href="%s" fetchpriority="high">' . "\n",
		esc_url( $hero_image_url )
	);
}
