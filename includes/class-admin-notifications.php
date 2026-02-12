<?php
if (!defined('ABSPATH')) exit;

class Nexora_Admin_Notifications {
    
    public function __construct() {
        add_action('admin_menu', array($this, 'add_notification_badge'), 999);
        add_action('admin_enqueue_scripts', array($this, 'enqueue_admin_scripts'));
        add_action('wp_ajax_nexora_update_request_status', array($this, 'update_request_status'));
        add_action('wp_ajax_get_new_requests_count', array($this, 'get_new_requests_count'));
        add_filter('nexora_admin_menu_items', array($this, 'add_status_column'));
        add_action('admin_head', array($this, 'add_admin_styles'));
        add_action('wp_ajax_debug_notifications', array($this, 'debug_notifications'));
        add_action('wp_ajax_nexora_get_admin_notifications', array($this, 'ajax_get_admin_notifications'));
        add_action('wp_ajax_nexora_mark_notification_read', array($this, 'ajax_mark_notification_read'));
        add_action('wp_ajax_nexora_get_new_users_count', array($this, 'ajax_get_new_users_count'));
        add_action('wp_ajax_nexora_delete_notification', array($this, 'ajax_delete_notification'));
        $this->update_requests_without_status();
    }
    
    public function add_notification_badge() {
        global $menu;
        foreach ($menu as $key => $item) {
            if (isset($item[2]) && $item[2] === 'Nexora Service Suite-service-request') {
                $new_count = $this->get_new_requests_count_value();
                if ($new_count > 0) {
                    $menu[$key][0] .= " <span class='awaiting-mod'>$new_count</span>";
                }
                break;
            }
        }
    }
    
    public function enqueue_admin_scripts($hook) {
        if (strpos($hook, 'Nexora Service Suite') !== false) {
            wp_enqueue_style(
                'Nexora Service Suite-admin-styles',
                NEXORA_PLUGIN_URL . 'assets/css/Nexora Service Suite-admin.css',
                array(),
                '1.1'
            );
            
            wp_enqueue_script(
                'Nexora Service Suite-admin-notifications',
                NEXORA_PLUGIN_URL . 'assets/js/admin-notifications.js',
                array('jquery'),
                filemtime(NEXORA_PLUGIN_DIR . 'assets/js/admin-notifications.js'),
                true
            );
            
            wp_localize_script('Nexora Service Suite-admin-notifications', 'nexora_notifications', array(
                'ajax_url' => admin_url('admin-ajax.php'),
                'nonce' => wp_create_nonce('nexora_notifications_nonce')
            ));
        }
    }
    
    public function get_new_requests_count() {
        check_ajax_referer('nexora_notifications_nonce', 'nonce');
        
        $count = $this->get_new_requests_count_value();
        
        wp_send_json_success(array('count' => $count));
    }
    
    
    private function get_new_requests_count_value() {
        global $wpdb;
        $cache_key = 'nexora_new_requests_count';
        $cached_count = wp_cache_get($cache_key);
        
        if ($cached_count !== false) {
            return intval($cached_count);
        }
        $new_status_id = $wpdb->get_var("SELECT id FROM {$wpdb->prefix}nexora_service_status WHERE is_default = 1 LIMIT 1");
        
        if (!$new_status_id) {
            $new_status_id = $wpdb->get_var("SELECT id FROM {$wpdb->prefix}nexora_service_status WHERE title = 'Neu' LIMIT 1");
        }
        
        if (!$new_status_id) {
            $new_status_id = $wpdb->get_var("SELECT id FROM {$wpdb->prefix}nexora_service_status WHERE title = 'In Bearbeitung' LIMIT 1");
        }
        
        if (!$new_status_id) {
            wp_cache_set($cache_key, 0, '', 30);
            return 0;
        }
        $count = $wpdb->get_var($wpdb->prepare(
            "SELECT COUNT(*) FROM {$wpdb->prefix}nexora_service_requests WHERE status_id = %d",
            $new_status_id
        ));
        
        $count = intval($count);
        wp_cache_set($cache_key, $count, '', 30);
        
        return $count;
    }
    
