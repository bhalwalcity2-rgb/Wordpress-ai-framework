<?php
/**
 * "Recently Purchased Vehicles" section. Assembles the global
 * Slider/Carousel Component wrapping Component 06 (Vehicle Card),
 * unmodified, per the Niche Functionality Library.
 *
 * @param array $args {
 *     @type string $id       Optional unique identifier for this instance.
 *     @type string $eyebrow  Optional eyebrow label.
 *     @type string $heading  Section heading text.
 *     @type string $intro    Optional supporting paragraph text.
 *     @type array  $vehicles List of vehicle data passed directly to
 *                            vehicle-card.php: image_id, image_alt,
 *                            vehicle, condition, url.
 * }
 */
$vehicles = $args['vehicles'] ?? array();

if ( empty( $vehicles ) ) {
	return;
}

$section_id = sanitize_title( $args['id'] ?? '' );
if ( '' === $section_id ) {
	$section_id = wp_unique_id( 'recently-purchased-vehicles-' );
}
$heading_id = $section_id . '-heading';

$eyebrow = $args['eyebrow'] ?? '';
$heading = $args['heading'] ?? '';
$intro   = $args['intro'] ?? '';
?>
<section class="lvjcb-section lvjcb-section--alt lvjcb-recently-purchased-vehicles" aria-labelledby="<?php echo esc_attr( $heading_id ); ?>">
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

		<?php
		lvjcb_slider_start(
			array(
				'id'       => $section_id,
				'label'    => __( 'Recently purchased vehicles', 'lvjcb' ),
				'autoplay' => true,
			)
		);
		foreach ( $vehicles as $vehicle ) :
			?>
			<li class="lvjcb-slider__slide">
				<?php get_template_part( 'template-parts/components/vehicle-card', null, $vehicle ); ?>
			</li>
			<?php
		endforeach;
		lvjcb_slider_end();
		?>

	</div>
</section>
