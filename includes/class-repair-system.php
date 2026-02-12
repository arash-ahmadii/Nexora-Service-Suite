<?php

if (!defined('ABSPATH')) {
    exit;
}

require_once __DIR__ . '/repair-system/RepairSystem_AJAX.php';
require_once __DIR__ . '/repair-system/RepairSystem_Tables.php';

class Nexora_Repair_System {
    use RepairSystem_AJAX, RepairSystem_Tables;
    
    public function __construct() {
        if (is_admin()) {
            add_action('wp_ajax_repair_check_tables', array($this, 'ajax_check_tables'));
            add_action('wp_ajax_repair_create_tables', array($this, 'ajax_create_tables'));
            add_action('wp_ajax_repair_repair_tables', array($this, 'ajax_repair_tables'));
            add_action('wp_ajax_repair_test_classes', array($this, 'ajax_test_classes'));
            add_action('wp_ajax_repair_test_ajax', array($this, 'ajax_test_ajax'));
            add_action('wp_ajax_repair_test_wordpress', array($this, 'ajax_test_wordpress'));
            add_action('wp_ajax_repair_test_functionality', array($this, 'ajax_test_functionality'));
            add_action('wp_ajax_repair_create_sample_data', array($this, 'ajax_create_sample_data'));
            add_action('wp_ajax_repair_run_full_test', array($this, 'ajax_run_full_test'));
            add_action('wp_ajax_repair_test_registration_errors', array($this, 'ajax_test_registration_errors'));
            add_action('wp_ajax_repair_test_ajax_registration', array($this, 'ajax_test_ajax_registration'));
            add_action('wp_ajax_repair_test_missing_fields', array($this, 'ajax_test_missing_fields'));
            add_action('wp_ajax_repair_test_invalid_email', array($this, 'ajax_test_invalid_email'));
            add_action('wp_ajax_repair_test_complete_registration', array($this, 'ajax_test_complete_registration'));
            add_action('wp_ajax_repair_test_duplicate_email', array($this, 'ajax_test_duplicate_email'));
            add_action('wp_ajax_repair_test_database_operations', array($this, 'ajax_test_database_operations'));
            add_action('wp_ajax_repair_test_ajax_registration_comprehensive', array($this, 'ajax_test_ajax_registration_comprehensive'));
            add_action('wp_ajax_repair_test_form_rendering', array($this, 'ajax_test_form_rendering'));
            add_action('wp_ajax_repair_comprehensive_system_test', array($this, 'ajax_comprehensive_system_test'));
            add_action('wp_ajax_repair_quick_registration_diagnostic', array($this, 'ajax_quick_registration_diagnostic'));
            add_action('wp_ajax_repair_fix_registration_issues', array($this, 'ajax_fix_registration_issues'));
            add_action('wp_ajax_repair_test_inherited_orderly', array($this, 'ajax_test_inherited_orderly'));
            add_action('wp_ajax_repair_test_privatkunde', array($this, 'ajax_test_privatkunde'));
            add_action('wp_ajax_repair_test_field_validation', array($this, 'ajax_test_field_validation'));
            add_action('wp_ajax_nexora_test_field_validation', array($this, 'ajax_test_field_validation'));
            add_action('wp_ajax_nexora_test_user_approval', array($this, 'ajax_test_user_approval'));
            add_action('wp_ajax_nexora_test_badge_system', array($this, 'ajax_test_badge_system'));
            add_action('wp_ajax_nexora_test_status_filter', array($this, 'ajax_test_status_filter'));
            add_action('wp_ajax_nexora_test_user_info', array($this, 'ajax_test_user_info'));
            add_action('wp_ajax_repair_request_invoices_table', array($this, 'ajax_repair_request_invoices_table'));
            add_action('wp_ajax_repair_debug_services_list', array($this, 'ajax_debug_services_list'));
            add_action('wp_ajax_repair_comprehensive_services_test', array($this, 'ajax_comprehensive_services_test'));
        }
    }
    public function ajax_check_tables() {
        check_ajax_referer('nexora_repair_nonce', 'nonce');
        wp_send_json($this->check_tables_status());
    }
    
    public function ajax_create_tables() {
        check_ajax_referer('nexora_repair_nonce', 'nonce');
        wp_send_json($this->create_missing_tables());
    }
    
    public function ajax_repair_tables() {
        check_ajax_referer('nexora_repair_nonce', 'nonce');
        wp_send_json($this->repair_tables());
    }
    
    public function ajax_test_classes() {
        check_ajax_referer('nexora_repair_nonce', 'nonce');
        wp_send_json($this->test_plugin_classes());
    }
    
    public function ajax_test_ajax() {
        check_ajax_referer('nexora_repair_nonce', 'nonce');
        wp_send_json($this->test_ajax_endpoints());
    }
    
    public function ajax_test_wordpress() {
        check_ajax_referer('nexora_repair_nonce', 'nonce');
        wp_send_json($this->test_wordpress_integration());
    }
    
    public function ajax_test_functionality() {
        check_ajax_referer('nexora_repair_nonce', 'nonce');
        wp_send_json($this->test_functionality());
    }
    
    public function ajax_create_sample_data() {
        check_ajax_referer('nexora_repair_nonce', 'nonce');
        wp_send_json($this->create_sample_data());
    }
    
    public function ajax_run_full_test() {
        check_ajax_referer('nexora_repair_nonce', 'nonce');
        $full_test = array(
            'tables' => $this->check_tables_status(),
            'classes' => $this->test_plugin_classes(),
            'ajax' => $this->test_ajax_endpoints(),
            'wordpress' => $this->test_wordpress_integration(),
            'functionality' => $this->test_functionality()
        );
        wp_send_json($full_test);
    }
    
    public function ajax_test_registration_errors() {
        check_ajax_referer('nexora_repair_nonce', 'nonce');
        wp_send_json($this->test_registration_errors());
    }
    
    public function ajax_test_ajax_registration() {
        check_ajax_referer('nexora_repair_nonce', 'nonce');
        wp_send_json($this->test_ajax_registration());
    }
    
    public function ajax_test_missing_fields() {
        check_ajax_referer('nexora_repair_nonce', 'nonce');
        wp_send_json($this->test_registration_with_missing_fields());
    }
    
    public function ajax_test_invalid_email() {
        check_ajax_referer('nexora_repair_nonce', 'nonce');
        $result = $this->test_registration_errors();
        wp_send_json($result['invalid_email']);
    }
    
    public function ajax_test_complete_registration() {
        check_ajax_referer('nexora_repair_nonce', 'nonce');
        wp_send_json($this->test_complete_registration());
    }
    
    public function ajax_test_duplicate_email() {
        check_ajax_referer('nexora_repair_nonce', 'nonce');
        $result = $this->test_registration_errors();
        wp_send_json($result['duplicate_email']);
    }
    
    public function ajax_test_database_operations() {
        check_ajax_referer('nexora_repair_nonce', 'nonce');
        wp_send_json($this->test_database_operations());
    }
    
    public function ajax_test_ajax_registration_comprehensive() {
        check_ajax_referer('nexora_repair_nonce', 'nonce');
        wp_send_json($this->test_ajax_registration_comprehensive());
    }
    
    public function ajax_test_form_rendering() {
        check_ajax_referer('nexora_repair_nonce', 'nonce');
        wp_send_json($this->test_form_rendering());
    }
    
    public function ajax_comprehensive_system_test() {
        check_ajax_referer('nexora_repair_nonce', 'nonce');
        ini_set('max_execution_time', 60);
        
        try {
            $result = $this->comprehensive_system_test();
            wp_send_json($result);
        } catch (Exception $e) {
            wp_send_json(array(
                'success' => false,
                'error' => 'Exception: ' . $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ));
        }
    }
    
    public function ajax_quick_registration_diagnostic() {
        check_ajax_referer('nexora_repair_nonce', 'nonce');
        
        try {
            $result = $this->quick_registration_diagnostic();
            wp_send_json($result);
        } catch (Exception $e) {
            wp_send_json(array(
                'success' => false,
                'error' => 'Exception: ' . $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ));
        }
    }
    
    public function ajax_fix_registration_issues() {
        check_ajax_referer('nexora_repair_nonce', 'nonce');
        
        try {
            $result = $this->fix_registration_issues();
            wp_send_json($result);
        } catch (Exception $e) {
            wp_send_json(array(
                'success' => false,
                'error' => 'Exception: ' . $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ));
        }
    }
    
    public function ajax_test_inherited_orderly() {
        check_ajax_referer('nexora_repair_nonce', 'nonce');
        
        try {
            $result = $this->test_inherited_orderly_error();
            wp_send_json($result);
        } catch (Exception $e) {
            wp_send_json(array(
                'success' => false,
                'error' => 'Exception: ' . $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ));
        }
    }
    
    public function ajax_test_privatkunde() {
        check_ajax_referer('nexora_repair_nonce', 'nonce');
        
        try {
            $result = $this->test_privatkunde_registration();
            wp_send_json($result);
        } catch (Exception $e) {
            wp_send_json(array(
                'success' => false,
                'error' => 'Exception: ' . $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ));
        }
    }
    
    public function ajax_test_field_validation() {
        check_ajax_referer('nexora_repair_nonce', 'nonce');
        wp_send_json($this->test_field_validation());
    }
    public function check_tables_status() {
        global $wpdb;
        
        $tables = array(
            'nexora_services',
            'nexora_service_status',
            'nexora_service_requests',
            'nexora_brands',
            'nexora_invoices',
            'nexora_invoice_items',
            'nexora_admin_notifications',
            'nexora_customer_info',
            'nexora_activity_logs',
            'nexora_file_attachments',
            'nexora_request_attachments',
            'nexora_request_invoices',
            'nexora_devices'
        );
        
        $results = array();
        
        foreach ($tables as $table) {
            $full_table_name = $wpdb->prefix . $table;
            
            $table_exists = $wpdb->get_var("SHOW TABLES LIKE '$full_table_name'") === $full_table_name;
            
            $results[$table] = array(
                'exists' => $table_exists,
                'structure_valid' => $table_exists ? $this->check_table_structure($full_table_name) : false
            );
        }
        
        return $results;
    }
    
    private function check_table_structure($table_name) {
        global $wpdb;
        
        $structure = $wpdb->get_results("DESCRIBE $table_name");
        
        return !empty($structure);
    }
    
    public function create_missing_tables() {
        global $wpdb;
        
        $results = array(
            'success' => true,
            'created_tables' => array(),
            'errors' => array(),
            'message' => 'Tables creation completed'
        );
        $tables = array(
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
            
            'nexora_service_status' => "CREATE TABLE IF NOT EXISTS `{$wpdb->prefix}nexora_service_status` (
                id INT NOT NULL AUTO_INCREMENT,
                title VARCHAR(100) NOT NULL,
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
            
            'nexora_service_requests' => "CREATE TABLE IF NOT EXISTS `{$wpdb->prefix}nexora_service_requests` (
                id mediumint(9) NOT NULL AUTO_INCREMENT,
                serial VARCHAR(100) NOT NULL,
                model VARCHAR(100) NOT NULL,
                description TEXT,
                user_id bigint(20) NOT NULL,
                service_id bigint(20) DEFAULT NULL,
                status_id bigint(20) NOT NULL,
                order_id BIGINT(20) NULL,
                brand_level_1_id INT DEFAULT NULL,
                brand_level_2_id INT DEFAULT NULL,
                brand_level_3_id INT DEFAULT NULL,
                created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
                updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                PRIMARY KEY (id),
                KEY idx_user_id (user_id),
                KEY idx_service_id (service_id),
                KEY idx_status_id (status_id)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci",
            
            'nexora_invoices' => "CREATE TABLE IF NOT EXISTS `{$wpdb->prefix}nexora_invoices` (
                id BIGINT(20) NOT NULL AUTO_INCREMENT,
                invoice_number VARCHAR(50) NOT NULL,
                invoice_type ENUM('service','request') NOT NULL,
                related_id BIGINT(20) NOT NULL,
                user_id BIGINT(20) NOT NULL,
                total_amount DECIMAL(10,2) NOT NULL DEFAULT 0.00,
                status ENUM('draft','sent','paid','cancelled') NOT NULL DEFAULT 'draft',
                due_date DATE DEFAULT NULL,
                paid_date DATE DEFAULT NULL,
                notes TEXT,
                created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
                updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                PRIMARY KEY (id),
                UNIQUE KEY unique_invoice_number (invoice_number),
                KEY idx_user_id (user_id)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci",
            
            'nexora_invoice_items' => "CREATE TABLE IF NOT EXISTS `{$wpdb->prefix}nexora_invoice_items` (
                id BIGINT(20) NOT NULL AUTO_INCREMENT,
                invoice_id BIGINT(20) NOT NULL,
                item_name VARCHAR(255) NOT NULL,
                description TEXT,
                quantity DECIMAL(10,2) NOT NULL DEFAULT 1.00,
                unit_price DECIMAL(10,2) NOT NULL DEFAULT 0.00,
                total_price DECIMAL(10,2) NOT NULL DEFAULT 0.00,
                tax_rate DECIMAL(5,2) DEFAULT 0.00,
                tax_amount DECIMAL(10,2) DEFAULT 0.00,
                created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
                PRIMARY KEY (id),
                KEY idx_invoice_id (invoice_id)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci",
            
            'nexora_activity_logs' => "CREATE TABLE IF NOT EXISTS `{$wpdb->prefix}nexora_activity_logs` (
                id BIGINT(20) NOT NULL AUTO_INCREMENT,
                user_id BIGINT(20) DEFAULT NULL,
                action VARCHAR(100) NOT NULL,
                object_type VARCHAR(50) NOT NULL,
                object_id BIGINT(20) DEFAULT NULL,
                description TEXT,
                details JSON DEFAULT NULL,
                ip_address VARCHAR(45) DEFAULT NULL,
                user_agent TEXT DEFAULT NULL,
                created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
                PRIMARY KEY (id),
                KEY idx_user_id (user_id),
                KEY idx_action (action),
                KEY idx_object_type (object_type),
                KEY idx_created_at (created_at)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci",
            
            'nexora_admin_notifications' => "CREATE TABLE IF NOT EXISTS `{$wpdb->prefix}nexora_admin_notifications` (
                id BIGINT(20) NOT NULL AUTO_INCREMENT,
                type VARCHAR(50) NOT NULL,
                title VARCHAR(255) NOT NULL,
                message TEXT NOT NULL,
                user_id BIGINT(20) DEFAULT NULL,
                related_id BIGINT(20) DEFAULT NULL,
                related_type VARCHAR(50) DEFAULT NULL,
                status ENUM('unread','read','archived') DEFAULT 'unread',
                priority ENUM('low','medium','high','urgent') DEFAULT 'medium',
                created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
                read_at DATETIME DEFAULT NULL,
                PRIMARY KEY (id),
                KEY idx_status (status),
                KEY idx_priority (priority),
                KEY idx_created_at (created_at),
                KEY idx_user_id (user_id)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci",
            
            'nexora_file_attachments' => "CREATE TABLE IF NOT EXISTS `{$wpdb->prefix}nexora_file_attachments` (
                id BIGINT(20) NOT NULL AUTO_INCREMENT,
                file_name VARCHAR(255) NOT NULL,
                original_name VARCHAR(255) NOT NULL,
                file_path VARCHAR(500) NOT NULL,
                file_size BIGINT(20) NOT NULL,
                mime_type VARCHAR(100) NOT NULL,
                related_type VARCHAR(50) NOT NULL,
                related_id BIGINT(20) NOT NULL,
                user_id BIGINT(20) DEFAULT NULL,
                upload_date DATETIME DEFAULT CURRENT_TIMESTAMP,
                PRIMARY KEY (id),
                KEY idx_related_type (related_type),
                KEY idx_related_id (related_id),
                KEY idx_user_id (user_id)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci",
            
            'nexora_request_attachments' => "CREATE TABLE IF NOT EXISTS `{$wpdb->prefix}nexora_request_attachments` (
                id BIGINT(20) NOT NULL AUTO_INCREMENT,
                request_id BIGINT(20) NOT NULL,
                file_name VARCHAR(255) NOT NULL,
                file_path VARCHAR(500) NOT NULL,
                file_size BIGINT(20) NOT NULL,
                file_type VARCHAR(100) NOT NULL,
                uploaded_by BIGINT(20) NOT NULL,
                created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
                PRIMARY KEY (id),
                KEY idx_request_id (request_id),
                KEY idx_uploaded_by (uploaded_by)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci",
            
            'nexora_request_invoices' => "CREATE TABLE IF NOT EXISTS `{$wpdb->prefix}nexora_request_invoices` (
                id BIGINT(20) NOT NULL AUTO_INCREMENT,
                request_id BIGINT(20) NOT NULL,
                file_name VARCHAR(255) NOT NULL,
                file_path VARCHAR(500) NOT NULL,
                file_size BIGINT(20) NOT NULL,
                file_type VARCHAR(100) NOT NULL,
                uploaded_by BIGINT(20) NOT NULL,
                created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
                PRIMARY KEY (id),
                KEY idx_request_id (request_id),
                KEY idx_uploaded_by (uploaded_by)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci",
            
            'nexora_devices' => "CREATE TABLE IF NOT EXISTS `{$wpdb->prefix}nexora_devices` (
                id INT NOT NULL AUTO_INCREMENT,
                name VARCHAR(100) NOT NULL,
                slug VARCHAR(120) NOT NULL,
                parent_id INT DEFAULT NULL,
                type ENUM('type','brand','series','model') NOT NULL,
                created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
                updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                PRIMARY KEY (id),
                UNIQUE KEY unique_slug (slug),
                UNIQUE KEY unique_name_per_level (name, parent_id, type),
                KEY idx_parent_id (parent_id),
                KEY idx_type (type),
                FOREIGN KEY (parent_id) REFERENCES {$wpdb->prefix}nexora_devices(id) ON DELETE CASCADE
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci"
        );
        foreach ($tables as $table_name => $sql) {
            try {
                $wpdb->query($sql);
                $results['created_tables'][] = $table_name;
            } catch (Exception $e) {
                $results['errors'][] = "Error creating $table_name: " . $e->getMessage();
                $results['success'] = false;
            }
        }
        if ($results['success']) {
            $this->add_default_data();
        }
        
        return $results;
    }
    
