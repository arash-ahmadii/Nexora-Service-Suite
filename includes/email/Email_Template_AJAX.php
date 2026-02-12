<?php

if (!defined('ABSPATH')) {
    exit;
}

class Nexora_Email_Template_AJAX {
    
    public function __construct() {
        add_action('wp_ajax_save_email_template', array($this, 'save_email_template'));
        add_action('wp_ajax_save_email_message', array($this, 'save_email_message'));
        add_action('wp_ajax_reset_email_templates', array($this, 'reset_email_templates'));
        add_action('wp_ajax_preview_email_template', array($this, 'preview_email_template'));
        add_action('wp_ajax_test_email_template', array($this, 'test_email_template'));
    }
    
    
    public function save_email_template() {
        check_ajax_referer('nexora_email_template_nonce', 'nonce');
        
        if (!current_user_can('manage_options')) {
            wp_send_json_error('Unauthorized access');
            return;
        }
        
        $template_id = sanitize_text_field($_POST['template_id'] ?? '');
        $template_data = array(
            'name' => sanitize_text_field($_POST['name'] ?? ''),
            'description' => sanitize_textarea_field($_POST['description'] ?? ''),
            'html' => wp_kses_post($_POST['html'] ?? ''),
            'css' => sanitize_textarea_field($_POST['css'] ?? '')
        );
        
        if (empty($template_id) || empty($template_data['name'])) {
            wp_send_json_error('Template ID and name are required');
            return;
        }
        
        $template_manager = new Nexora_Email_Template_Manager();
        $result = $template_manager->save_template($template_id, $template_data);
        
        if ($result) {
            wp_send_json_success('Template saved successfully');
        } else {
            wp_send_json_error('Failed to save template');
        }
    }
    
    
    public function save_email_message() {
        check_ajax_referer('nexora_email_template_nonce', 'nonce');
        
        if (!current_user_can('manage_options')) {
            wp_send_json_error('Unauthorized access');
            return;
        }
        
        $message_id = sanitize_text_field($_POST['message_id'] ?? '');
        $message_data = array(
            'title' => sanitize_text_field($_POST['title'] ?? ''),
            'subject' => sanitize_text_field($_POST['subject'] ?? ''),
            'message' => sanitize_textarea_field($_POST['message'] ?? '')
        );
        
        if (empty($message_id) || empty($message_data['title'])) {
            wp_send_json_error('Message ID and title are required');
            return;
        }
        
        $template_manager = new Nexora_Email_Template_Manager();
        $result = $template_manager->save_message($message_id, $message_data);
        
        if ($result) {
            wp_send_json_success('Message saved successfully');
        } else {
            wp_send_json_error('Failed to save message');
        }
    }
    
    
    public function reset_email_templates() {
        check_ajax_referer('nexora_email_template_nonce', 'nonce');
        
        if (!current_user_can('manage_options')) {
            wp_send_json_error('Unauthorized access');
            return;
        }
        
        $template_manager = new Nexora_Email_Template_Manager();
        $result = $template_manager->reset_to_defaults();
        
        if ($result) {
            wp_send_json_success('Templates reset to defaults successfully');
        } else {
            wp_send_json_error('Failed to reset templates');
        }
    }
    
    
    public function preview_email_template() {
        check_ajax_referer('nexora_email_template_nonce', 'nonce');
        
        if (!current_user_can('manage_options')) {
            wp_send_json_error('Unauthorized access');
            return;
        }
        
        $message_id = sanitize_text_field($_POST['message_id'] ?? '');
        $variables = array();
        if (isset($_POST['variables']) && is_array($_POST['variables'])) {
            foreach ($_POST['variables'] as $key => $value) {
                $variables[sanitize_text_field($key)] = sanitize_text_field($value);
            }
        }
        $variables = $this->get_sample_variables($message_id, $variables);
        
        $template_manager = new Nexora_Email_Template_Manager();
        $html = $template_manager->generate_email_html($message_id, $variables);
        
        if ($html) {
            wp_send_json_success(array('html' => $html));
        } else {
            wp_send_json_error('Failed to generate preview');
        }
    }
    
    
    public function test_email_template() {
        check_ajax_referer('nexora_email_template_nonce', 'nonce');
        
        if (!current_user_can('manage_options')) {
            wp_send_json_error('Unauthorized access');
            return;
        }
        
        $message_id = sanitize_text_field($_POST['message_id'] ?? '');
        $test_email = sanitize_email($_POST['test_email'] ?? '');
        
        if (empty($message_id) || empty($test_email)) {
            wp_send_json_error('Message ID and test email are required');
            return;
        }
        $variables = $this->get_sample_variables($message_id);
        $template_manager = new Nexora_Email_Template_Manager();
        $html = $template_manager->generate_email_html($message_id, $variables);
        
        if (!$html) {
            wp_send_json_error('Failed to generate email HTML');
            return;
        }
        $message = $template_manager->get_message($message_id);
        $subject = $template_manager->replace_variables($message['subject'], $variables);
        $headers = array('Content-Type: text/html; charset=UTF-8');
        $result = wp_mail($test_email, $subject, $html, $headers);
        
        if ($result) {
            wp_send_json_success('Test email sent successfully to ' . $test_email);
        } else {
            wp_send_json_error('Failed to send test email');
        }
    }
    
    
    private function get_sample_variables($message_id, $custom_variables = array()) {
        $sample_variables = array(
            'customer_name' => 'Max Mustermann',
            'request_id' => 'SR-2024-001',
            'service_type' => 'Handy-Reparatur',
            'request_date' => date('Y/m/d'),
            'current_status' => 'In Bearbeitung',
            'old_status' => 'Registriert',
            'new_status' => 'In Reparatur',
            'change_date' => date('Y/m/d'),
            'status_description' => 'Geräte werden von einem Spezialisten überprüft',
            'username' => 'max_mustermann',
            'email' => 'max@example.com',
            'registration_date' => date('Y/m/d'),
            'admin_name' => 'System-Administrator',
            'event_type' => 'Neue Serviceanfrage',
            'event_description' => 'Eine neue Serviceanfrage wurde registriert',
            'event_date' => date('Y/m/d'),
            'user_name' => 'Max Mustermann',
            'user_email' => 'max@example.com',
            'invoice_number' => 'INV-2024-001',
            'total_amount' => '250,00 €',
            'invoice_date' => date('Y/m/d'),
            'due_date' => date('Y/m/d', strtotime('+30 days'))
        );
        return array_merge($sample_variables, $custom_variables);
    }
    
    
    private function replace_variables($text, $variables) {
        foreach ($variables as $key => $value) {
            $text = str_replace('{' . $key . '}', $value, $text);
        }
        return $text;
    }
}
