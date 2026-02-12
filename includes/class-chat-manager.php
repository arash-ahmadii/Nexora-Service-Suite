<?php

if (!defined('ABSPATH')) {
    exit;
}

class Nexora_Chat_Manager {
    
    private $sessions_table;
    private $messages_table;
    private $notifications_table;
    
    public function __construct() {
        global $wpdb;
        $this->sessions_table = $wpdb->prefix . 'nexora_chat_sessions';
        $this->messages_table = $wpdb->prefix . 'nexora_chat_messages';
        $this->notifications_table = $wpdb->prefix . 'nexora_chat_notifications';
        add_action('wp_ajax_nexora_send_message', array($this, 'ajax_send_message'));
        add_action('wp_ajax_nexora_get_messages', array($this, 'ajax_get_messages'));
        add_action('wp_ajax_nexora_mark_messages_read', array($this, 'ajax_mark_messages_read'));
        add_action('wp_ajax_nexora_get_unread_count', array($this, 'ajax_get_unread_count'));
        add_action('wp_ajax_nexora_upload_chat_file', array($this, 'ajax_upload_chat_file'));
        add_action('wp_ajax_nopriv_nexora_send_message', array($this, 'ajax_send_message'));
        add_action('wp_ajax_nopriv_nexora_get_messages', array($this, 'ajax_get_messages'));
    }
    
    
    public function get_or_create_session($request_id, $user_id = null, $admin_id = null) {
        global $wpdb;
        
        if (!$user_id) {
            $user_id = get_current_user_id();
        }
        $session = $wpdb->get_row($wpdb->prepare(
            "SELECT * FROM {$this->sessions_table} WHERE request_id = %d",
            $request_id
        ));
        
        if ($session) {
            error_log('Chat Manager: Found existing session for request ' . $request_id . ' (Session ID: ' . $session->id . ')');
            return $session;
        }
        
        error_log('Chat Manager: Creating new session for request ' . $request_id . ', user ' . $user_id . ', admin ' . $admin_id);
        $session_data = array(
            'request_id' => $request_id,
            'user_id' => $user_id,
            'admin_id' => $admin_id ?: null,
            'status' => 'active'
        );
        
        $result = $wpdb->insert($this->sessions_table, $session_data);
        
        if ($result) {
            $session_id = $wpdb->insert_id;
            error_log('Chat Manager: Session created successfully with ID: ' . $session_id);
            return $wpdb->get_row($wpdb->prepare(
                "SELECT * FROM {$this->sessions_table} WHERE id = %d",
                $session_id
            ));
        } else {
            error_log('Chat Manager: Failed to create session. Error: ' . $wpdb->last_error);
            error_log('Chat Manager: Session data: ' . print_r($session_data, true));
        }
        
        return false;
    }
    
    
    public function send_message($session_id, $sender_id, $sender_type, $message, $message_type = 'text', $file_data = null) {
        global $wpdb;
        
        $message_data = array(
            'session_id' => $session_id,
            'sender_id' => $sender_id,
            'sender_type' => $sender_type,
            'message' => $message,
            'message_type' => $message_type,
            'is_read' => 0
        );
        if ($file_data && $message_type !== 'text') {
            $upload_result = $this->handle_file_upload($file_data);
            if ($upload_result['success']) {
                $message_data['file_path'] = $upload_result['file_path'];
                $message_data['file_name'] = $upload_result['file_name'];
                $message_data['file_size'] = $upload_result['file_size'];
            } else {
                return array('success' => false, 'error' => $upload_result['error']);
            }
        }
        
        $result = $wpdb->insert($this->messages_table, $message_data);
        
        if ($result) {
            $message_id = $wpdb->insert_id;
            error_log('Chat Manager: Message sent successfully with ID: ' . $message_id);
            $this->create_notification($session_id, $message_id, $sender_id, $sender_type);
            $wpdb->update(
                $this->sessions_table,
                array('updated_at' => current_time('mysql')),
                array('id' => $session_id)
            );
            
            return array('success' => true, 'message_id' => $message_id);
        } else {
            error_log('Chat Manager: Failed to send message. Error: ' . $wpdb->last_error);
            error_log('Chat Manager: Message data: ' . print_r($message_data, true));
        }
        
        return array('success' => false, 'error' => 'Failed to send message');
    }
    
    
    public function get_messages($session_id, $limit = 50, $offset = 0) {
        global $wpdb;
        
        $messages = $wpdb->get_results($wpdb->prepare(
            "SELECT m.*, u.display_name as sender_name
             FROM {$this->messages_table} m
             LEFT JOIN {$wpdb->prefix}users u ON m.sender_id = u.ID
             WHERE m.session_id = %d
             ORDER BY m.created_at ASC
             LIMIT %d OFFSET %d",
            $session_id, $limit, $offset
        ));
        
        return $messages;
    }
    
    
    public function mark_messages_read($session_id, $user_id, $sender_type) {
        global $wpdb;
        $wpdb->update(
            $this->messages_table,
            array(
                'is_read' => 1,
                'read_at' => current_time('mysql')
            ),
            array(
                'session_id' => $session_id,
                'sender_type' => $sender_type
            )
        );
        $wpdb->update(
            $this->notifications_table,
            array('is_read' => 1),
            array(
                'user_id' => $user_id,
                'session_id' => $session_id
            )
        );
    }
    
    
    public function get_unread_count($user_id, $session_id = null) {
        global $wpdb;
        
        $where = array('user_id' => $user_id, 'is_read' => 0);
        if ($session_id) {
            $where['session_id'] = $session_id;
        }
        
        $count = $wpdb->get_var($wpdb->prepare(
            "SELECT COUNT(*) FROM {$this->notifications_table} 
             WHERE user_id = %d AND is_read = 0" . 
             ($session_id ? " AND session_id = %d" : ""),
            $user_id, $session_id
        ));
        
        return intval($count);
    }
    
    
    public function handle_file_upload($file_data) {
        $upload_dir = wp_upload_dir();
        $chat_upload_dir = $upload_dir['basedir'] . '/Nexora Service Suite-chat-files';
        if (!file_exists($chat_upload_dir)) {
            wp_mkdir_p($chat_upload_dir);
        }
        $allowed_types = array('jpg', 'jpeg', 'png', 'gif', 'pdf', 'doc', 'docx', 'txt');
        $file_extension = strtolower(pathinfo($file_data['name'], PATHINFO_EXTENSION));
        
        if (!in_array($file_extension, $allowed_types)) {
            return array('success' => false, 'error' => 'File type not allowed');
        }
        if ($file_data['size'] > 5 * 1024 * 1024) {
            return array('success' => false, 'error' => 'File too large (max 5MB)');
        }
        $filename = uniqid() . '_' . sanitize_file_name($file_data['name']);
        $file_path = $chat_upload_dir . '/' . $filename;
        if (move_uploaded_file($file_data['tmp_name'], $file_path)) {
            return array(
                'success' => true,
                'file_path' => $file_path,
                'file_name' => $file_data['name'],
                'file_size' => $file_data['size']
            );
        }
        
        return array('success' => false, 'error' => 'Failed to upload file');
    }
    
    
    private function create_notification($session_id, $message_id, $sender_id, $sender_type) {
        global $wpdb;
        $session = $wpdb->get_row($wpdb->prepare(
            "SELECT * FROM {$this->sessions_table} WHERE id = %d",
            $session_id
        ));
        
        if (!$session) {
            return false;
        }
        $recipient_id = ($sender_type === 'user') ? $session->admin_id : $session->user_id;
        
        if (!$recipient_id) {
            return false;
        }
        $notification_data = array(
            'user_id' => $recipient_id,
            'session_id' => $session_id,
            'message_id' => $message_id,
            'notification_type' => 'new_message',
            'is_read' => 0
        );
        
        return $wpdb->insert($this->notifications_table, $notification_data);
    }
    
    
    public function ajax_send_message() {
        check_ajax_referer('nexora_chat_nonce', 'nonce');
        
        $session_id = intval($_POST['session_id']);
        $message = sanitize_textarea_field($_POST['message']);
        $message_type = sanitize_text_field($_POST['message_type'] ?? 'text');
        
        if (empty($message) && $message_type === 'text') {
            wp_send_json_error('Message cannot be empty');
        }
        
        $user_id = get_current_user_id();
        $sender_type = current_user_can('manage_options') ? 'admin' : 'user';
        
        $result = $this->send_message($session_id, $user_id, $sender_type, $message, $message_type);
        
        if ($result['success']) {
            wp_send_json_success(array('message_id' => $result['message_id']));
        } else {
            wp_send_json_error($result['error']);
        }
    }
    
    
    public function ajax_get_messages() {
        check_ajax_referer('nexora_chat_nonce', 'nonce');
        
        $session_id = intval($_POST['session_id']);
        $limit = intval($_POST['limit'] ?? 50);
        $offset = intval($_POST['offset'] ?? 0);
        
        $messages = $this->get_messages($session_id, $limit, $offset);
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
                'time_formatted' => $this->format_time($message->created_at)
            );
        }
        
        wp_send_json_success($formatted_messages);
    }
    
    
    public function ajax_mark_messages_read() {
        check_ajax_referer('nexora_chat_nonce', 'nonce');
        
        $session_id = intval($_POST['session_id']);
        $user_id = get_current_user_id();
        $sender_type = current_user_can('manage_options') ? 'admin' : 'user';
        
        $this->mark_messages_read($session_id, $user_id, $sender_type);
        
        wp_send_json_success();
    }
    
    
    public function ajax_get_unread_count() {
        check_ajax_referer('nexora_chat_nonce', 'nonce');
        
        $user_id = get_current_user_id();
        $session_id = isset($_POST['session_id']) ? intval($_POST['session_id']) : null;
        
        $count = $this->get_unread_count($user_id, $session_id);
        
        wp_send_json_success(array('count' => $count));
    }
    
    
    public function ajax_upload_chat_file() {
        check_ajax_referer('nexora_chat_nonce', 'nonce');
        
        if (!isset($_FILES['file'])) {
            wp_send_json_error('No file uploaded');
        }
        
        $file_data = $_FILES['file'];
        $upload_result = $this->handle_file_upload($file_data);
        
        if ($upload_result['success']) {
            wp_send_json_success($upload_result);
        } else {
            wp_send_json_error($upload_result['error']);
        }
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
    
    
    public function get_session_by_request($request_id) {
        global $wpdb;
        
        return $wpdb->get_row($wpdb->prepare(
            "SELECT * FROM {$this->sessions_table} WHERE request_id = %d",
            $request_id
        ));
    }
    
    
    public function can_access_session($session_id, $user_id = null) {
        if (!$user_id) {
            $user_id = get_current_user_id();
        }
        
        global $wpdb;
        $session = $wpdb->get_row($wpdb->prepare(
            "SELECT * FROM {$this->sessions_table} WHERE id = %d",
            $session_id
        ));
        
        if (!$session) {
            return false;
        }
        if (current_user_can('manage_options')) {
            return true;
        }
        return $session->user_id == $user_id;
    }
}
