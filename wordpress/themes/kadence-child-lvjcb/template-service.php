<?php
/**
 * Template Name: LVJCB Service Page
 *
 * Renders a service page using structured content from
 * content/services/{slug}.json when available, falling back to the
 * config intro when no content file exists.
 *
 * @package LVJCB
 */

get_header();

$slug = get_post_meta( get_the_ID(), 'lvjcb_service_slug', true );
if ( ! $slug ) {
	$slug = get_post_field( 'post_name', get_the_ID() );
}

$page_content  = lvjcb_get_service_content( $slug );
$service_areas = lvjcb_get_config( 'service_areas' );
$why_choose_us = lvjcb_get_config( 'why_choose_us' );
$faq_config    = lvjcb_get_config( 'faq' );
$links         = lvjcb_build_service_links( $slug );
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

	<?php get_template_part( 'template-parts/sections/why-choose-us', null, array(
		'eyebrow' => $why_choose_us['eyebrow'],
		'heading' => $why_choose_us['heading'],
		'intro'   => $why_choose_us['intro'],
		'cards'   => $why_choose_us['cards'],
	) ); ?>

	<?php get_template_part( 'template-parts/sections/cta-banner', null, lvjcb_get_cta_banner_args( 'service-' . $slug, 'mid_page' ) ); ?>

	<?php get_template_part( 'template-parts/sections/service-areas', null, array(
		'eyebrow'   => $service_areas['eyebrow'],
		'heading'   => $service_areas['heading'],
		'locations' => array_map(
			function ( $location ) {
				return array(
					'city'  => $location['city'],
					'state' => $location['state'],
					'url'   => home_url( '/service-areas/' . $location['slug'] . '/' ),
				);
			},
			$service_areas['items']
		),
	) ); ?>

	<?php
	// Page-specific FAQ from content file, or global FAQ as fallback.
	$faq_items = ! empty( $page_content['faq'] ) ? $page_content['faq'] : $faq_config['items'];

	get_template_part( 'template-parts/sections/faq', null, array(
		'eyebrow' => $faq_config['eyebrow'],
		'heading' => ! empty( $page_content['faq'] )
			? sprintf( '%s — FAQ', $page_content['hero_heading'] )
			: $faq_config['heading'],
		'items'   => $faq_items,
	) );
	?>

	<?php get_template_part( 'template-parts/sections/testimonials', null, array(
		'eyebrow'      => lvjcb_get_config( 'testimonials' )['eyebrow'],
		'heading'      => lvjcb_get_config( 'testimonials' )['heading'],
		'testimonials' => lvjcb_get_config( 'testimonials' )['items'],
	) ); ?>

	<?php
	// Internal links — other services and all locations.
	get_template_part( 'template-parts/sections/internal-links', null, array(
		'locations' => $links['locations'],
		'services'  => $links['services'],
		'context'   => 'service',
	) );
	?>

	<?php get_template_part( 'template-parts/sections/cta-banner', null, lvjcb_get_cta_banner_args( 'service-' . $slug . '-late', 'late_page' ) ); ?>

	<?php get_template_part( 'template-parts/sections/contact-information', null, lvjcb_get_contact_args() ); ?>

</main>

<?php get_template_part( 'template-parts/sections/footer' ); ?>
