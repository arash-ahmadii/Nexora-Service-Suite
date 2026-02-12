<?php

if (!defined('ABSPATH')) {
    exit;
}

class Nexora_Customer_Number_Manager {
    
    
    const STARTING_NUMBER = 350;
    
    
    const NUMBER_PREFIX = 'C';
    
    
    public function __construct() {
        add_action('user_register', array($this, 'assign_customer_number'), 10, 1);
        add_action('wp_ajax_nexora_update_customer_number', array($this, 'ajax_update_customer_number'));
        add_action('wp_ajax_nopriv_nexora_update_customer_number', array($this, 'ajax_update_customer_number'));
        add_action('wp_ajax_nexora_initialize_customer_numbers', array($this, 'ajax_initialize_customer_numbers'));
        add_action('wp_ajax_nopriv_nexora_initialize_customer_numbers', array($this, 'ajax_initialize_customer_numbers'));
        add_action('wp_ajax_nexora_add_customer_number_column', array($this, 'ajax_add_customer_number_column'));
        add_action('wp_ajax_nopriv_nexora_add_customer_number_column', array($this, 'ajax_add_customer_number_column'));
        add_action('wp_ajax_nexora_check_customer_number_status', array($this, 'ajax_check_customer_number_status'));
        add_action('wp_ajax_nopriv_nexora_check_customer_number_status', array($this, 'ajax_check_customer_number_status'));
        add_action('wp_ajax_nexora_check_easy_form_tables', array($this, 'ajax_check_easy_form_tables'));
        add_action('wp_ajax_nopriv_nexora_check_easy_form_tables', array($this, 'ajax_check_easy_form_tables'));
        add_action('wp_ajax_nexora_create_missing_easy_form_tables', array($this, 'ajax_create_missing_easy_form_tables'));
        add_action('wp_ajax_nopriv_nexora_create_missing_easy_form_tables', array($this, 'ajax_create_missing_easy_form_tables'));
        add_action('wp_ajax_nexora_repair_easy_form_database', array($this, 'ajax_repair_easy_form_database'));
        add_action('wp_ajax_nopriv_nexora_repair_easy_form_database', array($this, 'ajax_repair_easy_form_database'));
        add_action('wp_ajax_nexora_test_easy_form_database', array($this, 'ajax_test_easy_form_database'));
        add_action('wp_ajax_nopriv_nexora_test_easy_form_database', array($this, 'ajax_test_easy_form_database'));
    }
    
    
    public function assign_customer_number($user_id) {
        $user = get_userdata($user_id);
        if (!$user || !in_array('customer', $user->roles)) {
            return;
        }
        if ($this->get_user_customer_number($user_id)) {
            return;
        }
        $customer_number = $this->generate_next_customer_number();
        $this->set_user_customer_number($user_id, $customer_number);
        $this->update_customer_info_table($user_id, $customer_number);
    }
    
    
    public function generate_next_customer_number() {
        global $wpdb;
        $highest_number = $wpdb->get_var(
            "SELECT customer_number FROM {$wpdb->users} 
             WHERE customer_number IS NOT NULL 
             AND customer_number != '' 
             ORDER BY CAST(SUBSTRING(customer_number, 2) AS UNSIGNED) DESC 
             LIMIT 1"
        );
        
        if ($highest_number) {
            $number_part = intval(substr($highest_number, 1));
            $next_number = $number_part + 1;
        } else {
            $next_number = self::STARTING_NUMBER;
        }
        
        return self::NUMBER_PREFIX . str_pad($next_number, 4, '0', STR_PAD_LEFT);
    }
    
    
    public function get_user_customer_number($user_id) {
        global $wpdb;
        
        $customer_number = $wpdb->get_var($wpdb->prepare(
            "SELECT customer_number FROM {$wpdb->users} WHERE ID = %d",
            $user_id
        ));
        
        return $customer_number;
    }
    
    
    public function set_user_customer_number($user_id, $customer_number) {
        global $wpdb;
        
        $wpdb->update(
            $wpdb->users,
            array('customer_number' => $customer_number),
            array('ID' => $user_id),
            array('%s'),
            array('%d')
        );
    }
    
    
    private function update_customer_info_table($user_id, $customer_number) {
        global $wpdb;
        $wpdb->update(
            $wpdb->users,
            array('customer_number' => $customer_number),
            array('ID' => $user_id),
            array('%s'),
            array('%d')
        );
        update_user_meta($user_id, 'customer_number', $customer_number);
        
        error_log("Nexora Service Suite Customer Number Manager - Updated customer_number for user $user_id: $customer_number");
    }
    
    
    public function ajax_update_customer_number() {
        if (!wp_verify_nonce($_POST['nonce'], 'nexora_customer_number_nonce')) {
            wp_send_json_error('Nonce verification failed');
        }
        if (!current_user_can('manage_options')) {
            wp_send_json_error('Insufficient permissions');
        }
        
        $user_id = intval($_POST['user_id']);
        $new_customer_number = sanitize_text_field($_POST['customer_number']);
        
        if (!$user_id || !$new_customer_number) {
            wp_send_json_error('Invalid data');
        }
        $existing_user = $this->get_user_by_customer_number($new_customer_number);
        if ($existing_user && $existing_user != $user_id) {
            wp_send_json_error('Customer number already exists for another user');
        }
        $this->set_user_customer_number($user_id, $new_customer_number);
        $this->update_customer_info_table($user_id, $new_customer_number);
        
        wp_send_json_success(array(
            'message' => 'Customer number updated successfully',
            'customer_number' => $new_customer_number
        ));
    }
    
    
    public function get_user_by_customer_number($customer_number) {
        global $wpdb;
        
        $user_id = $wpdb->get_var($wpdb->prepare(
            "SELECT ID FROM {$wpdb->users} WHERE customer_number = %s",
            $customer_number
        ));
        
        return $user_id ? intval($user_id) : null;
    }
    
    
    public function get_all_customer_numbers() {
        global $wpdb;
        
        $results = $wpdb->get_results(
            "SELECT u.ID, u.user_login, u.user_email, u.customer_number, u.user_registered,
                    um.meta_value as customer_type
             FROM {$wpdb->users} u
             LEFT JOIN {$wpdb->usermeta} um ON u.ID = um.user_id AND um.meta_key = 'customer_type'
             WHERE u.customer_number IS NOT NULL
             ORDER BY u.ID DESC"
        );
        
        return $results;
    }
    
    
    public function initialize_existing_customer_numbers() {
        global $wpdb;
        $users_without_numbers = $wpdb->get_results(
            "SELECT u.ID 
             FROM {$wpdb->users} u
             JOIN {$wpdb->usermeta} um ON u.ID = um.user_id
             WHERE um.meta_key = 'wp_capabilities' 
             AND um.meta_value LIKE '%customer%'
             AND (u.customer_number IS NULL OR u.customer_number = '')
             ORDER BY u.ID"
        );
        
        $count = 0;
        foreach ($users_without_numbers as $user) {
            $customer_number = $this->generate_next_customer_number();
            $this->set_user_customer_number($user->ID, $customer_number);
            $this->update_customer_info_table($user->ID, $customer_number);
            $count++;
        }
        
        return $count;
    }
    
    
    public function ajax_initialize_customer_numbers() {
        if (!wp_verify_nonce($_POST['nonce'], 'nexora_customer_number_nonce')) {
            wp_send_json_error('Nonce verification failed');
        }
        if (!current_user_can('manage_options')) {
            wp_send_json_error('Insufficient permissions');
        }
        $count = $this->initialize_existing_customer_numbers();
        
        wp_send_json_success(array(
            'message' => 'Customer numbers initialized successfully',
            'count' => $count
        ));
    }
    
    
    public function ajax_add_customer_number_column() {
        if (!wp_verify_nonce($_POST['nonce'], 'nexora_customer_number_nonce')) {
            wp_send_json_error('Nonce verification failed');
        }
        if (!current_user_can('manage_options')) {
            wp_send_json_error('Insufficient permissions');
        }
        
        global $wpdb;
        $column_exists = $wpdb->get_results("SHOW COLUMNS FROM {$wpdb->users} LIKE 'customer_number'");
        
        if (!empty($column_exists)) {
            wp_send_json_success(array(
                'message' => 'Customer number column already exists',
                'column_exists' => true
            ));
        }
        $result = $wpdb->query("ALTER TABLE {$wpdb->users} ADD COLUMN customer_number VARCHAR(50) DEFAULT NULL AFTER user_email");
        
        if ($result !== false) {
            $wpdb->query("CREATE INDEX IF NOT EXISTS idx_customer_number ON {$wpdb->users}(customer_number)");
            
            wp_send_json_success(array(
                'message' => 'Customer number column added successfully',
                'column_exists' => false
            ));
        } else {
            wp_send_json_error('Failed to add customer number column: ' . $wpdb->last_error);
        }
    }
    
    
    public function ajax_check_customer_number_status() {
        if (!wp_verify_nonce($_POST['nonce'], 'nexora_customer_number_nonce')) {
            wp_send_json_error('Nonce verification failed');
        }
        if (!current_user_can('manage_options')) {
            wp_send_json_error('Insufficient permissions');
        }
        
        global $wpdb;
        $total_users = $wpdb->get_var("SELECT COUNT(*) FROM {$wpdb->users}");
        $users_with_numbers = $wpdb->get_var("SELECT COUNT(*) FROM {$wpdb->users} WHERE customer_number IS NOT NULL AND customer_number != ''");
        $users_without_numbers = $total_users - $users_with_numbers;
        $next_number = $this->generate_next_customer_number();
        
        wp_send_json_success(array(
            'total_users' => $total_users,
            'users_with_numbers' => $users_with_numbers,
            'users_without_numbers' => $users_without_numbers,
            'next_number' => $next_number
        ));
    }

    
    public function ajax_check_easy_form_tables() {
        if (!wp_verify_nonce($_POST['nonce'], 'nexora_easy_form_nonce')) {
            wp_send_json_error('Nonce verification failed');
        }
        if (!current_user_can('manage_options')) {
            wp_send_json_error('Insufficient permissions');
        }
        
        global $wpdb;
        $users_table = $wpdb->get_var("SHOW TABLES LIKE '{$wpdb->users}'") === $wpdb->users;
        $customer_number_column = $wpdb->get_results("SHOW COLUMNS FROM {$wpdb->users} LIKE 'customer_number'");
        $customer_info_table = $wpdb->get_var("SHOW TABLES LIKE '{$wpdb->prefix}nexora_customer_info'") === $wpdb->prefix . 'nexora_customer_info';
        $service_requests_table = $wpdb->get_var("SHOW TABLES LIKE '{$wpdb->prefix}nexora_service_requests'") === $wpdb->prefix . 'nexora_service_requests';
        $complete_service_requests_table = $wpdb->get_var("SHOW TABLES LIKE '{$wpdb->prefix}nexora_complete_service_requests'") === $wpdb->prefix . 'nexora_complete_service_requests';
        
        wp_send_json_success(array(
            'users_table' => $users_table,
            'customer_number_column' => !empty($customer_number_column),
            'customer_info_table' => $customer_info_table,
            'service_requests_table' => $service_requests_table,
            'complete_service_requests_table' => $complete_service_requests_table
        ));
    }

    
    public function ajax_create_missing_easy_form_tables() {
        if (!wp_verify_nonce($_POST['nonce'], 'nexora_easy_form_nonce')) {
            wp_send_json_error('Nonce verification failed');
        }
        if (!current_user_can('manage_options')) {
            wp_send_json_error('Insufficient permissions');
        }
        
        global $wpdb;
        $tables_created = 0;
        $columns_added = 0;
        $column_exists = $wpdb->get_results("SHOW COLUMNS FROM {$wpdb->users} LIKE 'customer_number'");
        if (empty($column_exists)) {
            $result = $wpdb->query("ALTER TABLE {$wpdb->users} ADD COLUMN customer_number VARCHAR(50) DEFAULT NULL AFTER user_email");
            if ($result !== false) {
                $columns_added++;
                $wpdb->query("CREATE INDEX IF NOT EXISTS idx_customer_number ON {$wpdb->users}(customer_number)");
            }
        }
        $customer_info_table = $wpdb->prefix . 'nexora_customer_info';
        if ($wpdb->get_var("SHOW TABLES LIKE '$customer_info_table'") !== $customer_info_table) {
            $sql = "CREATE TABLE IF NOT EXISTS $customer_info_table (
                id int(11) NOT NULL AUTO_INCREMENT,
                user_id bigint(20) NOT NULL,
                customer_type varchar(50) DEFAULT 'private',
                customer_number varchar(50) DEFAULT NULL,
                company_name varchar(255) DEFAULT '',
                company_name_2 varchar(255) DEFAULT '',
                street varchar(255) DEFAULT '',
                address_addition varchar(255) DEFAULT '',
                postal_code varchar(20) DEFAULT '',
                city varchar(255) DEFAULT '',
                country varchar(10) DEFAULT 'DE',
                industry varchar(255) DEFAULT '',
                vat_id varchar(255) DEFAULT '',
                salutation varchar(50) DEFAULT 'Herr',
                phone varchar(50) DEFAULT '',
                newsletter tinyint(1) DEFAULT 0,
                created_at timestamp DEFAULT CURRENT_TIMESTAMP,
                updated_at timestamp DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                PRIMARY KEY (id),
                UNIQUE KEY user_id (user_id),
                KEY customer_number (customer_number)
            ) " . $wpdb->get_charset_collate();
            
            $result = $wpdb->query($sql);
            if ($result !== false) {
                $tables_created++;
            }
        }
        $service_requests_table = $wpdb->prefix . 'nexora_service_requests';
        if ($wpdb->get_var("SHOW TABLES LIKE '$service_requests_table'") !== $service_requests_table) {
            $sql = "CREATE TABLE IF NOT EXISTS $service_requests_table (
                id int(11) NOT NULL AUTO_INCREMENT,
                serial varchar(255) DEFAULT '',
                model varchar(255) DEFAULT '',
                description text,
                user_id bigint(20) NOT NULL,
                status_id int(11) DEFAULT 1,
                created_at timestamp DEFAULT CURRENT_TIMESTAMP,
                updated_at timestamp DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                PRIMARY KEY (id),
                KEY user_id (user_id),
                KEY status_id (status_id)
            ) " . $wpdb->get_charset_collate();
            
