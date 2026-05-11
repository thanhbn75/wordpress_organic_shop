<?php
/**
 * Merchant Field: Select Size Chart
 *
 * @package Merchant
 * @since   2.2.5
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Merchant_Field_Select_Size_Chart
 *
 * Renders a size chart selector dropdown.
 *
 * @since 2.2.5
 */
class Merchant_Field_Select_Size_Chart extends Merchant_Abstract_Field {

	/**
	 * Render the select-size-chart field HTML output.
	 *
	 * @since 2.2.5
	 *
	 * @return void
	 */
	public function render() {
		$this->get_template_part();
	}

	/**
	 * Type-specific sanitization for the select-size-chart field.
	 *
	 * @since 2.2.5
	 *
	 * @param mixed $value The raw submitted value.
	 *
	 * @return mixed The sanitized value.
	 */
	protected function sanitize_value( $value ) {
		return absint( $value );
	}
}
