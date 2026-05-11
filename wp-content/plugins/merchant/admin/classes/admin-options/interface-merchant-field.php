<?php
/**
 * Merchant Field Interface.
 *
 * Defines the contract that all field types must follow.
 *
 * @package Merchant
 * @since   2.2.5
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

/**
 * Merchant_Field_Interface
 *
 * @since 2.2.5
 */
interface Merchant_Field_Interface {

	/**
	 * Render the field HTML output.
	 *
	 * @since 2.2.5
	 * @return void
	 */
	public function render();

	/**
	 * Sanitize the submitted field value.
	 *
	 * @since 2.2.5
	 *
	 * @param mixed $value The raw submitted value.
	 *
	 * @return mixed The sanitized value.
	 */
	public function sanitize( $value );

	/**
	 * Preprocess the field value before saving.
	 *
	 * @since 2.2.5
	 *
	 * @param mixed $value The sanitized value.
	 *
	 * @return mixed The preprocessed value.
	 */
	public function preprocess( $value );
}
