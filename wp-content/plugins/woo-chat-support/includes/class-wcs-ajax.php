<?php
/**
 * AJAX handlers for chat actions.
 */

if (!defined('ABSPATH')) {
    exit;
}

class WCS_Ajax {
    public function hooks() {
        add_action('wp_ajax_wcs_create_conversation', [$this, 'create_conversation']);
        add_action('wp_ajax_wcs_send_message', [$this, 'send_message']);
        add_action('wp_ajax_wcs_load_messages', [$this, 'load_messages']);
        add_action('wp_ajax_wcs_mark_read', [$this, 'mark_read']);
        add_action('wp_ajax_wcs_admin_send_message', [$this, 'admin_send_message']);
        add_action('wp_ajax_wcs_admin_load_messages', [$this, 'admin_load_messages']);
        add_action('wp_ajax_wcs_admin_mark_read', [$this, 'admin_mark_read']);
    }

    public function create_conversation() {
        $this->verify_public_request();

        $subject = isset($_POST['subject']) ? sanitize_text_field(wp_unslash($_POST['subject'])) : '';
        $message = isset($_POST['message']) ? wp_kses_post(wp_unslash($_POST['message'])) : '';

        if ('' === $subject || '' === wp_strip_all_tags($message)) {
            wp_send_json_error(['message' => __('Vui lòng nhập đầy đủ tiêu đề và nội dung.', 'woo-chat-support')], 400);
        }

        $conversation_id = WCS_Database::create_conversation(get_current_user_id(), $subject);
        WCS_Database::add_message($conversation_id, get_current_user_id(), $message, false);
        $this->notify_admin($conversation_id, $message);

        wp_send_json_success([
            'conversation_id' => $conversation_id,
            'subject' => $subject,
            'updated_at' => mysql2date(get_option('date_format'), current_time('mysql')),
        ]);
    }

    public function send_message() {
        $this->verify_public_request();

        $conversation_id = isset($_POST['conversation_id']) ? absint($_POST['conversation_id']) : 0;
        $message = isset($_POST['message']) ? wp_kses_post(wp_unslash($_POST['message'])) : '';

        if (!$conversation_id || '' === wp_strip_all_tags($message)) {
            wp_send_json_error(['message' => __('Dữ liệu không hợp lệ.', 'woo-chat-support')], 400);
        }

        if (!WCS_Database::user_can_access_conversation($conversation_id, get_current_user_id())) {
            wp_send_json_error(['message' => __('Bạn không có quyền gửi tin nhắn này.', 'woo-chat-support')], 403);
        }

        WCS_Database::add_message($conversation_id, get_current_user_id(), $message, false);
        $this->notify_admin($conversation_id, $message);

        wp_send_json_success();
    }

    public function load_messages() {
        $this->verify_public_request();

        $conversation_id = isset($_POST['conversation_id']) ? absint($_POST['conversation_id']) : 0;
        $after_id = isset($_POST['after_id']) ? absint($_POST['after_id']) : 0;

        if (!WCS_Database::user_can_access_conversation($conversation_id, get_current_user_id())) {
            wp_send_json_error(['message' => __('Bạn không có quyền xem hội thoại này.', 'woo-chat-support')], 403);
        }

        WCS_Database::mark_read($conversation_id, false);
        wp_send_json_success(['messages' => $this->format_messages(WCS_Database::get_messages($conversation_id, $after_id))]);
    }

    public function mark_read() {
        $this->verify_public_request();

        $conversation_id = isset($_POST['conversation_id']) ? absint($_POST['conversation_id']) : 0;

        if (!WCS_Database::user_can_access_conversation($conversation_id, get_current_user_id())) {
            wp_send_json_error(['message' => __('Bạn không có quyền cập nhật hội thoại này.', 'woo-chat-support')], 403);
        }

        WCS_Database::mark_read($conversation_id, false);
        wp_send_json_success();
    }

    public function admin_send_message() {
        $this->verify_admin_request();

        $conversation_id = isset($_POST['conversation_id']) ? absint($_POST['conversation_id']) : 0;
        $message = isset($_POST['message']) ? wp_kses_post(wp_unslash($_POST['message'])) : '';
        $conversation = WCS_Database::get_conversation($conversation_id);

        if (!$conversation || '' === wp_strip_all_tags($message)) {
            wp_send_json_error(['message' => __('Dữ liệu không hợp lệ.', 'woo-chat-support')], 400);
        }

        WCS_Database::add_message($conversation_id, get_current_user_id(), $message, true);
        $this->notify_user($conversation, $message);

        wp_send_json_success();
    }

    public function admin_load_messages() {
        $this->verify_admin_request();

        $conversation_id = isset($_POST['conversation_id']) ? absint($_POST['conversation_id']) : 0;
        $after_id = isset($_POST['after_id']) ? absint($_POST['after_id']) : 0;

        if (!WCS_Database::get_conversation($conversation_id)) {
            wp_send_json_error(['message' => __('Không tìm thấy hội thoại.', 'woo-chat-support')], 404);
        }

        WCS_Database::mark_read($conversation_id, true);
        wp_send_json_success(['messages' => $this->format_messages(WCS_Database::get_messages($conversation_id, $after_id))]);
    }

    public function admin_mark_read() {
        $this->verify_admin_request();

        $conversation_id = isset($_POST['conversation_id']) ? absint($_POST['conversation_id']) : 0;
        WCS_Database::mark_read($conversation_id, true);

        wp_send_json_success();
    }

    private function verify_public_request() {
        check_ajax_referer('wcs_public_nonce', 'nonce');

        if (!is_user_logged_in()) {
            wp_send_json_error(['message' => __('Bạn cần đăng nhập.', 'woo-chat-support')], 401);
        }
    }

    private function verify_admin_request() {
        check_ajax_referer('wcs_admin_nonce', 'nonce');

        if (!current_user_can('manage_woocommerce')) {
            wp_send_json_error(['message' => __('Bạn không có quyền thực hiện thao tác này.', 'woo-chat-support')], 403);
        }
    }

    private function format_messages($messages) {
        return array_map(
            function ($message) {
                return [
                    'id' => (int) $message->id,
                    'message' => wpautop(wp_kses_post($message->message)),
                    'is_admin' => (bool) $message->is_admin,
                    'is_read' => (bool) $message->is_read,
                    'created_at' => mysql2date(get_option('date_format') . ' ' . get_option('time_format'), $message->created_at),
                ];
            },
            $messages
        );
    }

    private function notify_admin($conversation_id, $message) {
        $email = get_option('admin_email');
        $subject = sprintf(__('Tin nhắn hỗ trợ mới #%d', 'woo-chat-support'), $conversation_id);
        $body = wp_strip_all_tags($message);

        wp_mail($email, $subject, $body);
    }

    private function notify_user($conversation, $message) {
        $user = get_userdata($conversation->user_id);

        if (!$user) {
            return;
        }

        $subject = sprintf(__('Admin đã trả lời: %s', 'woo-chat-support'), $conversation->subject);
        $body = wp_strip_all_tags($message);

        wp_mail($user->user_email, $subject, $body);
    }
}
