<?php
/**
 * Testimonial Card component.
 *
 * @param array $args {
 *     @type int    $rating             0-5 star rating.
 *     @type string $quote              Review text.
 *     @type string $customer_name      Customer first name / initial.
 *     @type string $customer_location  City, state.
 * }
 */
$rating            = max( 0, min( 5, (int) ( $args['rating'] ?? 0 ) ) );
$quote             = $args['quote'] ?? '';
$customer_name     = $args['customer_name'] ?? '';
$customer_location = $args['customer_location'] ?? '';
?>
<article class="lvjcb-testimonial-card">

	<?php if ( $rating > 0 ) : ?>
		<div class="lvjcb-testimonial-card__rating" role="img" aria-label="<?php echo esc_attr( sprintf(
			/* translators: %d: star rating out of 5. */
			__( 'Rated %d out of 5 stars', 'lvjcb' ),
			$rating
		) ); ?>">
			<?php for ( $i = 0; $i < 5; $i++ ) : ?>
				<?php echo lvjcb_icon( 'star', array( 'class' => 'lvjcb-testimonial-card__star' . ( $i >= $rating ? ' is-empty' : '' ), 'size' => 16 ) ); ?>
			<?php endfor; ?>
		</div>
	<?php endif; ?>

	<blockquote class="lvjcb-testimonial-card__quote">
		<p><?php echo esc_html( $quote ); ?></p>
	</blockquote>

	<div class="lvjcb-testimonial-card__who">
		<span class="lvjcb-testimonial-card__name"><?php echo esc_html( $customer_name ); ?></span>
		<?php if ( $customer_location ) : ?>
			<span class="lvjcb-testimonial-card__location"><?php echo esc_html( $customer_location ); ?></span>
		<?php endif; ?>
	</div>

</article>
