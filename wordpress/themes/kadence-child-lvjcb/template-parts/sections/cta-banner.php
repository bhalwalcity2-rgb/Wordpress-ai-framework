<?php
/**
 * "CTA Banner" section. No component mount — composes the shared
 * .lvjcb-btn classes directly onto its own link. Used twice on the
 * homepage; each instance must be given a distinct $args['id'].
 *
 * @param array $args {
 *     @type string $id       Optional unique identifier for this instance.
 *     @type string $eyebrow  Optional eyebrow label.
 *     @type string $heading  Section heading text.
 *     @type string $intro    Optional supporting copy.
 *     @type string $cta_text CTA link label.
 *     @type string $cta_url  CTA link destination.
 * }
 */
$cta_text = $args['cta_text'] ?? '';
$cta_url  = $args['cta_url'] ?? '';

if ( empty( $cta_text ) || empty( $cta_url ) ) {
	return;
}

$section_id = sanitize_title( $args['id'] ?? '' );
if ( '' === $section_id ) {
	$section_id = wp_unique_id( 'cta-banner-' );
}
$heading_id = $section_id . '-heading';

$eyebrow = $args['eyebrow'] ?? '';
$heading = $args['heading'] ?? '';
$intro   = $args['intro'] ?? '';
?>
<section class="lvjcb-section lvjcb-section--primary lvjcb-cta-banner" aria-labelledby="<?php echo esc_attr( $heading_id ); ?>">
	<div class="lvjcb-section__container lvjcb-cta-banner__inner">

		<div class="lvjcb-cta-banner__copy">
			<?php if ( $eyebrow ) : ?>
				<p class="lvjcb-section__eyebrow"><?php echo esc_html( $eyebrow ); ?></p>
			<?php endif; ?>

			<h2 id="<?php echo esc_attr( $heading_id ); ?>" class="lvjcb-section__heading">
				<?php echo esc_html( $heading ); ?>
			</h2>

			<?php if ( $intro ) : ?>
				<p class="lvjcb-section__intro"><?php echo esc_html( $intro ); ?></p>
			<?php endif; ?>
		</div>

		<a class="lvjcb-btn lvjcb-btn--inverse lvjcb-cta-banner__cta" href="<?php echo esc_url( $cta_url ); ?>">
			<?php echo esc_html( $cta_text ); ?>
		</a>

	</div>
</section>
