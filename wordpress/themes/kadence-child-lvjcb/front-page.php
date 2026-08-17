<?php
/**
 * Homepage template.
 *
 * Assembles Header, Hero, and Sections in the exact frozen order from
 * the Master Website Creation Workflow. Every piece of content below
 * comes from lvjcb_get_config() — this file has no business-specific
 * data of its own, so it does not need to change when this theme is
 * reused for a different business. Only inc/business-config.php does.
 *
 * @package LVJCB
 */

get_header();

$hero          = lvjcb_get_config( 'hero' );
$services      = lvjcb_get_config( 'services' );
$how_it_works   = lvjcb_get_config( 'how_it_works' );
$why_choose_us  = lvjcb_get_config( 'why_choose_us' );
$vehicles       = lvjcb_get_config( 'vehicles' );
$testimonials   = lvjcb_get_config( 'testimonials' );
$service_areas  = lvjcb_get_config( 'service_areas' );
$faq            = lvjcb_get_config( 'faq' );
?>

<main id="lvjcb-main">

	<?php get_template_part( 'template-parts/components/hero', null, array(
		'heading'     => $hero['heading'],
		'description' => $hero['description'],
		'image_id'    => lvjcb_get_attachment_id_by_slug( $hero['image_slug'] ?? '' ),
	) ); ?>

	<?php get_template_part( 'template-parts/components/trust-strip' ); ?>

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

	<?php get_template_part( 'template-parts/sections/cta-banner', null, lvjcb_get_cta_banner_args( 'home-mid-page', 'mid_page' ) ); ?>

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

	<?php get_template_part( 'template-parts/sections/cta-banner', null, lvjcb_get_cta_banner_args( 'home-late-page', 'late_page' ) ); ?>

	<?php get_template_part( 'template-parts/sections/contact-information', null, lvjcb_get_contact_args() ); ?>

</main>

<?php get_template_part( 'template-parts/sections/footer' ); ?>

<?php get_footer(); ?>
