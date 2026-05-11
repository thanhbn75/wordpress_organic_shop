<?php
/**
 * Database helpers for conversations and messages.
 */

if (!defined('ABSPATH')) {
    exit;
}

class WCS_Database {
    public static function conversations_table() {
        global $wpdb;
        return $wpdb->prefix . 'wcs_conversations';
    }

    public static function messages_table() {
        global $wpdb;
        return $wpdb->prefix . 'wcs_messages';
    }

    public static function create_tables() {
        global $wpdb;

        require_once ABSPATH . 'wp-admin/includes/upgrade.php';

        $charset_collate = $wpdb->get_charset_collate();
        $conversations = self::conversations_table();
        $messages = self::messages_table();

        $sql_conversations = "CREATE TABLE {$conversations} (
            id BIGINT(20) UNSIGNED NOT NULL AUTO_INCREMENT,
            user_id BIGINT(20) UNSIGNED NOT NULL,
            subject VARCHAR(255) NOT NULL DEFAULT '',
            status VARCHAR(30) NOT NULL DEFAULT 'open',
            created_at DATETIME NOT NULL,
            updated_at DATETIME NOT NULL,
            PRIMARY KEY (id),
            KEY user_id (user_id),
            KEY status (status),
            KEY updated_at (updated_at)
        ) {$charset_collate};";

        $sql_messages = "CREATE TABLE {$messages} (
            id BIGINT(20) UNSIGNED NOT NULL AUTO_INCREMENT,
            conversation_id BIGINT(20) UNSIGNED NOT NULL,
            sender_id BIGINT(20) UNSIGNED NOT NULL,
            message LONGTEXT NOT NULL,
            is_admin TINYINT(1) NOT NULL DEFAULT 0,
            is_read TINYINT(1) NOT NULL DEFAULT 0,
            created_at DATETIME NOT NULL,
            PRIMARY KEY (id),
            KEY conversation_id (conversation_id),
            KEY sender_id (sender_id),
            KEY is_read (is_read),
            KEY created_at (created_at)
        ) {$charset_collate};";

        dbDelta($sql_conversations);
        dbDelta($sql_messages);
    }

    public static function get_user_conversations($user_id) {
        global $wpdb;

        return $wpdb->get_results(
            $wpdb->prepare(
                'SELECT * FROM ' . self::conversations_table() . ' WHERE user_id = %d ORDER BY updated_at DESC LIMIT 1',
                $user_id
            )
        );
    }

    public static function get_user_conversation($user_id) {
        global $wpdb;

        return $wpdb->get_row(
            $wpdb->prepare(
                'SELECT * FROM ' . self::conversations_table() . ' WHERE user_id = %d ORDER BY updated_at DESC LIMIT 1',
                $user_id
            )
        );
    }

    public static function get_conversation($conversation_id) {
        global $wpdb;

        return $wpdb->get_row(
            $wpdb->prepare(
                'SELECT * FROM ' . self::conversations_table() . ' WHERE id = %d',
                $conversation_id
            )
        );
    }

    public static function create_conversation($user_id, $subject) {
        global $wpdb;

        $existing = self::get_user_conversation($user_id);

        if ($existing) {
            return (int) $existing->id;
        }

        $now = current_time('mysql');
        $wpdb->insert(
            self::conversations_table(),
            [
                'user_id' => absint($user_id),
                'subject' => sanitize_text_field($subject),
                'status' => 'open',
                'created_at' => $now,
                'updated_at' => $now,
            ],
            ['%d', '%s', '%s', '%s', '%s']
        );

        return (int) $wpdb->insert_id;
    }

    public static function add_message($conversation_id, $sender_id, $message, $is_admin = false) {
        global $wpdb;

        $now = current_time('mysql');
        $wpdb->insert(
            self::messages_table(),
            [
                'conversation_id' => absint($conversation_id),
                'sender_id' => absint($sender_id),
                'message' => wp_kses_post($message),
                'is_admin' => $is_admin ? 1 : 0,
                'is_read' => 0,
                'created_at' => $now,
            ],
            ['%d', '%d', '%s', '%d', '%d', '%s']
        );

        $wpdb->update(
            self::conversations_table(),
            ['updated_at' => $now],
            ['id' => absint($conversation_id)],
            ['%s'],
            ['%d']
        );

        return (int) $wpdb->insert_id;
    }

    public static function get_messages($conversation_id, $after_id = 0) {
        global $wpdb;

        return $wpdb->get_results(
            $wpdb->prepare(
                'SELECT * FROM ' . self::messages_table() . ' WHERE conversation_id = %d AND id > %d ORDER BY created_at ASC, id ASC',
                $conversation_id,
                $after_id
            )
        );
    }

    public static function mark_read($conversation_id, $reader_is_admin) {
        global $wpdb;

        $admin_value = $reader_is_admin ? 0 : 1;

        return $wpdb->query(
            $wpdb->prepare(
                'UPDATE ' . self::messages_table() . ' SET is_read = 1 WHERE conversation_id = %d AND is_admin = %d',
                $conversation_id,
                $admin_value
            )
        );
    }

    public static function get_admin_conversations() {
        global $wpdb;

        $conversations = self::conversations_table();
        $messages = self::messages_table();

        return $wpdb->get_results(
            "SELECT c.*,
                u.display_name,
                u.user_email,
                lm.message AS latest_message,
                lm.created_at AS latest_message_at,
                SUM(CASE WHEN m.is_admin = 0 AND m.is_read = 0 THEN 1 ELSE 0 END) AS unread_count
            FROM {$conversations} c
            LEFT JOIN {$wpdb->users} u ON u.ID = c.user_id
            LEFT JOIN {$messages} m ON m.conversation_id = c.id
            LEFT JOIN {$messages} lm ON lm.id = (
                SELECT id FROM {$messages}
                WHERE conversation_id = c.id
                ORDER BY created_at DESC, id DESC
                LIMIT 1
            )
            WHERE c.id = (
                SELECT latest_c.id FROM {$conversations} latest_c
                WHERE latest_c.user_id = c.user_id
                ORDER BY latest_c.updated_at DESC, latest_c.id DESC
                LIMIT 1
            )
            GROUP BY c.id
            ORDER BY c.updated_at DESC"
        );
    }

    public static function get_user_unread_count($user_id) {
        global $wpdb;

        return (int) $wpdb->get_var(
            $wpdb->prepare(
                'SELECT COUNT(m.id)
                FROM ' . self::messages_table() . ' m
                INNER JOIN ' . self::conversations_table() . ' c ON c.id = m.conversation_id
                WHERE c.user_id = %d AND m.is_admin = 1 AND m.is_read = 0',
                $user_id
            )
        );
    }

    public static function user_can_access_conversation($conversation_id, $user_id) {
        $conversation = self::get_conversation($conversation_id);
        return $conversation && (int) $conversation->user_id === (int) $user_id;
    }
}
