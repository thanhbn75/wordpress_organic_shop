<?php
/**
 * Merchant Field: Warning
 *
 * @package Merchant
 * @since   2.2.5
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Merchant_Field_Warning
 *
 * Renders a warning/notice message block.
 *
 * @since 2.2.5
 */
class Merchant_Field_Warning extends Merchant_Abstract_Field {

	/**
	 * Render the warning field HTML output.
	 *
	 * @since 2.2.5
	 *
	 * @return void
	 */
	public function render() {
		echo wp_kses_post( $this->field['content'] );
	}
}
