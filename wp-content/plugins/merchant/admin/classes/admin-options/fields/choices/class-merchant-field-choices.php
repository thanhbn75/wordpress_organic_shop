<?php
/**
 * Merchant Field: Choices
 *
 * @package Merchant
 * @since   2.2.5
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Merchant_Field_Choices
 *
 * Renders a radio-like visual choices selector with images.
 *
 * @since 2.2.5
 */
class Merchant_Field_Choices extends Merchant_Abstract_Field {

	/**
	 * Render the choices field HTML output.
	 *
	 * @since 2.2.5
	 *
	 * @return void
	 */
	public function render() {
		$this->get_template_part();
	}

	/**
	 * Type-specific sanitization for the choices field.
	 *
	 * @since 2.2.5
	 *
	 * @param mixed $value The raw submitted value.
	 *
	 * @return mixed The sanitized value.
	 */
	protected function sanitize_value( $value ) {
		if ( is_array( $value ) ) {
			return array_filter( array_map( 'sanitize_text_field', $value ) );
		}

		$options = $this->field['options'] ?? array();
		return ( in_array( $value, array_keys( $options ), true ) ) ? sanitize_key( (string) $value ) : '';
	}
}
