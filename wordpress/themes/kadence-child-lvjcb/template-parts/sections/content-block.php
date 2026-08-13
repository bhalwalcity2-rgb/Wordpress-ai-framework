<?php
/**
 * Section: Content Block
 *
 * Renders a heading + body text section from a content JSON file.
 * Used by location and service page templates for their rich content
 * sections.
 *
 * @package LVJCB
 * @since   0.4.0
 *
 * @param array $args {
 *     @type string $heading Section heading.
 *     @type string $body    Body text (supports \n\n paragraph breaks).
 * }
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$heading = $args['heading'] ?? '';
$body    = $args['body'] ?? '';

if ( ! $heading && ! $body ) {
	return;
}
?>

<section class="lvjcb-section lvjcb-content-block">
	<div class="lvjcb-section__container">
		<?php if ( $heading ) : ?>
			<h2 class="lvjcb-content-block__heading"><?php echo esc_html( $heading ); ?></h2>
		<?php endif; ?>
		<?php if ( $body ) : ?>
			<div class="lvjcb-content-block__body">
				<?php
				$paragraphs = preg_split( '/\n\s*\n/', $body );
				foreach ( $paragraphs as $paragraph ) :
					$paragraph = trim( $paragraph );
					if ( $paragraph ) :
						?>
						<p><?php echo wp_kses_post( $paragraph ); ?></p>
						<?php
					endif;
				endforeach;
				?>
			</div>
		<?php endif; ?>
	</div>
</section>
