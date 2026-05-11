<?php

/**
 * Silent Upgrader Skin.
 * Suppresses the output of the upgrader while capturing errors for logging.
 * 
 * @package Merchant
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

require_once ABSPATH . 'wp-admin/includes/class-wp-upgrader.php';

if ( ! class_exists( 'Merchant_Silent_Upgrader_Skin' ) ) {
    class Merchant_Silent_Upgrader_Skin extends WP_Upgrader_Skin {

        /**
         * Captured errors during the upgrade process.
         * 
         * @var string[]
         */
        private $captured_errors = array();

        /**
         * Suppress header output.
         * 
         * @return void
         */
        public function header() {}

        /**
         * Suppress footer output.
         * 
         * @return void
         */
        public function footer() {}

        /**
         * Capture errors instead of outputting them.
         * 
         * @param string|WP_Error $errors The error(s) to capture.
         * 
         * @return void
         */
        public function error( $errors ) {
            if ( is_string( $errors ) ) {
                $this->captured_errors[] = $errors;
            } elseif ( is_wp_error( $errors ) ) {
                foreach ( $errors->get_error_messages() as $message ) {
                    $this->captured_errors[] = $message;
                }
            }
        }

        /**
         * Suppress feedback output.
         * 
         * @param string $feedback The feedback string.
         * @param mixed  ...$args Optional text replacements.
         * 
         * @return void
         */
        public function feedback( $feedback, ...$args ) {}

        /**
         * Get all captured errors.
         * 
         * @return string[]
         */
        public function get_captured_errors() {
            return $this->captured_errors;
        }
    }
}
