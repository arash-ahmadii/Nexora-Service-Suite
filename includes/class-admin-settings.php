<?php
if (!defined('ABSPATH')) exit;

class Nexora_Admin_Settings {
    public function __construct() {
        error_log('=== Nexora_Admin_Settings CONSTRUCTOR CALLED ===');
        
        add_action('wp_ajax_nexora_save_general_settings', array($this, 'save_general_settings'));
        add_action('wp_ajax_nexora_save_notification_settings', array($this, 'save_notification_settings'));
        add_action('wp_ajax_nexora_save_invoice_settings', array($this, 'save_invoice_settings'));
        add_action('wp_ajax_nexora_save_system_settings', array($this, 'save_system_settings'));
        add_action('wp_ajax_nexora_repair_system', array($this, 'ajax_repair_system'));
        add_action('wp_ajax_create_complete_service_request_table', array($this, 'create_complete_service_request_table'));
        add_action('wp_ajax_save_service_request_data', array($this, 'save_service_request_data'));
        add_action('wp_ajax_delete_device_image', array($this, 'ajax_delete_device_image'));
        add_action('wp_ajax_save_notification_settings', array($this, 'handle_save_notification_settings'));
        add_action('wp_ajax_save_customer_notification_settings', array($this, 'save_customer_notification_settings'));
        add_action('wp_ajax_save_admin_notification_settings', array($this, 'save_admin_notification_settings'));
        add_action('wp_ajax_save_general_email_settings', array($this, 'save_general_email_settings'));
        
        error_log('=== AJAX ACTIONS REGISTERED ===');
        error_log('save_service_request_data action registered');
        error_log('delete_device_image action registered');
        add_action('init', array($this, 'test_ajax_endpoint'));
    }
    
    
    public function test_ajax_endpoint() {
        error_log('=== TESTING AJAX ENDPOINT ===');
        error_log('Testing save_service_request_data endpoint...');
        if (has_action('wp_ajax_save_service_request_data')) {
            error_log('✅ save_service_request_data action exists');
        } else {
            error_log('❌ save_service_request_data action NOT FOUND');
        }
        
        error_log('=== AJAX ENDPOINT TEST COMPLETED ===');
    }

    
    public function save_general_settings() {
        if (!wp_verify_nonce($_POST['nonce'], 'nexora_nonce')) {
            wp_die('Sicherheitscheck fehlgeschlagen');
        }
        if (!current_user_can('manage_options')) {
            wp_die('Unzureichende Berechtigungen');
        }

        $primary_color = sanitize_hex_color($_POST['primary_color']);
        $logo_url = esc_url_raw($_POST['logo_url']);
        $font_family = sanitize_text_field($_POST['font_family']);

        update_option('nexora_primary_color', $primary_color);
        update_option('nexora_logo_url', $logo_url);
        update_option('nexora_font_family', $font_family);

        wp_send_json_success('Allgemeine Einstellungen erfolgreich gespeichert');
    }

    
    public function save_notification_settings() {
        if (!wp_verify_nonce($_POST['nonce'], 'nexora_nonce')) {
            wp_die('Sicherheitscheck fehlgeschlagen');
        }
        if (!current_user_can('manage_options')) {
            wp_die('Unzureichende Berechtigungen');
        }

        $email_notifications = sanitize_text_field($_POST['email_notifications']);
        $admin_email = sanitize_email($_POST['admin_email']);
        $dashboard_notifications = sanitize_text_field($_POST['dashboard_notifications']);

        update_option('nexora_email_notifications', $email_notifications);
        update_option('nexora_admin_email', $admin_email);
        update_option('nexora_dashboard_notifications', $dashboard_notifications);

        wp_send_json_success('Benachrichtigungseinstellungen erfolgreich gespeichert');
    }

    
    public function save_invoice_settings() {
        if (!wp_verify_nonce($_POST['nonce'], 'nexora_nonce')) {
            wp_die('Sicherheitscheck fehlgeschlagen');
        }
        if (!current_user_can('manage_options')) {
            wp_die('Unzureichende Berechtigungen');
        }

        $company_name = sanitize_text_field($_POST['company_name']);
        $company_address = sanitize_textarea_field($_POST['company_address']);
        $tax_number = sanitize_text_field($_POST['tax_number']);

        update_option('nexora_company_name', $company_name);
        update_option('nexora_company_address', $company_address);
        update_option('nexora_tax_number', $tax_number);

        wp_send_json_success('Rechnungseinstellungen erfolgreich gespeichert');
    }

    
    public function save_system_settings() {
        if (!wp_verify_nonce($_POST['nonce'], 'nexora_nonce')) {
            wp_die('Sicherheitscheck fehlgeschlagen');
        }
        if (!current_user_can('manage_options')) {
            wp_die('Unzureichende Berechtigungen');
        }

        $items_per_page = intval($_POST['items_per_page']);
        $debug_mode = sanitize_text_field($_POST['debug_mode']);
        if (!in_array($items_per_page, array(10, 20, 50, 100))) {
            $items_per_page = 10;
        }

        update_option('nexora_items_per_page', $items_per_page);
        update_option('nexora_debug_mode', $debug_mode);

        wp_send_json_success('Systemeinstellungen erfolgreich gespeichert');
    }

    
    public function ajax_repair_system() {
        if (!wp_verify_nonce($_POST['nonce'], 'nexora_repair_nonce')) {
            wp_die('Sicherheitscheck fehlgeschlagen');
        }
        if (!current_user_can('manage_options')) {
            wp_die('Unzureichende Berechtigungen');
        }

        global $wpdb;
        $log = array();

        try {
            $log[] = array('type' => 'info', 'message' => '🔍 Prüfe Datenbanktabellen...');
            
            $tables_to_check = array(
                'nexora_services' => "CREATE TABLE IF NOT EXISTS `{$wpdb->prefix}nexora_services` (
                    id INT NOT NULL AUTO_INCREMENT,
                    title VARCHAR(255) NOT NULL,
                    description TEXT,
                    cost DECIMAL(10,2) DEFAULT 0.00,
                    status ENUM('active','inactive') NOT NULL DEFAULT 'active',
                    user_id bigint(20) DEFAULT NULL,
                    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
                    updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                    PRIMARY KEY (id),
                    KEY idx_status (status),
                    KEY idx_user_id (user_id)
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci",
                
                'nexora_service_requests' => "CREATE TABLE IF NOT EXISTS `{$wpdb->prefix}nexora_service_requests` (
                    id mediumint(9) NOT NULL AUTO_INCREMENT,
                    serial VARCHAR(100) NOT NULL,
                    model VARCHAR(100) NOT NULL,
                    description TEXT,
                    service_description TEXT,
                    user_id bigint(20) NOT NULL,
                    service_id bigint(20) DEFAULT NULL,
                    status_id bigint(20) NOT NULL,
                    priority VARCHAR(20) DEFAULT 'medium',
                    assigned_to BIGINT(20) DEFAULT NULL,
                    estimated_completion DATE DEFAULT NULL,
                    order_id BIGINT(20) NULL,
                    brand_level_1_id INT DEFAULT NULL,
                    brand_level_2_id INT DEFAULT NULL,
                    brand_level_3_id INT DEFAULT NULL,
                    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
                    updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                    PRIMARY KEY (id),
                    KEY idx_user_id (user_id),
                    KEY idx_service_id (service_id),
                    KEY idx_status_id (status_id),
                    KEY idx_assigned_to (assigned_to)
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci",
                
                'nexora_service_status' => "CREATE TABLE IF NOT EXISTS `{$wpdb->prefix}nexora_service_status` (
                    id INT NOT NULL AUTO_INCREMENT,
                    title VARCHAR(100) NOT NULL,
                    color VARCHAR(7) DEFAULT '#0073aa',
                    is_default TINYINT(1) DEFAULT 0,
                    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
                    updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                    PRIMARY KEY (id),
                    UNIQUE KEY unique_title (title)
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci",
                
                'nexora_brands' => "CREATE TABLE IF NOT EXISTS `{$wpdb->prefix}nexora_brands` (
                    id INT NOT NULL AUTO_INCREMENT,
                    name VARCHAR(100) NOT NULL,
                    parent_id INT DEFAULT NULL,
                    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
                    updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                    PRIMARY KEY (id),
                    UNIQUE KEY unique_name (name)
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci",
                
                'nexora_customer_info' => "CREATE TABLE IF NOT EXISTS `{$wpdb->prefix}nexora_customer_info` (
                    id INT NOT NULL AUTO_INCREMENT,
                    user_id bigint(20) NOT NULL,
                    customer_type ENUM('private','business') NOT NULL,
                    customer_number VARCHAR(50) DEFAULT NULL,
                    company_name VARCHAR(255) DEFAULT NULL,
                    company_name_2 VARCHAR(255) DEFAULT NULL,
                    street VARCHAR(255) NOT NULL,
                    address_addition VARCHAR(255) DEFAULT NULL,
                    postal_code VARCHAR(20) NOT NULL,
                    city VARCHAR(100) NOT NULL,
                    country VARCHAR(100) NOT NULL,
                    industry VARCHAR(100) DEFAULT NULL,
                    vat_id VARCHAR(50) DEFAULT NULL,
                    salutation ENUM('Herr','Frau','Divers') NOT NULL,
                    phone VARCHAR(50) DEFAULT NULL,
                    newsletter TINYINT(1) DEFAULT 0,
                    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
                    updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                    PRIMARY KEY (id),
                    UNIQUE KEY unique_user_id (user_id),
                    KEY idx_customer_type (customer_type)
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci",
                
                'nexora_complete_service_requests' => "CREATE TABLE IF NOT EXISTS `{$wpdb->prefix}nexora_complete_service_requests` (
                    id BIGINT(20) NOT NULL AUTO_INCREMENT,
                    request_id BIGINT(20) NOT NULL,
                    
                    -- Customer Information
                    customer_name VARCHAR(255) NOT NULL,
                    customer_email VARCHAR(255) NOT NULL,
                    customer_phone VARCHAR(50),
                    customer_type ENUM('private', 'business') DEFAULT 'private',
                    customer_number VARCHAR(50),
                    company_name VARCHAR(255),
                    street VARCHAR(255),
                    postal_code VARCHAR(20),
                    city VARCHAR(100),
                    country VARCHAR(100) DEFAULT 'DE',
                    vat_id VARCHAR(50),
                    user_id BIGINT(20),
                    
                    -- Device Information
                    device_id BIGINT(20),
                    device_type VARCHAR(50),
                    device_brand VARCHAR(100),
                    device_model VARCHAR(100),
                    device_serial VARCHAR(100),
                    device_description TEXT,
                    
                    -- Status Information
                    status_id BIGINT(20),
                    priority ENUM('low', 'medium', 'high', 'urgent') DEFAULT 'medium',
                    assigned_to BIGINT(20),
                    estimated_completion DATE,
                    
                    -- Services Data (JSON)
                    services_data JSON,
                    
                    -- Timestamps
                    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
                    updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                    
                    PRIMARY KEY (id),
                    UNIQUE KEY unique_request_id (request_id),
                    KEY idx_user_id (user_id),
                    KEY idx_device_id (device_id),
                    KEY idx_status_id (status_id),
                    KEY idx_assigned_to (assigned_to),
                    KEY idx_created_at (created_at)
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci"
            );

            foreach ($tables_to_check as $table_name => $create_sql) {
                $full_table_name = $wpdb->prefix . $table_name;
                $table_exists = $wpdb->get_var("SHOW TABLES LIKE '{$full_table_name}'") == $full_table_name;
                
                if (!$table_exists) {
                    $result = $wpdb->query($create_sql);
                    if ($result !== false) {
                        $log[] = array('type' => 'success', 'message' => "✅ Tabelle '{$table_name}' erstellt");
                    } else {
                        $log[] = array('type' => 'error', 'message' => "❌ Fehler beim Erstellen der Tabelle '{$table_name}': " . $wpdb->last_error);
                    }
                } else {
                    $log[] = array('type' => 'info', 'message' => "ℹ️ Tabelle '{$table_name}' existiert bereits");
                }
            }
            $log[] = array('type' => 'info', 'message' => '🔍 Prüfe Tabellenspalten...');
            $table_name = $wpdb->prefix . 'nexora_service_requests';
            $column_exists = $wpdb->get_results("SHOW COLUMNS FROM {$table_name} LIKE 'service_description'");
            if (empty($column_exists)) {
                $wpdb->query("ALTER TABLE {$table_name} ADD COLUMN service_description TEXT AFTER description");
                $wpdb->query("UPDATE {$table_name} SET service_description = '' WHERE service_description IS NULL");
                $log[] = array('type' => 'success', 'message' => "✅ Added service_description column to nexora_service_requests table");
            } else {
                $log[] = array('type' => 'info', 'message' => "ℹ️ service_description column already exists in nexora_service_requests table");
            }
            $column_exists = $wpdb->get_results("SHOW COLUMNS FROM {$table_name} LIKE 'assigned_to'");
            if (empty($column_exists)) {
                $wpdb->query("ALTER TABLE {$table_name} ADD COLUMN assigned_to BIGINT(20) DEFAULT NULL AFTER priority");
                $log[] = array('type' => 'success', 'message' => "✅ Added assigned_to column to nexora_service_requests table");
            } else {
                $log[] = array('type' => 'info', 'message' => "ℹ️ assigned_to column already exists in nexora_service_requests table");
            }
            $column_exists = $wpdb->get_results("SHOW COLUMNS FROM {$table_name} LIKE 'estimated_completion'");
            if (empty($column_exists)) {
                $wpdb->query("ALTER TABLE {$table_name} ADD COLUMN estimated_completion DATE DEFAULT NULL AFTER assigned_to");
                $log[] = array('type' => 'success', 'message' => "✅ Added estimated_completion column to nexora_service_requests table");
            } else {
                $log[] = array('type' => 'info', 'message' => "ℹ️ estimated_completion column already exists in nexora_service_requests table");
            }
            $quantity_column_exists = $wpdb->get_results("SHOW COLUMNS FROM {$table_name} LIKE 'service_quantity'");
            if (empty($quantity_column_exists)) {
                $wpdb->query("ALTER TABLE {$table_name} ADD COLUMN service_quantity INT DEFAULT 1 AFTER service_description");
                $wpdb->query("UPDATE {$table_name} SET service_quantity = 1 WHERE service_quantity IS NULL");
                $log[] = array('type' => 'success', 'message' => "✅ Added service_quantity column to nexora_service_requests table");
            } else {
                $log[] = array('type' => 'info', 'message' => "ℹ️ service_quantity column already exists in nexora_service_requests table");
            }
            $log[] = array('type' => 'info', 'message' => '🔍 Prüfe Standarddaten...');
            $log[] = array('type' => 'info', 'message' => '🔍 Prüfe Datenbankverbindung...');
            
            $test_query = $wpdb->get_var("SELECT 1");
            if ($test_query == '1') {
                $log[] = array('type' => 'success', 'message' => "✅ Datenbankverbindung funktioniert");
            } else {
                $log[] = array('type' => 'error', 'message' => "❌ Datenbankverbindungsfehler");
            }
            $log[] = array('type' => 'info', 'message' => '🔍 Prüfe WordPress-Integration...');
            
            if (function_exists('wp_create_nonce')) {
                $log[] = array('type' => 'success', 'message' => "✅ WordPress-Funktionen verfügbar");
            } else {
                $log[] = array('type' => 'error', 'message' => "❌ WordPress-Funktionen nicht verfügbar");
            }

            $log[] = array('type' => 'success', 'message' => '🎉 Repair System erfolgreich abgeschlossen!');

        } catch (Exception $e) {
            $log[] = array('type' => 'error', 'message' => "❌ Unerwarteter Fehler: " . $e->getMessage());
        }

        wp_send_json_success(array('log' => $log));
    }

    
    public function create_complete_service_request_table() {
        if (!wp_verify_nonce($_POST['nonce'], 'nexora_nonce')) {
            wp_die('Sicherheitscheck fehlgeschlagen');
        }
        if (!current_user_can('manage_options')) {
            wp_die('Unzureichende Berechtigungen');
        }

        global $wpdb;
        $table_name = $wpdb->prefix . 'nexora_complete_service_requests';
        
        $sql = "CREATE TABLE IF NOT EXISTS `{$table_name}` (
            id BIGINT(20) NOT NULL AUTO_INCREMENT,
            request_id BIGINT(20) NOT NULL,
            
            -- Customer Information
            customer_name VARCHAR(255) NOT NULL,
            customer_email VARCHAR(255) NOT NULL,
            customer_phone VARCHAR(50),
            customer_type ENUM('private', 'business') DEFAULT 'private',
            company_name VARCHAR(255),
            street VARCHAR(255),
            postal_code VARCHAR(20),
            city VARCHAR(100),
            country VARCHAR(100) DEFAULT 'DE',
            vat_id VARCHAR(50),
            user_id BIGINT(20),
            
            -- Device Information
            device_id BIGINT(20),
            device_type VARCHAR(50),
            device_brand VARCHAR(100),
            device_model VARCHAR(100),
            device_serial VARCHAR(100),
            device_description TEXT,
            
            -- Status Information
            status_id BIGINT(20),
            priority ENUM('low', 'medium', 'high', 'urgent') DEFAULT 'medium',
            assigned_to BIGINT(20),
            estimated_completion DATE,
            
            -- Services Data (JSON)
            services_data JSON,
            
            -- Timestamps
            created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
            updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            
            PRIMARY KEY (id),
            UNIQUE KEY unique_request_id (request_id),
            KEY idx_user_id (user_id),
            KEY idx_device_id (device_id),
            KEY idx_status_id (status_id),
            KEY idx_assigned_to (assigned_to),
            KEY idx_created_at (created_at)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci";

        $result = $wpdb->query($sql);
        
        if ($result !== false) {
            wp_send_json_success(array(
                'message' => 'Complete Service Request Table created successfully!',
                'table_name' => $table_name,
                'sql_executed' => $sql
            ));
        } else {
            wp_send_json_error(array(
                'error' => 'Failed to create table',
                'sql_error' => $wpdb->last_error
            ));
        }
    }

    
    public function save_service_request_data() {
        error_log('=== save_service_request_data METHOD CALLED ===');
        error_log('POST data: ' . json_encode($_POST));
        if (!wp_verify_nonce($_POST['nonce'], 'save_service_request_nonce')) {
            error_log('=== NONCE VERIFICATION FAILED ===');
            wp_die('Sicherheitscheck fehlgeschlagen');
        }
        if (!current_user_can('manage_options')) {
            wp_die('Unzureichende Berechtigungen');
        }

        global $wpdb;
        
        $request_id = intval($_POST['request_id']);
        $customer_data = $_POST['customer_data'];
        $device_data = $_POST['device_data'];
        $status_data = $_POST['status_data'];
        $services = isset($_POST['services']) ? $_POST['services'] : [];
        $normalize_services = function($arr) {
            global $wpdb;
            $out = [];
            if (is_array($arr)) {
                foreach ($arr as $s) {
                    if (!isset($s['service_id'])) continue;
                    $service_title = '';
                    $service_id = intval($s['service_id']);
                    if ($service_id > 0) {
                        $service_title = $wpdb->get_var($wpdb->prepare(
                            "SELECT title FROM {$wpdb->prefix}nexora_services WHERE id = %d",
                            $service_id
                        ));
                    }
                    
                    $out[] = [
                        'service_id' => $service_id,
                        'service_title' => $service_title ?: 'Service',
                        'quantity'   => isset($s['quantity']) ? intval($s['quantity']) : 1,
                        'service_cost' => isset($s['service_cost']) ? floatval($s['service_cost']) : 0,
                        'description' => isset($s['description']) ? sanitize_text_field($s['description']) : ''
                    ];
                }
            }
            return $out;
        };
        error_log('=== SERVICES DEBUG ===');
        error_log('Raw services from form: ' . json_encode($services));
        $table_name = $wpdb->prefix . 'nexora_complete_service_requests';
        $old_services = [];
        $old_row = $wpdb->get_row(
            $wpdb->prepare("SELECT services_data FROM $table_name WHERE request_id = %d", $request_id),
            ARRAY_A
        );
        if ($old_row && !empty($old_row['services_data'])) {
            $decoded = json_decode($old_row['services_data'], true);
            $old_services = $normalize_services($decoded);
        }
        if (empty($old_services)) {
            $service_details_table = $wpdb->prefix . 'nexora_service_details';
            $existing_service_details = $wpdb->get_results(
                $wpdb->prepare(
                    "SELECT service_id, quantity FROM $service_details_table WHERE request_id = %d",
                    $request_id
                ),
                ARRAY_A
            );
            if ($existing_service_details) {
                $old_services = $normalize_services($existing_service_details);
            }
        }
        $new_services = $normalize_services($services);

        error_log('=== SERVICE SNAPSHOT TAKEN BEFORE SAVE ===');
        error_log('Old services: ' . json_encode($old_services));
        error_log('New services: ' . json_encode($new_services));
        error_log('Normalized services structure: ' . json_encode($new_services));
        $data = array(
            'request_id' => $request_id,
            'customer_name' => sanitize_text_field($customer_data['customer_name']),
            'customer_email' => sanitize_email($customer_data['customer_email']),
            'customer_phone' => sanitize_text_field($customer_data['customer_phone']),
            'customer_type' => sanitize_text_field($customer_data['customer_type']),
            'company_name' => sanitize_text_field($customer_data['company_name']),
            'street' => sanitize_text_field($customer_data['street']),
            'postal_code' => sanitize_text_field($customer_data['postal_code']),
            'city' => sanitize_text_field($customer_data['city']),
            'country' => sanitize_text_field($customer_data['country']),
            'vat_id' => sanitize_text_field($customer_data['vat_id']),
            'user_id' => intval($customer_data['user_id']),
            'device_type' => sanitize_text_field($device_data['device_type_id']),
            'device_brand' => sanitize_text_field($device_data['device_brand_id']),
            'device_model' => sanitize_text_field($device_data['device_model_id']),
            'device_serial' => sanitize_text_field($device_data['device_serial']),
            'device_description' => sanitize_textarea_field($device_data['device_description']),
            'status_id' => intval($status_data['status']),
            'priority' => sanitize_text_field($status_data['priority']),
            'assigned_to' => intval($status_data['assigned_to']),
            'estimated_completion' => sanitize_text_field($status_data['estimated_completion']),
        'services_data' => json_encode($new_services),
            
            'updated_at' => current_time('mysql')
        );
        
        $table_name = $wpdb->prefix . 'nexora_complete_service_requests';
        $existing = $wpdb->get_var($wpdb->prepare("SELECT id FROM $table_name WHERE request_id = %d", $request_id));
        
        if ($existing) {
            $result = $wpdb->update($table_name, $data, array('request_id' => $request_id));
            $message = 'Service request data updated successfully!';
        } else {
            $data['created_at'] = current_time('mysql');
            $result = $wpdb->insert($table_name, $data);
            $message = 'Service request data saved successfully!';
        }
        
        if ($result !== false) {
            $main_table = $wpdb->prefix . 'nexora_service_requests';
            $custom_info = [];
            if (!empty($device_data['device_type_custom'])) $custom_info[] = 'Gerätetyp: ' . $device_data['device_type_custom'];
            if (!empty($device_data['device_brand_custom'])) $custom_info[] = 'Marke: ' . $device_data['device_brand_custom'];
            if (!empty($device_data['device_series_custom'])) $custom_info[] = 'Serie: ' . $device_data['device_series_custom'];
            if (!empty($device_data['device_model_custom'])) $custom_info[] = 'Modell: ' . $device_data['device_model_custom'];
            
            $description = $device_data['device_description'];
            if (!empty($custom_info)) {
                $description = implode(' | ', $custom_info) . "\n" . $description;
            }
            $model = $device_data['device_model_custom'] ?? '';
            if (!empty($device_data['device_model_id'])) {
                $model_name = $wpdb->get_var($wpdb->prepare(
                    "SELECT name FROM {$wpdb->prefix}nexora_devices WHERE id = %d",
                    $device_data['device_model_id']
                ));
                if ($model_name) {
                    $model = $model_name;
                }
            }
            $brand_level_1_id = 0;
            $brand_level_2_id = 0;
            $brand_level_3_id = 0;
            if (!empty($device_data['device_type_id'])) {
                $brand_level_1_id = intval($device_data['device_type_id']);
            }
            if (!empty($device_data['device_brand_id'])) {
                $brand_level_2_id = intval($device_data['device_brand_id']);
            }
            if (!empty($device_data['device_series_id'])) {
                $brand_level_3_id = intval($device_data['device_series_id']);
            }
            $old_main_data = $wpdb->get_row($wpdb->prepare("SELECT * FROM $main_table WHERE id = %d", $request_id), ARRAY_A);
            $new_status_id = isset($status_data['status']) && $status_data['status'] !== ''
                ? intval($status_data['status'])
                : (isset($old_main_data['status_id']) ? intval($old_main_data['status_id']) : null);

            $main_data = array(
                'serial' => $device_data['device_serial'],
                'model' => $model,
                'description' => $description,
                'brand_level_1_id' => $brand_level_1_id,
                'brand_level_2_id' => $brand_level_2_id,
                'brand_level_3_id' => $brand_level_3_id,
                'status_id' => $new_status_id,
                'priority' => !empty($status_data['priority']) ? sanitize_text_field($status_data['priority']) : (isset($old_main_data['priority']) ? $old_main_data['priority'] : ''),
                'assigned_to' => !empty($status_data['assigned_to']) ? intval($status_data['assigned_to']) : (isset($old_main_data['assigned_to']) ? $old_main_data['assigned_to'] : null),
                'estimated_completion' => !empty($status_data['estimated_completion']) ? sanitize_text_field($status_data['estimated_completion']) : (isset($old_main_data['estimated_completion']) ? $old_main_data['estimated_completion'] : null),
                'updated_at' => current_time('mysql')
            );
            
            $main_update_result = $wpdb->update($main_table, $main_data, array('id' => $request_id));
            if ($old_main_data && isset($old_main_data['status_id']) && isset($main_data['status_id']) && $old_main_data['status_id'] != $main_data['status_id']) {
                error_log('Status change detected in save_service_request_data - old: ' . $old_main_data['status_id'] . ', new: ' . $main_data['status_id']);
                do_action('nexora_service_status_changed', $request_id, $old_main_data['status_id'], $main_data['status_id']);
                error_log('Status change hook triggered: nexora_service_status_changed(' . $request_id . ', ' . $old_main_data['status_id'] . ', ' . $main_data['status_id'] . ')');
            } else {
                error_log('No status change detected in save_service_request_data - status remains: ' . ($old_main_data['status_id'] ?? 'unknown'));
            }
            
            if ($main_update_result === false) {
                error_log('Failed to update main table: ' . $wpdb->last_error);
            } else {
                error_log('Main table updated successfully for request ID: ' . $request_id);
                error_log('Updated status_id to: ' . intval($status_data['status']));
            }
            $existing_services = array();
            if (!empty($services)) {
                $existing_data = $wpdb->get_row($wpdb->prepare("SELECT services_data FROM $table_name WHERE request_id = %d", $request_id), ARRAY_A);
                if ($existing_data && $existing_data['services_data']) {
                    $existing_services = json_decode($existing_data['services_data'], true) ?: array();
                }
                if (empty($existing_services)) {
                    $service_details_table = $wpdb->prefix . 'nexora_service_details';
                    $existing_service_details = $wpdb->get_results($wpdb->prepare(
                        "SELECT service_id, quantity, description FROM $service_details_table WHERE request_id = %d",
                        $request_id
                    ), ARRAY_A);
                    
                    if ($existing_service_details) {
                        foreach ($existing_service_details as $detail) {
                            $existing_services[] = array(
                                'service_id' => $detail['service_id'],
                                'quantity' => $detail['quantity'],
                                'description' => $detail['description']
                            );
                        }
                    }
                }
                
                error_log('Existing services found: ' . json_encode($existing_services));
            }
            if (class_exists('Nexora_Activity_Logger')) {
                $logger = new Nexora_Activity_Logger();
                if ($old_main_data) {
                    $new_main_data = array_merge($old_main_data, $main_data);
                    $logger->log_request_updated($request_id, $old_main_data, $new_main_data);
                }
                if (!empty($services)) {
                    $logger->log_services_updated($request_id, $existing_services, $services);
                }
            }
                     error_log('=== ABOUT TO CALL trigger_service_change_notifications (ALWAYS) ===');
                     $this->trigger_service_change_notifications($request_id, $old_services, $new_services);
            
            wp_send_json_success(array(
                'message' => $message,
                'request_id' => $request_id,
                'data_saved' => $data
            ));
        } else {
            wp_send_json_error(array(
                'error' => 'Failed to save data',
                'sql_error' => $wpdb->last_error
            ));
        }
    }

    
    public function ajax_delete_device_image() {
        if (!wp_verify_nonce($_POST['nonce'], 'delete_device_image_nonce')) {
            wp_die('Sicherheitscheck fehlgeschlagen');
        }
        if (!current_user_can('manage_options')) {
            wp_die('Unzureichende Berechtigungen');
        }

        global $wpdb;
        
        $image_id = intval($_POST['image_id']);
        $attachments_table = $wpdb->prefix . 'nexora_request_attachments';
        $image = $wpdb->get_row($wpdb->prepare("SELECT * FROM $attachments_table WHERE id = %d", $image_id));
        
        if (!$image) {
            wp_send_json_error('Bild nicht gefunden');
        }
        if (file_exists($image->file_path)) {
            unlink($image->file_path);
        }
        $result = $wpdb->delete($attachments_table, array('id' => $image_id), array('%d'));
        
        if ($result !== false) {
            wp_send_json_success(array(
                'message' => 'Bild erfolgreich gelöscht',
                'image_id' => $image_id
            ));
        } else {
            wp_send_json_error('Fehler beim Löschen aus der Datenbank');
        }
    }

    
    public static function set_default_settings() {
        if (!get_option('nexora_primary_color')) {
            update_option('nexora_primary_color', '#6c63ff');
        }
        if (!get_option('nexora_font_family')) {
            update_option('nexora_font_family', 'inherit');
        }
        if (!get_option('nexora_email_notifications')) {
            update_option('nexora_email_notifications', '1');
        }
        if (!get_option('nexora_admin_email')) {
            update_option('nexora_admin_email', get_option('admin_email'));
        }
        if (!get_option('nexora_dashboard_notifications')) {
            update_option('nexora_dashboard_notifications', '1');
        }
        if (!get_option('nexora_items_per_page')) {
            update_option('nexora_items_per_page', '10');
        }
        if (!get_option('nexora_debug_mode')) {
            update_option('nexora_debug_mode', '0');
        }
    }

    
    public function save_customer_notification_settings() {
        if (!wp_verify_nonce($_POST['nonce'], 'nexora_nonce')) {
            wp_die('Security check failed');
        }
        if (!current_user_can('manage_options') && !current_user_can('reparaturdienst_settings')) {
            wp_die('Insufficient permissions');
        }
        if (!class_exists('Nexora_Notification_Settings')) {
            require_once NEXORA_PLUGIN_DIR . 'includes/email/class-notification-settings.php';
        }

        $notification_settings = new Nexora_Notification_Settings();
        $allowed_events = $notification_settings->get_available_events();
        $customer_notifications = array();
        foreach ($allowed_events as $event_key => $event_data) {
            $field_name = 'customer_notifications_' . $event_key;
            if (isset($_POST[$field_name])) {
                $customer_notifications[$event_key] = array(
                    'enabled' => true,
                    'channels' => array('email'),
                    'roles' => array('customer')
                );
            } else {
                $customer_notifications[$event_key] = array(
                    'enabled' => false,
                    'channels' => array(),
                    'roles' => array()
                );
            }
        }
        $result = $notification_settings->update_settings('customer', $customer_notifications);
        
        if ($result) {
            wp_send_json_success(array(
                'message' => 'تنظیمات اعلان‌های مشتری با موفقیت ذخیره شد',
                'data' => $customer_notifications
            ));
        } else {
            wp_send_json_error(array(
                'message' => 'خطا در ذخیره تنظیمات اعلان‌های مشتری'
            ));
        }
    }

    
    public function save_admin_notification_settings() {
        if (!wp_verify_nonce($_POST['nonce'], 'nexora_nonce')) {
            wp_die('Security check failed');
        }
        if (!current_user_can('manage_options') && !current_user_can('reparaturdienst_settings')) {
            wp_die('Insufficient permissions');
        }
        if (!class_exists('Nexora_Notification_Settings')) {
            require_once NEXORA_PLUGIN_DIR . 'includes/email/class-notification-settings.php';
        }

        $notification_settings = new Nexora_Notification_Settings();
        $allowed_events = $notification_settings->get_available_events();
        $admin_notifications = array();
        foreach ($allowed_events as $event_key => $event_data) {
            $field_name = 'admin_notifications_' . $event_key;
            if (isset($_POST[$field_name])) {
                $admin_notifications[$event_key] = array(
                    'enabled' => true,
                    'channels' => array('email'),
                    'roles' => array('administrator', 'reparaturdienst_admin')
                );
            } else {
                $admin_notifications[$event_key] = array(
                    'enabled' => false,
                    'channels' => array(),
                    'roles' => array()
                );
            }
        }
        $result = $notification_settings->update_settings('admin', $admin_notifications);
        
        if ($result) {
            wp_send_json_success(array(
                'message' => 'تنظیمات اعلان‌های ادمین با موفقیت ذخیره شد',
                'data' => $admin_notifications
            ));
        } else {
            wp_send_json_error(array(
                'message' => 'خطا در ذخیره تنظیمات اعلان‌های ادمین'
            ));
        }
    }

    
    public function save_general_email_settings() {
        if (!wp_verify_nonce($_POST['nonce'], 'nexora_nonce')) {
            wp_die('Security check failed');
        }
        if (!current_user_can('manage_options') && !current_user_can('reparaturdienst_settings')) {
            wp_die('Insufficient permissions');
        }
        $sender_name = sanitize_text_field($_POST['sender_name']);
        $sender_email = sanitize_email($_POST['sender_email']);
        $reply_to_email = sanitize_email($_POST['reply_to_email']);
        if (!is_email($sender_email)) {
            wp_send_json_error(array(
                'message' => 'آدرس ایمیل فرستنده نامعتبر است'
            ));
        }

        if (!empty($reply_to_email) && !is_email($reply_to_email)) {
            wp_send_json_error(array(
                'message' => 'آدرس ایمیل پاسخ نامعتبر است'
            ));
        }
        update_option('nexora_email_sender_name', $sender_name);
        update_option('nexora_email_sender_email', $sender_email);
        update_option('nexora_email_reply_to', $reply_to_email);

        wp_send_json_success(array(
            'message' => 'تنظیمات عمومی ایمیل با موفقیت ذخیره شد',
            'data' => array(
                'sender_name' => $sender_name,
                'sender_email' => $sender_email,
                'reply_to_email' => $reply_to_email
            )
        ));
    }
    
    
    private function trigger_service_change_notifications($request_id, $old_services, $new_services) {
        error_log('trigger_service_change_notifications called for request ID: ' . $request_id);
        error_log('Old services: ' . json_encode($old_services));
        error_log('New services: ' . json_encode($new_services));
        
        if (empty($old_services) && !empty($new_services)) {
            error_log('All services are new - triggering nexora_service_added hooks');
            foreach ($new_services as $service) {
                error_log('Triggering nexora_service_added for service ID: ' . $service['service_id'] . ' with quantity: ' . ($service['quantity'] ?? 1));
                error_log('About to call do_action with: request_id=' . $request_id . ', service_id=' . intval($service['service_id']) . ', quantity=' . intval($service['quantity'] ?? 1));
                error_log('=== EXECUTING SERVICE ADDED HOOK ===');
                error_log('Request ID: ' . $request_id);
                error_log('Service ID: ' . intval($service['service_id']));
                error_log('Quantity: ' . intval($service['quantity'] ?? 1));
                error_log('About to call do_action...');
                
                do_action('nexora_service_added', $request_id, intval($service['service_id']), intval($service['quantity'] ?? 1));
                
                error_log('✅ do_action nexora_service_added completed');
                error_log('=== SERVICE ADDED HOOK EXECUTION COMPLETED ===');
            }
        } elseif (!empty($old_services) && empty($new_services)) {
            error_log('All services were removed - triggering nexora_service_removed hooks');
            foreach ($old_services as $service) {
                error_log('Triggering nexora_service_removed for service ID: ' . $service['service_id'] . ' with quantity: ' . ($service['quantity'] ?? 1));
                do_action('nexora_service_removed', $request_id, intval($service['service_id']), intval($service['quantity'] ?? 1));
            }
        } elseif (!empty($old_services) && !empty($new_services)) {
            $old_services_map = [];
            foreach ($old_services as $service) {
                $old_services_map[$service['service_id']] = $service;
            }
            
            $new_services_map = [];
            foreach ($new_services as $service) {
                $new_services_map[$service['service_id']] = $service;
            }
            foreach ($old_services as $service) {
                if (!isset($new_services_map[$service['service_id']])) {
                    error_log('Service removed - triggering nexora_service_removed for service ID: ' . $service['service_id']);
                    do_action('nexora_service_removed', $request_id, intval($service['service_id']), intval($service['quantity'] ?? 1));
                }
            }
            foreach ($new_services as $service) {
                if (!isset($old_services_map[$service['service_id']])) {
                    error_log('New service added - triggering nexora_service_added for service ID: ' . $service['service_id']);
                    do_action('nexora_service_added', $request_id, intval($service['service_id']), intval($service['quantity'] ?? 1));
                } else {
                    $old_service = $old_services_map[$service['service_id']];
                    $old_quantity = intval($old_service['quantity'] ?? 1);
                    $new_quantity = intval($service['quantity'] ?? 1);
                    
                    if ($old_quantity != $new_quantity) {
                        error_log('Service quantity changed - triggering nexora_service_quantity_changed for service ID: ' . $service['service_id'] . ' from ' . $old_quantity . ' to ' . $new_quantity);
                        do_action('nexora_service_quantity_changed', $request_id, intval($service['service_id']), $old_quantity, $new_quantity);
                    }
                }
            }
        }
    }
} 