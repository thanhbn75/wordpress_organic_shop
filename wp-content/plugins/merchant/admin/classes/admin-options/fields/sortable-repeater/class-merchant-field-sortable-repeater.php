<?php
/**
 * Merchant Field: Sortable Repeater
 *
 * @package Merchant
 * @since   2.2.5
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Merchant_Field_Sortable_Repeater
 *
 * Renders a repeatable, sortable list of text inputs.
 *
 * @since 2.2.5
 */
class Merchant_Field_Sortable_Repeater extends Merchant_Abstract_Field {

	/**
	 * Render the sortable-repeater field HTML output.
	 *
	 * @since 2.2.5
	 *
	 * @return void
	 */
	public function render() {
		$this->get_template_part();
	}

	/**
	 * Type-specific sanitization for the sortable-repeater field.
	 *
	 * @since 2.2.5
	 *
	 * @param mixed $value The raw submitted value.
	 *
	 * @return mixed The sanitized value.
	 */
	protected function sanitize_value( $value ) {
		if ( is_string( $value ) ) {
			$value = json_decode( $value, true );
		}

		return is_array( $value ) ? array_map( 'sanitize_text_field', $value ) : array();
	}
}
