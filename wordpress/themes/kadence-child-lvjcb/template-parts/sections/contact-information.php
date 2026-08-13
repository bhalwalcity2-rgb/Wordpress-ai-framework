<?php
/**
 * "Contact Information" section. No component mount — composes
 * semantic markup directly from caller-supplied data.
 *
 * @param array $args {
 *     @type string $id             Optional unique identifier for this instance.
 *     @type string $eyebrow        Optional eyebrow label.
 *     @type string $heading        Section heading text.
 *     @type string $intro          Optional supporting paragraph text.
 *     @type string $business_name  Business name.
 *     @type string $address        Business address.
 *     @type string $phone          Business phone number, display format
 *                                  (caller should source this from
 *                                  lvjcb_get_phone_number( 'display' )).
 *     @type string $email          Business email address.
 *     @type string $business_hours Optional business hours text.
 *     @type string $map_embed      Optional map embed URL (a URL, never raw HTML).
 * }
 */
$business_name = $args['business_name'] ?? '';
$address       = $args['address'] ?? '';
$phone         = $args['phone'] ?? '';
$email         = $args['email'] ?? '';

if ( empty( $business_name ) || empty( $address ) || empty( $phone ) || empty( $email ) ) {
	return;
}

$business_hours = $args['business_hours'] ?? '';
$map_embed      = $args['map_embed'] ?? '';

$section_id = sanitize_title( $args['id'] ?? '' );
if ( '' === $section_id ) {
	$section_id = wp_unique_id( 'contact-information-' );
}
$heading_id = $section_id . '-heading';

$eyebrow = $args['eyebrow'] ?? '';
$heading = $args['heading'] ?? '';
$intro   = $args['intro'] ?? '';

$phone_href = preg_replace( '/[^0-9+]/', '', $phone );
?>
<section class="lvjcb-section lvjcb-section--alt lvjcb-contact-information" aria-labelledby="<?php echo esc_attr( $heading_id ); ?>">
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

		<div class="lvjcb-contact-information__grid">

			<div>
				<address class="lvjcb-contact-information__details">
					<span class="lvjcb-contact-information__business-name"><?php echo esc_html( $business_name ); ?></span>
					<span class="lvjcb-contact-information__address"><?php echo esc_html( $address ); ?></span>

					<a class="lvjcb-contact-information__phone" href="<?php echo esc_url( 'tel:' . $phone_href ); ?>">
						<?php echo esc_html( $phone ); ?>
					</a>

					<a class="lvjcb-contact-information__email" href="<?php echo esc_url( 'mailto:' . $email ); ?>">
						<?php echo esc_html( $email ); ?>
					</a>
				</address>

				<?php if ( $business_hours ) : ?>
					<p class="lvjcb-contact-information__hours"><?php echo esc_html( $business_hours ); ?></p>
				<?php endif; ?>
			</div>

			<?php if ( $map_embed ) : ?>
				<div class="lvjcb-contact-information__map">
					<iframe
						src="<?php echo esc_url( $map_embed ); ?>"
						title="<?php echo esc_attr( sprintf(
							/* translators: %s: business name. */
							__( 'Map showing the location of %s', 'lvjcb' ),
							$business_name
						) ); ?>"
						loading="lazy"
					></iframe>
				</div>
			<?php endif; ?>

		</div>

	</div>
</section>
