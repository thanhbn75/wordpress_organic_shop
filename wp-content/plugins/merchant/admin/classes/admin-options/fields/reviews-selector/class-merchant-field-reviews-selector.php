<?php
/**
 * Merchant Field: Reviews Selector
 *
 * A searchable selector that lets users pick specific WooCommerce product reviews.
 * Helper methods remain on Merchant_Admin_Options since AJAX handlers also use them.
 *
 * @package Merchant
 * @since   2.2.5
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Merchant_Field_Reviews_Selector
 *
 * Renders a WooCommerce product reviews selector with AJAX search.
 *
 * @since 2.2.5
 */
class Merchant_Field_Reviews_Selector extends Merchant_Abstract_Field {

	/**
	 * Render the reviews-selector field HTML output.
	 *
	 * @since 2.2.5
	 *
	 * @return void
	 */
	public function render() {
		$this->get_template_part();
	}

	/**
	 * Type-specific sanitization for the reviews-selector field.
	 *
	 * @since 2.2.5
	 *
	 * @param mixed $value The raw submitted value.
	 *
	 * @return mixed The sanitized value.
	 */
	protected function sanitize_value( $value ) {
		if ( empty( $value ) ) {
			return array();
		}

		if ( is_string( $value ) ) {
			$value = explode( ',', $value );
		}

		return array_map( 'absint', array_filter( $value ) );
	}

	/**
	 * Preprocess the raw POST value before sanitization.
	 *
	 * @param mixed $value The raw submitted value.
	 *
	 * @return mixed The preprocessed value.
	 */
	public function preprocess( $value ) {
		$value = sanitize_textarea_field( $value );

		return explode( ',', $value );
	}
}
