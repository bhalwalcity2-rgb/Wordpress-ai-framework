<?php
/**
 * "Testimonials" section — Google Maps review-style layout.
 *
 * @param array $args {
 *     @type string $id               Optional unique identifier.
 *     @type string $eyebrow          Optional eyebrow label.
 *     @type string $heading          Section heading text.
 *     @type string $intro            Optional supporting paragraph.
 *     @type float  $aggregate_rating Overall rating (e.g. 4.9).
 *     @type int    $review_count     Total number of reviews.
 *     @type array  $testimonials     List of testimonial data.
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

$eyebrow          = $args['eyebrow'] ?? '';
$heading           = $args['heading'] ?? '';
$intro             = $args['intro'] ?? '';
$aggregate_rating  = (float) ( $args['aggregate_rating'] ?? 0 );
$review_count      = (int) ( $args['review_count'] ?? 0 );
?>
<section class="lvjcb-section lvjcb-section--white lvjcb-testimonials" aria-labelledby="<?php echo esc_attr( $heading_id ); ?>">
	<div class="lvjcb-section__container">

		<?php if ( $eyebrow ) : ?>
			<p class="lvjcb-section__eyebrow"><?php echo esc_html( $eyebrow ); ?></p>
		<?php endif; ?>

		<h2 id="<?php echo esc_attr( $heading_id ); ?>" class="lvjcb-section__heading">
			<?php echo esc_html( $heading ); ?>
		</h2>

		<?php if ( $aggregate_rating > 0 ) : ?>
			<div class="lvjcb-testimonials__summary">
				<span class="lvjcb-testimonials__score"><?php echo esc_html( number_format( $aggregate_rating, 1 ) ); ?></span>
				<div class="lvjcb-testimonials__summary-stars" role="img" aria-label="<?php echo esc_attr( sprintf( __( '%s out of 5 stars', 'lvjcb' ), number_format( $aggregate_rating, 1 ) ) ); ?>">
					<?php for ( $i = 0; $i < 5; $i++ ) : ?>
						<?php echo lvjcb_icon( 'star', array( 'class' => 'lvjcb-testimonials__summary-star' . ( $i >= round( $aggregate_rating ) ? ' is-empty' : '' ), 'size' => 18 ) ); ?>
					<?php endfor; ?>
				</div>
				<?php if ( $review_count > 0 ) : ?>
					<span class="lvjcb-testimonials__count"><?php echo esc_html( sprintf( __( '%s reviews', 'lvjcb' ), number_format_i18n( $review_count ) ) ); ?></span>
				<?php endif; ?>
			</div>
		<?php endif; ?>

		<?php if ( $intro ) : ?>
			<p class="lvjcb-section__intro"><?php echo esc_html( $intro ); ?></p>
		<?php endif; ?>

		<?php
		lvjcb_slider_start(
			array(
				'id'       => $section_id,
				'label'    => __( 'Customer reviews', 'lvjcb' ),
				'modifier' => 'lvjcb-slider--3up',
				'autoplay' => true,
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
