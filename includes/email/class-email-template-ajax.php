<?php
if (!defined('ABSPATH')) { exit; }

class Nexora_Email_Template_AJAX {
    
    public function __construct() {
        add_action('wp_ajax_load_email_template', array($this, 'load_email_template'));
        add_action('wp_ajax_save_email_template', array($this, 'save_email_template'));
        add_action('wp_ajax_save_email_message', array($this, 'save_email_message'));
        add_action('wp_ajax_reset_email_templates', array($this, 'reset_email_templates'));
        add_action('wp_ajax_reset_email_template', array($this, 'reset_email_template'));
        add_action('wp_ajax_preview_email_template', array($this, 'preview_email_template'));
        add_action('wp_ajax_test_email_template', array($this, 'test_email_template'));
        add_action('wp_ajax_save_company_info', array($this, 'save_company_info'));
        add_action('wp_ajax_test_ajax_connection', array($this, 'test_ajax_connection'));
    }
    
    
    public function test_ajax_connection() {
        check_ajax_referer('nexora_email_template_nonce', 'nonce');
        
        if (!current_user_can('manage_options')) {
            wp_send_json_error('Unauthorized access');
            return;
        }
        
        wp_send_json_success('AJAX connection working!');
    }
    
    
    public function load_email_template() {
        check_ajax_referer('nexora_email_template_nonce', 'nonce');
        
        if (!current_user_can('manage_options')) {
            wp_send_json_error('Unauthorized access');
            return;
        }
        
        $template_id = sanitize_text_field($_POST['template'] ?? '');
        
        if (empty($template_id)) {
            wp_send_json_error('Template ID is required');
            return;
        }
        
        $template_manager = new Nexora_Email_Template_Manager();
        if ($template_id === 'master_template') {
            $template = $template_manager->get_template($template_id);
            
            if ($template) {
                wp_send_json_success(array(
                    'html' => $template['html'] ?? '',
                    'css' => $template['css'] ?? '',
                    'title' => '',
                    'subject' => '',
                    'message' => ''
                ));
            } else {
                wp_send_json_error('Master template not found');
            }
        } else {
            $message = $template_manager->get_message($template_id);
            
            if ($message) {
                wp_send_json_success(array(
                    'html' => '',
                    'css' => '',
                    'title' => $message['title'] ?? '',
                    'subject' => $message['subject'] ?? '',
                    'message' => $message['message'] ?? ''
                ));
            } else {
                wp_send_json_error('Message template not found');
            }
        }
    }
    
    
    public function save_email_template() {
        check_ajax_referer('nexora_email_template_nonce', 'nonce');
        
        if (!current_user_can('manage_options')) {
            wp_send_json_error('Unauthorized access');
            return;
        }
        
        $template_id = sanitize_text_field($_POST['template'] ?? '');
        
        if (empty($template_id)) {
            wp_send_json_error('Template ID is required');
            return;
        }
        
        $template_manager = new Nexora_Email_Template_Manager();
        if ($template_id === 'master_template') {
            $template_data = array(
                'html' => wp_kses_post($_POST['html'] ?? ''),
                'css' => sanitize_textarea_field($_POST['css'] ?? '')
            );
            
            if (empty($template_data['html'])) {
                wp_send_json_error('HTML content is required for master template');
                return;
            }
            
            $result = $template_manager->save_template($template_id, $template_data);
        } else {
            $message_data = array(
                'title' => sanitize_text_field($_POST['title'] ?? ''),
                'subject' => sanitize_text_field($_POST['subject'] ?? ''),
                'message' => wp_kses_post($_POST['message'] ?? '')
            );
            
            if (empty($message_data['title']) || empty($message_data['subject']) || empty($message_data['message'])) {
                wp_send_json_error('Title, subject, and message are required for message templates');
                return;
            }
            
            $result = $template_manager->save_message($template_id, $message_data);
        }
        
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
            'text' => wp_kses_post($_POST['text'] ?? '')
        );
        
