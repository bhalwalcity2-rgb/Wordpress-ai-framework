<?php
/**
 * Template Name: LVJCB About Page
 *
 * Simple body-copy page — no existing card/grid component fits a single
 * paragraph of content, so this reuses the shared .lvjcb-section /
 * .lvjcb-section__container / .lvjcb-section__heading classes directly
 * (the same pattern CTA Banner and Contact Information already use for
 * markup that doesn't mount a component), rather than inventing a new
 * component for one paragraph.
 *
 * @package LVJCB
 */

get_header();

$about   = lvjcb_get_config( 'about' );
$why     = lvjcb_get_config( 'why_choose_us' );
?>

<main id="lvjcb-main">

	<?php get_template_part( 'template-parts/components/hero', null, array(
		'heading'     => $about['heading'],
		'description' => '',
		'image_id'    => 0,
	) ); ?>

	<?php get_template_part( 'template-parts/components/trust-strip' ); ?>

	<section class="lvjcb-section lvjcb-section--white" aria-labelledby="lvjcb-about-heading">
		<div class="lvjcb-section__container">
			<?php if ( $about['eyebrow'] ) : ?>
				<p class="lvjcb-section__eyebrow"><?php echo esc_html( $about['eyebrow'] ); ?></p>
			<?php endif; ?>
			<h2 id="lvjcb-about-heading" class="lvjcb-section__heading"><?php echo esc_html( $about['heading'] ); ?></h2>
			<p class="lvjcb-section__intro"><?php echo esc_html( $about['body'] ); ?></p>
		</div>
	</section>

	<?php get_template_part( 'template-parts/sections/why-choose-us', null, array(
		'eyebrow' => $why['eyebrow'],
		'heading' => $why['heading'],
		'intro'   => $why['intro'],
		'cards'   => $why['cards'],
	) ); ?>

	<?php get_template_part( 'template-parts/sections/cta-banner', null, lvjcb_get_cta_banner_args( 'about', 'mid_page' ) ); ?>

	<?php get_template_part( 'template-parts/sections/contact-information', null, lvjcb_get_contact_args() ); ?>

</main>

<?php get_template_part( 'template-parts/sections/footer' ); ?>
