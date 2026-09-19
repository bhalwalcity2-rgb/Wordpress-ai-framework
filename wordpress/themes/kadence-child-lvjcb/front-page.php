<?php
/**
 * Homepage template.
 *
 * The homepage body is managed in WordPress with Elementor. This shell
 * intentionally owns only the theme header and footer, leaving page
 * content, images, and layouts editable without a theme deployment.
 *
 * @package LVJCB
 */

get_header();
?>

<main id="lvjcb-main">
	<?php
	while ( have_posts() ) {
		the_post();
		the_content();
	}
	?>

</main>

<?php get_template_part( 'template-parts/sections/footer' ); ?>

<?php get_footer(); ?>
