<?php
/**
 * Activation tasks.
 */

if (!defined('ABSPATH')) {
    exit;
}

class WCS_Activator {
    public static function activate() {
        require_once plugin_dir_path(dirname(__FILE__)) . 'includes/class-wcs-database.php';

        WCS_Database::create_tables();
        add_rewrite_endpoint('messages', EP_ROOT | EP_PAGES);
        flush_rewrite_rules();
    }
}
