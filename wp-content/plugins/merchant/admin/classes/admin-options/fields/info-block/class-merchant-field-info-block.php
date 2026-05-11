<?php
/**
 * Merchant Field: Info Block
 *
 * @package Merchant
 * @since   2.2.5
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Merchant_Field_Info_Block
 *
 * Renders a styled informational callout block.
 *
 * @since 2.2.5
 */
class Merchant_Field_Info_Block extends Merchant_Abstract_Field {

	/**
	 * Render the info-block field HTML output.
	 *
	 * @since 2.2.5
	 *
	 * @return void
	 */
	public function render() {
		$this->get_template_part();
	}
}
