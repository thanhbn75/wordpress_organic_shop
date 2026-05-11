<?php
/**
 * Admin dashboard screens.
 */

if (!defined('ABSPATH')) {
    exit;
}

class WCS_Admin {
    public function hooks() {
        add_action('admin_menu', [$this, 'add_menu']);
        add_action('admin_enqueue_scripts', [$this, 'enqueue_assets']);
    }

    public function add_menu() {
        add_menu_page(
            __('Tin nhắn hỗ trợ', 'woo-chat-support'),
            __('Tin nhắn hỗ trợ', 'woo-chat-support'),
            'manage_woocommerce',
            'wcs-support-messages',
            [$this, 'render_page'],
            'dashicons-format-chat',
            56
        );
    }

    public function enqueue_assets($hook) {
        if ('toplevel_page_wcs-support-messages' !== $hook) {
            return;
        }

        wp_enqueue_style('wcs-admin', WCS_PLUGIN_URL . 'assets/css/admin.css', [], WCS_VERSION);
        wp_enqueue_script('wcs-admin', WCS_PLUGIN_URL . 'assets/js/admin.js', ['jquery'], WCS_VERSION, true);
        wp_localize_script(
            'wcs-admin',
            'wcsAdmin',
            [
                'ajaxUrl' => admin_url('admin-ajax.php'),
                'nonce' => wp_create_nonce('wcs_admin_nonce'),
                'pollInterval' => 5000,
                'conversationId' => isset($_GET['conversation_id']) ? absint($_GET['conversation_id']) : 0,
                'i18n' => [
                    'emptyMessage' => __('Vui lòng nhập nội dung trả lời.', 'woo-chat-support'),
                ],
            ]
        );
    }

    public function render_page() {
        if (!current_user_can('manage_woocommerce')) {
            wp_die(esc_html__('Bạn không có quyền truy cập trang này.', 'woo-chat-support'));
        }

        $conversation_id = isset($_GET['conversation_id']) ? absint($_GET['conversation_id']) : 0;

        if ($conversation_id > 0) {
            $conversation = WCS_Database::get_conversation($conversation_id);
            include WCS_PLUGIN_DIR . 'admin/views/conversation-detail.php';
            return;
        }

        $conversations = WCS_Database::get_admin_conversations();
        include WCS_PLUGIN_DIR . 'admin/views/conversations-list.php';
    }
}
