<?php
if (!defined('ABSPATH')) {
    $wp_path = dirname(__FILE__);
    for ($i = 0; $i < 5; $i++) {
        if (file_exists($wp_path . '/wp-config.php')) {
            define('ABSPATH', $wp_path . '/');
            break;
        }
        $wp_path = dirname($wp_path);
    }
    
    if (!defined('ABSPATH')) {
        $possible_paths = [
            dirname(__FILE__) . '/../../',
            dirname(__FILE__) . '/../../../',
            dirname(__FILE__) . '/../../../../',
            '/home/seo2se/public_html/',
            '/home/seo2se/public_html/wp-content/',
        ];
        
        foreach ($possible_paths as $path) {
            if (file_exists($path . 'wp-config.php')) {
                define('ABSPATH', $path);
                break;
            }
        }
    }
}
if (defined('ABSPATH') && file_exists(ABSPATH . 'wp-load.php')) {
    require_once(ABSPATH . 'wp-load.php');
} else {
    $wp_load_path = dirname(__FILE__);
    for ($i = 0; $i < 5; $i++) {
        if (file_exists($wp_load_path . '/wp-load.php')) {
            require_once($wp_load_path . '/wp-load.php');
            break;
        }
        $wp_load_path = dirname($wp_load_path);
    }
}

class Nexora_Automatic_Email_Notifications {
    
    private static $instance = null;
    private $email_system;
    private $templates;
    private $log_file;
    private $hooks_setup = false;
    
    public function __construct() {
        if (self::$instance !== null) {
            $this->log_warning('Attempted to create second instance - returning existing instance');
            return;
        }
        $this->log_file = dirname(__DIR__, 3) . '/logs/Nexora Service Suite-email-notifications.log';
        $this->ensure_log_directory();
        require_once dirname(__FILE__) . '/class-independent-email-system.php';
        $this->email_system = new Nexora_Independent_Email_System();
        $this->load_email_templates();
        add_action('plugins_loaded', array($this, 'setup_hooks'), 5);
        add_action('init', array($this, 'setup_hooks'), 1);
        $this->setup_hooks();
        $this->log_info('Nexora_Automatic_Email_Notifications constructor called - FIRST INSTANCE');
        error_log('Nexora_Automatic_Email_Notifications constructor called - FIRST INSTANCE');
        self::$instance = $this;
    }
    
    
    public static function get_instance() {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }
    
    
    private function ensure_log_directory() {
        $log_dir = dirname($this->log_file);
        if (!is_dir($log_dir)) {
            mkdir($log_dir, 0755, true);
        }
        $htaccess_file = $log_dir . '/.htaccess';
        if (!file_exists($htaccess_file)) {
            $htaccess_content = "Order deny,allow\nDeny from all\n";
            file_put_contents($htaccess_file, $htaccess_content);
        }
    }
    
    
    private function log_info($message) {
        $timestamp = date('Y-m-d H:i:s');
        $log_entry = "[$timestamp] [INFO] $message" . PHP_EOL;
        file_put_contents($this->log_file, $log_entry, FILE_APPEND | LOCK_EX);
    }
    
    
    private function log_warning($message) {
        $timestamp = date('Y-m-d H:i:s');
        $log_entry = "[$timestamp] [WARNING] $message" . PHP_EOL;
        file_put_contents($this->log_file, $log_entry, FILE_APPEND | LOCK_EX);
    }
    
    
    private function log_error($message) {
        $timestamp = date('Y-m-d H:i:s');
        $log_entry = "[$timestamp] [ERROR] $message" . PHP_EOL;
        file_put_contents($this->log_file, $log_entry, FILE_APPEND | LOCK_EX);
    }
    
    
    private function log_debug($message) {
        $timestamp = date('Y-m-d H:i:s');
        $log_entry = "[$timestamp] [DEBUG] $message" . PHP_EOL;
        file_put_contents($this->log_file, $log_entry, FILE_APPEND | LOCK_EX);
    }
    
    
    private function is_email_system_available() {
        try {
            if (!class_exists('PHPMailer\PHPMailer\PHPMailer')) {
                $phpmailer_paths = array(
                    dirname(__DIR__) . '/lib/PHPMailer/src/PHPMailer.php',
                    dirname(__DIR__, 3) . '/lib/phpmailer/PHPMailer.php',
                    dirname(__DIR__, 3) . '/vendor/phpmailer/phpmailer/src/PHPMailer.php',
                    '/usr/share/php/PHPMailer/PHPMailer.php'
                );
                
                $loaded = false;
                foreach ($phpmailer_paths as $path) {
                    if (file_exists($path)) {
                        require_once $path;
                        if (file_exists(dirname($path) . '/SMTP.php')) {
                            require_once dirname($path) . '/SMTP.php';
                        }
                        if (file_exists(dirname($path) . '/Exception.php')) {
                            require_once dirname($path) . '/Exception.php';
                        }
                        $loaded = true;
                        break;
                    }
                }
                
                if (!$loaded) {
                    return false;
                }
            }
            if (!$this->email_system) {
                return false;
            }
            $smtp_settings = $this->email_system->get_smtp_settings();
            if (empty($smtp_settings['enabled'])) {
                return false;
            }
            
            return true;
            
        } catch (Exception $e) {
            $this->log_error('Exception checking email system availability: ' . $e->getMessage());
            return false;
        }
    }
    
    
    public function setup_hooks() {
        if ($this->hooks_setup) {
            $this->log_info('Hooks already setup, skipping...');
            return;
        }
        
        $this->log_info('=== STARTING HOOK SETUP ===');
        add_action('nexora_service_request_created', array($this, 'notify_new_service_request'), 10, 2);
        $this->log_info('Hook nexora_service_request_created registered');
        if (has_action('nexora_service_request_created')) {
            $this->log_info('✅ VERIFICATION: nexora_service_request_created hook is registered');
            $this->log_info('✅ VERIFICATION: Callback method: ' . get_class($this) . '::notify_new_service_request');
        } else {
            $this->log_error('❌ VERIFICATION: nexora_service_request_created hook is NOT registered!');
        }
        add_action('nexora_service_status_changed', array($this, 'notify_service_status_change'), 10, 3);
        $this->log_info('Hook nexora_service_status_changed registered');
        add_action('nexora_customer_registered', array($this, 'notify_customer_registration'), 10, 1);
        $this->log_info('Hook nexora_customer_registered registered');
        add_action('nexora_invoice_generated', array($this, 'notify_invoice_generated'), 10, 2);
        $this->log_info('Hook nexora_invoice_generated registered');
        add_action('nexora_service_added', array($this, 'notify_service_added'), 10, 3);
        $this->log_info('Hook nexora_service_added registered with callback: ' . get_class($this) . '::notify_service_added');
        
        add_action('nexora_service_removed', array($this, 'notify_service_removed'), 10, 3);
        $this->log_info('Hook nexora_service_removed registered');
        
        add_action('nexora_service_quantity_changed', array($this, 'notify_service_quantity_changed'), 10, 4);
        $this->log_info('Hook nexora_service_quantity_changed registered');
        $this->hooks_setup = true;
        $this->verify_hook_registration();
        
        $this->log_info('=== HOOK SETUP COMPLETED ===');
        $this->test_hook_after_setup();
    }
    
    
    public function get_templates() {
        return $this->templates;
    }
    
    
    private function test_hook_after_setup() {
        $this->log_info('=== TESTING HOOK AFTER SETUP ===');
        $test_request_id = 999999;
        $test_user_id = 999999;
        
        $this->log_info("Testing hook execution with dummy data: request_id=$test_request_id, user_id=$test_user_id");
        do_action('nexora_service_request_created', $test_request_id, $test_user_id);
        
        $this->log_info('=== HOOK TEST AFTER SETUP COMPLETED ===');
    }
    
    
    private function verify_hook_registration() {
        global $wp_filter;
        
        $this->log_info('=== VERIFYING HOOK REGISTRATION ===');
        if (isset($wp_filter['nexora_service_added'])) {
            $callback_count = 0;
            foreach ($wp_filter['nexora_service_added']->callbacks as $priority => $callbacks) {
                $callback_count += count($callbacks);
            }
            $this->log_info("nexora_service_added hook has $callback_count callbacks");
            foreach ($wp_filter['nexora_service_added']->callbacks as $priority => $callbacks) {
                foreach ($callbacks as $callback_id => $callback) {
                    $this->log_info("  Priority $priority: $callback_id");
                }
            }
        } else {
            $this->log_error("nexora_service_added hook NOT found in wp_filter");
        }
        if (isset($wp_filter['nexora_service_status_changed'])) {
            $callback_count = 0;
            foreach ($wp_filter['nexora_service_status_changed']->callbacks as $priority => $callbacks) {
                $callback_count += count($callbacks);
            }
            $this->log_info("nexora_service_status_changed hook has $callback_count callbacks");
        } else {
            $this->log_error("nexora_service_status_changed hook NOT found in wp_filter");
        }
        
        $this->log_info('=== HOOK VERIFICATION COMPLETED ===');
    }
    
    
    private function load_email_templates() {
        global $wpdb;
        $table_name = $wpdb->prefix . 'nexora_email_templates';
        $table_exists = $wpdb->get_var("SHOW TABLES LIKE '$table_name'") == $table_name;
        if (!$table_exists) {
            $this->log_warning('Email templates table does not exist. Creating default templates...');
            $this->create_default_email_templates();
            return;
        }
        $templates = $wpdb->get_results("SELECT * FROM $table_name WHERE is_active = 1");
        
        $this->templates = array();
        foreach ($templates as $template) {
            $this->templates[$template->template_name] = $template;
        }
        
        $this->log_info('Loaded ' . count($this->templates) . ' email templates from database');
    }
    
    
    private function create_default_email_templates() {
        global $wpdb;
        $table_name = $wpdb->prefix . 'nexora_email_templates';
        $charset_collate = $wpdb->get_charset_collate();
        
        $sql = "CREATE TABLE $table_name (
            id INT NOT NULL AUTO_INCREMENT,
            template_name VARCHAR(100) NOT NULL,
            subject VARCHAR(255) NOT NULL,
            message TEXT NOT NULL,
            notification_type VARCHAR(50) NOT NULL,
            is_active TINYINT(1) DEFAULT 1,
            created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
            updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            PRIMARY KEY (id),
            UNIQUE KEY unique_template_name (template_name),
            KEY notification_type (notification_type),
            KEY is_active (is_active)
        ) $charset_collate;";
        
        $result = $wpdb->query($sql);
        
