<?php
/**
 * Usage Tracker functionality to understand what's going on client's sites.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

/**
 * Merchant_Usage_Tracking class.
 */
class Merchant_Usage_Tracking {

	/**
	 * The slug that will be used to save the option of Usage Tracker.
	 *
	 * @since 2.2.0
	 */
	const SETTINGS_SLUG = 'usage-tracking-enabled';

	/**
	 * Initialize the usage tracking system.
	 *
	 * @since 2.2.0
	 */
	public static function init_system() {

		/**
		 * Filter whether the Usage Tracking code is allowed to be loaded.
		 *
		 * @param bool $allowed Whether usage tracking is allowed.
		 *
		 * @since 2.2.0
		 */
		if ( ! apply_filters( 'merchant_usage_tracking_is_allowed', true ) ) {
			return;
		}

		$usage_tracking = new self();
		$usage_tracking->init();

		$send_task = new Merchant_Send_Usage_Task();
		$send_task->init();
	}

	/**
	 * Attach hooks to the WordPress API
	 *
	 * @since 2.2.0
	 */
	public function init() {

		// Add settings option.
		add_action( 'merchant_module_settings', array( $this, 'add_settings_option' ) );

		// Deregister the action if option is disabled.
		add_action( 'merchant_options_saved_global-settings', array( $this, 'maybe_cancel_task' ) );

		// Schedule the task if enabled.
		if ( self::is_enabled() ) {
			$this->schedule_task();
		}
	}

	/**
	 * Whether Usage Tracking is enabled.
	 *
	 * @since 2.2.0
	 *
	 * @return bool
	 */
	public static function is_enabled() {

		/**
		 * Filter whether the Usage Tracking is enabled.
		 *
		 * @param bool $enabled Whether usage tracking is enabled.
		 *
		 * @since 2.2.0
		 */
		return (bool) apply_filters(
			'merchant_usage_tracking_is_enabled',
			Merchant_Admin_Options::get( 'global-settings', self::SETTINGS_SLUG, false )
		);
	}

	/**
	 * Add "Allow Usage Tracking" to Merchant settings.
	 *
	 * @since 2.2.0
	 */
	public function add_settings_option( $settings ) {

		if ( 
			! isset( $settings['module'] ) ||
			$settings['module'] !== 'global-settings' ||
			! isset( $settings['title'] ) ||
			$settings['title'] !== esc_html__( 'Merchant Analytics', 'merchant' )
		) {
			return $settings;
		}

		/**
		 * Filter whether to show the usage tracking setting in the admin.
		 *
		 * @param bool $show_setting Whether to show the usage tracking setting.
		 *
		 * @since 2.2.0
		 */
		if ( ! apply_filters( 'merchant_usage_tracking_show_setting', true ) ) {
			return $settings;
		}

		// Ensure fields array exists.
		if ( ! isset( $settings['fields'] ) || ! is_array( $settings['fields'] ) ) {
			$settings['fields'] = array();
		}

		$settings['fields'] = array_merge(
			array(
				array(
					'id'      => self::SETTINGS_SLUG,
					'type'    => 'switcher',
					'title'   => esc_html__( 'Improve Merchant', 'merchant' ),
					'desc'    => esc_html__( 'By allowing us to track usage data, we can better help you, as we will know which WordPress configurations, themes, and plugins we should test. No sensitive data is collected.', 'merchant' ),
					'default' => false,
				),
			),
			$settings['fields']
		);

		return $settings;
	}

	/**
	 * Schedule the usage tracking task.
	 *
	 * @since 2.2.0
	 */
	public function schedule_task() {

		if ( ! function_exists( 'as_schedule_recurring_action' ) ) {
			return;
		}

		// Check if already scheduled.
		if ( as_next_scheduled_action( 'merchant_send_usage_data', array(), 'merchant' ) ) {
			return;
		}

		// Schedule to run weekly.
		as_schedule_recurring_action( time(), WEEK_IN_SECONDS, 'merchant_send_usage_data', array(), 'merchant', true );
	}

	/**
	 * Cancel the usage tracking task if disabled.
	 *
	 * @since 2.2.0
	 */
	public function maybe_cancel_task() {

		if ( self::is_enabled() || ! function_exists( 'as_unschedule_action' ) ) {
			return;
		}

		as_unschedule_action( 'merchant_send_usage_data', array(), 'merchant' );
	}

