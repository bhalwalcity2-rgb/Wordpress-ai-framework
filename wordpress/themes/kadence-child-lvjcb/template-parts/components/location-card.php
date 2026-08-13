<?php
/**
 * Location Card component.
 *
 * @param array $args {
 *     @type string $city        City name.
 *     @type string $state       State abbreviation.
 *     @type string $description Optional short description.
 *     @type string $url         Optional destination URL.
 * }
 */
$city        = $args['city'] ?? '';
$state       = $args['state'] ?? '';
$description = $args['description'] ?? '';
$url         = $args['url'] ?? '';

$has_url    = ! empty( $url );
$label_text = trim( $city . ( $state ? ', ' . $state : '' ) );
?>
<article class="lvjcb-location-card">
	<h3 class="lvjcb-location-card__heading">
		<?php if ( $has_url ) : ?>
			<a href="<?php echo esc_url( $url ); ?>" class="lvjcb-location-card__link"><?php echo esc_html( $label_text ); ?></a>
		<?php else : ?>
			<?php echo esc_html( $label_text ); ?>
		<?php endif; ?>
	</h3>

	<?php if ( $description ) : ?>
		<p class="lvjcb-location-card__description"><?php echo esc_html( $description ); ?></p>
	<?php endif; ?>

	<?php if ( $has_url ) : ?>
		<?php echo lvjcb_icon( 'arrow', array( 'class' => 'lvjcb-location-card__arrow', 'size' => 16 ) ); ?>
	<?php endif; ?>
</article>
