<?php
/**
 * Merchant Field: Text
 *
 * @package Merchant
 * @since   2.2.5
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Merchant_Field_Text
 *
 * @since 2.2.5
 */
class Merchant_Field_Text extends Merchant_Abstract_Field {

	/**
	 * Render the text field.
	 *
	 * @since 2.2.5
	 * @return void
	 */
	public function render() {
		$this->get_template_part();
	}

	/**
	 * Sanitize the text field value.
	 *
	 * @since 2.2.5
	 *
	 * @param mixed $value The raw submitted value.
	 *
	 * @return string
	 */
	protected function sanitize_value( $value ) {
		return sanitize_text_field( $value );
	}
}
