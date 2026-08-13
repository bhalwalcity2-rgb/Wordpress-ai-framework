<?php
/**
 * "What We Buy" section. Assembles Component 04 (Service Card).
 *
 * @param array $args {
 *     @type string $id      Optional unique identifier for this instance.
 *     @type string $eyebrow Optional eyebrow label.
 *     @type string $heading Section heading text.
 *     @type string $intro   Optional supporting paragraph text.
 *     @type array  $cards   List of card data passed directly to service-card.php.
 * }
 */
$cards = $args['cards'] ?? array();

if ( empty( $cards ) ) {
	return;
}

$section_id = sanitize_title( $args['id'] ?? '' );
if ( '' === $section_id ) {
	$section_id = wp_unique_id( 'what-we-buy-' );
}
$heading_id = $section_id . '-heading';

$eyebrow = $args['eyebrow'] ?? '';
$heading = $args['heading'] ?? '';
$intro   = $args['intro'] ?? '';
?>
<section class="lvjcb-section lvjcb-what-we-buy" aria-labelledby="<?php echo esc_attr( $heading_id ); ?>">
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

		<div class="lvjcb-section__grid lvjcb-section__grid--3up">
			<?php foreach ( $cards as $card ) : ?>
				<?php get_template_part( 'template-parts/components/service-card', null, $card ); ?>
			<?php endforeach; ?>
		</div>

	</div>
</section>
