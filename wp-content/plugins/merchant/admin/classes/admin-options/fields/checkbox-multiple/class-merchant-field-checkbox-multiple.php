<?php
/**
 * Merchant Field: Checkbox Multiple
 *
 * @package Merchant
 * @since   2.2.5
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Merchant_Field_Checkbox_Multiple
 *
 * Renders a group of checkboxes allowing multiple selections.
 *
 * @since 2.2.5
 */
class Merchant_Field_Checkbox_Multiple extends Merchant_Abstract_Field {

	/**
	 * Render the checkbox-multiple field HTML output.
	 *
	 * @since 2.2.5
	 *
	 * @return void
	 */
	public function render() {
		$this->get_template_part();
	}

	/**
	 * Type-specific sanitization for the checkbox-multiple field.
	 *
	 * @since 2.2.5
	 *
	 * @param mixed $value The raw submitted value.
	 *
	 * @return mixed The sanitized value.
	 */
	protected function sanitize_value( $value ) {
		if ( ! is_array( $value ) ) {
			return array();
		}

		return array_filter( array_map( 'sanitize_text_field', $value ) );
	}
}
