<?php
/**
 * "Testimonials" section. Assembles the global Slider/Carousel
 * Component (3-up variant) wrapping Component 07 (Testimonial Card),
 * unmodified, per the Niche Functionality Library. Content follows the
 * Review & Testimonial Authenticity Rule — genuine, supplied reviews
 * only, presented in a familiar review-layout style without fabricating
 * any platform's branding.
 *
 * @param array $args {
 *     @type string $id           Optional unique identifier for this instance.
 *     @type string $eyebrow      Optional eyebrow label.
 *     @type string $heading      Section heading text.
 *     @type string $intro        Optional supporting paragraph text.
 *     @type array  $testimonials List of testimonial data passed directly
 *                                to testimonial-card.php: rating, quote,
 *                                customer_name, customer_location.
 * }
 */
$testimonials = $args['testimonials'] ?? array();

if ( empty( $testimonials ) ) {
	return;
}

$section_id = sanitize_title( $args['id'] ?? '' );
if ( '' === $section_id ) {
	$section_id = wp_unique_id( 'testimonials-' );
}
$heading_id = $section_id . '-heading';

$eyebrow = $args['eyebrow'] ?? '';
$heading = $args['heading'] ?? '';
$intro   = $args['intro'] ?? '';
?>
<section class="lvjcb-section lvjcb-testimonials" aria-labelledby="<?php echo esc_attr( $heading_id ); ?>">
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
				'label'    => __( 'Customer testimonials', 'lvjcb' ),
				'modifier' => 'lvjcb-slider--3up',
			)
		);
		foreach ( $testimonials as $testimonial ) :
			?>
			<li class="lvjcb-slider__slide">
				<?php get_template_part( 'template-parts/components/testimonial-card', null, $testimonial ); ?>
			</li>
			<?php
		endforeach;
		lvjcb_slider_end();
		?>

	</div>
</section>