    public function update_request_status() {
        check_ajax_referer('nexora_notifications_nonce', 'nonce');
        
        if (!current_user_can('manage_options')) {
            wp_send_json_error('Unbefugter Zugriff');
        }
        
        $request_id = intval($_POST['request_id']);
        $new_status_id = intval($_POST['status_id']);
        
        global $wpdb;
        $old_status_id = $wpdb->get_var($wpdb->prepare(
            "SELECT status_id FROM {$wpdb->prefix}nexora_service_requests WHERE id = %d",
            $request_id
        ));
        
        $result = $wpdb->update(
            $wpdb->prefix . 'nexora_service_requests',
            array(
                'status_id' => $new_status_id,
                'updated_at' => current_time('mysql')
            ),
            array('id' => $request_id),
            array('%d', '%s'),
            array('%d')
        );
        
        if ($result !== false) {
            if (class_exists('Nexora_Activity_Logger') && $old_status_id != $new_status_id) {
                $logger = new Nexora_Activity_Logger();
                $logger->log_status_change($request_id, $old_status_id, $new_status_id);
            }
            if ($old_status_id != $new_status_id) {
                $old_status_name = $wpdb->get_var($wpdb->prepare(
                    "SELECT title FROM {$wpdb->prefix}nexora_service_status WHERE id = %d",
                    $old_status_id
                ));
                $new_status_name = $wpdb->get_var($wpdb->prepare(
                    "SELECT title FROM {$wpdb->prefix}nexora_service_status WHERE id = %d",
                    $new_status_id
                ));
                
                $current_user_id = get_current_user_id();
                self::notify_status_change($request_id, $old_status_name, $new_status_name, $current_user_id);
            }
            $new_count = $this->get_new_requests_count_value();
            wp_send_json_success(array(
                'message' => 'Status erfolgreich aktualisiert',
                'new_count' => $new_count
            ));
        } else {
            wp_send_json_error('Fehler beim Aktualisieren des Status');
        }
    }
    
    public function add_status_column($items) {
        add_filter('manage_nexora_service_requests_columns', array($this, 'add_status_column_header'));
        add_action('manage_nexora_service_requests_custom_column', array($this, 'add_status_column_content'), 10, 2);
        
        return $items;
    }
    
    public function add_status_column_header($columns) {
        $new_columns = array();
        foreach ($columns as $key => $column) {
            $new_columns[$key] = $column;
            if ($key === 'description') {
                $new_columns['status'] = 'Status';
            }
        }
        return $new_columns;
    }
    
