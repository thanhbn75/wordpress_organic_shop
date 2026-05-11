<?php
/**
 * Merchant Field: Range
 *
 * @package Merchant
 * @since   2.2.5
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Merchant_Field_Range
 *
 * Renders a range slider input with numeric display.
 *
 * @since 2.2.5
 */
class Merchant_Field_Range extends Merchant_Abstract_Field {

	/**
	 * Render the range field HTML output.
	 *
	 * @since 2.2.5
	 *
	 * @return void
	 */
	public function render() {
		$this->get_template_part();
	}

	/**
	 * Type-specific sanitization for the range field.
	 *
	 * @since 2.2.5
	 *
	 * @param mixed $value The raw submitted value.
	 *
	 * @return mixed The sanitized value.
	 */
	protected function sanitize_value( $value ) {
		return floatval( $value );
	}
}