            $result = $wpdb->query($sql);
            if ($result !== false) {
                $tables_created++;
            }
        }
        $complete_service_requests_table = $wpdb->prefix . 'nexora_complete_service_requests';
        if ($wpdb->get_var("SHOW TABLES LIKE '$complete_service_requests_table'") !== $complete_service_requests_table) {
            $sql = "CREATE TABLE IF NOT EXISTS $complete_service_requests_table (
                id int(11) NOT NULL AUTO_INCREMENT,
                user_id bigint(20) NOT NULL,
                customer_number varchar(50) DEFAULT NULL,
                device_type varchar(255) DEFAULT '',
                description text,
                priority varchar(50) DEFAULT 'medium',
                status varchar(50) DEFAULT 'pending',
                created_at timestamp DEFAULT CURRENT_TIMESTAMP,
                updated_at timestamp DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                PRIMARY KEY (id),
                KEY user_id (user_id),
                KEY customer_number (customer_number),
                KEY status (status)
            ) " . $wpdb->get_charset_collate();
            
            $result = $wpdb->query($sql);
            if ($result !== false) {
                $tables_created++;
            }
        }
        
        wp_send_json_success(array(
            'message' => 'Missing Easy Form tables created successfully',
            'tables_created' => $tables_created,
            'columns_added' => $columns_added
        ));
    }

    
    public function ajax_repair_easy_form_database() {
        if (!wp_verify_nonce($_POST['nonce'], 'nexora_easy_form_nonce')) {
            wp_send_json_error('Nonce verification failed');
        }
        if (!current_user_can('manage_options')) {
            wp_send_json_error('Insufficient permissions');
        }
        
        global $wpdb;
        $repairs_performed = 0;
        $issues_fixed = 0;
        $column_info = $wpdb->get_results("SHOW COLUMNS FROM {$wpdb->users} LIKE 'customer_number'");
        if (!empty($column_info)) {
            $column = $column_info[0];
            if ($column->Type !== 'varchar(50)') {
                $wpdb->query("ALTER TABLE {$wpdb->users} MODIFY COLUMN customer_number VARCHAR(50) DEFAULT NULL");
                $repairs_performed++;
                $issues_fixed++;
            }
        }
        $indexes = $wpdb->get_results("SHOW INDEX FROM {$wpdb->users} WHERE Key_name = 'idx_customer_number'");
        if (empty($indexes)) {
            $wpdb->query("CREATE INDEX IF NOT EXISTS idx_customer_number ON {$wpdb->users}(customer_number)");
            $repairs_performed++;
        }
        $tables_to_check = array(
            $wpdb->prefix . 'nexora_customer_info',
            $wpdb->prefix . 'nexora_service_requests',
            $wpdb->prefix . 'nexora_complete_service_requests'
        );
        
        foreach ($tables_to_check as $table) {
            if ($wpdb->get_var("SHOW TABLES LIKE '$table'") === $table) {
                $table_collation = $wpdb->get_var("SELECT TABLE_COLLATION FROM information_schema.TABLES WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = '$table'");
                if ($table_collation !== $wpdb->collate) {
                    $wpdb->query("ALTER TABLE $table CONVERT TO CHARACTER SET " . $wpdb->charset . " COLLATE " . $wpdb->collate);
                    $repairs_performed++;
                    $issues_fixed++;
                }
            }
        }
        
        wp_send_json_success(array(
            'message' => 'Easy Form database repaired successfully',
            'repairs_performed' => $repairs_performed,
            'issues_fixed' => $issues_fixed
        ));
    }

    
    public function ajax_test_easy_form_database() {
        if (!wp_verify_nonce($_POST['nonce'], 'nexora_easy_form_nonce')) {
            wp_send_json_error('Nonce verification failed');
        }
        if (!current_user_can('manage_options')) {
            wp_send_json_error('Insufficient permissions');
        }
        
        global $wpdb;
        $test_results = array();
        $database_operations = 0;
        $user_count = $wpdb->get_var("SELECT COUNT(*) FROM {$wpdb->users}");
        $test_results[] = "User count: $user_count";
        $database_operations++;
        $test_user_id = 1;
        $test_customer_number = 'TEST-' . time();
        $original_number = $wpdb->get_var($wpdb->prepare("SELECT customer_number FROM {$wpdb->users} WHERE ID = %d", $test_user_id));
        
        $update_result = $wpdb->update(
            $wpdb->users,
            array('customer_number' => $test_customer_number),
            array('ID' => $test_user_id),
            array('%s'),
            array('%d')
        );
        
        if ($update_result !== false) {
            $test_results[] = "Write test: PASSED";
            $database_operations++;
            $wpdb->update(
                $wpdb->users,
                array('customer_number' => $original_number),
                array('ID' => $test_user_id),
                array('%s'),
                array('%d')
            );
        } else {
            $test_results[] = "Write test: FAILED";
        }
        $customer_info_table = $wpdb->prefix . 'nexora_customer_info';
        if ($wpdb->get_var("SHOW TABLES LIKE '$customer_info_table'") === $customer_info_table) {
            $test_results[] = "Customer info table: EXISTS";
            $database_operations++;
            $test_data = array(
                'user_id' => 999999,
                'customer_type' => 'test',
                'customer_number' => 'TEST-' . time(),
                'postal_code' => '12345'
            );
            
            $insert_result = $wpdb->insert($customer_info_table, $test_data);
            if ($insert_result !== false) {
                $test_results[] = "Customer info insert: PASSED";
                $database_operations++;
                $wpdb->delete($customer_info_table, array('user_id' => 999999));
            } else {
                $test_results[] = "Customer info insert: FAILED";
            }
        } else {
            $test_results[] = "Customer info table: MISSING";
        }
        $service_requests_table = $wpdb->prefix . 'nexora_service_requests';
        if ($wpdb->get_var("SHOW TABLES LIKE '$service_requests_table'") === $service_requests_table) {
            $test_results[] = "Service requests table: EXISTS";
            $database_operations++;
        } else {
            $test_results[] = "Service requests table: MISSING";
        }
        
        wp_send_json_success(array(
            'message' => 'Easy Form database test completed',
            'test_results' => implode(' | ', $test_results),
            'database_operations' => $database_operations
        ));
    }
}
new Nexora_Customer_Number_Manager();
