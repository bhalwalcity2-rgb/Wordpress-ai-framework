<?php
/**
 * Hero component.
 *
 * Niche Functionality Library — Junk Car Buyers: actual phone-number CTA
 * (never "Call Now") plus an "Instant Offer" CTA that reveals the
 * embedded Quote Form. Image right on desktop, 60/40 split.
 *
 * @param array $args {
 *     @type string $heading     Hero heading text.
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
?>
<section class="lvjcb-hero">
	<div class="lvjcb-hero__container">

		<div class="lvjcb-hero__content">

			<div class="lvjcb-hero__eyebrow">
				<span class="lvjcb-hero__eyebrow-dot"></span>
				<?php esc_html_e( 'Serving Las Vegas & Clark County', 'lvjcb' ); ?>
			</div>

			<h1 class="lvjcb-hero__heading"><?php echo esc_html( $heading ); ?></h1>

			<?php if ( $description ) : ?>
				<p class="lvjcb-hero__description"><?php echo esc_html( $description ); ?></p>
			<?php endif; ?>

			<a href="<?php echo esc_url( $phone_href ); ?>" class="lvjcb-hero__phone">
				<?php echo lvjcb_icon( 'phone', array( 'size' => 22 ) ); ?>
				<?php echo esc_html( $phone_display ); ?>
			</a>

			<div class="lvjcb-hero__ctas">
				<a href="<?php echo esc_url( $phone_href ); ?>" class="lvjcb-btn lvjcb-btn--primary">
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

			<?php if ( $trust_items ) : ?>
				<div class="lvjcb-hero__badges">
					<?php foreach ( $trust_items as $item ) : ?>
						<div class="lvjcb-hero__badge">
							<?php echo lvjcb_icon( $item['icon'], array( 'size' => 20 ) ); ?>
							<span><?php echo esc_html( $item['label'] ); ?></span>
						</div>
					<?php endforeach; ?>
				</div>
			<?php endif; ?>

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
					<span><?php esc_html_e( 'Hero photograph pending — tow truck loading a real junk vehicle, Las Vegas residential driveway, natural daylight, per the AI Image Production Guide.', 'lvjcb' ); ?></span>
				</div>
			<?php endif; ?>
		</div>

	</div>
</section>
