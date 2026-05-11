<?php
/**
 * Admin conversations list.
 */

if (!defined('ABSPATH')) {
    exit;
}
?>
<div class="wrap">
    <h1><?php esc_html_e('Tin nhắn hỗ trợ', 'woo-chat-support'); ?></h1>

    <table class="widefat striped wcs-admin-table">
        <thead>
            <tr>
                <th><?php esc_html_e('Người dùng', 'woo-chat-support'); ?></th>
                <th><?php esc_html_e('Tin nhắn mới nhất', 'woo-chat-support'); ?></th>
                <th><?php esc_html_e('Trạng thái', 'woo-chat-support'); ?></th>
                <th><?php esc_html_e('Cập nhật', 'woo-chat-support'); ?></th>
                <th><?php esc_html_e('Thao tác', 'woo-chat-support'); ?></th>
            </tr>
        </thead>
        <tbody>
            <?php if (!empty($conversations)) : ?>
                <?php foreach ($conversations as $conversation) : ?>
                    <tr>
                        <td>
                            <strong><?php echo esc_html($conversation->display_name ?: $conversation->user_email); ?></strong>
                            <?php if ((int) $conversation->unread_count > 0) : ?>
                                <span class="wcs-badge"><?php echo esc_html($conversation->unread_count); ?></span>
                            <?php endif; ?>
                            <br>
                            <small><?php echo esc_html($conversation->subject); ?></small>
                        </td>
                        <td><?php echo esc_html(wp_trim_words(wp_strip_all_tags($conversation->latest_message), 18)); ?></td>
                        <td><?php echo (int) $conversation->unread_count > 0 ? esc_html__('Chưa đọc', 'woo-chat-support') : esc_html__('Đã đọc', 'woo-chat-support'); ?></td>
                        <td><?php echo esc_html(mysql2date(get_option('date_format') . ' ' . get_option('time_format'), $conversation->updated_at)); ?></td>
                        <td>
                            <a class="button button-primary" href="<?php echo esc_url(admin_url('admin.php?page=wcs-support-messages&conversation_id=' . absint($conversation->id))); ?>">
                                <?php esc_html_e('Mở', 'woo-chat-support'); ?>
                            </a>
                        </td>
                    </tr>
                <?php endforeach; ?>
            <?php else : ?>
                <tr>
                    <td colspan="5"><?php esc_html_e('Chưa có hội thoại nào.', 'woo-chat-support'); ?></td>
                </tr>
            <?php endif; ?>
        </tbody>
    </table>
</div>
