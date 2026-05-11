<?php
/**
 * Merchant Field: Color
 *
 * @package Merchant
 * @since   2.2.5
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Merchant_Field_Color
 *
 * Renders a simple color input field.
 *
 * @since 2.2.5
 */
class Merchant_Field_Color extends Merchant_Abstract_Field {

	/**
	 * Render the color field HTML output.
	 *
	 * @since 2.2.5
	 *
	 * @return void
	 */
	public function render() {
		$this->get_template_part();
	}

	/**
	 * Type-specific sanitization for the color field.
	 *
	 * @since 2.2.5
	 *
	 * @param mixed $value The raw submitted value.
	 *
	 * @return mixed The sanitized value.
	 */
	protected function sanitize_value( $value ) {
		if ( ! is_string( $value ) ) {
			return '';
		}

		$value = trim( $value );

		// Hex: #RGB, #RRGGBB, #RRGGBBAA.
		if ( preg_match( '/^#([A-Fa-f0-9]{3,4}){1,2}$/', $value ) ) {
			return $value;
		}

		// rgba(R, G, B, A) or rgb(R, G, B).
		if ( preg_match( '/^rgba?\(\s*\d{1,3}\s*,\s*\d{1,3}\s*,\s*\d{1,3}\s*(,\s*(0|1|0?\.\d+)\s*)?\)$/', $value ) ) {
			return $value;
		}

		// hsla(H, S%, L%, A) or hsl(H, S%, L%).
		if ( preg_match( '/^hsla?\(\s*\d{1,3}\s*,\s*\d{1,3}%\s*,\s*\d{1,3}%\s*(,\s*(0|1|0?\.\d+)\s*)?\)$/', $value ) ) {
			return $value;
		}

		// Fallback: sanitize as plain text (matches original behavior).
		return sanitize_text_field( $value );
	}
}
