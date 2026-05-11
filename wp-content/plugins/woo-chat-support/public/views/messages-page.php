<?php
/**
 * Customer messages page.
 */

if (!defined('ABSPATH')) {
    exit;
}
?>
<div class="wcs-chat wcs-chat--single" data-active-conversation="<?php echo esc_attr($active_id); ?>">
    <section class="wcs-chat__panel">
        <div class="wcs-new-conversation" <?php echo $active_id ? 'hidden' : ''; ?>>
            <input type="text" class="wcs-input" data-wcs-subject placeholder="<?php esc_attr_e('Tiêu đề', 'woo-chat-support'); ?>">
            <textarea class="wcs-textarea" data-wcs-first-message placeholder="<?php esc_attr_e('Nội dung tin nhắn', 'woo-chat-support'); ?>"></textarea>
            <button type="button" class="wcs-button" data-wcs-create-conversation><?php esc_html_e('Gửi tin nhắn', 'woo-chat-support'); ?></button>
        </div>

        <div class="wcs-chat-box" <?php echo $active_id ? '' : 'hidden'; ?>>
            <div class="wcs-messages" data-wcs-messages></div>
            <form class="wcs-reply-form" data-wcs-reply-form>
                <textarea class="wcs-textarea" data-wcs-message placeholder="<?php esc_attr_e('Nhập tin nhắn...', 'woo-chat-support'); ?>"></textarea>
                <button type="submit" class="wcs-button"><?php esc_html_e('Gửi', 'woo-chat-support'); ?></button>
            </form>
        </div>
    </section>
</div>
