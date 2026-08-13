<?php
/**
 * Contrast-Safe Color Derivation
 *
 * Turns the accessibility-driven color adjustments that were originally
 * done by hand for Las Vegas Junk Car Buyers (darkening the approved
 * red/gold/blue just enough to pass WCAG contrast) into a reusable,
 * programmatic system. Given any business's five raw brand colors,
 * these functions compute the same category of safe derived shades
 * automatically — this is the part of the "new business" workflow that
 * can be fully mechanical rather than a manual audit each time.
 *
 * @package LVJCB
 * @since   0.2.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Convert a hex color to an [r, g, b] array (0-255 each).
 *
 * @since 0.2.0
 *
 * @param string $hex Hex color, with or without leading #.
 * @return int[] [r, g, b].
 */
function lvjcb_hex_to_rgb( $hex ) {

	$hex = ltrim( $hex, '#' );

	if ( 3 === strlen( $hex ) ) {
		$hex = $hex[0] . $hex[0] . $hex[1] . $hex[1] . $hex[2] . $hex[2];
	}

	return array(
		hexdec( substr( $hex, 0, 2 ) ),
		hexdec( substr( $hex, 2, 2 ) ),
		hexdec( substr( $hex, 4, 2 ) ),
	);
}

/**
 * Convert an [r, g, b] array back to a hex color string.
 *
 * @since 0.2.0
 *
 * @param int[] $rgb [r, g, b], each 0-255.
 * @return string Hex color with leading #.
 */
function lvjcb_rgb_to_hex( $rgb ) {

	return sprintf(
		'#%02X%02X%02X',
		max( 0, min( 255, (int) round( $rgb[0] ) ) ),
		max( 0, min( 255, (int) round( $rgb[1] ) ) ),
		max( 0, min( 255, (int) round( $rgb[2] ) ) )
	);
}

/**
 * WCAG relative luminance of a color.
 *
 * @since 0.2.0
 *
 * @param int[] $rgb [r, g, b], each 0-255.
 * @return float Relative luminance, 0 (black) to 1 (white).
 */
function lvjcb_relative_luminance( $rgb ) {

	$channels = array_map(
		function ( $c ) {
			$c = $c / 255;
			return ( $c <= 0.03928 ) ? ( $c / 12.92 ) : pow( ( $c + 0.055 ) / 1.055, 2.4 );
		},
		$rgb
	);

	return 0.2126 * $channels[0] + 0.7152 * $channels[1] + 0.0722 * $channels[2];
}

/**
 * WCAG contrast ratio between two hex colors.
 *
 * @since 0.2.0
 *
 * @param string $hex_a First color.
 * @param string $hex_b Second color.
 * @return float Contrast ratio, 1 (no contrast) to 21 (max contrast).
 */
function lvjcb_contrast_ratio( $hex_a, $hex_b ) {

	$l_a = lvjcb_relative_luminance( lvjcb_hex_to_rgb( $hex_a ) );
	$l_b = lvjcb_relative_luminance( lvjcb_hex_to_rgb( $hex_b ) );

	$lighter = max( $l_a, $l_b );
	$darker  = min( $l_a, $l_b );

	return ( $lighter + 0.05 ) / ( $darker + 0.05 );
}

/**
 * Mix a color toward black or white by a given amount.
 *
 * @since 0.2.0
 *
 * @param string $hex    Base color.
 * @param float  $amount 0-1. Negative darkens (mixes toward black),
 *                       positive lightens (mixes toward white).
 * @return string Adjusted hex color.
 */
function lvjcb_mix_shade( $hex, $amount ) {

	$rgb    = lvjcb_hex_to_rgb( $hex );
	$target = $amount < 0 ? array( 0, 0, 0 ) : array( 255, 255, 255 );
	$amount = abs( $amount );

	$mixed = array(
		$rgb[0] + ( $target[0] - $rgb[0] ) * $amount,
		$rgb[1] + ( $target[1] - $rgb[1] ) * $amount,
		$rgb[2] + ( $target[2] - $rgb[2] ) * $amount,
	);

	return lvjcb_rgb_to_hex( $mixed );
}

