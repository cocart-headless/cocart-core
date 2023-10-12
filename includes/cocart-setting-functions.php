<?php
/**
 * CoCart Setting Functions.
 *
 * Functions for the settings.
 *
 * @author  Sébastien Dumont
 * @package CoCart\Functions
 * @since   4.0.0 Introduced.
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Update a CoCart setting value.
 *
 * This bypasses any validation the settings page normally does but the
 * setting will be validated again if the settings page has been updated.
 *
 * @link https://developer.wordpress.org/reference/functions/update_option/
 *
 * @param string $name  The setting name.
 * @param mixed  $value The setting value.
 *
 * @return void
 */
function cocart_update_setting( $section, $name, $value ) {
	$settings                      = cocart_get_settings();
	$settings[ $section ][ $name ] = $value;

	update_option( 'cocart_settings', $settings );
} // END cocart_update_setting()

/**
 * Get all CoCart settings or a section of the settings.
 *
 * @param string $section The section of settings requested.
 *
 * @return array An array of settings.
 */
function cocart_get_settings( $section = '' ) {
	$settings = get_option( 'cocart_settings', array() );

	$settings = ! empty( $section ) && isset( $settings[ $section ] ) ? $settings[ $section ] : $settings;

	return $settings;
} // END cocart_get_settings()

/**
 * Get a CoCart setting by name.
 *
 * @param string $section The section the setting is under.
 * @param string $name    The setting name.
 * @param mixed  $default Optional setting value. Default false.
 *
 * @return mixed The setting value.
 */
function cocart_get_setting( $section, $name, $default = false ) {
	$value    = $default;
	$settings = cocart_get_settings( $section );

	if ( isset( $settings[ $name ] ) ) {
		$value = $settings[ $name ];
	}

	return $value;
} // END cocart_get_setting()