	/**
	 * Get the User Agent string that will be sent to the API.
	 *
	 * @since 2.2.0
	 *
	 * @return string
	 */
	public function get_user_agent() {

		$version = MERCHANT_VERSION;

		if ( defined( 'MERCHANT_PRO_VERSION' ) ) {
			$version .= ' Pro/' . MERCHANT_PRO_VERSION;
		}

		return 'aThemes Merchant/' . $version . '; ' . get_bloginfo( 'url' );
	}

	/**
	 * Get data for sending to the server.
	 *
	 * @since 2.2.0
	 *
	 * @return array
	 */
	public function get_data() {

		global $wpdb;

		$theme_data = wp_get_theme();
		$is_pro     = defined( 'MERCHANT_PRO_VERSION' );

		$data = array(
			// Generic data (environment) - keys without prefix.
			'url'                    => home_url(),
			'php_version'            => PHP_MAJOR_VERSION . '.' . PHP_MINOR_VERSION,
			'wp_version'             => get_bloginfo( 'version' ),
			'mysql_version'          => $wpdb->db_version(),
			'server_version'         => isset( $_SERVER['SERVER_SOFTWARE'] ) ? sanitize_text_field( wp_unslash( $_SERVER['SERVER_SOFTWARE'] ) ) : '',
			'is_ssl'                 => (int) is_ssl(),
			'is_multisite'           => (int) is_multisite(),
			'is_network_activated'   => (int) $this->is_active_for_network(),
			'is_wpcom'               => (int) ( defined( 'IS_WPCOM' ) && IS_WPCOM ),
			'is_wpcom_vip'           => (int) ( ( defined( 'WPCOM_IS_VIP_ENV' ) && WPCOM_IS_VIP_ENV ) || ( function_exists( 'wpcom_is_vip' ) && wpcom_is_vip() ) ),
			'is_wp_cache'            => (int) ( defined( 'WP_CACHE' ) && WP_CACHE ),
			'is_wp_rest_api_enabled' => (int) $this->is_rest_api_enabled(),
			'is_user_logged_in'      => (int) is_user_logged_in(),
			'sites_count'            => $this->get_sites_total(),
			'active_plugins'         => $this->get_active_plugins(),
			'theme_name'             => $theme_data->get( 'Name' ),
			'theme_version'          => $theme_data->get( 'Version' ),
			'locale'                 => get_locale(),
			'timezone_offset'        => wp_timezone_string(),
			// Merchant-specific data - keys with athemes_merchant_ prefix.
			'athemes_merchant_version'             => MERCHANT_VERSION,
			'athemes_merchant_license_key'         => $this->get_license_key(),
			'athemes_merchant_license_type'        => $this->get_license_type(),
			'athemes_merchant_is_pro'              => (int) $is_pro,
			'athemes_merchant_lite_installed_date' => $this->get_installed_date( 'lite' ),
			'athemes_merchant_pro_installed_date'  => $is_pro ? $this->get_installed_date( 'pro' ) : '',
			'athemes_merchant_active_modules'      => $this->get_active_modules(),
			'athemes_merchant_settings'            => $this->get_settings(),
		);

		if ( $is_pro ) {
			$data['athemes_merchant_pro_version'] = MERCHANT_PRO_VERSION;
		}

		if ( $data['is_multisite'] ) {
			$data['url_primary'] = network_site_url();
		}

		/**
		 * Filter the usage tracking data.
		 *
		 * @param array $data Usage tracking data.
		 *
		 * @since 2.2.0
		 */
		return apply_filters( 'merchant_usage_tracking_data', $data );
	}

	/**
	 * Get the installed date for Merchant or Merchant Pro.
	 *
	 * @since 2.2.0
	 *
	 * @param string $type Either 'lite' or 'pro'.
	 *
	 * @return int Unix timestamp of installation date.
	 */
	private function get_installed_date( $type = 'lite' ) {

		$option_name = 'merchant_installed_date';
		
		if ( $type === 'pro' ) {
			$option_name = 'merchant_pro_installed_date';
		}

		$installed_date = get_option( $option_name, 0 );

		// If not set, set it now.
		if ( empty( $installed_date ) ) {
			$installed_date = time();
			// Update the option.
			update_option( $option_name, $installed_date );
		}

		return (int) $installed_date;
	}

	/**
	 * Get the license key (masked for security)
	 *
	 * @since 2.2.0
	 
	 * @return string
	 */
	private function get_license_key() {

		$license_key = trim( get_option( 'merchant_pro_license_key', '' ) );

		if ( empty( $license_key ) ) {
			return '';
		}

		// Mask the license key for security (show only last 4 characters).
		return sanitize_text_field( $license_key );
	}

