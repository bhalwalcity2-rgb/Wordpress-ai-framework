<?php
/**
 * Template Name: LVJCB Location Page
 *
 * Renders a location page using structured content from
 * content/locations/{slug}.json when available, falling back to the
 * short intro from business-config.php when no content file exists.
 *
 * @package LVJCB
 */

get_header();

$slug = get_post_meta( get_the_ID(), 'lvjcb_location_slug', true );
if ( ! $slug ) {
	$slug = get_post_field( 'post_name', get_the_ID() );
}

$page_content  = lvjcb_get_location_content( $slug );
$services      = lvjcb_get_config( 'services' );
$testimonials  = lvjcb_get_config( 'testimonials' );
$faq_config    = lvjcb_get_config( 'faq' );
$links         = lvjcb_build_location_links( $slug );
?>

<main id="lvjcb-main">

	<?php get_template_part( 'template-parts/components/hero', null, array(
		'heading'     => $page_content['hero_heading'],
		'description' => $page_content['hero_description'],
		'image_id'    => 0,
	) ); ?>

	<?php get_template_part( 'template-parts/components/trust-strip' ); ?>

	<?php
	// Rich content sections from JSON file.
	if ( ! empty( $page_content['sections'] ) ) :
		foreach ( $page_content['sections'] as $section ) :
			get_template_part( 'template-parts/sections/content-block', null, array(
				'heading' => $section['heading'],
				'body'    => $section['body'],
			) );
		endforeach;
	endif;
	?>

	<?php get_template_part( 'template-parts/sections/what-we-buy', null, array(
		'eyebrow' => $services['eyebrow'],
		'heading' => $services['heading'],
		'intro'   => $services['intro'],
		'cards'   => array_map(
			function ( $card ) {
				return array(
					'icon'        => $card['icon'],
					'heading'     => $card['heading'],
					'description' => $card['description'],
					'url'         => home_url( '/cash-for-junk-cars/' . $card['slug'] . '/' ),
				);
			},
			$services['cards']
		),
	) ); ?>

	<?php get_template_part( 'template-parts/sections/cta-banner', null, lvjcb_get_cta_banner_args( 'location-' . $slug, 'mid_page' ) ); ?>

	<?php
	// Page-specific FAQ from content file, or global FAQ as fallback.
	$faq_items = ! empty( $page_content['faq'] ) ? $page_content['faq'] : $faq_config['items'];
	$faq_heading = ! empty( $page_content['faq'] )
		? sprintf( __( 'FAQ About Selling Junk Cars in %s', 'lvjcb' ), $page_content['city'] ?? '' )
		: $faq_config['heading'];

	get_template_part( 'template-parts/sections/faq', null, array(
		'eyebrow' => $faq_config['eyebrow'],
		'heading' => $faq_heading,
		'items'   => $faq_items,
	) );
	?>

	<?php get_template_part( 'template-parts/sections/testimonials', null, array(
		'eyebrow'      => $testimonials['eyebrow'],
		'heading'      => $testimonials['heading'],
		'testimonials' => $testimonials['items'],
	) ); ?>

	<?php
	// Internal links — nearby locations and services.
	get_template_part( 'template-parts/sections/internal-links', null, array(
		'locations' => $links['locations'],
		'services'  => $links['services'],
		'context'   => 'location',
		'city'      => $page_content['city'] ?? '',
	) );
	?>

	<?php get_template_part( 'template-parts/sections/cta-banner', null, lvjcb_get_cta_banner_args( 'location-' . $slug . '-late', 'late_page' ) ); ?>

	<?php get_template_part( 'template-parts/sections/contact-information', null, lvjcb_get_contact_args() ); ?>

</main>

<?php get_template_part( 'template-parts/sections/footer' ); ?>
