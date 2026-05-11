<?php
/**
 * Merchant Field: Products Selector
 *
 * A searchable, multi-select product picker that queries WooCommerce products.
 *
 * @package Merchant
 * @since   2.2.5
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Merchant_Field_Products_Selector
 *
 * Renders a searchable WooCommerce product selector with AJAX search.
 *
 * @since 2.2.5
 */
class Merchant_Field_Products_Selector extends Merchant_Abstract_Field {

	/**
	 * Render the products-selector field HTML output.
	 *
	 * @since 2.2.5
	 *
	 * @return void
	 */
	public function render() {
		$this->get_template_part();
	}

	/**
	 * Type-specific sanitization for the products-selector field.
	 *
	 * @since 2.2.5
	 *
	 * @param mixed $value The raw submitted value.
	 *
	 * @return mixed The sanitized value.
	 */
	protected function sanitize_value( $value ) {
		if ( empty( $value ) ) {
			return '';
		}

		$ids = explode( ',', $value );

		return implode( ',', array_map( 'absint', array_filter( $ids ) ) );
	}
}
