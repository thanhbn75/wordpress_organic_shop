# Website Bán Thực Phẩm Hữu Cơ

## Tên đề tài
Xây dựng Website Bán Thực Phẩm Hữu Cơ bằng WordPress

---

## Giới thiệu website/hệ thống
Website bán thực phẩm hữu cơ được xây dựng nhằm hỗ trợ người dùng tìm kiếm, xem thông tin và mua các sản phẩm hữu cơ trực tuyến một cách thuận tiện và nhanh chóng.

Hệ thống được phát triển trên nền tảng WordPress kết hợp WooCommerce, cung cấp các chức năng cơ bản của một website thương mại điện tử như:

- Hiển thị danh sách sản phẩm
- Tìm kiếm sản phẩm
- Quản lý giỏ hàng
- Đặt hàng trực tuyến
- Quản lý tài khoản người dùng
- Quản lý sản phẩm và đơn hàng trong trang quản trị
- Giao diện thân thiện, dễ sử dụng

Dự án hướng đến việc xây dựng một hệ thống bán hàng trực tuyến hiện đại, dễ triển khai và phù hợp với nhu cầu kinh doanh thực phẩm hữu cơ.

---

## Danh sách thành viên

| STT | Họ và tên | MSSV |
|-----|------------|------------|
| 1 | Nguyễn Đức Minh | 23810310259 |
| 2 | Ngô Đức Dũng | 23810310264 |
| 3 | Vũ Minh Thành | 23810310259 |

---

## Phân công nhiệm vụ cụ thể

| Thành viên | Nhiệm vụ |
|------------|------------|
| Nguyễn Đức Minh | Thiết kế giao diện website, xây dựng trang chủ và các trang sản phẩm |
| Ngô Đức Dũng | Cấu hình WordPress, WooCommerce và xử lý cơ sở dữ liệu |
| Vũ Minh Thành | Tích hợp chức năng, kiểm thử hệ thống và triển khai website |

---

## Công nghệ sử dụng

### Frontend
- HTML5
- CSS3
- JavaScript
- Bootstrap

### Backend
- WordPress
- PHP

### Database
- MySQL

### Plugin sử dụng
- WooCommerce
- Elementor
- Contact Form 7
- Yoast SEO

### Công cụ hỗ trợ
- XAMPP
- Git & GitHub

---

## Hướng dẫn cài đặt

### Bước 1: Clone project
```bash
git clone https://github.com/thanhbn75/wordpress_organic_shop.git
```

### Bước 2: Di chuyển project vào thư mục htdocs

Ví dụ:
```text
C:\xampp\htdocs\
```

### Bước 3: Khởi động XAMPP

Mở:
- Apache
- MySQL

### Bước 4: Tạo database

Truy cập:
```text
http://localhost/phpmyadmin
```

Tạo database mới:
```text
wordpress_organic_shop
```

### Bước 5: Import database

- Chọn database vừa tạo
- Chọn tab Import
- Import file `organic_shop.sql` của project (nếu có)

### Bước 6: Cấu hình kết nối database

Chỉnh file:
```text
wp-config.php
```

Ví dụ:
```php
define('DB_NAME', 'wordpress_organic_shop');
define('DB_USER', 'root');
define('DB_PASSWORD', '');
define('DB_HOST', 'localhost');
```

---

## Hướng dẫn chạy project

Sau khi cấu hình hoàn tất, truy cập:

```text
http://localhost/wordpress_organic_shop
```

Trang quản trị:
```text
http://localhost/wordpress_organic_shop/wp-admin
```

---

## Tài khoản demo (nếu có)

### Admin
```text
Username: admin
Password: admin123
```

### User
```text
Username: user
Password: user123
```

> Có thể thay đổi tùy theo cấu hình thực tế của hệ thống.

---

## Hình ảnh minh họa hệ thống

### Trang chủ
- Banner giới thiệu sản phẩm hữu cơ
- Danh sách sản phẩm nổi bật
- Danh mục sản phẩm

### Trang sản phẩm
- Hiển thị chi tiết sản phẩm
- Giá sản phẩm
- Hình ảnh minh họa
- Nút thêm vào giỏ hàng

### Trang giỏ hàng
- Danh sách sản phẩm đã chọn
- Tổng tiền đơn hàng

### Trang quản trị
- Quản lý sản phẩm
- Quản lý đơn hàng
- Quản lý người dùng

---

## Link video demo

Cập nhật sau.

---

## Link online đã deploy (nếu có)

Cập nhật sau.

---

## Repository GitHub

https://github.com/thanhbn75/wordpress_organic_shop.git