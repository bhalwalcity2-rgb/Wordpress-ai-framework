<?php
/**
 * "How It Works" section. Assembles Component 09 (Step Card) into an
 * ordered, numbered process list.
 *
 * @param array $args {
 *     @type string $id      Optional unique identifier for this instance.
 *     @type string $eyebrow Optional eyebrow label.
 *     @type string $heading Section heading text.
 *     @type string $intro   Optional supporting paragraph text.
 *     @type array  $steps   List of step data passed directly to step-card.php.
 * }
 */
$steps = $args['steps'] ?? array();

if ( empty( $steps ) ) {
	return;
}

$section_id = sanitize_title( $args['id'] ?? '' );
if ( '' === $section_id ) {
	$section_id = wp_unique_id( 'how-it-works-' );
}
$heading_id = $section_id . '-heading';

$eyebrow = $args['eyebrow'] ?? '';
$heading = $args['heading'] ?? '';
$intro   = $args['intro'] ?? '';
?>
<section class="lvjcb-section lvjcb-section--alt lvjcb-section--centered lvjcb-how-it-works" aria-labelledby="<?php echo esc_attr( $heading_id ); ?>">
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

		<ol class="lvjcb-section__grid lvjcb-section__grid--4up lvjcb-how-it-works__list" role="list">
			<?php foreach ( $steps as $step ) : ?>
				<li class="lvjcb-how-it-works__item">
					<?php get_template_part( 'template-parts/components/step-card', null, $step ); ?>
				</li>
			<?php endforeach; ?>
		</ol>

	</div>
</section>
