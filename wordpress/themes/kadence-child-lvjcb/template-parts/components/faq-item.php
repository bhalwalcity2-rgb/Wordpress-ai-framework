<?php
/**
 * FAQ Accordion Item component.
 *
 * @param array $args {
 *     @type string $question Question text.
 *     @type string $answer   Answer text.
 *     @type string $id       Unique identifier for this instance, supplied by the caller.
 * }
 */
$question = $args['question'] ?? '';
$answer   = $args['answer'] ?? '';
$id       = sanitize_title( $args['id'] ?? '' );

if ( '' === $id ) {
	$id = wp_unique_id( 'item-' );
}

$button_id = 'lvjcb-faq-button-' . $id;
$panel_id  = 'lvjcb-faq-panel-' . $id;
?>
<div class="lvjcb-faq-item">

	<h3 class="lvjcb-faq-item__heading">
		<button
			type="button"
			class="lvjcb-faq-item__toggle"
			id="<?php echo esc_attr( $button_id ); ?>"
			aria-expanded="false"
			aria-controls="<?php echo esc_attr( $panel_id ); ?>"
		>
			<span class="lvjcb-faq-item__question"><?php echo esc_html( $question ); ?></span>
			<?php echo lvjcb_icon( 'plus', array( 'class' => 'lvjcb-faq-item__icon', 'size' => 20 ) ); ?>
		</button>
	</h3>

	<div
		class="lvjcb-faq-item__panel"
		id="<?php echo esc_attr( $panel_id ); ?>"
		aria-labelledby="<?php echo esc_attr( $button_id ); ?>"
		hidden
	>
		<div class="lvjcb-faq-item__panel-inner">
			<p><?php echo esc_html( $answer ); ?></p>
		</div>
	</div>

</div>
