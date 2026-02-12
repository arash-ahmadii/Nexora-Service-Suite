<?php
if (!defined('WP_UNINSTALL_PLUGIN')) {
    exit;
}

global $wpdb;

   $search_term = $wpdb->esc_like('nexora_') . '%';
    $deleted = $wpdb->query(
        $wpdb->prepare(
            "DELETE FROM $wpdb->usermeta WHERE meta_key LIKE %s",
            $search_term
        )
    );

$tables = array(
    $wpdb->prefix . 'nexora_services',
    $wpdb->prefix . 'nexora_service_status',
    $wpdb->prefix . 'nexora_brands',
    $wpdb->prefix . 'nexora_user_status',
    $wpdb->prefix . 'nexora_service_requests',
    $wpdb->prefix . 'nexora_request_comments',
    $wpdb->prefix . 'nexora_user_show_status',
    $wpdb->prefix . 'nexora_logs',
    $wpdb->prefix . 'nexora_viewed_logs',
    $wpdb->prefix . 'nexora_activity_logs'
);

foreach ($tables as $table) {
    $wpdb->query("DROP TABLE IF EXISTS $table");
}