<?php
/**
 * Global Slider / Carousel Component
 *
 * The single reusable slider implementation used by both Recently
 * Purchased Vehicles and Testimonials — per the Niche Functionality
 * Library, there is exactly one slider system, never a per-section
 * fork of it.
 *
 * Implemented as open/close functions rather than a single
 * get_template_part(), because its job is to wrap markup a calling
 * section still owns the loop for (each slide is that section's own
 * card, rendered via its own get_template_part() call, completely
 * unmodified). This is the mechanism that lets the slider reuse
 * Vehicle Card and Testimonial Card without either component knowing
 * a slider exists.
 *
 * Mechanics: a native horizontally-scrolling, scroll-snap track — not
 * a JS-transform carousel. This gives touch/swipe, keyboard scrolling,
 * and trackpad support for free from the browser, requires no cloned
 * "loop" slides (explicitly ruled out by the Master Workflow), and
 * cannot cause layout shift since nothing reflows on interaction.
 * Previous/Next buttons call scrollBy() and disable naturally at each
 * end — see assets/js/components/slider.js.
 *
 * @package LVJCB
 * @since   0.1.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Open a slider — call, then output one <li class="lvjcb-slider__slide">
 * per item (each wrapping an unmodified card component), then call
 * lvjcb_slider_end().
 *
 * @since 0.1.0
 *
 * @param array $args {
 *     @type string $id       Optional unique identifier for this instance.
 *     @type string $label    Accessible label for the slide track (e.g.
 *                             "Recently purchased vehicles").
 *     @type string $modifier Optional CSS modifier, e.g. 'lvjcb-slider--3up'
 *                             to show three slides per view on desktop
 *                             instead of the default four. This is a
 *                             CSS-only variation of the one slider system,
 *                             not a second implementation.
 *     @type bool   $autoplay  Whether to auto-advance slides. Pauses on
 *                             hover/focus and respects prefers-reduced-motion.
 *     @type int    $interval  Autoplay interval in milliseconds (default 4000).
 * }
 * @return void
 */
function lvjcb_slider_start( $args = array() ) {

	$id = sanitize_title( $args['id'] ?? '' );
	if ( '' === $id ) {
		$id = wp_unique_id( 'slider-' );
	}

	$label    = $args['label'] ?? __( 'Items', 'lvjcb' );
	$modifier = $args['modifier'] ?? '';
	$autoplay = ! empty( $args['autoplay'] );
	$interval = absint( $args['interval'] ?? 4000 );
	$class    = 'lvjcb-slider' . ( $modifier ? ' ' . sanitize_html_class( $modifier ) : '' );

	$autoplay_attr = '';
	if ( $autoplay ) {
		$autoplay_attr = ' data-lvjcb-slider-autoplay="' . esc_attr( $interval ) . '"';
	}
	?>
	<div class="<?php echo esc_attr( $class ); ?>" data-lvjcb-slider<?php echo $autoplay_attr; ?>>
		<div class="lvjcb-slider__viewport">
			<ul
				class="lvjcb-slider__track"
				id="lvjcb-slider-track-<?php echo esc_attr( $id ); ?>"
				role="list"
				aria-label="<?php echo esc_attr( $label ); ?>"
				tabindex="0"
			>
	<?php
}

/**
 * Close a slider opened with lvjcb_slider_start().
 *
 * @since 0.1.0
 *
 * @return void
 */
function lvjcb_slider_end() {
	?>
			</ul>
		</div>
		<div class="lvjcb-slider__controls">
			<button type="button" class="lvjcb-slider__prev" data-lvjcb-slider-prev aria-label="<?php esc_attr_e( 'Previous', 'lvjcb' ); ?>">
				<?php echo lvjcb_icon( 'chevron-left', array( 'size' => 20 ) ); ?>
			</button>
			<button type="button" class="lvjcb-slider__next" data-lvjcb-slider-next aria-label="<?php esc_attr_e( 'Next', 'lvjcb' ); ?>">
				<?php echo lvjcb_icon( 'chevron-right', array( 'size' => 20 ) ); ?>
			</button>
		</div>
	</div>
	<?php
}
