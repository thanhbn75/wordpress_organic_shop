<?php
/**
 * Merchant Field: Checkbox
 *
 * @package Merchant
 * @since   2.2.5
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Merchant_Field_Checkbox
 *
 * Renders a single checkbox toggle field.
 *
 * @since 2.2.5
 */
class Merchant_Field_Checkbox extends Merchant_Abstract_Field {

	/**
	 * Render the checkbox field HTML output.
	 *
	 * @since 2.2.5
	 *
	 * @return void
	 */
	public function render() {
		$this->get_template_part();
	}

	/**
	 * Type-specific sanitization for the checkbox field.
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
