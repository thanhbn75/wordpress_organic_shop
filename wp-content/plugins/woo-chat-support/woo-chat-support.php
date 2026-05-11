<?php
/**
 * Plugin Name: Woo Chat Support
 * Description: Thêm hệ thống tin nhắn hỗ trợ giữa khách hàng và quản trị viên trong WooCommerce.
 * Version: 1.0.3
 * Author: Your Name
 * Text Domain: woo-chat-support
 * Requires Plugins: woocommerce
 */

if (!defined('ABSPATH')) {
    exit;
}

define('WCS_VERSION', '1.0.3');
define('WCS_PLUGIN_FILE', __FILE__);
define('WCS_PLUGIN_DIR', plugin_dir_path(__FILE__));
define('WCS_PLUGIN_URL', plugin_dir_url(__FILE__));

require_once WCS_PLUGIN_DIR . 'includes/class-wcs-activator.php';
require_once WCS_PLUGIN_DIR . 'includes/class-wcs-database.php';
require_once WCS_PLUGIN_DIR . 'includes/class-wcs-loader.php';
require_once WCS_PLUGIN_DIR . 'includes/class-wcs-frontend.php';
require_once WCS_PLUGIN_DIR . 'includes/class-wcs-admin.php';
require_once WCS_PLUGIN_DIR . 'includes/class-wcs-ajax.php';

register_activation_hook(__FILE__, ['WCS_Activator', 'activate']);
register_deactivation_hook(__FILE__, 'wcs_deactivate_plugin');

function wcs_deactivate_plugin() {
    flush_rewrite_rules();
}

function wcs_run_plugin() {
    if (!class_exists('WooCommerce')) {
        add_action('admin_notices', 'wcs_missing_woocommerce_notice');
        return;
    }

    $plugin = new WCS_Loader();
    $plugin->run();
}
add_action('plugins_loaded', 'wcs_run_plugin');

function wcs_missing_woocommerce_notice() {
    echo '<div class="notice notice-error"><p>';
    echo esc_html__('Woo Chat Support yêu cầu WooCommerce được cài đặt và kích hoạt.', 'woo-chat-support');
    echo '</p></div>';
}
