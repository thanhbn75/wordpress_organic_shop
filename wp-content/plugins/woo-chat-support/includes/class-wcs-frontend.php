<?php
/**
 * WooCommerce My Account frontend.
 */

if (!defined('ABSPATH')) {
    exit;
}

class WCS_Frontend {
    public function hooks() {
        add_action('init', [$this, 'add_endpoint']);
        add_filter('woocommerce_account_menu_items', [$this, 'add_menu_item']);
        add_action('woocommerce_account_messages_endpoint', [$this, 'render_messages_page']);
        add_action('wp_enqueue_scripts', [$this, 'enqueue_assets']);
        add_action('wp_footer', [$this, 'render_floating_chat']);
    }

    public function add_endpoint() {
        add_rewrite_endpoint('messages', EP_ROOT | EP_PAGES);
    }

    public function add_menu_item($items) {
        $logout = [];

        if (isset($items['customer-logout'])) {
            $logout['customer-logout'] = $items['customer-logout'];
            unset($items['customer-logout']);
        }

        $count = is_user_logged_in() ? WCS_Database::get_user_unread_count(get_current_user_id()) : 0;
        $label = __('Tin nhắn', 'woo-chat-support');

        if ($count > 0) {
            $label .= ' (' . $count . ')';
        }

        $items['messages'] = $label;

        return array_merge($items, $logout);
    }

    public function enqueue_assets() {
        if (is_admin()) {
            return;
        }

        wp_enqueue_style('wcs-public', WCS_PLUGIN_URL . 'assets/css/public.css', [], WCS_VERSION);
        wp_enqueue_script('wcs-public', WCS_PLUGIN_URL . 'assets/js/public.js', ['jquery'], WCS_VERSION, true);
        wp_localize_script(
            'wcs-public',
            'wcsPublic',
            [
                'ajaxUrl' => admin_url('admin-ajax.php'),
                'nonce' => wp_create_nonce('wcs_public_nonce'),
                'pollInterval' => 5000,
                'i18n' => [
                    'emptyMessage' => __('Vui lòng nhập nội dung tin nhắn.', 'woo-chat-support'),
                    'emptySubject' => __('Vui lòng nhập tiêu đề.', 'woo-chat-support'),
                ],
            ]
        );
    }

    public function render_floating_chat() {
        if (is_admin() || $this->is_messages_endpoint()) {
            return;
        }

        if (!is_user_logged_in()) {
            include WCS_PLUGIN_DIR . 'public/views/floating-chat.php';
            return;
        }

        $user_id = get_current_user_id();
        $conversations = WCS_Database::get_user_conversations($user_id);
        $active_id = !empty($conversations) ? (int) $conversations[0]->id : 0;
        $unread_count = WCS_Database::get_user_unread_count($user_id);

        include WCS_PLUGIN_DIR . 'public/views/floating-chat.php';
    }

    public function render_messages_page() {
        if (!is_user_logged_in()) {
            echo '<p>' . esc_html__('Bạn cần đăng nhập để sử dụng tin nhắn.', 'woo-chat-support') . '</p>';
            return;
        }

        $user_id = get_current_user_id();
        $conversations = WCS_Database::get_user_conversations($user_id);
        $active_id = !empty($conversations) ? (int) $conversations[0]->id : 0;

        include WCS_PLUGIN_DIR . 'public/views/messages-page.php';
    }

    private function is_messages_endpoint() {
        if (!function_exists('is_wc_endpoint_url')) {
            return false;
        }

        return is_account_page() && is_wc_endpoint_url('messages');
    }
}
