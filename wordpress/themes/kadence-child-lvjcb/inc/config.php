<?php
/**
 * Business Config Loader
 *
 * The single entry point every other file uses to read business data.
 * Nothing outside this file and business-config.php should know where
 * the data physically lives — this is what makes cloning the theme for
 * a new business a one-file change instead of a code-wide find/replace.
 *
 * @package LVJCB
 * @since   0.2.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Get the business configuration, or one key from it.
 *
 * @since 0.2.0
 *
 * @param string|null $key Optional. A top-level config key to return
 *                         directly. Omit to get the full config array.
 * @return mixed The full config array, a single key's value, or null
 *               if the requested key doesn't exist.
 */
function lvjcb_get_config( $key = null ) {

	static $config = null;

	if ( null === $config ) {
		$config = require LVJCB_INC_DIR . '/business-config.php';
	}

	if ( null === $key ) {
		return $config;
	}

	return $config[ $key ] ?? null;
}
