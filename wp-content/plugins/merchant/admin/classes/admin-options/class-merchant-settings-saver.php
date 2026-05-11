<?php
/**
 * Merchant Settings Saver.
 *
 * Handles saving, sanitizing, and preprocessing of module
 * option values submitted via the admin settings form.
 * Extracted from {@see Merchant_Admin_Options}.
 *
 * @package Merchant
 * @since   1.9.3
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Merchant_Settings_Saver
 *
 * Processes submitted form data: verifies nonces/capabilities,
 * preprocesses and sanitizes values via the field registry,
 * and persists settings to the database.
 *
 * @since 1.9.3
 */
class Merchant_Settings_Saver {

	/**
	 * Save module options from a form submission.
	 *
	 * Verifies the request, then iterates each field definition to
	 * preprocess, sanitize, and persist the values. Supports
	 * both save and reset operations.
	 *
	 * Fires `merchant_options_saved` and `merchant_options_saved_{module_id}`
	 * actions after a successful save.
	 *
	 * @since 1.0
	 *
	 * @param array<string, mixed> $settings Module settings configuration with 'module' and 'fields' keys.
	 *
	 * @return void
	 */
	public static function save_options( $settings ) {
		if ( ! self::verify_save_request() ) {
			return;
		}

		self::maybe_decode_json_payload();

		$save    = ! empty( $_POST['merchant_save'] ); // phpcs:ignore WordPress.Security.NonceVerification.Missing -- verified in verify_save_request().
		$reset   = ! empty( $_POST['merchant_reset'] ); // phpcs:ignore WordPress.Security.NonceVerification.Missing -- verified in verify_save_request().
		$options = get_option( 'merchant', array() );

		if ( $save && ! empty( $settings['fields'] ) ) {
			$options[ $settings['module'] ] = self::process_save_fields( $settings['fields'], $settings['module'], $options );
		} elseif ( $reset ) {
			$options[ $settings['module'] ] = array();
		}

		update_option( 'merchant', $options );

		/**
		 * Hook: merchant_options_saved, fired after saving module options.
		 *
		 * @param string $module  module ID.
		 * @param array  $options module options.
		 *
		 * @since 1.9.3
		 */
		do_action( 'merchant_options_saved', $settings['module'], $options[ $settings['module'] ] );

		/**
		 * Hook: merchant_options_saved, fired after saving specific module options.
		 *
		 * @param array $options module options.
		 *
		 * @since 1.9.3
		 */
		do_action( "merchant_options_saved_{$settings['module']}", $options[ $settings['module'] ] );
	}

	/**
	 * Verify the save request has valid nonce and user capabilities.
	 *
	 * @since 1.9.3
	 *
	 * @return bool True if the request is valid and the user can save.
	 */
	private static function verify_save_request() {
		$nonce = isset( $_POST['merchant_nonce'] )
			? sanitize_text_field( wp_unslash( $_POST['merchant_nonce'] ) )
			: '';

		if ( ! wp_verify_nonce( $nonce, 'merchant_nonce' ) ) {
			return false;
		}

		return current_user_can( 'manage_options' );
	}

	/**
	 * Process and sanitize submitted field values for saving.
	 *
	 * Iterates each field definition, preprocesses through the field
	 * registry, sanitizes the value, and returns the updated module
	 * options array.
	 *
	 * @since 1.9.3
	 *
	 * @param array<int, array<string, mixed>>  $fields  Array of field definition arrays.
	 * @param string $module  The module ID.
	 * @param array<string, mixed>  $options All saved merchant options.
	 *
	 * @return array<string, mixed> Updated module options.
	 */
	private static function process_save_fields( $fields, $module, $options ) {
		$module_options = $options[ $module ] ?? array();

		foreach ( $fields as $field ) {
			if ( ! isset( $field['id'] ) ) {
				continue;
			}

			if ( ! merchant_is_pro_active() && isset( $field['pro'] ) && $field['pro'] === true ) {
				continue;
			}

			$value = null;

			if ( isset( $_POST['merchant'][ $field['id'] ] ) ) { // phpcs:ignore WordPress.Security.NonceVerification.Missing -- verified in verify_save_request().
				$raw_value = wp_unslash( $_POST['merchant'][ $field['id'] ] ); // phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized, WordPress.Security.NonceVerification.Missing -- verified in verify_save_request().
				$value     = self::sanitize_field_value( $field, $raw_value );
			} else {
				$value = self::sanitize( $field, $value );
			}

			$module_options[ $field['id'] ] = $value;
		}

		return $module_options;
	}

