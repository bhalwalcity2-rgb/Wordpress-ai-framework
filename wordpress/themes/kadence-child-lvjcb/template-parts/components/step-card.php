<?php
/**
 * Step Card component.
 *
 * @param array $args {
 *     @type int    $step_number Sequence number of this step (1-based).
 *     @type string $icon        Optional icon name.
 *     @type string $heading     Step heading text.
 *     @type string $description Step description text.
 * }
 */
$step_number = isset( $args['step_number'] ) ? (int) $args['step_number'] : 0;
$icon        = $args['icon'] ?? '';
$heading     = $args['heading'] ?? '';
$description = $args['description'] ?? '';
?>
<article class="lvjcb-step-card">

	<?php if ( $step_number > 0 ) : ?>
		<span class="lvjcb-step-card__number" aria-hidden="true">
			<?php echo esc_html( $step_number ); ?>
		</span>
	<?php endif; ?>

	<?php if ( $icon ) : ?>
		<?php echo lvjcb_icon( $icon, array( 'class' => 'lvjcb-step-card__icon', 'size' => 24 ) ); ?>
	<?php endif; ?>

	<h3 class="lvjcb-step-card__heading">
		<?php if ( $step_number > 0 ) : ?>
			<span class="screen-reader-text">
				<?php
				echo esc_html(
					sprintf(
						/* translators: %d: Step number in the process sequence. */
						__( 'Step %d: ', 'lvjcb' ),
						$step_number
					)
				);
				?>
			</span>
		<?php endif; ?>
		<?php echo esc_html( $heading ); ?>
	</h3>

	<p class="lvjcb-step-card__description"><?php echo esc_html( $description ); ?></p>

</article>
