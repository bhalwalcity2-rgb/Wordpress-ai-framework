<?php
/**
 * Icon System
 *
 * Single source of truth for the project's icon set. All icons share one
 * stroke-based family, weight, and bounding box, per the Design System
 * and the Master Website Creation Workflow's icon consistency rule.
 *
 * The sprite is rendered once, inline, as early in the document as
 * possible (see lvjcb_render_icon_sprite(), hooked to wp_body_open).
 * This resolves the sprite-delivery question flagged in the Developer
 * Handoff Notes: icons load eagerly because the sprite is inline in the
 * initial HTML response, not a separate fetched file.
 *
 * @package LVJCB
 * @since   0.1.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

add_action( 'wp_body_open', 'lvjcb_render_icon_sprite' );

/**
 * Output the inline SVG icon sprite.
 *
 * Every <symbol> here uses viewBox="0 0 24 24", 1.6px stroke, round
 * caps/joins — the one consistent icon language used everywhere.
 *
 * @since 0.1.0
 *
 * @return void
 */
function lvjcb_render_icon_sprite() {

	?>
	<svg xmlns="http://www.w3.org/2000/svg" style="display:none" aria-hidden="true">
		<defs>
			<symbol id="icon-phone" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round">
				<path d="M6.6 10.5c1.1 2.2 2.9 4 5.1 5.1l1.7-1.7a1 1 0 0 1 1-.25c1 .33 2.1.5 3.2.5a1 1 0 0 1 1 1V18.5a1 1 0 0 1-1 1C10.6 19.5 4.5 13.4 4.5 5.5a1 1 0 0 1 1-1H8a1 1 0 0 1 1 1c0 1.1.17 2.2.5 3.2a1 1 0 0 1-.25 1L6.6 10.5Z"/>
			</symbol>
			<symbol id="icon-menu" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round">
				<line x1="4" y1="7" x2="20" y2="7"/><line x1="4" y1="12" x2="20" y2="12"/><line x1="4" y1="17" x2="20" y2="17"/>
			</symbol>
			<symbol id="icon-close" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round">
				<line x1="6" y1="6" x2="18" y2="18"/><line x1="18" y1="6" x2="6" y2="18"/>
			</symbol>
			<symbol id="icon-truck" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round">
				<path d="M3 16.5V9.5a1 1 0 0 1 1-1h9l4.5 4.5H21a1 1 0 0 1 1 1v2.5a1 1 0 0 1-1 1H4a1 1 0 0 1-1-1Z"/><circle cx="7.5" cy="17.5" r="1.8"/><circle cx="16.5" cy="17.5" r="1.8"/>
			</symbol>
			<symbol id="icon-cash" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round">
				<circle cx="12" cy="12" r="9"/><path d="M9.5 12.5l1.8 1.8L15 10"/>
			</symbol>
			<symbol id="icon-clock" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round">
				<circle cx="12" cy="12" r="9"/><path d="M12 7v5l3.2 2"/>
			</symbol>
			<symbol id="icon-shield" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round">
				<path d="M12 3l7 3v5c0 4.5-3 7.7-7 9-4-1.3-7-4.5-7-9V6l7-3Z"/><path d="M9.5 12.3l1.8 1.8L15 10"/>
			</symbol>
			<symbol id="icon-document" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round">
				<path d="M6 4h9l4 4v12a1 1 0 0 1-1 1H6a1 1 0 0 1-1-1V5a1 1 0 0 1 1-1Z"/><path d="M9 12.5l2 2 4-4.5"/>
			</symbol>
			<symbol id="icon-arrow" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round">
				<path d="M7 17L17 7M9 7h8v8"/>
			</symbol>
			<symbol id="icon-chevron-down" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round">
				<path d="M6 9l6 6 6-6"/>
			</symbol>
			<symbol id="icon-chevron-left" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round">
				<path d="M15 6l-6 6 6 6"/>
			</symbol>
			<symbol id="icon-chevron-right" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round">
				<path d="M9 6l6 6-6 6"/>
			</symbol>
			<symbol id="icon-star" viewBox="0 0 24 24" fill="currentColor" stroke="currentColor" stroke-width="1.2" stroke-linejoin="round">
				<path d="M12 2l2.9 6.3 6.9.7-5.2 4.7 1.5 6.8L12 17l-6.1 3.5 1.5-6.8L2.2 9l6.9-.7L12 2Z"/>
			</symbol>
			<symbol id="icon-plus" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round">
				<line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/>
			</symbol>
			<symbol id="icon-map-pin" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round">
				<path d="M12 21s-6-5.7-6-11a6 6 0 1 1 12 0c0 5.3-6 11-6 11Z"/><circle cx="12" cy="10" r="2.2"/>
			</symbol>
			<symbol id="icon-wrench" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round">
				<path d="M14.7 6.3a4 4 0 0 0-5.4 4.7L3 17.3 5.7 20l6.3-6.3a4 4 0 0 0 4.7-5.4l-2.6 2.6-2-2 2.6-2.6Z"/>
			</symbol>
			<symbol id="icon-handshake" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round">
				<path d="M12 21s-7-4.4-9.3-9.1C1.4 8.4 3 5 6.5 5c2 0 3.4 1.1 5.5 3.3C14.1 6.1 15.5 5 17.5 5 21 5 22.6 8.4 21.3 11.9 19 16.6 12 21 12 21Z"/>
			</symbol>
			<symbol id="icon-bolt" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round">
				<path d="M13 3L4 14h6l-1 7 9-11h-6l1-7Z"/>
			</symbol>
			<symbol id="icon-car-sedan" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round">
				<path d="M3 15.5v-4a1 1 0 0 1 .8-1L6 10l1.5-3a1 1 0 0 1 .9-.6h7.2a1 1 0 0 1 .9.6L18 10l2.2.5a1 1 0 0 1 .8 1v4a1 1 0 0 1-1 1H4a1 1 0 0 1-1-1Z"/><circle cx="7.5" cy="16.5" r="1.6"/><circle cx="16.5" cy="16.5" r="1.6"/>
			</symbol>
			<symbol id="icon-car-suv" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round">
				<path d="M3 16.5v-5a1 1 0 0 1 1-1h3l2-3.5h7l3 3.5h1a1 1 0 0 1 1 1v5a1 1 0 0 1-1 1H4a1 1 0 0 1-1-1Z"/><path d="M9 10.5V7"/><circle cx="7.5" cy="17.5" r="1.6"/><circle cx="16.5" cy="17.5" r="1.6"/>
			</symbol>
			<symbol id="icon-damage" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round">
				<path d="M4 17V9.5l4-4h8l4 4V17"/><rect x="6" y="12" width="12" height="5" rx="1"/>
			</symbol>
			<symbol id="icon-no-fee" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round">
				<circle cx="12" cy="12" r="9"/><path d="M9.5 12.5l1.8 1.8L15 10"/>
			</symbol>
			<symbol id="icon-check-circle" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round">
				<circle cx="12" cy="12" r="9"/><path d="M9 12l2 2 4-4"/>
			</symbol>
		</defs>
	</svg>
	<?php
}

/**
 * Render one icon referencing the inline sprite.
 *
 * @since 0.1.0
 *
 * @param string $name Icon name, matching a symbol id (without the icon- prefix).
 * @param array  $args {
 *     @type string $class Additional CSS class(es).
 *     @type int    $size  Width/height in pixels. Default 24.
 * }
 * @return string Escaped SVG markup.
 */
function lvjcb_icon( $name, $args = array() ) {

	$name  = sanitize_html_class( $name );
	$class = isset( $args['class'] ) ? sanitize_html_class( $args['class'] ) : '';
	$size  = isset( $args['size'] ) ? (int) $args['size'] : 24;

	return sprintf(
		'<svg class="icon %1$s" width="%2$d" height="%2$d" aria-hidden="true" focusable="false"><use href="#icon-%3$s"></use></svg>',
		esc_attr( $class ),
		absint( $size ),
		esc_attr( $name )
	);
}