	/**
	 * Sanitize a single field value through the registry or fallback.
	 *
	 * Expects `$raw_value` to already be unslashed.
	 *
	 * @since 1.9.3
	 *
	 * @param array<string, mixed> $field     The field configuration array.
	 * @param mixed $raw_value The raw submitted value (already unslashed).
	 *
	 * @return mixed The sanitized value.
	 */
	private static function sanitize_field_value( $field, $raw_value ) {
		$type     = $field['type'] ?? '';
		$registry = Merchant_Field_Registry::instance();

		// Preprocess first (only during save, not in the public API).
		if ( $registry->has( $type ) ) {
			$field_instance = $registry->create( $type, $field, $raw_value );
			if ( $field_instance !== null ) {
				$raw_value = $field_instance->preprocess( $raw_value );
			}
		}

		// Then sanitize through the single consolidated path.
		return self::sanitize( $field, $raw_value );
	}

	/**
	 * Sanitize options.
	 *
	 * Delegates type-specific sanitization to the field registry.
	 * Each field class implements its own sanitize_value() method.
	 *
	 * @since 1.9.3
	 *
	 * @param array<string, mixed> $field The field configuration.
	 * @param mixed $value The raw submitted value.
	 *
	 * @return mixed The sanitized value.
	 */
	public static function sanitize( $field, $value ) {
		// Custom sanitize callback takes priority.
		if ( ! empty( $field['sanitize'] ) && is_callable( $field['sanitize'] ) ) {
			return call_user_func( $field['sanitize'], $value );
		}

		$type     = $field['type'] ?? '';
		$registry = Merchant_Field_Registry::instance();

		if ( $registry->has( $type ) ) {
			$field_instance = $registry->create( $type, $field, $value );

			if ( $field_instance !== null ) {
				return $field_instance->sanitize( $value );
			}
		}

		// Fallback for unregistered types.
		return sanitize_text_field( $value );
	}

	/**
	 * Decode JSON payload submitted by the admin JS to bypass max_input_vars.
	 *
	 * When the form has many fields (e.g. flexible_content with 20+ campaigns),
	 * the JS serializes all merchant[*] fields into a single JSON string posted
	 * as `merchant_json_payload`. This method decodes it back into $_POST['merchant'].
	 *
	 * Falls back silently to standard POST if the payload is missing or malformed.
	 *
	 * @since 2.2.5
	 *
	 * @return void
	 */
	private static function maybe_decode_json_payload() {
		// phpcs:ignore WordPress.Security.NonceVerification.Missing -- verified in verify_save_request().
		if ( ! isset( $_POST['merchant_json_payload'] ) ) {
			return;
		}

		// phpcs:ignore WordPress.Security.NonceVerification.Missing, WordPress.Security.ValidatedSanitizedInput.InputNotSanitized -- JSON decoded, each field sanitized downstream.
		$raw     = wp_unslash( $_POST['merchant_json_payload'] );
		$decoded = json_decode( $raw, true );

		if ( json_last_error() !== JSON_ERROR_NONE || ! is_array( $decoded ) ) {
			return; // Malformed — fall back to standard POST.
		}

		$_POST['merchant'] = $decoded; // phpcs:ignore WordPress.Security.NonceVerification.Missing
	}
}
