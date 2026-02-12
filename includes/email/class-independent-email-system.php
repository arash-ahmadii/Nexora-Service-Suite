<?php

class Nexora_Independent_Email_System {
    
    private $smtp_settings = array();
    private $log_file;
    private $config_file;
    
    public function __construct() {
        $this->log_file = dirname(__DIR__, 3) . '/logs/Nexora Service Suite-independent-email.log';
        $this->config_file = dirname(__DIR__, 3) . '/data/smtp-config.json';
        $this->ensure_directories();
        $this->load_smtp_settings();
        $this->log_info('Independent Email System initialized');
    }
    
    
    private function ensure_directories() {
        $log_dir = dirname($this->log_file);
        $config_dir = dirname($this->config_file);
        
        if (!is_dir($log_dir)) {
            mkdir($log_dir, 0755, true);
        }
        
        if (!is_dir($config_dir)) {
            mkdir($config_dir, 0755, true);
        }
        $htaccess_file = $log_dir . '/.htaccess';
        if (!file_exists($htaccess_file)) {
            $htaccess_content = "Order deny,allow\nDeny from all\n";
            file_put_contents($htaccess_file, $htaccess_content);
        }
    }
    
    
    private function load_smtp_settings() {
        try {
            global $wpdb;
            $table_name = $wpdb->prefix . 'simple_smtp_settings';
            $table_exists = $wpdb->get_var("SHOW TABLES LIKE '$table_name'") == $table_name;
            if (!$table_exists) {
                $this->log_warning("SMTP settings table does not exist, using defaults");
                $this->set_default_smtp_settings();
                return;
            }
            $smtp_data = $wpdb->get_row("SELECT * FROM $table_name LIMIT 1");
            
            if ($smtp_data) {
                $this->smtp_settings = array(
                    'enabled' => isset($smtp_data->enabled) ? (bool)$smtp_data->enabled : false,
                    'host' => isset($smtp_data->host) ? $smtp_data->host : 'localhost',
                    'port' => isset($smtp_data->port) ? (int)$smtp_data->port : 587,
                    'encryption' => isset($smtp_data->encryption) ? $smtp_data->encryption : 'tls',
                    'username' => isset($smtp_data->username) ? $smtp_data->username : '',
                    'password' => isset($smtp_data->password) ? $smtp_data->password : '',
                    'auth_mode' => isset($smtp_data->auth_mode) ? $smtp_data->auth_mode : 'login',
                    'sender_name' => isset($smtp_data->sender_name) ? $smtp_data->sender_name : 'Nexora Service Suite',
                    'sender_email' => isset($smtp_data->sender_email) ? $smtp_data->sender_email : 'noreply@example.com',
                    'reply_to' => isset($smtp_data->reply_to) ? $smtp_data->reply_to : 'support@example.com',
                    'timeout' => isset($smtp_data->timeout) ? (int)$smtp_data->timeout : 30,
                    'max_retries' => isset($smtp_data->max_retries) ? (int)$smtp_data->max_retries : 3
                );
                
                $this->log_info('SMTP settings loaded from database: ' . json_encode($this->smtp_settings));
            } else {
                $this->log_warning('No SMTP settings found in database, using defaults');
                $this->set_default_smtp_settings();
            }
            
        } catch (Exception $e) {
            $this->log_error('Failed to load SMTP settings from database: ' . $e->getMessage());
            $this->set_default_smtp_settings();
        }
    }
    
    
    private function set_default_smtp_settings() {
        $this->smtp_settings = array(
            'enabled' => false,
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
        
        $this->log_info('Default SMTP settings loaded');
    }
    
    
    public function save_smtp_settings($settings = null) {
        if ($settings !== null) {
            $this->smtp_settings = array_merge($this->smtp_settings, $settings);
        }
        
        try {
            global $wpdb;
            
            $table_name = $wpdb->prefix . 'simple_smtp_settings';
            $table_exists = $wpdb->get_var("SHOW TABLES LIKE '$table_name'") == $table_name;
            if (!$table_exists) {
                $this->log_error('SMTP settings table does not exist');
                return false;
            }
            $existing = $wpdb->get_row("SELECT id FROM $table_name LIMIT 1");
            
            if ($existing) {
                $update_data = array();
                $format = array();
                if (isset($this->smtp_settings['enabled'])) {
                    $update_data['enabled'] = $this->smtp_settings['enabled'];
                    $format[] = '%d';
                }
                if (isset($this->smtp_settings['host'])) {
                    $update_data['host'] = $this->smtp_settings['host'];
                    $format[] = '%s';
                }
                if (isset($this->smtp_settings['port'])) {
                    $update_data['port'] = $this->smtp_settings['port'];
                    $format[] = '%d';
                }
                if (isset($this->smtp_settings['encryption'])) {
                    $update_data['encryption'] = $this->smtp_settings['encryption'];
                    $format[] = '%s';
                }
                if (isset($this->smtp_settings['username'])) {
                    $update_data['username'] = $this->smtp_settings['username'];
                    $format[] = '%s';
                }
                if (isset($this->smtp_settings['password'])) {
                    $update_data['password'] = $this->smtp_settings['password'];
                    $format[] = '%s';
                }
                if (isset($this->smtp_settings['auth_mode'])) {
                    $update_data['auth_mode'] = $this->smtp_settings['auth_mode'];
                    $format[] = '%s';
                }
                if (isset($this->smtp_settings['sender_name'])) {
                    $update_data['sender_name'] = $this->smtp_settings['sender_name'];
                    $format[] = '%s';
                }
                if (isset($this->smtp_settings['sender_email'])) {
                    $update_data['sender_email'] = $this->smtp_settings['sender_email'];
                    $format[] = '%s';
                }
                
                if (!empty($update_data)) {
                    $result = $wpdb->update(
                        $table_name,
                        $update_data,
                        array('id' => $existing->id),
                        $format
                    );
                } else {
                    $result = true;
                }
            } else {
                $insert_data = array();
                $format = array();
                if (isset($this->smtp_settings['enabled'])) {
                    $insert_data['enabled'] = $this->smtp_settings['enabled'];
                    $format[] = '%d';
                }
                if (isset($this->smtp_settings['host'])) {
                    $insert_data['host'] = $this->smtp_settings['host'];
                    $format[] = '%s';
                }
                if (isset($this->smtp_settings['port'])) {
                    $insert_data['port'] = $this->smtp_settings['port'];
                    $format[] = '%d';
                }
                if (isset($this->smtp_settings['encryption'])) {
                    $insert_data['encryption'] = $this->smtp_settings['encryption'];
                    $format[] = '%s';
                }
                if (isset($this->smtp_settings['username'])) {
                    $insert_data['username'] = $this->smtp_settings['username'];
                    $format[] = '%s';
                }
                if (isset($this->smtp_settings['password'])) {
                    $insert_data['password'] = $this->smtp_settings['password'];
                    $format[] = '%s';
                }
                if (isset($this->smtp_settings['auth_mode'])) {
                    $insert_data['auth_mode'] = $this->smtp_settings['auth_mode'];
                    $format[] = '%s';
                }
                if (isset($this->smtp_settings['sender_name'])) {
                    $insert_data['sender_name'] = $this->smtp_settings['sender_name'];
                    $format[] = '%s';
                }
                if (isset($this->smtp_settings['sender_email'])) {
                    $insert_data['sender_email'] = $this->smtp_settings['sender_email'];
                    $format[] = '%s';
                }
                
                if (!empty($insert_data)) {
                    $result = $wpdb->insert(
                        $table_name,
                        $insert_data,
                        $format
                    );
                } else {
                    $result = false;
                }
            }
            
            if ($result !== false) {
                $this->log_info('SMTP settings saved to database successfully');
                return true;
            } else {
                $this->log_error('Failed to save SMTP settings to database: ' . $wpdb->last_error);
                return false;
            }
            
        } catch (Exception $e) {
            $this->log_error('Failed to save SMTP settings to database: ' . $e->getMessage());
            return false;
        }
    }
    
    
    public function get_smtp_settings() {
        return $this->smtp_settings;
    }
    
    
    public function update_smtp_setting($key, $value) {
        $this->smtp_settings[$key] = $value;
        
        try {
            global $wpdb;
            
            $table_name = $wpdb->prefix . 'simple_smtp_settings';
            $table_exists = $wpdb->get_var("SHOW TABLES LIKE '$table_name'") == $table_name;
            if (!$table_exists) {
                $this->log_error('SMTP settings table does not exist');
                return false;
            }
            $existing = $wpdb->get_row("SELECT id FROM $table_name LIMIT 1");
            if ($existing) {
                $result = $wpdb->update(
                    $table_name,
                    array($key => $value),
                    array('id' => $existing->id),
                    array('%s')
                );
                
                if ($result !== false) {
                    $this->log_info("SMTP setting '$key' updated to '$value'");
                    return true;
                } else {
                    $this->log_error('Failed to update SMTP setting: ' . $wpdb->last_error);
                    return false;
                }
            } else {
                return $this->save_smtp_settings();
            }
            
        } catch (Exception $e) {
            $this->log_error('Failed to update SMTP setting: ' . $e->getMessage());
            return false;
        }
    }
    
    
    public function test_smtp_connection() {
        try {
            if (empty($this->smtp_settings['enabled'])) {
                return array(
                    'success' => false,
                    'message' => 'SMTP is not enabled'
                );
            }
            if (empty($this->smtp_settings['host']) || empty($this->smtp_settings['username'])) {
                return array(
                    'success' => false,
                    'message' => 'Missing SMTP host or username'
                );
            }
            if (empty($this->smtp_settings['password'])) {
                return array(
                    'success' => false,
                    'message' => 'SMTP password is not set'
                );
            }
            
            $this->log_info('Testing SMTP connection to ' . $this->smtp_settings['host'] . ':' . $this->smtp_settings['port']);
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
                        require_once dirname($path) . '/SMTP.php';
                        require_once dirname($path) . '/Exception.php';
                        $loaded = true;
                        break;
                    }
                }
                
                if (!$loaded) {
                    return array(
                        'success' => false,
                        'message' => 'PHPMailer not available'
                    );
                }
            }
            
