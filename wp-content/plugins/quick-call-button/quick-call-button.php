<?php
/*
Plugin Name: Quick Call Button
Description: Hien thi nut goi nhanh qua so dien thoai ngoai trang web.
Version: 1.1
Author: Your Name
*/

if (!defined('ABSPATH')) {
    exit;
}

add_shortcode('quick_call', 'qcb_display_call_button');

function qcb_sanitize_phone($phone) {
    return preg_replace('/[^0-9+]/', '', (string) $phone);
}

function qcb_get_safe_tel_url($phone) {
    return esc_url('tel:' . qcb_sanitize_phone($phone), array_merge(wp_allowed_protocols(), ['tel']));
}

function qcb_display_call_button($atts) {
    $atts = shortcode_atts([
        'phone' => '0123456789',
        'label' => 'Goi ngay',
        'bg_color' => '#28a745',
        'text_color' => '#ffffff',
    ], $atts);

    return qcb_render_call_button(
        $atts['phone'],
        $atts['label'],
        $atts['bg_color'],
        $atts['text_color']
    );
}

function qcb_render_call_button($phone, $label, $bg_color, $text_color) {
    $bg_color = sanitize_hex_color($bg_color) ?: '#28a745';
    $text_color = sanitize_hex_color($text_color) ?: '#ffffff';
    $style = 'background-color: ' . esc_attr($bg_color) . '; color: ' . esc_attr($text_color) . ';';

    $html = '<div class="quick-call-button">';
    $html .= '<a href="' . qcb_get_safe_tel_url($phone) . '" class="quick-call-link" style="' . $style . '" aria-label="' . esc_attr($label) . '">';
    $html .= '<i class="fas fa-phone-alt" aria-hidden="true"></i>';
    $html .= '<span>' . esc_html($label) . '</span>';
    $html .= '</a>';
    $html .= '</div>';

    return $html;
}

add_action('wp_enqueue_scripts', 'qcb_enqueue_font_awesome');
function qcb_enqueue_font_awesome() {
    wp_enqueue_style('font-awesome', 'https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css');
}

add_action('wp_enqueue_scripts', 'qcb_enqueue_styles');
function qcb_enqueue_styles() {
    wp_enqueue_style('qcb-styles', plugin_dir_url(__FILE__) . 'assets/style.css', [], '1.1');
}

add_action('wp_footer', 'qcb_display_call_button_on_all_pages');
function qcb_display_call_button_on_all_pages() {
    $phone = get_option('qcb_phone', '0987654321');
    $label = get_option('qcb_label', 'Goi ngay');
    $bg_color = get_option('qcb_bg_color', '#28a745');
    $text_color = get_option('qcb_text_color', '#ffffff');

    echo qcb_render_call_button($phone, $label, $bg_color, $text_color);
}

add_action('admin_menu', 'qcb_add_settings_menu');
function qcb_add_settings_menu() {
    add_menu_page(
        'Quick Call Settings',
        'Quick Call',
        'manage_options',
        'quick-call-settings',
        'qcb_settings_page',
        'dashicons-phone',
        100
    );
}

function qcb_settings_page() {
    ?>
    <div class="wrap">
        <h1>Cai dat Nut Goi Nhanh</h1>
        <form method="post" action="options.php">
            <?php
            settings_fields('qcb_settings_group');
            do_settings_sections('quick-call-settings');
            submit_button();
            ?>
        </form>
    </div>
    <?php
}

add_action('admin_init', 'qcb_register_settings');
function qcb_register_settings() {
    register_setting('qcb_settings_group', 'qcb_phone', ['sanitize_callback' => 'qcb_sanitize_phone']);
    register_setting('qcb_settings_group', 'qcb_label', ['sanitize_callback' => 'sanitize_text_field']);
    register_setting('qcb_settings_group', 'qcb_bg_color', ['sanitize_callback' => 'sanitize_hex_color']);
    register_setting('qcb_settings_group', 'qcb_text_color', ['sanitize_callback' => 'sanitize_hex_color']);

    add_settings_section(
        'qcb_settings_section',
        'Cau hinh Nut Goi',
        'qcb_settings_section_callback',
        'quick-call-settings'
    );

    add_settings_field('qcb_phone', 'So dien thoai', 'qcb_phone_callback', 'quick-call-settings', 'qcb_settings_section');
    add_settings_field('qcb_label', 'Noi dung nut', 'qcb_label_callback', 'quick-call-settings', 'qcb_settings_section');
    add_settings_field('qcb_bg_color', 'Mau nen', 'qcb_bg_color_callback', 'quick-call-settings', 'qcb_settings_section');
    add_settings_field('qcb_text_color', 'Mau chu', 'qcb_text_color_callback', 'quick-call-settings', 'qcb_settings_section');
}

function qcb_settings_section_callback() {
    echo '<p>Cau hinh cac tuy chon cho nut goi nhanh.</p>';
}

function qcb_phone_callback() {
    $value = get_option('qcb_phone', '0987654321');
    echo '<input type="text" name="qcb_phone" value="' . esc_attr($value) . '" class="regular-text">';
}

function qcb_label_callback() {
    $value = get_option('qcb_label', 'Goi ngay');
    echo '<input type="text" name="qcb_label" value="' . esc_attr($value) . '" class="regular-text">';
}

function qcb_bg_color_callback() {
    $value = get_option('qcb_bg_color', '#28a745');
    echo '<input type="color" name="qcb_bg_color" value="' . esc_attr($value) . '">';
}

function qcb_text_color_callback() {
    $value = get_option('qcb_text_color', '#ffffff');
    echo '<input type="color" name="qcb_text_color" value="' . esc_attr($value) . '">';
}
