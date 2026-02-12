<?php

if (!defined('ABSPATH')) {
    exit;
}

class Nexora_Email_Database_Manager {
    
    private $log_file;
    
    public function __construct() {
        $this->log_file = dirname(__DIR__, 3) . '/logs/Nexora Service Suite-email-db.log';
        $this->ensure_log_directory();
    }
    
    
    private function ensure_log_directory() {
        $log_dir = dirname($this->log_file);
        if (!is_dir($log_dir)) {
            mkdir($log_dir, 0755, true);
        }
    }
    
    
    public function create_email_tables() {
        global $wpdb;
        
        $this->log_info('Starting email database tables creation...');
        
        $charset_collate = $wpdb->get_charset_collate();
        $results = array();
        $table_name = $wpdb->prefix . 'nexora_email_smtp_settings';
        $sql1 = "CREATE TABLE IF NOT EXISTS $table_name (
            id INT NOT NULL AUTO_INCREMENT,
            enabled TINYINT(1) DEFAULT 0,
            host VARCHAR(255),
            port INT DEFAULT 587,
            encryption ENUM('tls', 'ssl', 'none') DEFAULT 'tls',
            username VARCHAR(255),
            password VARCHAR(255),
            auth_mode ENUM('login', 'plain', 'cram-md5') DEFAULT 'login',
            sender_name VARCHAR(255),
            sender_email VARCHAR(255),
            reply_to VARCHAR(255),
            timeout INT DEFAULT 30,
            max_retries INT DEFAULT 3,
            created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
            updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            PRIMARY KEY (id)
        ) $charset_collate;";
        
