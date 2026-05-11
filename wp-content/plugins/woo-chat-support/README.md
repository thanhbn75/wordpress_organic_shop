# Woo Chat Support

Plugin WordPress cho WooCommerce, cho phép khách hàng và quản trị viên nhắn tin hai chiều.

## Cài đặt

1. Đặt thư mục `woo-chat-support` vào `wp-content/plugins/`.
2. Vào WordPress Dashboard > Plugins.
3. Kích hoạt plugin `Woo Chat Support`.
4. Vào Settings > Permalinks và bấm Save Changes nếu endpoint `/my-account/messages` chưa hiển thị.

## Sử dụng

- Khách hàng đăng nhập vào My Account > Tin nhắn để tạo hội thoại và chat với admin.
- Admin vào Dashboard > Tin nhắn hỗ trợ để xem danh sách hội thoại và trả lời.

## Database

Plugin tạo custom tables bằng `dbDelta()` khi kích hoạt:

```sql
CREATE TABLE wp_wcs_conversations (
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
);

CREATE TABLE wp_wcs_messages (
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
);
```

Tiền tố `wp_` sẽ thay đổi theo `$wpdb->prefix` của website.

## Tính năng

- Endpoint WooCommerce: `/my-account/messages`
- Menu My Account: `Tin nhắn`
- Admin menu: `Tin nhắn hỗ trợ`
- AJAX gửi tin nhắn, tải tin nhắn mới, đánh dấu đã đọc
- Polling mỗi 5 giây
- Email notification khi có tin nhắn mới hoặc admin trả lời
- Đếm tin nhắn chưa đọc trong menu tài khoản
- Giao diện chat responsive

## Gỡ cài đặt

Khi xóa plugin từ WordPress, file `uninstall.php` sẽ xóa hai bảng:

- `{prefix}wcs_messages`
- `{prefix}wcs_conversations`
