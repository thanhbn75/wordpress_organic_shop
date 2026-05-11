<?php
/**
 * Merchant Field: Select
 *
 * @package Merchant
 * @since   2.2.5
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Merchant_Field_Select
 *
 * @since 2.2.5
 */
class Merchant_Field_Select extends Merchant_Abstract_Field {

	/**
	 * Render the select field.
	 *
	 * @since 2.2.5
	 * @return void
	 */
	public function render() {
		$this->get_template_part();
	}

	/**
	 * Sanitize the select field value.
	 *
	 * @since 2.2.5
	 *
	 * @param mixed $value The raw submitted value.
	 *
	 * @return string
	 */
	protected function sanitize_value( $value ) {
		$options = isset( $this->field['options'] ) ? $this->field['options'] : array();

		return ( in_array( $value, array_map( 'strval', array_keys( $options ) ), true ) ) ? sanitize_key( $value ) : '';
	}
}
