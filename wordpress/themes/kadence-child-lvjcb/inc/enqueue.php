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
			'hero'             => array( 'css' => true, 'js' => false ),
			'trust-strip'      => array( 'css' => true, 'js' => false ),
			'sticky-cta'       => array( 'css' => true, 'js' => false ),
			'service-card'     => array( 'css' => true, 'js' => false ),
			'location-card'    => array( 'css' => true, 'js' => false ),
			'vehicle-card'     => array( 'css' => true, 'js' => false ),
			'testimonial-card' => array( 'css' => true, 'js' => false ),
			'why-choose-card'  => array( 'css' => true, 'js' => false ),
			'step-card'        => array( 'css' => true, 'js' => false ),
			'content-figure'   => array( 'css' => true, 'js' => false ),
			'faq-item'         => array( 'css' => true, 'js' => true ),
			// 'quote-form' is built but not mounted: the hero slot that held it
			// was unreachable (its reveal trigger existed in no template), so
			// shipping its CSS and JS site-wide bought nothing. Re-register it
			// here the moment a template actually renders the form.
			// 'quote-form'    => array( 'css' => true, 'js' => true ),
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
			'intro-content'                => array( 'css' => true, 'js' => false ),
			'rich-content'                 => array( 'css' => true, 'js' => false ),
			'what-we-buy'                  => array( 'css' => false, 'js' => false ),
			'why-choose-us'                => array( 'css' => false, 'js' => false ),
			'how-it-works'                 => array( 'css' => true, 'js' => false ),
			'recently-purchased-vehicles'  => array( 'css' => false, 'js' => false ),
			'testimonials'                 => array( 'css' => false, 'js' => false ),
			'service-areas'                => array( 'css' => false, 'js' => false ),
			'faq'                          => array( 'css' => true, 'js' => false ),
			'cta-banner'                   => array( 'css' => true, 'js' => false ),
			'contact-information'          => array( 'css' => true, 'js' => false ),
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
add_action( 'wp_head', 'lvjcb_favicon', 2 );
add_action( 'wp_footer', 'lvjcb_peddle_embed_script', 99 );

/**
 * Resolve the attachment ID of the Hero image for the current page.
 *
 * Mirrors the lookup in written-page.php: a page prefers an image named for
 * its own slug and falls back to the shared homepage photo.
 *
 * @since 0.4.1
 *
 * @return int Attachment ID, or 0 when there is none.
 */
function lvjcb_get_hero_attachment_id() {

	$slug = is_front_page() ? '' : (string) get_post_field( 'post_name', get_queried_object_id() );

	if ( $slug ) {
		$id = lvjcb_get_attachment_id_by_slug( 'hero-' . $slug . '-01' );
		if ( $id ) {
			return $id;
		}
	}

	return lvjcb_get_attachment_id_by_slug( lvjcb_get_config( 'hero' )['image_slug'] ?? '' );
}

/**
 * Output a preload hint for the Hero image.
 *
 * Previously this resolved its URL through an 'lvjcb_hero_image_url' filter
 * that nothing ever added, so it read an empty string and returned early on
 * every request — the hint has never actually been emitted. It now resolves
 * the attachment itself and keeps the filter as an override.
 *
 * The hint carries the same srcset and sizes the <img> will carry. Without
 * them the browser preloads one candidate, then the responsive image picks a
 * different one and downloads it again, which costs more than no preload.
 *
 * @since 0.1.0
 *
 * @return void
 */
function lvjcb_preload_hero_image() {

	if ( is_404() || is_search() ) {
		return;
	}

	$image_id = lvjcb_get_hero_attachment_id();

	$hero_image_url = apply_filters(
		'lvjcb_hero_image_url',
		$image_id ? wp_get_attachment_image_url( $image_id, 'large' ) : ''
	);

	if ( ! $hero_image_url ) {
		return;
	}

	$srcset = $image_id ? wp_get_attachment_image_srcset( $image_id, 'large' ) : '';
	$sizes  = '(max-width: 900px) 100vw, 560px';

	printf(
		'<link rel="preload" as="image" href="%s"%s%s fetchpriority="high">' . "\n",
		esc_url( $hero_image_url ),
		$srcset ? sprintf( ' imagesrcset="%s"', esc_attr( $srcset ) ) : '',
		$srcset ? sprintf( ' imagesizes="%s"', esc_attr( $sizes ) ) : ''
	);
}

/**
 * Output the theme favicon.
 *
 * @since 0.4.0
 *
 * @return void
 */
function lvjcb_favicon() {

	printf(
		'<link rel="icon" href="%s" type="image/svg+xml">' . "\n",
		esc_url( lvjcb_get_asset_uri( 'images/favicon.svg' ) )
	);
}

/**
 * Output the Peddle Publisher Embed script in the footer.
 *
 * @since 0.4.0
 *
 * @return void
 */
function lvjcb_peddle_embed_script() {

	$publisher_id = lvjcb_get_config( 'peddle_publisher_id' );

	if ( ! $publisher_id ) {
		return;
	}
	?>
	<script>
		PeddlePublisherEmbedConfig={publisherID:<?php echo wp_json_encode( $publisher_id ); ?>},function(){if("function"!=typeof window.PeddlePublisherEmbed){if(!window.PeddlePublisherEmbedConfig||!window.PeddlePublisherEmbedConfig.publisherID)throw new Error("Unable to bootstrap Peddle Publisher Embed, make sure to set PeddlePublisherEmbedConfig.publisherID");const e=(d,i)=>{e.queue.push({operation:d,options:i})};e.queue=[],window.PeddlePublisherEmbed=e;const d=()=>{const e=document.createElement("script");e.type="text/javascript",e.async=!0,e.src="https://publisher-embed.peddle.com/api/v1/embed/"+PeddlePublisherEmbedConfig.publisherID;const d=document.getElementsByTagName("script")[0];d.parentNode.insertBefore(e,d)};"complete"===document.readyState?d():window.addEventListener("load",d,!1)}}();window.PeddlePublisherEmbed('boot');
	</script>
	<?php
}
