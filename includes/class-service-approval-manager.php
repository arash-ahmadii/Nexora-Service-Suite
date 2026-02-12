<?php

if (!defined('ABSPATH')) {
    exit;
}

class Nexora_Service_Approval_Manager {
    
    private $complete_service_requests_table;
    private $history_table;
    
    public function __construct() {
        global $wpdb;
        $this->complete_service_requests_table = $wpdb->prefix . 'nexora_complete_service_requests';
        $this->history_table = $wpdb->prefix . 'nexora_service_approval_history';
        add_action('wp_ajax_nexora_send_service_approval', array($this, 'ajax_send_service_approval'));
        add_action('wp_ajax_nexora_approve_service', array($this, 'ajax_approve_service'));
        add_action('wp_ajax_nexora_reject_service', array($this, 'ajax_reject_service'));
        add_action('wp_ajax_nexora_get_service_approvals', array($this, 'ajax_get_service_approvals'));
        add_action('wp_ajax_nexora_cancel_service_approval', array($this, 'ajax_cancel_service_approval'));
        add_action('wp_ajax_nexora_get_service_approval_status', array($this, 'ajax_get_service_approval_status'));
        add_action('wp_ajax_nexora_clear_service_approval', array($this, 'ajax_clear_service_approval'));
        add_action('wp_ajax_nopriv_nexora_approve_service', array($this, 'ajax_approve_service'));
        add_action('wp_ajax_nopriv_nexora_reject_service', array($this, 'ajax_reject_service'));
    }
    
    
    public function send_service_approval($request_id, $service_id, $admin_id, $admin_note = '') {
        global $wpdb;
        $service = $wpdb->get_row($wpdb->prepare(
            "SELECT * FROM {$wpdb->prefix}nexora_services WHERE id = %d",
            $service_id
        ));
        
        if (!$service) {
            return array('success' => false, 'error' => 'Service not found');
        }
        $complete_request = $wpdb->get_row($wpdb->prepare(
            "SELECT * FROM {$this->complete_service_requests_table} WHERE request_id = %d",
            $request_id
        ));
        
        if (!$complete_request) {
            $service_data = array(
                array(
                    'service_id' => $service_id,
                    'service_title' => $service->title,
                    'service_cost' => $service->cost,
                    'quantity' => 1,
                    'description' => $service->description,
                    'approval_status' => 'pending',
                    'approval_admin_id' => $admin_id,
                    'approval_admin_note' => $admin_note,
                    'approval_created_at' => current_time('mysql')
                )
            );
            
            $complete_data = array(
                'request_id' => $request_id,
                'services_data' => json_encode($service_data),
                'approval_status' => 'pending',
                'approval_admin_id' => $admin_id,
                'approval_admin_note' => $admin_note,
                'approval_created_at' => current_time('mysql'),
                'approval_updated_at' => current_time('mysql')
            );
            
            $result = $wpdb->insert($this->complete_service_requests_table, $complete_data);
            $approval_id = $wpdb->insert_id;
        } else {
            $services_data = json_decode($complete_request->services_data, true) ?: array();
            $service_exists = false;
            foreach ($services_data as &$existing_service) {
                if (isset($existing_service['service_id']) && $existing_service['service_id'] == $service_id) {
                    $existing_service['approval_status'] = 'pending';
                    $existing_service['approval_admin_id'] = $admin_id;
                    $existing_service['approval_admin_note'] = $admin_note;
                    $existing_service['approval_created_at'] = current_time('mysql');
                    $service_exists = true;
                    break;
                }
            }
            if (!$service_exists) {
                $services_data[] = array(
                    'service_id' => $service_id,
                    'service_title' => $service->title,
                    'service_cost' => $service->cost,
                    'quantity' => 1,
                    'description' => $service->description,
                    'approval_status' => 'pending',
                    'approval_admin_id' => $admin_id,
                    'approval_admin_note' => $admin_note,
                    'approval_created_at' => current_time('mysql')
                );
            }
            
            $update_data = array(
                'services_data' => json_encode($services_data),
                'approval_status' => 'pending',
                'approval_admin_id' => $admin_id,
                'approval_admin_note' => $admin_note,
                'approval_updated_at' => current_time('mysql')
            );
            
            $result = $wpdb->update(
                $this->complete_service_requests_table,
                $update_data,
                array('request_id' => $request_id)
            );
            $approval_id = $complete_request->id;
        }
        
        if ($result) {
            $this->log_approval_history($approval_id, 'created', $admin_id, 'admin', 'Service approval request created', 'complete_service_requests');
            $this->send_approval_chat_message($request_id, $approval_id, $service, $admin_note, 'complete_service_requests');
            
            return array('success' => true, 'approval_id' => $approval_id, 'action' => 'updated', 'table' => 'complete_service_requests');
        }
        
        return array('success' => false, 'error' => 'Failed to create approval request: ' . $wpdb->last_error);
    }
    
    
    public function approve_service($request_id, $customer_id, $customer_note = '', $table_type = 'complete_service_requests') {
        global $wpdb;
        $approval = $wpdb->get_row($wpdb->prepare(
            "SELECT * FROM {$this->complete_service_requests_table} WHERE request_id = %d",
            $request_id
        ));
        
        if (!$approval) {
            return array('success' => false, 'error' => 'Approval not found');
        }
        $services_data = json_decode($approval->services_data, true) ?: array();
        $updated = false;
        error_log('Services data for approval: ' . print_r($services_data, true));
        
        foreach ($services_data as &$service) {
            error_log('Checking service: ' . print_r($service, true));
            if (isset($service['approval_status']) && $service['approval_status'] === 'pending') {
                $service['approval_status'] = 'approved';
                $service['approval_customer_response'] = $customer_note;
                $service['approval_customer_response_time'] = current_time('mysql');
                $updated = true;
                error_log('Approved service: ' . print_r($service, true));
                break;
            }
        }
        
        if (!$updated) {
            foreach ($services_data as &$service) {
                if (!isset($service['approval_status']) || $service['approval_status'] === 'none') {
                    $service['approval_status'] = 'approved';
                    $service['approval_customer_response'] = $customer_note;
                    $service['approval_customer_response_time'] = current_time('mysql');
                    $updated = true;
                    error_log('Approved service without status: ' . print_r($service, true));
                    break;
                }
            }
        }
        
        if (!$updated) {
            return array('success' => false, 'error' => 'No pending service found to approve');
        }
        $result = $wpdb->update(
            $this->complete_service_requests_table,
            array(
                'services_data' => json_encode($services_data),
                'approval_customer_response' => $customer_note,
                'approval_customer_response_time' => current_time('mysql'),
                'approval_updated_at' => current_time('mysql')
            ),
            array('request_id' => $request_id)
        );
        
        if ($result) {
            $this->log_approval_history($approval->id, 'approved', $customer_id, 'customer', $customer_note, $table_type);
            $this->send_status_chat_message($request_id, 'approved', $customer_note, $table_type);
            
            return array('success' => true, 'approval_id' => $approval->id);
        }
        
        return array('success' => false, 'error' => 'Failed to update approval status');
    }
    
    
    public function reject_service($request_id, $customer_id, $customer_note = '', $table_type = 'complete_service_requests') {
        global $wpdb;
        $approval = $wpdb->get_row($wpdb->prepare(
            "SELECT * FROM {$this->complete_service_requests_table} WHERE request_id = %d",
            $request_id
        ));
        
        if (!$approval) {
            return array('success' => false, 'error' => 'Approval not found');
        }
        $services_data = json_decode($approval->services_data, true) ?: array();
        $updated = false;
        error_log('Services data for rejection: ' . print_r($services_data, true));
        
        foreach ($services_data as &$service) {
            error_log('Checking service for rejection: ' . print_r($service, true));
            if (isset($service['approval_status']) && $service['approval_status'] === 'pending') {
                $service['approval_status'] = 'rejected';
                $service['approval_customer_response'] = $customer_note;
                $service['approval_customer_response_time'] = current_time('mysql');
                $updated = true;
                error_log('Rejected service: ' . print_r($service, true));
                break;
            }
        }
        
        if (!$updated) {
            foreach ($services_data as &$service) {
                if (!isset($service['approval_status']) || $service['approval_status'] === 'none') {
                    $service['approval_status'] = 'rejected';
                    $service['approval_customer_response'] = $customer_note;
                    $service['approval_customer_response_time'] = current_time('mysql');
                    $updated = true;
                    error_log('Rejected service without status: ' . print_r($service, true));
                    break;
                }
            }
        }
        
        if (!$updated) {
            return array('success' => false, 'error' => 'No pending service found to reject');
        }
        $result = $wpdb->update(
            $this->complete_service_requests_table,
            array(
                'services_data' => json_encode($services_data),
                'approval_customer_response' => $customer_note,
                'approval_customer_response_time' => current_time('mysql'),
                'approval_updated_at' => current_time('mysql')
            ),
            array('request_id' => $request_id)
        );
        
        if ($result) {
            $this->log_approval_history($approval->id, 'rejected', $customer_id, 'customer', $customer_note, $table_type);
            $this->send_status_chat_message($request_id, 'rejected', $customer_note, $table_type);
            
            return array('success' => true, 'approval_id' => $approval->id);
        }
        
        return array('success' => false, 'error' => 'Failed to update approval status');
    }
    
    
    public function cancel_service_approval($request_id, $admin_id, $table_type = 'complete_service_requests') {
        global $wpdb;
        $approval = $wpdb->get_row($wpdb->prepare(
            "SELECT * FROM {$this->complete_service_requests_table} WHERE request_id = %d AND approval_status = 'pending'",
            $request_id
        ));
        
        if (!$approval) {
            return array('success' => false, 'error' => 'Approval not found or not pending');
        }
        $result = $wpdb->update(
            $this->complete_service_requests_table,
            array(
                'approval_status' => 'none',
                'approval_admin_id' => null,
                'approval_admin_note' => null,
                'approval_customer_response' => null,
                'approval_customer_response_time' => null,
                'approval_updated_at' => current_time('mysql')
            ),
            array('request_id' => $request_id)
        );
        
        if ($result) {
            $this->log_approval_history($approval->id, 'cancelled', $admin_id, 'admin', 'Service approval cancelled', $table_type);
            
            return array('success' => true, 'approval_id' => $approval->id);
        }
        
        return array('success' => false, 'error' => 'Failed to cancel approval');
    }
    
    
    public function get_service_approvals($request_id) {
        global $wpdb;
        $approval = $wpdb->get_row($wpdb->prepare(
            "SELECT *, 'complete_service_requests' as table_type FROM {$this->complete_service_requests_table} 
             WHERE request_id = %d AND approval_status IS NOT NULL AND approval_status != '' AND approval_status != 'none'",
            $request_id
        ));
        
        return $approval ? array($approval) : array();
    }
    
    
    public function get_service_approval($approval_id, $table_type = 'complete_service_requests') {
        global $wpdb;
        
        $approval = $wpdb->get_row($wpdb->prepare(
            "SELECT * FROM {$this->complete_service_requests_table} WHERE id = %d",
            $approval_id
        ));
        
        if ($approval) {
            $approval->table_type = $table_type;
        }
        
        return $approval;
    }
    
    
    public function has_pending_approval($request_id, $service_id) {
        global $wpdb;
        $count = $wpdb->get_var($wpdb->prepare(
            "SELECT COUNT(*) FROM {$this->complete_service_requests_table} 
             WHERE request_id = %d AND approval_status IS NOT NULL AND approval_status = 'pending'",
            $request_id
        ));
        
        return $count > 0;
    }
    
    
    public function get_approval_status($request_id, $service_id) {
        global $wpdb;
        $complete_request = $wpdb->get_row($wpdb->prepare(
            "SELECT services_data, approval_status FROM {$this->complete_service_requests_table} 
             WHERE request_id = %d",
            $request_id
        ));
        
        if ($complete_request && !empty($complete_request->services_data)) {
            $services_data = json_decode($complete_request->services_data, true);
            
            if (is_array($services_data)) {
                foreach ($services_data as $service) {
                    if (isset($service['service_id']) && $service['service_id'] == $service_id) {
                        if (isset($service['approval_status'])) {
                            return $service['approval_status'];
                        }
                        break;
                    }
                }
            }
        }
        return 'none';
    }
    
    
    private function log_approval_history($approval_id, $action, $user_id, $user_type, $note = '', $table_type = 'service_requests') {
        global $wpdb;
        
        $history_data = array(
            'approval_id' => $approval_id,
            'action' => $action,
            'user_id' => $user_id,
            'user_type' => $user_type,
            'note' => $note,
            'table_type' => $table_type
        );
        
        return $wpdb->insert($this->history_table, $history_data);
    }
    
    
    private function send_approval_chat_message($request_id, $approval_id, $service, $admin_note, $table_type = 'service_requests') {
        if (class_exists('Nexora_Chat_Manager')) {
            $chat_manager = new Nexora_Chat_Manager();
            global $wpdb;
            $request = $wpdb->get_row($wpdb->prepare(
                "SELECT user_id FROM {$wpdb->prefix}nexora_service_requests WHERE id = %d",
                $request_id
            ));
            
            if ($request) {
                $session = $chat_manager->get_or_create_session($request_id, $request->user_id, get_current_user_id());
                
                if ($session) {
                    $approval_card = $this->create_approval_card_html($request_id, $service, $admin_note, $table_type);
                    error_log('Sending service approval message with type: service_approval');
                    $result = $chat_manager->send_message(
                        $session->id,
                        get_current_user_id(),
                        'admin',
                        $approval_card,
                        'service_approval'
                    );
                    
                    error_log('Send message result: ' . print_r($result, true));
                    
                    if ($result['success']) {
                        error_log("Service approval card sent to chat for request $request_id, approval $approval_id");
                    } else {
                        error_log("Failed to send service approval card to chat: " . $result['error']);
                    }
                } else {
                    error_log("Failed to get or create chat session for request $request_id");
                }
            } else {
                error_log("Request not found: $request_id");
            }
        } else {
            error_log("Chat manager not available");
        }
    }
    
    
    private function create_approval_card_html($request_id, $service, $admin_note, $table_type = 'complete_service_requests') {
        $card_html = '<div class="service-approval-card" data-request-id="' . $request_id . '" data-table-type="' . $table_type . '">';
        $card_html .= '<div class="approval-card-header">';
        $card_html .= '<h4>🎯 Service zur Freigabe</h4>';
        $card_html .= '</div>';
        $card_html .= '<div class="approval-card-content">';
        $card_html .= '<div class="service-details">';
        $card_html .= '<p><strong>Service:</strong> ' . esc_html($service->title) . '</p>';
        $card_html .= '<p><strong>Kosten:</strong> €' . number_format($service->cost, 2) . '</p>';
        $card_html .= '<p><strong>Menge:</strong> 1</p>';
        if (!empty($service->description)) {
            $card_html .= '<p><strong>Beschreibung:</strong> ' . esc_html($service->description) . '</p>';
        }
        $card_html .= '</div>';
        
        if (!empty($admin_note)) {
            $card_html .= '<div class="admin-note">';
            $card_html .= '<p><strong>Admin Notiz:</strong> ' . esc_html($admin_note) . '</p>';
            $card_html .= '</div>';
        }
        
        $card_html .= '<div class="approval-actions">';
        $card_html .= '<button type="button" class="btn btn-success approve-service-btn" onclick="approveService(' . $request_id . ', \'' . $table_type . '\')">✅ Bestätigen</button>';
        $card_html .= '<button type="button" class="btn btn-danger reject-service-btn" onclick="rejectService(' . $request_id . ', \'' . $table_type . '\')">❌ Ablehnen</button>';
        $card_html .= '</div>';
        $card_html .= '</div>';
        $card_html .= '</div>';
        
        return $card_html;
    }
    
    
    private function send_status_chat_message($request_id, $status, $note, $table_type = 'complete_service_requests') {
        global $wpdb;
        
        $approval = $wpdb->get_row($wpdb->prepare(
            "SELECT * FROM {$this->complete_service_requests_table} WHERE request_id = %d",
            $request_id
        ));
        
        if (!$approval) {
            error_log("Approval not found for request: $request_id");
            return;
        }
        if (class_exists('Nexora_Chat_Manager')) {
            $chat_manager = new Nexora_Chat_Manager();
            $request = $wpdb->get_row($wpdb->prepare(
                "SELECT user_id FROM {$wpdb->prefix}nexora_service_requests WHERE id = %d",
                $request_id
            ));
            
            if ($request) {
                $session = $chat_manager->get_or_create_session($request_id, $request->user_id, get_current_user_id());
                
                if ($session) {
                    $status_message = '';
                    if ($status === 'approved') {
                        $status_message = "✅ Service wurde genehmigt.";
                    } elseif ($status === 'rejected') {
                        $status_message = "❌ Service wurde abgelehnt.";
                    }
                    
                    if (!empty($note)) {
                        $status_message .= " Notiz: " . esc_html($note);
                    }
                    $result = $chat_manager->send_message(
                        $session->id,
                        get_current_user_id(),
                        'customer',
                        $status_message,
                        'text'
                    );
                    
                    if ($result['success']) {
                        error_log("Status message sent to chat for request $request_id");
                    } else {
                        error_log("Failed to send status message to chat: " . $result['error']);
                    }
                } else {
                    error_log("Failed to get or create chat session for request $request_id");
                }
            } else {
                error_log("Request not found: $request_id");
            }
        } else {
            error_log("Chat manager not available");
        }
    }
    
    
    public function ajax_send_service_approval() {
        if (!wp_verify_nonce($_POST['nonce'], 'nexora_nonce')) {
            wp_send_json_error('Invalid nonce');
        }
        if (!current_user_can('manage_options')) {
            wp_send_json_error('Access denied');
        }
        
        $request_id = intval($_POST['request_id']);
        $service_id = intval($_POST['service_id']);
        $admin_note = sanitize_textarea_field($_POST['admin_note'] ?? '');
        
        if (!$request_id || !$service_id) {
            wp_send_json_error('Invalid request or service ID');
        }
        
        $result = $this->send_service_approval($request_id, $service_id, get_current_user_id(), $admin_note);
        
        if ($result['success']) {
            wp_send_json_success(array(
                'message' => 'Service sent for approval successfully',
                'approval_id' => $result['approval_id'],
                'table_type' => $result['table']
            ));
        } else {
            wp_send_json_error($result['error']);
        }
    }
    
    
    public function ajax_approve_service() {
        error_log('ajax_approve_service called');
        error_log('POST data: ' . print_r($_POST, true));
        $nonce_valid = false;
        if (isset($_POST['nonce'])) {
            $nonce_valid = wp_verify_nonce($_POST['nonce'], 'nexora_nonce') ||
                          wp_verify_nonce($_POST['nonce'], 'nexora_chat_nonce') ||
                          wp_verify_nonce($_POST['nonce'], 'nexora_user_nonce');
        }
        
        if (!$nonce_valid) {
            error_log('Nonce verification failed');
            wp_send_json_error('Invalid nonce');
        }
        if (!is_user_logged_in()) {
            error_log('User not logged in');
            wp_send_json_error('User not logged in');
        }
        
        $request_id = intval($_POST['request_id'] ?? $_POST['approval_id']);
        $customer_note = sanitize_textarea_field($_POST['customer_note'] ?? '');
        $table_type = sanitize_text_field($_POST['table_type'] ?? 'complete_service_requests');
        
        error_log('Request ID: ' . $request_id);
        error_log('Customer Note: ' . $customer_note);
        error_log('Table Type: ' . $table_type);
        
        if (!$request_id) {
            error_log('Invalid request ID');
            wp_send_json_error('Invalid request ID');
        }
        
        $result = $this->approve_service($request_id, get_current_user_id(), $customer_note, $table_type);
        
        error_log('Approve result: ' . print_r($result, true));
        
        if ($result['success']) {
            wp_send_json_success('Service approved successfully');
        } else {
            wp_send_json_error($result['error']);
        }
    }
    
    
    public function ajax_reject_service() {
        error_log('ajax_reject_service called');
        error_log('POST data: ' . print_r($_POST, true));
        $nonce_valid = false;
        if (isset($_POST['nonce'])) {
            $nonce_valid = wp_verify_nonce($_POST['nonce'], 'nexora_nonce') ||
                          wp_verify_nonce($_POST['nonce'], 'nexora_chat_nonce') ||
                          wp_verify_nonce($_POST['nonce'], 'nexora_user_nonce');
        }
        
        if (!$nonce_valid) {
            error_log('Nonce verification failed');
            wp_send_json_error('Invalid nonce');
        }
        if (!is_user_logged_in()) {
            error_log('User not logged in');
            wp_send_json_error('User not logged in');
        }
        
        $request_id = intval($_POST['request_id'] ?? $_POST['approval_id']);
        $customer_note = sanitize_textarea_field($_POST['customer_note'] ?? '');
        $table_type = sanitize_text_field($_POST['table_type'] ?? 'complete_service_requests');
        
        error_log('Request ID: ' . $request_id);
        error_log('Customer Note: ' . $customer_note);
        error_log('Table Type: ' . $table_type);
        
        if (!$request_id) {
            error_log('Invalid request ID');
            wp_send_json_error('Invalid request ID');
        }
        
        $result = $this->reject_service($request_id, get_current_user_id(), $customer_note, $table_type);
        
        error_log('Reject result: ' . print_r($result, true));
        
        if ($result['success']) {
            wp_send_json_success('Service rejected successfully');
        } else {
            wp_send_json_error($result['error']);
        }
    }
    
    
    public function ajax_get_service_approvals() {
        if (!wp_verify_nonce($_POST['nonce'], 'nexora_nonce')) {
            wp_send_json_error('Invalid nonce');
        }
        
        $request_id = intval($_POST['request_id']);
        
        if (!$request_id) {
            wp_send_json_error('Invalid request ID');
        }
        
        $approvals = $this->get_service_approvals($request_id);
        
        wp_send_json_success($approvals);
    }
    
    
    public function ajax_cancel_service_approval() {
        if (!wp_verify_nonce($_POST['nonce'], 'nexora_nonce')) {
            wp_send_json_error('Invalid nonce');
        }
        if (!current_user_can('manage_options')) {
            wp_send_json_error('Access denied');
        }
        
        $request_id = intval($_POST['request_id'] ?? $_POST['approval_id']);
        $table_type = sanitize_text_field($_POST['table_type'] ?? 'complete_service_requests');
        
        if (!$request_id) {
            wp_send_json_error('Invalid request ID');
        }
        
        $result = $this->cancel_service_approval($request_id, get_current_user_id(), $table_type);
        
        if ($result['success']) {
            wp_send_json_success('Service approval cancelled successfully');
        } else {
            wp_send_json_error($result['error']);
        }
    }
    
    
    public function ajax_get_service_approval_status() {
        if (!wp_verify_nonce($_POST['nonce'], 'nexora_nonce')) {
            wp_send_json_error('Invalid nonce');
        }
        
        $request_id = intval($_POST['request_id']);
        $service_id = intval($_POST['service_id']);
        
        if (!$request_id || !$service_id) {
            wp_send_json_error('Invalid request or service ID');
        }
        
        $status = $this->get_approval_status($request_id, $service_id);
        
        wp_send_json_success(array(
            'status' => $status,
            'request_id' => $request_id,
            'service_id' => $service_id
        ));
    }
    
    
    public function ajax_clear_service_approval() {
        if (!wp_verify_nonce($_POST['nonce'], 'nexora_nonce')) {
            wp_send_json_error('Invalid nonce');
        }
        if (!current_user_can('manage_options')) {
            wp_send_json_error('Access denied');
        }
        
        $request_id = intval($_POST['request_id']);
        $service_id = intval($_POST['service_id']);
        
        if (!$request_id || !$service_id) {
            wp_send_json_error('Invalid request or service ID');
        }
        
        global $wpdb;
        $result = $wpdb->update(
            $this->main_request_table,
            array(
                'approval_status' => 'none',
                'approval_admin_id' => null,
                'approval_admin_note' => null,
                'approval_customer_response' => null,
                'approval_customer_response_time' => null,
                'approval_updated_at' => current_time('mysql')
            ),
            array(
                'id' => $request_id
            )
        );
        
        if ($result !== false) {
            wp_send_json_success(array(
                'message' => 'Service approval cleared successfully',
                'request_updated' => $result
            ));
        } else {
            wp_send_json_error('Failed to clear service approval');
        }
    }
}
