<?php
/**
 * Quote Form component.
 *
 * @param array $args {
 *     @type string $context     Placement context this instance is mounted in (e.g. 'hero').
 *     @type string $form_id     Unique identifier for this form instance.
 *     @type string $submit_text Submit button label.
 * }
 */
$context     = sanitize_key( $args['context'] ?? '' );
$form_id     = sanitize_title( $args['form_id'] ?? '' );
$submit_text = $args['submit_text'] ?? '';

if ( '' === $form_id ) {
	$form_id = wp_unique_id( 'quote-form-' );
}
?>
<form class="lvjcb-quote-form" method="post" id="<?php echo esc_attr( $form_id ); ?>">

	<?php if ( $context ) : ?>
		<input type="hidden" name="lead_source" value="<?php echo esc_attr( $context ); ?>">
	<?php endif; ?>

	<div class="lvjcb-quote-form__field">
		<label class="lvjcb-quote-form__label" for="<?php echo esc_attr( $form_id ); ?>-name">
			<?php esc_html_e( 'Full Name', 'lvjcb' ); ?>
		</label>
		<input
			type="text"
			class="lvjcb-quote-form__input"
			id="<?php echo esc_attr( $form_id ); ?>-name"
			name="name"
			autocomplete="name"
			required
		>
	</div>

	<div class="lvjcb-quote-form__field">
		<label class="lvjcb-quote-form__label" for="<?php echo esc_attr( $form_id ); ?>-phone">
			<?php esc_html_e( 'Phone Number', 'lvjcb' ); ?>
		</label>
		<input
			type="tel"
			class="lvjcb-quote-form__input"
			id="<?php echo esc_attr( $form_id ); ?>-phone"
			name="phone"
			autocomplete="tel"
			inputmode="tel"
			required
		>
	</div>

	<div class="lvjcb-quote-form__row">
		<div class="lvjcb-quote-form__field">
			<label class="lvjcb-quote-form__label" for="<?php echo esc_attr( $form_id ); ?>-year">
				<?php esc_html_e( 'Vehicle Year', 'lvjcb' ); ?>
			</label>
			<input
				type="text"
				class="lvjcb-quote-form__input"
				id="<?php echo esc_attr( $form_id ); ?>-year"
				name="vehicle_year"
				inputmode="numeric"
				pattern="[0-9]{4}"
				maxlength="4"
				autocomplete="off"
				required
			>
		</div>

		<div class="lvjcb-quote-form__field">
			<label class="lvjcb-quote-form__label" for="<?php echo esc_attr( $form_id ); ?>-make">
				<?php esc_html_e( 'Vehicle Make', 'lvjcb' ); ?>
			</label>
			<input
				type="text"
				class="lvjcb-quote-form__input"
				id="<?php echo esc_attr( $form_id ); ?>-make"
				name="vehicle_make"
				autocomplete="off"
				required
			>
		</div>

		<div class="lvjcb-quote-form__field">
			<label class="lvjcb-quote-form__label" for="<?php echo esc_attr( $form_id ); ?>-model">
				<?php esc_html_e( 'Vehicle Model', 'lvjcb' ); ?>
			</label>
			<input
				type="text"
				class="lvjcb-quote-form__input"
				id="<?php echo esc_attr( $form_id ); ?>-model"
				name="vehicle_model"
				autocomplete="off"
				required
			>
		</div>
	</div>

	<div class="lvjcb-quote-form__field">
		<label class="lvjcb-quote-form__label" for="<?php echo esc_attr( $form_id ); ?>-zip">
			<?php esc_html_e( 'Zip Code', 'lvjcb' ); ?>
		</label>
		<input
			type="text"
			class="lvjcb-quote-form__input"
			id="<?php echo esc_attr( $form_id ); ?>-zip"
			name="zip"
			autocomplete="postal-code"
			inputmode="numeric"
			pattern="[0-9]{5}"
			maxlength="5"
			required
		>
	</div>

	<button type="submit" class="lvjcb-btn lvjcb-btn--primary lvjcb-quote-form__submit">
		<?php echo esc_html( $submit_text ); ?>
	</button>

</form>