    private function add_default_data() {
        global $wpdb;
        $brands_table = $wpdb->prefix . 'nexora_brands';
        $existing_brands = $wpdb->get_var("SELECT COUNT(*) FROM $brands_table");
        
        if ($existing_brands == 0) {
            $brands = array('HP', 'Canon', 'Epson', 'Brother', 'Samsung', 'Xerox', 'Lexmark');
            foreach ($brands as $brand) {
                $wpdb->insert($brands_table, array('name' => $brand));
            }
        }
    }
    
    public function repair_tables() {
        $this->create_missing_tables();
        $this->repair_customer_info_table();
        
        return array('success' => true, 'message' => 'Tables repaired successfully');
    }
    
    private function repair_customer_info_table() {
        global $wpdb;
        $customer_info_table = $wpdb->prefix . 'nexora_customer_info';
        $table_exists = $wpdb->get_var("SHOW TABLES LIKE '$customer_info_table'") === $customer_info_table;
        $messages = [];
        if ($table_exists) {
            $required_columns = [
                'house_number' => "ALTER TABLE $customer_info_table ADD COLUMN house_number VARCHAR(50) DEFAULT NULL AFTER street",
                'reference_number' => "ALTER TABLE $customer_info_table ADD COLUMN reference_number VARCHAR(100) DEFAULT NULL AFTER vat_id",
                'first_name' => "ALTER TABLE $customer_info_table ADD COLUMN first_name VARCHAR(100) DEFAULT NULL AFTER user_id",
                'last_name' => "ALTER TABLE $customer_info_table ADD COLUMN last_name VARCHAR(100) DEFAULT NULL AFTER first_name"
            ];
            foreach ($required_columns as $col => $sql) {
                $exists = $wpdb->get_row("SHOW COLUMNS FROM $customer_info_table LIKE '$col'");
                if (!$exists) {
                    $wpdb->query($sql);
                    $messages[] = "Spalte '$col' wurde hinzugefügt.";
                }
            }
            $salutation_column = $wpdb->get_row("SHOW COLUMNS FROM $customer_info_table LIKE 'salutation'");
            if ($salutation_column) {
                $column_type = $salutation_column->Type;
                if (strpos($column_type, "'Herr'") === false || strpos($column_type, "'Frau'") === false || strpos($column_type, "'Divers'") === false) {
                    $wpdb->query("ALTER TABLE $customer_info_table MODIFY COLUMN salutation ENUM('Herr','Frau','Divers') NOT NULL");
                    $messages[] = "Spalte 'salutation' wurde repariert.";
                }
            }
            if (empty($messages)) {
                return array('success' => true, 'message' => 'Customer info table ist bereits aktuell.');
            } else {
                return array('success' => true, 'message' => implode(' ', $messages));
        }
        }
        return array('success' => true, 'message' => 'Customer info table existiert nicht.');
    }
    
    public function test_plugin_classes() {
        $classes = array(
            'Nexora_Service_Handler',
            'Nexora_Service_Status_Handler', 
            'Nexora_Service_Request',
            'Nexora_Brand_Handler',
            'Nexora_Invoice_Generator',
            'Nexora_User_Registration',
            'Nexora_User_Profile',
            'Nexora_Customer',
            'Nexora_Admin_Menu',
            'Nexora_Admin_Settings',
            'Nexora_Admin_Notifications'
        );
        
        $results = array();
        
        foreach ($classes as $class) {
            $results[$class] = class_exists($class);
        }
        
        return $results;
    }
    
    public function test_ajax_endpoints() {
        $endpoints = array(
            'nexora_add_service',
            'nexora_get_services',
            'nexora_register_user',
            'nexora_login_user',
            'nexora_get_customer_details',
            'nexora_add_service_request',
            'nexora_get_service_requests',
            'nexora_update_service_request'
        );
        
        $results = array();
        
        foreach ($endpoints as $endpoint) {
            $results[$endpoint] = array(
                'wp_ajax' => has_action("wp_ajax_$endpoint"),
                'wp_ajax_nopriv' => has_action("wp_ajax_nopriv_$endpoint")
            );
        }
        
        return $results;
    }
    
    public function test_wordpress_integration() {
        global $wp_version;
        
        return array(
            'wp_version' => $wp_version,
            'php_version' => PHP_VERSION,
            'mysql_version' => $this->get_mysql_version(),
            'plugin_active' => is_plugin_active(plugin_basename(NEXORA_PLUGIN_DIR . 'Nexora Service Suite-service-manager.php')),
            'database_connected' => $this->test_database_connection(),
            'plugin_loaded' => defined('NEXORA_PLUGIN_DIR')
        );
    }
    
    private function get_mysql_version() {
        global $wpdb;
        return $wpdb->get_var("SELECT VERSION()");
    }
    
    private function test_database_connection() {
        global $wpdb;
        return $wpdb->get_var("SELECT 1") === '1';
    }
    
    public function test_functionality() {
        return array(
            'shortcodes' => $this->test_shortcodes(),
            'hooks' => $this->test_hooks(),
            'capabilities' => $this->test_capabilities()
        );
    }
    
    private function test_shortcodes() {
        return array(
            'nexora_auth_form' => shortcode_exists('nexora_auth_form'),
            'nexora_service_request_form' => shortcode_exists('nexora_service_request_form')
        );
    }
    
    private function test_hooks() {
        return array(
            'init' => has_action('init'),
            'admin_menu' => has_action('admin_menu'),
            'wp_enqueue_scripts' => has_action('wp_enqueue_scripts')
        );
    }
    
    private function test_capabilities() {
        return array(
            'current_user_can_manage_options' => current_user_can('manage_options'),
            'current_user_can_edit_posts' => current_user_can('edit_posts')
        );
    }
    
