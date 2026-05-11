<?php
/**
 * Merchant Field: Gallery
 *
 * @package Merchant
 * @since   2.2.5
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Merchant_Field_Gallery
 *
 * Renders a WordPress media gallery picker for multiple images.
 *
 * @since 2.2.5
 */
class Merchant_Field_Gallery extends Merchant_Abstract_Field {

	/**
	 * Render the gallery field HTML output.
	 *
	 * @since 2.2.5
	 *
	 * @return void
	 */
	public function render() {
		$this->get_template_part();
	}

	/**
	 * Type-specific sanitization for the gallery field.
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
