<?php
/**
 * Template Name: LVJCB Service Areas Hub Page
 *
 * The parent page for every individual location page (e.g.
 * /service-areas/henderson/) — lists all cities and links to each.
 *
 * @package LVJCB
 */

get_header();

$service_areas = lvjcb_get_config( 'service_areas' );
?>

<main id="lvjcb-main">

	<?php get_template_part( 'template-parts/components/hero', null, array(
		'heading'     => $service_areas['heading'],
		'description' => '',
		'image_id'    => 0,
	) ); ?>

	<?php get_template_part( 'template-parts/components/trust-strip' ); ?>

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

	<?php get_template_part( 'template-parts/sections/cta-banner', null, lvjcb_get_cta_banner_args( 'areas-hub', 'mid_page' ) ); ?>

	<?php get_template_part( 'template-parts/sections/contact-information', null, lvjcb_get_contact_args() ); ?>

</main>

<?php get_template_part( 'template-parts/sections/footer' ); ?>