/**
 * Darken (or lighten) a color in small steps until it reaches a minimum
 * contrast ratio against a given background — the same process used by
 * hand to derive --lvjcb-color-primary (#B00000 from #D00000) and
 * --lvjcb-color-accent-deep (#B37D06 from #FFBA08), now automatic for
 * any input color.
 *
 * @since 0.2.0
 *
 * @param string $hex         Starting color.
 * @param string $against_hex Background color to contrast against.
 * @param float  $min_ratio   Minimum WCAG contrast ratio required.
 * @return string A hex color meeting the contrast requirement, as close
 *                to the original as possible.
 */
function lvjcb_ensure_contrast( $hex, $against_hex, $min_ratio ) {

	if ( lvjcb_contrast_ratio( $hex, $against_hex ) >= $min_ratio ) {
		return $hex;
	}

	// Darkening is tried first since every brand color in practice has
	// been a mid-to-bright tone needing to work against a light
	// background; if the color is already darker than its target, mix
	// toward black still increases contrast against a light background.
	for ( $step = 1; $step <= 20; $step++ ) {
		$candidate = lvjcb_mix_shade( $hex, -1 * ( $step / 20 ) );
		if ( lvjcb_contrast_ratio( $candidate, $against_hex ) >= $min_ratio ) {
			return $candidate;
		}
	}

	// Fallback: pure black always passes against any light background.
	return '#000000';
}

/**
 * Compute the full set of derived color tokens from five raw brand
 * colors, applying the same accessibility rules established for Las
 * Vegas Junk Car Buyers: button/CTA fills need 4.5:1 against white text,
 * focus rings need 3:1 (non-text UI threshold, not the stricter text
 * threshold), small text-on-light needs 4.5:1.
 *
 * @since 0.2.0
 *
 * @param array $brand {
 *     @type string $primary   Primary/CTA color.
 *     @type string $accent    Accent color.
 *     @type string $dark      Dark/branded background color.
 *     @type string $neutral   Neutral/light-gray supporting color.
 *     @type string $secondary Secondary/supporting color.
 * }
 * @return array Full token map, keys matching the CSS custom property
 *               names in tokens.css (without the --lvjcb-color- prefix).
 */
function lvjcb_derive_color_tokens( $brand ) {

	$white = '#FFFFFF';

	return array(
		'dark'             => $brand['dark'],
		'dark-2'           => lvjcb_mix_shade( $brand['dark'], 0.12 ),
		'primary-bright'   => $brand['primary'],
		'accent'           => $brand['accent'],
		'secondary'        => $brand['secondary'],
		'neutral'          => $brand['neutral'],
		'primary'          => lvjcb_ensure_contrast( $brand['primary'], $white, 4.5 ),
		'primary-deep'     => lvjcb_mix_shade( lvjcb_ensure_contrast( $brand['primary'], $white, 4.5 ), -0.2 ),
		'accent-deep'      => lvjcb_ensure_contrast( $brand['accent'], $white, 3.0 ),
		'secondary-deep'   => lvjcb_ensure_contrast( $brand['secondary'], $white, 4.5 ),
		'surface-alt'      => lvjcb_mix_shade( $brand['neutral'], 0.82 ),
		'border'           => lvjcb_mix_shade( $brand['neutral'], 0.55 ),
		'text-muted'       => lvjcb_mix_shade( $brand['dark'], 0.35 ),
		'surface'          => $white,
		'text'             => $brand['dark'],
		'text-on-dark'     => '#F5F7F9',
		'text-on-dark-muted' => $brand['neutral'],
	);
}

/**
 * Output the color portion of the design tokens as an inline
 * :root { ... } block, computed live from the current business config.
 * The static tokens.css keeps only the business-agnostic system values
 * (spacing, radius, shadow, typography scale, breakpoints) — colors are
 * the one token category that legitimately varies per business, so they
 * are generated here rather than hardcoded in a shared stylesheet.
 *
 * @since 0.2.0
 *
 * @return void
 */
function lvjcb_render_color_tokens() {

	$brand  = lvjcb_get_config( 'colors' );
	$tokens = lvjcb_derive_color_tokens( $brand );

	echo '<style id="lvjcb-color-tokens">:root{';
	foreach ( $tokens as $name => $value ) {
		printf( '--lvjcb-color-%s:%s;', esc_html( $name ), esc_html( $value ) );
	}
	echo '}</style>' . "\n";
}
add_action( 'wp_head', 'lvjcb_render_color_tokens', 2 );
