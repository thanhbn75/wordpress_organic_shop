<?php
/**
 * Merchant Field: Textarea Code Snippet
 *
 * @package Merchant
 * @since   2.2.5
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Merchant_Field_Textarea_Code
 *
 * Renders a textarea styled for code input.
 *
 * @since 2.2.5
 */
class Merchant_Field_Textarea_Code extends Merchant_Abstract_Field {

	/**
	 * Render the textarea-code field HTML output.
	 *
	 * @since 2.2.5
	 *
	 * @return void
	 */
	public function render() {
		$this->get_template_part();
	}

	/**
	 * Type-specific sanitization for the textarea-code field.
	 *
	 * @since 2.2.5
	 *
	 * @param mixed $value The raw submitted value.
	 *
	 * @return mixed The sanitized value.
	 */
	protected function sanitize_value( $value ) {
		return wp_kses_post( $value );
	}

	/**
	 * Preprocess the raw POST value before sanitization.
	 *
	 * Data is already unslashed at the call site.
	 *
	 * @param mixed $value The raw submitted value (already unslashed).
	 *
	 * @return mixed The preprocessed value.
	 */
	public function preprocess( $value ) {
		return trim( wp_kses( $value, merchant_kses_allowed_tags_for_code_snippets() ) );
	}
}
