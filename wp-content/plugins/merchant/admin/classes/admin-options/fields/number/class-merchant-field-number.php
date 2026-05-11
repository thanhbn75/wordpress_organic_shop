<?php
/**
 * Merchant Field: Number
 *
 * @package Merchant
 * @since   2.2.5
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Merchant_Field_Number
 *
 * @since 2.2.5
 */
class Merchant_Field_Number extends Merchant_Abstract_Field {

	/**
	 * Render the number field.
	 *
	 * @since 2.2.5
	 * @return void
	 */
	public function render() {
		$this->get_template_part();
	}

	/**
	 * Sanitize the number field value.
	 *
	 * @since 2.2.5
	 *
	 * @param mixed $value The raw submitted value.
	 *
	 * @return int
	 */
	protected function sanitize_value( $value ) {
		return absint( $value );
	}
}
