<?php
/**
 * Content Figure component.
 *
 * A photo inside a written page's prose. The image is cropped to a fixed
 * aspect ratio rather than trusted at its native size, because stock
 * photography arrives at whatever shape it was shot at and one tall
 * frame in a row of wide ones breaks the page's rhythm.
 *
 * @param array $args {
 *     @type int    $image_id Attachment ID.
 *     @type string $alt      Alt text describing the photo.
 *     @type string $caption  Optional visible caption.
 *     @type string $modifier Optional shape modifier, e.g. 'wide'.
 * }
 */

$image_id = isset( $args['image_id'] ) ? (int) $args['image_id'] : 0;

if ( ! $image_id ) {
	return;
}

$caption  = $args['caption'] ?? '';
$modifier = $args['modifier'] ?? '';

$classes = 'lvjcb-content-figure';
if ( $modifier ) {
	$classes .= ' lvjcb-content-figure--' . sanitize_html_class( $modifier );
}
?>
<figure class="<?php echo esc_attr( $classes ); ?>">

	<?php
	echo wp_get_attachment_image(
		$image_id,
		'large',
		false,
		array(
			'class'    => 'lvjcb-content-figure__image',
			'alt'      => $args['alt'] ?? '',
			'loading'  => 'lazy',
			'decoding' => 'async',
		)
	);
	?>

	<?php if ( $caption ) : ?>
		<figcaption class="lvjcb-content-figure__caption"><?php echo esc_html( $caption ); ?></figcaption>
	<?php endif; ?>

</figure>
