<?php

/**
 * Plugin Installer.
 * This class is responsible for installing and activating plugins. This could be from wp.org and external sources.
 * 
 * @package Merchant
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

if ( ! class_exists( 'Merchant_Plugin_Installer' ) ) {

    class Merchant_Plugin_Installer {

        /**
         * Constructor.
         * 
         */
        public function __construct() {
            add_action( 'admin_init', array( $this, 'init' ) );
        }

        /**
         * Initialize the class.
         * 
         * @return void
         */
        public function init() {
            if ( ! is_admin() ) {
                return;
            }

            if ( ! current_user_can( 'install_plugins' ) ) {
                return;
            }

            add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_scripts' ) );
            add_action( 'wp_ajax_merchant_install_plugin', array( $this, 'install_plugin' ) );
            add_action( 'wp_ajax_merchant_install_external_plugin', array( $this, 'install_external_plugin' ) );
        }

        /**
         * Enqueue scripts.
         * 
         * @return void
         */
        public function enqueue_scripts() {
            wp_enqueue_script( 'merchant-plugin-installer', MERCHANT_URI . '/assets/js/admin/plugin-installer.min.js', array( 'jquery' ), MERCHANT_VERSION, true );

            wp_localize_script( 'merchant-plugin-installer', 'merchantPluginInstallerConfig', array(
                'ajax_url' => admin_url( 'admin-ajax.php' ),
                'nonce' => wp_create_nonce( 'merchant_plugin_installer_nonce' ),
                'i18n' => array(
                    'defaultText' => esc_html__( 'Install and Activate', 'merchant' ),
                    'installingText' => esc_html__( 'Installing...', 'merchant' ),
                    'activatingText' => esc_html__( 'Activating...', 'merchant' ),
                ),
            ) );
        }

        /**
         * Install plugin.
         * This method is responsible for installing plugins from the wp.org.
         * 
         * @return void
         */
        public function install_plugin() {
            // TODO: Implement this method.
        }

        /**
         * Install and activate an external plugin.
         * Handles three states: already active, installed but inactive, not installed.
         * 
         * @return void
         */
        public function install_external_plugin() {
            list( $url, $plugin_name ) = $this->validate_install_request();

            $state = $this->get_plugin_state( $plugin_name );

            // Already active — nothing to do.
            if ( 'active' === $state ) {
                wp_send_json_success( array( 'message' => esc_html__( 'Plugin is already active.', 'merchant' ) ) );
            }

            // Not installed — download and install first.
            if ( 'not_installed' === $state ) {
                $install_result = $this->install_from_url( $url );

                if ( is_wp_error( $install_result ) ) {
                    wp_send_json_error( array( 'message' => $install_result->get_error_message() ) );
                }
            }

            // Activate the plugin (covers both 'installed' and freshly installed).
            $activate_result = $this->activate( $plugin_name );

            if ( is_wp_error( $activate_result ) ) {
                wp_send_json_error( array( 'message' => $activate_result->get_error_message() ) );
            }

            wp_send_json_success( array( 'message' => esc_html__( 'Plugin activated successfully.', 'merchant' ) ) );
        }

        /**
         * Validate the install request.
         * Checks nonce, capability, and required parameters.
         * Sends a JSON error and dies if validation fails.
         * 
         * @return array{0: string, 1: string} The validated URL and plugin name.
         */
        private function validate_install_request() {
            check_ajax_referer( 'merchant_plugin_installer_nonce', 'nonce' );

            if ( ! current_user_can( 'install_plugins' ) ) {
                wp_send_json_error( array( 'message' => esc_html__( 'You do not have permission to install plugins.', 'merchant' ) ) );
            }

            if ( empty( $_POST['url'] ) ) {
                wp_send_json_error( array( 'message' => esc_html__( 'Plugin URL is required.', 'merchant' ) ) );
            }

            if ( empty( $_POST['plugin_name'] ) ) {
                wp_send_json_error( array( 'message' => esc_html__( 'Plugin name is required.', 'merchant' ) ) );
            }

            return array(
                esc_url_raw( $_POST['url'] ), // phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized
                sanitize_text_field( wp_unslash( $_POST['plugin_name'] ) ),
            );
        }

        /**
         * Determine the current state of a plugin.
         * 
         * @param string $plugin_name The plugin basename (e.g. 'athemes-patcher/athemes-patcher.php').
         * 
         * @return string One of 'active', 'installed', or 'not_installed'.
         */
        private function get_plugin_state( $plugin_name ) {
            if ( is_plugin_active( $plugin_name ) ) {
                return 'active';
            }

            $plugin_file = WP_PLUGIN_DIR . '/' . $plugin_name;

            if ( file_exists( $plugin_file ) ) {
                return 'installed';
            }

            return 'not_installed';
        }

        /**
         * Download and install a plugin from an external URL.
         * Uses `overwrite_package` to handle leftover directories from failed installs.
         * 
         * @param string $url The URL to the plugin ZIP file.
         * 
         * @return true|WP_Error True on success, WP_Error on failure.
         */
        private function install_from_url( $url ) {
            include_once ABSPATH . 'wp-admin/includes/class-wp-upgrader.php';
            require_once MERCHANT_DIR . 'admin/classes/class-merchant-silent-upgrader-skin.php';

            $skin     = new Merchant_Silent_Upgrader_Skin();
            $upgrader = new Plugin_Upgrader( $skin );
            $result   = $upgrader->install( $url, array( 'overwrite_package' => true ) );

            if ( is_wp_error( $result ) ) {
                return new WP_Error(
                    'merchant_install_failed',
                    $this->get_user_friendly_error( $result )
                );
            }

            if ( ! $result ) {
                $captured = $skin->get_captured_errors();
                $error_message = ! empty( $captured ) ? $captured[0] : '';
                
                $error = new WP_Error( 'download_failed', $error_message );
                
                return new WP_Error(
                    'merchant_install_failed',
                    $this->get_user_friendly_error( $error )
                );
            }

            return true;
        }

        /**
         * Get a user-friendly error message based on the error type.
         * 
         * @param WP_Error $error The WP_Error object.
         * 
         * @return string User-friendly error message.
         */
        private function get_user_friendly_error( WP_Error $error ) {
            $error_code = $error->get_error_code();
            $error_message = $error->get_error_message();
            $error_data = $error->get_error_data();
            
            // Ensure error_message is a string for strpos() calls
            if ( ! is_string( $error_message ) ) {
                $error_message = '';
            }
            
            // Extract HTTP status code if available
            $http_code = null;
            if ( is_array( $error_data ) && isset( $error_data['status'] ) && is_int( $error_data['status'] ) ) {
                $http_code = $error_data['status'];
            }
            
            // Check for 403 Forbidden (server blocking the download)
            if ( 403 === $http_code || strpos( $error_message, '403' ) !== false || strpos( $error_message, 'Forbidden' ) !== false ) {
                return esc_html__( 'The download is currently unavailable. Please try again later or contact support for assistance.', 'merchant' );
            }

            // Check for server errors (500, 502, 503)
            if ( in_array( $http_code, array( 500, 502, 503 ), true ) ||
                strpos( $error_message, '500' ) !== false || 
                strpos( $error_message, '502' ) !== false || 
                strpos( $error_message, '503' ) !== false ||
                strpos( $error_message, 'Internal Server Error' ) !== false ||
                strpos( $error_message, 'Bad Gateway' ) !== false ||
                strpos( $error_message, 'Service Unavailable' ) !== false ) {
                return esc_html__( 'The download server is temporarily unavailable. Please try again in a few minutes.', 'merchant' );
            }

            // Check for rate limiting (429)
            if ( 429 === $http_code || strpos( $error_message, '429' ) !== false || strpos( $error_message, 'Too Many Requests' ) !== false ) {
                return esc_html__( 'Too many download requests. Please wait a moment and try again.', 'merchant' );
            }

            // Check for network/connectivity errors
            if ( in_array( $error_code, array( 'http_request_failed', 'http_no_url', 'http_404' ), true ) ) {
                return esc_html__( 'The server could not connect to the download source. Please try again or contact your hosting provider if the problem persists.', 'merchant' );
            }

            // Check for download failures
            if ( strpos( $error_message, 'Download failed' ) !== false ) {
                return esc_html__( 'The download could not be completed. Please try again or install the plugin manually.', 'merchant' );
            }

            // Generic fallback
            return esc_html__( 'Installation failed. Please try again or contact support if the problem persists.', 'merchant' );
        }

        /**
         * Activate a plugin.
         * 
         * @param string $plugin_name The plugin basename (e.g. 'athemes-patcher/athemes-patcher.php').
         * 
         * @return true|WP_Error True on success, WP_Error on failure.
         */
        private function activate( $plugin_name ) {
            $result = activate_plugin( $plugin_name );

            if ( is_wp_error( $result ) ) {
                return $result;
            }

            return true;
        }
    }

    new Merchant_Plugin_Installer();
}