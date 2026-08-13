<?php
/**
 * Section: Internal Links
 *
 * Renders a contextual internal linking section at the bottom of
 * location and service pages. Links to related services and nearby
 * locations, built automatically from the config — no manual linking
 * needed. This is critical for SEO site structure.
 *
 * @package LVJCB
 * @since   0.4.0
 *
 * @param array $args {
 *     @type array  $locations Array of ['label' => string, 'url' => string].
 *     @type array  $services  Array of ['label' => string, 'url' => string].
 *     @type string $context   'location' or 'service'.
 *     @type string $city      Current city name (for location context).
 * }
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$locations = $args['locations'] ?? array();
$services  = $args['services'] ?? array();
$context   = $args['context'] ?? '';
$city      = $args['city'] ?? '';

if ( empty( $locations ) && empty( $services ) ) {
	return;
}

$business = lvjcb_get_config( 'business_name' );
?>

<section class="lvjcb-section lvjcb-section--gray lvjcb-internal-links">
	<div class="lvjcb-section__container">

		<?php if ( ! empty( $services ) ) : ?>
			<div class="lvjcb-internal-links__group">
				<h3 class="lvjcb-internal-links__heading">
					<?php
					if ( 'location' === $context && $city ) {
						printf( esc_html__( 'Our Services in %s', 'lvjcb' ), esc_html( $city ) );
					} else {
						esc_html_e( 'Other Services We Offer', 'lvjcb' );
					}
					?>
				</h3>
				<div class="lvjcb-internal-links__list">
					<?php foreach ( $services as $link ) : ?>
						<a href="<?php echo esc_url( $link['url'] ); ?>" class="lvjcb-internal-links__link">
							<?php echo esc_html( $link['label'] ); ?>
						</a>
					<?php endforeach; ?>
				</div>
			</div>
		<?php endif; ?>

		<?php if ( ! empty( $locations ) ) : ?>
			<div class="lvjcb-internal-links__group">
				<h3 class="lvjcb-internal-links__heading">
					<?php
					if ( 'location' === $context ) {
						esc_html_e( 'We Also Serve These Areas', 'lvjcb' );
					} else {
						esc_html_e( 'Available in These Areas', 'lvjcb' );
					}
					?>
				</h3>
				<div class="lvjcb-internal-links__list">
					<?php foreach ( $locations as $link ) : ?>
						<a href="<?php echo esc_url( $link['url'] ); ?>" class="lvjcb-internal-links__link">
							<?php echo esc_html( $link['label'] ); ?>
						</a>
					<?php endforeach; ?>
				</div>
			</div>
		<?php endif; ?>

	</div>
</section>
