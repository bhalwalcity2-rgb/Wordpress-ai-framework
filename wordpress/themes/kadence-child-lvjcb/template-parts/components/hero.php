<?php
/**
 * Hero component.
 *
 * Two columns on desktop: the offer on the left, a photograph of the thing
 * being sold on the right. The previous version laid the text over a
 * full-bleed background photo behind a gradient, which meant the image was
 * always competing with the headline and the CTA for the same pixels — the
 * gradient existed only to win that fight. Giving the picture its own column
 * lets it be legible instead of decorative, and the buttons sit on flat
 * colour where their contrast is predictable rather than dependent on
 * whatever the photo happens to be doing behind them.
 *
 * Without an image the left column simply spans the row, so a page with no
 * hero photo yet gets a deliberate single-column hero rather than an empty
 * half.
 *
 * @param array $args {
 *     @type string $heading     Hero heading text. Em-dash splits white/gold.
 *     @type string $description Supporting description.
 *     @type string $eyebrow     Optional line above the heading.
 *     @type int    $image_id    Attachment ID for the hero image.
 *     @type string $image_alt   Alt text. Empty marks the photo decorative.
 * }
 */
$heading     = $args['heading'] ?? '';
$description = $args['description'] ?? '';
$eyebrow     = $args['eyebrow'] ?? '';
$image_id    = isset( $args['image_id'] ) ? (int) $args['image_id'] : 0;

$phone_display = lvjcb_get_phone_number( 'display' );
$phone_href    = 'tel:' . lvjcb_get_phone_number( 'e164' );
$quote_url     = lvjcb_get_config( 'instant_quote_url' );
$trust_items   = lvjcb_get_trust_items();

/*
 * The em dash marks where the gold half begins, but it is also real
 * punctuation: dropping it left the two halves running together as one
 * sentence for anyone reading the text rather than seeing the colour —
 * a screen reader, a search snippet, a page with CSS off. It stays,
 * carried into the gold half where the break belongs.
 */
$heading_parts = explode( "\u{2014}", $heading, 2 );
if ( count( $heading_parts ) === 2 ) {
	$heading_html = esc_html( trim( $heading_parts[0] ) ) . ' <em>' . esc_html( "\u{2014} " . trim( $heading_parts[1] ) ) . '</em>';
} else {
	$heading_html = esc_html( $heading );
}

$hero_features = array_slice( $trust_items, 0, 3 );
?>
<section class="lvjcb-hero<?php echo $image_id ? ' lvjcb-hero--has-image' : ''; ?>">

	<div class="lvjcb-hero__container">

		<div class="lvjcb-hero__content">

			<?php if ( $eyebrow ) : ?>
				<p class="lvjcb-hero__eyebrow">
					<?php echo lvjcb_icon( 'map-pin', array( 'size' => 16 ) ); ?>
					<?php echo esc_html( $eyebrow ); ?>
				</p>
			<?php endif; ?>

			<h1 class="lvjcb-hero__heading"><?php echo $heading_html; ?></h1>

			<?php if ( $description ) : ?>
				<p class="lvjcb-hero__description"><?php echo esc_html( $description ); ?></p>
			<?php endif; ?>

			<div class="lvjcb-hero__ctas">
				<?php if ( $quote_url ) : ?>
					<a href="<?php echo esc_url( $quote_url ); ?>" class="lvjcb-btn lvjcb-btn--primary lvjcb-hero__cta" target="_blank" rel="noopener">
						<?php esc_html_e( 'Get My Offer Online', 'lvjcb' ); ?>
						<?php echo lvjcb_icon( 'arrow', array( 'size' => 18 ) ); ?>
					</a>
				<?php endif; ?>
				<a href="<?php echo esc_url( $phone_href ); ?>" class="lvjcb-btn lvjcb-btn--outline-light lvjcb-hero__cta">
					<?php echo lvjcb_icon( 'phone', array( 'size' => 18 ) ); ?>
					<?php
					/* translators: %s: phone number. */
					printf( esc_html__( 'Call %s', 'lvjcb' ), esc_html( $phone_display ) );
					?>
				</a>
			</div>

			<?php if ( ! empty( $hero_features ) ) : ?>
				<ul class="lvjcb-hero__features" role="list">
					<?php foreach ( $hero_features as $feature ) : ?>
						<li class="lvjcb-hero__feature">
							<?php echo lvjcb_icon( 'check-circle', array( 'size' => 18 ) ); ?>
							<span><?php echo esc_html( $feature['label'] ); ?></span>
						</li>
					<?php endforeach; ?>
				</ul>
			<?php endif; ?>

		</div>

		<?php if ( $image_id ) : ?>
			<div class="lvjcb-hero__media">
				<?php
				/*
				 * fetchpriority high and no lazy attribute: this is the LCP
				 * element on every page that has one. 'large' rather than
				 * 'full' because the column is never wider than ~560px, and
				 * sizes tells the browser that before it picks a candidate.
				 */
				echo wp_get_attachment_image(
					$image_id,
					'large',
					false,
					array(
						'class'         => 'lvjcb-hero__image',
						'fetchpriority' => 'high',
						'decoding'      => 'async',
						'sizes'         => '(max-width: 900px) 100vw, 560px',
						'alt'           => $args['image_alt'] ?? '',
					)
				);
				?>
			</div>
		<?php endif; ?>

	</div>
</section>
