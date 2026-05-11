<?php
/**
 * Merchant Field: Textarea Multiline
 *
 * @package Merchant
 * @since   2.2.5
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Merchant_Field_Textarea_Multiline
 *
 * Renders a textarea for multi-line text with line-based storage.
 *
 * @since 2.2.5
 */
class Merchant_Field_Textarea_Multiline extends Merchant_Abstract_Field {

	/**
	 * Render the textarea-multiline field HTML output.
	 *
	 * @since 2.2.5
	 *
	 * @return void
	 */
	public function render() {
		$this->get_template_part();
	}

	/**
	 * Type-specific sanitization for the textarea-multiline field.
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

	/**
	 * Preprocess the raw POST value before sanitization.
	 *
	 * @param mixed $value The raw submitted value.
	 *
	 * @return mixed The preprocessed value.
	 */
	public function preprocess( $value ) {
		return sanitize_textarea_field( $value );
	}
}
