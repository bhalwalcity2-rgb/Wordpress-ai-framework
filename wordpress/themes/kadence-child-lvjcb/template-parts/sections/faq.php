<?php
/**
 * "FAQ" section. Assembles Component 10 (FAQ Accordion Item) into a
 * vertical stack — the one section with no grid at any breakpoint.
 *
 * @param array $args {
 *     @type string $id      Optional unique identifier for this instance.
 *     @type string $eyebrow Optional eyebrow label.
 *     @type string $heading Section heading text.
 *     @type string $intro   Optional supporting paragraph text.
 *     @type array  $items   List of FAQ item data passed directly to faq-item.php.
 * }
 */
$items = $args['items'] ?? array();

if ( empty( $items ) ) {
	return;
}

$section_id = sanitize_title( $args['id'] ?? '' );
if ( '' === $section_id ) {
	$section_id = wp_unique_id( 'faq-' );
}
$heading_id = $section_id . '-heading';

$eyebrow = $args['eyebrow'] ?? '';
$heading = $args['heading'] ?? '';
$intro   = $args['intro'] ?? '';
?>
<section class="lvjcb-section lvjcb-section--alt lvjcb-faq" aria-labelledby="<?php echo esc_attr( $heading_id ); ?>">
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

		<div class="lvjcb-faq__list">
			<?php foreach ( $items as $item ) : ?>
				<?php get_template_part( 'template-parts/components/faq-item', null, $item ); ?>
			<?php endforeach; ?>
		</div>

	</div>
</section>
