<?php
/**
 * Merchant Field: Hook Select
 *
 * @package Merchant
 * @since   2.2.5
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Merchant_Field_Hook_Select
 *
 * Renders a dropdown select paired with a priority number input.
 *
 * @since 2.2.5
 */
class Merchant_Field_Hook_Select extends Merchant_Abstract_Field {

	/**
	 * Render the hook-select field HTML output.
	 *
	 * @since 2.2.5
	 *
	 * @return void
	 */
	public function render() {
		$this->get_template_part();
	}

	/**
	 * Type-specific sanitization for the hook-select field.
	 *
	 * @since 2.2.5
	 *
	 * @param mixed $value The raw submitted value.
	 *
	 * @return mixed The sanitized value.
	 */
	protected function sanitize_value( $value ) {
		if ( ! is_array( $value ) ) {
			return array();
		}

		return array(
			'hook_name'     => sanitize_key( $value['hook_name'] ?? '' ),
			'hook_priority' => absint( $value['hook_priority'] ?? 10 ),
		);
	}
}
