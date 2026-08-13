<?php
/**
 * Hero component.
 *
 * Full-bleed background image with gradient overlay, uppercase heading
 * with gold accent, inline trust features, and gold CTA button.
 *
 * @param array $args {
 *     @type string $heading     Hero heading text. Em-dash splits white/gold.
 *     @type string $description Supporting description.
 *     @type int    $image_id    Attachment ID for the hero background image.
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
	$heading_html = esc_html( trim( $heading_parts[0] ) ) . ' <em>' . esc_html( trim( $heading_parts[1] ) ) . '</em>';
} else {
	$heading_html = esc_html( $heading );
}

$hero_features = array_slice( $trust_items, 0, 3 );
?>
<section class="lvjcb-hero<?php echo $image_id ? ' lvjcb-hero--has-image' : ''; ?>">

	<?php if ( $image_id ) : ?>
		<div class="lvjcb-hero__bg">
			<?php
			echo wp_get_attachment_image(
				$image_id,
				'full',
				false,
				array(
					'class'         => 'lvjcb-hero__bg-image',
					'fetchpriority' => 'high',
					'decoding'      => 'async',
					'sizes'         => '100vw',
					'alt'           => '',
				)
			);
			?>
		</div>
	<?php endif; ?>

	<div class="lvjcb-hero__overlay"></div>

	<div class="lvjcb-hero__container">
		<div class="lvjcb-hero__content">

			<h1 class="lvjcb-hero__heading"><?php echo $heading_html; ?></h1>

			<?php if ( $description ) : ?>
				<p class="lvjcb-hero__description"><?php echo esc_html( $description ); ?></p>
			<?php endif; ?>

			<?php if ( ! empty( $hero_features ) ) : ?>
				<div class="lvjcb-hero__features">
					<?php foreach ( $hero_features as $feature ) : ?>
						<div class="lvjcb-hero__feature">
							<?php echo lvjcb_icon( 'check-circle', array( 'size' => 20 ) ); ?>
							<span><?php echo esc_html( $feature['label'] ); ?></span>
						</div>
					<?php endforeach; ?>
				</div>
			<?php endif; ?>

			<div class="lvjcb-hero__ctas">
				<a href="<?php echo esc_url( $phone_href ); ?>" class="lvjcb-btn lvjcb-btn--gold lvjcb-hero__cta">
					<?php echo esc_html( $phone_display ); ?>
					<?php echo lvjcb_icon( 'arrow', array( 'size' => 18 ) ); ?>
				</a>
			</div>

			<div class="lvjcb-hero__quote-form-slot" id="lvjcb-quote-form-hero" tabindex="-1" hidden>
				<?php get_template_part( 'template-parts/components/quote-form', null, array(
					'context'     => 'hero',
					'form_id'     => 'hero',
					'submit_text' => __( 'Get My Cash Offer', 'lvjcb' ),
				) ); ?>
			</div>

		</div>
	</div>
</section>
