<?php
/**
 * Template Name: LVJCB Services Hub Page
 *
 * The parent page for every individual service page (e.g.
 * /cash-for-junk-cars/sedans/) — lists all services and links to each.
 *
 * @package LVJCB
 */

get_header();

$services = lvjcb_get_config( 'services' );
?>

<main id="lvjcb-main">

	<?php get_template_part( 'template-parts/components/hero', null, array(
		'heading'     => $services['heading'],
		'description' => $services['intro'],
		'image_id'    => 0,
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

	<?php get_template_part( 'template-parts/sections/cta-banner', null, lvjcb_get_cta_banner_args( 'services-hub', 'mid_page' ) ); ?>

	<?php get_template_part( 'template-parts/sections/contact-information', null, lvjcb_get_contact_args() ); ?>

</main>

<?php get_template_part( 'template-parts/sections/footer' ); ?>
