<?php
/**
 * "Service Areas" section. Assembles Component 05 (Location Card).
 *
 * @param array $args {
 *     @type string $id        Optional unique identifier for this instance.
 *     @type string $eyebrow   Optional eyebrow label.
 *     @type string $heading   Section heading text.
 *     @type string $intro     Optional supporting paragraph text.
 *     @type array  $locations List of location data passed directly to location-card.php.
 * }
 */
$locations = $args['locations'] ?? array();

if ( empty( $locations ) ) {
	return;
}

$section_id = sanitize_title( $args['id'] ?? '' );
if ( '' === $section_id ) {
	$section_id = wp_unique_id( 'service-areas-' );
}
$heading_id = $section_id . '-heading';

$eyebrow = $args['eyebrow'] ?? '';
$heading = $args['heading'] ?? '';
$intro   = $args['intro'] ?? '';
?>
<section class="lvjcb-section lvjcb-service-areas" aria-labelledby="<?php echo esc_attr( $heading_id ); ?>">
	<div class="lvjcb-section__container">

		<?php if ( $eyebrow ) : ?>
			<p class="lvjcb-section__eyebrow"><?php echo esc_html( $eyebrow ); ?></p>
		<?php endif; ?>

		<h2 id="<?php echo esc_attr( $heading_id ); ?>" class="lvjcb-section__heading">
			<?php echo esc_html( $heading ); ?>
		</h2>

		<?php if ( $intro ) : ?>
			<p class="lvjcb-section__intro"><?php echo esc_html( $intro ); ?></p>
		<?php endif; ?>

		<div class="lvjcb-section__grid lvjcb-section__grid--4up">
			<?php foreach ( $locations as $location ) : ?>
				<?php get_template_part( 'template-parts/components/location-card', null, $location ); ?>
			<?php endforeach; ?>
		</div>

	</div>
</section>
