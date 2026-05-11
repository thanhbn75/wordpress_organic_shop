<?php
/**
 * Merchant Field: Textarea
 *
 * @package Merchant
 * @since   2.2.5
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Merchant_Field_Textarea
 *
 * Renders a standard textarea input field.
 *
 * @since 2.2.5
 */
class Merchant_Field_Textarea extends Merchant_Abstract_Field {

	/**
	 * Render the textarea field HTML output.
	 *
	 * @since 2.2.5
	 *
	 * @return void
	 */
	public function render() {
		$this->get_template_part();
	}

	/**
	 * Type-specific sanitization for the textarea field.
	 *
	 * @since 2.2.5
	 *
	 * @param mixed $value The raw submitted value.
	 *
	 * @return mixed The sanitized value.
	 */
	protected function sanitize_value( $value ) {
		return sanitize_textarea_field( $value );
	}
}
