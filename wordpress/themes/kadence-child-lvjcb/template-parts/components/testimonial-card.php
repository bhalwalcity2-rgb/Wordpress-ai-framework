<?php
/**
 * Testimonial Card — Google Maps review style.
 *
 * @param array $args {
 *     @type int    $rating             0-5 star rating.
 *     @type string $quote              Review text.
 *     @type string $customer_name      Customer first name / initial.
 *     @type string $customer_location  City, state.
 *     @type string $date               Relative time (e.g. "2 months ago").
 * }
 */
$rating            = max( 0, min( 5, (int) ( $args['rating'] ?? 0 ) ) );
$quote             = $args['quote'] ?? '';
$customer_name     = $args['customer_name'] ?? '';
$customer_location = $args['customer_location'] ?? '';
$date              = $args['date'] ?? '';

$initial = mb_strtoupper( mb_substr( trim( $customer_name ), 0, 1 ) );
?>
<article class="lvjcb-review-card">

	<div class="lvjcb-review-card__header">
		<div class="lvjcb-review-card__avatar" aria-hidden="true">
			<?php echo esc_html( $initial ); ?>
		</div>
		<div class="lvjcb-review-card__meta">
			<span class="lvjcb-review-card__name"><?php echo esc_html( $customer_name ); ?></span>
			<?php if ( $customer_location ) : ?>
				<span class="lvjcb-review-card__location"><?php echo esc_html( $customer_location ); ?></span>
			<?php endif; ?>
		</div>
	</div>

	<div class="lvjcb-review-card__rating-row">
		<?php if ( $rating > 0 ) : ?>
			<div class="lvjcb-review-card__stars" role="img" aria-label="<?php echo esc_attr( sprintf( __( 'Rated %d out of 5 stars', 'lvjcb' ), $rating ) ); ?>">
				<?php for ( $i = 0; $i < 5; $i++ ) : ?>
					<?php echo lvjcb_icon( 'star', array( 'class' => 'lvjcb-review-card__star' . ( $i >= $rating ? ' is-empty' : '' ), 'size' => 14 ) ); ?>
				<?php endfor; ?>
			</div>
		<?php endif; ?>
		<?php if ( $date ) : ?>
			<span class="lvjcb-review-card__date"><?php echo esc_html( $date ); ?></span>
		<?php endif; ?>
	</div>

	<p class="lvjcb-review-card__text"><?php echo esc_html( $quote ); ?></p>

</article>
