<?php
/**
 * Vehicle Card component.
 *
 * @param array $args {
 *     @type int    $image_id  Attachment ID.
 *     @type string $image_alt Alt text for the vehicle photo.
 *     @type string $vehicle   Vehicle title, e.g. "2011 Honda Accord".
 *     @type string $condition Short condition/location caption.
 *     @type string $url       Optional destination URL.
 * }
 */
$image_id  = isset( $args['image_id'] ) ? (int) $args['image_id'] : 0;
$image_alt = $args['image_alt'] ?? '';
$vehicle   = $args['vehicle'] ?? '';
$condition = $args['condition'] ?? '';
$url       = $args['url'] ?? '';

$has_url    = ! empty( $url );
$card_class = 'lvjcb-vehicle-card' . ( $has_url ? ' lvjcb-vehicle-card--linked' : '' );
?>
<article class="<?php echo esc_attr( $card_class ); ?>">

	<div class="lvjcb-vehicle-card__figure">
		<?php if ( $image_id ) : ?>
			<?php
			echo wp_get_attachment_image(
				$image_id,
				'lvjcb-vehicle-card',
				false,
				array(
					'class'   => 'lvjcb-vehicle-card__image',
					'alt'     => $image_alt,
					'loading' => 'lazy',
					'decoding' => 'async',
					'sizes'   => '(min-width: 1025px) 300px, (min-width: 769px) 50vw, 100vw',
				)
			);
			?>
		<?php endif; ?>
	</div>

	<div class="lvjcb-vehicle-card__caption">
		<?php if ( $has_url ) : ?>
			<a href="<?php echo esc_url( $url ); ?>" class="lvjcb-vehicle-card__link">
				<span class="lvjcb-vehicle-card__title"><?php echo esc_html( $vehicle ); ?></span>
			</a>
		<?php else : ?>
			<span class="lvjcb-vehicle-card__title"><?php echo esc_html( $vehicle ); ?></span>
		<?php endif; ?>

		<?php if ( $condition ) : ?>
			<span class="lvjcb-vehicle-card__condition"><?php echo esc_html( $condition ); ?></span>
		<?php endif; ?>
	</div>

</article>
