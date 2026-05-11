<?php
/**
 * Loads plugin integrations.
 */

if (!defined('ABSPATH')) {
    exit;
}

class WCS_Loader {
    public function run() {
        $frontend = new WCS_Frontend();
        $admin = new WCS_Admin();
        $ajax = new WCS_Ajax();

        $frontend->hooks();
        $admin->hooks();
        $ajax->hooks();
    }
}
