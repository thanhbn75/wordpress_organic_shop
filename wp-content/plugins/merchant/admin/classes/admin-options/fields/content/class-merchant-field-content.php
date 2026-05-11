<?php
/**
 * Merchant Field: Content
 *
 * @package Merchant
 * @since   2.2.5
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Merchant_Field_Content
 *
 * Renders static HTML content within the settings panel.
 *
 * @since 2.2.5
 */
class Merchant_Field_Content extends Merchant_Abstract_Field {

	/**
	 * Render the content field HTML output.
	 *
	 * @since 2.2.5
	 *
	 * @return void
	 */
	public function render() {
		echo wp_kses_post( $this->field['content'] );
	}
}