	/**
	 * Get the license type.
	 *
	 * @since 2.2.0
	 *
	 * @return string
	 */
	private function get_license_type() {

		if ( ! defined( 'MERCHANT_PRO_VERSION' ) ) {
			return 'lite';
		}

		// Get the license item name from option (for future plan names like "agency", "lifetime", etc.)
		$license_item_name = get_option( 'merchant_pro_license_item_name', '' );

		// If option is empty or default, return 'pro', otherwise return the plan name
		if ( empty( $license_item_name ) || $license_item_name === 'Merchant Pro' ) {
			return 'pro';
		}

		return sanitize_text_field( strtolower( $license_item_name ) );
	}

	/**
	 * Get all active modules.
	 *
	 * @since 2.2.0
	 *
	 * @return array
	 */
	private function get_active_modules() {

		if ( ! function_exists( 'merchant_get_active_modules' ) ) {
			return array();
		}

		return merchant_get_active_modules();
	}

	/**
	 * Get all settings, except those with sensitive data.
	 *
	 * @since 2.2.0
	 *
	 * @return array
	 */
	private function get_settings() {
	
		// Get global settings (excluding sensitive data).
		$global_settings = Merchant_Admin_Options::get_all( 'global-settings' );

		if ( empty( $global_settings ) || ! is_array( $global_settings ) ) {
			return array();
		}

		return $global_settings;
	}

	/**
	 * Get the list of active plugins.
	 *
	 * @since 2.2.0
	 *
	 * @return array
	 */
	private function get_active_plugins() {

		if ( ! function_exists( 'get_plugins' ) ) {
			include ABSPATH . '/wp-admin/includes/plugin.php';
		}

		$active = is_multisite() ?
			array_merge( get_option( 'active_plugins', array() ), array_flip( get_site_option( 'active_sitewide_plugins', array() ) ) ) :
			get_option( 'active_plugins', array() );

		$plugins = array_intersect_key( get_plugins(), array_flip( $active ) );

		return array_map(
			static function ( $plugin ) {
				if ( isset( $plugin['Version'] ) ) {
					return $plugin['Version'];
				}

				return 'Not Set';
			},
			$plugins
		);
	}

	/**
	 * Test if the REST API is accessible.
	 *
	 * The REST API might be inaccessible due to various security measures,
	 * or it might be completely disabled by a plugin.
	 *
	 * @since 2.2.0
	 *
	 * @return bool
	 */
	private function is_rest_api_enabled() {

		$url      = rest_url( 'wp/v2/types/post' );
		$response = wp_remote_get(
			$url,
			array(
				'timeout'   => 10,
				'cookies'   => is_user_logged_in() ? wp_unslash( $_COOKIE ) : array(),
				'headers'   => array(
					'Cache-Control' => 'no-cache',
					'X-WP-Nonce'    => wp_create_nonce( 'wp_rest' ),
				),
			)
		);

		// When testing the REST API, an error was encountered, leave early.
		if ( is_wp_error( $response ) ) {
			return false;
		}

		// When testing the REST API, an unexpected result was returned, leave early.
		if ( wp_remote_retrieve_response_code( $response ) !== 200 ) {
			return false;
		}

		// The REST API did not behave correctly, leave early.
		$body = wp_remote_retrieve_body( $response );

		if ( ! $this->is_json( $body ) ) {
			return false;
		}

		// We are all set. Confirm the connection.
		return true;
	}

	/**
	 * Check if a string is valid JSON.
	 *
	 * @since 2.2.0
	 *
	 * @param string $value The string to check.
	 *
	 * @return bool
	 */
	private function is_json( $value ) {

		if ( ! is_string( $value ) ) {
			return false;
		}

		json_decode( $value, true );

		return json_last_error() === JSON_ERROR_NONE;
	}

	/**
	 * Determines whether the plugin is active for the entire network.
	 *
	 * @since 2.2.0
	 *
	 * @return bool
	 */
	private function is_active_for_network() {

		// Bail early, in case we are not in multisite.
		if ( ! is_multisite() ) {
			return false;
		}

		// Get all active plugins.
		$plugins = get_site_option( 'active_sitewide_plugins' );

		// Bail early, in case the plugin is active for the entire network.
		if ( isset( $plugins[ plugin_basename( MERCHANT_FILE ) ] ) ) {
			return true;
		}

		return false;
	}

	/**
	 * Total number of sites.
	 *
	 * @since 2.2.0
	 *
	 * @return int
	 */
	private function get_sites_total() {
		return function_exists( 'get_blog_count' ) ? (int) get_blog_count() : 1;
	}
}

// Initialize usage tracking.
add_action( 'init', array( 'Merchant_Usage_Tracking', 'init_system' ) );
