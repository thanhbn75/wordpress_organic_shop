<?php
/**
 * Merchant Field: URL
 *
 * @package Merchant
 * @since   2.2.5
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Merchant_Field_Url
 *
 * Renders a URL input field with validation.
 *
 * @since 2.2.5
 */
class Merchant_Field_Url extends Merchant_Abstract_Field {

	/**
	 * Render the url field HTML output.
	 *
	 * @since 2.2.5
	 *
	 * @return void
	 */
	public function render() {
		$this->get_template_part();
	}

	/**
	 * Type-specific sanitization for the url field.
	 *
	 * @since 2.2.5
	 *
	 * @param mixed $value The raw submitted value.
	 *
	 * @return mixed The sanitized value.
	 */
	protected function sanitize_value( $value ) {
		return sanitize_url( $value );
	}
}
