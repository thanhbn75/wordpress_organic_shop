<?php
/**
 * Admin conversation detail.
 */

if (!defined('ABSPATH')) {
    exit;
}

if (!$conversation) {
    echo '<div class="wrap"><h1>' . esc_html__('Không tìm thấy hội thoại.', 'woo-chat-support') . '</h1></div>';
    return;
}

$user = get_userdata($conversation->user_id);
?>
<div class="wrap">
    <h1><?php echo esc_html($conversation->subject); ?></h1>
    <p>
        <a href="<?php echo esc_url(admin_url('admin.php?page=wcs-support-messages')); ?>">&larr; <?php esc_html_e('Quay lại danh sách hội thoại', 'woo-chat-support'); ?></a>
    </p>

    <div class="wcs-admin-chat" data-conversation-id="<?php echo esc_attr($conversation->id); ?>">
        <div class="wcs-admin-meta">
            <strong><?php esc_html_e('Khách hàng:', 'woo-chat-support'); ?></strong>
            <?php echo esc_html($user ? $user->display_name . ' (' . $user->user_email . ')' : __('Không rõ người dùng', 'woo-chat-support')); ?>
        </div>

        <div class="wcs-messages" data-wcs-admin-messages></div>

        <form class="wcs-admin-reply-form" data-wcs-admin-reply-form>
            <textarea class="wcs-textarea" data-wcs-admin-message placeholder="<?php esc_attr_e('Nhập phản hồi...', 'woo-chat-support'); ?>"></textarea>
            <button type="submit" class="button button-primary"><?php esc_html_e('Gửi phản hồi', 'woo-chat-support'); ?></button>
        </form>
    </div>
</div>
