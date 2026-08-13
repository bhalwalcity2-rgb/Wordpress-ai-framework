<?php
/**
 * Template Name: LVJCB FAQ Page
 *
 * @package LVJCB
 */

get_header();

$faq = lvjcb_get_config( 'faq' );
?>

<main id="lvjcb-main">

	<?php get_template_part( 'template-parts/components/hero', null, array(
		'heading'     => $faq['heading'],
		'description' => '',
		'image_id'    => 0,
	) ); ?>

	<?php get_template_part( 'template-parts/components/trust-strip' ); ?>

	<?php get_template_part( 'template-parts/sections/faq', null, array(
		'eyebrow' => $faq['eyebrow'],
		'heading' => $faq['heading'],
		'items'   => $faq['items'],
	) ); ?>

	<?php get_template_part( 'template-parts/sections/cta-banner', null, lvjcb_get_cta_banner_args( 'faq-page', 'mid_page' ) ); ?>

	<?php get_template_part( 'template-parts/sections/contact-information', null, lvjcb_get_contact_args() ); ?>

</main>

<?php get_template_part( 'template-parts/sections/footer' ); ?>
