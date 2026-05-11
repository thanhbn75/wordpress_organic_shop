<?php
/**
 * Merchant Field: Buttons Alt
 *
 * @package Merchant
 * @since   2.2.5
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Merchant_Field_Buttons_Alt
 *
 * Renders alternate-style toggle buttons with icon support.
 *
 * @since 2.2.5
 */
class Merchant_Field_Buttons_Alt extends Merchant_Abstract_Field {

	/**
	 * Render the buttons-alt field HTML output.
	 *
	 * @since 2.2.5
	 *
	 * @return void
	 */
	public function render() {
		$this->get_template_part();
	}

	/**
	 * Type-specific sanitization for the buttons-alt field.
	 *
	 * @since 2.2.5
	 *
	 * @param mixed $value The raw submitted value.
	 *
	 * @return mixed The sanitized value.
	 */
	protected function sanitize_value( $value ) {
		return sanitize_key( $value );
	}
}