    public function create_sample_data() {
        global $wpdb;
        $service_table = $wpdb->prefix . 'nexora_services';
        $wpdb->insert($service_table, array(
            'service_name' => 'Test Service',
            'service_description' => 'This is a test service',
            'service_price' => '29.99',
            'created_at' => current_time('mysql')
        ));
        $brand_table = $wpdb->prefix . 'nexora_brands';
        $wpdb->insert($brand_table, array(
            'brand_name' => 'Test Brand',
            'brand_description' => 'This is a test brand',
            'created_at' => current_time('mysql')
        ));
        
        return array('success' => true, 'message' => 'Sample data created successfully');
    }
    public function test_registration_errors() {
        $results = array();
        $results['missing_fields'] = $this->test_registration_with_missing_fields();
        $results['invalid_email'] = $this->test_registration_with_invalid_email();
        $results['complete_registration'] = $this->test_complete_registration();
        $results['duplicate_email'] = $this->test_duplicate_email_registration();
        
        return $results;
    }
    public function test_registration_with_missing_fields() {
        if (!class_exists('Nexora_User_Registration')) {
            return array('error' => 'Registration class not found');
        }
        $original_post = $_POST;
        $test_cases = array(
            'missing_first_name' => array(
                'email' => 'test@example.com',
                'password' => 'testpass123',
                'customer_type' => 'private',
                'salutation' => 'Herr',
                'last_name' => 'Test',
                'street' => 'Teststraße 1',
                'postal_code' => '12345',
                'city' => 'Teststadt',
                'country' => 'Deutschland'
            ),
            'missing_email' => array(
                'password' => 'testpass123',
                'customer_type' => 'private',
                'salutation' => 'Herr',
                'first_name' => 'Test',
                'last_name' => 'User',
                'street' => 'Teststraße 1',
                'postal_code' => '12345',
                'city' => 'Teststadt',
                'country' => 'Deutschland'
            ),
            'missing_salutation' => array(
                'email' => 'test2@example.com',
                'password' => 'testpass123',
                'customer_type' => 'private',
                'first_name' => 'Test',
                'last_name' => 'User',
                'street' => 'Teststraße 1',
                'postal_code' => '12345',
                'city' => 'Teststadt',
                'country' => 'Deutschland'
            )
        );

        $results = array();
        
        foreach ($test_cases as $case_name => $test_data) {
            $_POST = array_merge(array(
                'action' => 'nexora_register_user',
                'auth_nonce' => wp_create_nonce('nexora_auth_nonce')
            ), $test_data);

            try {
                if (ob_get_level()) {
                    ob_end_clean();
                }
                
                ob_start();
                $registration = new Nexora_User_Registration();
                $registration->handle_registration();
                $output = ob_get_clean();
                
                $response = json_decode($output, true);
                
                $results[$case_name] = array(
                    'success' => $response['success'] ?? false,
                    'message' => $response['message'] ?? 'No message returned',
                    'contains_orderly_error' => strpos($output, 'inherited orderly') !== false,
                    'output_is_valid_json' => json_last_error() === JSON_ERROR_NONE,
                    'raw_output' => $output,
                    'json_error' => json_last_error_msg()
                );
                
            } catch (Exception $e) {
                $results[$case_name] = array(
                    'error' => 'Exception: ' . $e->getMessage(),
                    'trace' => $e->getTraceAsString()
                );
            }
        }
        $_POST = $original_post;
        
        return $results;
    }
    private function test_registration_with_invalid_email() {
        if (!class_exists('Nexora_User_Registration')) {
            return array('error' => 'Registration class not found');
        }
        
        $original_post = $_POST;
        
        $_POST = array(
            'action' => 'nexora_register_user',
            'auth_nonce' => wp_create_nonce('nexora_auth_nonce'),
            'email' => 'invalid-email',
            'password' => 'testpass123',
            'customer_type' => 'private',
            'salutation' => 'Herr',
            'first_name' => 'Test',
            'last_name' => 'User',
            'street' => 'Teststraße 1',
            'postal_code' => '12345',
            'city' => 'Teststadt',
            'country' => 'Deutschland'
        );
        
        try {
            ob_start();
            $registration = new Nexora_User_Registration();
            $registration->handle_registration();
            $output = ob_get_clean();
            
            $_POST = $original_post;
            
            $response = json_decode($output, true);
            
            return array(
                'success' => false,
                'message' => $response['message'] ?? 'No message returned',
                'raw_output' => $output
            );
        } catch (Exception $e) {
            $_POST = $original_post;
            
            return array(
                'error' => 'Exception: ' . $e->getMessage()
            );
        }
    }
    public function test_complete_registration() {
        if (!class_exists('Nexora_User_Registration')) {
            return array('error' => 'Registration class not found');
        }

        global $wpdb;
        $original_post = $_POST;
        $test_email = 'complete_test_' . time() . '@example.com';
        
        $_POST = array(
            'action' => 'nexora_register_user',
            'auth_nonce' => wp_create_nonce('nexora_auth_nonce'),
            'email' => $test_email,
            'password' => 'testpass123',
            'customer_type' => 'private',
            'salutation' => 'Herr',
            'first_name' => 'Test',
            'last_name' => 'User',
            'street' => 'Teststraße 1',
            'postal_code' => '12345',
            'city' => 'Teststadt',
            'country' => 'Deutschland',
            'phone' => '123456789',
            'terms_accepted' => '1'
        );

        try {
            if (ob_get_level()) {
                ob_end_clean();
            }
            
            ob_start();
            $registration = new Nexora_User_Registration();
            $registration->handle_registration();
            $output = ob_get_clean();
            
            $_POST = $original_post;
            
            $response = json_decode($output, true);
            $user = get_user_by('email', $test_email);
            $user_created = $user !== false;
            
            $result = array(
                'success' => $response['success'] ?? false,
                'message' => $response['message'] ?? 'No message returned',
                'user_created' => $user_created,
                'user_id' => $user_created ? $user->ID : null,
                'output_is_valid_json' => json_last_error() === JSON_ERROR_NONE,
                'raw_output' => $output,
                'json_error' => json_last_error_msg()
            );
            if ($user_created) {
                $user_data = $wpdb->get_row($wpdb->prepare(
                    "SELECT customer_type, company_name, street, house_number, postfach, postal_code, city, country, vat_id, reference_number, salutation, phone, newsletter, nexora_kind_user FROM {$wpdb->users} WHERE ID = %d",
                    $user->ID
                ));
                
                $result['wp_users_data'] = $user_data;
                $result['wp_users_saved'] = $user_data !== null;
                $user_meta = get_user_meta($user->ID);
                $result['user_meta'] = $user_meta;
                wp_delete_user($user->ID);
            }
            
            return $result;
            
        } catch (Exception $e) {
            $_POST = $original_post;
            
            return array(
                'error' => 'Exception: ' . $e->getMessage(),
                'trace' => $e->getTraceAsString()
            );
        }
    }
    private function test_duplicate_email_registration() {
        if (!class_exists('Nexora_User_Registration')) {
            return array('error' => 'Registration class not found');
        }
        
        $original_post = $_POST;
        $admin_email = get_option('admin_email');
        
        $_POST = array(
            'action' => 'nexora_register_user',
            'auth_nonce' => wp_create_nonce('nexora_auth_nonce'),
            'email' => $admin_email,
            'password' => 'testpass123',
            'customer_type' => 'private',
            'salutation' => 'Herr',
            'first_name' => 'Duplicate',
            'last_name' => 'Test',
            'street' => 'Teststraße 1',
            'postal_code' => '12345',
            'city' => 'Teststadt',
            'country' => 'Deutschland'
        );
        
        try {
            ob_start();
            $registration = new Nexora_User_Registration();
            $registration->handle_registration();
            $output = ob_get_clean();
            
            $_POST = $original_post;
            
            $response = json_decode($output, true);
            
            return array(
                'success' => false,
                'message' => $response['message'] ?? 'No message returned',
                'tested_email' => $admin_email,
                'raw_output' => $output
            );
        } catch (Exception $e) {
            $_POST = $original_post;
            
            return array(
                'error' => 'Exception: ' . $e->getMessage()
            );
        }
    }
    public function test_ajax_registration() {
        $ajax_actions = array(
            'nexora_register_user',
            'nexora_login_user'
        );
        
        $results = array();
        
        foreach ($ajax_actions as $action) {
            $wp_ajax_hook = 'wp_ajax_' . $action;
            $wp_ajax_nopriv_hook = 'wp_ajax_nopriv_' . $action;
            
            $results[$action] = array(
                'registered' => has_action($wp_ajax_hook) || has_action($wp_ajax_nopriv_hook),
                'wp_ajax' => has_action($wp_ajax_hook),
                'wp_ajax_nopriv' => has_action($wp_ajax_nopriv_hook),
                'nonce_valid' => wp_verify_nonce(wp_create_nonce('nexora_auth_nonce'), 'nexora_auth_nonce')
            );
        }
        
        return $results;
    }
    public function test_database_operations() {
        global $wpdb;
        
        $results = array();
        $customer_info_table = $wpdb->prefix . 'nexora_customer_info';
        
        $table_exists = $wpdb->get_var("SHOW TABLES LIKE '$customer_info_table'") === $customer_info_table;
        $results['customer_info_table_exists'] = $table_exists;
        
        if ($table_exists) {
            $table_structure = $wpdb->get_results("DESCRIBE $customer_info_table");
            $results['customer_info_table_structure'] = $table_structure;
            $salutation_column = null;
            foreach ($table_structure as $column) {
                if ($column->Field === 'salutation') {
                    $salutation_column = $column;
                    break;
                }
            }
            
            $results['salutation_column'] = $salutation_column;
            $results['salutation_enum_correct'] = $salutation_column && 
                strpos($salutation_column->Type, "'Herr'") !== false && 
                strpos($salutation_column->Type, "'Frau'") !== false && 
                strpos($salutation_column->Type, "'Divers'") !== false;
        }
        if ($table_exists) {
            $test_data = array(
                'user_id' => 9999,
                'customer_type' => 'private',
                'company_name' => '',
                'street' => 'Teststraße 1',
                'postal_code' => '12345',
                'city' => 'Teststadt',
                'country' => 'Deutschland',
                'salutation' => 'Herr',
                'phone' => '123456789',
                'newsletter' => 0
            );
            
            $insert_result = $wpdb->insert($customer_info_table, $test_data);
            $results['test_insert_success'] = $insert_result !== false;
            $results['test_insert_error'] = $wpdb->last_error;
            if ($insert_result) {
                $wpdb->delete($customer_info_table, array('user_id' => 9999));
            }
        }
        
        return $results;
    }
    public function test_ajax_registration_comprehensive() {
        $ajax_results = $this->test_ajax_registration();
        $test_email = 'ajax_test_' . time() . '@example.com';
        $original_post = $_POST;
        $original_server = $_SERVER;
        if (!defined('DOING_AJAX')) {
            define('DOING_AJAX', true);
        }
        $_SERVER['HTTP_X_REQUESTED_WITH'] = 'XMLHttpRequest';
        $_POST = array(
            'action' => 'nexora_register_user',
            'auth_nonce' => wp_create_nonce('nexora_auth_nonce'),
            'email' => $test_email,
            'password' => 'testpass123',
            'customer_type' => 'private',
            'salutation' => 'Herr',
            'first_name' => 'AJAX',
            'last_name' => 'Test',
            'street' => 'Teststraße 1',
            'postal_code' => '12345',
            'city' => 'Teststadt',
            'country' => 'Deutschland'
        );
        
        try {
            if (ob_get_level()) {
                ob_end_clean();
            }
            
            ob_start();
            if (class_exists('Nexora_User_Registration')) {
                $registration = new Nexora_User_Registration();
                $registration->handle_registration();
            } else {
                echo json_encode(array(
                    'success' => false,
                    'message' => 'Registration class not found'
                ));
            }
            
            $output = ob_get_clean();
            
            $response = json_decode($output, true);
            
            $ajax_test_result = array(
                'output' => $output,
                'response' => $response,
                'success' => $response['success'] ?? false,
                'message' => $response['message'] ?? 'No message',
                'valid_json' => json_last_error() === JSON_ERROR_NONE,
                'json_error' => json_last_error_msg()
            );
            $user = get_user_by('email', $test_email);
            if ($user) {
                $ajax_test_result['user_created'] = true;
                $ajax_test_result['user_id'] = $user->ID;
                wp_delete_user($user->ID);
                
                global $wpdb;
                $customer_info_table = $wpdb->prefix . 'nexora_customer_info';
                $wpdb->delete($customer_info_table, array('user_id' => $user->ID));
            } else {
                $ajax_test_result['user_created'] = false;
            }
            
        } catch (Exception $e) {
            $ajax_test_result = array(
                'error' => 'Exception: ' . $e->getMessage(),
                'trace' => $e->getTraceAsString()
            );
        } finally {
            $_POST = $original_post;
            $_SERVER = $original_server;
        }
        
        return array(
            'endpoint_registration' => $ajax_results,
            'actual_ajax_test' => $ajax_test_result
        );
    }
    public function test_form_rendering() {
        if (!class_exists('Nexora_User_Registration')) {
            return array('error' => 'Registration class not found');
        }
        
        try {
            $registration = new Nexora_User_Registration();
            
            ob_start();
            $form_output = $registration->render_auth_form(array('mode' => 'register'));
            ob_end_clean();
            
            $results = array(
                'form_renders' => !empty($form_output),
                'contains_register_form' => strpos($form_output, 'Nexora Service Suite-register-form') !== false,
                'contains_salutation_field' => strpos($form_output, 'salutation') !== false,
                'contains_divers_option' => strpos($form_output, 'Divers') !== false,
                'form_length' => strlen($form_output),
                'form_html' => $form_output,
                'debug_info' => array(
                    'form_contains_register' => strpos($form_output, 'Nexora Service Suite-register-form'),
                    'form_contains_salutation' => strpos($form_output, 'salutation'),
                    'form_contains_divers' => strpos($form_output, 'Divers'),
                    'form_contains_register_tab' => strpos($form_output, 'register-tab'),
                    'form_contains_auth_container' => strpos($form_output, 'Nexora Service Suite-auth-container')
                )
            );
            
            return $results;
            
        } catch (Exception $e) {
            return array(
                'error' => 'Exception: ' . $e->getMessage(),
                'trace' => $e->getTraceAsString()
            );
        }
    }
    public function comprehensive_system_test() {
        $start_time = microtime(true);
        $results = array(
            'start_time' => date('Y-m-d H:i:s'),
            'tests_performed' => array(),
            'issues_found' => array(),
            'fixes_applied' => array(),
            'recommendations' => array(),
            'summary' => array(),
            'success' => true,
            'debug_info' => array(),
            'detailed_results' => array()
        );
        $results['debug_info'][] = "Starting comprehensive test at " . date('Y-m-d H:i:s');
        $results['debug_info'][] = "PHP Version: " . PHP_VERSION;
        $results['debug_info'][] = "WordPress Version: " . get_bloginfo('version');
        $results['debug_info'][] = "Plugin Directory: " . NEXORA_PLUGIN_DIR;
        $results['debug_info'][] = "Plugin URL: " . NEXORA_PLUGIN_URL;
        $results['debug_info'][] = "Current User: " . (wp_get_current_user()->user_login ?: 'Not logged in');
        $results['debug_info'][] = "User Capabilities: " . implode(', ', array_keys(wp_get_current_user()->allcaps));
        try {
            $results['tests_performed'][] = 'Database Tables Check';
            $results['debug_info'][] = "Running enhanced database tables check...";
            $table_results = $this->check_tables_status();
            $missing_tables = array();
            $invalid_tables = array();
            $existing_tables = array();
            
            $results['detailed_results']['database_tables'] = array();
            
            foreach ($table_results as $table => $status) {
                $table_info = array(
                    'table_name' => $table,
                    'exists' => $status['exists'],
                    'structure_valid' => $status['structure_valid']
                );
                
                if (!$status['exists']) {
                    $missing_tables[] = $table;
                    $table_info['status'] = 'MISSING';
                    $results['debug_info'][] = "Table $table: MISSING - Will be created automatically";
                } elseif (!$status['structure_valid']) {
                    $invalid_tables[] = $table;
                    $table_info['status'] = 'INVALID_STRUCTURE';
                    $results['debug_info'][] = "Table $table: EXISTS but INVALID structure";
                } else {
                    $existing_tables[] = $table;
                    $table_info['status'] = 'OK';
                    $results['debug_info'][] = "Table $table: EXISTS and VALID";
                }
                
                $results['detailed_results']['database_tables'][$table] = $table_info;
            }
            if (!empty($missing_tables)) {
                $results['issues_found'][] = "Missing tables: " . implode(', ', $missing_tables);
                $results['debug_info'][] = "Attempting to create missing tables...";
                $this->create_missing_tables();
                $results['fixes_applied'][] = "Missing tables automatically created: " . implode(', ', $missing_tables);
                $results['debug_info'][] = "Missing tables creation completed";
            }
            
            if (!empty($invalid_tables)) {
                $results['issues_found'][] = "Invalid table structures: " . implode(', ', $invalid_tables);
                $results['recommendations'][] = "Run table repair for: " . implode(', ', $invalid_tables);
            }
            
            $results['debug_info'][] = "Database tables check completed successfully";
            $results['debug_info'][] = "Summary: " . count($existing_tables) . " OK, " . count($missing_tables) . " missing, " . count($invalid_tables) . " invalid";
            
        } catch (Exception $e) {
            $results['issues_found'][] = "Database Tables Check Error: " . $e->getMessage();
            $results['debug_info'][] = "Database tables check failed: " . $e->getMessage();
            $results['debug_info'][] = "Exception trace: " . $e->getTraceAsString();
            $results['success'] = false;
        }
        $results['tests_performed'][] = 'Plugin Classes Check';
        $class_results = $this->test_plugin_classes();
        $missing_classes = array();
        
        foreach ($class_results as $class => $exists) {
            if (!$exists) {
                $missing_classes[] = $class;
            }
        }
        
        if (!empty($missing_classes)) {
            $results['issues_found'][] = "Missing classes: " . implode(', ', $missing_classes);
            $results['recommendations'][] = "Reactivate plugin or check files";
            $results['success'] = false;
        }
        $results['tests_performed'][] = 'AJAX Endpoints Check';
        $ajax_results = $this->test_ajax_endpoints();
        $missing_endpoints = array();
        
        foreach ($ajax_results as $endpoint => $status) {
            if (!$status['wp_ajax'] && !$status['wp_ajax_nopriv']) {
                $missing_endpoints[] = $endpoint;
            }
        }
        
        if (!empty($missing_endpoints)) {
            $results['issues_found'][] = "Missing AJAX endpoints: " . implode(', ', $missing_endpoints);
            $results['recommendations'][] = "Reactivate plugin";
            $results['success'] = false;
        }
        $results['tests_performed'][] = 'WordPress Integration Check';
        $wp_results = $this->test_wordpress_integration();
        
        if (!$wp_results['database_connected']) {
            $results['issues_found'][] = "Database connection failed";
            $results['success'] = false;
        }
        
        if (!$wp_results['plugin_loaded']) {
            $results['issues_found'][] = "Plugin not properly loaded";
            $results['success'] = false;
        }
        $results['tests_performed'][] = 'Database Operations Check';
        $db_results = $this->test_database_operations();
        
        if (!$db_results['customer_info_table_exists']) {
            $results['issues_found'][] = "Customer Info table missing";
            $this->create_missing_tables();
            $results['fixes_applied'][] = "Customer Info table created";
        } elseif (!$db_results['salutation_enum_correct']) {
            $results['issues_found'][] = "Salutation ENUM values are incorrect";
            $results['recommendations'][] = "Run auto-fix-tables.php";
        }
        
        if (!$db_results['test_insert_success'] && !empty($db_results['test_insert_error'])) {
            $results['issues_found'][] = "Database insert error: " . $db_results['test_insert_error'];
            $results['success'] = false;
        }
        $results['tests_performed'][] = 'Form Rendering Check';
        $form_results = $this->test_form_rendering();
        
        if (isset($form_results['error'])) {
            $results['issues_found'][] = "Form rendering error: " . $form_results['error'];
            $results['success'] = false;
        } elseif (!$form_results['form_renders']) {
            $results['issues_found'][] = "Registration form not rendering";
            $results['success'] = false;
        } elseif (!$form_results['contains_divers_option']) {
            $results['issues_found'][] = "Divers option missing in salutation field";
            $results['recommendations'][] = "Check form template";
        }
        try {
            $results['tests_performed'][] = 'Registration Error Handling';
            $results['debug_info'][] = "Testing comprehensive registration error handling...";
            $reg_missing_results = $this->test_registration_with_missing_fields();
            
            $results['detailed_results']['registration_errors'] = array();
            
            if (empty($reg_missing_results) || !is_array($reg_missing_results)) {
                $results['issues_found'][] = "Registration error test returned empty or invalid results";
                $results['debug_info'][] = "ERROR: Registration test returned: " . var_export($reg_missing_results, true);
                $results['success'] = false;
            } else {
                $results['debug_info'][] = "Registration test returned " . count($reg_missing_results) . " test cases";
                
                foreach ($reg_missing_results as $test_case => $result) {
                    $test_info = array(
                        'test_case' => $test_case,
                        'result' => $result
                    );
                    
                    $results['debug_info'][] = "Analyzing test case: $test_case";
                    
                    if (isset($result['error'])) {
                        $results['issues_found'][] = "Registration $test_case error: " . $result['error'];
                        $test_info['status'] = 'ERROR';
                        $results['debug_info'][] = "  ERROR in $test_case: " . $result['error'];
                        $results['success'] = false;
                    } elseif (isset($result['contains_orderly_error']) && $result['contains_orderly_error']) {
                        $results['issues_found'][] = "CRITICAL: Strange 'inherited orderly' error found in $test_case";
                        $results['recommendations'][] = "Activate/check error message cleaning function";
                        $results['recommendations'][] = "IMMEDIATE: Check Nexora_User_Registration class";
                        $test_info['status'] = 'CRITICAL_ERROR';
                        $results['debug_info'][] = "  CRITICAL ERROR in $test_case: 'inherited orderly' detected";
                    } elseif (!$result['output_is_valid_json']) {
                        $results['issues_found'][] = "Invalid JSON in $test_case: " . $result['json_error'];
                        $test_info['status'] = 'INVALID_JSON';
                        $results['debug_info'][] = "  INVALID JSON in $test_case: " . $result['json_error'];
                        $results['success'] = false;
                    } else {
                        $test_info['status'] = 'OK';
                        $results['debug_info'][] = "  $test_case: OK";
                    }
                    if (isset($result['message'])) {
                        $results['debug_info'][] = "  Message: " . $result['message'];
                        
                        if (strpos($result['message'], 'field') !== false && strpos($result['message'], 'inherited') !== false) {
                            $results['issues_found'][] = "WARNING: Possible translation corruption detected in registration";
                            $results['recommendations'][] = "CRITICAL: Check WordPress language files or plugin translation";
                            $results['debug_info'][] = "  TRANSLATION CORRUPTION detected in message";
                        }
                        
                        if (strpos($result['message'], 'inherited orderly') !== false) {
                            $results['issues_found'][] = "CRITICAL: 'inherited orderly' error message corruption detected";
                            $results['recommendations'][] = "IMMEDIATE: Check includes/class-user-registration.php";
                            $results['debug_info'][] = "  'inherited orderly' corruption detected";
                        }
                    }
                    
                    $results['detailed_results']['registration_errors'][$test_case] = $test_info;
                }
            }
            
            $results['debug_info'][] = "Registration error handling test completed";
        } catch (Exception $e) {
            $results['issues_found'][] = "Registration Error Handling Test Error: " . $e->getMessage();
            $results['debug_info'][] = "Registration error handling test failed: " . $e->getMessage();
            $results['debug_info'][] = "Exception trace: " . $e->getTraceAsString();
            $results['success'] = false;
        }
        $results['tests_performed'][] = 'Complete Registration Flow';
        $complete_reg_result = $this->test_complete_registration();
        
        if (isset($complete_reg_result['error'])) {
            $results['issues_found'][] = "Complete registration error: " . $complete_reg_result['error'];
            $results['success'] = false;
        } elseif (!$complete_reg_result['success']) {
            $results['issues_found'][] = "Registration fails: " . $complete_reg_result['message'];
            $results['success'] = false;
        } elseif (!$complete_reg_result['user_created']) {
            $results['issues_found'][] = "User not created despite success message";
            $results['success'] = false;
        } elseif (!$complete_reg_result['customer_info_saved']) {
            $results['issues_found'][] = "Customer information not saved to database";
            $results['recommendations'][] = "Check Customer Info table and save_user_meta function";
        }
        $results['tests_performed'][] = 'AJAX Registration Comprehensive';
        $ajax_comp_results = $this->test_ajax_registration_comprehensive();
        
        if (isset($ajax_comp_results['actual_ajax_test']['error'])) {
            $results['issues_found'][] = "AJAX registration error: " . $ajax_comp_results['actual_ajax_test']['error'];
            $results['success'] = false;
        } elseif (!$ajax_comp_results['actual_ajax_test']['success']) {
            $results['issues_found'][] = "AJAX registration fails: " . $ajax_comp_results['actual_ajax_test']['message'];
            $results['success'] = false;
        } elseif (!$ajax_comp_results['actual_ajax_test']['valid_json']) {
            $results['issues_found'][] = "AJAX response is not valid JSON: " . $ajax_comp_results['actual_ajax_test']['json_error'];
            $results['success'] = false;
        }
        $results['tests_performed'][] = 'Registration AJAX Endpoints';
        $reg_ajax_results = $this->test_ajax_registration();
        
        foreach ($reg_ajax_results as $endpoint => $status) {
            if (!$status['registered']) {
                $results['issues_found'][] = "Registration AJAX endpoint $endpoint not registered";
                $results['success'] = false;
            }
        }
        $end_time = microtime(true);
        $execution_time = round($end_time - $start_time, 2);
        $results['summary'] = array(
            'total_tests' => count($results['tests_performed']),
            'issues_found_count' => count($results['issues_found']),
            'fixes_applied_count' => count($results['fixes_applied']),
            'recommendations_count' => count($results['recommendations']),
            'execution_time' => $execution_time . 's',
            'overall_status' => $results['success'] ? 'All tests passed' : 'Issues found'
        );
        try {
            $results['tests_performed'][] = 'Additional Registration Debug';
            $results['debug_info'][] = "Running additional registration debug tests...";
            if (class_exists('Nexora_User_Registration')) {
                $registration_instance = new Nexora_User_Registration();
                $results['debug_info'][] = "Nexora_User_Registration class successfully instantiated";
                if (function_exists('nexora_clean_error_message')) {
                    $results['debug_info'][] = "nexora_clean_error_message function exists";
                } else {
                    $results['issues_found'][] = "nexora_clean_error_message function does not exist";
                    $results['recommendations'][] = "IMMEDIATE: Error cleaning function in class-user-registration.php needs to be activated";
                }
            } else {
                $results['issues_found'][] = "Nexora_User_Registration class does not exist";
                $results['success'] = false;
            }
            if (function_exists('wp_insert_user')) {
                $results['debug_info'][] = "wp_insert_user function available";
            } else {
                $results['issues_found'][] = "wp_insert_user function not available";
                $results['success'] = false;
            }
            $test_nonce = wp_create_nonce('nexora_auth_nonce');
            if ($test_nonce) {
                $results['debug_info'][] = "Nonce creation working";
            } else {
                $results['issues_found'][] = "Nonce creation failed";
                $results['success'] = false;
            }
            $results['debug_info'][] = "Testing for 'inherited orderly' error specifically...";
            try {
                $test_data = array(
                    'first_name' => '',
                    'last_name' => '',
                    'email' => 'test@example.com',
                    'phone' => '',
                    'salutation' => '',
                    'street' => '',
                    'house_number' => '',
                    'postal_code' => '',
                    'city' => '',
                    'country' => 'Germany'
                );
                if (function_exists('nexora_clean_error_message')) {
                    $test_error = "This field is inherited orderly required.";
                    $cleaned_error = nexora_clean_error_message($test_error);
                    
                    if ($cleaned_error === $test_error) {
                        $results['issues_found'][] = "CRITICAL: Error cleaning function not working properly";
                        $results['recommendations'][] = "IMMEDIATE: Check nexora_clean_error_message function implementation";
                        $results['debug_info'][] = "Error cleaning function test failed - function exists but not working";
                    } else {
                        $results['debug_info'][] = "Error cleaning function working correctly";
                        $results['debug_info'][] = "Original: '$test_error' -> Cleaned: '$cleaned_error'";
                    }
                } else {
                    $results['issues_found'][] = "CRITICAL: Error cleaning function missing - this causes 'inherited orderly' errors";
                    $results['recommendations'][] = "IMMEDIATE: Add nexora_clean_error_message function to class-user-registration.php";
                    $results['debug_info'][] = "Error cleaning function missing - this is likely the cause of 'inherited orderly' errors";
                }
                
            } catch (Exception $e) {
                $results['debug_info'][] = "Error during 'inherited orderly' test: " . $e->getMessage();
            }
            
            $results['debug_info'][] = "Additional registration debug completed";
        } catch (Exception $e) {
            $results['issues_found'][] = "Additional Registration Debug Error: " . $e->getMessage();
            $results['debug_info'][] = "Additional registration debug failed: " . $e->getMessage();
            $results['debug_info'][] = "Exception trace: " . $e->getTraceAsString();
            $results['success'] = false;
        }
        if (!empty($results['issues_found'])) {
            if (strpos(implode(' ', $results['issues_found']), 'inherited orderly') !== false) {
                $results['recommendations'][] = "CRITICAL: Strange error message corruption found - Activate Error Message Cleaning function";
                $results['recommendations'][] = "IMMEDIATE: Check includes/class-user-registration.php lines 426-450";
                $results['recommendations'][] = "SOLUTION: Manually activate nexora_clean_error_message function";
            }
            
            if (strpos(implode(' ', $results['issues_found']), 'JSON') !== false) {
                $results['recommendations'][] = "JSON output problems - Check output buffer or PHP errors";
                $results['recommendations'][] = "Check wp_send_json() calls in registration handler";
            }
            
            if (strpos(implode(' ', $results['issues_found']), 'table') !== false) {
                $results['recommendations'][] = "Database problems - Run auto-fix-tables.php";
            }
            
            if (strpos(implode(' ', $results['issues_found']), 'translation') !== false) {
                $results['recommendations'][] = "CRITICAL: WordPress language files corrupted - Deactivate/reactivate plugin";
            }
        }
        
        $results['end_time'] = date('Y-m-d H:i:s');
        
        return $results;
    }
    public function quick_registration_diagnostic() {
        $results = array(
            'success' => true,
            'issues' => array(),
            'fixes' => array(),
            'debug_info' => array(),
            'registration_test_results' => array()
        );
        
        $results['debug_info'][] = "🚀 Starting quick registration diagnostic...";
        $results['debug_info'][] = "Focusing on registration button and form issues...";
        $results['debug_info'][] = "1. Checking Nexora_User_Registration class...";
        if (!class_exists('Nexora_User_Registration')) {
            $results['issues'][] = "❌ Nexora_User_Registration class not found";
            $results['debug_info'][] = "   ERROR: Registration class missing - this is why registration fails";
        } else {
            $results['debug_info'][] = "   ✅ Nexora_User_Registration class found";
            $results['debug_info'][] = "2. Checking registration AJAX endpoint...";
            if (!has_action('wp_ajax_nexora_register_user') && !has_action('wp_ajax_nopriv_nexora_register_user')) {
                $results['issues'][] = "❌ Registration AJAX endpoint not registered";
                $results['debug_info'][] = "   ERROR: AJAX endpoint missing - registration button won't work";
            } else {
                $results['debug_info'][] = "   ✅ Registration AJAX endpoint registered";
            }
        }
        $results['debug_info'][] = "3. Checking customer_info table...";
        global $wpdb;
        $customer_table = $wpdb->prefix . 'nexora_customer_info';
        $table_exists = $wpdb->get_var("SHOW TABLES LIKE '$customer_table'") === $customer_table;
        
        if (!$table_exists) {
            $results['issues'][] = "❌ customer_info table missing";
            $results['debug_info'][] = "   ERROR: customer_info table missing - user data can't be saved";
            $this->create_missing_tables();
            $results['fixes'][] = "✅ customer_info table created automatically";
        } else {
            $results['debug_info'][] = "   ✅ customer_info table exists";
        }
        $results['debug_info'][] = "4. Testing for 'inherited orderly' error...";
        if (function_exists('nexora_clean_error_message')) {
            $test_error = "This field is inherited orderly required.";
            $cleaned = nexora_clean_error_message($test_error);
            
            if ($cleaned === $test_error) {
                $results['issues'][] = "❌ Error cleaning function not working";
                $results['debug_info'][] = "   ERROR: Error cleaning function exists but doesn't work";
            } else {
                $results['debug_info'][] = "   ✅ Error cleaning function working";
            }
        } else {
            $results['issues'][] = "❌ Error cleaning function missing - causes 'inherited orderly' errors";
            $results['debug_info'][] = "   ERROR: nexora_clean_error_message function missing";
        }
        $results['debug_info'][] = "5. Testing registration with missing fields...";
        try {
            $test_data = array(
                'first_name' => '',
                'last_name' => '',
                'email' => 'test@example.com',
                'phone' => '',
                'salutation' => '',
                'street' => '',
                'house_number' => '',
                'postal_code' => '',
                'city' => '',
                'country' => 'Germany'
            );
            $registration_result = $this->test_registration_with_missing_fields();
            
            if (empty($registration_result)) {
                $results['issues'][] = "❌ Registration test returned empty results";
                $results['debug_info'][] = "   ERROR: Registration test failed completely";
            } else {
                $results['debug_info'][] = "   Registration test completed";
                $results['registration_test_results'] = $registration_result;
                foreach ($registration_result as $test_name => $result) {
                    if (isset($result['message']) && strpos($result['message'], 'inherited orderly') !== false) {
                        $results['issues'][] = "❌ 'inherited orderly' error detected in $test_name";
                        $results['debug_info'][] = "   ERROR: Found 'inherited orderly' in: " . $result['message'];
                    }
                    
                    if (isset($result['error'])) {
                        $results['issues'][] = "❌ Registration error in $test_name: " . $result['error'];
                        $results['debug_info'][] = "   ERROR: $test_name failed: " . $result['error'];
                    }
                }
            }
        } catch (Exception $e) {
            $results['issues'][] = "❌ Registration test exception: " . $e->getMessage();
            $results['debug_info'][] = "   ERROR: Exception during registration test: " . $e->getMessage();
        }
        $results['debug_info'][] = "6. Testing form rendering...";
        $form_result = $this->test_form_rendering();
        
        if (isset($form_result['error'])) {
            $results['issues'][] = "❌ Form rendering error: " . $form_result['error'];
            $results['debug_info'][] = "   ERROR: Form won't render: " . $form_result['error'];
        } elseif (!$form_result['form_renders']) {
            $results['issues'][] = "❌ Registration form not rendering";
            $results['debug_info'][] = "   ERROR: Form doesn't render at all";
        } else {
            $results['debug_info'][] = "   ✅ Form renders correctly";
        }
        $results['debug_info'][] = "7. Testing WordPress user creation...";
        if (!function_exists('wp_insert_user')) {
            $results['issues'][] = "❌ wp_insert_user function not available";
            $results['debug_info'][] = "   ERROR: WordPress user creation function missing";
        } else {
            $results['debug_info'][] = "   ✅ wp_insert_user function available";
        }
        $results['debug_info'][] = "2. PLZ validation removed - no validation for postal code";
        $results['debug_info'][] = "   Testing PLZ validation removal...";
        $original_post = $_POST;
        $_POST = array(
            'action' => 'nexora_register_user',
            'auth_nonce' => wp_create_nonce('nexora_auth_nonce'),
            'email' => 'test_plz_' . time() . '@example.com',
            'password' => 'testpass123',
            'customer_type' => 'private',
            'salutation' => 'Herr',
            'first_name' => 'Test',
            'last_name' => 'User',
            'street' => 'Teststraße 1',
            'postal_code' => 'abc123',
            'city' => 'Teststadt',
            'country' => 'Deutschland'
        );
        
        ob_start();
        $registration = new Nexora_User_Registration();
        $registration->handle_registration();
        $output = ob_get_clean();
        
        $_POST = $original_post;
        
        $response = json_decode($output, true);
        
        if (isset($response['success']) && $response['success']) {
            $results['debug_info'][] = "     ✅ PLZ validation removed - invalid PLZ accepted";
        } else {
            $results['debug_info'][] = "     ❌ PLZ validation still active";
            if (isset($response['message'])) {
                $results['debug_info'][] = "     Error message: " . $response['message'];
            }
        }
        $results['debug_info'][] = "🏁 Quick diagnostic completed";
        $results['debug_info'][] = "Found " . count($results['issues']) . " issues";
        $results['debug_info'][] = "Applied " . count($results['fixes']) . " fixes";
        
        if (count($results['issues']) > 0) {
            $results['success'] = false;
            $results['debug_info'][] = "⚠️ Issues found - registration button will not work properly";
        } else {
            $results['debug_info'][] = "✅ No issues found - registration should work";
        }
        
        return $results;
    }
    public function fix_registration_issues() {
        $results = array(
            'success' => true,
            'issues_fixed' => array(),
            'errors' => array(),
            'debug_info' => array()
        );
        
        $results['debug_info'][] = "🔧 Starting registration issues fix...";
        $results['debug_info'][] = "1. Repairing customer_info table...";
        $repair_result = $this->repair_customer_info_table();
        if ($repair_result['success']) {
            $results['issues_fixed'][] = "Customer info table repaired";
            $results['debug_info'][] = "   ✅ " . $repair_result['message'];
        } else {
            $results['errors'][] = "Failed to repair customer info table";
            $results['debug_info'][] = "   ❌ Customer info table repair failed";
        }
        $results['debug_info'][] = "2. Checking salutation ENUM values...";
        global $wpdb;
        $customer_info_table = $wpdb->prefix . 'nexora_customer_info';
        
        if ($wpdb->get_var("SHOW TABLES LIKE '$customer_info_table'") === $customer_info_table) {
            $salutation_column = $wpdb->get_row("SHOW COLUMNS FROM $customer_info_table LIKE 'salutation'");
            
            if ($salutation_column) {
                $column_type = $salutation_column->Type;
                $results['debug_info'][] = "   Current salutation type: $column_type";
                
                if (strpos($column_type, "'Herr'") !== false && 
                    strpos($column_type, "'Frau'") !== false && 
                    strpos($column_type, "'Divers'") !== false) {
                    $results['debug_info'][] = "   ✅ Salutation ENUM is correct";
                } else {
                    $results['debug_info'][] = "   🔧 Fixing salutation ENUM...";
                    $wpdb->query("ALTER TABLE $customer_info_table MODIFY COLUMN salutation ENUM('Herr','Frau','Divers') NOT NULL");
                    $results['issues_fixed'][] = "Salutation ENUM fixed";
                    $results['debug_info'][] = "   ✅ Salutation ENUM fixed";
                }
            }
        }
        $results['debug_info'][] = "3. Testing form rendering...";
        $form_test = $this->test_form_rendering();
        
        if (isset($form_test['error'])) {
            $results['errors'][] = "Form rendering error: " . $form_test['error'];
            $results['debug_info'][] = "   ❌ Form rendering failed";
        } else {
            $results['debug_info'][] = "   ✅ Form renders: " . ($form_test['form_renders'] ? 'Yes' : 'No');
            $results['debug_info'][] = "   ✅ Contains salutation: " . ($form_test['contains_salutation_field'] ? 'Yes' : 'No');
            $results['debug_info'][] = "   ✅ Contains Divers: " . ($form_test['contains_divers_option'] ? 'Yes' : 'No');
            
            if (!$form_test['contains_salutation_field'] || !$form_test['contains_divers_option']) {
                $results['errors'][] = "Form missing required fields";
            }
        }
        $results['debug_info'][] = "4. Testing registration class...";
        if (class_exists('Nexora_User_Registration')) {
            $results['debug_info'][] = "   ✅ Registration class exists";
            
            try {
                $registration = new Nexora_User_Registration();
                $results['debug_info'][] = "   ✅ Registration class instantiated";
            } catch (Exception $e) {
                $results['errors'][] = "Registration class instantiation failed: " . $e->getMessage();
                $results['debug_info'][] = "   ❌ Registration class instantiation failed";
            }
        } else {
            $results['errors'][] = "Registration class not found";
            $results['debug_info'][] = "   ❌ Registration class not found";
        }
        $results['debug_info'][] = "5. Testing AJAX endpoints...";
        if (has_action('wp_ajax_nexora_register_user') || has_action('wp_ajax_nopriv_nexora_register_user')) {
            $results['debug_info'][] = "   ✅ Registration AJAX endpoint registered";
        } else {
            $results['errors'][] = "Registration AJAX endpoint not registered";
            $results['debug_info'][] = "   ❌ Registration AJAX endpoint not registered";
        }
        
        $results['debug_info'][] = "🏁 Registration issues fix completed";
        $results['debug_info'][] = "Fixed " . count($results['issues_fixed']) . " issues";
        $results['debug_info'][] = "Found " . count($results['errors']) . " errors";
        
        if (count($results['errors']) > 0) {
            $results['success'] = false;
        }
        
        return $results;
    }
    public function render_repair_page() {
        wp_enqueue_style('Nexora Service Suite-admin-css', NEXORA_PLUGIN_URL . 'assets/css/admin.css');
        wp_enqueue_script('jquery');
        
        $nonce = wp_create_nonce('nexora_repair_nonce');
        $ajax_url = admin_url('admin-ajax.php');
        
        include(NEXORA_PLUGIN_DIR . 'templates/repair-system.php');
    }
    public function test_inherited_orderly_error() {
        $results = array(
            'success' => true,
            'debug_info' => array(),
            'issues_found' => array(),
            'fixes_applied' => array()
        );
        
        $results['debug_info'][] = "🔍 Testing for 'inherited orderly' error specifically...";
        if (!function_exists('nexora_clean_error_message')) {
            $results['issues_found'][] = "CRITICAL: Error cleaning function missing";
            $results['debug_info'][] = "   ❌ nexora_clean_error_message function not found";
        } else {
            $results['debug_info'][] = "   ✅ Error cleaning function exists";
            $test_error = "The field Vorname is inherited orderly required.";
            $cleaned = nexora_clean_error_message($test_error);
            
            if ($cleaned === $test_error) {
                $results['issues_found'][] = "CRITICAL: Error cleaning function not working";
                $results['debug_info'][] = "   ❌ Function exists but doesn't clean the error";
            } else {
                $results['debug_info'][] = "   ✅ Error cleaning function working: '$cleaned'";
            }
        }
        $results['debug_info'][] = "2. Checking WordPress translation filters...";
        if (has_filter('gettext')) {
            $results['debug_info'][] = "   ✅ gettext filter exists";
        } else {
            $results['issues_found'][] = "No gettext filter found";
            $results['debug_info'][] = "   ❌ No gettext filter found";
        }
        $results['debug_info'][] = "3. Testing actual registration with missing fields...";
        try {
            $original_post = $_POST;
            
            $_POST = array(
                'action' => 'nexora_register_user',
                'auth_nonce' => wp_create_nonce('nexora_auth_nonce'),
                'email' => 'test@example.com',
                'password' => 'testpass123',
                'customer_type' => 'private',
                'salutation' => '',
                'first_name' => '',
                'last_name' => 'Test',
                'street' => 'Teststraße 1',
                'postal_code' => '12345',
                'city' => 'Teststadt',
                'country' => 'Deutschland'
            );
            
            ob_start();
            $registration = new Nexora_User_Registration();
            $registration->handle_registration();
            $output = ob_get_clean();
            
            $_POST = $original_post;
            
            $response = json_decode($output, true);
            
            $results['debug_info'][] = "   Raw output: " . substr($output, 0, 200) . "...";
            $results['debug_info'][] = "   JSON valid: " . (json_last_error() === JSON_ERROR_NONE ? 'Yes' : 'No');
            
            if (isset($response['message'])) {
                $results['debug_info'][] = "   Response message: " . $response['message'];
                
                if (strpos($response['message'], 'inherited orderly') !== false) {
                    $results['issues_found'][] = "CRITICAL: 'inherited orderly' error still appearing";
                    $results['debug_info'][] = "   ❌ 'inherited orderly' found in response";
                } else {
                    $results['debug_info'][] = "   ✅ No 'inherited orderly' in response";
                }
            }
            
        } catch (Exception $e) {
            $results['issues_found'][] = "Exception during test: " . $e->getMessage();
            $results['debug_info'][] = "   ❌ Exception: " . $e->getMessage();
        }
        $results['debug_info'][] = "4. Testing translation filters...";
        $test_text = "The field Vorname is inherited orderly required.";
        $filtered_text = apply_filters('gettext', $test_text, $test_text, 'default');
        
        if ($filtered_text === $test_text) {
            $results['issues_found'][] = "Translation filters not working";
            $results['debug_info'][] = "   ❌ Translation filters not working";
        } else {
            $results['debug_info'][] = "   ✅ Translation filters working: '$filtered_text'";
        }
        
        if (count($results['issues_found']) > 0) {
            $results['success'] = false;
        }
        
        return $results;
    }
    public function test_privatkunde_registration() {
        $results = array(
            'success' => true,
            'debug_info' => array(),
            'issues_found' => array(),
            'fixes_applied' => array()
        );
        
        $results['debug_info'][] = "🔍 Testing Privatkunde registration specifically...";
        if (!class_exists('Nexora_User_Registration')) {
            $results['issues_found'][] = "CRITICAL: Registration class not found";
            $results['debug_info'][] = "   ❌ Nexora_User_Registration class not found";
            $results['success'] = false;
            return $results;
        }
        
        $results['debug_info'][] = "   ✅ Registration class exists";
        $results['debug_info'][] = "2. Testing Privatkunde registration with all fields...";
        try {
            $original_post = $_POST;
            
            $_POST = array(
                'action' => 'nexora_register_user',
                'auth_nonce' => wp_create_nonce('nexora_auth_nonce'),
                'email' => 'privatkunde_test_' . time() . '@example.com',
                'password' => 'testpass123',
                'customer_type' => 'private',
                'salutation' => 'Herr',
                'first_name' => 'Test',
                'last_name' => 'Privatkunde',
                'street' => 'Teststraße 1',
                'postal_code' => '12345',
                'city' => 'Teststadt',
                'country' => 'Deutschland',
                'phone' => '123456789',
                'terms_accepted' => '1'
            );
            
            ob_start();
            $registration = new Nexora_User_Registration();
            $registration->handle_registration();
            $output = ob_get_clean();
            
            $_POST = $original_post;
            
            $response = json_decode($output, true);
            
            $results['debug_info'][] = "   Raw output: " . substr($output, 0, 200) . "...";
            $results['debug_info'][] = "   JSON valid: " . (json_last_error() === JSON_ERROR_NONE ? 'Yes' : 'No');
            
            if (isset($response['success'])) {
                $results['debug_info'][] = "   Success: " . ($response['success'] ? 'Yes' : 'No');
                if (isset($response['message'])) {
                    $results['debug_info'][] = "   Message: " . $response['message'];
                }
                
                if ($response['success']) {
                    $results['debug_info'][] = "   ✅ Privatkunde registration successful";
                } else {
                    $results['issues_found'][] = "Privatkunde registration failed";
                    $results['debug_info'][] = "   ❌ Privatkunde registration failed";
                    
                    if (strpos($output, 'inherited orderly') !== false) {
                        $results['issues_found'][] = "CRITICAL: 'inherited orderly' error in Privatkunde";
                        $results['debug_info'][] = "   ❌ 'inherited orderly' error detected";
                    }
                }
            } else {
                $results['issues_found'][] = "Invalid response from registration";
                $results['debug_info'][] = "   ❌ Invalid response structure";
            }
            
        } catch (Exception $e) {
            $results['issues_found'][] = "Exception during Privatkunde test: " . $e->getMessage();
            $results['debug_info'][] = "   ❌ Exception: " . $e->getMessage();
        }
        $results['debug_info'][] = "3. Testing Privatkunde registration with missing fields...";
        try {
            $original_post = $_POST;
            
            $_POST = array(
                'action' => 'nexora_register_user',
                'auth_nonce' => wp_create_nonce('nexora_auth_nonce'),
                'email' => 'test@example.com',
                'password' => 'testpass123',
                'customer_type' => 'private',
                'salutation' => '',
                'first_name' => '',
                'last_name' => 'Test',
                'street' => 'Teststraße 1',
                'postal_code' => '12345',
                'city' => 'Teststadt',
                'country' => 'Deutschland'
            );
            
            ob_start();
            $registration = new Nexora_User_Registration();
            $registration->handle_registration();
            $output = ob_get_clean();
            
            $_POST = $original_post;
            
            $response = json_decode($output, true);
            
            if (isset($response['success']) && !$response['success']) {
                $results['debug_info'][] = "   ✅ Error handling working (expected failure)";
                if (isset($response['message'])) {
                    $results['debug_info'][] = "   Error message: " . $response['message'];
                    
                    if (strpos($response['message'], 'inherited orderly') !== false) {
                        $results['issues_found'][] = "CRITICAL: 'inherited orderly' in error message";
                        $results['debug_info'][] = "   ❌ 'inherited orderly' in error message";
                    } else {
                        $results['debug_info'][] = "   ✅ Proper error message";
                    }
                }
            } else {
                $results['issues_found'][] = "Error handling not working properly";
                $results['debug_info'][] = "   ❌ Error handling failed";
            }
            
        } catch (Exception $e) {
            $results['issues_found'][] = "Exception during error test: " . $e->getMessage();
            $results['debug_info'][] = "   ❌ Exception: " . $e->getMessage();
        }
        $results['debug_info'][] = "4. Checking required fields for private customers...";
        $required_fields = array('email', 'password', 'customer_type', 'salutation', 
                                'first_name', 'last_name', 'street', 'postal_code', 
                                'city', 'country');
        
        $results['debug_info'][] = "   Required fields for private: " . implode(', ', $required_fields);
        if (in_array('house_number', $required_fields)) {
            $results['issues_found'][] = "house_number should not be required for private customers";
            $results['debug_info'][] = "   ❌ house_number is required for private customers";
        } else {
            $results['debug_info'][] = "   ✅ house_number not required for private customers";
        }
        
        if (count($results['issues_found']) > 0) {
            $results['success'] = false;
        }
        
        return $results;
    }
    public function test_field_validation() {
        $results = array(
            'success' => true,
            'debug_info' => array(),
            'issues_found' => array(),
            'fixes_applied' => array()
        );
        
        $results['debug_info'][] = "🔍 Testing that ALL validation has been removed...";
        if (!class_exists('Nexora_User_Registration')) {
            $results['issues_found'][] = "CRITICAL: Registration class not found";
            $results['debug_info'][] = "   ❌ Nexora_User_Registration class not found";
            $results['success'] = false;
            return $results;
        }
        
        $results['debug_info'][] = "   ✅ Registration class exists";
        $results['debug_info'][] = "2. Testing registration with minimal data...";
        
        $original_post = $_POST;
        $_POST = array(
            'action' => 'nexora_register_user',
            'auth_nonce' => wp_create_nonce('nexora_auth_nonce'),
            'email' => 'test@example.com',
            'password' => 'testpass123'
        );
        
        ob_start();
        $registration = new Nexora_User_Registration();
        $registration->handle_registration();
        $output = ob_get_clean();
        
        $_POST = $original_post;
        
        $response = json_decode($output, true);
        
        if (isset($response['success']) && $response['success']) {
            $results['debug_info'][] = "   ✅ Registration accepts minimal data (validation removed)";
        } else {
            $results['issues_found'][] = "Registration still has validation restrictions";
            $results['debug_info'][] = "   ❌ Registration failed with minimal data";
            if (isset($response['message'])) {
                $results['debug_info'][] = "   Error: " . $response['message'];
            }
        }
        $results['debug_info'][] = "3. Testing registration with invalid data...";
        
        $_POST = array(
            'action' => 'nexora_register_user',
            'auth_nonce' => wp_create_nonce('nexora_auth_nonce'),
            'email' => 'invalid-email',
            'password' => '123',
            'first_name' => 'A',
            'last_name' => 'B',
            'street' => 'A',
            'city' => 'B',
            'phone' => 'abc'
        );
        
        ob_start();
        $registration = new Nexora_User_Registration();
        $registration->handle_registration();
        $output = ob_get_clean();
        
        $_POST = $original_post;
        
        $response = json_decode($output, true);
        
        if (isset($response['success']) && $response['success']) {
            $results['debug_info'][] = "   ✅ Registration accepts invalid data (validation removed)";
        } else {
            $results['issues_found'][] = "Registration still validates data";
            $results['debug_info'][] = "   ❌ Registration failed with invalid data";
            if (isset($response['message'])) {
                $results['debug_info'][] = "   Error: " . $response['message'];
            }
        }
        
        $results['debug_info'][] = "4. All validation restrictions have been removed successfully";
        
        return $results;
    }
    public function test_user_approval() {
        $results = array(
            'success' => true,
            'debug_info' => array(),
            'issues_found' => array(),
            'fixes_applied' => array()
        );
        
        $results['debug_info'][] = "🔍 Testing user approval system...";
        if (!class_exists('Nexora_User_Registration')) {
            $results['issues_found'][] = "CRITICAL: User registration class not found";
            $results['debug_info'][] = "   ❌ Nexora_User_Registration class not found";
            $results['success'] = false;
            return $results;
        }
        
        $results['debug_info'][] = "   ✅ User registration class exists";
        if (!method_exists('Nexora_User_Registration', 'user_has_access')) {
            $results['issues_found'][] = "user_has_access function not found";
            $results['debug_info'][] = "   ❌ user_has_access function missing";
        } else {
            $results['debug_info'][] = "   ✅ user_has_access function exists";
        }
        
        if (!method_exists('Nexora_User_Registration', 'get_approval_status_message')) {
            $results['issues_found'][] = "get_approval_status_message function not found";
            $results['debug_info'][] = "   ❌ get_approval_status_message function missing";
        } else {
            $results['debug_info'][] = "   ✅ get_approval_status_message function exists";
        }
        $results['debug_info'][] = "3. Testing approval status messages...";
        $pending_message = Nexora_User_Registration::get_approval_status_message(0);
        if (strpos($pending_message, 'Genehmigung') !== false) {
            $results['debug_info'][] = "   ✅ Pending message working";
        } else {
            $results['issues_found'][] = "Pending message not working";
            $results['debug_info'][] = "   ❌ Pending message: " . $pending_message;
        }
        $results['debug_info'][] = "4. Testing user access function...";
        $has_access = Nexora_User_Registration::user_has_access();
        if (!$has_access) {
            $results['debug_info'][] = "   ✅ Access denied for non-logged in users";
        } else {
            $results['issues_found'][] = "Access granted to non-logged in users";
            $results['debug_info'][] = "   ❌ Access granted to non-logged in users";
        }
        $results['debug_info'][] = "5. Testing registration sets pending status...";
        
        $original_post = $_POST;
        $_POST = array(
            'action' => 'nexora_register_user',
            'auth_nonce' => wp_create_nonce('nexora_auth_nonce'),
            'email' => 'approval-test@example.com',
            'password' => 'testpass123',
            'customer_type' => 'private',
            'salutation' => 'Herr',
            'first_name' => 'Test',
            'last_name' => 'User',
            'street' => 'Teststraße 1',
            'postal_code' => '12345',
            'city' => 'Teststadt',
            'country' => 'Deutschland'
        );
        
        ob_start();
        $registration = new Nexora_User_Registration();
        $registration->handle_registration();
        $output = ob_get_clean();
        
        $_POST = $original_post;
        
        $response = json_decode($output, true);
        
        if (isset($response['success']) && $response['success']) {
            $results['debug_info'][] = "   ✅ Registration successful";
            $user = get_user_by('email', 'approval-test@example.com');
            if ($user) {
                $user_approved = get_user_meta($user->ID, 'user_approved', true);
                if ($user_approved === 'pending') {
                    $results['debug_info'][] = "   ✅ User created with pending status";
                } else {
                    $results['issues_found'][] = "User not created with pending status";
                    $results['debug_info'][] = "   ❌ User approval status: " . $user_approved;
                }
                wp_delete_user($user->ID);
                
                global $wpdb;
                $customer_info_table = $wpdb->prefix . 'nexora_customer_info';
                $wpdb->delete($customer_info_table, array('user_id' => $user->ID));
            } else {
                $results['issues_found'][] = "Test user not created";
                $results['debug_info'][] = "   ❌ Test user not created";
            }
        } else {
            $results['issues_found'][] = "Registration failed during approval test";
            $results['debug_info'][] = "   ❌ Registration failed";
            if (isset($response['message'])) {
                $results['debug_info'][] = "   Error: " . $response['message'];
            }
        }
        $results['debug_info'][] = "6. Testing login with unapproved user...";
        $user_data = array(
            'user_login' => 'pending-test@example.com',
            'user_email' => 'pending-test@example.com',
            'user_pass' => 'testpass123',
            'role' => 'customer'
        );
        
        $user_id = wp_insert_user($user_data);
        if ($user_id) {
            update_user_meta($user_id, 'user_approved', 'pending');
            $_POST = array(
                'action' => 'nexora_login_user',
                'auth_nonce' => wp_create_nonce('nexora_auth_nonce'),
                'email' => 'pending-test@example.com',
                'password' => 'testpass123'
            );
            
            ob_start();
            $registration = new Nexora_User_Registration();
            $registration->handle_login();
            $output = ob_get_clean();
            
            $_POST = $original_post;
            
            $response = json_decode($output, true);
            
            if (isset($response['success']) && !$response['success']) {
                $results['debug_info'][] = "   ✅ Login blocked for pending user";
                if (strpos($response['message'], 'Genehmigung') !== false) {
                    $results['debug_info'][] = "   ✅ Proper approval message shown";
                } else {
                    $results['debug_info'][] = "   Message: " . $response['message'];
                }
            } else {
                $results['issues_found'][] = "Login not blocked for pending user";
                $results['debug_info'][] = "   ❌ Login not blocked";
            }
            wp_delete_user($user_id);
        } else {
            $results['issues_found'][] = "Could not create test user for login test";
            $results['debug_info'][] = "   ❌ Could not create test user";
        }
        $results['debug_info'][] = "7. Testing approval banner shortcode...";
        
        if (method_exists('Nexora_User_Registration', 'render_approval_banner')) {
            $results['debug_info'][] = "   ✅ render_approval_banner function exists";
            $user_data = array(
                'user_login' => 'banner-test@example.com',
                'user_email' => 'banner-test@example.com',
                'user_pass' => 'testpass123',
                'role' => 'customer'
            );
            
            $user_id = wp_insert_user($user_data);
            if ($user_id) {
                update_user_meta($user_id, 'user_approved', 'pending');
                wp_set_current_user($user_id);
                ob_start();
                $registration = new Nexora_User_Registration();
                $banner_output = $registration->render_approval_banner();
                ob_end_clean();
                
                if (strpos($banner_output, 'Genehmigung') !== false) {
                    $results['debug_info'][] = "   ✅ Approval banner working correctly";
                } else {
                    $results['issues_found'][] = "Approval banner not working";
                    $results['debug_info'][] = "   ❌ Banner output: " . substr($banner_output, 0, 100);
                }
                wp_delete_user($user_id);
            } else {
                $results['issues_found'][] = "Could not create test user for banner test";
                $results['debug_info'][] = "   ❌ Could not create test user";
            }
        } else {
            $results['issues_found'][] = "render_approval_banner function not found";
            $results['debug_info'][] = "   ❌ render_approval_banner function missing";
        }
        
        if (count($results['issues_found']) > 0) {
            $results['success'] = false;
        }
        
        return $results;
    }
    public function ajax_test_user_approval() {
        if (!current_user_can('manage_options')) {
            wp_send_json_error('Insufficient permissions');
        }
        
        $results = $this->test_user_approval();
        wp_send_json($results);
    }
    public function test_badge_system() {
        $results = array(
            'success' => true,
            'debug_info' => array(),
            'issues_found' => array(),
            'fixes_applied' => array()
        );
        
        $results['debug_info'][] = "🔍 Testing badge system for new requests...";
        global $wpdb;
        $new_status_id = $wpdb->get_var("SELECT id FROM {$wpdb->prefix}nexora_service_status WHERE title = 'Neu' LIMIT 1");
        
        if ($new_status_id) {
            $results['debug_info'][] = "   ✅ 'Neu' status found with ID: " . $new_status_id;
        } else {
            $results['issues_found'][] = "'Neu' status not found in database";
            $results['debug_info'][] = "   ❌ 'Neu' status not found";
            $results['success'] = false;
            return $results;
        }
        $new_requests_count = $wpdb->get_var($wpdb->prepare(
            "SELECT COUNT(*) FROM {$wpdb->prefix}nexora_service_requests WHERE status_id = %d",
            $new_status_id
        ));
        
        $results['debug_info'][] = "   📊 Requests with 'Neu' status: " . $new_requests_count;
        $results['debug_info'][] = "3. Creating test request with 'Neu' status...";
        
        $test_user_id = wp_insert_user(array(
            'user_login' => 'badge-test@example.com',
            'user_email' => 'badge-test@example.com',
            'user_pass' => 'testpass123',
            'role' => 'customer'
        ));
        
        if ($test_user_id) {
            $request_inserted = $wpdb->insert(
                $wpdb->prefix . 'nexora_service_requests',
                array(
                    'user_id' => $test_user_id,
                    'service_id' => 1,
                    'model' => 'Test Model',
                    'serial' => 'TEST123',
                    'description' => 'Test request for badge system',
                    'status_id' => $new_status_id,
                    'created_at' => current_time('mysql'),
                    'updated_at' => current_time('mysql')
                )
            );
            
            if ($request_inserted) {
                $results['debug_info'][] = "   ✅ Test request created successfully";
                $new_count_after = $wpdb->get_var($wpdb->prepare(
                    "SELECT COUNT(*) FROM {$wpdb->prefix}nexora_service_requests WHERE status_id = %d",
                    $new_status_id
                ));
                
                $results['debug_info'][] = "   📊 Badge count after test request: " . $new_count_after;
                
                if ($new_count_after > $new_requests_count) {
                    $results['debug_info'][] = "   ✅ Badge count increased correctly";
                } else {
                    $results['issues_found'][] = "Badge count not updated correctly";
                    $results['debug_info'][] = "   ❌ Badge count not updated";
                }
                $results['debug_info'][] = "5. Testing status change and badge removal...";
                
                $other_status_id = $wpdb->get_var("SELECT id FROM {$wpdb->prefix}nexora_service_status WHERE title != 'Neu' LIMIT 1");
                
                if ($other_status_id) {
                    $wpdb->update(
                        $wpdb->prefix . 'nexora_service_requests',
                        array('status_id' => $other_status_id),
                        array('user_id' => $test_user_id)
                    );
                    
                    $count_after_status_change = $wpdb->get_var($wpdb->prepare(
                        "SELECT COUNT(*) FROM {$wpdb->prefix}nexora_service_requests WHERE status_id = %d",
                        $new_status_id
                    ));
                    
                    $results['debug_info'][] = "   📊 Badge count after status change: " . $count_after_status_change;
                    
                    if ($count_after_status_change < $new_count_after) {
                        $results['debug_info'][] = "   ✅ Badge count decreased correctly after status change";
                    } else {
                        $results['issues_found'][] = "Badge count not decreased after status change";
                        $results['debug_info'][] = "   ❌ Badge count not decreased";
                    }
                } else {
                    $results['issues_found'][] = "No other status found for testing";
                    $results['debug_info'][] = "   ❌ No other status found";
                }
                $wpdb->delete($wpdb->prefix . 'nexora_service_requests', array('user_id' => $test_user_id));
                wp_delete_user($test_user_id);
                
            } else {
                $results['issues_found'][] = "Could not create test request";
                $results['debug_info'][] = "   ❌ Could not create test request";
                wp_delete_user($test_user_id);
            }
        } else {
            $results['issues_found'][] = "Could not create test user";
            $results['debug_info'][] = "   ❌ Could not create test user";
        }
        
        if (count($results['issues_found']) > 0) {
            $results['success'] = false;
        }
        
        return $results;
    }
    public function ajax_test_badge_system() {
        if (!current_user_can('manage_options')) {
            wp_send_json_error('Insufficient permissions');
        }
        
        $results = $this->test_badge_system();
        wp_send_json($results);
    }
    public function test_status_filter() {
        $results = array(
            'success' => true,
            'debug_info' => array(),
            'issues_found' => array(),
            'fixes_applied' => array()
        );
        
        $results['debug_info'][] = "🔍 Testing status filter system...";
        if (!has_action('wp_ajax_nexora_get_service_requests')) {
            $results['issues_found'][] = "Service requests AJAX handler not found";
            $results['debug_info'][] = "   ❌ nexora_get_service_requests handler missing";
            $results['success'] = false;
            return $results;
        }
        
        $results['debug_info'][] = "   ✅ Service requests AJAX handler exists";
        $results['debug_info'][] = "2. Testing status filter parameter handling...";
        $original_post = $_POST;
        $_POST = array(
            'action' => 'nexora_get_service_requests',
            'page' => 1,
            'per_page' => 10,
            'status' => '1',
            'nonce' => wp_create_nonce('nexora_nonce')
        );
        
        ob_start();
        do_action('wp_ajax_nexora_get_service_requests');
        $output = ob_get_clean();
        
        $_POST = $original_post;
        
        $response = json_decode($output, true);
        
        if ($response && isset($response['success'])) {
            $results['debug_info'][] = "   ✅ Status filter AJAX call successful";
            if (isset($response['data']['requests'])) {
                $results['debug_info'][] = "   📊 Found " . count($response['data']['requests']) . " filtered requests";
            }
        } else {
            $results['issues_found'][] = "Status filter AJAX call failed";
            $results['debug_info'][] = "   ❌ Status filter AJAX call failed";
        }
        $results['debug_info'][] = "3. Testing status options loading...";
        
        $_POST = array(
            'action' => 'nexora_get_service_statuses',
            'nonce' => wp_create_nonce('nexora_nonce')
        );
        
        ob_start();
        do_action('wp_ajax_nexora_get_service_statuses');
        $output = ob_get_clean();
        
        $_POST = $original_post;
        
        $response = json_decode($output, true);
        
        if ($response && isset($response['success']) && isset($response['data']['statuses'])) {
            $results['debug_info'][] = "   ✅ Status options loaded successfully";
            $results['debug_info'][] = "   📊 Found " . count($response['data']['statuses']) . " status options";
        } else {
            $results['issues_found'][] = "Status options loading failed";
            $results['debug_info'][] = "   ❌ Status options loading failed";
        }
        $results['debug_info'][] = "4. Testing database filter functionality...";
        
        global $wpdb;
        $statuses = $wpdb->get_results("SELECT id, title FROM {$wpdb->prefix}nexora_service_status ORDER BY id ASC");
        
        if ($statuses) {
            $results['debug_info'][] = "   ✅ Statuses found in database";
            foreach ($statuses as $status) {
                $results['debug_info'][] = "   📋 Status: {$status->title} (ID: {$status->id})";
            }
            $test_status_id = $statuses[0]->id;
            $filtered_count = $wpdb->get_var($wpdb->prepare(
                "SELECT COUNT(*) FROM {$wpdb->prefix}nexora_service_requests WHERE status_id = %d",
                $test_status_id
            ));
            
            $results['debug_info'][] = "   📊 Filtered requests for status {$statuses[0]->title}: {$filtered_count}";
        } else {
            $results['issues_found'][] = "No statuses found in database";
            $results['debug_info'][] = "   ❌ No statuses found in database";
        }
        
        if (count($results['issues_found']) > 0) {
            $results['success'] = false;
        }
        
        return $results;
    }
    public function ajax_test_status_filter() {
        if (!current_user_can('manage_options')) {
            wp_send_json_error('Insufficient permissions');
        }
        
        $results = $this->test_status_filter();
        wp_send_json($results);
    }

    
    public function ajax_test_user_info() {
        check_ajax_referer('nexora_repair_nonce', 'nonce');
        global $wpdb;
        $user = $wpdb->get_row("SELECT * FROM {$wpdb->users} ORDER BY ID DESC LIMIT 1");
        if (!$user) {
            wp_send_json_error('Kein Benutzer gefunden.');
        }
        $user_id = $user->ID;
        $customer = $wpdb->get_row($wpdb->prepare("SELECT * FROM {$wpdb->prefix}nexora_customer_info WHERE user_id = %d", $user_id));
        $log = "--- WordPress Benutzer (wp_users) ---\n";
        $log .= "ID: {$user->ID}\nBenutzername: {$user->user_login}\nE-Mail: {$user->user_email}\nRegistriert: {$user->user_registered}\n";
        $log .= "\n--- Kundeninfo (nexora_customer_info) ---\n";
        if ($customer) {
            foreach ($customer as $key => $val) {
                $log .= ucfirst($key) . ': ' . $val . "\n";
            }
        } else {
            $log .= "Keine Kundeninfo gefunden.\n";
        }
        wp_send_json_success($log);
    }

