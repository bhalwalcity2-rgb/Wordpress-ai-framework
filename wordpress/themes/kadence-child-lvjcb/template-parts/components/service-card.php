<?php
/**
 * Service Card component.
 *
 * @param array $args {
 *     @type string $icon        Icon name (matches an inline sprite symbol).
 *     @type string $heading     Card heading text.
 *     @type string $description Card description text.
 *     @type string $url         Destination URL.
 * }
 */
$icon        = $args['icon'] ?? '';
$heading     = $args['heading'] ?? '';
$description = $args['description'] ?? '';
$url         = $args['url'] ?? '';
?>
<article class="lvjcb-service-card">

	<?php if ( $icon ) : ?>
		<div class="lvjcb-service-card__icon-wrap">
			<?php echo lvjcb_icon( $icon, array( 'class' => 'lvjcb-service-card__icon', 'size' => 24 ) ); ?>
		</div>
	<?php endif; ?>

	<h3 class="lvjcb-service-card__heading">
		<?php if ( $url ) : ?>
			<a href="<?php echo esc_url( $url ); ?>" class="lvjcb-service-card__link"><?php echo esc_html( $heading ); ?></a>
		<?php else : ?>
			<?php echo esc_html( $heading ); ?>
		<?php endif; ?>
	</h3>

	<p class="lvjcb-service-card__description"><?php echo esc_html( $description ); ?></p>

	<?php if ( $url ) : ?>
		<?php echo lvjcb_icon( 'arrow', array( 'class' => 'lvjcb-service-card__arrow', 'size' => 18 ) ); ?>
	<?php endif; ?>

</article>
