<?php
/**
 * Plugin uninstall cleanup.
 */

if (!defined('WP_UNINSTALL_PLUGIN')) {
    exit;
}

global $wpdb;

$wpdb->query('DROP TABLE IF EXISTS ' . $wpdb->prefix . 'wcs_messages');
$wpdb->query('DROP TABLE IF EXISTS ' . $wpdb->prefix . 'wcs_conversations');
