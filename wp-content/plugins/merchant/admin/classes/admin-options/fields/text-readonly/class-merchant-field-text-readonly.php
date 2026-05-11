<?php
/**
 * Merchant Field: Text Readonly
 *
 * @package Merchant
 * @since   2.2.5
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Merchant_Field_Text_Readonly
 *
 * Renders a read-only text display with copy-to-clipboard support.
 *
 * @since 2.2.5
 */
class Merchant_Field_Text_Readonly extends Merchant_Abstract_Field {

	/**
	 * Render the text-readonly field HTML output.
	 *
	 * @since 2.2.5
	 *
	 * @return void
	 */
	public function render() {
		$this->get_template_part();
	}

	/**
	 * Type-specific sanitization for the text-readonly field.
	 *
	 * @since 2.2.5
	 *
	 * @param mixed $value The raw submitted value.
	 *
	 * @return mixed The sanitized value.
	 */
	protected function sanitize_value( $value ) {
		return sanitize_text_field( $value );
	}
}