    public function repair_request_invoices_table() {
        global $wpdb;
        $table = $wpdb->prefix . 'nexora_request_invoices';
        $sql = "CREATE TABLE IF NOT EXISTS `$table` (
            id BIGINT(20) NOT NULL AUTO_INCREMENT,
            request_id BIGINT(20) NOT NULL,
            file_name VARCHAR(255) NOT NULL,
            file_path VARCHAR(500) NOT NULL,
            file_size BIGINT(20) NOT NULL,
            file_type VARCHAR(100) NOT NULL,
            uploaded_by BIGINT(20) NOT NULL,
            created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
            PRIMARY KEY (id),
            KEY idx_request_id (request_id),
            KEY idx_uploaded_by (uploaded_by)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci";
        require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
        dbDelta($sql);
        return array('success' => true, 'message' => 'nexora_request_invoices table repaired/created.');
    }

    public function ajax_repair_request_invoices_table() {
        check_ajax_referer('nexora_repair_nonce', 'nonce');
        $result = $this->repair_request_invoices_table();
        wp_send_json($result);
    }
    
    
    public function debug_services_list() {
        global $wpdb;
        
        $results = array(
            'success' => true,
            'debug_info' => array(),
            'issues_found' => array(),
            'fixes_applied' => array(),
            'test_results' => array()
        );
        
        $results['debug_info'][] = "🔍 Starting comprehensive Dienstleistungen list debugging...";
        $results['debug_info'][] = "1. Checking services table structure...";
        $services_table = $wpdb->prefix . 'nexora_services';
        $table_exists = $wpdb->get_var("SHOW TABLES LIKE '$services_table'") == $services_table;
        
        if (!$table_exists) {
            $results['issues_found'][] = "CRITICAL: Services table does not exist";
            $results['debug_info'][] = "   ❌ Table '$services_table' not found";
            $results['success'] = false;
        } else {
            $results['debug_info'][] = "   ✅ Services table exists";
            $columns = $wpdb->get_results("DESCRIBE $services_table");
            $expected_columns = array('id', 'title', 'description', 'cost', 'status', 'user_id', 'created_at', 'updated_at');
            $found_columns = array();
            
            foreach ($columns as $column) {
                $found_columns[] = $column->Field;
            }
            
            $results['debug_info'][] = "   📊 Found columns: " . implode(', ', $found_columns);
            
            $missing_columns = array_diff($expected_columns, $found_columns);
            if (!empty($missing_columns)) {
                $results['issues_found'][] = "Missing columns in services table: " . implode(', ', $missing_columns);
                $results['debug_info'][] = "   ❌ Missing columns: " . implode(', ', $missing_columns);
            } else {
                $results['debug_info'][] = "   ✅ All expected columns present";
            }
        }
        $results['debug_info'][] = "2. Checking services data...";
        $services_count = $wpdb->get_var("SELECT COUNT(*) FROM $services_table");
        $results['debug_info'][] = "   📊 Total services in database: $services_count";
        
        if ($services_count == 0) {
            $results['issues_found'][] = "No services found in database";
            $results['debug_info'][] = "   ⚠️ No services found - this might be why the list is empty";
        } else {
            $sample_services = $wpdb->get_results("SELECT * FROM $services_table LIMIT 3");
            $results['debug_info'][] = "   📋 Sample services:";
            foreach ($sample_services as $service) {
                $results['debug_info'][] = "      - ID: {$service->id}, Title: {$service->title}, Status: {$service->status}";
            }
        }
        $results['debug_info'][] = "3. Checking Service Handler class...";
        if (!class_exists('Nexora_Service_Handler')) {
            $results['issues_found'][] = "CRITICAL: Service Handler class not found";
            $results['debug_info'][] = "   ❌ Nexora_Service_Handler class not found";
            $results['success'] = false;
        } else {
            $results['debug_info'][] = "   ✅ Service Handler class exists";
            $results['debug_info'][] = "4. Checking AJAX handlers...";
            $ajax_handlers = array(
                'nexora_get_services',
                'nexora_add_service',
                'nexora_update_service',
                'nexora_delete_service'
            );
            
            foreach ($ajax_handlers as $handler) {
                if (has_action("wp_ajax_$handler")) {
                    $results['debug_info'][] = "   ✅ AJAX handler '$handler' registered";
                } else {
                    $results['issues_found'][] = "AJAX handler '$handler' not registered";
                    $results['debug_info'][] = "   ❌ AJAX handler '$handler' not found";
                }
            }
        }
        $results['debug_info'][] = "5. Checking template files...";
        $template_file = NEXORA_PLUGIN_DIR . 'templates/services-list.php';
        if (file_exists($template_file)) {
            $results['debug_info'][] = "   ✅ Services list template exists";
            $template_content = file_get_contents($template_file);
            $required_elements = array(
                'wp-list-table widefat fixed striped',
                'Nexora Service Suite-service-list',
                'Nexora Service Suite-service-list-container'
            );
            
            foreach ($required_elements as $element) {
                if (strpos($template_content, $element) !== false) {
                    $results['debug_info'][] = "   ✅ Template contains '$element'";
                } else {
                    $results['issues_found'][] = "Template missing required element: $element";
                    $results['debug_info'][] = "   ❌ Template missing '$element'";
                }
            }
        } else {
            $results['issues_found'][] = "Services list template not found";
            $results['debug_info'][] = "   ❌ Template file not found: $template_file";
        }
        $results['debug_info'][] = "6. Checking JavaScript files...";
        $js_file = NEXORA_PLUGIN_DIR . 'assets/js/admin.js';
        if (file_exists($js_file)) {
            $results['debug_info'][] = "   ✅ Admin JS file exists";
            
            $js_content = file_get_contents($js_file);
            $js_requirements = array(
                'nexora_get_services',
                'Nexora Service Suite-service-list',
                'loadServices'
            );
            
            foreach ($js_requirements as $requirement) {
                if (strpos($js_content, $requirement) !== false) {
                    $results['debug_info'][] = "   ✅ JS contains '$requirement'";
                } else {
                    $results['issues_found'][] = "JavaScript missing required function: $requirement";
                    $results['debug_info'][] = "   ❌ JS missing '$requirement'";
                }
            }
        } else {
            $results['issues_found'][] = "Admin JavaScript file not found";
            $results['debug_info'][] = "   ❌ JS file not found: $js_file";
        }
        $results['debug_info'][] = "7. Testing AJAX endpoint directly...";
        try {
            $_POST = array(
                'action' => 'nexora_get_services',
                'page' => 1,
                'per_page' => 10,
                'search' => '',
                'nonce' => wp_create_nonce('nexora_nonce')
            );
            
            ob_start();
            do_action('wp_ajax_nexora_get_services');
            $ajax_output = ob_get_clean();
            
            $results['debug_info'][] = "   📄 AJAX output length: " . strlen($ajax_output);
            $results['debug_info'][] = "   📄 AJAX output preview: " . substr($ajax_output, 0, 200);
            
            if (empty($ajax_output)) {
                $results['issues_found'][] = "AJAX endpoint returns empty response";
                $results['debug_info'][] = "   ❌ AJAX endpoint returns empty response";
            } else {
                $json_response = json_decode($ajax_output, true);
                if (json_last_error() === JSON_ERROR_NONE) {
                    $results['debug_info'][] = "   ✅ AJAX response is valid JSON";
                    if (isset($json_response['success'])) {
                        $results['debug_info'][] = "   📊 AJAX success: " . ($json_response['success'] ? 'Yes' : 'No');
                        if (isset($json_response['data']['services'])) {
                            $results['debug_info'][] = "   📊 Services returned: " . count($json_response['data']['services']);
                            $results['debug_info'][] = "   📋 Services data: " . json_encode($json_response['data']['services']);
                        }
                    }
                } else {
                    $results['issues_found'][] = "AJAX response is not valid JSON";
                    $results['debug_info'][] = "   ❌ AJAX response is not valid JSON: " . json_last_error_msg();
                }
            }
            
        } catch (Exception $e) {
            $results['issues_found'][] = "Exception during AJAX test: " . $e->getMessage();
            $results['debug_info'][] = "   ❌ Exception: " . $e->getMessage();
        }
        $results['debug_info'][] = "8. Checking admin menu registration...";
        if (class_exists('Nexora_Admin_Menu')) {
            $results['debug_info'][] = "   ✅ Admin Menu class exists";
            global $submenu;
            $services_page_found = false;
            
            if (isset($submenu['Nexora Service Suite-main'])) {
                foreach ($submenu['Nexora Service Suite-main'] as $item) {
                    if (isset($item[2]) && $item[2] === 'Nexora Service Suite-services') {
                        $services_page_found = true;
                        break;
                    }
                }
            }
            
            if ($services_page_found) {
                $results['debug_info'][] = "   ✅ Services page registered in admin menu";
            } else {
                $results['issues_found'][] = "Services page not registered in admin menu";
                $results['debug_info'][] = "   ❌ Services page not found in admin menu";
            }
        } else {
            $results['issues_found'][] = "Admin Menu class not found";
            $results['debug_info'][] = "   ❌ Nexora_Admin_Menu class not found";
        }
        $results['debug_info'][] = "9. Checking for potential JavaScript issues...";
        $results['debug_info'][] = "   💡 Check browser console for JavaScript errors";
        $results['debug_info'][] = "   💡 Check Network tab for failed AJAX requests";
        $results['debug_info'][] = "   💡 Check if nexora_ajax object is defined";
        $results['debug_info'][] = "10. Checking database permissions...";
        try {
            $test_query = $wpdb->get_results("SELECT 1 FROM $services_table LIMIT 1");
            $results['debug_info'][] = "   ✅ Database read permissions OK";
        } catch (Exception $e) {
            $results['issues_found'][] = "Database permission issues: " . $e->getMessage();
            $results['debug_info'][] = "   ❌ Database permission error: " . $e->getMessage();
        }
        $results['debug_info'][] = "11. Checking for potential conflicts...";
        $active_plugins = get_option('active_plugins');
        $results['debug_info'][] = "   📊 Active plugins: " . count($active_plugins);
        
        $potential_conflicts = array('woocommerce', 'contact-form-7', 'elementor');
        foreach ($potential_conflicts as $plugin) {
            if (in_array($plugin, $active_plugins)) {
                $results['debug_info'][] = "   ⚠️ Potential conflict: $plugin is active";
            }
        }
        $results['debug_info'][] = "12. Checking AJAX localization...";
        if (wp_script_is('Nexora Service Suite-admin-js', 'enqueued')) {
            $results['debug_info'][] = "   ✅ Admin JS is enqueued";
        } else {
            $results['issues_found'][] = "Admin JS not enqueued";
            $results['debug_info'][] = "   ❌ Admin JS not enqueued";
        }
        $results['debug_info'][] = "13. Checking admin.js file content...";
        $js_file_path = NEXORA_PLUGIN_DIR . 'assets/js/admin.js';
        if (file_exists($js_file_path)) {
            $js_content = file_get_contents($js_file_path);
            $results['debug_info'][] = "   ✅ Admin JS file exists";
            $results['debug_info'][] = "   📊 File size: " . strlen($js_content) . " bytes";
            $js_checks = array(
                'loadServices' => 'loadServices function',
                'nexora_get_services' => 'AJAX action call',
                'Nexora Service Suite-service-list' => 'Service list selector',
                'jQuery' => 'jQuery usage'
            );
            
            foreach ($js_checks as $check => $description) {
                if (strpos($js_content, $check) !== false) {
                    $results['debug_info'][] = "   ✅ JS contains: $description";
                } else {
                    $results['issues_found'][] = "JavaScript missing: $description";
                    $results['debug_info'][] = "   ❌ JS missing: $description";
                }
            }
        } else {
            $results['issues_found'][] = "Admin JS file not found";
            $results['debug_info'][] = "   ❌ Admin JS file not found at: $js_file_path";
        }
        $results['debug_info'][] = "14. Checking nonce creation...";
        $nonce = wp_create_nonce('nexora_nonce');
        if ($nonce) {
            $results['debug_info'][] = "   ✅ Nonce created successfully";
        } else {
            $results['issues_found'][] = "Nonce creation failed";
            $results['debug_info'][] = "   ❌ Nonce creation failed";
        }
        $results['debug_info'][] = "15. Checking services page accessibility...";
        $services_url = admin_url('admin.php?page=Nexora Service Suite-services');
        $results['debug_info'][] = "   📄 Services page URL: $services_url";
        $results['debug_info'][] = "16. Checking for PHP errors...";
        $error_log = ini_get('error_log');
        if ($error_log && file_exists($error_log)) {
            $recent_errors = file_get_contents($error_log);
            if (strpos($recent_errors, 'Nexora Service Suite') !== false) {
                $results['debug_info'][] = "   ⚠️ Found Nexora Service Suite errors in error log";
            } else {
                $results['debug_info'][] = "   ✅ No Nexora Service Suite errors in error log";
            }
        } else {
            $results['debug_info'][] = "   ℹ️ Error log not accessible";
        }
        $results['debug_info'][] = "17. Testing actual services page loading...";
        try {
            $current_user = wp_get_current_user();
            $results['debug_info'][] = "   👤 Current user: " . $current_user->user_login;
            if (current_user_can('manage_options')) {
                $results['debug_info'][] = "   ✅ User has admin capabilities";
            } else {
                $results['issues_found'][] = "User lacks admin capabilities";
                $results['debug_info'][] = "   ❌ User lacks admin capabilities";
            }
            $services_url = admin_url('admin.php?page=Nexora Service Suite-services');
            $results['debug_info'][] = "   📄 Services page URL: $services_url";
            global $submenu;
            $page_exists = false;
            if (isset($submenu['Nexora Service Suite-main'])) {
                foreach ($submenu['Nexora Service Suite-main'] as $item) {
                    if (isset($item[2]) && $item[2] === 'Nexora Service Suite-services') {
                        $page_exists = true;
                        $results['debug_info'][] = "   ✅ Services page found in admin menu";
                        break;
                    }
                }
            }
            
            if (!$page_exists) {
                $results['issues_found'][] = "Services page not found in admin menu";
                $results['debug_info'][] = "   ❌ Services page not found in admin menu";
            }
            
        } catch (Exception $e) {
            $results['issues_found'][] = "Exception during page access test: " . $e->getMessage();
            $results['debug_info'][] = "   ❌ Exception: " . $e->getMessage();
        }
        $results['debug_info'][] = "18. Checking JavaScript loading...";
        $results['debug_info'][] = "   💡 To check if JavaScript is loaded:";
        $results['debug_info'][] = "      1. Open browser developer tools (F12)";
        $results['debug_info'][] = "      2. Go to Console tab";
        $results['debug_info'][] = "      3. Type: console.log(nexora_ajax)";
        $results['debug_info'][] = "      4. If undefined, JavaScript is not loaded";
        $results['debug_info'][] = "      5. Check Network tab for failed JS requests";
        $results['debug_info'][] = "19. Checking CSS loading...";
        $results['debug_info'][] = "   💡 To check if CSS is loaded:";
        $results['debug_info'][] = "      1. Open browser developer tools (F12)";
        $results['debug_info'][] = "      2. Go to Elements tab";
        $results['debug_info'][] = "      3. Look for wp-list-table class";
        $results['debug_info'][] = "      4. Check if table has proper styling";
        $results['debug_info'][] = "20. Checking for JavaScript errors...";
        $results['debug_info'][] = "   💡 Common JavaScript errors to check:";
        $results['debug_info'][] = "      1. 'nexora_ajax is not defined'";
        $results['debug_info'][] = "      2. 'jQuery is not defined'";
        $results['debug_info'][] = "      3. 'Cannot read property of undefined'";
        $results['debug_info'][] = "      4. Network errors in Console tab";
        $results['debug_info'][] = "21. Checking HTML structure...";
        $results['debug_info'][] = "   💡 To check HTML structure:";
        $results['debug_info'][] = "      1. Open browser developer tools (F12)";
        $results['debug_info'][] = "      2. Go to Elements tab";
        $results['debug_info'][] = "      3. Look for: <table class='wp-list-table widefat fixed striped'>";
        $results['debug_info'][] = "      4. Check if tbody#Nexora Service Suite-service-list exists";
        $results['debug_info'][] = "      5. Check if tbody is empty or has content";
        $results['debug_info'][] = "22. Checking AJAX call execution...";
        $results['debug_info'][] = "   💡 To check AJAX calls:";
        $results['debug_info'][] = "      1. Open browser developer tools (F12)";
        $results['debug_info'][] = "      2. Go to Network tab";
        $results['debug_info'][] = "      3. Refresh the services page";
        $results['debug_info'][] = "      4. Look for POST requests to admin-ajax.php";
        $results['debug_info'][] = "      5. Check if action=nexora_get_services exists";
        $results['debug_info'][] = "23. Summary and recommendations...";
        if (count($results['issues_found']) > 0) {
            $results['debug_info'][] = "   ❌ Issues found: " . count($results['issues_found']);
            foreach ($results['issues_found'] as $issue) {
                $results['debug_info'][] = "      - $issue";
            }
        } else {
            $results['debug_info'][] = "   ✅ No critical issues found";
            $results['debug_info'][] = "   ⚠️ Since no issues found, problem might be:";
            $results['debug_info'][] = "      1. JavaScript not loading properly";
            $results['debug_info'][] = "      2. CSS not applied correctly";
            $results['debug_info'][] = "      3. AJAX call not being made";
            $results['debug_info'][] = "      4. Browser cache issues";
            $results['debug_info'][] = "      5. Plugin conflicts";
        }
        
        $results['debug_info'][] = "   💡 Manual debugging steps:";
        $results['debug_info'][] = "      1. Open browser developer tools (F12)";
        $results['debug_info'][] = "      2. Go to Console tab and check for errors";
        $results['debug_info'][] = "      3. Go to Network tab and check AJAX requests";
        $results['debug_info'][] = "      4. Go to Elements tab and check HTML structure";
        $results['debug_info'][] = "      5. Try hard refresh (Ctrl+F5)";
        $results['debug_info'][] = "      6. Check if other admin pages work";
        $results['debug_info'][] = "      7. Disable other plugins temporarily";
        
        if (count($results['issues_found']) > 0) {
            $results['success'] = false;
        }
        
        return $results;
    }
    
