<?php
/**
 * "Intro Content" section. Multi-paragraph long-form text block used
 * for SEO-rich introductions on any page template.
 *
 * @param array $args {
 *     @type string   $id         Optional unique identifier for this instance.
 *     @type string   $eyebrow    Optional eyebrow label.
 *     @type string   $heading    Section heading text.
 *     @type string[] $paragraphs Array of paragraph strings.
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

$eyebrow = $args['eyebrow'] ?? '';
$heading = $args['heading'] ?? '';
?>
<section class="lvjcb-section lvjcb-intro-content" aria-labelledby="<?php echo esc_attr( $heading_id ); ?>">
	<div class="lvjcb-section__container">

		<?php if ( $eyebrow ) : ?>
			<p class="lvjcb-section__eyebrow"><?php echo esc_html( $eyebrow ); ?></p>
		<?php endif; ?>

		<h2 id="<?php echo esc_attr( $heading_id ); ?>" class="lvjcb-section__heading">
			<?php echo esc_html( $heading ); ?>
		</h2>

		<div class="lvjcb-intro-content__body">
			<?php foreach ( $paragraphs as $paragraph ) : ?>
				<p><?php echo esc_html( $paragraph ); ?></p>
			<?php endforeach; ?>
		</div>

	</div>
</section>
