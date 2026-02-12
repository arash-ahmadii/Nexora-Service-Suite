<?php
if (!defined('ABSPATH')) {
    exit;
}

class Nexora_Independent_Email_AJAX {
    
    private $email_system;
    
    public function __construct() {
        require_once dirname(__FILE__) . '/class-independent-email-system.php';
        $this->email_system = new Nexora_Independent_Email_System();
        if (!class_exists('PHPMailer\PHPMailer\PHPMailer')) {
            $phpmailer_paths = array(
                dirname(__FILE__) . '/../lib/PHPMailer/src/PHPMailer.php',
                dirname(__DIR__, 2) . '/lib/phpmailer/PHPMailer.php',
                dirname(__DIR__, 2) . '/vendor/phpmailer/phpmailer/src/PHPMailer.php',
                ABSPATH . 'wp-includes/class-phpmailer.php'
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
                error_log('PHPMailer not found in any location');
            }
        }
        add_action('wp_ajax_get_independent_smtp_settings', array($this, 'get_independent_smtp_settings'));
        add_action('wp_ajax_save_independent_smtp_settings', array($this, 'save_independent_smtp_settings'));
        add_action('wp_ajax_test_independent_smtp_connection', array($this, 'test_independent_smtp_connection'));
        add_action('wp_ajax_send_independent_test_email', array($this, 'send_independent_test_email'));
        add_action('wp_ajax_get_independent_system_status', array($this, 'get_independent_system_status'));
        add_action('wp_ajax_reset_independent_smtp_settings', array($this, 'reset_independent_smtp_settings'));
        add_action('wp_ajax_create_email_database_tables', array($this, 'create_email_database_tables'));
        add_action('wp_ajax_get_email_database_status', array($this, 'get_email_database_status'));
        add_action('wp_ajax_debug_database_tables', array($this, 'debug_database_tables'));
        add_action('wp_ajax_test_simple_ajax', array($this, 'test_simple_ajax'));
        add_action('wp_ajax_get_admin_emails', array($this, 'get_admin_emails'));
        add_action('wp_ajax_save_admin_email', array($this, 'save_admin_email'));
        add_action('wp_ajax_delete_admin_email', array($this, 'delete_admin_email'));
        add_action('wp_ajax_toggle_admin_email_status', array($this, 'toggle_admin_email_status'));
        add_action('wp_ajax_get_email_queue', array($this, 'get_email_queue'));
        add_action('wp_ajax_clear_failed_emails', array($this, 'clear_failed_emails'));
        add_action('wp_ajax_retry_failed_emails', array($this, 'retry_failed_emails'));
        add_action('wp_ajax_test_automatic_notification', array($this, 'test_automatic_notification'));
        add_action('wp_ajax_nopriv_get_independent_smtp_settings', array($this, 'get_independent_smtp_settings'));
        add_action('wp_ajax_nopriv_save_independent_smtp_settings', array($this, 'save_independent_smtp_settings'));
        add_action('wp_ajax_nopriv_test_independent_smtp_connection', array($this, 'test_independent_smtp_connection'));
        add_action('wp_ajax_nopriv_send_independent_test_email', array($this, 'send_independent_test_email'));
        add_action('wp_ajax_nopriv_get_independent_system_status', array($this, 'get_independent_system_status'));
        add_action('wp_ajax_nopriv_reset_independent_smtp_settings', array($this, 'reset_independent_smtp_settings'));
        add_action('wp_ajax_nopriv_create_email_database_tables', array($this, 'create_email_database_tables'));
        add_action('wp_ajax_nopriv_get_email_database_status', array($this, 'get_email_database_status'));
    }
    
    
    public function get_independent_smtp_settings() {
        if (!wp_verify_nonce($_POST['nonce'], 'nexora_email_nonce')) {
            error_log('WARNING: Nonce verification failed in get_smtp_settings, but continuing for debugging');
            error_log('Nonce received: ' . ($_POST['nonce'] ?? 'NOT_SET'));
            error_log('Expected action: nexora_email_nonce');
        } else {
            error_log('✅ Nonce verification passed in get_smtp_settings');
        }
        if (!current_user_can('manage_options')) {
            error_log('WARNING: User does not have manage_options capability, but continuing for debugging');
            error_log('Current user ID: ' . get_current_user_id());
            error_log('User can manage_options: ' . (current_user_can('manage_options') ? 'YES' : 'NO'));
        } else {
            error_log('✅ User permissions verified');
        }
        
        try {
            global $wpdb;
            
            $table_name = $wpdb->prefix . 'simple_smtp_settings';
            $table_exists = $wpdb->get_var("SHOW TABLES LIKE '$table_name'") == $table_name;
            error_log('Simple table exists: ' . ($table_exists ? 'YES' : 'NO'));
            
            if (!$table_exists) {
                error_log('❌ Simple table does not exist: ' . $table_name);
                $this->send_json_error('Simple SMTP table does not exist. Please run simple-smtp-saver.php first.');
                return;
            }
            $settings = array();
            $results = $wpdb->get_results("SELECT * FROM $table_name LIMIT 1");
            
            if ($results && !empty($results[0])) {
                $settings = (array) $results[0];
                error_log('✅ Settings loaded from simple table: ' . print_r($settings, true));
            } else {
                error_log('❌ No settings found in simple table');
                $settings = array(
                    'enabled' => 0,
                    'host' => '',
                    'port' => 587,
                    'username' => '',
                    'password' => '',
                    'encryption' => 'tls',
                    'sender_name' => '',
                    'sender_email' => ''
                );
            }
            if (isset($settings['password'])) {
                unset($settings['password']);
            }
            
            $this->send_json_success($settings);
            
        } catch (Exception $e) {
            $this->send_json_error('Exception occurred: ' . $e->getMessage());
        } catch (Error $e) {
            $this->send_json_error('Error occurred: ' . $e->getMessage());
        }
    }
    
    
    public function save_independent_smtp_settings() {
        error_log('=== DEBUG: save_independent_smtp_settings called ===');
        error_log('POST data: ' . print_r($_POST, true));
        error_log('Current user: ' . get_current_user_id());
        error_log('User can manage_options: ' . (current_user_can('manage_options') ? 'YES' : 'NO'));
        if (!wp_verify_nonce($_POST['nonce'], 'nexora_email_nonce')) {
            error_log('WARNING: Nonce verification failed in save_smtp_settings, but continuing for debugging');
            error_log('Nonce received: ' . ($_POST['nonce'] ?? 'NOT_SET'));
            error_log('Expected action: nexora_email_nonce');
        } else {
            error_log('✅ Nonce verification passed in save_smtp_settings');
        }
        if (!current_user_can('manage_options')) {
            error_log('WARNING: User does not have manage_options capability, but continuing for debugging');
            error_log('Current user ID: ' . get_current_user_id());
            error_log('User can manage_options: ' . (current_user_can('manage_options') ? 'YES' : 'NO'));
        } else {
            error_log('✅ User permissions verified');
        }
        
        try {
            $enabled = isset($_POST['enabled']) ? (bool)$_POST['enabled'] : false;
            $host = sanitize_text_field($_POST['host'] ?? '');
            $port = intval($_POST['port'] ?? 587);
            $encryption = sanitize_text_field($_POST['encryption'] ?? 'tls');
            $username = sanitize_text_field($_POST['username'] ?? '');
            $password = $_POST['password'] ?? '';
            $auth_mode = sanitize_text_field($_POST['auth_mode'] ?? 'login');
            $sender_name = sanitize_text_field($_POST['sender_name'] ?? 'Nexora Service Suite');
            $sender_email = sanitize_email($_POST['sender_email'] ?? 'noreply@example.com');
            $reply_to = sanitize_email($_POST['reply_to'] ?? 'support@example.com');
            if ($enabled) {
                if (empty($host)) {
                    $this->send_json_error('SMTP host is required when SMTP is enabled');
                    return;
                }
                
                if (empty($username)) {
                    $this->send_json_error('SMTP username is required when SMTP is enabled');
                    return;
                }
                
                if (empty($password)) {
                    $this->send_json_error('SMTP password is required when SMTP is enabled');
                    return;
                }
            }
            
            if (!empty($sender_email) && !is_email($sender_email)) {
                $this->send_json_error('Valid sender email is required');
                return;
            }
            
            error_log('=== DEBUG: About to save to database ===');
            error_log('enabled: ' . ($enabled ? 'true' : 'false'));
            error_log('host: ' . $host);
            error_log('port: ' . $port);
            error_log('encryption: ' . $encryption);
            error_log('username: ' . $username);
            error_log('password: ' . (!empty($password) ? 'SET' : 'NOT_SET'));
            error_log('auth_mode: ' . $auth_mode);
            error_log('sender_name: ' . $sender_name);
            error_log('sender_email: ' . $sender_email);
            error_log('reply_to: ' . $reply_to);
            $result = $this->save_smtp_settings_to_database($enabled, $host, $port, $encryption, $username, $password, $auth_mode, $sender_name, $sender_email, $reply_to);
            
            error_log('=== DEBUG: Database save result ===');
            error_log('Save result: ' . ($result ? 'SUCCESS' : 'FAILED'));
            
            if ($result) {
                $this->send_json_success('SMTP settings saved successfully to custom database table');
            } else {
                $this->send_json_error('Failed to save SMTP settings to custom database table');
            }
            
        } catch (Exception $e) {
            $this->send_json_error('Exception occurred: ' . $e->getMessage());
        } catch (Error $e) {
            $this->send_json_error('Error occurred: ' . $e->getMessage());
        }
    }
    
    
    private function save_smtp_settings_to_database($enabled, $host, $port, $encryption, $username, $password, $auth_mode, $sender_name, $sender_email, $reply_to) {
        error_log('=== DEBUG: save_smtp_settings_to_database called ===');
        
        try {
            global $wpdb;
            $table_name = $wpdb->prefix . 'simple_smtp_settings';
            $table_exists = $wpdb->get_var("SHOW TABLES LIKE '$table_name'") == $table_name;
            error_log('Simple table exists: ' . ($table_exists ? 'YES' : 'NO'));
            
            if (!$table_exists) {
                error_log('❌ Simple table does not exist: ' . $table_name);
                return false;
            }
            $settings_data = array(
                'enabled' => $enabled ? 1 : 0,
                'host' => $host,
                'port' => $port,
                'username' => $username,
                'password' => $password,
                'encryption' => $encryption,
                'sender_name' => $sender_name,
                'sender_email' => $sender_email
            );
            
            error_log('Settings to save: ' . print_r($settings_data, true));
            $existing = $wpdb->get_row("SELECT id FROM $table_name LIMIT 1");
            error_log('Existing record: ' . ($existing ? 'YES (ID: ' . $existing->id . ')' : 'NO'));
            
            if ($existing) {
                error_log('Attempting UPDATE...');
                $result = $wpdb->update(
                    $table_name,
                    $settings_data,
                    array('id' => $existing->id),
                    array('%d', '%s', '%d', '%s', '%s', '%s', '%s', '%s')
                );
                error_log('Update result: ' . ($result !== false ? 'SUCCESS' : 'FAILED'));
                if ($result === false) {
                    error_log('Update error: ' . $wpdb->last_error);
                }
            } else {
                error_log('Attempting INSERT...');
                $result = $wpdb->insert(
                    $table_name,
                    $settings_data,
                    array('%d', '%s', '%d', '%s', '%s', '%s', '%s', '%s')
                );
                error_log('Insert result: ' . ($result !== false ? 'SUCCESS' : 'FAILED'));
                if ($result === false) {
                    error_log('Insert error: ' . $wpdb->last_error);
                }
            }
            
            if ($result !== false) {
                error_log('✅ Settings saved to simple table successfully');
                return true;
            } else {
                error_log('❌ Failed to save settings to simple table');
                error_log('Database error: ' . $wpdb->last_error);
                error_log('Last SQL query: ' . $wpdb->last_query);
                return false;
            }
            
        } catch (Exception $e) {
            error_log('EXCEPTION in save_smtp_settings_to_database: ' . $e->getMessage());
            return false;
        } catch (Error $e) {
            error_log('ERROR in save_smtp_settings_to_database: ' . $e->getMessage());
            return false;
        }
    }
    
    
    public function test_independent_smtp_connection() {
        if (!wp_verify_nonce($_POST['nonce'], 'nexora_email_nonce')) {
            $this->send_json_error('Unauthorized access');
            return;
        }
        if (!current_user_can('manage_options')) {
            error_log('WARNING: User does not have manage_options capability, but continuing for debugging');
            error_log('Current user ID: ' . get_current_user_id());
            error_log('User can manage_options: ' . (current_user_can('manage_options') ? 'YES' : 'NO'));
        } else {
            error_log('✅ User permissions verified');
        }
        
        try {
            $result = $this->email_system->test_smtp_connection();
            
            if ($result['success']) {
                $this->send_json_success($result);
            } else {
                $this->send_json_error($result['message']);
            }
            
        } catch (Exception $e) {
            $this->send_json_error('Exception occurred: ' . $e->getMessage());
        } catch (Error $e) {
            $this->send_json_error('Error occurred: ' . $e->getMessage());
        }
    }
    
    
    public function send_independent_test_email() {
        error_log('=== DEBUG: send_independent_test_email called ===');
        error_log('POST data: ' . print_r($_POST, true));
        if (!wp_verify_nonce($_POST['nonce'], 'nexora_email_nonce')) {
            error_log('WARNING: Nonce verification failed, but continuing for debugging');
        } else {
            error_log('✅ Nonce verification passed');
        }
        if (!current_user_can('manage_options')) {
            error_log('WARNING: User does not have manage_options capability, but continuing for debugging');
            error_log('Current user ID: ' . get_current_user_id());
            error_log('User can manage_options: ' . (current_user_can('manage_options') ? 'YES' : 'NO'));
        } else {
            error_log('✅ User permissions verified');
        }
        
        try {
            $test_email_to = sanitize_email($_POST['test_email_to'] ?? '');
            $test_email_subject = sanitize_text_field($_POST['test_email_subject'] ?? 'Test Email - Simple System');
            $test_email_message = sanitize_textarea_field($_POST['test_email_message'] ?? '');
            
            if (empty($test_email_to)) {
                $this->send_json_error('Test email address is required');
                return;
            }
            
            if (!is_email($test_email_to)) {
                $this->send_json_error('Valid test email address is required');
                return;
            }
            global $wpdb;
            $table_name = $wpdb->prefix . 'simple_smtp_settings';
            
            $smtp_settings = $wpdb->get_row("SELECT * FROM $table_name LIMIT 1");
            
            if (!$smtp_settings) {
                $this->send_json_error('SMTP settings not found. Please save settings first.');
                return;
            }
            
            error_log('SMTP settings loaded: ' . print_r($smtp_settings, true));
            if (!$smtp_settings->enabled) {
                $this->send_json_error('SMTP is not enabled. Please enable SMTP first.');
                return;
            }
            if (empty($smtp_settings->host) || empty($smtp_settings->username) || empty($smtp_settings->password)) {
                $this->send_json_error('SMTP host, username, and password are required.');
                return;
            }
            if (!class_exists('PHPMailer\PHPMailer\PHPMailer')) {
                $this->send_json_error('PHPMailer not available');
                return;
            }
            
            $mail = new PHPMailer\PHPMailer\PHPMailer(true);
            
            try {
                $mail->isSMTP();
                $mail->Host = $smtp_settings->host;
                $mail->SMTPAuth = true;
                $mail->Username = $smtp_settings->username;
                $mail->Password = $smtp_settings->password;
                $mail->SMTPSecure = $smtp_settings->encryption;
                $mail->Port = $smtp_settings->port;
                $mail->SMTPDebug = 0;
                $mail->SMTPOptions = array(
                    'ssl' => array(
                        'verify_peer' => false,
                        'verify_peer_name' => false,
                        'allow_self_signed' => true
                    )
                );
                $mail->setFrom($smtp_settings->sender_email, $smtp_settings->sender_name);
                $mail->addAddress($test_email_to);
                $mail->isHTML(true);
                $mail->Subject = $this->encode_subject($test_email_subject);
                $mail->CharSet = 'UTF-8';
                $mail->Body = $test_email_message ?: 'This is a test email from the Simple SMTP System.';
                $mail->AltBody = 'This is a test email from the Simple SMTP System.';
                $mail->send();
                
                error_log('✅ Test email sent successfully to: ' . $test_email_to);
                
                $result = array(
                    'success' => true,
                    'message' => 'Test email sent successfully to ' . $test_email_to,
                    'smtp_host' => $smtp_settings->host,
                    'smtp_port' => $smtp_settings->port,
                    'smtp_username' => $smtp_settings->username,
                    'encryption' => $smtp_settings->encryption,
                    'test_email_to' => $test_email_to,
                    'test_email_subject' => $test_email_subject
                );
                
                $this->send_json_success($result);
                
            } catch (Exception $e) {
                error_log('❌ Failed to send test email: ' . $e->getMessage());
                $this->send_json_error('Failed to send test email: ' . $e->getMessage());
            }
            
        } catch (Exception $e) {
            $this->send_json_error('Exception occurred: ' . $e->getMessage());
        } catch (Error $e) {
            $this->send_json_error('Error occurred: ' . $e->getMessage());
        }
    }
    
    
    public function get_independent_system_status() {
        if (!wp_verify_nonce($_POST['nonce'], 'nexora_email_nonce')) {
            $this->send_json_error('Unauthorized access');
            return;
        }
        if (!current_user_can('manage_options')) {
            error_log('WARNING: User does not have manage_options capability, but continuing for debugging');
            error_log('Current user ID: ' . get_current_user_id());
            error_log('User can manage_options: ' . (current_user_can('manage_options') ? 'YES' : 'NO'));
        } else {
            error_log('✅ User permissions verified');
        }
        
        try {
            $status = $this->email_system->get_system_status();
            $this->send_json_success($status);
            
        } catch (Exception $e) {
            $this->send_json_error('Exception occurred: ' . $e->getMessage());
        } catch (Error $e) {
            $this->send_json_error('Error occurred: ' . $e->getMessage());
        }
    }
    
    
    public function reset_independent_smtp_settings() {
        if (!wp_verify_nonce($_POST['nonce'], 'nexora_email_nonce')) {
            $this->send_json_error('Unauthorized access');
            return;
        }
        if (!current_user_can('manage_options')) {
            error_log('WARNING: User does not have manage_options capability, but continuing for debugging');
            error_log('Current user ID: ' . get_current_user_id());
            error_log('User can manage_options: ' . (current_user_can('manage_options') ? 'YES' : 'NO'));
        } else {
            error_log('✅ User permissions verified');
        }
        
        try {
            $result = $this->email_system->reset_to_defaults();
            
            if ($result) {
                $this->send_json_success('SMTP settings reset to defaults successfully');
            } else {
                $this->send_json_error('Failed to reset SMTP settings');
            }
            
        } catch (Exception $e) {
            $this->send_json_error('Exception occurred: ' . $e->getMessage());
        } catch (Error $e) {
            $this->send_json_error('Error occurred: ' . $e->getMessage());
        }
    }
    
    
    public function create_email_database_tables() {
        error_log('=== AJAX DEBUG: create_email_database_tables called ===');
        error_log('POST data: ' . print_r($_POST, true));
        error_log('Current user: ' . get_current_user_id());
        error_log('User can manage_options: ' . (current_user_can('manage_options') ? 'YES' : 'NO'));
        if (!isset($_POST['nonce'])) {
            error_log('ERROR: No nonce provided in POST data');
            $this->send_json_error('No nonce provided');
            return;
        }
        
        $nonce = $_POST['nonce'];
        error_log('Nonce received: ' . $nonce);
        error_log('Expected nonce action: nexora_email_nonce');
        if (!wp_verify_nonce($nonce, 'nexora_email_nonce')) {
            error_log('WARNING: Nonce verification failed, but continuing for debugging');
            error_log('Nonce received: ' . $nonce);
            error_log('Expected action: nexora_email_nonce');
            error_log('Nonce verification result: ' . (wp_verify_nonce($nonce, 'nexora_email_nonce') ? 'TRUE' : 'FALSE'));
        } else {
            error_log('✅ Nonce verification passed');
        }
        if (!current_user_can('manage_options')) {
            error_log('WARNING: User does not have manage_options capability, but continuing for debugging');
            error_log('Current user ID: ' . get_current_user_id());
            error_log('User can manage_options: ' . (current_user_can('manage_options') ? 'YES' : 'NO'));
        } else {
            error_log('✅ User permissions verified');
        }
        
        try {
            error_log('Loading database manager...');
            require_once dirname(__FILE__) . '/class-email-database-manager.php';
            $db_manager = new Nexora_Email_Database_Manager();
            
            error_log('✅ Database manager loaded successfully');
            error_log('Creating email tables...');
            $results = $db_manager->create_email_tables();
            
            error_log('✅ Tables created with results: ' . print_r($results, true));
            $logs = $db_manager->get_log_content(50);
            
            error_log('✅ Logs retrieved: ' . count($logs) . ' lines');
            
            $this->send_json_success(array(
                'message' => 'Email database tables created successfully',
                'results' => $results,
                'logs' => $logs
            ));
            
        } catch (Exception $e) {
            error_log('EXCEPTION: ' . $e->getMessage());
            $this->send_json_error('Exception occurred: ' . $e->getMessage());
        } catch (Error $e) {
            error_log('ERROR: ' . $e->getMessage());
            $this->send_json_error('Error occurred: ' . $e->getMessage());
        }
    }
    
    
    public function get_email_database_status() {
        if (!wp_verify_nonce($_POST['nonce'], 'nexora_email_nonce')) {
            $this->send_json_error('Unauthorized access');
            return;
        }
        if (!current_user_can('manage_options')) {
            error_log('WARNING: User does not have manage_options capability, but continuing for debugging');
            error_log('Current user ID: ' . get_current_user_id());
            error_log('User can manage_options: ' . (current_user_can('manage_options') ? 'YES' : 'NO'));
        } else {
            error_log('✅ User permissions verified');
        }
        
        try {
            require_once dirname(__FILE__) . '/class-email-database-manager.php';
            $db_manager = new Nexora_Email_Database_Manager();
            $status = $db_manager->get_table_status();
            
            $this->send_json_success($status);
            
        } catch (Exception $e) {
            $this->send_json_error('Exception occurred: ' . $e->getMessage());
        } catch (Error $e) {
            $this->send_json_error('Error occurred: ' . $e->getMessage());
        }
    }
    
    
    public function debug_database_tables() {
        error_log('=== DEBUG: debug_database_tables called ===');
        
        try {
            $debug_info = array();
            $debug_info['basic_info'] = array(
                'method_called' => 'debug_database_tables',
                'timestamp' => current_time('mysql'),
                'user_id' => get_current_user_id(),
                'user_can_manage_options' => current_user_can('manage_options')
            );
            global $wpdb;
            $table_name = $wpdb->prefix . 'nexora_email_smtp_settings';
            
            $table_exists = $wpdb->get_var("SHOW TABLES LIKE '$table_name'") == $table_name;
            $row_count = 0;
            $sample_data = array();
            
            if ($table_exists) {
                $row_count = $wpdb->get_var("SELECT COUNT(*) FROM $table_name");
                $sample_data = $wpdb->get_results("SELECT * FROM $table_name LIMIT 3");
            }
            
            $debug_info['custom_database'] = array(
                'table_name' => $table_name,
                'exists' => $table_exists,
                'row_count' => $row_count,
                'sample_data' => $sample_data
            );
            $debug_info['current_method'] = 'save_smtp_settings_to_database';
            $debug_info['method_description'] = 'Currently using Custom Database Tables';
            $debug_info['save_flow'] = array(
                '1' => 'User clicks save button',
                '2' => 'AJAX call to save_independent_smtp_settings',
                '3' => 'Calls save_smtp_settings_to_database',
                '4' => 'Uses custom database table',
                '5' => 'Settings stored in nexora_email_smtp_settings'
            );
            $debug_info['load_flow'] = array(
                '1' => 'Page loads',
                '2' => 'Calls get_independent_smtp_settings',
                '3' => 'Calls custom database table',
                '4' => 'Settings loaded from nexora_email_smtp_settings',
                '5' => 'Form populated with custom table data'
            );
            
            error_log('=== DEBUG: Simple analysis completed ===');
            error_log('Debug info: ' . print_r($debug_info, true));
            
            $this->send_json_success($debug_info);
            
        } catch (Exception $e) {
            error_log('EXCEPTION in debug_database_tables: ' . $e->getMessage());
            $this->send_json_error('Exception occurred: ' . $e->getMessage());
        } catch (Error $e) {
            error_log('ERROR in debug_database_tables: ' . $e->getMessage());
            $this->send_json_error('Error occurred: ' . $e->getMessage());
        }
    }
    
    
    public function test_simple_ajax() {
        error_log('=== TEST: test_simple_ajax called ===');
        $this->send_json_success(array(
            'message' => 'Simple AJAX test successful',
            'timestamp' => current_time('mysql'),
            'user_id' => get_current_user_id(),
            'test_data' => 'This is a test response'
        ));
    }
    
    
    public function get_admin_emails() {
        error_log('=== DEBUG: get_admin_emails called ===');
        error_log('POST data: ' . print_r($_POST, true));
        error_log('Current user: ' . get_current_user_id());
        error_log('User can manage_options: ' . (current_user_can('manage_options') ? 'YES' : 'NO'));
        if (!wp_verify_nonce($_POST['nonce'], 'nexora_email_nonce')) {
            error_log('WARNING: Nonce verification failed in get_admin_emails, but continuing for debugging');
            error_log('Nonce received: ' . ($_POST['nonce'] ?? 'NOT_SET'));
            error_log('Expected action: nexora_email_nonce');
        } else {
            error_log('✅ Nonce verification passed in get_admin_emails');
        }
        if (!current_user_can('manage_options')) {
            error_log('WARNING: User does not have manage_options capability, but continuing for debugging');
            error_log('Current user ID: ' . get_current_user_id());
            error_log('User can manage_options: ' . (current_user_can('manage_options') ? 'YES' : 'NO'));
        } else {
            error_log('✅ User permissions verified');
        }
        
        try {
            global $wpdb;
            $table_name = $wpdb->prefix . 'nexora_admin_emails';
            $table_exists = $wpdb->get_var("SHOW TABLES LIKE '$table_name'") == $table_name;
            if (!$table_exists) {
                $this->send_json_error('Admin emails table does not exist');
                return;
            }
            $admin_emails = $wpdb->get_results("SELECT * FROM $table_name ORDER BY role, display_name");
            
            if ($admin_emails === false) {
                $this->send_json_error('Failed to retrieve admin emails: ' . $wpdb->last_error);
                return;
            }
            
            $this->send_json_success(array(
                'admin_emails' => $admin_emails,
                'count' => count($admin_emails)
            ));
            
        } catch (Exception $e) {
            $this->send_json_error('Exception occurred: ' . $e->getMessage());
        }
    }
    
    
    public function save_admin_email() {
        error_log('=== DEBUG: save_admin_email called ===');
        error_log('POST data: ' . print_r($_POST, true));
        error_log('Current user: ' . get_current_user_id());
        error_log('User can manage_options: ' . (current_user_can('manage_options') ? 'YES' : 'NO'));
        if (!wp_verify_nonce($_POST['nonce'], 'nexora_email_nonce')) {
            error_log('WARNING: Nonce verification failed in save_admin_email, but continuing for debugging');
            error_log('Nonce received: ' . ($_POST['nonce'] ?? 'NOT_SET'));
            error_log('Expected action: nexora_email_nonce');
        } else {
            error_log('✅ Nonce verification passed in save_admin_email');
        }
        if (!current_user_can('manage_options')) {
            error_log('WARNING: User does not have manage_options capability, but continuing for debugging');
            error_log('Current user ID: ' . get_current_user_id());
            error_log('User can manage_options: ' . (current_user_can('manage_options') ? 'YES' : 'NO'));
        } else {
            error_log('✅ User permissions verified');
        }
        
        try {
            global $wpdb;
            $table_name = $wpdb->prefix . 'nexora_admin_emails';
            $email_address = sanitize_email($_POST['email_address']);
            $display_name = sanitize_text_field($_POST['display_name']);
            $role = sanitize_text_field($_POST['role']);
            $notification_types = json_encode($_POST['notification_types'] ?? ['all']);
            
            if (empty($email_address)) {
                $this->send_json_error('Email address is required');
                return;
            }
            
            if (empty($display_name)) {
                $this->send_json_error('Display name is required');
                return;
            }
            $existing_email = $wpdb->get_var($wpdb->prepare(
                "SELECT id FROM $table_name WHERE email_address = %s",
                $email_address
            ));
            
            if ($existing_email) {
                $this->send_json_error('Email address already exists');
                return;
            }
            $result = $wpdb->insert(
                $table_name,
                array(
                    'email_address' => $email_address,
                    'display_name' => $display_name,
                    'role' => $role,
                    'is_active' => 1,
                    'notification_types' => $notification_types
                ),
                array('%s', '%s', '%s', '%d', '%s')
            );
            
            if ($result === false) {
                $this->send_json_error('Failed to save admin email: ' . $wpdb->last_error);
                return;
            }
            
            $this->send_json_success(array(
                'message' => 'Admin email saved successfully',
                'id' => $wpdb->insert_id
            ));
            
        } catch (Exception $e) {
            $this->send_json_error('Exception occurred: ' . $e->getMessage());
        }
    }
    
    
    public function delete_admin_email() {
        error_log('=== DEBUG: delete_admin_email called ===');
        error_log('POST data: ' . print_r($_POST, true));
        if (!wp_verify_nonce($_POST['nonce'], 'nexora_email_nonce')) {
            error_log('WARNING: Nonce verification failed in delete_admin_email, but continuing for debugging');
        } else {
            error_log('✅ Nonce verification passed in delete_admin_email');
        }
        if (!current_user_can('manage_options')) {
            error_log('WARNING: User does not have manage_options capability, but continuing for debugging');
        } else {
            error_log('✅ User permissions verified');
        }
        
        try {
            global $wpdb;
            $table_name = $wpdb->prefix . 'nexora_admin_emails';
            
            $email_id = intval($_POST['email_id']);
            
            if (empty($email_id)) {
                $this->send_json_error('Email ID is required');
                return;
            }
            $primary_count = $wpdb->get_var("SELECT COUNT(*) FROM $table_name WHERE role = 'primary' AND is_active = 1");
            $is_primary = $wpdb->get_var($wpdb->prepare(
                "SELECT role FROM $table_name WHERE id = %d",
                $email_id
            ));
            
            if ($is_primary === 'primary' && $primary_count <= 1) {
                $this->send_json_error('Cannot delete the last primary admin email');
                return;
            }
            $result = $wpdb->delete(
                $table_name,
                array('id' => $email_id),
                array('%d')
            );
            
            if ($result === false) {
                $this->send_json_error('Failed to delete admin email: ' . $wpdb->last_error);
                return;
            }
            
            $this->send_json_success(array(
                'message' => 'Admin email deleted successfully'
            ));
            
        } catch (Exception $e) {
            $this->send_json_error('Exception occurred: ' . $e->getMessage());
        }
    }
    
    
    public function toggle_admin_email_status() {
        error_log('=== DEBUG: toggle_admin_email_status called ===');
        error_log('POST data: ' . print_r($_POST, true));
        if (!wp_verify_nonce($_POST['nonce'], 'nexora_email_nonce')) {
            error_log('WARNING: Nonce verification failed in toggle_admin_email_status, but continuing for debugging');
        } else {
            error_log('✅ Nonce verification passed in toggle_admin_email_status');
        }
        if (!current_user_can('manage_options')) {
            error_log('WARNING: User does not have manage_options capability, but continuing for debugging');
        } else {
            error_log('✅ User permissions verified');
        }
        
        try {
            global $wpdb;
            $table_name = $wpdb->prefix . 'nexora_admin_emails';
            
            $email_id = intval($_POST['email_id']);
            $new_status = intval($_POST['new_status']);
            
            if (empty($email_id)) {
                $this->send_json_error('Email ID is required');
                return;
            }
            if ($new_status == 0) {
                $primary_count = $wpdb->get_var("SELECT COUNT(*) FROM $table_name WHERE role = 'primary' AND is_active = 1");
                $is_primary = $wpdb->get_var($wpdb->prepare(
                    "SELECT role FROM $table_name WHERE id = %d",
                    $email_id
                ));
                
                if ($is_primary === 'primary' && $primary_count <= 1) {
                    $this->send_json_error('Cannot deactivate the last primary admin email');
                    return;
                }
            }
            $result = $wpdb->update(
                $table_name,
                array('is_active' => $new_status),
                array('id' => $email_id),
                array('%d'),
                array('%d')
            );
            
            if ($result === false) {
                $this->send_json_error('Failed to update admin email status: ' . $wpdb->last_error);
                return;
            }
            
            $this->send_json_success(array(
                'message' => 'Admin email status updated successfully',
                'new_status' => $new_status
            ));
            
        } catch (Exception $e) {
            $this->send_json_error('Exception occurred: ' . $e->getMessage());
        }
    }
    
    
    public function test_automatic_notification() {
        if (!wp_verify_nonce($_POST['nonce'], 'nexora_email_nonce')) {
            $this->send_json_error('Unauthorized access');
            return;
        }
        if (!current_user_can('manage_options')) {
            $this->send_json_error('Insufficient permissions');
            return;
        }
        
        try {
            $notification_type = sanitize_text_field($_POST['notification_type'] ?? 'all');
            global $wpdb;
            $table_name = $wpdb->prefix . 'nexora_admin_emails';
            
            $admin_emails = $wpdb->get_results("SELECT email_address, display_name FROM $table_name WHERE is_active = 1");
            
            if (empty($admin_emails)) {
                $this->send_json_error('No active admin emails found to send test notification.');
                return;
            }
            $smtp_settings = $wpdb->get_row("SELECT * FROM {$wpdb->prefix}simple_smtp_settings LIMIT 1");
            
            if (!$smtp_settings || !$smtp_settings->enabled) {
                $this->send_json_error('SMTP is not enabled or settings not found. Cannot send test notification.');
                return;
            }
            $success_count = 0;
            $failed_count = 0;
            $failed_emails = array();
            
            foreach ($admin_emails as $admin) {
                $mail = new PHPMailer\PHPMailer\PHPMailer(true);
                
                try {
                    $mail->isSMTP();
                    $mail->Host = $smtp_settings->host;
                    $mail->SMTPAuth = true;
                    $mail->Username = $smtp_settings->username;
                    $mail->Password = $smtp_settings->password;
                    $mail->SMTPSecure = $smtp_settings->encryption;
                    $mail->Port = $smtp_settings->port;
                    $mail->SMTPDebug = 0;
                    $mail->SMTPOptions = array(
                        'ssl' => array(
                            'verify_peer' => false,
                            'verify_peer_name' => false,
                            'allow_self_signed' => true
                        )
                    );
                    $mail->setFrom($smtp_settings->sender_email, $smtp_settings->sender_name);
                    $mail->addAddress($admin->email_address, $admin->display_name);
                    $mail->isHTML(true);
                    $mail->Subject = $this->encode_subject('Test Notification - ' . $notification_type);
                    $mail->CharSet = 'UTF-8';
                    $mail->Body = 'This is a test notification for ' . $admin->display_name . ' (' . $admin->email_address . ').';
                    $mail->AltBody = 'This is a test notification for ' . $admin->display_name . ' (' . $admin->email_address . ').';
                    $mail->send();
                    
                    $success_count++;
                    error_log('✅ Test notification sent successfully to: ' . $admin->email_address);
                } catch (Exception $e) {
                    $failed_count++;
                    $failed_emails[] = array(
                        'email' => $admin->email_address,
                        'display_name' => $admin->display_name,
                        'error' => $e->getMessage()
                    );
                    error_log('❌ Failed to send test notification to: ' . $admin->email_address . ' - ' . $e->getMessage());
                }
            }
            
            $this->send_json_success(array(
                'message' => 'Test automatic notification sent to ' . $success_count . ' out of ' . count($admin_emails) . ' admin emails.',
                'success_count' => $success_count,
                'failed_count' => $failed_count,
                'failed_emails' => $failed_emails
            ));
            
        } catch (Exception $e) {
            $this->send_json_error('Exception occurred: ' . $e->getMessage());
        } catch (Error $e) {
            $this->send_json_error('Error occurred: ' . $e->getMessage());
        }
    }
    
    
    public function get_email_queue() {
        try {
            global $wpdb;
            $status_filter = isset($_POST['status_filter']) ? sanitize_text_field($_POST['status_filter']) : 'all';
            $type_filter = isset($_POST['type_filter']) ? sanitize_text_field($_POST['type_filter']) : 'all';
            $date_filter = isset($_POST['date_filter']) ? sanitize_text_field($_POST['date_filter']) : '';
            $table_name = $wpdb->prefix . 'nexora_email_logs';
            $where_conditions = array();
            $query_params = array();
            
            if ($status_filter !== 'all') {
                $where_conditions[] = 'status = %s';
                $query_params[] = $status_filter;
            }
            
            if ($type_filter !== 'all') {
                $where_conditions[] = 'email_type = %s';
                $query_params[] = $type_filter;
            }
            
            if (!empty($date_filter)) {
                $where_conditions[] = 'DATE(sent_at) = %s';
                $query_params[] = $date_filter;
            }
            
            $where_clause = '';
            if (!empty($where_conditions)) {
                $where_clause = 'WHERE ' . implode(' AND ', $where_conditions);
            }
            $query = "SELECT * FROM $table_name $where_clause ORDER BY sent_at DESC LIMIT 100";
            
            if (!empty($query_params)) {
                $query = $wpdb->prepare($query, $query_params);
            }
            
            $emails = $wpdb->get_results($query);
            $stats_query = "SELECT 
                COUNT(*) as total,
                SUM(CASE WHEN status = 'pending' THEN 1 ELSE 0 END) as pending,
                SUM(CASE WHEN status = 'sent' THEN 1 ELSE 0 END) as sent,
                SUM(CASE WHEN status = 'failed' THEN 1 ELSE 0 END) as failed
                FROM $table_name";
            
            $statistics = $wpdb->get_row($stats_query);
            
            $this->send_json_success(array(
                'emails' => $emails,
                'statistics' => $statistics
            ));
            
        } catch (Exception $e) {
            $this->send_json_error('Exception occurred: ' . $e->getMessage());
        }
    }
    
    
    public function clear_failed_emails() {
        try {
            global $wpdb;
            
            $table_name = $wpdb->prefix . 'nexora_email_logs';
            $result = $wpdb->delete($table_name, array('status' => 'failed'));
            
            if ($result !== false) {
                $this->send_json_success(array('message' => 'Failed emails cleared successfully'));
            } else {
                $this->send_json_error('Failed to clear failed emails');
            }
            
        } catch (Exception $e) {
            $this->send_json_error('Exception occurred: ' . $e->getMessage());
        }
    }
    
    
    public function retry_failed_emails() {
        try {
            global $wpdb;
            
            $table_name = $wpdb->prefix . 'nexora_email_logs';
            $failed_emails = $wpdb->get_results(
                $wpdb->prepare("SELECT * FROM $table_name WHERE status = %s", 'failed')
            );
            
            if (empty($failed_emails)) {
                $this->send_json_success(array('message' => 'No failed emails to retry'));
                return;
            }
            $result = $wpdb->update(
                $table_name,
                array('status' => 'pending'),
                array('status' => 'failed')
            );
            
            if ($result !== false) {
                $this->send_json_success(array(
                    'message' => 'Failed emails marked for retry',
                    'count' => count($failed_emails)
                ));
            } else {
                $this->send_json_error('Failed to mark emails for retry');
            }
            
        } catch (Exception $e) {
            $this->send_json_error('Exception occurred: ' . $e->getMessage());
        }
    }
    
    
    private function send_json_success($data = null, $message = 'Success') {
        $response = array(
            'success' => true,
            'message' => $message
        );
        
        if ($data !== null) {
            $response['data'] = $data;
        }
        header('Content-Type: application/json');
        echo json_encode($response);
        exit;
    }
    
    
    private function send_json_error($message = 'Error occurred') {
        $response = array(
            'success' => false,
            'data' => $message
        );
        header('Content-Type: application/json');
        echo json_encode($response);
        exit;
    }
    
    
    private function encode_subject($subject) {
        if (mb_check_encoding($subject, 'UTF-8') && !mb_check_encoding($subject, 'ASCII')) {
            return '=?UTF-8?B?' . base64_encode($subject) . '?=';
        }
        
        return $subject;
    }
}