        if (empty($message_id) || empty($message_data['title']) || empty($message_data['subject'])) {
            wp_send_json_error('Message ID, title, and subject are required');
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
    
    
    public function reset_email_template() {
        check_ajax_referer('nexora_email_template_nonce', 'nonce');
        
        if (!current_user_can('manage_options')) {
            wp_send_json_error('Unauthorized access');
            return;
        }
        
        $template_id = sanitize_text_field($_POST['template'] ?? '');
        
        if (empty($template_id)) {
            wp_send_json_error('Template ID is required');
            return;
        }
        
        $template_manager = new Nexora_Email_Template_Manager();
        $result = $template_manager->reset_template_to_default($template_id);
        
        if ($result) {
            wp_send_json_success('Template reset to default successfully');
        } else {
            wp_send_json_error('Failed to reset template');
        }
    }
    
    
    public function preview_email_template() {
        check_ajax_referer('nexora_email_template_nonce', 'nonce');
        
        if (!current_user_can('manage_options')) {
            wp_send_json_error('Unauthorized access');
            return;
        }
        
        $template_id = sanitize_text_field($_POST['template'] ?? '');
        
        if (empty($template_id)) {
            wp_send_json_error('Template ID is required');
            return;
        }
        
        $template_manager = new Nexora_Email_Template_Manager();
        
        if ($template_id === 'master_template') {
            $html = sanitize_text_field($_POST['html'] ?? '');
            $css = sanitize_textarea_field($_POST['css'] ?? '');
            
            if (empty($html)) {
                wp_send_json_error('HTML content is required for preview');
                return;
            }
            if (!empty($css)) {
                $html = str_replace('</head>', '<style>' . $css . '</style></head>', $html);
            }
            
            wp_send_json_success(array('html' => $html));
        } else {
            $sample_variables = $this->get_sample_variables($template_id, array());
            $html = $template_manager->generate_email_html($template_id, $sample_variables);
            
            if ($html) {
                wp_send_json_success(array('html' => $html));
            } else {
                wp_send_json_error('Failed to generate preview');
            }
        }
    }
    
    
    public function test_email_template() {
        check_ajax_referer('nexora_email_template_nonce', 'nonce');
        
        if (!current_user_can('manage_options')) {
            wp_send_json_error('Unauthorized access');
            return;
        }
        
        $template_id = sanitize_text_field($_POST['template'] ?? '');
        
        if (empty($template_id)) {
            wp_send_json_error('Template ID is required');
            return;
        }
        
        $template_manager = new Nexora_Email_Template_Manager();
        
        if ($template_id === 'master_template') {
            $html = sanitize_text_field($_POST['html'] ?? '');
            $css = sanitize_textarea_field($_POST['css'] ?? '');
            
            if (empty($html)) {
                wp_send_json_error('HTML content is required for test');
                return;
            }
            if (!empty($css)) {
                $html = str_replace('</head>', '<style>' . $css . '</style></head>', $html);
            }
            $current_user = wp_get_current_user();
            $test_email = $current_user->user_email;
            $headers = array('Content-Type: text/html; charset=UTF-8');
            $sent = wp_mail($test_email, 'Test E-Mail-Vorlage', $html, $headers);
            
            if ($sent) {
                wp_send_json_success('Test-E-Mail erfolgreich gesendet an ' . $test_email);
            } else {
                wp_send_json_error('Fehler beim Senden der Test-E-Mail');
            }
        } else {
            $sample_variables = $this->get_sample_variables($template_id, array());
            $html = $template_manager->generate_email_html($template_id, $sample_variables);
            
            if (!$html) {
                wp_send_json_error('Fehler beim Generieren der E-Mail-HTML');
                return;
            }
            $current_user = wp_get_current_user();
            $test_email = $current_user->user_email;
            $messages = $template_manager->get_messages();
            $subject = $messages[$template_id]['subject'] ?? 'Test E-Mail';
            $headers = array('Content-Type: text/html; charset=UTF-8');
            $sent = wp_mail($test_email, $subject, $html, $headers);
            
            if ($sent) {
                wp_send_json_success('Test-E-Mail erfolgreich gesendet an ' . $test_email);
            } else {
                wp_send_json_error('Fehler beim Senden der Test-E-Mail');
            }
        }
    }
    
    
    private function get_sample_variables($message_id, $custom_variables = array()) {
        $default_variables = array(
            'user_name' => 'John Doe',
            'user_email' => 'john.doe@example.com',
            'service_title' => 'iPhone Screen Repair',
            'service_description' => 'Professional screen replacement service',
            'request_id' => 'REQ-2024-001',
            'status' => 'In Progress',
            'estimated_cost' => '€89.99',
            'technician_name' => 'Mike Smith',
            'company_name' => 'Nexora Service Suite',
            'company_slogan' => 'Professional Repair Services',
            'company_phone' => '+43 1 234 5678',
            'company_website' => 'https://example.com',
            'company_address' => 'Vienna, Austria',
            'invoice_number' => 'INV-2024-001',
            'invoice_amount' => '€89.99',
            'due_date' => '2024-12-31',
            'current_date' => current_time('Y-m-d'),
            'current_time' => current_time('H:i:s')
        );
        return array_merge($default_variables, $custom_variables);
    }
    
    
    private function sanitize_variables($variables) {
        $sanitized = array();
        
        if (is_array($variables)) {
            foreach ($variables as $key => $value) {
                $sanitized[sanitize_text_field($key)] = sanitize_text_field($value);
            }
        }
        
        return $sanitized;
    }
    
    
    public function save_company_info() {
        check_ajax_referer('nexora_email_template_nonce', 'nonce');
        
        if (!current_user_can('manage_options')) {
            wp_send_json_error('Unauthorized access');
            return;
        }
        
        $company_data = array(
            'company_slogan' => sanitize_text_field($_POST['company_slogan'] ?? ''),
            'company_phone' => sanitize_text_field($_POST['company_phone'] ?? ''),
            'company_website' => esc_url_raw($_POST['company_website'] ?? ''),
            'company_address' => sanitize_textarea_field($_POST['company_address'] ?? ''),
            'social_instagram' => esc_url_raw($_POST['social_instagram'] ?? ''),
            'social_telegram' => esc_url_raw($_POST['social_telegram'] ?? ''),
            'social_whatsapp' => esc_url_raw($_POST['social_whatsapp'] ?? '')
        );
        
        $success = true;
        foreach ($company_data as $key => $value) {
            $option_name = 'nexora_' . $key;
            $result = update_option($option_name, $value);
            if (!$result) {
                $success = false;
            }
        }
        
        if ($success) {
            wp_send_json_success('Company information saved successfully');
        } else {
            wp_send_json_error('Some company information could not be saved');
        }
    }
}
