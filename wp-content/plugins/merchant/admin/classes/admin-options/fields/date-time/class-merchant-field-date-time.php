<?php
/**
 * Merchant Field: Date Time
 *
 * @package Merchant
 * @since   2.2.5
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Merchant_Field_Date_Time
 *
 * Renders a date/time picker powered by air-datepicker.
 *
 * @since 2.2.5
 */
class Merchant_Field_Date_Time extends Merchant_Abstract_Field {

	/**
	 * Render the date-time field HTML output.
	 *
	 * @since 2.2.5
	 *
	 * @return void
	 */
	public function render() {
		$this->get_template_part();
	}

	/**
	 * Type-specific sanitization for the date-time field.
	 *
	 * @since 2.2.5
	 *
	 * @param mixed $value The raw submitted value.
	 *
	 * @return mixed The sanitized value.
	 */
	protected function sanitize_value( $value ) {
		return sanitize_text_field( $value );
	}
}
