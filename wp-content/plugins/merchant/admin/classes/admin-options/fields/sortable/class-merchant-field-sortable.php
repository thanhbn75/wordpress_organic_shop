<?php
/**
 * Merchant Field: Sortable
 *
 * @package Merchant
 * @since   2.2.5
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Merchant_Field_Sortable
 *
 * Renders a sortable, toggleable list of predefined options.
 *
 * @since 2.2.5
 */
class Merchant_Field_Sortable extends Merchant_Abstract_Field {

	/**
	 * Render the sortable field HTML output.
	 *
	 * @since 2.2.5
	 *
	 * @return void
	 */
	public function render() {
		$this->get_template_part();
	}

	/**
	 * Type-specific sanitization for the sortable field.
	 *
	 * @since 2.2.5
	 *
	 * @param mixed $value The raw submitted value.
	 *
	 * @return mixed The sanitized value.
	 */
	protected function sanitize_value( $value ) {
		if ( is_string( $value ) ) {
			$value = json_decode( $value, true );
		}

		return is_array( $value ) ? array_map( 'sanitize_key', $value ) : array();
	}
}
