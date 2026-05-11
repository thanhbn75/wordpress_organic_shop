<?php
/**
 * Merchant Field: Radio
 *
 * @package Merchant
 * @since   2.2.5
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Merchant_Field_Radio
 *
 * Renders standard radio button options.
 *
 * @since 2.2.5
 */
class Merchant_Field_Radio extends Merchant_Abstract_Field {

	/**
	 * Render the radio field HTML output.
	 *
	 * @since 2.2.5
	 *
	 * @return void
	 */
	public function render() {
		$this->get_template_part();
	}

	/**
	 * Type-specific sanitization for the radio field.
	 *
	 * @since 2.2.5
	 *
	 * @param mixed $value The raw submitted value.
	 *
	 * @return mixed The sanitized value.
	 */
	protected function sanitize_value( $value ) {
		$options = isset( $this->field['options'] ) ? $this->field['options'] : array();

		return ( in_array( $value, array_map( 'strval', array_keys( $options ) ), true ) ) ? sanitize_key( $value ) : '';
	}
}
