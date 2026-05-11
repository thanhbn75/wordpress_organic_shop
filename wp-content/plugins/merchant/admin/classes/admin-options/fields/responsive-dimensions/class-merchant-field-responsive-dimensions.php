<?php
/**
 * Merchant Field: Responsive Dimensions
 *
 * @package Merchant
 * @since   2.2.5
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Merchant_Field_Responsive_Dimensions
 *
 * Renders responsive dimension inputs for desktop/tablet/mobile.
 *
 * @since 2.2.5
 */
class Merchant_Field_Responsive_Dimensions extends Merchant_Abstract_Field {

	/**
	 * Render the responsive-dimensions field HTML output.
	 *
	 * @since 2.2.5
	 *
	 * @return void
	 */
	public function render() {
		$this->get_template_part();
	}

	/**
	 * Type-specific sanitization for the responsive-dimensions field.
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

		$sanitized = array();
		foreach ( $value as $device => $dims ) {
			$sanitized[ sanitize_key( $device ) ] = array(
				'unit' => sanitize_key( $dims['unit'] ?? 'px' ),
			);
			foreach ( array( 'top', 'right', 'bottom', 'left' ) as $dim ) {
				if ( isset( $dims[ $dim ] ) ) {
					$sanitized[ sanitize_key( $device ) ][ $dim ] = floatval( $dims[ $dim ] );
				}
			}
		}

		return $sanitized;
	}
}
