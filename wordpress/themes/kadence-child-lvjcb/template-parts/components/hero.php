<?php
/**
 * Hero component.
 *
 * Gradient navy background with diagonal bottom edge. Gold-accented
 * heading, stats row, phone CTA with gold button. Image right on
 * desktop, 55/45 split.
 *
 * @param array $args {
 *     @type string $heading     Hero heading text. Wrap key phrase in <em> for gold accent.
 *     @type string $description Supporting description.
 *     @type int    $image_id    Attachment ID for the hero image.
 * }
 */
$heading     = $args['heading'] ?? '';
$description = $args['description'] ?? '';
$image_id    = isset( $args['image_id'] ) ? (int) $args['image_id'] : 0;

$phone_display = lvjcb_get_phone_number( 'display' );
$phone_href    = 'tel:' . lvjcb_get_phone_number( 'e164' );
$trust_items   = lvjcb_get_trust_items();

$heading_parts = explode( "\u{2014}", $heading, 2 );
if ( count( $heading_parts ) === 2 ) {
	$heading_html = esc_html( $heading_parts[0] ) . '&mdash; <em>' . esc_html( trim( $heading_parts[1] ) ) . '</em>';
} else {
	$heading_html = esc_html( $heading );
}
?>
<section class="lvjcb-hero">
	<div class="lvjcb-hero__container">

		<div class="lvjcb-hero__content">

			<div class="lvjcb-hero__eyebrow">
				<span class="lvjcb-hero__eyebrow-dot"></span>
				<?php esc_html_e( 'Serving Las Vegas & Clark County', 'lvjcb' ); ?>
			</div>

			<h1 class="lvjcb-hero__heading"><?php echo $heading_html; ?></h1>

			<?php if ( $description ) : ?>
				<p class="lvjcb-hero__description"><?php echo esc_html( $description ); ?></p>
			<?php endif; ?>

			<div class="lvjcb-hero__ctas">
				<a href="<?php echo esc_url( $phone_href ); ?>" class="lvjcb-btn lvjcb-btn--gold">
					<?php echo esc_html( $phone_display ); ?>
				</a>
				<button type="button" class="lvjcb-btn lvjcb-btn--secondary lvjcb-btn--on-dark" id="lvjcb-hero-instant-offer" aria-expanded="false" aria-controls="lvjcb-quote-form-hero">
					<?php esc_html_e( 'Instant Offer', 'lvjcb' ); ?>
				</button>
			</div>

			<div class="lvjcb-hero__quote-form-slot" id="lvjcb-quote-form-hero" tabindex="-1" hidden>
				<?php get_template_part( 'template-parts/components/quote-form', null, array(
					'context'     => 'hero',
					'form_id'     => 'hero',
					'submit_text' => __( 'Get My Cash Offer', 'lvjcb' ),
				) ); ?>
			</div>

			<div class="lvjcb-hero__stats">
				<div>
					<div class="lvjcb-hero__stat-value">500+</div>
					<div class="lvjcb-hero__stat-label"><?php esc_html_e( 'Cars bought this year', 'lvjcb' ); ?></div>
				</div>
				<div>
					<div class="lvjcb-hero__stat-value"><?php esc_html_e( 'Same Day', 'lvjcb' ); ?></div>
					<div class="lvjcb-hero__stat-label"><?php esc_html_e( 'Pickup & payment', 'lvjcb' ); ?></div>
				</div>
				<div>
					<div class="lvjcb-hero__stat-value">$0</div>
					<div class="lvjcb-hero__stat-label"><?php esc_html_e( 'Towing fees, always', 'lvjcb' ); ?></div>
				</div>
			</div>

		</div>

		<div class="lvjcb-hero__media">
			<?php if ( $image_id ) : ?>
				<?php
				echo wp_get_attachment_image(
					$image_id,
					'lvjcb-hero',
					false,
					array(
						'class'        => 'lvjcb-hero__image',
						'fetchpriority' => 'high',
						'decoding'     => 'async',
						'sizes'        => '(min-width: 1025px) 44vw, 100vw',
					)
				);
				?>
			<?php else : ?>
				<div class="lvjcb-hero__placeholder">
					<?php echo lvjcb_icon( 'truck', array( 'size' => 34 ) ); ?>
					<span><?php esc_html_e( 'Hero photograph pending', 'lvjcb' ); ?></span>
				</div>
			<?php endif; ?>
		</div>

	</div>
</section>
