<?php
/**
 * Template Name: LVJCB Contact Page
 *
 * @package LVJCB
 */

get_header();

$contact = lvjcb_get_config( 'contact' );
?>

<main id="lvjcb-main">

	<?php get_template_part( 'template-parts/components/hero', null, array(
		'heading'     => $contact['heading'],
		'description' => '',
		'image_id'    => 0,
	) ); ?>

	<?php get_template_part( 'template-parts/components/trust-strip' ); ?>

	<?php get_template_part( 'template-parts/sections/contact-information', null, lvjcb_get_contact_args() ); ?>

</main>

<?php get_template_part( 'template-parts/sections/footer' ); ?>
