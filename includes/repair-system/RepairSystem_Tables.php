<?php

trait RepairSystem_Tables {
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
            ) {$wpdb->get_charset_collate()};"
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
    }
    public function repair_tables() {
        $this->create_missing_tables();
        $this->repair_customer_info_table();
        return array('success' => true, 'message' => 'Tables repaired successfully');
    }
    private function repair_customer_info_table() {
        global $wpdb;
        $users_table = $wpdb->users;
        $messages = [];
        $required_columns = [
            'customer_type' => "ALTER TABLE $users_table ADD COLUMN customer_type VARCHAR(20) DEFAULT NULL COMMENT 'نوع مشتری: private یا business'",
            'company_name' => "ALTER TABLE $users_table ADD COLUMN company_name VARCHAR(255) DEFAULT NULL COMMENT 'نام شرکت'",
            'street' => "ALTER TABLE $users_table ADD COLUMN street VARCHAR(255) DEFAULT NULL COMMENT 'آدرس خیابان'",
            'house_number' => "ALTER TABLE $users_table ADD COLUMN house_number VARCHAR(20) DEFAULT NULL COMMENT 'شماره خانه'",
            'postfach' => "ALTER TABLE $users_table ADD COLUMN postfach VARCHAR(50) DEFAULT NULL COMMENT 'صندوق پستی'",
            'postal_code' => "ALTER TABLE $users_table ADD COLUMN postal_code VARCHAR(20) DEFAULT NULL COMMENT 'کد پستی'",
            'city' => "ALTER TABLE $users_table ADD COLUMN city VARCHAR(100) DEFAULT NULL COMMENT 'شهر'",
            'country' => "ALTER TABLE $users_table ADD COLUMN country VARCHAR(10) DEFAULT 'AT' COMMENT 'کشور'",
            'vat_id' => "ALTER TABLE $users_table ADD COLUMN vat_id VARCHAR(50) DEFAULT NULL COMMENT 'شماره شناسایی مالیات'",
            'reference_number' => "ALTER TABLE $users_table ADD COLUMN reference_number VARCHAR(100) DEFAULT NULL COMMENT 'شماره مرجع'",
            'salutation' => "ALTER TABLE $users_table ADD COLUMN salutation VARCHAR(20) DEFAULT NULL COMMENT 'عنوان'",
            'phone' => "ALTER TABLE $users_table ADD COLUMN phone VARCHAR(50) DEFAULT NULL COMMENT 'شماره تلفن'",
            'newsletter' => "ALTER TABLE $users_table ADD COLUMN newsletter ENUM('yes','no') DEFAULT 'no' COMMENT 'عضویت در خبرنامه'",
            'nexora_kind_user' => "ALTER TABLE $users_table ADD COLUMN nexora_kind_user VARCHAR(20) DEFAULT 'customer' COMMENT 'نوع کاربر در سیستم'"
        ];
        
        foreach ($required_columns as $col => $sql) {
            $exists = $wpdb->get_row("SHOW COLUMNS FROM $users_table LIKE '$col'");
            if (!$exists) {
                $wpdb->query($sql);
                $messages[] = "Spalte '$col' wurde zu wp_users hinzugefügt.";
            }
        }
        $private_columns = [
            'salutation_private' => "ALTER TABLE $users_table ADD COLUMN salutation_private VARCHAR(20) DEFAULT NULL COMMENT 'عنوان مشتری خصوصی'",
            'first_name_private' => "ALTER TABLE $users_table ADD COLUMN first_name_private VARCHAR(100) DEFAULT NULL COMMENT 'نام مشتری خصوصی'",
            'last_name_private' => "ALTER TABLE $users_table ADD COLUMN last_name_private VARCHAR(100) DEFAULT NULL COMMENT 'نام خانوادگی مشتری خصوصی'",
            'street_private' => "ALTER TABLE $users_table ADD COLUMN street_private VARCHAR(255) DEFAULT NULL COMMENT 'آدرس مشتری خصوصی'",
            'house_number_private' => "ALTER TABLE $users_table ADD COLUMN house_number_private VARCHAR(20) DEFAULT NULL COMMENT 'شماره خانه مشتری خصوصی'",
            'postfach_private' => "ALTER TABLE $users_table ADD COLUMN postfach_private VARCHAR(50) DEFAULT NULL COMMENT 'صندوق پستی مشتری خصوصی'",
            'postal_code_private' => "ALTER TABLE $users_table ADD COLUMN postal_code_private VARCHAR(20) DEFAULT NULL COMMENT 'کد پستی مشتری خصوصی'",
            'city_private' => "ALTER TABLE $users_table ADD COLUMN city_private VARCHAR(100) DEFAULT NULL COMMENT 'شهر مشتری خصوصی'",
            'country_private' => "ALTER TABLE $users_table ADD COLUMN country_private VARCHAR(10) DEFAULT 'AT' COMMENT 'کشور مشتری خصوصی'",
            'reference_number_private' => "ALTER TABLE $users_table ADD COLUMN reference_number_private VARCHAR(100) DEFAULT NULL COMMENT 'شماره مرجع مشتری خصوصی'",
            'phone_private' => "ALTER TABLE $users_table ADD COLUMN phone_private VARCHAR(50) DEFAULT NULL COMMENT 'تلفن مشتری خصوصی'",
            'newsletter_private' => "ALTER TABLE $users_table ADD COLUMN newsletter_private ENUM('yes','no') DEFAULT 'no' COMMENT 'خبرنامه مشتری خصوصی'",
            'terms_accepted_private' => "ALTER TABLE $users_table ADD COLUMN terms_accepted_private ENUM('yes','no') DEFAULT 'no' COMMENT 'پذیرش شرایط مشتری خصوصی'"
        ];
        
        foreach ($private_columns as $col => $sql) {
            $exists = $wpdb->get_row("SHOW COLUMNS FROM $users_table LIKE '$col'");
            if (!$exists) {
                $wpdb->query($sql);
                $messages[] = "Spalte '$col' wurde zu wp_users hinzugefügt.";
            }
        }
        
        $business_columns = [
            'salutation_business' => "ALTER TABLE $users_table ADD COLUMN salutation_business VARCHAR(20) DEFAULT NULL COMMENT 'عنوان مشتری تجاری'",
            'first_name_business' => "ALTER TABLE $users_table ADD COLUMN first_name_business VARCHAR(100) DEFAULT NULL COMMENT 'نام مشتری تجاری'",
            'last_name_business' => "ALTER TABLE $users_table ADD COLUMN last_name_business VARCHAR(100) DEFAULT NULL COMMENT 'نام خانوادگی مشتری تجاری'",
            'street_business' => "ALTER TABLE $users_table ADD COLUMN street_business VARCHAR(255) DEFAULT NULL COMMENT 'آدرس مشتری تجاری'",
            'house_number_business' => "ALTER TABLE $users_table ADD COLUMN house_number_business VARCHAR(20) DEFAULT NULL COMMENT 'شماره خانه مشتری تجاری'",
            'postfach_business' => "ALTER TABLE $users_table ADD COLUMN postfach_business VARCHAR(50) DEFAULT NULL COMMENT 'صندوق پستی مشتری تجاری'",
            'postal_code_business' => "ALTER TABLE $users_table ADD COLUMN postal_code_business VARCHAR(20) DEFAULT NULL COMMENT 'کد پستی مشتری تجاری'",
            'city_business' => "ALTER TABLE $users_table ADD COLUMN city_business VARCHAR(100) DEFAULT NULL COMMENT 'شهر مشتری تجاری'",
            'country_business' => "ALTER TABLE $users_table ADD COLUMN country_business VARCHAR(10) DEFAULT 'AT' COMMENT 'کشور مشتری تجاری'",
            'phone_business' => "ALTER TABLE $users_table ADD COLUMN phone_business VARCHAR(50) DEFAULT NULL COMMENT 'تلفن مشتری تجاری'",
            'newsletter_business' => "ALTER TABLE $users_table ADD COLUMN newsletter_business ENUM('yes','no') DEFAULT 'no' COMMENT 'خبرنامه مشتری تجاری'",
            'terms_accepted_business' => "ALTER TABLE $users_table ADD COLUMN terms_accepted_business ENUM('yes','no') DEFAULT 'no' COMMENT 'پذیرش شرایط مشتری تجاری'"
        ];
        
        foreach ($business_columns as $col => $sql) {
            $exists = $wpdb->get_row("SHOW COLUMNS FROM $users_table LIKE '$col'");
            if (!$exists) {
                $wpdb->query($sql);
                $messages[] = "Spalte '$col' wurde zu wp_users hinzugefügt.";
            }
        }
        $system_columns = [
            'registration_date' => "ALTER TABLE $users_table ADD COLUMN registration_date DATETIME DEFAULT CURRENT_TIMESTAMP COMMENT 'تاریخ ثبت نام'",
            'last_updated' => "ALTER TABLE $users_table ADD COLUMN last_updated DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP COMMENT 'آخرین بروزرسانی'",
            'customer_status' => "ALTER TABLE $users_table ADD COLUMN customer_status ENUM('active','inactive','pending') DEFAULT 'active' COMMENT 'وضعیت مشتری'",
            'customer_notes' => "ALTER TABLE $users_table ADD COLUMN customer_notes TEXT DEFAULT NULL COMMENT 'یادداشت‌های مشتری'"
        ];
        
        foreach ($system_columns as $col => $sql) {
            $exists = $wpdb->get_row("SHOW COLUMNS FROM $users_table LIKE '$col'");
            if (!$exists) {
                $wpdb->query($sql);
                $messages[] = "Spalte '$col' wurde zu wp_users hinzugefügt.";
            }
        }
        
        if (empty($messages)) {
            return array('success' => true, 'message' => 'wp_users Tabelle ist bereits aktuell mit allen erforderlichen Feldern.');
        } else {
            return array('success' => true, 'message' => implode(' ', $messages));
        }
    }
} 