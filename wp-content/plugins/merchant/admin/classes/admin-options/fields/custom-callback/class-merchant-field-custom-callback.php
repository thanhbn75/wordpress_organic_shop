<?php
/**
 * Merchant Field: Custom Callback
 *
 * @package Merchant
 * @since   2.2.5
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Merchant_Field_Custom_Callback
 *
 * Delegates rendering to a custom user-defined callback function.
 *
 * @since 2.2.5
 */
class Merchant_Field_Custom_Callback extends Merchant_Abstract_Field {

	/**
	 * Render the custom-callback field HTML output.
	 *
	 * @since 2.2.5
	 *
	 * @return void
	 */
	public function render() {
		$settings = $this->field;
		$value    = $this->value;

		if ( ! empty( $settings['class_name'] ) && ! empty( $settings['callback_name'] ) ) {
			/** @var callable $callback */
			$callback = array( $settings['class_name'], $settings['callback_name'] );
			call_user_func( $callback, $settings, $value );
			return;
		}

		if ( ! empty( $settings['callback_name'] ) ) {
			call_user_func( $settings['callback_name'], $settings, $value );
		}
	}
}
