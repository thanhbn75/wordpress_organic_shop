<?php
/**
 * Merchant Field: WooCommerce Coupons
 *
 * @package Merchant
 * @since   2.2.5
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Merchant_Field_Wc_Coupons
 *
 * Renders a WooCommerce coupon selector with AJAX search.
 *
 * @since 2.2.5
 */
class Merchant_Field_Wc_Coupons extends Merchant_Abstract_Field {

	/**
	 * Render the wc-coupons field HTML output.
	 *
	 * @since 2.2.5
	 *
	 * @return void
	 */
	public function render() {
		$this->get_template_part();
	}

	/**
	 * Type-specific sanitization for the wc-coupons field.
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
