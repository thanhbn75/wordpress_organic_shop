<?php
/*
Plugin Name: Redirect Guests To Home With Popup Login
Description: Chuyển người chưa đăng nhập về trang chủ và tự mở popup login.
Version: 1.0
Author: Your Name
*/

if (!defined('ABSPATH')) {
    exit;
}

/*
|--------------------------------------------------------------------------
| Redirect guest về trang chủ
|--------------------------------------------------------------------------
*/

function rgth_redirect_guest_to_home() {

    // Nếu đã đăng nhập => cho truy cập
    if (is_user_logged_in()) {
        return;
    }

    // Không redirect trong admin
    if (is_admin()) {
        return;
    }

    // Không redirect ajax
    if (defined('DOING_AJAX') && DOING_AJAX) {
        return;
    }

    // Không redirect REST API
    if (defined('REST_REQUEST') && REST_REQUEST) {
        return;
    }

    // Không redirect wp-login.php
    if (strpos($_SERVER['REQUEST_URI'], 'wp-login.php') !== false) {
        return;
    }

    // Nếu KHÔNG phải trang chủ
    if (!is_front_page() && !is_home()) {

        // Redirect về trang chủ + thông báo login
        wp_redirect(home_url('/?login_required=1'));
        exit;
    }
}

add_action('template_redirect', 'rgth_redirect_guest_to_home');


/*
|--------------------------------------------------------------------------
| Hiển thị popup login + thông báo
|--------------------------------------------------------------------------
*/

function rgth_auto_open_popup() {

    // Chỉ cho guest
    if (is_user_logged_in()) {
        return;
    }

    // Chỉ chạy khi có param
    if (!isset($_GET['login_required'])) {
        return;
    }

    ?>

    <!-- Thông báo -->
    <div id="login-required-message">
        Bạn cần đăng nhập để truy cập nội dung này.
    </div>

    <style>
        #login-required-message{
            position: fixed;
            top: 20px;
            right: 20px;
            background: #ff4d4f;
            color: #fff;
            padding: 12px 18px;
            border-radius: 8px;
            z-index: 999999;
            font-size: 14px;
            font-weight: 500;
            box-shadow: 0 4px 12px rgba(0,0,0,0.2);
        }
    </style>

    <script>
    document.addEventListener("DOMContentLoaded", function(){

        // Mở popup login của plugin xoo-el
        if(typeof xoo_el_open_popup === 'function'){
            xoo_el_open_popup('login');
        }

        // Ẩn thông báo sau 4 giây
        setTimeout(function(){

            let msg = document.getElementById('login-required-message');

            if(msg){
                msg.remove();
            }

        }, 4000);

    });
    </script>

    <?php
}

add_action('wp_footer', 'rgth_auto_open_popup');