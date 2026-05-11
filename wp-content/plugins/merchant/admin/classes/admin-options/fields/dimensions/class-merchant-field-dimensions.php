<?php
/**
 * Merchant Field: Dimensions
 *
 * @package Merchant
 * @since   2.2.5
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Merchant_Field_Dimensions
 *
 * Renders top/right/bottom/left dimension inputs with linked toggle.
 *
 * @since 2.2.5
 */
class Merchant_Field_Dimensions extends Merchant_Abstract_Field {

	/**
	 * Render the dimensions field HTML output.
	 *
	 * @since 2.2.5
	 *
	 * @return void
	 */
	public function render() {
		$this->get_template_part();
	}

	/**
	 * Type-specific sanitization for the dimensions field.
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

		$sanitized = array(
			'unit' => sanitize_key( $value['unit'] ?? 'px' ),
		);

		foreach ( array( 'top', 'right', 'bottom', 'left' ) as $dim ) {
			if ( isset( $value[ $dim ] ) ) {
				$sanitized[ $dim ] = floatval( $value[ $dim ] );
			}
		}

		return $sanitized;
	}
}