        $result1 = $wpdb->query($sql1);
        $results['smtp_settings'] = $result1 !== false;
        $this->log_info('SMTP Settings table creation: ' . ($result1 !== false ? 'SUCCESS' : 'FAILED'));
        $table_name = $wpdb->prefix . 'nexora_email_logs';
        $sql2 = "CREATE TABLE IF NOT EXISTS $table_name (
            id INT NOT NULL AUTO_INCREMENT,
            email_type ENUM('test', 'notification', 'system', 'error') NOT NULL,
            recipient_email VARCHAR(255) NOT NULL,
            subject VARCHAR(500),
            message_preview TEXT,
            smtp_host VARCHAR(255),
            smtp_port INT,
            smtp_username VARCHAR(255),
            encryption VARCHAR(10),
            status ENUM('success', 'failed', 'pending') NOT NULL,
            error_message TEXT,
            response_code VARCHAR(10),
            response_message TEXT,
            sent_at DATETIME DEFAULT CURRENT_TIMESTAMP,
            processing_time_ms INT,
            ip_address VARCHAR(45),
            user_agent TEXT,
            PRIMARY KEY (id),
            KEY idx_email_type (email_type),
            KEY idx_status (status),
            KEY idx_sent_at (sent_at),
            KEY idx_recipient (recipient_email)
        ) $charset_collate;";
        
        $result2 = $wpdb->query($sql2);
        $results['email_logs'] = $result2 !== false;
        $this->log_info('Email Logs table creation: ' . ($result2 !== false ? 'SUCCESS' : 'FAILED'));
        $table_name = $wpdb->prefix . 'nexora_email_templates';
        $sql3 = "CREATE TABLE IF NOT EXISTS $table_name (
            id INT NOT NULL AUTO_INCREMENT,
            template_name VARCHAR(100) NOT NULL,
            template_type ENUM('notification', 'welcome', 'status_change', 'invoice', 'custom') NOT NULL,
            subject_template TEXT NOT NULL,
            body_template LONGTEXT NOT NULL,
            variables JSON,
            is_active TINYINT(1) DEFAULT 1,
            created_by INT,
            created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
            updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            PRIMARY KEY (id),
            UNIQUE KEY unique_template_name (template_name),
            KEY idx_template_type (template_type),
            KEY idx_is_active (is_active)
        ) $charset_collate;";
        
        $result3 = $wpdb->query($sql3);
        $results['email_templates'] = $result3 !== false;
        $this->log_info('Email Templates table creation: ' . ($result3 !== false ? 'SUCCESS' : 'FAILED'));
        $table_name = $wpdb->prefix . 'nexora_email_notifications';
        $sql4 = "CREATE TABLE IF NOT EXISTS $table_name (
            id INT NOT NULL AUTO_INCREMENT,
            notification_type VARCHAR(100) NOT NULL,
            event_name VARCHAR(100) NOT NULL,
            is_enabled TINYINT(1) DEFAULT 1,
            template_id INT,
            recipients JSON,
            conditions JSON,
            schedule_type ENUM('immediate', 'delayed', 'scheduled') DEFAULT 'immediate',
            delay_minutes INT DEFAULT 0,
            created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
            updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            PRIMARY KEY (id),
            KEY idx_notification_type (notification_type),
            KEY idx_event_name (event_name),
            KEY idx_is_enabled (is_enabled),
            FOREIGN KEY (template_id) REFERENCES {$wpdb->prefix}nexora_email_templates(id) ON DELETE SET NULL
        ) $charset_collate;";
        
        $result4 = $wpdb->query($sql4);
        $results['email_notifications'] = $result4 !== false;
        $this->log_info('Email Notifications table creation: ' . ($result4 !== false ? 'SUCCESS' : 'FAILED'));
        $table_name = $wpdb->prefix . 'nexora_email_queue';
        $sql5 = "CREATE TABLE IF NOT EXISTS $table_name (
            id INT NOT NULL AUTO_INCREMENT,
            email_type VARCHAR(100) NOT NULL,
            recipient_email VARCHAR(255) NOT NULL,
            subject VARCHAR(500),
            message LONGTEXT,
            headers JSON,
            priority ENUM('low', 'normal', 'high', 'urgent') DEFAULT 'normal',
            status ENUM('pending', 'processing', 'sent', 'failed', 'cancelled') DEFAULT 'pending',
            attempts INT DEFAULT 0,
            max_attempts INT DEFAULT 3,
            scheduled_at DATETIME DEFAULT CURRENT_TIMESTAMP,
            sent_at DATETIME NULL,
            error_message TEXT,
            created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
            updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            PRIMARY KEY (id),
            KEY idx_status (status),
            KEY idx_priority (priority),
            KEY idx_scheduled_at (scheduled_at),
            KEY idx_recipient (recipient_email)
        ) $charset_collate;";
        
        $result5 = $wpdb->query($sql5);
        $results['email_queue'] = $result5 !== false;
        $this->log_info('Email Queue table creation: ' . ($result5 !== false ? 'SUCCESS' : 'FAILED'));
        $this->insert_default_data();
        
        $this->log_info('Email database tables creation completed');
        
        return $results;
    }
    
    
    private function insert_default_data() {
        global $wpdb;
        
        $this->log_info('Inserting default data...');
        $smtp_table = $wpdb->prefix . 'nexora_email_smtp_settings';
        $default_smtp_settings = array(
            'enabled' => 0,
            'host' => 'localhost',
            'port' => 587,
            'encryption' => 'tls',
            'username' => '',
            'password' => '',
            'auth_mode' => 'login',
            'sender_name' => 'Nexora Service Suite',
            'sender_email' => 'noreply@example.com',
            'reply_to' => 'support@example.com',
            'timeout' => 30,
            'max_retries' => 3
        );
        
        $wpdb->insert(
            $smtp_table,
            $default_smtp_settings,
            array('%d', '%s', '%d', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%d', '%d')
        );
        $templates_table = $wpdb->prefix . 'nexora_email_templates';
        $default_templates = array(
            array(
                'template_name' => 'welcome_customer',
                'template_type' => 'welcome',
                'subject_template' => 'Welcome to Nexora Service Suite',
                'body_template' => '<h2>Welcome to Nexora Service Suite</h2><p>Hello {customer_name},</p><p>Thank you for registering!</p>',
                'variables' => '["customer_name", "customer_email", "registration_date"]'
            ),
            array(
                'template_name' => 'service_status_change',
                'template_type' => 'status_change',
                'subject_template' => 'Status update for your service request #{request_id}',
                'body_template' => '<h2>Status update</h2><p>Your service request #{request_id} has a new status.</p>',
                'variables' => '["request_id", "old_status", "new_status", "customer_name"]'
            ),
            array(
                'template_name' => 'test_email',
                'template_type' => 'custom',
                'subject_template' => 'Test E-Mail - {subject}',
                'body_template' => '<h2>Test E-Mail</h2><p>Dies ist eine Test-E-Mail vom Independent Email System.</p>',
                'variables' => '["subject", "message", "timestamp"]'
            )
        );
        
        foreach ($default_templates as $template) {
            $wpdb->replace(
                $templates_table,
                array(
                    'template_name' => $template[0],
                    'template_type' => $template[1],
                    'subject_template' => $template[2],
                    'body_template' => $template[3],
                    'variables' => $template[4],
                    'is_active' => 1
                ),
                array('%s', '%s', '%s', '%s', '%s', '%d')
            );
        }
        $notifications_table = $wpdb->prefix . 'nexora_email_notifications';
        $default_notifications = array(
            array(
                'notification_type' => 'customer_registration',
                'event_name' => 'customer_registered',
                'is_enabled' => 1,
                'template_id' => 1
            ),
            array(
                'notification_type' => 'service_status_change',
                'event_name' => 'service_status_changed',
                'is_enabled' => 1,
                'template_id' => 2
            )
        );
        
        foreach ($default_notifications as $notification) {
            $wpdb->replace(
                $notifications_table,
                array(
                    'notification_type' => $notification[0],
                    'event_name' => $notification[1],
                    'is_enabled' => $notification[2],
                    'template_id' => $notification[3]
                ),
                array('%s', '%s', '%d', '%d')
            );
        }
        
        $this->log_info('Default data insertion completed');
    }
    
    
    public function check_email_tables() {
        global $wpdb;
        
        $tables = array(
            $wpdb->prefix . 'nexora_email_smtp_settings',
            $wpdb->prefix . 'nexora_email_logs',
            $wpdb->prefix . 'nexora_email_templates',
            $wpdb->prefix . 'nexora_email_notifications',
            $wpdb->prefix . 'nexora_email_queue'
        );
        
        $results = array();
        foreach ($tables as $table) {
            $results[$table] = $wpdb->get_var("SHOW TABLES LIKE '$table'") == $table;
        }
        
        return $results;
    }
    
    
    public function get_table_status() {
        $tables_exist = $this->check_email_tables();
        $all_exist = !in_array(false, $tables_exist);
        
        return array(
            'tables_exist' => $tables_exist,
            'all_exist' => $all_exist,
            'total_tables' => count($tables_exist),
            'existing_tables' => count(array_filter($tables_exist)),
            'missing_tables' => count(array_filter($tables_exist, function($exists) { return !$exists; }))
        );
    }
    
    
    private function log_info($message) {
        $this->write_log('INFO', $message);
    }
    
    
    private function log_error($message) {
        $this->write_log('ERROR', $message);
    }
    
    
    private function write_log($level, $message) {
        if (!is_writable(dirname($this->log_file))) {
            return;
        }
        
        $log_entry = sprintf("[%s] [%s] %s\n", date('Y-m-d H:i:s'), $level, $message);
        file_put_contents($this->log_file, $log_entry, FILE_APPEND | LOCK_EX);
    }
    
    
    public function get_log_content($lines = 100) {
        if (!file_exists($this->log_file)) {
            return array();
        }
        
        $log_content = file($this->log_file);
        return array_slice($log_content, -$lines);
    }
    
    
    public function log_email_manually($email_type, $recipient_email, $subject, $message, $status, $error_message = '', $processing_time = 0) {
        try {
            global $wpdb;
            
            $table_name = $wpdb->prefix . 'nexora_email_logs';
            $table_exists = $wpdb->get_var("SHOW TABLES LIKE '$table_name'") == $table_name;
            
            if (!$table_exists) {
                $this->log_error('Email logs table does not exist, cannot log email');
                return false;
            }
            $message_preview = substr(strip_tags($message), 0, 200);
            if (strlen($message) > 200) {
                $message_preview .= '...';
            }
            $ip_address = $this->get_client_ip();
            $user_agent = isset($_SERVER['HTTP_USER_AGENT']) ? $_SERVER['HTTP_USER_AGENT'] : '';
            $result = $wpdb->insert(
                $table_name,
                array(
                    'email_type' => $email_type,
                    'recipient_email' => $recipient_email,
                    'subject' => $subject,
                    'message_preview' => $message_preview,
                    'status' => $status,
                    'error_message' => $error_message,
                    'processing_time_ms' => $processing_time,
                    'ip_address' => $ip_address,
                    'user_agent' => $user_agent
                ),
                array(
                    '%s',
                    '%s',
                    '%s',
                    '%s',
                    '%s',
                    '%s',
                    '%d',
                    '%s',
                    '%s'
                )
            );
            
            if ($result !== false) {
                $this->log_info("Manual email logged successfully. Log ID: " . $wpdb->insert_id);
                return $wpdb->insert_id;
            } else {
                $this->log_error("Failed to log manual email: " . $wpdb->last_error);
                return false;
            }
            
        } catch (Exception $e) {
            $this->log_error('Exception logging manual email: ' . $e->getMessage());
            return false;
        }
    }
    
    
    private function get_client_ip() {
        $ip_keys = array('HTTP_CLIENT_IP', 'HTTP_X_FORWARDED_FOR', 'HTTP_X_FORWARDED', 'HTTP_X_CLUSTER_CLIENT_IP', 'HTTP_FORWARDED_FOR', 'HTTP_FORWARDED', 'REMOTE_ADDR');
        
        foreach ($ip_keys as $key) {
            if (array_key_exists($key, $_SERVER) === true) {
                foreach (explode(',', $_SERVER[$key]) as $ip) {
                    $ip = trim($ip);
                    if (filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE) !== false) {
                        return $ip;
                    }
                }
            }
        }
        
        return isset($_SERVER['REMOTE_ADDR']) ? $_SERVER['REMOTE_ADDR'] : 'unknown';
    }
}
