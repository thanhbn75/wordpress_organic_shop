<?php
/**
 * Merchant Admin Options.
 *
 * Thin orchestrator that manages module settings. Delegates rendering
 * to {@see Merchant_Settings_Renderer}, saving to {@see Merchant_Settings_Saver},
 * asset enqueueing to {@see Merchant_Admin_Assets}, and Select2 data
 * to {@see Merchant_Select2_Choices}.
 *
 * @package Merchant
 * @since   1.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

if ( ! class_exists( 'Merchant_Admin_Options' ) ) {
	/**
	 * Merchant_Admin_Options
	 *
	 * Orchestrates the Merchant admin settings panel. Core responsibilities:
	 * - CRUD operations for module settings (`get`, `set`, `delete`, `get_all`)
	 * - Wiring admin hooks (assets, cache, cleanup, Pro activation)
	 * - Backward-compatible facades that delegate to extracted classes
	 *
	 * This class is a singleton; use {@see Merchant_Admin_Options::instance()} to retrieve it.
	 *
	 * @since 1.0
	 */
	class Merchant_Admin_Options {

		/**
		 * The single class instance.
		 *
		 * @since 1.0
		 * @var Merchant_Admin_Options|null
		 */
		private static $instance = null;

		/**
		 * Get the singleton instance.
		 *
		 * @since 1.0
		 *
		 * @return Merchant_Admin_Options
		 */
		public static function instance() {
			if ( is_null( self::$instance ) ) {
				self::$instance = new self();
			}

			return self::$instance;
		}

		/**
		 * Constructor.
		 *
		 * Registers admin hooks for script enqueueing, cache clearance,
		 * legacy data cleanup, and Pro plugin activation.
		 *
		 * @since 1.0
		 */
		public function __construct() {
			$assets   = new Merchant_Admin_Assets();
			$choices  = new Merchant_Select2_Choices();

			add_action( 'admin_enqueue_scripts', array( $assets, 'enqueue_scripts' ) );
			add_action( 'clean_user_cache', array( $choices, 'clear_customer_choices_cache' ), 10, 2 );

			add_action( 'admin_init', array( $this, 'delete_module_data' ) );
			add_action( 'admin_init', array( $this, 'activate_pro_plugin' ) );
		}

		// ──────────────────────────────────────────────────────
		// CRUD — core orchestrator responsibility.
		// ──────────────────────────────────────────────────────

		/**
		 * Get a single module setting value.
		 *
		 * Retrieves the value of a specific setting for a given module
		 * from the `merchant` option. Falls back to `$default_val` if
		 * the setting does not exist.
		 *
		 * @since 1.0
		 *
		 * @param string $module      The module ID (e.g. 'buy-x-get-y').
		 * @param string $setting     The setting key within the module.
		 * @param mixed  $default_val The default value if the setting is not found.
		 *
		 * @return mixed The setting value, filtered via `merchant_get_option`.
		 */
		public static function get( $module, $setting, $default_val ) {
			$options = get_option( 'merchant', array() );

			$value = $default_val;

			if ( isset( $options[ $module ][ $setting ] ) ) {
				$value = $options[ $module ][ $setting ];
			}

			/**
			 * Hook: merchant_get_option filter.
			 * Fires after getting module option.
			 *
			 * @param mixed  $value       Option value.
			 * @param string $module      Module ID.
			 * @param string $setting     Setting ID.
			 * @param mixed  $default_val Default value.
			 *
			 * @since 1.9.3
			 */
			return apply_filters( 'merchant_get_option', $value, $module, $setting, $default_val );
		}

		/**
		 * Set a single module setting value.
		 *
		 * @since 1.0
		 *
		 * @param string $module  The module ID.
		 * @param string $setting The setting key.
		 * @param mixed  $value   The value to store.
		 *
		 * @return void
		 */
		public static function set( $module, $setting, $value ) {
			$options = get_option( 'merchant', array() );
			$options[ $module ][ $setting ] = $value;
			update_option( 'merchant', $options );
		}

		/**
		 * Delete a single module setting.
		 *
		 * @since 1.0
		 *
		 * @param string $module  The module ID.
		 * @param string $setting The setting key to remove.
		 *
		 * @return void
		 */
		public static function delete( $module, $setting ) {
			$options = get_option( 'merchant', array() );
			unset( $options[ $module ][ $setting ] );
			update_option( 'merchant', $options );
		}

		/**
		 * Get all settings for a module.
		 *
		 * @since 1.0
		 *
		 * @param string $module The module ID.
		 *
		 * @return array All settings for the module.
		 */
		public static function get_all( $module ) {
			$options = get_option( 'merchant', array() );
			$value   = array();

			if ( isset( $options[ $module ] ) ) {
				$value = $options[ $module ];
			}

			return $value;
		}

		// ──────────────────────────────────────────────────────
		// Delegation facades — backward compatibility.
		// ──────────────────────────────────────────────────────

		/**
		 * Create and render a module settings panel.
		 *
		 * @since 1.0
		 * @see   Merchant_Settings_Renderer::create()
		 *
		 * @param array $settings Module settings configuration.
		 *
		 * @return void
		 */
		public static function create( $settings ) {
			Merchant_Settings_Renderer::create( $settings );
		}

		/**
		 * Render a single field.
		 *
		 * @since 1.0
		 * @see   Merchant_Settings_Renderer::field()
		 *
		 * @param array  $settings  The field configuration array.
		 * @param mixed  $value     The current saved value.
		 * @param string $module_id The module ID.
		 *
		 * @return void
		 */
		public static function field( $settings, $value, $module_id = '' ) {
			Merchant_Settings_Renderer::field( $settings, $value, $module_id );
		}

		/**
		 * Render a disabled (pro-gated) field.
		 *
		 * @since 1.0
		 * @see   Merchant_Settings_Renderer::disabled_field()
		 *
		 * @param array  $settings  Field settings.
		 * @param mixed  $value     Field value.
		 * @param string $module_id Module ID.
		 *
		 * @return void
		 */
		public static function disabled_field( $settings, $value, $module_id = '' ) {
			Merchant_Settings_Renderer::disabled_field( $settings, $value, $module_id );
		}

		/**
		 * Save module options from a form submission.
		 *
		 * @since 1.0
		 * @see   Merchant_Settings_Saver::save_options()
		 *
		 * @param array $settings Module settings configuration.
		 *
		 * @return void
		 */
		public static function save_options( $settings ) {
			Merchant_Settings_Saver::save_options( $settings );
		}

		/**
		 * Sanitize options.
		 *
		 * @since 1.9.3
		 * @see   Merchant_Settings_Saver::sanitize()
		 *
		 * @param array $field The field configuration.
		 * @param mixed $value The raw submitted value.
		 *
		 * @return mixed The sanitized value.
		 */
		public static function sanitize( $field, $value ) {
			return Merchant_Settings_Saver::sanitize( $field, $value );
		}

		/**
		 * Get product category choices for Select2.
		 *
		 * @since 1.9.3
		 * @see   Merchant_Select2_Choices::get_category_select2_choices()
		 *
		 * @return array Formatted category choices.
		 */
		public static function get_category_select2_choices() {
			return Merchant_Select2_Choices::get_category_select2_choices();
		}

		/**
		 * Get product tag choices for Select2.
		 *
		 * @since 1.9.3
		 * @see   Merchant_Select2_Choices::get_tag_select2_choices()
		 *
		 * @return array Formatted tag choices.
		 */
		public static function get_tag_select2_choices() {
			return Merchant_Select2_Choices::get_tag_select2_choices();
		}

		/**
		 * Get product brand choices for Select2.
		 *
		 * @since 1.9.3
		 * @see   Merchant_Select2_Choices::get_brand_select2_choices()
		 *
		 * @return array Formatted brand choices.
		 */
		public static function get_brand_select2_choices() {
			return Merchant_Select2_Choices::get_brand_select2_choices();
		}

		/**
		 * Get user role choices for Select2.
		 *
		 * @since 1.9.3
		 * @see   Merchant_Select2_Choices::get_user_roles_select2_choices()
		 *
		 * @return array Formatted role choices.
		 */
		public static function get_user_roles_select2_choices() {
			return Merchant_Select2_Choices::get_user_roles_select2_choices();
		}

		/**
		 * Get customer user choices for Select2.
		 *
		 * @since 1.9.3
		 * @see   Merchant_Select2_Choices::get_customers_select2_choices()
		 *
		 * @return array Formatted customer choices.
		 */
		public static function get_customers_select2_choices() {
			return Merchant_Select2_Choices::get_customers_select2_choices();
		}

		// ──────────────────────────────────────────────────────
		// Admin hooks — small one-off actions.
		// ──────────────────────────────────────────────────────

		/**
		 * Delete stale module data from the database.
		 *
		 * Cleans up data for removed modules (e.g., the deprecated
		 * `code-snippets` module). Hooked to `admin_init`.
		 *
		 * @since 1.9.3
		 *
		 * @return void
		 */
		public function delete_module_data() {
			$options = get_option( 'merchant', array() );

			if ( isset( $options['code-snippets'] ) ) {
				unset( $options['code-snippets'] );
				update_option( 'merchant', $options );
			}
		}

		/**
		 * Activate the Merchant Pro plugin.
		 *
		 * Handles the `merchant_activate_pro` admin action: verifies
		 * nonce and capabilities, activates the Pro plugin, and
		 * redirects back to the originating Merchant page.
		 *
		 * @since 1.0
		 *
		 * @return void
		 */
		public function activate_pro_plugin() {
			if ( ! isset( $_GET['action'] ) || 'merchant_activate_pro' !== $_GET['action'] ) {
				return;
			}

			if (
				! isset( $_GET['nonce'] )
				|| ! wp_verify_nonce(
					sanitize_text_field( wp_unslash( $_GET['nonce'] ) ),
					'merchant_activate_pro'
				)
			) {
				return;
			}

			if ( ! current_user_can( 'activate_plugins' ) ) {
				return;
			}

			$result = activate_plugin( 'merchant-pro/merchant-pro.php' );

			if ( is_wp_error( $result ) ) {
				wp_die( esc_html( $result->get_error_message() ) );
			}

			wp_safe_redirect( $this->get_safe_merchant_redirect_url() );
			exit;
		}

		/**
		 * Get a safe redirect URL pointing to a Merchant admin page.
		 *
		 * Validates the HTTP referer is a Merchant page. Falls back
		 * to the main Merchant dashboard if the referer is invalid
		 * or points to a non-Merchant page.
		 *
		 * @since 1.9.3
		 *
		 * @return string Safe redirect URL.
		 */
		private function get_safe_merchant_redirect_url() {
			$fallback_url = admin_url( 'admin.php?page=merchant' );
			$referer      = wp_get_referer();
			$redirect_to  = wp_validate_redirect( $referer, $fallback_url );

			if ( $redirect_to === $fallback_url ) {
				return $fallback_url;
			}

			$parsed_url   = wp_parse_url( $redirect_to );
			$query_params = array();
			parse_str( $parsed_url['query'] ?? '', $query_params );

			if ( empty( $query_params['page'] ) || strpos( $query_params['page'], 'merchant' ) !== 0 ) {
				return $fallback_url;
			}

			return $redirect_to;
		}
	}

	Merchant_Admin_Options::instance();
}
