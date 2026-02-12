<?php
if (!defined('ABSPATH')) exit;

add_action('wp_ajax_nexora_get_device_types', 'nexora_get_device_types');
add_action('wp_ajax_nopriv_nexora_get_device_types', 'nexora_get_device_types');
add_action('wp_ajax_nexora_get_device_brands', 'nexora_get_device_brands');
add_action('wp_ajax_nopriv_nexora_get_device_brands', 'nexora_get_device_brands');
add_action('wp_ajax_nexora_get_device_series', 'nexora_get_device_series');
add_action('wp_ajax_nopriv_nexora_get_device_series', 'nexora_get_device_series');
add_action('wp_ajax_nexora_get_device_models', 'nexora_get_device_models');
add_action('wp_ajax_nopriv_nexora_get_device_models', 'nexora_get_device_models');

function nexora_get_device_types() {
    check_ajax_referer('nexora_nonce', 'nonce');
    global $wpdb;
    $table = $wpdb->prefix . 'nexora_devices';
    $types = $wpdb->get_results($wpdb->prepare("SELECT id, name FROM $table WHERE type = %s", 'type'));
    wp_send_json_success($types);
}

function nexora_get_device_brands() {
    check_ajax_referer('nexora_nonce', 'nonce');
    global $wpdb;
    $type_id = intval($_POST['type_id'] ?? 0);
    $table = $wpdb->prefix . 'nexora_devices';
    $brands = $wpdb->get_results($wpdb->prepare("SELECT id, name FROM $table WHERE type = %s AND parent_id = %d", 'brand', $type_id));
    wp_send_json_success($brands);
}

function nexora_get_device_series() {
    check_ajax_referer('nexora_nonce', 'nonce');
    global $wpdb;
    $brand_id = intval($_POST['brand_id'] ?? 0);
    $table = $wpdb->prefix . 'nexora_devices';
    $series = $wpdb->get_results($wpdb->prepare("SELECT id, name FROM $table WHERE type = %s AND parent_id = %d", 'series', $brand_id));
    wp_send_json_success($series);
}

function nexora_get_device_models() {
    check_ajax_referer('nexora_nonce', 'nonce');
    global $wpdb;
    $series_id = intval($_POST['series_id'] ?? 0);
    $table = $wpdb->prefix . 'nexora_devices';
    $models = $wpdb->get_results($wpdb->prepare("SELECT id, name FROM $table WHERE type = %s AND parent_id = %d", 'model', $series_id));
    wp_send_json_success($models);
} 