    public function add_status_column_content($column, $post_id) {
        if ($column === 'status') {
            global $wpdb;
            $request = $wpdb->get_row($wpdb->prepare(
                "SELECT status_id FROM {$wpdb->prefix}nexora_service_requests WHERE id = %d",
                $post_id
            ));
            
            if ($request) {
                $statuses = $wpdb->get_results("SELECT id, title FROM {$wpdb->prefix}nexora_service_status ORDER BY id");
                
                echo '<div class="status-selector" data-request-id="' . esc_attr($post_id) . '">';
                echo '<select class="request-status-select" data-request-id="' . esc_attr($post_id) . '">';
                
                foreach ($statuses as $status) {
                    $selected = ($status->id == $request->status_id) ? 'selected' : '';
                    echo '<option value="' . esc_attr($status->id) . '" ' . $selected . '>';
                    echo esc_html($status->title);
                    echo '</option>';
                }
                
                echo '</select>';
                echo '<span class="status-badge status-' . esc_attr($request->status_id) . '">';
                echo esc_html($wpdb->get_var($wpdb->prepare(
                    "SELECT title FROM {$wpdb->prefix}nexora_service_status WHERE id = %d",
                    $request->status_id
                )));
                echo '</span>';
                echo '</div>';
            }
        }
    }
    private function update_requests_without_status() {
        global $wpdb;
        $new_status_id = $wpdb->get_var("SELECT id FROM {$wpdb->prefix}nexora_service_status WHERE title = 'Neu' LIMIT 1");
        
        if ($new_status_id) {
            $wpdb->update(
                $wpdb->prefix . 'nexora_service_requests',
                array('status_id' => $new_status_id),
                array('status_id' => 0),
                array('%d'),
                array('%d')
            );
            $wpdb->query("UPDATE {$wpdb->prefix}nexora_service_requests SET status_id = {$new_status_id} WHERE status_id IS NULL");
        }
    }
    public function debug_notifications() {
        check_ajax_referer('nexora_notifications_nonce', 'nonce');
        
        if (!current_user_can('manage_options')) {
            wp_send_json_error('Unbefugter Zugriff');
        }
        
        global $wpdb;
        
        $debug_info = array(
            'new_status_id' => $wpdb->get_var("SELECT id FROM {$wpdb->prefix}nexora_service_status WHERE title = 'Neu'"),
            'total_requests' => $wpdb->get_var("SELECT COUNT(*) FROM {$wpdb->prefix}nexora_service_requests"),
            'new_requests' => $this->get_new_requests_count_value(),
            'statuses' => $wpdb->get_results("SELECT id, title FROM {$wpdb->prefix}nexora_service_status ORDER BY id"),
            'requests_without_status' => $wpdb->get_var("SELECT COUNT(*) FROM {$wpdb->prefix}nexora_service_requests WHERE status_id = 0 OR status_id IS NULL")
        );
        
        wp_send_json_success($debug_info);
    }
    public function add_admin_styles() {
        ?>
        <style>
        .awaiting-mod {
            background: #e74c3c;
            color: white;
            border-radius: 50%;
            padding: 2px 6px;
            font-size: 11px;
            font-weight: bold;
            margin-left: 5px;
            display: inline-block;
            min-width: 18px;
            text-align: center;
        }
        
        .status-selector {
            position: relative;
        }
        
        .request-status-select {
            width: 100%;
            padding: 6px 8px;
            border: 1px solid #ddd;
            border-radius: 4px;
            background: white;
            font-size: 12px;
        }
        
        .status-badge {
            display: inline-block;
            padding: 4px 8px;
            border-radius: 12px;
            font-size: 11px;
            font-weight: 500;
            text-transform: uppercase;
            margin-top: 4px;
        }
        
        .status-1 {
            background: #fff3cd;
            color: #856404;
        }
        
        .status-2 {
            background: #d1ecf1;
            color: #0c5460;
        }
        
        .status-3 {
            background: #d4edda;
            color: #155724;
        }
        
        .status-4 {
            background: #f8d7da;
            color: #721c24;
        }
        
        .status-5 {
            background: #e2e3e5;
            color: #383d41;
        }
        
        
        .status-updating {
            opacity: 0.6;
            pointer-events: none;
        }
        
        .status-updated {
            animation: statusUpdate 0.5s ease;
        }
        
        @keyframes statusUpdate {
            0% { transform: scale(1); }
            50% { transform: scale(1.1); }
            100% { transform: scale(1); }
        }
        </style>
        <?php
    }

    public function ajax_get_admin_notifications() {
        global $wpdb;
        $table = $wpdb->prefix . 'nexora_admin_notifications';
        $cache_key = 'nexora_admin_notifications';
        $cached_notifications = wp_cache_get($cache_key);
        
        if ($cached_notifications !== false) {
            wp_send_json_success($cached_notifications);
            return;
        }
        
        $notifications = $wpdb->get_results("SELECT * FROM $table WHERE status = 'unread' ORDER BY created_at DESC LIMIT 30");
        wp_cache_set($cache_key, $notifications, '', 15);
        
        wp_send_json_success($notifications);
    }

