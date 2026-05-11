<?php
/**
 * Merchant Field: Radio Alt
 *
 * @package Merchant
 * @since   2.2.5
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Merchant_Field_Radio_Alt
 *
 * Renders alternate-style radio buttons with descriptions.
 *
 * @since 2.2.5
 */
class Merchant_Field_Radio_Alt extends Merchant_Abstract_Field {

	/**
	 * Render the radio-alt field HTML output.
	 *
	 * @since 2.2.5
	 *
	 * @return void
	 */
	public function render() {
		$this->get_template_part();
	}

	/**
	 * Type-specific sanitization for the radio-alt field.
	 *
	 * @since 2.2.5
	 *
	 * @param mixed $value The raw submitted value.
	 *
	 * @return mixed The sanitized value.
	 */
	protected function sanitize_value( $value ) {
		return sanitize_key( $value );
	}
}
