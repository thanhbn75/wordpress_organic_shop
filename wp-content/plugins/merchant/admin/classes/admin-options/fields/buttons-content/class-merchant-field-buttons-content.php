<?php
/**
 * Merchant Field: Buttons Content
 *
 * @package Merchant
 * @since   2.2.5
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Merchant_Field_Buttons_Content
 *
 * Renders buttons with content labels for visual selection.
 *
 * @since 2.2.5
 */
class Merchant_Field_Buttons_Content extends Merchant_Abstract_Field {

	/**
	 * Render the buttons-content field HTML output.
	 *
	 * @since 2.2.5
	 *
	 * @return void
	 */
	public function render() {
		$this->get_template_part();
	}

	/**
	 * Type-specific sanitization for the buttons-content field.
	 *
	 * @since 2.2.5
	 *
	 * @param mixed $value The raw submitted value.
	 *
	 * @return mixed The sanitized value.
	 */
	protected function sanitize_value( $value ) {
		return sanitize_key( $value );
	}
}