    public function ajax_mark_notification_read() {
        global $wpdb;
        $id = intval($_POST['id']);
        $table = $wpdb->prefix . 'nexora_admin_notifications';
        $wpdb->update($table, array('status' => 'read'), array('id' => $id));
        wp_cache_delete('nexora_admin_notifications');
        
        wp_send_json_success();
    }
    
    
    public function ajax_get_new_users_count() {
        check_ajax_referer('nexora_nonce', 'nonce');
        
        if (!current_user_can('manage_options')) {
            wp_send_json_error('Unbefugter Zugriff');
        }
        
        global $wpdb;
        $count = $wpdb->get_var("
            SELECT COUNT(*) 
            FROM {$wpdb->users} u 
            LEFT JOIN {$wpdb->prefix}nexora_user_status us ON u.ID = us.user_id 
            WHERE us.destination_status_id IS NULL OR us.destination_status_id = 0
        ");
        
        $count = intval($count);
        wp_send_json_success(array('count' => $count));
    }
    
    
    public static function create_notification($type, $message, $related_id = null, $user_id = null) {
        global $wpdb;
        
        $table = $wpdb->prefix . 'nexora_admin_notifications';
        
        $data = array(
            'type' => sanitize_text_field($type),
            'message' => sanitize_text_field($message),
            'related_id' => $related_id ? intval($related_id) : null,
            'user_id' => $user_id ? intval($user_id) : null,
            'status' => 'unread',
            'created_at' => current_time('mysql')
        );
        
        $result = $wpdb->insert($table, $data);
        
        if ($result) {
            wp_cache_delete('nexora_admin_notifications');
            wp_cache_delete('nexora_new_requests_count');
            return $wpdb->insert_id;
        }
        
        return false;
    }
    
    
    public static function notify_new_service_request($request_id, $user_id, $serial, $model) {
        $user = get_user_by('id', $user_id);
        $username = $user ? $user->display_name : 'Unbekannter Benutzer';
        
        $message = sprintf(
            'Neue Service-Anfrage von %s - %s (Seriennummer: %s)',
            $username,
            $model,
            $serial
        );
        
        return self::create_notification('Neue Anfrage', $message, $request_id, $user_id);
    }
    
    
    public static function notify_new_invoice($invoice_id, $request_id, $amount) {
        $message = sprintf(
            'Neue Rechnung erstellt für Anfrage #%d - Betrag: €%.2f',
            $request_id,
            $amount
        );
        
        return self::create_notification('Neue Rechnung', $message, $invoice_id);
    }
    
    
    public static function notify_new_user_registration($user_id, $customer_type) {
        $user = get_user_by('id', $user_id);
        $username = $user ? $user->display_name : 'Unbekannter Benutzer';
        $type_text = $customer_type === 'business' ? 'Geschäftskunde' : 'Privatkunde';
        
        $message = sprintf(
            'Neuer Benutzer registriert: %s (%s)',
            $username,
            $type_text
        );
        
        return self::create_notification('Neue Registrierung', $message, null, $user_id);
    }
    
    
    public static function notify_status_change($request_id, $old_status, $new_status, $user_id) {
        $user = get_user_by('id', $user_id);
        $username = $user ? $user->display_name : 'System';
        
        $message = sprintf(
            'Status von Anfrage #%d geändert von "%s" zu "%s" durch %s',
            $request_id,
            $old_status,
            $new_status,
            $username
        );
        
        return self::create_notification('Status geändert', $message, $request_id, $user_id);
    }
    
    public function ajax_delete_notification() {
        check_ajax_referer('nexora_notifications_nonce', 'nonce');
        
        if (!current_user_can('manage_options')) {
            wp_send_json_error('Unbefugter Zugriff');
        }
        
        $notification_id = intval($_POST['id']);
        
        if (!$notification_id) {
            wp_send_json_error('Ungültige Notification-ID');
        }
        
        global $wpdb;
        $table = $wpdb->prefix . 'nexora_admin_notifications';
        
        $result = $wpdb->delete(
            $table,
            array('id' => $notification_id),
            array('%d')
        );
        
        if ($result === false) {
            wp_send_json_error('Fehler beim Löschen der Notification');
        }
        wp_cache_delete('nexora_admin_notifications');
        
        wp_send_json_success('Notification deleted successfully');
    }
}

new Nexora_Admin_Notifications(); 