<?php
/**
 * Merchant Admin Assets.
 *
 * Handles enqueueing of admin scripts and styles for module
 * settings pages. Extracted from {@see Merchant_Admin_Options}
 * to isolate asset management concerns.
 *
 * @package Merchant
 * @since   1.9.3
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Merchant_Admin_Assets
 *
 * Enqueues Select2, air-datepicker, and their localized strings
 * when viewing a Merchant module settings page.
 *
 * @since 1.9.3
 */
class Merchant_Admin_Assets {

	/**
	 * Enqueue admin scripts and styles for module settings pages.
	 *
	 * Delegates to dedicated helpers for each asset group when on
	 * a Merchant module settings page.
	 *
	 * @since 1.0
	 *
	 * @return void
	 */
	public function enqueue_scripts() {
		if ( ! $this->is_module_settings_page() ) {
			return;
		}

		$this->enqueue_select2_assets();
		$this->enqueue_datepicker_assets();
	}

	/**
	 * Check if the current admin page is a Merchant module settings page.
	 *
	 * @since 1.9.3
	 *
	 * @return bool True if on a module settings page.
	 */
	private function is_module_settings_page() {
		return isset( $_GET['page'] ) // phpcs:ignore WordPress.Security.NonceVerification.Recommended
			&& 'merchant' === $_GET['page'] // phpcs:ignore WordPress.Security.NonceVerification.Recommended
			&& isset( $_GET['module'] ); // phpcs:ignore WordPress.Security.NonceVerification.Recommended
	}

	/**
	 * Enqueue Select2 scripts, styles, and localized strings.
	 *
	 * @since 1.9.3
	 *
	 * @return void
	 */
	private function enqueue_select2_assets() {
		wp_enqueue_script( 'merchant-select2', MERCHANT_URI . 'assets/vendor/select2/select2.full.min.js', array( 'jquery' ), '4.0.13', true );
		wp_enqueue_style( 'merchant-select2', MERCHANT_URI . 'assets/vendor/select2/select2.min.css', array(), '4.0.13', 'all' );

		wp_localize_script( 'merchant-select2', 'merchant_admin_options', array(
			'ajaxurl'                             => admin_url( 'admin-ajax.php' ),
			'ajaxnonce'                           => wp_create_nonce( 'merchant_admin_options' ),
			'product_delete_confirmation_message' => esc_html__( 'Are you sure you want to remove this product?', 'merchant' ),
			'invalid_file'                        => esc_html__( 'Invalid file', 'merchant' ),
		) );
	}

	/**
	 * Enqueue air-datepicker scripts, styles, and locale strings.
	 *
	 * @since 1.9.3
	 *
	 * @return void
	 */
	private function enqueue_datepicker_assets() {
		wp_enqueue_style( 'date-picker', MERCHANT_URI . 'assets/vendor/air-datepicker/air-datepicker.css', array(), MERCHANT_VERSION, 'all' );
		wp_enqueue_script( 'date-picker', MERCHANT_URI . 'assets/vendor/air-datepicker/air-datepicker.js', array( 'jquery' ), MERCHANT_VERSION, true );
		wp_localize_script( 'date-picker', 'merchant_datepicker_locale', array(
			wp_json_encode( $this->get_datepicker_locale() ),
		) );
	}

	/**
	 * Get the datepicker localization data.
	 *
	 * Returns translated day names, month names, and labels
	 * for the air-datepicker library.
	 *
	 * @since 1.9.3
	 *
	 * @return array<string, mixed> Datepicker locale strings.
	 */
	private function get_datepicker_locale() {
		return array(
			'days'        => array(
				esc_html__( 'Sunday', 'merchant' ),
				esc_html__( 'Monday', 'merchant' ),
				esc_html__( 'Tuesday', 'merchant' ),
				esc_html__( 'Wednesday', 'merchant' ),
				esc_html__( 'Thursday', 'merchant' ),
				esc_html__( 'Friday', 'merchant' ),
				esc_html__( 'Saturday', 'merchant' ),
			),
			'daysShort'   => array(
				esc_html__( 'Sun', 'merchant' ),
				esc_html__( 'Mon', 'merchant' ),
				esc_html__( 'Tue', 'merchant' ),
				esc_html__( 'Wed', 'merchant' ),
				esc_html__( 'Thu', 'merchant' ),
				esc_html__( 'Fri', 'merchant' ),
				esc_html__( 'Sat', 'merchant' ),
			),
			'daysMin'     => array(
				esc_html__( 'Su', 'merchant' ),
				esc_html__( 'Mo', 'merchant' ),
				esc_html__( 'Tu', 'merchant' ),
				esc_html__( 'We', 'merchant' ),
				esc_html__( 'Th', 'merchant' ),
				esc_html__( 'Fr', 'merchant' ),
				esc_html__( 'Sa', 'merchant' ),
			),
			'months'      => array(
				esc_html__( 'January', 'merchant' ),
				esc_html__( 'February', 'merchant' ),
				esc_html__( 'March', 'merchant' ),
				esc_html__( 'April', 'merchant' ),
				esc_html__( 'May', 'merchant' ),
				esc_html__( 'June', 'merchant' ),
				esc_html__( 'July', 'merchant' ),
				esc_html__( 'August', 'merchant' ),
				esc_html__( 'September', 'merchant' ),
				esc_html__( 'October', 'merchant' ),
				esc_html__( 'November', 'merchant' ),
				esc_html__( 'December', 'merchant' ),
			),
			'monthsShort' => array(
				esc_html__( 'Jan', 'merchant' ),
				esc_html__( 'Feb', 'merchant' ),
				esc_html__( 'Mar', 'merchant' ),
				esc_html__( 'Apr', 'merchant' ),
				esc_html__( 'May', 'merchant' ),
				esc_html__( 'Jun', 'merchant' ),
				esc_html__( 'Jul', 'merchant' ),
				esc_html__( 'Aug', 'merchant' ),
				esc_html__( 'Sep', 'merchant' ),
				esc_html__( 'Oct', 'merchant' ),
				esc_html__( 'Nov', 'merchant' ),
				esc_html__( 'Dec', 'merchant' ),
			),
			'clear'       => esc_html__( 'Clear', 'merchant' ),
		);
	}
}
