<?php
/**
 * Floating customer chat widget.
 */

if (!defined('ABSPATH')) {
    exit;
}

$account_url = function_exists('wc_get_account_endpoint_url') ? wc_get_account_endpoint_url('messages') : wp_login_url();
?>
<div class="wcs-floating" data-wcs-floating>
    <button type="button" class="wcs-floating__toggle" data-wcs-floating-toggle aria-expanded="false" aria-label="<?php esc_attr_e('Mở chat hỗ trợ', 'woo-chat-support'); ?>">
        <span class="wcs-floating__icon" aria-hidden="true"></span>
        <?php if (!empty($unread_count)) : ?>
            <span class="wcs-floating__badge"><?php echo esc_html((int) $unread_count); ?></span>
        <?php endif; ?>
    </button>

    <div class="wcs-floating__panel" data-wcs-floating-panel hidden>
        <div class="wcs-floating__topbar">
            <div>
                <strong><?php esc_html_e('Hỗ trợ trực tuyến', 'woo-chat-support'); ?></strong>
                <span><?php esc_html_e('Gửi tin nhắn cho cửa hàng', 'woo-chat-support'); ?></span>
            </div>
            <button type="button" class="wcs-floating__close" data-wcs-floating-close aria-label="<?php esc_attr_e('Đóng chat', 'woo-chat-support'); ?>">&times;</button>
        </div>

        <?php if (!is_user_logged_in()) : ?>
            <div class="wcs-floating__login">
                <p><?php esc_html_e('Bạn cần đăng nhập để chat với cửa hàng.', 'woo-chat-support'); ?></p>
                <a class="wcs-button" href="<?php echo esc_url($account_url); ?>"><?php esc_html_e('Đăng nhập', 'woo-chat-support'); ?></a>
            </div>
        <?php else : ?>
            <div class="wcs-chat wcs-chat--floating wcs-chat--single" data-active-conversation="<?php echo esc_attr($active_id); ?>">
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
        <?php endif; ?>
    </div>
</div>