        if ($result !== false) {
            $this->log_info('Email templates table created successfully');
            $default_templates = [
                [
                    'template_name' => 'new_service_request',
                    'subject' => 'New service request #{request_id} received',
                    'message' => "Hello Admin,\n\nA new service request has been submitted:\n\nRequest ID: {request_id}\nCustomer: {customer_name}\nEmail: {customer_email}\nSerial: {serial}\nModel: {model}\nDescription: {description}\nBrand: {brand_level_1} / {brand_level_2} / {brand_level_3}\nServices: {services_list}\nCreated at: {created_date}\n\nAdmin link: {admin_url}\n\nBest regards,\nNexora Service Suite",
                    'notification_type' => 'service_request_created'
                ],
                [
                    'template_name' => 'customer_registration',
                    'subject' => 'New customer registration: {customer_name}',
                    'message' => "Hello Admin,\n\nA new customer has registered:\n\nName: {customer_name}\nEmail: {customer_email}\nRegistered at: {registration_date}\n\nAdmin link: {admin_url}\n\nBest regards,\nNexora Service Suite",
                    'notification_type' => 'customer_registration'
                ]
            ];
            
            foreach ($default_templates as $template) {
                $wpdb->insert(
                    $table_name,
                    $template,
                    ['%s', '%s', '%s', '%s']
                );
            }
            
            $this->log_info('Default email templates created successfully');
            $this->load_email_templates();
        } else {
            $this->log_error('Failed to create email templates table: ' . $wpdb->last_error);
        }
    }
    
    
    public function notify_new_service_request($request_id, $user_id) {
        try {
            if (!$this->is_email_system_available()) {
                $log_message = "WARNING: Email system not available, skipping email notifications for request #$request_id" . PHP_EOL;
                file_put_contents($this->log_file, $log_message, FILE_APPEND | LOCK_EX);
                return;
            }
            $log_file = WP_CONTENT_DIR . '/logs/Nexora Service Suite-email-notifications.log';
            $log_dir = dirname($log_file);
            if (!is_dir($log_dir)) {
                wp_mkdir_p($log_dir);
            }
            
            $log_message = "=== notify_new_service_request METHOD CALLED ===" . PHP_EOL;
            $log_message .= "Timestamp: " . date('Y-m-d H:i:s') . PHP_EOL;
            $log_message .= "Request ID: $request_id" . PHP_EOL;
            $log_message .= "User ID: $user_id" . PHP_EOL;
            $log_message .= "Memory Usage: " . memory_get_usage(true) . " bytes" . PHP_EOL;
            $log_message .= "===============================================" . PHP_EOL;
            
            file_put_contents($log_file, $log_message, FILE_APPEND | LOCK_EX);
            global $wpdb;
            $log_message = "Testing database connection..." . PHP_EOL;
            file_put_contents($log_file, $log_message, FILE_APPEND | LOCK_EX);
            
            if (!$wpdb) {
                $log_message = "ERROR: wpdb is null!" . PHP_EOL;
                file_put_contents($log_file, $log_message, FILE_APPEND | LOCK_EX);
                return;
            }
            
            $log_message = "Database connection OK" . PHP_EOL;
            file_put_contents($log_file, $log_message, FILE_APPEND | LOCK_EX);
            $log_message = "Getting user details for user_id: $user_id" . PHP_EOL;
            file_put_contents($log_file, $log_message, FILE_APPEND | LOCK_EX);
            if (!function_exists('get_userdata')) {
                $log_message = "ERROR: WordPress function get_userdata not available" . PHP_EOL;
                file_put_contents($log_file, $log_message, FILE_APPEND | LOCK_EX);
                return;
            }
            
            $user = get_userdata($user_id);
            if (!$user) {
                $log_message = "ERROR: Failed to get user data for user_id: $user_id" . PHP_EOL;
                file_put_contents($log_file, $log_message, FILE_APPEND | LOCK_EX);
                return;
            }
            
            $log_message = "User details retrieved successfully:" . PHP_EOL;
            $log_message .= "  - User ID: " . $user->ID . PHP_EOL;
            $log_message .= "  - Username: " . $user->user_login . PHP_EOL;
            $log_message .= "  - Display Name: " . $user->display_name . PHP_EOL;
            $log_message .= "  - Email: " . $user->user_email . PHP_EOL;
            file_put_contents($log_file, $log_message, FILE_APPEND | LOCK_EX);
            $log_message = "Getting service request details for request_id: $request_id" . PHP_EOL;
            file_put_contents($log_file, $log_message, FILE_APPEND | LOCK_EX);
            
            $table_name = $wpdb->prefix . 'nexora_service_requests';
            $log_message = "Table name: $table_name" . PHP_EOL;
            file_put_contents($log_file, $log_message, FILE_APPEND | LOCK_EX);
            $table_exists = $wpdb->get_var("SHOW TABLES LIKE '$table_name'") == $table_name;
            $log_message = "Table exists: " . ($table_exists ? 'YES' : 'NO') . PHP_EOL;
            file_put_contents($log_file, $log_message, FILE_APPEND | LOCK_EX);
            
            if (!$table_exists) {
                $log_message = "ERROR: Table $table_name does not exist!" . PHP_EOL;
                file_put_contents($log_file, $log_message, FILE_APPEND | LOCK_EX);
                return;
            }
            
            $request = $wpdb->get_row($wpdb->prepare(
                "SELECT * FROM {$wpdb->prefix}nexora_service_requests WHERE id = %d",
                $request_id
            ));
            
            if (!$request) {
                $log_message = "ERROR: Failed to get service request details for ID: $request_id" . PHP_EOL;
                $log_message .= "Database error: " . $wpdb->last_error . PHP_EOL;
                $log_message .= "Last query: " . $wpdb->last_query . PHP_EOL;
                file_put_contents($log_file, $log_message, FILE_APPEND | LOCK_EX);
                return;
            }
            
            $log_message = "Service request details retrieved successfully:" . PHP_EOL;
            $log_message .= "  - Request ID: " . $request->id . PHP_EOL;
            $log_message .= "  - User ID: " . $request->user_id . PHP_EOL;
            $log_message .= "  - Serial: " . $request->serial . PHP_EOL;
            $log_message .= "  - Model: " . $request->model . PHP_EOL;
            $log_message .= "  - Description: " . $request->description . PHP_EOL;
            $log_message .= "  - Brand L1 ID: " . $request->brand_level_1_id . PHP_EOL;
            $log_message .= "  - Brand L2 ID: " . $request->brand_level_2_id . PHP_EOL;
            $log_message .= "  - Brand L3 ID: " . $request->brand_level_3_id . PHP_EOL;
            $log_message .= "  - Created At: " . $request->created_at . PHP_EOL;
            file_put_contents($log_file, $log_message, FILE_APPEND | LOCK_EX);
            $log_message = "Getting brand information..." . PHP_EOL;
            file_put_contents($log_file, $log_message, FILE_APPEND | LOCK_EX);
            
            $brand_level_1 = $wpdb->get_var($wpdb->prepare(
                "SELECT name FROM {$wpdb->prefix}nexora_brands WHERE id = %d",
                $request->brand_level_1_id
            ));
            
            $brand_level_2 = $wpdb->get_var($wpdb->prepare(
                "SELECT name FROM {$wpdb->prefix}nexora_brands WHERE id = %d",
                $request->brand_level_2_id
            ));
            
            $brand_level_3 = $wpdb->get_var($wpdb->prepare(
                "SELECT name FROM {$wpdb->prefix}nexora_brands WHERE id = %d",
                $request->brand_level_3_id
            ));
            
            $log_message = "Brand information retrieved:" . PHP_EOL;
            $log_message .= "  - Brand L1: " . ($brand_level_1 ?: 'NULL') . PHP_EOL;
            $log_message .= "  - Brand L2: " . ($brand_level_2 ?: 'NULL') . PHP_EOL;
            $log_message .= "  - Brand L3: " . ($brand_level_3 ?: 'NULL') . PHP_EOL;
            file_put_contents($log_file, $log_message, FILE_APPEND | LOCK_EX);
            $log_message = "Getting services information..." . PHP_EOL;
            file_put_contents($log_file, $log_message, FILE_APPEND | LOCK_EX);
            
            $services = $wpdb->get_results($wpdb->prepare(
                "SELECT sd.*, s.title as service_title 
                 FROM {$wpdb->prefix}nexora_service_details sd
                 LEFT JOIN {$wpdb->prefix}nexora_services s ON sd.service_id = s.id
                 WHERE sd.request_id = %d",
                $request_id
            ));
            
            $log_message = "Services information retrieved: " . count($services) . " services found" . PHP_EOL;
            foreach ($services as $index => $service) {
                $log_message .= "  - Service " . ($index + 1) . ": " . ($service->service_title ?: 'Unknown') . " (Qty: " . $service->quantity . ")" . PHP_EOL;
            }
            file_put_contents($log_file, $log_message, FILE_APPEND | LOCK_EX);
            $log_message = "Getting email template..." . PHP_EOL;
            file_put_contents($log_file, $log_message, FILE_APPEND | LOCK_EX);
            
            $message = $this->get_new_service_request_template();
            $log_message = "Template retrieved, length: " . strlen($message) . " characters" . PHP_EOL;
            file_put_contents($log_file, $log_message, FILE_APPEND | LOCK_EX);
            
            if (empty($message)) {
                $log_message = "ERROR: Template is empty!" . PHP_EOL;
                file_put_contents($log_file, $log_message, FILE_APPEND | LOCK_EX);
                return;
            }
            $log_message = "Replacing placeholders in template..." . PHP_EOL;
            file_put_contents($log_file, $log_message, FILE_APPEND | LOCK_EX);
            
            $replacement_data = array(
                $request_id,
                $user->display_name ?: $user->user_login,
                $user->user_email,
                $request->serial,
                $request->model,
                $request->description ?: 'Keine Beschreibung',
                $brand_level_1 ?: 'Nicht angegeben',
                $brand_level_2 ?: 'Nicht angegeben',
                $brand_level_3 ?: 'Nicht angegeben',
                $this->format_services_list($services),
                date('d.m.Y H:i', strtotime($request->created_at)),
                admin_url("admin.php?page=Nexora Service Suite-service-request&action=edit&id=$request_id")
            );
            
            $log_message = "Replacement data prepared:" . PHP_EOL;
            $log_message .= "  - Request ID: " . $replacement_data[0] . PHP_EOL;
            $log_message .= "  - Customer Name: " . $replacement_data[1] . PHP_EOL;
            $log_message .= "  - Customer Email: " . $replacement_data[2] . PHP_EOL;
            $log_message .= "  - Serial: " . $replacement_data[3] . PHP_EOL;
            $log_message .= "  - Model: " . $replacement_data[4] . PHP_EOL;
            $log_message .= "  - Description: " . $replacement_data[5] . PHP_EOL;
            $log_message .= "  - Brand L1: " . $replacement_data[6] . PHP_EOL;
            $log_message .= "  - Brand L2: " . $replacement_data[7] . PHP_EOL;
            $log_message .= "  - Brand L3: " . $replacement_data[8] . PHP_EOL;
            $log_message .= "  - Services List: " . $replacement_data[9] . PHP_EOL;
            $log_message .= "  - Created Date: " . $replacement_data[10] . PHP_EOL;
            $log_message .= "  - Admin URL: " . $replacement_data[11] . PHP_EOL;
            file_put_contents($log_file, $log_message, FILE_APPEND | LOCK_EX);
            
            $message = str_replace(
                array(
                    '{request_id}',
                    '{customer_name}',
                    '{customer_email}',
                    '{serial}',
                    '{model}',
                    '{description}',
                    '{brand_level_1}',
                    '{brand_level_2}',
                    '{brand_level_3}',
                    '{services_list}',
                    '{created_date}',
                    '{admin_url}'
                ),
                $replacement_data,
                $message
            );
            
            $log_message = "Placeholders replaced successfully. Final message length: " . strlen($message) . " characters" . PHP_EOL;
            file_put_contents($log_file, $log_message, FILE_APPEND | LOCK_EX);
            $log_message = "Testing email system..." . PHP_EOL;
            file_put_contents($log_file, $log_message, FILE_APPEND | LOCK_EX);
            
            if (!$this->email_system) {
                $log_message = "ERROR: email_system is null!" . PHP_EOL;
                file_put_contents($log_file, $log_message, FILE_APPEND | LOCK_EX);
                return;
            }
            
            $log_message = "Email system exists: " . get_class($this->email_system) . PHP_EOL;
            file_put_contents($log_file, $log_message, FILE_APPEND | LOCK_EX);
            $subject = "New service request #$request_id received - " . ($user->display_name ?: $user->user_login);
            $log_message = "Preparing to send email with subject: $subject" . PHP_EOL;
            file_put_contents($log_file, $log_message, FILE_APPEND | LOCK_EX);
            
            $result = $this->email_system->send_admin_notification(
                $subject,
                $message,
                'service_request_created'
            );
            
            $log_message = "Email send result:" . PHP_EOL;
            $log_message .= "  - Success: " . ($result['success'] ? 'YES' : 'NO') . PHP_EOL;
            $log_message .= "  - Message: " . ($result['message'] ?? 'No message') . PHP_EOL;
            $log_message .= "  - Success Count: " . ($result['success_count'] ?? 'Unknown') . PHP_EOL;
            $log_message .= "  - Full Result: " . json_encode($result) . PHP_EOL;
            file_put_contents($log_file, $log_message, FILE_APPEND | LOCK_EX);
            
            if ($result['success']) {
                $log_message = "SUCCESS: Service request notification sent successfully for request #$request_id to " . $result['success_count'] . " recipients" . PHP_EOL;
                file_put_contents($log_file, $log_message, FILE_APPEND | LOCK_EX);
            } else {
                $log_message = "ERROR: Failed to send service request notification for request #$request_id: " . $result['message'] . PHP_EOL;
                file_put_contents($log_file, $log_message, FILE_APPEND | LOCK_EX);
            }
            $log_message = "=== notify_new_service_request METHOD COMPLETED SUCCESSFULLY ===" . PHP_EOL;
            $log_message .= "Timestamp: " . date('Y-m-d H:i:s') . PHP_EOL;
            $log_message .= "Memory Usage: " . memory_get_usage(true) . " bytes" . PHP_EOL;
            $log_message .= "===============================================" . PHP_EOL . PHP_EOL;
            file_put_contents($log_file, $log_message, FILE_APPEND | LOCK_EX);
            
        } catch (Exception $e) {
            $log_message = "EXCEPTION in notify_new_service_request:" . PHP_EOL;
            $log_message .= "  - Error: " . $e->getMessage() . PHP_EOL;
            $log_message .= "  - File: " . $e->getFile() . PHP_EOL;
            $log_message .= "  - Line: " . $e->getLine() . PHP_EOL;
            $log_message .= "  - Stack Trace: " . $e->getTraceAsString() . PHP_EOL;
            $log_message .= "===============================================" . PHP_EOL . PHP_EOL;
            file_put_contents($log_file, $log_message, FILE_APPEND | LOCK_EX);
            
            error_log("Exception in notify_new_service_request: " . $e->getMessage());
        }
    }
    
    
    public function notify_service_status_change($request_id, $old_status_id, $new_status_id) {
        try {
            if (!$this->is_email_system_available()) {
                error_log("WARNING: Email system not available, skipping status change notification for request #$request_id");
                return;
            }
            if (empty($new_status_id) || intval($old_status_id) === intval($new_status_id)) {
                error_log("Skip status change email: no real change for request #$request_id (old: $old_status_id, new: $new_status_id)");
                return;
            }
            
            global $wpdb;
            $request = $wpdb->get_row($wpdb->prepare(
                "SELECT * FROM {$wpdb->prefix}nexora_service_requests WHERE id = %d",
                $request_id
            ));
            
            if (!$request) {
                return;
            }
            $old_status = $wpdb->get_row($wpdb->prepare(
                "SELECT * FROM {$wpdb->prefix}nexora_service_status WHERE id = %d",
                $old_status_id
            ));
            
            $new_status = $wpdb->get_row($wpdb->prepare(
                "SELECT * FROM {$wpdb->prefix}nexora_service_status WHERE id = %d",
                $new_status_id
            ));
            if (!function_exists('get_userdata')) {
                error_log("ERROR: WordPress function get_userdata not available in notify_service_status_change");
                return;
            }
            $user = get_userdata($request->user_id);
            $subject = "Status update for service request #$request_id";
            
            $message = $this->get_status_change_template();
            $message = str_replace(
                array(
                    '{request_id}',
                    '{customer_name}',
                    '{old_status}',
                    '{new_status}',
                    '{serial}',
                    '{model}',
                    '{admin_url}'
                ),
                array(
                    $request_id,
                    $user ? ($user->display_name ?: $user->user_login) : 'Unbekannt',
                    $old_status ? $old_status->title : 'Unbekannt',
                    $new_status ? $new_status->title : 'Unbekannt',
                    $request->serial,
                    $request->model,
                    admin_url("admin.php?page=Nexora Service Suite-service-request&action=edit&id=$request_id")
                ),
                $message
            );
            $result = $this->email_system->send_admin_notification(
                $subject,
                $message,
                'service_status_change'
            );
            
            if ($result['success']) {
                error_log("Status change notification sent successfully for service request #$request_id to " . $result['success_count'] . " recipients");
            } else {
                error_log("Failed to send status change notification for service request #$request_id: " . $result['message']);
            }
            error_log("Attempting to send customer status change notification...");
            $customer_subject = "Status update for your service request #$request_id";
            $customer_message = $this->get_customer_status_change_template();
            $customer_message = str_replace(
                array(
                    '{request_id}',
                    '{customer_name}',
                    '{old_status}',
                    '{new_status}',
                    '{serial}',
                    '{model}',
                    '{customer_dashboard_url}'
                ),
                array(
                    $request_id,
                    $user ? ($user->display_name ?: $user->user_login) : 'Unbekannt',
                    $old_status ? $old_status->title : 'Unbekannt',
                    $new_status ? $new_status->title : 'Unbekannt',
                    $request->serial,
                    $request->model,
                    home_url('/dashboard')
                ),
                $customer_message
            );
            
            $customer_result = $this->email_system->send_customer_notification(
                $user->user_email,
                $user ? ($user->display_name ?: $user->user_login) : 'Unbekannt',
                $customer_subject,
                $customer_message,
                'service_status_change'
            );
            
            if ($customer_result['success']) {
                error_log("Customer status change notification sent successfully for service request #$request_id to: " . $user->user_email);
            } else {
                error_log("Failed to send customer status change notification for service request #$request_id: " . $customer_result['message']);
            }
            
        } catch (Exception $e) {
            error_log("Exception in notify_service_status_change: " . $e->getMessage());
        }
    }
    
    
    public function notify_customer_registration($user_id) {
        try {
            if (!function_exists('get_userdata')) {
                error_log("ERROR: WordPress function get_userdata not available in notify_customer_registration");
                return;
            }
            $user = get_userdata($user_id);
            if (!$user) {
                return;
            }
            
            $subject = "New customer registration: " . ($user->display_name ?: $user->user_login);
            
            $message = $this->get_customer_registration_template();
            $message = str_replace(
                array(
                    '{customer_name}',
                    '{customer_email}',
                    '{registration_date}',
                    '{admin_url}'
                ),
                array(
                    $user->display_name ?: $user->user_login,
                    $user->user_email,
                    date('d.m.Y H:i'),
                    admin_url("admin.php?page=Nexora Service Suite-users&action=edit&id=$user_id")
                ),
                $message
            );
            $result = $this->email_system->send_admin_notification(
                $subject,
                $message,
                'customer_registration'
            );
            
            if ($result['success']) {
                error_log("Customer registration notification sent successfully for user #$user_id to " . $result['success_count'] . " recipients");
            } else {
                error_log("Failed to send customer registration notification for user #$user_id: " . $result['message']);
            }
            
        } catch (Exception $e) {
            error_log("Exception in notify_customer_registration: " . $e->getMessage());
        }
    }
    
    
    public function notify_invoice_generated($invoice_id, $user_id) {
        try {
            if (!function_exists('get_userdata')) {
                error_log("ERROR: WordPress function get_userdata not available in notify_invoice_generated");
                return;
            }
            $user = get_userdata($user_id);
            if (!$user) {
                return;
            }
            
            $subject = "New invoice generated for " . ($user->display_name ?: $user->user_login);
            
            $message = $this->get_invoice_generated_template();
            $message = str_replace(
                array(
                    '{invoice_id}',
                    '{customer_name}',
                    '{customer_email}',
                    '{generation_date}',
                    '{admin_url}'
                ),
                array(
                    $invoice_id,
                    $user->display_name ?: $user->user_login,
                    $user->user_email,
                    date('d.m.Y H:i'),
                    admin_url("admin.php?page=Nexora Service Suite-invoices&action=edit&id=$invoice_id")
                ),
                $message
            );
            $result = $this->email_system->send_admin_notification(
                $subject,
                $message,
                'invoice_generated'
            );
            
            if ($result['success']) {
                error_log("Invoice generation notification sent successfully for invoice #$invoice_id to " . $result['success_count'] . " recipients");
            } else {
                error_log("Failed to send invoice generation notification for invoice #$invoice_id: " . $result['message']);
            }
            
        } catch (Exception $e) {
            error_log("Exception in notify_invoice_generated: " . $e->getMessage());
        }
    }
    
    
    public function notify_service_added($request_id, $service_id, $quantity) {
        $this->log_info("=== notify_service_added METHOD CALLED ===");
        $this->log_info("Request ID: $request_id, Service ID: $service_id, Quantity: $quantity");
        
        try {
            global $wpdb;
            $this->log_debug("Getting request details from database for ID: $request_id");
            $request = $wpdb->get_row($wpdb->prepare(
                "SELECT * FROM {$wpdb->prefix}nexora_service_requests WHERE id = %d",
                $request_id
            ));
            
            if (!$request) {
                $this->log_error("Failed to get service request details for ID: $request_id");
                return;
            }
            $this->log_info("Request details retrieved successfully: " . json_encode($request));
            $this->log_debug("Getting service details from database for ID: $service_id");
            $service = $wpdb->get_row($wpdb->prepare(
                "SELECT * FROM {$wpdb->prefix}nexora_services WHERE id = %d",
                $service_id
            ));
            
            if (!$service) {
                $this->log_error("Failed to get service details for ID: $service_id");
                return;
            }
            $this->log_info("Service details retrieved successfully: " . json_encode($service));
            $this->log_debug("Getting user details for user ID: " . $request->user_id);
            if (!function_exists('get_userdata')) {
                $this->log_error("ERROR: WordPress function get_userdata not available in notify_service_added");
                return;
            }
            $user = get_userdata($request->user_id);
            if ($user) {
                $this->log_info("User details retrieved successfully: " . json_encode($user));
            } else {
                $this->log_warning("Failed to get user details for user ID: " . $request->user_id);
            }
            $this->log_debug("Preparing admin email content");
            $subject = "Service added to service request #$request_id";
            $message = $this->get_service_added_template();
            $this->log_debug("Replacing placeholders in admin email");
            $message = str_replace(
                array(
                    '{request_id}',
                    '{customer_name}',
                    '{service_name}',
                    '{quantity}',
                    '{serial}',
                    '{model}',
                    '{admin_url}'
                ),
                array(
                    $request_id,
                    $user ? ($user->display_name ?: $user->user_login) : 'Unbekannt',
                    $service->title,
                    $quantity,
                    $request->serial,
                    $request->model,
                    admin_url("admin.php?page=Nexora Service Suite-service-request&action=edit&id=$request_id")
                ),
                $message
            );
            $this->log_info("Sending admin notification via email system");
            $result = $this->email_system->send_admin_notification(
                $subject,
                $message,
                'service_added'
            );
            
            if ($result['success']) {
                $this->log_info("Service added notification sent successfully for service request #$request_id to " . $result['success_count'] . " recipients");
            } else {
                $this->log_error("Failed to send service added notification for service request #$request_id: " . $result['message']);
            }
            $this->log_info("Preparing customer email content");
            $customer_subject = "Service added to your service request #$request_id";
            $customer_message = $this->get_customer_service_added_template();
            $this->log_debug("Replacing placeholders in customer email");
            $customer_message = str_replace(
                array(
                    '{request_id}',
                    '{customer_name}',
                    '{service_name}',
                    '{quantity}',
                    '{serial}',
                    '{model}',
                    '{customer_dashboard_url}'
                ),
                array(
                    $request_id,
                    $user ? ($user->display_name ?: $user->user_login) : 'Unbekannt',
                    $service->title,
                    $quantity,
                    $request->serial,
                    $request->model,
                    home_url('/dashboard')
                ),
                $customer_message
            );
            $this->log_info("Sending customer notification via email system");
            $customer_result = $this->email_system->send_customer_notification(
                $user->user_email,
                $user ? ($user->display_name ?: $user->user_login) : 'Unbekannt',
                $customer_subject,
                $customer_message,
                'service_added'
            );
            
            if ($customer_result['success']) {
                $this->log_info("Customer service added notification sent successfully for service request #$request_id to: " . $user->user_email);
            } else {
                $this->log_error("Failed to send customer service added notification for service request #$request_id: " . $customer_result['message']);
            }
            
            $this->log_info("=== notify_service_added METHOD COMPLETED SUCCESSFULLY ===");
            
        } catch (Exception $e) {
            $this->log_error("Exception in notify_service_added: " . $e->getMessage());
            $this->log_error("Stack trace: " . $e->getTraceAsString());
        }
    }
    
    
    public function notify_service_removed($request_id, $service_id, $quantity) {
        try {
            error_log("=== notify_service_removed called ===");
            error_log("Request ID: $request_id, Service ID: $service_id, Quantity: $quantity");
            
            global $wpdb;
            $request = $wpdb->get_row($wpdb->prepare(
                "SELECT * FROM {$wpdb->prefix}nexora_service_requests WHERE id = %d",
                $request_id
            ));
            
            if (!$request) {
                error_log("Failed to get service request details for ID: $request_id");
                return;
            }
            $service = $wpdb->get_row($wpdb->prepare(
                "SELECT * FROM {$wpdb->prefix}nexora_services WHERE id = %d",
                $service_id
            ));
            
            if (!$service) {
                error_log("Failed to get service details for ID: $service_id");
                return;
            }
            if (!function_exists('get_userdata')) {
                error_log("ERROR: WordPress function get_userdata not available in notify_service_removed");
                return;
            }
            $user = get_userdata($request->user_id);
            $subject = "Service removed from service request #$request_id";
            
            $message = $this->get_service_removed_template();
            $message = str_replace(
                array(
                    '{request_id}',
                    '{customer_name}',
                    '{service_name}',
                    '{quantity}',
                    '{serial}',
                    '{model}',
                    '{admin_url}'
                ),
                array(
                    $request_id,
                    $user ? ($user->display_name ?: $user->user_login) : 'Unbekannt',
                    $service->title,
                    $quantity,
                    $request->serial,
                    $request->model,
                    admin_url("admin.php?page=Nexora Service Suite-service-request&action=edit&id=$request_id")
                ),
                $message
            );
            $result = $this->email_system->send_admin_notification(
                $subject,
                $message,
                'service_removed'
            );
            
            if ($result['success']) {
                error_log("Service removed notification sent successfully for service request #$request_id to " . $result['success_count'] . " recipients");
            } else {
                error_log("Failed to send service removed notification for service request #$request_id: " . $result['message']);
            }
            error_log("Attempting to send customer service removed notification...");
            $customer_subject = "Service removed from your service request #$request_id";
            $customer_message = $this->get_customer_service_removed_template();
            $customer_message = str_replace(
                array(
                    '{request_id}',
                    '{customer_name}',
                    '{service_name}',
                    '{quantity}',
                    '{serial}',
                    '{model}',
                    '{customer_dashboard_url}'
                ),
                array(
                    $request_id,
                    $user ? ($user->display_name ?: $user->user_login) : 'Unbekannt',
                    $service->title,
                    $quantity,
                    $request->serial,
                    $request->model,
                    home_url('/dashboard')
                ),
                $customer_message
            );
            $customer_result = $this->email_system->send_customer_notification(
                $user->user_email,
                $user ? ($user->display_name ?: $user->user_login) : 'Unbekannt',
                $customer_subject,
                $customer_message,
                'service_removed'
            );
            
            if ($customer_result['success']) {
                error_log("Customer service removed notification sent successfully for service request #$request_id to: " . $user->user_email);
            } else {
                error_log("Failed to send customer service removed notification for service request #$request_id: " . $customer_result['message']);
            }
            
        } catch (Exception $e) {
            error_log("Exception in notify_service_removed: " . $e->getMessage());
        }
    }
    
    
    
    public function notify_service_quantity_changed($request_id, $service_id, $old_quantity, $new_quantity) {
        try {
            error_log("=== notify_service_quantity_changed called ===");
            error_log("Request ID: $request_id, Service ID: $service_id, Old Quantity: $old_quantity, New Quantity: $new_quantity");
            
            global $wpdb;
            $request = $wpdb->get_row($wpdb->prepare(
                "SELECT * FROM {$wpdb->prefix}nexora_service_requests WHERE id = %d",
                $request_id
            ));
            
            if (!$request) {
                error_log("Failed to get service request details for ID: $request_id");
                return;
            }
            $service = $wpdb->get_row($wpdb->prepare(
                "SELECT * FROM {$wpdb->prefix}nexora_services WHERE id = %d",
                $service_id
            ));
            
            if (!$service) {
                error_log("Failed to get service details for ID: $service_id");
                return;
            }
            if (!function_exists('get_userdata')) {
                error_log("ERROR: WordPress function get_userdata not available in notify_service_quantity_changed");
                return;
            }
            $user = get_userdata($request->user_id);
            $subject = "Service quantity changed for service request #$request_id";
            
            $message = $this->get_service_quantity_changed_template();
            $message = str_replace(
                array(
                    '{request_id}',
                    '{customer_name}',
                    '{service_name}',
                    '{old_quantity}',
                    '{new_quantity}',
                    '{serial}',
                    '{model}',
                    '{admin_url}'
                ),
                array(
                    $request_id,
                    $user ? ($user->display_name ?: $user->user_login) : 'Unbekannt',
                    $service->title,
                    $old_quantity,
                    $new_quantity,
                    $request->serial,
                    $request->model,
                    admin_url("admin.php?page=Nexora Service Suite-service-request&action=edit&id=$request_id")
                ),
                $message
            );
            $result = $this->email_system->send_admin_notification(
                $subject,
                $message,
                'service_quantity_changed'
            );
            
            if ($result['success']) {
                error_log("Service quantity change notification sent successfully for service request #$request_id to " . $result['success_count'] . " recipients");
            } else {
                error_log("Failed to send service quantity change notification for service request #$request_id: " . $result['message']);
            }
            error_log("Attempting to send customer service quantity change notification...");
            $customer_subject = "Service quantity changed for your service request #$request_id";
            $customer_message = $this->get_customer_service_quantity_changed_template();
            $customer_message = str_replace(
                array(
                    '{request_id}',
                    '{customer_name}',
                    '{service_name}',
                    '{old_quantity}',
                    '{new_quantity}',
                    '{serial}',
                    '{model}',
                    '{customer_dashboard_url}'
                ),
                array(
                    $request_id,
                    $user ? ($user->display_name ?: $user->user_login) : 'Unbekannt',
                    $service->title,
                    $old_quantity,
                    $new_quantity,
                    $request->serial,
                    $request->model,
                    home_url('/dashboard')
                ),
                $customer_message
            );
            $customer_result = $this->email_system->send_customer_notification(
                $user->user_email,
                $user ? ($user->display_name ?: $user->user_login) : 'Unbekannt',
                $customer_subject,
                $customer_message,
                'service_quantity_changed'
            );
            
            if ($customer_result['success']) {
                error_log("Customer service quantity change notification sent successfully for service request #$request_id to: " . $user->user_email);
            } else {
                error_log("Failed to send customer service quantity change notification for service request #$request_id: " . $customer_result['message']);
            }
            
        } catch (Exception $e) {
            error_log("Exception in notify_service_quantity_changed: " . $e->getMessage());
        }
    }
    
    
    private function format_services_list($services) {
        if (empty($services)) {
            return 'Keine Services ausgewählt';
        }
        
        $list = '<ul>';
        foreach ($services as $service) {
            $list .= '<li>';
            $list .= '<strong>' . htmlspecialchars($service->service_title ?: 'Unbekannter Service') . '</strong>';
            if ($service->quantity > 1) {
                $list .= ' (Menge: ' . $service->quantity . ')';
            }
            if (!empty($service->note)) {
                $list .= ' - Notiz: ' . htmlspecialchars($service->note);
            }
            $list .= '</li>';
        }
        $list .= '</ul>';
        
        return $list;
    }
    
    
    private function get_new_service_request_template() {
        if (isset($this->templates['new_service_request'])) {
            return $this->templates['new_service_request']->message;
        }
        global $wpdb;
        $table_name = $wpdb->prefix . 'nexora_email_templates';
        
        if ($wpdb->get_var("SHOW TABLES LIKE '$table_name'") == $table_name) {
            $template = $wpdb->get_row(
                $wpdb->prepare(
                    "SELECT * FROM $table_name WHERE template_name = %s AND is_active = 1",
                    'new_service_request'
                )
            );
            
            if ($template) {
                return $template->message;
            }
        }
        return '
        <!DOCTYPE html>
        <html lang="de" dir="ltr">
        <head>
            <meta charset="UTF-8">
            <meta name="viewport" content="width=device-width, initial-scale=1.0">
            <title>Neue Serviceanfrage - Nexora Service Suite Service</title>
            <style>
                body { 
                    margin: 0; 
                    padding: 0; 
                    font-family: \'Segoe UI\', Tahoma, Geneva, Verdana, sans-serif; 
                    background-color: #f8f9fa; 
                    line-height: 1.6; 
                }
                
                .email-container {
                    max-width: 650px;
                    margin: 0 auto;
                    background-color: #ffffff;
                    box-shadow: 0 4px 20px rgba(0, 0, 0, 0.1);
                    border-radius: 12px;
                    overflow: hidden;
                }
                
                .email-header {
                    background-color: #273269;
                    padding: 40px 30px;
                    text-align: center;
                }
                
                .logo {
                    margin-bottom: 25px;
                }
                
                .logo img {
                    max-width: 200px;
                    height: auto;
                    border-radius: 8px;
                }
                
                .header-subtitle {
                    color: #ffffff;
                    font-size: 18px;
                    font-weight: 500;
                    opacity: 0.95;
                }
                
                .email-content {
                    padding: 50px 40px;
                    line-height: 1.7;
                }
                
                .greeting {
                    margin-bottom: 35px;
                    text-align: center;
                }
                
                .greeting h2 {
                    color: #2c3e50;
                    margin: 0 0 25px 0;
                    font-size: 26px;
                    font-weight: 600;
                }
                
                .main-message {
                    margin-bottom: 35px;
                    line-height: 1.7;
                    color: #34495e;
                    font-size: 16px;
                    text-align: justify;
                }
                
                .service-request-info {
                    background: linear-gradient(135deg, #e8f4fd 0%, #d1ecf1 100%);
                    border: 2px solid #bee5eb;
                    border-radius: 12px;
                    padding: 25px;
                    margin: 25px 0;
                    text-align: center;
                }
                
                .service-request-info h3 {
                    color: #0c5460;
                    margin-top: 0;
                    margin-bottom: 20px;
                    font-size: 20px;
                    font-weight: 600;
                }
                
                .request-details {
                    display: grid;
                    grid-template-columns: 1fr 1fr;
                    gap: 20px;
                    margin-bottom: 25px;
                }
                
                .request-detail {
                    text-align: center;
                    padding: 15px;
                    background-color: rgba(255, 255, 255, 0.7);
                    border-radius: 8px;
                }
                
                .request-detail-label {
                    font-weight: 600;
                    color: #0c5460;
                    margin-bottom: 8px;
                    font-size: 14px;
                }
                
                .request-detail-value {
                    color: #495057;
                    font-weight: 500;
                    font-size: 16px;
                }
                
                .admin-link {
                    display: inline-block;
                    background: linear-gradient(135deg, #273269 0%, #34495e 100%);
                    color: #ffffff;
                    padding: 15px 30px;
                    text-decoration: none;
                    border-radius: 25px;
                    font-weight: 600;
                    margin-top: 20px;
                    transition: all 0.3s ease;
                    box-shadow: 0 4px 15px rgba(39, 50, 105, 0.3);
                }
                
                .admin-link:hover {
                    transform: translateY(-2px);
                    box-shadow: 0 6px 20px rgba(39, 50, 105, 0.4);
                }
                
                .email-footer {
                    background-color: #273269;
                    padding: 40px 30px;
                    text-align: center;
                    color: #ffffff;
                }
                
                .copyright {
                    font-size: 14px;
                    opacity: 0.9;
                    padding-top: 25px;
                    border-top: 1px solid rgba(255, 255, 255, 0.2);
                }
                
                @media (max-width: 600px) {
                    .email-container {
                        max-width: 100%;
                        border-radius: 0;
                    }
                    
                    .email-content {
                        padding: 30px 20px;
                    }
                    
                    .email-header,
                    .email-footer {
                        padding: 30px 20px;
                    }
                    
                    .request-details {
                        grid-template-columns: 1fr;
                    }
                }
            </style>
        </head>
        <body>
            <div class="email-container">
                
                
                <div class="email-header">
                    <div class="logo">
                        <img src="' . plugin_dir_url(dirname(__FILE__)) . 'assets/images/eccoripair.webp" alt="Nexora Service Suite">
                    </div>
                    <div class="header-subtitle">
                        Quality services at fair prices
                    </div>
                </div>
                
                
                <div class="email-content">
                    <div class="greeting">
                        <h2>🆕 New service request received!</h2>
                    </div>
                    
                    <div class="main-message">
                        <p>A new service request has been submitted in the system.</p>
                        
                        <p>Customer <strong>{customer_name}</strong> has created a new service request and is waiting for processing.</p>
                        
                        <p>Please review the request details and take appropriate action.</p>
                    </div>
                    
                    
                    <div class="service-request-info">
                        <h3>📋 Service request details</h3>
                        <div class="request-details">
                            <div class="request-detail">
                                <div class="request-detail-label">Request ID:</div>
                                <div class="request-detail-value">#{request_id}</div>
                            </div>
                            <div class="request-detail">
                                <div class="request-detail-label">Customer:</div>
                                <div class="request-detail-value">{customer_name}</div>
                            </div>
                            <div class="request-detail">
                                <div class="request-detail-label">E-Mail:</div>
                                <div class="request-detail-value">{customer_email}</div>
                            </div>
                            <div class="request-detail">
                                <div class="request-detail-label">Submitted at:</div>
                                <div class="request-detail-value">{created_date}</div>
                            </div>
                            <div class="request-detail">
                                <div class="request-detail-label">Serial:</div>
                                <div class="request-detail-value">{serial}</div>
                            </div>
                            <div class="request-detail">
                                <div class="request-detail-label">Model:</div>
                                <div class="request-detail-value">{model}</div>
                            </div>
                        </div>
                        <div style="margin-bottom: 20px;">
                            <div class="request-detail-label">Brand:</div>
                            <div class="request-detail-value">{brand_level_1} / {brand_level_2} / {brand_level_3}</div>
                        </div>
                        <div style="margin-bottom: 20px;">
                            <div class="request-detail-label">Beschreibung:</div>
                            <div class="request-detail-value">{description}</div>
                        </div>
                        <div style="margin-bottom: 20px;">
                            <div class="request-detail-label">Gewählte Services:</div>
                            <div class="request-detail-value">{services_list}</div>
                        </div>
                        <a href="{admin_url}" class="admin-link">
                            🚀 Anfrage bearbeiten
                        </a>
                    </div>
                </div>
                
                
                <div class="email-footer">
                    <div class="copyright">
                        © ' . date('Y') . ' Nexora. Alle Rechte vorbehalten.<br>
                        Diese E-Mail wurde automatisch generiert. Bitte antworten Sie nicht auf diese E-Mail.
                    </div>
                </div>
                
            </div>
        </body>
        </html>';
    }
    
    
    private function get_status_change_template() {
        if (isset($this->templates['service_status_change'])) {
            return $this->templates['service_status_change']->body_template;
        }
        return '
        <!DOCTYPE html>
        <html lang="de" dir="ltr">
        <head>
            <meta charset="UTF-8">
            <meta name="viewport" content="width=device-width, initial-scale=1.0">
            <title>Status-Änderung für Serviceanfrage</title>
            <style>
                body { 
                    margin: 0; 
                    padding: 0; 
                    font-family: \'Segoe UI\', Tahoma, Geneva, Verdana, sans-serif; 
                    background-color: #f8f9fa; 
                    line-height: 1.6; 
                }
                
                .email-container {
                    max-width: 650px;
                    margin: 0 auto;
                    background-color: #ffffff;
                    box-shadow: 0 4px 20px rgba(0, 0, 0, 0.1);
                    border-radius: 12px;
                    overflow: hidden;
                }
                
                .email-header {
                    background-color: #273269;
                    padding: 40px 30px;
                    text-align: center;
                }
                
                .logo {
                    margin-bottom: 25px;
                }
                
                .logo img {
                    max-width: 200px;
                    height: auto;
                    border-radius: 8px;
                }
                
                .header-subtitle {
                    color: #ffffff;
                    font-size: 18px;
                    font-weight: 500;
                    opacity: 0.95;
                }
                
                .email-content {
                    padding: 50px 40px;
                    line-height: 1.7;
                }
                
                .info-box {
                    background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);
                    border: 2px solid #dee2e6;
                    border-radius: 12px;
                    padding: 25px;
                    margin: 25px 0;
                    box-shadow: 0 2px 10px rgba(0, 0, 0, 0.05);
                }
                
                .info-box h3 {
                    color: #273269;
                    margin-top: 0;
                    margin-bottom: 20px;
                    font-size: 20px;
                    font-weight: 600;
                    text-align: center;
                }
                
                .info-list {
                    list-style: none;
                    padding: 0;
                    margin: 0;
                }
                
                .info-list li {
                    padding: 12px 0;
                    border-bottom: 1px solid #e9ecef;
                    display: flex;
                    justify-content: space-between;
                    align-items: center;
                }
                
                .info-list li:last-child {
                    border-bottom: none;
                }
                
                .info-label {
                    font-weight: 600;
                    color: #273269;
                    font-size: 15px;
                }
                
                .info-value {
                    color: #495057;
                    font-weight: 500;
                    font-size: 15px;
                }
                
                .status-badge {
                    display: inline-block;
                    padding: 8px 16px;
                    border-radius: 20px;
                    font-size: 14px;
                    font-weight: 600;
                    text-transform: uppercase;
                    color: #ffffff;
                    text-align: center;
                    min-width: 120px;
                }
                
                .status-badge.old {
                    background-color: #e74c3c;
                    border: 2px solid #c0392b;
                }
                
                .status-badge.new {
                    background-color: #27ae60;
                    border: 2px solid #229954;
                }
                
                .action-button {
                    display: inline-block;
                    background: linear-gradient(135deg, #273269 0%, #34495e 100%);
                    color: #ffffff;
                    padding: 18px 35px;
                    text-decoration: none;
                    border-radius: 30px;
                    font-weight: 600;
                    font-size: 16px;
                    margin: 25px 0;
                    transition: all 0.3s ease;
                    box-shadow: 0 4px 15px rgba(39, 50, 105, 0.3);
                }
                
                .action-button:hover {
                    transform: translateY(-3px);
                    box-shadow: 0 6px 20px rgba(39, 50, 105, 0.4);
                    background: linear-gradient(135deg, #34495e 0%, #273269 100%);
                }
                
                .email-footer {
                    background-color: #273269;
                    padding: 40px 30px;
                    text-align: center;
                    color: #ffffff;
                }
                
                .contact-info {
                    margin-bottom: 30px;
                }
                
                .contact-info h3 {
                    color: #ffffff;
                    margin: 0 0 20px 0;
                    font-size: 20px;
                    font-weight: 600;
                }
                
                .contact-grid {
                    display: grid;
                    grid-template-columns: 1fr 1fr;
                    gap: 20px;
                    margin-bottom: 25px;
                }
                
                .contact-item {
                    text-align: left;
                }
                
                .contact-item > div {
                    margin-bottom: 12px;
                    display: flex;
                    align-items: center;
                    gap: 10px;
                }
                
                .contact-item span:first-child {
                    font-size: 18px;
                }
                
                .contact-item span:last-child {
                    font-weight: 500;
                }
                
                .social-links {
                    margin-bottom: 30px;
                }
                
                .social-links h4 {
                    color: #ffffff;
                    margin: 0 0 20px 0;
                    font-size: 18px;
                    font-weight: 500;
                }
                
                .social-buttons {
                    display: flex;
                    justify-content: center;
                    gap: 25px;
                }
                
                .social-button {
                    color: #ffffff;
                    text-decoration: none;
                    padding: 12px 20px;
                    background-color: rgba(255, 255, 255, 0.1);
                    border-radius: 25px;
                    font-weight: 500;
                    transition: all 0.3s ease;
                    display: flex;
                    align-items: center;
                    gap: 8px;
                }
                
                .social-button:hover {
                    background-color: rgba(255, 255, 255, 0.2);
                    transform: translateY(-2px);
                }
                
                .copyright {
                    font-size: 14px;
                    opacity: 0.9;
                    padding-top: 25px;
                    border-top: 1px solid rgba(255, 255, 255, 0.2);
                }
                
                @media (max-width: 600px) {
                    .email-container {
                        max-width: 100%;
                        border-radius: 0;
                    }
                    
                    .email-content {
                        padding: 30px 20px;
                    }
                    
                    .email-header,
                    .email-footer {
                        padding: 30px 20px;
                    }
                    
                    .contact-grid {
                        grid-template-columns: 1fr;
                    }
                    
                    .social-buttons {
                        flex-direction: column;
                        gap: 15px;
                    }
                }
            </style>
        </head>
        <body>
            <div class="email-container">
                
                
                <div class="email-header">
                    <div class="logo">
                        <img src="' . plugin_dir_url(dirname(__FILE__)) . 'assets/images/eccoripair.webp" alt="Nexora Service Suite">
                    </div>
                    <div class="header-subtitle">
                        Qualitätsdienstleistungen zu fairen Preisen
                    </div>
                </div>
                
                
                <div class="email-content">
                    <div class="info-box">
                        <h3>🔄 Service-Statusänderung</h3>
                        <ul class="info-list">
                            <li>
                                <span class="info-label">Anfrage-ID:</span>
                                <span class="info-value">#{request_id}</span>
                            </li>
                            <li>
                                <span class="info-label">Kunde:</span>
                                <span class="info-value">{customer_name}</span>
                            </li>
                            <li>
                                <span class="info-label">Gerät:</span>
                                <span class="info-value">{serial} / {model}</span>
                            </li>
                        </ul>
                        
                        <div style="text-align: center; margin: 30px 0;">
                            <div style="margin-bottom: 20px;">
                                <div style="font-weight: 600; color: #273269; margin-bottom: 10px; font-size: 16px;">Vorheriger Status:</div>
                                <span class="status-badge old">{old_status}</span>
                            </div>
                            <div style="font-size: 24px; color: #273269; margin: 20px 0;">⬇️</div>
                            <div>
                                <div style="font-weight: 600; color: #273269; margin-bottom: 10px; font-size: 16px;">Neuer Status:</div>
                                <span class="status-badge new">{new_status}</span>
                            </div>
                        </div>
                    </div>
                    
                    
                    <div style="text-align: center; margin: 30px 0;">
                        <a href="{admin_url}" class="action-button">
                            🔍 Anfrage bearbeiten
                        </a>
                    </div>
                </div>
                
                
                <div class="email-footer">
                    <div class="contact-info">
                        <h3>📞 Kontaktinformationen</h3>
                        <div class="contact-grid">
                            <div class="contact-item">
                                <div>
                                    <span>📞</span>
                                    <span>+43 1 234 5678</span>
                                </div>
                                <div>
                                    <span>📧</span>
                                    <span>info@example.com</span>
                                </div>
                            </div>
                            <div class="contact-item">
                                <div>
                                    <span>🌐</span>
                                    <span>https://example.com</span>
                                </div>
                                <div>
                                    <span>📍</span>
                                    <span>Wien, Österreich</span>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="social-links">
                        <h4>📱 Folge uns auf Social Media</h4>
                        <div class="social-buttons">
                            <a href="#" class="social-button">
                                📷 Instagram
                            </a>
                            <a href="#" class="social-button">
                                📱 Telegram
                            </a>
                            <a href="#" class="social-button">
                                💬 WhatsApp
                            </a>
                        </div>
                    </div>
                    
                    <div class="copyright">
                        © ' . date('Y') . ' Nexora. Alle Rechte vorbehalten.
                    </div>
                </div>
                
            </div>
        </body>
        </html>';
    }
    
    
    private function get_customer_registration_template() {
        if (isset($this->templates['customer_registration'])) {
            return $this->templates['customer_registration']->body_template;
        }
        return '
        <!DOCTYPE html>
        <html lang="de" dir="ltr">
        <head>
            <meta charset="UTF-8">
            <meta name="viewport" content="width=device-width, initial-scale=1.0">
            <title>Willkommen bei Nexora Service Suite</title>
            <style>
                body { 
                    margin: 0; 
                    padding: 0; 
                    font-family: \'Segoe UI\', Tahoma, Geneva, Verdana, sans-serif; 
                    background-color: #f8f9fa; 
                    line-height: 1.6; 
                }
                
                .email-container {
                    max-width: 650px;
                    margin: 0 auto;
                    background-color: #ffffff;
                    box-shadow: 0 4px 20px rgba(0, 0, 0, 0.1);
                    border-radius: 12px;
                    overflow: hidden;
                }
                
                .email-header {
                    background-color: #273269;
                    padding: 40px 30px;
                    text-align: center;
                }
                
                .logo {
                    margin-bottom: 25px;
                }
                
                .logo img {
                    max-width: 200px;
                    height: auto;
                    border-radius: 8px;
                }
                
                .header-subtitle {
                    color: #ffffff;
                    font-size: 18px;
                    font-weight: 500;
                    opacity: 0.95;
                }
                
                .email-content {
                    padding: 50px 40px;
                    line-height: 1.7;
                }
                
                .greeting {
                    margin-bottom: 35px;
                    text-align: center;
                }
                
                .greeting h2 {
                    color: #2c3e50;
                    margin: 0 0 25px 0;
                    font-size: 26px;
                    font-weight: 600;
                }
                
                .main-message {
                    margin-bottom: 35px;
                    line-height: 1.7;
                    color: #34495e;
                    font-size: 16px;
                    text-align: justify;
                }
                
                .customer-account-info {
                    background: linear-gradient(135deg, #e8f4fd 0%, #d1ecf1 100%);
                    border: 2px solid #bee5eb;
                    border-radius: 12px;
                    padding: 25px;
                    margin: 25px 0;
                    text-align: center;
                }
                
                .customer-account-info h3 {
                    color: #0c5460;
                    margin-top: 0;
                    margin-bottom: 20px;
                    font-size: 20px;
                    font-weight: 600;
                }
                
                .account-details {
                    display: grid;
                    grid-template-columns: 1fr 1fr;
                    gap: 20px;
                    margin-bottom: 25px;
                }
                
                .account-detail {
                    text-align: center;
                    padding: 15px;
                    background-color: rgba(255, 255, 255, 0.7);
                    border-radius: 8px;
                }
                
                .account-detail-label {
                    font-weight: 600;
                    color: #0c5460;
                    margin-bottom: 8px;
                    font-size: 14px;
                }
                
                .account-detail-value {
                    color: #495057;
                    font-weight: 500;
                    font-size: 16px;
                }
                
                .dashboard-link {
                    display: inline-block;
                    background: linear-gradient(135deg, #273269 0%, #34495e 100%);
                    color: #ffffff;
                    padding: 15px 30px;
                    text-decoration: none;
                    border-radius: 25px;
                    font-weight: 600;
                    margin-top: 20px;
                    transition: all 0.3s ease;
                    box-shadow: 0 4px 15px rgba(39, 50, 105, 0.3);
                }
                
                .dashboard-link:hover {
                    transform: translateY(-2px);
                    box-shadow: 0 6px 20px rgba(39, 50, 105, 0.4);
                }
                
                .email-footer {
                    background-color: #273269;
                    padding: 40px 30px;
                    text-align: center;
                    color: #ffffff;
                }
                
                .contact-info {
                    margin-bottom: 30px;
                }
                
                .contact-info h3 {
                    color: #ffffff;
                    margin: 0 0 20px 0;
                    font-size: 20px;
                    font-weight: 600;
                }
                
                .contact-grid {
                    display: grid;
                    grid-template-columns: 1fr 1fr;
                    gap: 20px;
                    margin-bottom: 25px;
                }
                
                .contact-item {
                    text-align: left;
                }
                
                .contact-item > div {
                    margin-bottom: 12px;
                    display: flex;
                    align-items: center;
                    gap: 10px;
                }
                
                .contact-item span:first-child {
                    font-size: 18px;
                }
                
                .contact-item span:last-child {
                    font-weight: 500;
                }
                
                .social-links {
                    margin-bottom: 30px;
                }
                
                .social-links h4 {
                    color: #ffffff;
                    margin: 0 0 20px 0;
                    font-size: 18px;
                    font-weight: 500;
                }
                
                .social-buttons {
                    display: flex;
                    justify-content: center;
                    gap: 25px;
                }
                
                .social-button {
                    color: #ffffff;
                    text-decoration: none;
                    padding: 12px 20px;
                    background-color: rgba(255, 255, 255, 0.1);
                    border-radius: 25px;
                    font-weight: 500;
                    transition: all 0.3s ease;
                    display: flex;
                    align-items: center;
                    gap: 8px;
                }
                
                .social-button:hover {
                    background-color: rgba(255, 255, 255, 0.2);
                    transform: translateY(-2px);
                }
                
                .copyright {
                    font-size: 14px;
                    opacity: 0.9;
                    padding-top: 25px;
                    border-top: 1px solid rgba(255, 255, 255, 0.2);
                }
                
                @media (max-width: 600px) {
                    .email-container {
                        max-width: 100%;
                        border-radius: 0;
                    }
                    
                    .email-content {
                        padding: 30px 20px;
                    }
                    
                    .email-header,
                    .email-footer {
                        padding: 30px 20px;
                    }
                    
                    .contact-grid,
                    .account-details {
                        grid-template-columns: 1fr;
                    }
                    
                    .social-buttons {
                        flex-direction: column;
                        gap: 15px;
                    }
                }
            </style>
        </head>
        <body>
            <div class="email-container">
                
                
                <div class="email-header">
                    <div class="logo">
                        <img src="' . plugin_dir_url(dirname(__FILE__)) . 'assets/images/eccoripair.webp" alt="Nexora Service Suite">
                    </div>
                    <div class="header-subtitle">
                        Qualitätsdienstleistungen zu fairen Preisen
                    </div>
                </div>
                
                
                <div class="email-content">
                    <div class="greeting">
                        <h2>Willkommen bei Nexora Service Suite!</h2>
                    </div>
                    
                    <div class="main-message">
                        <p>Hallo {customer_name},</p>
                        
                        <p>Willkommen in der Nexora Service Suite Familie!</p>
                        
                        <p>Wir freuen uns, Sie als neuen Kunden begrüßen zu dürfen. Ihr Konto wurde erfolgreich erstellt und Sie können sich ab sofort in unserem System anmelden.</p>
                        
                        <p>Sie haben in unserem Webportal ein Benutzerkonto erstellt und können über den folgenden Link auf Ihr Dashboard zugreifen:</p>
                        
                        <p><strong>Dashboard-Link:</strong> <a href="https://example.com/my-account/">https://example.com/my-account/</a></p>
                        
                        <p>Bei Fragen oder Problemen stehen wir Ihnen gerne zur Verfügung.</p>
                        
                        <p>Vielen Dank für Ihr Vertrauen,<br>
                        Nexora Service Suite</p>
                    </div>
                    
                    
                    <div class="customer-account-info">
                        <h3>👋 Willkommen in der Nexora Service Suite Familie!</h3>
                        <p>Ihr Konto wurde erfolgreich erstellt und Sie können sich ab sofort in unserem System anmelden.</p>
                        <div class="account-details">
                            <div class="account-detail">
                                <div class="account-detail-label">Benutzername:</div>
                                <div class="account-detail-value">{customer_name}</div>
                            </div>
                            <div class="account-detail">
                                <div class="account-detail-label">E-Mail:</div>
                                <div class="account-detail-value">{customer_email}</div>
                            </div>
                        </div>
                        <a href="https://example.com/my-account/" class="dashboard-link">
                            🚀 Zu meinem Dashboard
                        </a>
                    </div>
                </div>
                
                
                <div class="email-footer">
                    <div class="contact-info">
                        <h3>📞 Kontaktinformationen</h3>
                        <div class="contact-grid">
                            <div class="contact-item">
                                <div>
                                    <span>📞</span>
                                    <span>+43 1 234 5678</span>
                                </div>
                                <div>
                                    <span>📧</span>
                                    <span>info@example.com</span>
                                </div>
                            </div>
                            <div class="contact-item">
                                <div>
                                    <span>🌐</span>
                                    <span>https://example.com</span>
                                </div>
                                <div>
                                    <span>📍</span>
                                    <span>Wien, Österreich</span>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="social-links">
                        <h4>📱 Folge uns auf Social Media</h4>
                        <div class="social-buttons">
                            <a href="#" class="social-button">
                                📷 Instagram
                            </a>
                            <a href="#" class="social-button">
                                📱 Telegram
                            </a>
                            <a href="#" class="social-button">
                                💬 WhatsApp
                            </a>
                        </div>
                    </div>
                    
                    <div class="copyright">
                        © ' . date('Y') . ' Nexora. Alle Rechte vorbehalten.
                    </div>
                </div>
                
            </div>
        </body>
        </html>';
    }
    
    
    private function get_invoice_generated_template() {
        if (isset($this->templates['invoice_generated'])) {
            return $this->templates['invoice_generated']->body_template;
        }
        return '
        <h2>Neue Rechnung generiert</h2>
        
        <p>Eine neue Rechnung wurde im System generiert.</p>
        
        <h3>Rechnungs-Details:</h3>
        <table style="border-collapse: collapse; width: 100%; margin: 20px 0;">
            <tr>
                <td style="padding: 8px; border: 1px solid #ddd; font-weight: bold; background: #f5f5f5;">Rechnungs-ID:</td>
                <td style="padding: 8px; border: 1px solid #ddd;">#{invoice_id}</td>
            </tr>
            <tr>
                <td style="padding: 8px; border: 1px solid #ddd; font-weight: bold; background: #f5f5f5;">Kunde:</td>
                <td style="padding: 8px; border: 1px solid #ddd;">{customer_name} ({customer_email})</td>
            </tr>
            <tr>
                <td style="padding: 8px; border: 1px solid #ddd; font-weight: bold; background: #f5f5f5;">Generiert am:</td>
                <td style="padding: 8px; border: 1px solid #ddd;">{generation_date}</td>
            </tr>
        </table>
        
        <p><strong>Admin-Link:</strong> <a href="{admin_url}">Rechnung bearbeiten</a></p>
        
        <p>Diese E-Mail wurde automatisch generiert. Bitte antworten Sie nicht auf diese E-Mail.</p>
        ';
    }
    
    
    private function get_service_added_template() {
        if (isset($this->templates['service_added'])) {
            return $this->templates['service_added']->body_template;
        }
        return '
        <h2>Service zu Serviceanfrage hinzugefügt</h2>
        
        <p>Ein neuer Service wurde zu einer Serviceanfrage hinzugefügt.</p>
        
        <h3>Details:</h3>
        <table style="border-collapse: collapse; width: 100%; margin: 20px 0;">
            <tr>
                <td style="padding: 8px; border: 1px solid #ddd; font-weight: bold; background: #f5f5f5;">Anfrage-ID:</td>
                <td style="padding: 8px; border: 1px solid #ddd;">#{request_id}</td>
            </tr>
            <tr>
                <td style="padding: 8px; border: 1px solid #ddd; font-weight: bold; background: #f5f5f5;">Kunde:</td>
                <td style="padding: 8px; border: 1px solid #ddd;">{customer_name}</td>
            </tr>
            <tr>
                <td style="padding: 8px; border: 1px solid #ddd; font-weight: bold; background: #f5f5f5;">Gerät:</td>
                <td style="padding: 8px; border: 1px solid #ddd;">{serial} / {model}</td>
            </tr>
            <tr>
                <td style="padding: 8px; border: 1px solid #ddd; font-weight: bold; background: #f5f5f5;">Hinzugefügter Service:</td>
                <td style="padding: 8px; border: 1px solid #ddd;">{service_name}</td>
            </tr>
            <tr>
                <td style="padding: 8px; border: 1px solid #ddd; font-weight: bold; background: #f5f5f5;">Menge:</td>
                <td style="padding: 8px; border: 1px solid #ddd;">{quantity}</td>
            </tr>
        </table>
        
        <p><strong>Admin-Link:</strong> <a href="{admin_url}">Anfrage bearbeiten</a></p>
        
        <p>Diese E-Mail wurde automatisch generiert. Bitte antworten Sie nicht auf diese E-Mail.</p>
        ';
    }
    
    
    private function get_service_removed_template() {
        if (isset($this->templates['service_removed'])) {
            return $this->templates['service_removed']->body_template;
        }
        return '
        <h2>Service von Serviceanfrage entfernt</h2>
        
        <p>Ein Service wurde von einer Serviceanfrage entfernt.</p>
        
        <h3>Details:</h3>
        <table style="border-collapse: collapse; width: 100%; margin: 20px 0;">
            <tr>
                <td style="padding: 8px; border: 1px solid #ddd; font-weight: bold; background: #f5f5f5;">Anfrage-ID:</td>
                <td style="padding: 8px; border: 1px solid #ddd;">#{request_id}</td>
            </tr>
            <tr>
                <td style="padding: 8px; border: 1px solid #ddd; font-weight: bold; background: #f5f5f5;">Kunde:</td>
                <td style="padding: 8px; border: 1px solid #ddd;">{customer_name}</td>
            </tr>
            <tr>
                <td style="padding: 8px; border: 1px solid #ddd; font-weight: bold; background: #f5f5f5;">Gerät:</td>
                <td style="padding: 8px; border: 1px solid #ddd;">{serial} / {model}</td>
            </tr>
            <tr>
                <td style="padding: 8px; border: 1px solid #ddd; font-weight: bold; background: #f5f5f5;">Entfernter Service:</td>
                <td style="padding: 8px; border: 1px solid #ddd;">{service_name}</td>
            </tr>
            <tr>
                <td style="padding: 8px; border: 1px solid #ddd; font-weight: bold; background: #f5f5f5;">Menge:</td>
                <td style="padding: 8px; border: 1px solid #ddd;">{quantity}</td>
            </tr>
        </table>
        
        <p><strong>Admin-Link:</strong> <a href="{admin_url}">Anfrage bearbeiten</a></p>
        
        <p>Diese E-Mail wurde automatisch generiert. Bitte antworten Sie nicht auf diese E-Mail.</p>
        ';
    }
    
    
    private function get_service_quantity_changed_template() {
        if (isset($this->templates['service_quantity_changed'])) {
            return $this->templates['service_quantity_changed']->body_template;
        }
        return '
        <h2>Service-Menge geändert</h2>
        
        <p>Die Menge eines Services wurde in einer Serviceanfrage geändert.</p>
        
        <h3>Details:</h3>
        <table style="border-collapse: collapse; width: 100%; margin: 20px 0;">
            <tr>
                <td style="padding: 8px; border: 1px solid #ddd; font-weight: bold; background: #f5f5f5;">Anfrage-ID:</td>
                <td style="padding: 8px; border: 1px solid #ddd;">#{request_id}</td>
            </tr>
            <tr>
                <td style="padding: 8px; border: 1px solid #ddd; font-weight: bold; background: #f5f5f5;">Kunde:</td>
                <td style="padding: 8px; border: 1px solid #ddd;">{customer_name}</td>
            </tr>
            <tr>
                <td style="padding: 8px; border: 1px solid #ddd; font-weight: bold; background: #f5f5f5;">Gerät:</td>
                <td style="padding: 8px; border: 1px solid #ddd;">{serial} / {model}</td>
            </tr>
            <tr>
                <td style="padding: 8px; border: 1px solid #ddd; font-weight: bold; background: #f5f5f5;">Service:</td>
                <td style="padding: 8px; border: 1px solid #ddd;">{service_name}</td>
            </tr>
            <tr>
                <td style="padding: 8px; border: 1px solid #ddd; font-weight: bold; background: #f5f5f5;">Alte Menge:</td>
                <td style="padding: 8px; border: 1px solid #ddd;">{old_quantity}</td>
            </tr>
            <tr>
                <td style="padding: 8px; border: 1px solid #ddd; font-weight: bold; background: #f5f5f5;">Neue Menge:</td>
                <td style="padding: 8px; border: 1px solid #ddd;">{new_quantity}</td>
            </tr>
        </table>
        
        <p><strong>Admin-Link:</strong> <a href="{admin_url}">Anfrage bearbeiten</a></p>
        
        <p>Diese E-Mail wurde automatisch generiert. Bitte antworten Sie nicht auf diese E-Mail.</p>
        ';
    }
    
    
    private function get_customer_service_request_created_template() {
        if (isset($this->templates['customer_service_request_created'])) {
            return $this->templates['customer_service_request_created']->body_template;
        }
        return '
        <h2>Ihre Serviceanfrage wurde erfolgreich erstellt</h2>
        
        <p>Sehr geehrte(r) {customer_name},</p>
        
        <p>Vielen Dank für Ihre Serviceanfrage. Diese wurde erfolgreich in unserem System registriert.</p>
        
        <h3>Details Ihrer Anfrage:</h3>
        <table style="border-collapse: collapse; width: 100%; margin: 20px 0;">
            <tr>
                <td style="padding: 8px; border: 1px solid #ddd; font-weight: bold; background: #f5f5f5;">Anfrage-ID:</td>
                <td style="padding: 8px; border: 1px solid #ddd;">#{request_id}</td>
            </tr>
            <tr>
                <td style="padding: 8px; border: 1px solid #ddd; font-weight: bold; background: #f5f5f5;">Gerät:</td>
                <td style="padding: 8px; border: 1px solid #ddd;">{serial} / {model}</td>
            </tr>
            <tr>
                <td style="padding: 8px; border: 1px solid #ddd; font-weight: bold; background: #f5f5f5;">Beschreibung:</td>
                <td style="padding: 8px; border: 1px solid #ddd;">{description}</td>
            </tr>
            <tr>
                <td style="padding: 8px; border: 1px solid #ddd; font-weight: bold; background: #f5f5f5;">Marke:</td>
                <td style="padding: 8px; border: 1px solid #ddd;">{brand_level_1} {brand_level_2} {brand_level_3}</td>
            </tr>
            <tr>
                <td style="padding: 8px; border: 1px solid #ddd; font-weight: bold; background: #f5f5f5;">Gewählte Services:</td>
                <td style="padding: 8px; border: 1px solid #ddd;">{services_list}</td>
            </tr>
            <tr>
                <td style="padding: 8px; border: 1px solid #ddd; font-weight: bold; background: #f5f5f5;">Erstellt am:</td>
                <td style="padding: 8px; border: 1px solid #ddd;">{created_date}</td>
            </tr>
        </table>
        
        <p>Wir werden uns in Kürze mit Ihnen in Verbindung setzen, um den nächsten Schritt zu besprechen.</p>
        
        <p><strong>Dashboard-Link:</strong> <a href="{customer_dashboard_url}">Ihr Dashboard aufrufen</a></p>
        
        <p>Bei Fragen stehen wir Ihnen gerne zur Verfügung.</p>
        
        <p>Mit freundlichen Grüßen<br>
        Ihr Nexora Service Suite Team</p>
        
        <p>Diese E-Mail wurde automatisch generiert. Bitte antworten Sie nicht auf diese E-Mail.</p>
        ';
    }
    
    
    private function get_customer_status_change_template() {
        if (isset($this->templates['customer_status_change'])) {
            return $this->templates['customer_status_change']->body_template;
        }
        return '
        <!DOCTYPE html>
        <html lang="de" dir="ltr">
        <head>
            <meta charset="UTF-8">
            <meta name="viewport" content="width=device-width, initial-scale=1.0">
            <title>Status-Änderung für Ihre Serviceanfrage</title>
            <style>
                body { 
                    margin: 0; 
                    padding: 0; 
                    font-family: \'Segoe UI\', Tahoma, Geneva, Verdana, sans-serif; 
                    background-color: #f8f9fa; 
                    line-height: 1.6; 
                }
                
                .email-container {
                    max-width: 650px;
                    margin: 0 auto;
                    background-color: #ffffff;
                    box-shadow: 0 4px 20px rgba(0, 0, 0, 0.1);
                    border-radius: 12px;
                    overflow: hidden;
                }
                
                .email-header {
                    background-color: #273269;
                    padding: 40px 30px;
                    text-align: center;
                }
                
                .logo {
                    margin-bottom: 25px;
                }
                
                .logo img {
                    max-width: 200px;
                    height: auto;
                    border-radius: 8px;
                }
                
                .header-subtitle {
                    color: #ffffff;
                    font-size: 18px;
                    font-weight: 500;
                    opacity: 0.95;
                }
                
                .email-content {
                    padding: 50px 40px;
                    line-height: 1.7;
                }
                
                .greeting {
                    margin-bottom: 35px;
                    text-align: center;
                }
                
                .greeting h2 {
                    color: #2c3e50;
                    margin: 0 0 25px 0;
                    font-size: 26px;
                    font-weight: 600;
                }
                
                .main-message {
                    margin-bottom: 35px;
                    line-height: 1.7;
                    color: #34495e;
                    font-size: 16px;
                    text-align: justify;
                }
                
                .info-box {
                    background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);
                    border: 2px solid #dee2e6;
                    border-radius: 12px;
                    padding: 25px;
                    margin: 25px 0;
                    box-shadow: 0 2px 10px rgba(0, 0, 0, 0.05);
                }
                
                .info-box h3 {
                    color: #273269;
                    margin-top: 0;
                    margin-bottom: 20px;
                    font-size: 20px;
                    font-weight: 600;
                    text-align: center;
                }
                
                .info-list {
                    list-style: none;
                    padding: 0;
                    margin: 0;
                }
                
                .info-list li {
                    padding: 12px 0;
                    border-bottom: 1px solid #e9ecef;
                    display: flex;
                    justify-content: space-between;
                    align-items: center;
                }
                
                .info-list li:last-child {
                    border-bottom: none;
                }
                
                .info-label {
                    font-weight: 600;
                    color: #273269;
                    font-size: 15px;
                }
                
                .info-value {
                    color: #495057;
                    font-weight: 500;
                    font-size: 15px;
                }
                
                .status-badge {
                    display: inline-block;
                    padding: 8px 16px;
                    border-radius: 20px;
                    font-size: 14px;
                    font-weight: 600;
                    text-transform: uppercase;
                    color: #ffffff;
                    text-align: center;
                    min-width: 120px;
                }
                
                .status-badge.old {
                    background-color: #e74c3c;
                    border: 2px solid #c0392b;
                }
                
                .status-badge.new {
                    background-color: #27ae60;
                    border: 2px solid #229954;
                }
                
                .action-button {
                    display: inline-block;
                    background: linear-gradient(135deg, #273269 0%, #34495e 100%);
                    color: #ffffff;
                    padding: 18px 35px;
                    text-decoration: none;
                    border-radius: 30px;
                    font-weight: 600;
                    font-size: 16px;
                    margin: 25px 0;
                    transition: all 0.3s ease;
                    box-shadow: 0 4px 15px rgba(39, 50, 105, 0.3);
                }
                
                .action-button:hover {
                    transform: translateY(-3px);
                    box-shadow: 0 6px 20px rgba(39, 50, 105, 0.4);
                    background: linear-gradient(135deg, #34495e 0%, #273269 100%);
                }
                
                .email-footer {
                    background-color: #273269;
                    padding: 40px 30px;
                    text-align: center;
                    color: #ffffff;
                }
                
                .contact-info {
                    margin-bottom: 30px;
                }
                
                .contact-info h3 {
                    color: #ffffff;
                    margin: 0 0 20px 0;
                    font-size: 20px;
                    font-weight: 600;
                }
                
                .contact-grid {
                    display: grid;
                    grid-template-columns: 1fr 1fr;
                    gap: 20px;
                    margin-bottom: 25px;
                }
                
                .contact-item {
                    text-align: left;
                }
                
                .contact-item > div {
                    margin-bottom: 12px;
                    display: flex;
                    align-items: center;
                    gap: 10px;
                }
                
                .contact-item span:first-child {
                    font-size: 18px;
                }
                
                .contact-item span:last-child {
                    font-weight: 500;
                }
                
                .social-links {
                    margin-bottom: 30px;
                }
                
                .social-links h4 {
                    color: #ffffff;
                    margin: 0 0 20px 0;
                    font-size: 18px;
                    font-weight: 500;
                }
                
                .social-buttons {
                    display: flex;
                    justify-content: center;
                    gap: 25px;
                }
                
                .social-button {
                    color: #ffffff;
                    text-decoration: none;
                    padding: 12px 20px;
                    background-color: rgba(255, 255, 255, 0.1);
                    border-radius: 25px;
                    font-weight: 500;
                    transition: all 0.3s ease;
                    display: flex;
                    align-items: center;
                    gap: 8px;
                }
                
                .social-button:hover {
                    background-color: rgba(255, 255, 255, 0.2);
                    transform: translateY(-2px);
                }
                
                .copyright {
                    font-size: 14px;
                    opacity: 0.9;
                    padding-top: 25px;
                    border-top: 1px solid rgba(255, 255, 255, 0.2);
                }
                
                @media (max-width: 600px) {
                    .email-container {
                        max-width: 100%;
                        border-radius: 0;
                    }
                    
                    .email-content {
                        padding: 30px 20px;
                    }
                    
                    .email-header,
                    .email-footer {
                        padding: 30px 20px;
                    }
                    
                    .contact-grid {
                        grid-template-columns: 1fr;
                    }
                    
                    .social-buttons {
                        flex-direction: column;
                        gap: 15px;
                    }
                }
            </style>
        </head>
        <body>
            <div class="email-container">
                
                
                <div class="email-header">
                    <div class="logo">
                        <img src="' . plugin_dir_url(dirname(__FILE__)) . 'assets/images/eccoripair.webp" alt="Nexora Service Suite">
                    </div>
                    <div class="header-subtitle">
                        Qualitätsdienstleistungen zu fairen Preisen
                    </div>
                </div>
                
                
                <div class="email-content">
                    <div class="greeting">
        <h2>Status-Änderung für Ihre Serviceanfrage</h2>
                    </div>
        
                    <div class="main-message">
        <p>Sehr geehrte(r) {customer_name},</p>
        
        <p>Der Status Ihrer Serviceanfrage hat sich geändert.</p>
        
        <p>Wir arbeiten kontinuierlich an Ihrer Anfrage und halten Sie über alle wichtigen Entwicklungen auf dem Laufenden.</p>
                    </div>
                    
                    <div class="info-box">
                        <h3>🔄 Status-Update</h3>
                        <ul class="info-list">
                            <li>
                                <span class="info-label">Anfrage-ID:</span>
                                <span class="info-value">#{request_id}</span>
                            </li>
                            <li>
                                <span class="info-label">Gerät:</span>
                                <span class="info-value">{serial} / {model}</span>
                            </li>
                        </ul>
                        
                        <div style="text-align: center; margin: 30px 0;">
                            <div style="margin-bottom: 20px;">
                                <div style="font-weight: 600; color: #273269; margin-bottom: 10px; font-size: 16px;">Vorheriger Status:</div>
                                <span class="status-badge old">{old_status}</span>
                            </div>
                            <div style="font-size: 24px; color: #273269; margin: 20px 0;">⬇️</div>
                            <div>
                                <div style="font-weight: 600; color: #273269; margin-bottom: 10px; font-size: 16px;">Neuer Status:</div>
                                <span class="status-badge new">{new_status}</span>
                            </div>
                        </div>
                    </div>
                    
                    
                    <div style="text-align: center; margin: 30px 0;">
                        <a href="{customer_dashboard_url}" class="action-button">
                            🚀 Ihr Dashboard aufrufen
                        </a>
                    </div>
                    
                    <div style="text-align: center; margin: 30px 0; color: #7f8c8d; font-style: italic;">
                        Bei Fragen stehen wir Ihnen gerne zur Verfügung.
                    </div>
                </div>
                
                
                <div class="email-footer">
                    <div class="contact-info">
                        <h3>📞 Kontaktinformationen</h3>
                        <div class="contact-grid">
                            <div class="contact-item">
                                <div>
                                    <span>📞</span>
                                    <span>+43 1 234 5678</span>
                                </div>
                                <div>
                                    <span>📧</span>
                                    <span>info@example.com</span>
                                </div>
                            </div>
                            <div class="contact-item">
                                <div>
                                    <span>🌐</span>
                                    <span>https://example.com</span>
                                </div>
                                <div>
                                    <span>📍</span>
                                    <span>Wien, Österreich</span>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="social-links">
                        <h4>📱 Folge uns auf Social Media</h4>
                        <div class="social-buttons">
                            <a href="#" class="social-button">
                                📷 Instagram
                            </a>
                            <a href="#" class="social-button">
                                📱 Telegram
                            </a>
                            <a href="#" class="social-button">
                                💬 WhatsApp
                            </a>
                        </div>
                    </div>
                    
                    <div class="copyright">
                        © ' . date('Y') . ' Nexora. Alle Rechte vorbehalten.
                    </div>
                </div>
                
            </div>
        </body>
        </html>';
    }
    
    
    private function get_customer_service_added_template() {
        if (isset($this->templates['customer_service_added'])) {
            return $this->templates['customer_service_added']->body_template;
        }
        return '
        <h2>Service zu Ihrer Anfrage hinzugefügt</h2>
        
        <p>Sehr geehrte(r) {customer_name},</p>
        
        <p>Ein neuer Service wurde zu Ihrer Serviceanfrage hinzugefügt.</p>
        
        <h3>Service-Details:</h3>
        <table style="border-collapse: collapse; width: 100%; margin: 20px 0;">
            <tr>
                <td style="padding: 8px; border: 1px solid #ddd; font-weight: bold; background: #f5f5f5;">Anfrage-ID:</td>
                <td style="padding: 8px; border: 1px solid #ddd;">#{request_id}</td>
            </tr>
            <tr>
                <td style="padding: 8px; border: 1px solid #ddd; font-weight: bold; background: #f5f5f5;">Gerät:</td>
                <td style="padding: 8px; border: 1px solid #ddd;">{serial} / {model}</td>
            </tr>
            <tr>
                <td style="padding: 8px; border: 1px solid #ddd; font-weight: bold; background: #f5f5f5;">Hinzugefügter Service:</td>
                <td style="padding: 8px; border: 1px solid #ddd;">{service_name}</td>
            </tr>
            <tr>
                <td style="padding: 8px; border: 1px solid #ddd; font-weight: bold; background: #f5f5f5;">Menge:</td>
                <td style="padding: 8px; border: 1px solid #ddd;">{quantity}</td>
            </tr>
        </table>
        
        <p>Dieser Service wurde nach sorgfältiger Prüfung Ihrer Anfrage hinzugefügt, um Ihnen den bestmöglichen Service zu bieten.</p>
        
        <p><strong>Dashboard-Link:</strong> <a href="{customer_dashboard_url}">Ihr Dashboard aufrufen</a></p>
        
        <p>Bei Fragen stehen wir Ihnen gerne zur Verfügung.</p>
        
        <p>Mit freundlichen Grüßen<br>
        Ihr Nexora Service Suite Team</p>
        
        <p>Diese E-Mail wurde automatisch generiert. Bitte antworten Sie nicht auf diese E-Mail.</p>
        ';
    }
    
    
    private function get_customer_service_removed_template() {
        if (isset($this->templates['customer_service_removed'])) {
            return $this->templates['customer_service_removed']->body_template;
        }
        return '
        <h2>Service von Ihrer Anfrage entfernt</h2>
        
        <p>Sehr geehrte(r) {customer_name},</p>
        
        <p>Ein Service wurde von Ihrer Serviceanfrage entfernt.</p>
        
        <h3>Service-Details:</h3>
        <table style="border-collapse: collapse; width: 100%; margin: 20px 0;">
            <tr>
                <td style="padding: 8px; border: 1px solid #ddd; font-weight: bold; background: #f5f5f5;">Anfrage-ID:</td>
                <td style="padding: 8px; border: 1px solid #ddd;">#{request_id}</td>
            </tr>
            <tr>
                <td style="padding: 8px; border: 1px solid #ddd; font-weight: bold; background: #f5f5f5;">Gerät:</td>
                <td style="padding: 8px; border: 1px solid #ddd;">{serial} / {model}</td>
            </tr>
            <tr>
                <td style="padding: 8px; border: 1px solid #ddd; font-weight: bold; background: #f5f5f5;">Entfernter Service:</td>
                <td style="padding: 8px; border: 1px solid #ddd;">{service_name}</td>
            </tr>
            <tr>
                <td style="padding: 8px; border: 1px solid #ddd; font-weight: bold; background: #f5f5f5;">Menge:</td>
                <td style="padding: 8px; border: 1px solid #ddd;">{quantity}</td>
            </tr>
        </table>
        
        <p>Dieser Service wurde nach sorgfältiger Prüfung von Ihrer Anfrage entfernt. Falls Sie Fragen dazu haben, kontaktieren Sie uns gerne.</p>
        
        <p><strong>Dashboard-Link:</strong> <a href="{customer_dashboard_url}">Ihr Dashboard aufrufen</a></p>
        
        <p>Bei Fragen stehen wir Ihnen gerne zur Verfügung.</p>
        
        <p>Mit freundlichen Grüßen<br>
        Ihr Nexora Service Suite Team</p>
        
        <p>Diese E-Mail wurde automatisch generiert. Bitte antworten Sie nicht auf diese E-Mail.</p>
        ';
    }
    
    
    private function get_customer_service_quantity_changed_template() {
        if (isset($this->templates['customer_service_quantity_changed'])) {
            return $this->templates['customer_service_quantity_changed']->body_template;
        }
        return '
        <h2>Service-Menge geändert</h2>
        
        <p>Sehr geehrte(r) {customer_name},</p>
        
        <p>Die Menge eines Services in Ihrer Anfrage wurde geändert.</p>
        
        <h3>Änderungs-Details:</h3>
        <table style="border-collapse: collapse; width: 100%; margin: 20px 0;">
            <tr>
                <td style="padding: 8px; border: 1px solid #ddd; font-weight: bold; background: #f5f5f5;">Anfrage-ID:</td>
                <td style="padding: 8px; border: 1px solid #ddd;">#{request_id}</td>
            </tr>
            <tr>
                <td style="padding: 8px; border: 1px solid #ddd; font-weight: bold; background: #f5f5f5;">Gerät:</td>
                <td style="padding: 8px; border: 1px solid #ddd;">{serial} / {model}</td>
            </tr>
            <tr>
                <td style="padding: 8px; border: 1px solid #ddd; font-weight: bold; background: #f5f5f5;">Service:</td>
                <td style="padding: 8px; border: 1px solid #ddd;">{service_name}</td>
            </tr>
            <tr>
                <td style="padding: 8px; border: 1px solid #ddd; font-weight: bold; background: #f5f5f5;">Vorherige Menge:</td>
                <td style="padding: 8px; border: 1px solid #ddd;">{old_quantity}</td>
            </tr>
            <tr>
                <td style="padding: 8px; border: 1px solid #ddd; font-weight: bold; background: #f5f5f5;">Neue Menge:</td>
                <td style="padding: 8px; border: 1px solid #ddd;">{new_quantity}</td>
            </tr>
        </table>
        
        <p>Diese Änderung wurde nach sorgfältiger Prüfung Ihrer Anfrage vorgenommen, um Ihnen den bestmöglichen Service zu bieten.</p>
        
        <p><strong>Dashboard-Link:</strong> <a href="{customer_dashboard_url}">Ihr Dashboard aufrufen</a></p>
        
        <p>Bei Fragen stehen wir Ihnen gerne zur Verfügung.</p>
        
        <p>Mit freundlichen Grüßen<br>
        Ihr Nexora Service Suite Team</p>
        
        <p>Diese E-Mail wurde automatisch generiert. Bitte antworten Sie nicht auf diese E-Mail.</p>
        ';
    }
}