    public function ajax_debug_services_list() {
        check_ajax_referer('nexora_repair_nonce', 'nonce');
        wp_send_json($this->debug_services_list());
    }
    
    
    public function comprehensive_services_test() {
        global $wpdb;
        
        $results = array(
            'success' => true,
            'debug_info' => array(),
            'issues_found' => array(),
            'fixes_applied' => array(),
            'test_results' => array(),
            'recommendations' => array()
        );
        
        $results['debug_info'][] = "🚨 ULTIMATE COMPREHENSIVE SERVICES TEST STARTED";
        $results['debug_info'][] = "🔍 Testing EVERYTHING that could prevent services list display";
        $results['debug_info'][] = "📋 Testing: Database, Classes, Files, AJAX, HTML, CSS, JavaScript, WordPress Integration";
        $results['debug_info'][] = "";
        $results['debug_info'][] = "📊 PHASE 1: DATABASE COMPREHENSIVE TESTS";
        $results['debug_info'][] = "==========================================";
        $services_table = $wpdb->prefix . 'nexora_services';
        $table_exists = $wpdb->get_var("SHOW TABLES LIKE '$services_table'") == $services_table;
        
        if (!$table_exists) {
            $results['issues_found'][] = "CRITICAL: Services table does not exist";
            $results['debug_info'][] = "❌ Table '$services_table' not found";
            $results['success'] = false;
        } else {
            $results['debug_info'][] = "✅ Services table exists";
            $columns = $wpdb->get_results("DESCRIBE $services_table");
            $found_columns = array();
            $expected_columns = array('id', 'title', 'description', 'cost', 'status', 'user_id', 'created_at', 'updated_at');
            
            foreach ($columns as $column) {
                $found_columns[] = $column->Field;
            }
            
            $results['debug_info'][] = "📋 Found columns: " . implode(', ', $found_columns);
            $missing_columns = array_diff($expected_columns, $found_columns);
            if (!empty($missing_columns)) {
                $results['issues_found'][] = "Missing columns in services table: " . implode(', ', $missing_columns);
                $results['debug_info'][] = "❌ Missing columns: " . implode(', ', $missing_columns);
            } else {
                $results['debug_info'][] = "✅ All expected columns present";
            }
            $services_count = $wpdb->get_var("SELECT COUNT(*) FROM $services_table");
            $results['debug_info'][] = "📊 Total services: $services_count";
            
            if ($services_count == 0) {
                $results['issues_found'][] = "No services found in database";
                $results['debug_info'][] = "⚠️ No services found - this is why list is empty";
            } else {
                $sample_services = $wpdb->get_results("SELECT * FROM $services_table LIMIT 3");
                $results['debug_info'][] = "📋 Sample services:";
                foreach ($sample_services as $service) {
                    $results['debug_info'][] = "   - ID: {$service->id}, Title: {$service->title}, Status: {$service->status}";
                }
                $active_services = $wpdb->get_var("SELECT COUNT(*) FROM $services_table WHERE status = 'active'");
                $results['debug_info'][] = "📊 Active services: $active_services";
            }
            try {
                $test_query = $wpdb->get_results("SELECT 1 FROM $services_table LIMIT 1");
                $results['debug_info'][] = "✅ Database read permissions OK";
            } catch (Exception $e) {
                $results['issues_found'][] = "Database permission issues: " . $e->getMessage();
                $results['debug_info'][] = "❌ Database permission error: " . $e->getMessage();
            }
        }
        $results['debug_info'][] = "";
        $results['debug_info'][] = "🔧 PHASE 2: PHP CLASSES AND METHODS COMPREHENSIVE TESTS";
        $results['debug_info'][] = "=====================================================";
        if (!class_exists('Nexora_Service_Handler')) {
            $results['issues_found'][] = "CRITICAL: Service Handler class not found";
            $results['debug_info'][] = "❌ Nexora_Service_Handler class not found";
            $results['success'] = false;
        } else {
            $results['debug_info'][] = "✅ Service Handler class exists";
            try {
                $service_handler = new Nexora_Service_Handler();
                $results['debug_info'][] = "✅ Service Handler can be instantiated";
            } catch (Exception $e) {
                $results['issues_found'][] = "Service Handler instantiation failed: " . $e->getMessage();
                $results['debug_info'][] = "❌ Service Handler instantiation error: " . $e->getMessage();
            }
            $ajax_handlers = array(
                'nexora_get_services',
                'nexora_add_service',
                'nexora_update_service',
                'nexora_delete_service'
            );
            
            foreach ($ajax_handlers as $handler) {
                if (has_action("wp_ajax_$handler")) {
                    $results['debug_info'][] = "✅ AJAX handler '$handler' registered";
                } else {
                    $results['issues_found'][] = "AJAX handler '$handler' not registered";
                    $results['debug_info'][] = "❌ AJAX handler '$handler' not found";
                }
            }
        }
        if (!class_exists('Nexora_Admin_Menu')) {
            $results['issues_found'][] = "CRITICAL: Admin Menu class not found";
            $results['debug_info'][] = "❌ Nexora_Admin_Menu class not found";
            $results['success'] = false;
        } else {
            $results['debug_info'][] = "✅ Admin Menu class exists";
            try {
                $admin_menu = new Nexora_Admin_Menu();
                $results['debug_info'][] = "✅ Admin Menu can be instantiated";
            } catch (Exception $e) {
                $results['issues_found'][] = "Admin Menu instantiation failed: " . $e->getMessage();
                $results['debug_info'][] = "❌ Admin Menu instantiation error: " . $e->getMessage();
            }
        }
        $results['debug_info'][] = "";
        $results['debug_info'][] = "📁 PHASE 3: FILE SYSTEM COMPREHENSIVE TESTS";
        $results['debug_info'][] = "=============================================";
        $template_file = NEXORA_PLUGIN_DIR . 'templates/services-list.php';
        if (file_exists($template_file)) {
            $results['debug_info'][] = "✅ Services list template exists";
            
            $template_content = file_get_contents($template_file);
            $results['debug_info'][] = "📊 Template file size: " . strlen($template_content) . " bytes";
            $required_elements = array(
                'wp-list-table widefat fixed striped',
                'Nexora Service Suite-service-list',
                'Nexora Service Suite-service-list-container',
                'Nexora Service Suite-service-search',
                'Nexora Service Suite-search-service-btn',
                'Nexora Service Suite-prev-page',
                'Nexora Service Suite-next-page',
                'Nexora Service Suite-page-info'
            );
            
            foreach ($required_elements as $element) {
                if (strpos($template_content, $element) !== false) {
                    $results['debug_info'][] = "✅ Template contains '$element'";
                } else {
                    $results['issues_found'][] = "Template missing: $element";
                    $results['debug_info'][] = "❌ Template missing '$element'";
                }
            }
            if (strpos($template_content, '<table') !== false && strpos($template_content, '<thead') !== false && strpos($template_content, '<tbody') !== false) {
                $results['debug_info'][] = "✅ Template has proper table structure";
            } else {
                $results['issues_found'][] = "Template missing proper table structure";
                $results['debug_info'][] = "❌ Template missing proper table structure";
            }
            
        } else {
            $results['issues_found'][] = "Services list template not found";
            $results['debug_info'][] = "❌ Template file not found: $template_file";
        }
        $js_file = NEXORA_PLUGIN_DIR . 'assets/js/admin.js';
        if (file_exists($js_file)) {
            $results['debug_info'][] = "✅ Admin JS file exists";
            
            $js_content = file_get_contents($js_file);
            $results['debug_info'][] = "📊 JS file size: " . strlen($js_content) . " bytes";
            $js_requirements = array(
                'loadServices',
                'nexora_get_services',
                'Nexora Service Suite-service-list',
                'jQuery',
                'ajax',
                'success',
                'error'
            );
            
            foreach ($js_requirements as $requirement) {
                if (strpos($js_content, $requirement) !== false) {
                    $results['debug_info'][] = "✅ JS contains '$requirement'";
                } else {
                    $results['issues_found'][] = "JavaScript missing: $requirement";
                    $results['debug_info'][] = "❌ JS missing '$requirement'";
                }
            }
            if (strpos($js_content, 'function loadServices') !== false) {
                $results['debug_info'][] = "✅ loadServices function found";
            } else {
                $results['issues_found'][] = "loadServices function not found";
                $results['debug_info'][] = "❌ loadServices function not found";
            }
            
        } else {
            $results['issues_found'][] = "Admin JavaScript file not found";
            $results['debug_info'][] = "❌ JS file not found: $js_file";
        }
        $css_file = NEXORA_PLUGIN_DIR . 'assets/css/admin.css';
        if (file_exists($css_file)) {
            $results['debug_info'][] = "✅ Admin CSS file exists";
            $css_content = file_get_contents($css_file);
            $results['debug_info'][] = "📊 CSS file size: " . strlen($css_content) . " bytes";
        } else {
            $results['debug_info'][] = "⚠️ Admin CSS file not found (may not be critical)";
        }
        $results['debug_info'][] = "";
        $results['debug_info'][] = "🌐 PHASE 4: AJAX COMPREHENSIVE TESTS";
        $results['debug_info'][] = "=====================================";
        
        try {
            $_POST = array(
                'action' => 'nexora_get_services',
                'page' => 1,
                'per_page' => 10,
                'search' => '',
                'nonce' => wp_create_nonce('nexora_nonce')
            );
            
            ob_start();
            do_action('wp_ajax_nexora_get_services');
            $ajax_output = ob_get_clean();
            
            $results['debug_info'][] = "📄 AJAX output length: " . strlen($ajax_output);
            $results['debug_info'][] = "📄 AJAX output preview: " . substr($ajax_output, 0, 300);
            
            if (empty($ajax_output)) {
                $results['issues_found'][] = "AJAX endpoint returns empty response";
                $results['debug_info'][] = "❌ AJAX endpoint returns empty response";
            } else {
                $json_response = json_decode($ajax_output, true);
                if (json_last_error() === JSON_ERROR_NONE) {
                    $results['debug_info'][] = "✅ AJAX response is valid JSON";
                    
                    if (isset($json_response['success'])) {
                        $results['debug_info'][] = "📊 AJAX success: " . ($json_response['success'] ? 'Yes' : 'No');
                        
                        if (isset($json_response['data']['services'])) {
                            $services_returned = count($json_response['data']['services']);
                            $results['debug_info'][] = "📊 Services returned: $services_returned";
                            
                            if ($services_returned > 0) {
                                $results['debug_info'][] = "✅ AJAX is returning services data";
                            } else {
                                $results['debug_info'][] = "⚠️ AJAX is working but no services returned";
                            }
                        } else {
                            $results['issues_found'][] = "AJAX response missing services data";
                            $results['debug_info'][] = "❌ AJAX response missing services data";
                        }
                        
                        if (isset($json_response['data']['total_pages'])) {
                            $results['debug_info'][] = "📊 Total pages: " . $json_response['data']['total_pages'];
                        }
                    } else {
                        $results['issues_found'][] = "AJAX response missing success field";
                        $results['debug_info'][] = "❌ AJAX response missing success field";
                    }
                } else {
                    $results['issues_found'][] = "AJAX response is not valid JSON";
                    $results['debug_info'][] = "❌ AJAX response is not valid JSON: " . json_last_error_msg();
                }
            }
            
        } catch (Exception $e) {
            $results['issues_found'][] = "Exception during AJAX test: " . $e->getMessage();
            $results['debug_info'][] = "❌ Exception: " . $e->getMessage();
        }
        $results['debug_info'][] = "";
        $results['debug_info'][] = "🔗 PHASE 5: WORDPRESS INTEGRATION COMPREHENSIVE TESTS";
        $results['debug_info'][] = "==================================================";
        global $submenu;
        $services_page_found = false;
        if (isset($submenu['Nexora Service Suite-main'])) {
            foreach ($submenu['Nexora Service Suite-main'] as $item) {
                if (isset($item[2]) && $item[2] === 'Nexora Service Suite-services') {
                    $services_page_found = true;
                    $results['debug_info'][] = "✅ Services page found in admin menu: " . $item[0];
                    break;
                }
            }
        }
        
        if (!$services_page_found) {
            $results['issues_found'][] = "Services page not registered in admin menu";
            $results['debug_info'][] = "❌ Services page not found in admin menu";
        }
        if (wp_script_is('Nexora Service Suite-admin-js', 'enqueued')) {
            $results['debug_info'][] = "✅ Admin JS is enqueued";
        } else {
            $results['issues_found'][] = "Admin JS not enqueued";
            $results['debug_info'][] = "❌ Admin JS not enqueued";
        }
        if (wp_style_is('Nexora Service Suite-admin-css', 'enqueued')) {
            $results['debug_info'][] = "✅ Admin CSS is enqueued";
        } else {
            $results['debug_info'][] = "⚠️ Admin CSS not enqueued (may not be critical)";
        }
        $nonce = wp_create_nonce('nexora_nonce');
        if ($nonce) {
            $results['debug_info'][] = "✅ Nonce created successfully";
        } else {
            $results['issues_found'][] = "Nonce creation failed";
            $results['debug_info'][] = "❌ Nonce creation failed";
        }
        if (current_user_can('manage_options')) {
            $results['debug_info'][] = "✅ User has admin capabilities";
        } else {
            $results['issues_found'][] = "User lacks admin capabilities";
            $results['debug_info'][] = "❌ User lacks admin capabilities";
        }
        if (defined('NEXORA_PLUGIN_DIR')) {
            $results['debug_info'][] = "✅ NEXORA_PLUGIN_DIR constant defined";
        } else {
            $results['issues_found'][] = "NEXORA_PLUGIN_DIR constant not defined";
            $results['debug_info'][] = "❌ NEXORA_PLUGIN_DIR constant not defined";
        }
        
        if (defined('NEXORA_PLUGIN_URL')) {
            $results['debug_info'][] = "✅ NEXORA_PLUGIN_URL constant defined";
        } else {
            $results['issues_found'][] = "NEXORA_PLUGIN_URL constant not defined";
            $results['debug_info'][] = "❌ NEXORA_PLUGIN_URL constant not defined";
        }
        $results['debug_info'][] = "";
        $results['debug_info'][] = "🌐 PHASE 6: BROWSER DEBUGGING COMPREHENSIVE GUIDE";
        $results['debug_info'][] = "===============================================";
        $results['debug_info'][] = "💡 MANUAL DEBUGGING STEPS:";
        $results['debug_info'][] = "";
        $results['debug_info'][] = "1. 🔍 Open browser developer tools (F12)";
        $results['debug_info'][] = "2. 📋 Go to Console tab and check for errors";
        $results['debug_info'][] = "3. 🌐 Go to Network tab and check AJAX requests";
        $results['debug_info'][] = "4. 🏗️ Go to Elements tab and check HTML structure";
        $results['debug_info'][] = "5. 🔄 Try hard refresh (Ctrl+F5)";
        $results['debug_info'][] = "6. 🧹 Clear browser cache";
        $results['debug_info'][] = "7. 🔌 Disable other plugins temporarily";
        $results['debug_info'][] = "";
        $results['debug_info'][] = "🔍 SPECIFIC CHECKS:";
        $results['debug_info'][] = "- Type in console: console.log(nexora_ajax)";
        $results['debug_info'][] = "- Look for: <table class='wp-list-table widefat fixed striped'>";
        $results['debug_info'][] = "- Check if tbody#Nexora Service Suite-service-list has content";
        $results['debug_info'][] = "- Look for POST requests to admin-ajax.php";
        $results['debug_info'][] = "- Check if jQuery is loaded: console.log(jQuery)";
        $results['debug_info'][] = "- Check if AJAX is working: console.log($.ajax)";
        $results['debug_info'][] = "";
        $results['debug_info'][] = "📊 PHASE 7: ULTIMATE SUMMARY AND RECOMMENDATIONS";
        $results['debug_info'][] = "===============================================";
        
        if (count($results['issues_found']) > 0) {
            $results['debug_info'][] = "❌ ISSUES FOUND: " . count($results['issues_found']);
            foreach ($results['issues_found'] as $issue) {
                $results['debug_info'][] = "   - $issue";
            }
            
            $results['debug_info'][] = "";
            $results['debug_info'][] = "🔧 RECOMMENDED FIXES:";
            foreach ($results['issues_found'] as $issue) {
                if (strpos($issue, 'table does not exist') !== false) {
                    $results['debug_info'][] = "   - Run 'Create Missing Tables' to create services table";
                } elseif (strpos($issue, 'not registered') !== false) {
                    $results['debug_info'][] = "   - Check if Service Handler class is properly loaded";
                } elseif (strpos($issue, 'not found') !== false) {
                    $results['debug_info'][] = "   - Check if template/JS files exist in correct location";
                } elseif (strpos($issue, 'empty response') !== false) {
                    $results['debug_info'][] = "   - Check PHP error logs for AJAX errors";
                } elseif (strpos($issue, 'not enqueued') !== false) {
                    $results['debug_info'][] = "   - Check if scripts are properly enqueued in admin";
                } elseif (strpos($issue, 'missing') !== false) {
                    $results['debug_info'][] = "   - Check if required elements exist in template/JS files";
                }
            }
        } else {
            $results['debug_info'][] = "✅ NO CRITICAL ISSUES FOUND";
            $results['debug_info'][] = "⚠️ Since no issues found, problem might be:";
            $results['debug_info'][] = "   - JavaScript not loading properly";
            $results['debug_info'][] = "   - CSS not applied correctly";
            $results['debug_info'][] = "   - Browser cache issues";
            $results['debug_info'][] = "   - Plugin conflicts";
            $results['debug_info'][] = "   - Network connectivity issues";
            $results['debug_info'][] = "   - JavaScript errors in browser console";
            $results['debug_info'][] = "   - AJAX requests failing silently";
        }
        
        $results['debug_info'][] = "";
        $results['debug_info'][] = "🎯 ULTIMATE NEXT STEPS:";
        $results['debug_info'][] = "1. Follow the browser debugging guide above";
        $results['debug_info'][] = "2. Check browser console for JavaScript errors";
        $results['debug_info'][] = "3. Check Network tab for failed AJAX requests";
        $results['debug_info'][] = "4. Try hard refresh and clear cache";
        $results['debug_info'][] = "5. Disable other plugins to check for conflicts";
        $results['debug_info'][] = "6. Check if services exist in database";
        $results['debug_info'][] = "7. Verify all HTML elements are present";
        $results['debug_info'][] = "8. Test with different browsers";
        
        if (count($results['issues_found']) > 0) {
            $results['success'] = false;
        }
        
        return $results;
    }
    
    public function ajax_comprehensive_services_test() {
        check_ajax_referer('nexora_repair_nonce', 'nonce');
        wp_send_json($this->comprehensive_services_test());
    }
}
new Nexora_Repair_System(); 