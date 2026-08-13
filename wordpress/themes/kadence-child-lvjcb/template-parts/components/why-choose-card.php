<?php
/**
 * Why Choose Us Card component. No wrapper divs, no link, no hover —
 * non-interactive by design.
 *
 * @param array $args {
 *     @type string $icon        Optional icon name.
 *     @type string $heading     Card heading text.
 *     @type string $description Card description text.
 * }
 */
$icon        = $args['icon'] ?? '';
$heading     = $args['heading'] ?? '';
$description = $args['description'] ?? '';
?>
<article class="lvjcb-why-card">
	<?php if ( $icon ) : ?>
		<?php echo lvjcb_icon( $icon, array( 'class' => 'lvjcb-why-card__icon', 'size' => 24 ) ); ?>
	<?php endif; ?>
	<h3 class="lvjcb-why-card__heading"><?php echo esc_html( $heading ); ?></h3>
	<p class="lvjcb-why-card__description"><?php echo esc_html( $description ); ?></p>
</article>
