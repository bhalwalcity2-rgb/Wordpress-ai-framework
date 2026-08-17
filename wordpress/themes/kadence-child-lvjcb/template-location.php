<?php
/**
 * Template Name: Location Page
 *
 * Same section layout as the homepage, localized with the city name.
 * Matched to the service_areas config entry via the page slug.
 *
 * @package LVJCB
 */

get_header();

$page_slug      = get_post_field( 'post_name', get_the_ID() );
$service_areas  = lvjcb_get_config( 'service_areas' );
$services       = lvjcb_get_config( 'services' );
$how_it_works   = lvjcb_get_config( 'how_it_works' );
$why_choose_us  = lvjcb_get_config( 'why_choose_us' );
$vehicles       = lvjcb_get_config( 'vehicles' );
$testimonials   = lvjcb_get_config( 'testimonials' );
$faq            = lvjcb_get_config( 'faq' );

$location = null;
foreach ( $service_areas['items'] as $area ) {
	if ( $area['slug'] === $page_slug ) {
		$location = $area;
		break;
	}
}

if ( ! $location ) {
	get_template_part( '404' );
	return;
}

$city  = $location['city'];
$state = $location['state'];
$intro = $location['intro'];

$hero = lvjcb_get_config( 'hero' );
?>

<main id="lvjcb-main">

	<?php get_template_part( 'template-parts/components/hero', null, array(
		'heading'     => "We Buy Junk Cars \u{2014}in {$city}, Get Cash Today",
		'description' => $intro,
		'image_id'    => lvjcb_get_attachment_id_by_slug( $hero['image_slug'] ?? '' ),
	) ); ?>

	<?php get_template_part( 'template-parts/components/trust-strip' ); ?>

	<?php get_template_part( 'template-parts/sections/what-we-buy', null, array(
		'eyebrow' => $services['eyebrow'],
		'heading' => "Any vehicle, any condition in {$city}",
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

	<?php get_template_part( 'template-parts/sections/cta-banner', null, lvjcb_get_cta_banner_args( $page_slug . '-mid-page', 'mid_page' ) ); ?>

	<?php get_template_part( 'template-parts/sections/why-choose-us', null, array(
		'eyebrow' => $why_choose_us['eyebrow'],
		'heading' => "Why {$city} residents choose us",
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

	<?php
	$other_areas = array_filter( $service_areas['items'], function ( $area ) use ( $page_slug ) {
		return $area['slug'] !== $page_slug;
	} );
	?>
	<?php get_template_part( 'template-parts/sections/service-areas', null, array(
		'eyebrow'   => $service_areas['eyebrow'],
		'heading'   => 'We also serve these areas',
		'locations' => array_map(
			function ( $area ) {
				return array(
					'city'  => $area['city'],
					'state' => $area['state'],
					'url'   => home_url( '/service-areas/' . $area['slug'] . '/' ),
				);
			},
			array_values( $other_areas )
		),
	) ); ?>

	<?php get_template_part( 'template-parts/sections/faq', null, array(
		'eyebrow' => $faq['eyebrow'],
		'heading' => $faq['heading'],
		'items'   => $faq['items'],
	) ); ?>

	<?php get_template_part( 'template-parts/sections/cta-banner', null, lvjcb_get_cta_banner_args( $page_slug . '-late-page', 'late_page' ) ); ?>

	<?php get_template_part( 'template-parts/sections/contact-information', null, lvjcb_get_contact_args() ); ?>

</main>

<?php get_template_part( 'template-parts/sections/footer' ); ?>

<?php get_footer(); ?>