            $mail = new PHPMailer\PHPMailer\PHPMailer(true);
            $mail->isSMTP();
            $mail->Host = $this->smtp_settings['host'];
            $mail->Port = $this->smtp_settings['port'];
            $mail->SMTPSecure = $this->smtp_settings['encryption'];
            $mail->SMTPAuth = true;
            $mail->Username = $this->smtp_settings['username'];
            $mail->Password = $this->smtp_settings['password'];
            $mail->SMTPOptions = array(
                'ssl' => array(
                    'verify_peer' => false,
                    'verify_peer_name' => false,
                    'allow_self_signed' => true
                )
            );
            $mail->Timeout = 30;
            $mail->SMTPDebug = 0;
            
            $this->log_info('Attempting SMTP connection...');
            if ($mail->smtpConnect()) {
                $mail->smtpClose();
                $this->log_info('SMTP connection test successful');
                return array(
                    'success' => true,
                    'message' => 'SMTP connection successful'
                );
            } else {
                $error = $mail->ErrorInfo;
                $this->log_error('SMTP connection test failed: ' . $error);
                return array(
                    'success' => false,
                    'message' => 'SMTP connection failed: ' . $error
                );
            }
            
        } catch (Exception $e) {
            $this->log_error('Exception during SMTP test: ' . $e->getMessage());
            return array(
                'success' => false,
                'message' => 'Exception during SMTP test: ' . $e->getMessage()
            );
        }
    }
    
    
    public function get_detailed_smtp_settings() {
        $settings = $this->smtp_settings;
        if (!empty($settings['password'])) {
            $settings['password'] = str_repeat('*', min(strlen($settings['password']), 8));
        }
        
        return $settings;
    }
    
    
    public function send_email($to, $subject, $message, $headers = array()) {
        if (empty($this->smtp_settings['enabled'])) {
            $this->log_error('Cannot send email: SMTP is not enabled');
            return array(
                'success' => false,
                'message' => 'SMTP is not enabled'
            );
        }
        
        $this->log_info("Sending email to: $to, Subject: $subject");
        $start_time = microtime(true);
        
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
                        require_once dirname($path) . '/SMTP.php';
                        require_once dirname($path) . '/Exception.php';
                        $loaded = true;
                        break;
                    }
                }
                
                if (!$loaded) {
                    $this->log_email_to_database($to, $subject, $message, 'failed', 'PHPMailer not available', 0, $headers);
                    return array(
                        'success' => false,
                        'message' => 'PHPMailer not available'
                    );
                }
            }
            
            $mail = new PHPMailer\PHPMailer\PHPMailer(true);
            $mail->isSMTP();
            $mail->Host = $this->smtp_settings['host'];
            $mail->Port = $this->smtp_settings['port'];
            $mail->SMTPSecure = $this->smtp_settings['encryption'];
            $mail->SMTPAuth = true;
            $mail->Username = $this->smtp_settings['username'];
            $mail->Password = $this->smtp_settings['password'];
            $mail->SMTPOptions = array(
                'ssl' => array(
                    'verify_peer' => false,
                    'verify_peer_name' => false,
                    'allow_self_signed' => true
                )
            );
            $mail->Timeout = 30;
            $mail->SMTPKeepAlive = false;
            $mail->setFrom($this->smtp_settings['sender_email'], $this->smtp_settings['sender_name']);
            if (!empty($this->smtp_settings['reply_to'])) {
                $mail->addReplyTo($this->smtp_settings['reply_to']);
            }
            $mail->addAddress($to);
            $mail->Subject = $this->encode_subject($subject);
            $mail->Body = $message;
            $mail->isHTML(true);
            $mail->CharSet = 'UTF-8';
            if (!empty($headers)) {
                foreach ($headers as $header) {
                    $mail->addCustomHeader($header);
                }
            }
            
            $this->log_info('Email configured, attempting to send...');
            if ($mail->send()) {
                $processing_time = round((microtime(true) - $start_time) * 1000);
                $this->log_info("Email sent successfully to: $to");
                $this->log_email_to_database($to, $subject, $message, 'success', '', $processing_time, $headers);
                
                return array('success' => true, 'message' => 'Email sent successfully');
            } else {
                $processing_time = round((microtime(true) - $start_time) * 1000);
                $error_msg = $mail->ErrorInfo;
                $this->log_error("Failed to send email to: $to - " . $error_msg);
                $this->log_email_to_database($to, $subject, $message, 'failed', $error_msg, $processing_time, $headers);
                
                return array('success' => false, 'message' => 'Failed to send email: ' . $error_msg);
            }
            
        } catch (Exception $e) {
            $processing_time = round((microtime(true) - $start_time) * 1000);
            $error_msg = $e->getMessage();
            $this->log_error('Email sending failed: ' . $error_msg);
            $this->log_email_to_database($to, $subject, $message, 'failed', $error_msg, $processing_time, $headers);
            
            return array(
                'success' => false,
                'message' => 'Email sending failed: ' . $error_msg
            );
        }
    }
    
    
    private function log_email_to_database($recipient_email, $subject, $message, $status, $error_message = '', $processing_time = 0, $headers = array()) {
        try {
            global $wpdb;
            $table_name = $wpdb->prefix . 'nexora_email_logs';
            $table_exists = $wpdb->get_var("SHOW TABLES LIKE '$table_name'") == $table_name;
            
            if (!$table_exists) {
                $this->log_warning('Email logs table does not exist, cannot log email');
                return false;
            }
            $email_type = 'notification';
            foreach ($headers as $header) {
                if (strpos($header, 'X-Email-Type:') === 0) {
                    $type = trim(substr($header, strlen('X-Email-Type:')));
                    if (in_array($type, array('test', 'notification', 'system', 'error'))) {
                        $email_type = $type;
                        break;
                    }
                }
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
                    'smtp_host' => $this->smtp_settings['host'],
                    'smtp_port' => $this->smtp_settings['port'],
                    'smtp_username' => $this->smtp_settings['username'],
                    'encryption' => $this->smtp_settings['encryption'],
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
                    '%d',
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
                $this->log_info("Email logged to database successfully. Log ID: " . $wpdb->insert_id . ", Type: " . $email_type);
                return true;
            } else {
                $this->log_error("Failed to log email to database: " . $wpdb->last_error);
                return false;
            }
            
        } catch (Exception $e) {
            $this->log_error('Exception logging email to database: ' . $e->getMessage());
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
    
    
    public function get_admin_emails($notification_type = 'all') {
        try {
            global $wpdb;
            $table_name = $wpdb->prefix . 'nexora_admin_emails';
            $table_exists = $wpdb->get_var("SHOW TABLES LIKE '$table_name'") == $table_name;
            if (!$table_exists) {
                $this->log_error('Admin emails table does not exist');
                return array();
            }
            $query = "SELECT * FROM $table_name WHERE is_active = 1";
            if ($notification_type !== 'all') {
                $query .= " AND (notification_types LIKE '%\"all\"%' OR notification_types LIKE '%\"$notification_type\"%')";
                $admin_emails = $wpdb->get_results($query);
                if (empty($admin_emails)) {
                    $this->log_warning("No admin emails found for notification type: $notification_type, falling back to 'all'");
                    $query = "SELECT * FROM $table_name WHERE is_active = 1 AND (notification_types LIKE '%\"all\"%') ORDER BY role, display_name";
                }
            }
            
            $query .= " ORDER BY role, display_name";
            
            $admin_emails = $wpdb->get_results($query);
            
            if ($admin_emails === false) {
                $this->log_error('Failed to retrieve admin emails: ' . $wpdb->last_error);
                return array();
            }
            
            $this->log_info('Retrieved ' . count($admin_emails) . ' admin emails for notification type: ' . $notification_type);
            return $admin_emails;
            
        } catch (Exception $e) {
            $this->log_error('Exception getting admin emails: ' . $e->getMessage());
            return array();
        }
    }
    
    
    public function send_admin_notification($subject, $message, $notification_type = 'all', $additional_recipients = array()) {
        try {
            $admin_emails = $this->get_admin_emails($notification_type);
            
            if (empty($admin_emails)) {
                $this->log_warning('No admin emails found for notification type: ' . $notification_type);
                return array(
                    'success' => false,
                    'message' => 'No admin emails found for this notification type'
                );
            }
            $recipients = array();
            foreach ($admin_emails as $admin) {
                $recipients[] = array(
                    'email' => $admin->email_address,
                    'name' => $admin->display_name,
                    'type' => 'admin'
                );
            }
            if (!empty($additional_recipients)) {
                foreach ($additional_recipients as $recipient) {
                    $recipients[] = array(
                        'email' => $recipient['email'],
                        'name' => $recipient['name'] ?? '',
                        'type' => 'additional'
                    );
                }
            }
            
            $this->log_info('Sending notification to ' . count($recipients) . ' recipients');
            
            $success_count = 0;
            $failed_count = 0;
            $results = array();
            foreach ($recipients as $recipient) {
                $result = $this->send_email(
                    $recipient['email'],
                    $subject,
                    $message,
                    array(
                        'X-Notification-Type: ' . $notification_type,
                        'X-Recipient-Type: ' . $recipient['type']
                    )
                );
                
                $results[] = array(
                    'email' => $recipient['email'],
                    'name' => $recipient['name'],
                    'type' => $recipient['type'],
                    'success' => $result['success'],
                    'message' => $result['message']
                );
                
                if ($result['success']) {
                    $success_count++;
                } else {
                    $failed_count++;
                }
            }
            
            $this->log_info("Admin notification sent: $success_count successful, $failed_count failed");
            
            return array(
                'success' => $failed_count === 0,
                'message' => "Notification sent to $success_count recipients" . ($failed_count > 0 ? ", $failed_count failed" : ''),
                'results' => $results,
                'success_count' => $success_count,
                'failed_count' => $failed_count
            );
            
        } catch (Exception $e) {
            $this->log_error('Exception sending admin notification: ' . $e->getMessage());
            return array(
                'success' => false,
                'message' => 'Exception sending admin notification: ' . $e->getMessage()
            );
        }
    }
    
    
    public function send_customer_notification($customer_email, $customer_name, $subject, $message, $notification_type = 'customer') {
        try {
            if (empty($customer_email) || !is_email($customer_email)) {
                $this->log_warning('Invalid customer email provided: ' . $customer_email);
                return array(
                    'success' => false,
                    'message' => 'Invalid customer email address'
                );
            }
            
            $this->log_info("Sending customer notification to: $customer_email, Type: $notification_type");
            $result = $this->send_email(
                $customer_email,
                $subject,
                $message,
                array(
                    'X-Notification-Type: ' . $notification_type,
                    'X-Recipient-Type: customer',
                    'X-Customer-Name: ' . $customer_name
                )
            );
            
            if ($result['success']) {
                $this->log_info("Customer notification sent successfully to: $customer_email");
                return array(
                    'success' => true,
                    'message' => 'Customer notification sent successfully',
                    'email' => $customer_email,
                    'customer_name' => $customer_name
                );
            } else {
                $this->log_error("Failed to send customer notification to: $customer_email - " . $result['message']);
                return array(
                    'success' => false,
                    'message' => 'Failed to send customer notification: ' . $result['message'],
                    'email' => $customer_email,
                    'customer_name' => $customer_name
                );
            }
            
        } catch (Exception $e) {
            $this->log_error('Exception sending customer notification: ' . $e->getMessage());
            return array(
                'success' => false,
                'message' => 'Exception sending customer notification: ' . $e->getMessage(),
                'email' => $customer_email,
                'customer_name' => $customer_name
            );
        }
    }
    
    
    public function send_test_email($to_email, $subject = 'Test Email - Independent System') {
        $message = sprintf(
            'This is a test email sent via the Independent Email System at %s.<br><br>' .
            'SMTP Settings:<br>' .
            'Host: %s<br>' .
            'Port: %s<br>' .
            'Encryption: %s<br>' .
            'Username: %s<br><br>' .
            'If you receive this email, the SMTP configuration is working correctly.',
            $this->get_current_time(),
            $this->smtp_settings['host'],
            $this->smtp_settings['port'],
            $this->smtp_settings['encryption'],
            $this->smtp_settings['username']
        );
        
        $headers = array(
            'Content-Type: text/html; charset=UTF-8',
            'From: ' . $this->smtp_settings['sender_name'] . ' <' . $this->smtp_settings['sender_email'] . '>',
            'X-Email-Type: test'
        );
        
        if (!empty($this->smtp_settings['reply_to'])) {
            $headers[] = 'Reply-To: ' . $this->smtp_settings['reply_to'];
        }
        
        return $this->send_email($to_email, $subject, $message, $headers);
    }
    
    
    private function encode_subject($subject) {
        if (mb_check_encoding($subject, 'UTF-8') && !mb_check_encoding($subject, 'ASCII')) {
            return '=?UTF-8?B?' . base64_encode($subject) . '?=';
        }
        
        return $subject;
    }
    
    
    private function get_current_time($format = 'Y-m-d H:i:s') {
        return date($format);
    }
    
    
    private function log_info($message) {
        $this->write_log('INFO', $message);
    }
    
    
    private function log_error($message) {
        $this->write_log('ERROR', $message);
    }
    
    
    private function log_warning($message) {
        $this->write_log('WARNING', $message);
    }
    
    
    private function write_log($level, $message) {
        if (!is_writable(dirname($this->log_file))) {
            return;
        }
        
        $log_entry = sprintf("[%s] [%s] %s\n", $this->get_current_time('Y-m-d H:i:s'), $level, $message);
        file_put_contents($this->log_file, $log_entry, FILE_APPEND | LOCK_EX);
    }
    
    
    public function get_system_status() {
        return array(
            'enabled' => $this->smtp_settings['enabled'],
            'host' => $this->smtp_settings['host'],
            'port' => $this->smtp_settings['port'],
            'encryption' => $this->smtp_settings['encryption'],
            'username' => $this->smtp_settings['username'],
            'password_set' => !empty($this->smtp_settings['password']),
            'sender_name' => $this->smtp_settings['sender_name'],
            'sender_email' => $this->smtp_settings['sender_email'],
            'reply_to' => $this->smtp_settings['reply_to'],
            'config_file' => $this->config_file,
            'log_file' => $this->log_file
        );
    }
    
    
    public function reset_to_defaults() {
        $this->smtp_settings = array(
            'host' => 'localhost',
            'port' => 587,
            'encryption' => 'tls',
            'username' => '',
            'password' => '',
            'auth_mode' => 'login',
            'sender_name' => 'Nexora Service Suite',
            'sender_email' => 'noreply@example.com',
            'reply_to' => 'support@example.com',
            'enabled' => false
        );
        
        return $this->save_smtp_settings();
    }
}
