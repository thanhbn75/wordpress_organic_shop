<?php
/**
 * Merchant Field: Image Picker
 *
 * @package Merchant
 * @since   2.2.5
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Merchant_Field_Image_Picker
 *
 * Renders a visual image-based option picker (radio style).
 *
 * @since 2.2.5
 */
class Merchant_Field_Image_Picker extends Merchant_Abstract_Field {

	/**
	 * Render the image-picker field HTML output.
	 *
	 * @since 2.2.5
	 *
	 * @return void
	 */
	public function render() {
		$this->get_template_part();
	}

	/**
	 * Type-specific sanitization for the image-picker field.
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
