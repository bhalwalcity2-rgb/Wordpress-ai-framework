<?php
/**
 * Theme header template (WordPress template hierarchy root file).
 *
 * Distinct from template-parts/components/header.php, which is the
 * visual Header component's actual content. This file only provides
 * the required HTML document skeleton — doctype, <head> with wp_head(),
 * and wp_body_open() — and deliberately does NOT fall back to Kadence's
 * own header.php, so Kadence's default header markup never renders
 * alongside this project's own Header component.
 *
 * @package LVJCB
 */
?><!DOCTYPE html>
<html <?php language_attributes(); ?>>
<head>
	<meta charset="<?php bloginfo( 'charset' ); ?>">
	<meta name="viewport" content="width=device-width, initial-scale=1">
	<?php wp_head(); ?>
</head>
<body <?php body_class(); ?>>
<?php wp_body_open(); ?>
<?php get_template_part( 'template-parts/components/header' ); ?>
