<?php
/**
 * Merchant Field: Info
 *
 * @package Merchant
 * @since   2.2.5
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Merchant_Field_Info
 *
 * Renders an informational text block (non-interactive).
 *
 * @since 2.2.5
 */
class Merchant_Field_Info extends Merchant_Abstract_Field {

	/**
	 * Render the info field HTML output.
	 *
	 * @since 2.2.5
	 *
	 * @return void
	 */
	public function render() {
		echo wp_kses_post( $this->field['content'] );
	}
}
