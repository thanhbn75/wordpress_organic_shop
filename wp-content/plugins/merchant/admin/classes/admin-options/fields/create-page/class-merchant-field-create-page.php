<?php
/**
 * Merchant Field: Create Page
 *
 * @package Merchant
 * @since   2.2.5
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Merchant_Field_Create_Page
 *
 * Renders a button to create a new WordPress page via AJAX.
 *
 * @since 2.2.5
 */
class Merchant_Field_Create_Page extends Merchant_Abstract_Field {

	/**
	 * Render the create-page field HTML output.
	 *
	 * @since 2.2.5
	 *
	 * @return void
	 */
	public function render() {
		$this->get_template_part();
	}
}
