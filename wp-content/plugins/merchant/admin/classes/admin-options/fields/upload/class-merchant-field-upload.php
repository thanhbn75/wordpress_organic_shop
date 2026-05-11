<?php
/**
 * Merchant Field: Upload
 *
 * @package Merchant
 * @since   2.2.5
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Merchant_Field_Upload
 *
 * Renders a WordPress media uploader for file/image selection.
 *
 * @since 2.2.5
 */
class Merchant_Field_Upload extends Merchant_Abstract_Field {

	/**
	 * Render the upload field HTML output.
	 *
	 * @since 2.2.5
	 *
	 * @return void
	 */
	public function render() {
		$this->get_template_part();
	}

	/**
	 * Type-specific sanitization for the upload field.
	 *
	 * @since 2.2.5
	 *
	 * @param mixed $value The raw submitted value.
	 *
	 * @return mixed The sanitized value.
	 */
	protected function sanitize_value( $value ) {
		return absint( $value );
	}
}
