<?php
/**
 * Template Name: LVJCB Service Page
 *
 * Individual service page (e.g. /cash-for-junk-cars/sedans/) — same
 * full layout as the homepage with service-specific hero text.
 *
 * @package LVJCB
 */

get_header();

$page_slug     = get_post_field( 'post_name', get_the_ID() );
$services      = lvjcb_get_config( 'services' );
$how_it_works  = lvjcb_get_config( 'how_it_works' );
$why_choose_us = lvjcb_get_config( 'why_choose_us' );
$vehicles      = lvjcb_get_config( 'vehicles' );
$testimonials  = lvjcb_get_config( 'testimonials' );
$service_areas = lvjcb_get_config( 'service_areas' );
$faq           = lvjcb_get_config( 'faq' );
$hero          = lvjcb_get_config( 'hero' );

$service = null;
foreach ( $services['cards'] as $card ) {
	if ( $card['slug'] === $page_slug ) {
		$service = $card;
		break;
	}
}

$service_heading = $service ? $service['heading'] : get_the_title();
$service_intro   = $service['intro'] ?? $service['description'] ?? '';
?>

<main id="lvjcb-main">

	<?php get_template_part( 'template-parts/components/hero', null, array(
		'heading'     => "Cash for {$service_heading} \u{2014}in Las Vegas",
		'description' => $service_intro,
		'image_id'    => lvjcb_get_attachment_id_by_slug( $hero['image_slug'] ?? '' ),
	) ); ?>

	<?php get_template_part( 'template-parts/components/trust-strip' ); ?>

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

	<?php get_template_part( 'template-parts/sections/cta-banner', null, lvjcb_get_cta_banner_args( 'service-' . $page_slug . '-mid', 'mid_page' ) ); ?>

	<?php get_template_part( 'template-parts/sections/why-choose-us', null, array(
		'eyebrow' => $why_choose_us['eyebrow'],
		'heading' => $why_choose_us['heading'],
		'intro'   => $why_choose_us['intro'],
		'cards'   => $why_choose_us['cards'],
	) ); ?>

	<?php get_template_part( 'template-parts/sections/recently-purchased-vehicles', null, array(
		'eyebrow'  => $vehicles['eyebrow'],
		'heading'  => $vehicles['heading'],
		'intro'    => $vehicles['intro'],
		'vehicles' => array_map(
			function ( $vehicle ) {
				return array_merge(
					$vehicle,
					array( 'image_id' => lvjcb_get_attachment_id_by_slug( $vehicle['image_slug'] ?? '' ) )
				);
			},
			$vehicles['items']
		),
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

	<?php get_template_part( 'template-parts/sections/cta-banner', null, lvjcb_get_cta_banner_args( 'service-' . $page_slug . '-late', 'late_page' ) ); ?>

	<?php get_template_part( 'template-parts/sections/contact-information', null, lvjcb_get_contact_args() ); ?>

</main>

<?php get_template_part( 'template-parts/sections/footer' ); ?>

<?php get_footer(); ?>
