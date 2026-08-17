<?php
/**
 * Template Name: LVJCB Contact Page
 *
 * Contact page — same full layout as the homepage.
 *
 * @package LVJCB
 */

get_header();

$contact       = lvjcb_get_config( 'contact' );
$how_it_works  = lvjcb_get_config( 'how_it_works' );
$why_choose_us = lvjcb_get_config( 'why_choose_us' );
$testimonials  = lvjcb_get_config( 'testimonials' );
$service_areas = lvjcb_get_config( 'service_areas' );
$faq           = lvjcb_get_config( 'faq' );
$hero          = lvjcb_get_config( 'hero' );
?>

<main id="lvjcb-main">

	<?php get_template_part( 'template-parts/components/hero', null, array(
		'heading'     => $contact['heading'],
		'description' => 'Call us, visit us, or request an instant offer online.',
		'image_id'    => lvjcb_get_attachment_id_by_slug( $hero['image_slug'] ?? '' ),
	) ); ?>

	<?php get_template_part( 'template-parts/components/trust-strip' ); ?>

	<?php get_template_part( 'template-parts/sections/contact-information', null, lvjcb_get_contact_args() ); ?>

	<?php
	$steps = array();
	foreach ( $how_it_works['steps'] as $index => $step ) {
		$steps[] = array_merge( $step, array( 'step_number' => $index + 1 ) );
	}
	get_template_part( 'template-parts/sections/how-it-works', null, array(
		'eyebrow' => $how_it_works['eyebrow'],
		'heading' => $how_it_works['heading'],
		'intro'   => $how_it_works['intro'],
		'steps'   => $steps,
	) );
	?>

	<?php get_template_part( 'template-parts/sections/cta-banner', null, lvjcb_get_cta_banner_args( 'contact-mid', 'mid_page' ) ); ?>

	<?php get_template_part( 'template-parts/sections/why-choose-us', null, array(
		'eyebrow' => $why_choose_us['eyebrow'],
		'heading' => $why_choose_us['heading'],
		'intro'   => $why_choose_us['intro'],
		'cards'   => $why_choose_us['cards'],
	) ); ?>

	<?php get_template_part( 'template-parts/sections/testimonials', null, array(
		'eyebrow'          => $testimonials['eyebrow'],
		'heading'          => $testimonials['heading'],
		'aggregate_rating' => $testimonials['aggregate_rating'] ?? 0,
		'review_count'     => $testimonials['review_count'] ?? 0,
		'testimonials'     => $testimonials['items'],
	) ); ?>

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

	<?php get_template_part( 'template-parts/sections/faq', null, array(
		'eyebrow' => $faq['eyebrow'],
		'heading' => $faq['heading'],
		'items'   => $faq['items'],
	) ); ?>

	<?php get_template_part( 'template-parts/sections/cta-banner', null, lvjcb_get_cta_banner_args( 'contact-late', 'late_page' ) ); ?>

</main>

<?php get_template_part( 'template-parts/sections/footer' ); ?>

<?php get_footer(); ?>
