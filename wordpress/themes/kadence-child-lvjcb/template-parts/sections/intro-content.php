<?php
/**
 * "Intro Content" section. Two-column layout with text on the left
 * and an optional image on the right.
 *
 * @param array $args {
 *     @type string   $id         Optional unique identifier for this instance.
 *     @type string   $eyebrow    Optional eyebrow label.
 *     @type string   $heading    Section heading text.
 *     @type string[] $paragraphs Array of paragraph strings.
 *     @type int      $image_id   Optional attachment ID for the right-side image.
 *     @type string   $image_alt  Optional alt text for the image.
 * }
 */
$paragraphs = $args['paragraphs'] ?? array();

if ( empty( $paragraphs ) ) {
	return;
}

$section_id = sanitize_title( $args['id'] ?? '' );
if ( '' === $section_id ) {
	$section_id = wp_unique_id( 'intro-content-' );
}
$heading_id = $section_id . '-heading';

$eyebrow  = $args['eyebrow'] ?? '';
$heading  = $args['heading'] ?? '';
$image_id = isset( $args['image_id'] ) ? (int) $args['image_id'] : 0;
$has_image = (bool) $image_id;
?>
<section class="lvjcb-section lvjcb-intro-content<?php echo $has_image ? ' lvjcb-intro-content--has-image' : ''; ?>" aria-labelledby="<?php echo esc_attr( $heading_id ); ?>">
	<div class="lvjcb-section__container lvjcb-intro-content__layout">

		<div class="lvjcb-intro-content__text">
			<?php if ( $eyebrow ) : ?>
				<p class="lvjcb-section__eyebrow"><?php echo esc_html( $eyebrow ); ?></p>
			<?php endif; ?>

			<h2 id="<?php echo esc_attr( $heading_id ); ?>" class="lvjcb-section__heading">
				<?php echo esc_html( $heading ); ?>
			</h2>

			<div class="lvjcb-intro-content__body">
				<?php
				/*
				 * Escape first, then linkify — so the only markup that can
				 * reach the page is the anchors the helpers added, and
				 * $linked is shared so each city links once per section
				 * rather than once per paragraph.
				 */
				$linked = array();
				foreach ( $paragraphs as $paragraph ) :
					$body = lvjcb_autolink_locations( esc_html( $paragraph ), $linked );
					$body = lvjcb_linkify_phone( $body );
					?>
					<p><?php echo wp_kses( $body, lvjcb_allowed_inline_html() ); ?></p>
				<?php endforeach; ?>
			</div>
		</div>

		<?php if ( $has_image ) : ?>
			<div class="lvjcb-intro-content__media">
				<?php
				echo wp_get_attachment_image(
					$image_id,
					'large',
					false,
					array(
						'class'    => 'lvjcb-intro-content__image',
						'loading'  => 'lazy',
						'decoding' => 'async',
					)
				);
				?>
			</div>
		<?php endif; ?>

	</div>
</section>
