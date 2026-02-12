<?php

if (!defined('ABSPATH')) {
    exit;
}

class Nexora_Chat_AJAX {
    
    private $chat_manager;
    
    public function __construct() {
        $this->chat_manager = new Nexora_Chat_Manager();
        add_action('wp_ajax_nexora_chat_send_message', array($this, 'send_message'));
        add_action('wp_ajax_nexora_chat_get_messages', array($this, 'get_messages'));
        add_action('wp_ajax_nexora_chat_mark_read', array($this, 'mark_messages_read'));
        add_action('wp_ajax_nexora_chat_get_unread', array($this, 'get_unread_count'));
        add_action('wp_ajax_nexora_chat_upload_file', array($this, 'upload_file'));
        add_action('wp_ajax_nexora_chat_get_session', array($this, 'get_or_create_session'));
        add_action('wp_ajax_nopriv_nexora_chat_send_message', array($this, 'send_message'));
        add_action('wp_ajax_nopriv_nexora_chat_get_messages', array($this, 'get_messages'));
        add_action('wp_ajax_nexora_chat_debug', array($this, 'debug_chat_system'));
    }
    
    
    public function send_message() {
        $nonce_valid = false;
        if (isset($_POST['nonce'])) {
            $nonce_valid = wp_verify_nonce($_POST['nonce'], 'nexora_chat_nonce') ||
                          wp_verify_nonce($_POST['nonce'], 'nexora_nonce') ||
                          wp_verify_nonce($_POST['nonce'], 'nexora_user_nonce');
        }
        
        if (!$nonce_valid) {
            wp_send_json_error('Invalid nonce');
        }
        if (!is_user_logged_in()) {
            wp_send_json_error('User not logged in');
        }
        
        $session_id = intval($_POST['session_id']);
        $message = sanitize_textarea_field($_POST['message']);
        $message_type = sanitize_text_field($_POST['message_type'] ?? 'text');
        if (empty($message) && $message_type === 'text') {
            wp_send_json_error('Message cannot be empty');
        }
        
        if (!$session_id) {
            wp_send_json_error('Invalid session ID');
        }
        if (!$this->chat_manager->can_access_session($session_id)) {
            wp_send_json_error('Access denied');
        }
        
        $user_id = get_current_user_id();
        $sender_type = current_user_can('manage_options') ? 'admin' : 'user';
        $file_data = null;
        if (isset($_FILES['file']) && !empty($_FILES['file']['name'])) {
            $file_data = $_FILES['file'];
            $message_type = $this->get_file_type($_FILES['file']['name']);
        }
        
        $result = $this->chat_manager->send_message($session_id, $user_id, $sender_type, $message, $message_type, $file_data);
        
        if ($result['success']) {
            wp_send_json_success(array(
                'message_id' => $result['message_id'],
                'message' => 'Message sent successfully'
            ));
        } else {
            error_log('Chat AJAX: Send message failed. Error: ' . $result['error']);
            wp_send_json_error($result['error']);
        }
    }
    
    
    public function get_messages() {
        $nonce_valid = false;
        if (isset($_POST['nonce'])) {
            $nonce_valid = wp_verify_nonce($_POST['nonce'], 'nexora_chat_nonce') ||
                          wp_verify_nonce($_POST['nonce'], 'nexora_nonce') ||
                          wp_verify_nonce($_POST['nonce'], 'nexora_user_nonce');
        }
        
        if (!$nonce_valid) {
            wp_send_json_error('Invalid nonce');
        }
        if (!is_user_logged_in()) {
            wp_send_json_error('User not logged in');
        }
        
        $session_id = intval($_POST['session_id']);
        $limit = intval($_POST['limit'] ?? 50);
        $offset = intval($_POST['offset'] ?? 0);
        
        if (!$session_id) {
            wp_send_json_error('Invalid session ID');
        }
        if (!$this->chat_manager->can_access_session($session_id)) {
            wp_send_json_error('Access denied');
        }
        
        $messages = $this->chat_manager->get_messages($session_id, $limit, $offset);
        $formatted_messages = array();
        foreach ($messages as $message) {
            $formatted_messages[] = array(
                'id' => $message->id,
                'sender_id' => $message->sender_id,
                'sender_type' => $message->sender_type,
                'sender_name' => $message->sender_name,
                'message' => $message->message,
                'message_type' => $message->message_type,
                'file_name' => $message->file_name,
                'file_size' => $message->file_size,
                'is_read' => $message->is_read,
                'created_at' => $message->created_at,
                'time_formatted' => $this->format_time($message->created_at),
                'file_url' => $this->get_file_url($message->file_path)
            );
        }
        
        wp_send_json_success($formatted_messages);
    }
    
    
    public function mark_messages_read() {
        $nonce_valid = false;
        if (isset($_POST['nonce'])) {
            $nonce_valid = wp_verify_nonce($_POST['nonce'], 'nexora_chat_nonce') ||
                          wp_verify_nonce($_POST['nonce'], 'nexora_nonce') ||
                          wp_verify_nonce($_POST['nonce'], 'nexora_user_nonce');
        }
        
        if (!$nonce_valid) {
            wp_send_json_error('Invalid nonce');
        }
        if (!is_user_logged_in()) {
            wp_send_json_error('User not logged in');
        }
        
        $session_id = intval($_POST['session_id']);
        
        if (!$session_id) {
            wp_send_json_error('Invalid session ID');
        }
        if (!$this->chat_manager->can_access_session($session_id)) {
            wp_send_json_error('Access denied');
        }
        
        $user_id = get_current_user_id();
        $sender_type = current_user_can('manage_options') ? 'admin' : 'user';
        
        $this->chat_manager->mark_messages_read($session_id, $user_id, $sender_type);
        
        wp_send_json_success('Messages marked as read');
    }
    
    
    public function get_unread_count() {
        $nonce_valid = false;
        if (isset($_POST['nonce'])) {
            $nonce_valid = wp_verify_nonce($_POST['nonce'], 'nexora_chat_nonce') ||
                          wp_verify_nonce($_POST['nonce'], 'nexora_nonce') ||
                          wp_verify_nonce($_POST['nonce'], 'nexora_user_nonce');
        }
        
        if (!$nonce_valid) {
            wp_send_json_error('Invalid nonce');
        }
        if (!is_user_logged_in()) {
            wp_send_json_error('User not logged in');
        }
        
        $user_id = get_current_user_id();
        $session_id = isset($_POST['session_id']) ? intval($_POST['session_id']) : null;
        
        $count = $this->chat_manager->get_unread_count($user_id, $session_id);
        
        wp_send_json_success(array('count' => $count));
    }
    
    
    public function upload_file() {
        $nonce_valid = false;
        if (isset($_POST['nonce'])) {
            $nonce_valid = wp_verify_nonce($_POST['nonce'], 'nexora_chat_nonce') ||
                          wp_verify_nonce($_POST['nonce'], 'nexora_nonce') ||
                          wp_verify_nonce($_POST['nonce'], 'nexora_user_nonce');
        }
        
        if (!$nonce_valid) {
            wp_send_json_error('Invalid nonce');
        }
        if (!is_user_logged_in()) {
            wp_send_json_error('User not logged in');
        }
        
        if (!isset($_FILES['file'])) {
            wp_send_json_error('No file uploaded');
        }
        
        $file_data = $_FILES['file'];
        $validation_result = $this->validate_file($file_data);
        if (!$validation_result['valid']) {
            wp_send_json_error($validation_result['error']);
        }
        
        $upload_result = $this->chat_manager->handle_file_upload($file_data);
        
        if ($upload_result['success']) {
            wp_send_json_success(array(
                'file_name' => $upload_result['file_name'],
                'file_size' => $upload_result['file_size'],
                'file_url' => $this->get_file_url($upload_result['file_path'])
            ));
        } else {
            wp_send_json_error($upload_result['error']);
        }
    }
    
    
    public function get_or_create_session() {
        $nonce_valid = false;
        if (isset($_POST['nonce'])) {
            $nonce_valid = wp_verify_nonce($_POST['nonce'], 'nexora_chat_nonce') ||
                          wp_verify_nonce($_POST['nonce'], 'nexora_nonce') ||
                          wp_verify_nonce($_POST['nonce'], 'nexora_user_nonce');
        }
        
        if (!$nonce_valid) {
            wp_send_json_error('Invalid nonce');
        }
        if (!is_user_logged_in()) {
            wp_send_json_error('User not logged in');
        }
        
        $request_id = intval($_POST['request_id']);
        
        if (!$request_id) {
            wp_send_json_error('Invalid request ID');
        }
        if (!$this->can_access_request($request_id)) {
            wp_send_json_error('Access denied to this request');
        }
        
        $user_id = get_current_user_id();
        $admin_id = current_user_can('manage_options') ? $user_id : null;
        
        $session = $this->chat_manager->get_or_create_session($request_id, $user_id, $admin_id);
        
        if ($session) {
            wp_send_json_success(array(
                'session_id' => $session->id,
                'request_id' => $session->request_id,
                'user_id' => $session->user_id,
                'admin_id' => $session->admin_id,
                'status' => $session->status
            ));
        } else {
            error_log('Chat AJAX: Failed to create or get session. Request ID: ' . $request_id . ', User ID: ' . $user_id . ', Admin ID: ' . $admin_id);
            wp_send_json_error('Failed to create or get session');
        }
    }
    
    
    private function can_access_request($request_id) {
        global $wpdb;
        if (current_user_can('manage_options')) {
            return true;
        }
        $user_id = get_current_user_id();
        $request = $wpdb->get_row($wpdb->prepare(
            "SELECT user_id FROM {$wpdb->prefix}nexora_service_requests WHERE id = %d",
            $request_id
        ));
        
        return $request && $request->user_id == $user_id;
    }
    
    
    private function validate_file($file_data) {
        $allowed_types = array('jpg', 'jpeg', 'png', 'gif', 'pdf', 'doc', 'docx', 'txt');
        $max_size = 5 * 1024 * 1024;
        if ($file_data['error'] !== UPLOAD_ERR_OK) {
            return array('valid' => false, 'error' => 'File upload error');
        }
        if ($file_data['size'] > $max_size) {
            return array('valid' => false, 'error' => 'File too large (max 5MB)');
        }
        $file_extension = strtolower(pathinfo($file_data['name'], PATHINFO_EXTENSION));
        if (!in_array($file_extension, $allowed_types)) {
            return array('valid' => false, 'error' => 'File type not allowed');
        }
        
        return array('valid' => true);
    }
    
    
    private function get_file_type($filename) {
        $extension = strtolower(pathinfo($filename, PATHINFO_EXTENSION));
        
        if (in_array($extension, array('jpg', 'jpeg', 'png', 'gif'))) {
            return 'image';
        } else {
            return 'file';
        }
    }
    
    
    private function get_file_url($file_path) {
        if (!$file_path) {
            return null;
        }
        
        $upload_dir = wp_upload_dir();
        $relative_path = str_replace($upload_dir['basedir'], '', $file_path);
        
        return $upload_dir['baseurl'] . $relative_path;
    }
    
    
    private function format_time($timestamp) {
        $time = strtotime($timestamp);
        $now = time();
        $diff = $now - $time;
        
        if ($diff < 60) {
            return 'gerade eben';
        } elseif ($diff < 3600) {
            return floor($diff / 60) . ' Min.';
        } elseif ($diff < 86400) {
            return floor($diff / 3600) . ' Std.';
        } else {
            return date('d.m.Y H:i', $time);
        }
    }
    
    
    public function debug_chat_system() {
        global $wpdb;
        
        $debug_info = array(
            'user_logged_in' => is_user_logged_in(),
            'user_id' => get_current_user_id(),
            'is_admin' => current_user_can('manage_options'),
            'tables_exist' => array(),
            'ajax_actions_registered' => array(),
            'nonce_test' => array()
        );
        $tables = array(
            'sessions' => $wpdb->prefix . 'nexora_chat_sessions',
            'messages' => $wpdb->prefix . 'nexora_chat_messages',
            'notifications' => $wpdb->prefix . 'nexora_chat_notifications'
        );
        
        foreach ($tables as $name => $table) {
            $debug_info['tables_exist'][$name] = $wpdb->get_var("SHOW TABLES LIKE '$table'") ? true : false;
        }
        $actions = array(
            'nexora_chat_send_message',
            'nexora_chat_get_messages',
            'nexora_chat_mark_read',
            'nexora_chat_get_unread',
            'nexora_chat_upload_file',
            'nexora_chat_get_session'
        );
        
        foreach ($actions as $action) {
            $debug_info['ajax_actions_registered'][$action] = has_action('wp_ajax_' . $action);
        }
        $debug_info['nonce_test'] = array(
            'nexora_nonce' => wp_verify_nonce(wp_create_nonce('nexora_nonce'), 'nexora_nonce'),
            'nexora_user_nonce' => wp_verify_nonce(wp_create_nonce('nexora_user_nonce'), 'nexora_user_nonce'),
            'nexora_chat_nonce' => wp_verify_nonce(wp_create_nonce('nexora_chat_nonce'), 'nexora_chat_nonce')
        );
        
        wp_send_json_success($debug_info);
    }
}
