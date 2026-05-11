<?php
/**
 * Merchant Field: Divider
 *
 * @package Merchant
 * @since   2.2.5
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Merchant_Field_Divider
 *
 * Renders a horizontal divider between settings fields.
 *
 * @since 2.2.5
 */
class Merchant_Field_Divider extends Merchant_Abstract_Field {

	/**
	 * Render the divider field HTML output.
	 *
	 * @since 2.2.5
	 *
	 * @return void
	 */
	public function render() {
		// Divider is purely structural — no output needed.
	}
}
