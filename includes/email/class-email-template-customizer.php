<?php

if (!defined('ABSPATH')) {
    exit;
}

class Nexora_Email_Template_Customizer {
    
    
    public function __construct() {
        add_action('wp_ajax_nexora_get_template_settings', array($this, 'ajax_get_template_settings'));
        add_action('wp_ajax_nexora_save_template_settings', array($this, 'ajax_save_template_settings'));
        add_action('wp_ajax_nexora_reset_template_settings', array($this, 'ajax_reset_template_settings'));
    }
    
    
    public function get_template_settings() {
        global $wpdb;
        $table_name = $wpdb->prefix . 'nexora_email_templates';
        $table_exists = $wpdb->get_var("SHOW TABLES LIKE '$table_name'") == $table_name;
        
        if (!$table_exists) {
            return $this->get_default_template_settings();
        }
        
        $settings = $wpdb->get_row("SELECT * FROM $table_name WHERE template_type = 'master' LIMIT 1");
        
        if (!$settings) {
            return $this->get_default_template_settings();
        }
        
        return array(
            'header_logo' => $settings->header_logo ?? '',
            'header_background_color' => $settings->header_background_color ?? '#273269',
            'header_text_color' => $settings->header_text_color ?? '#ffffff',
            'header_subtitle' => $settings->header_subtitle ?? 'Qualitätsdienstleistungen zu fairen Preisen',
            'footer_background_color' => $settings->footer_background_color ?? '#273269',
            'footer_text_color' => $settings->footer_text_color ?? '#ffffff',
            'company_phone' => $settings->company_phone ?? '+43 1 234 5678',
            'company_email' => $settings->company_email ?? 'info@example.com',
            'company_website' => $settings->company_website ?? 'https://example.com',
            'company_address' => $settings->company_address ?? 'Wien, Österreich',
            'social_instagram' => $settings->social_instagram ?? '#',
            'social_telegram' => $settings->social_telegram ?? '#',
            'social_whatsapp' => $settings->social_whatsapp ?? '#',
            'status_change_text' => $settings->status_change_text ?? 'Der Status Ihrer Serviceanfrage hat sich geändert.',
            'customer_welcome_text' => $settings->customer_welcome_text ?? 'Willkommen bei Nexora Service Suite! Ihr Konto wurde erfolgreich erstellt.',
            'dashboard_link_text' => $settings->dashboard_link_text ?? '🚀 Ihr Dashboard aufrufen'
        );
    }
    
    
    private function get_default_template_settings() {
        return array(
            'header_logo' => 'eccoripair.webp',
            'header_background_color' => '#273269',
            'header_text_color' => '#ffffff',
            'header_subtitle' => 'Qualitätsdienstleistungen zu fairen Preisen',
            'footer_background_color' => '#273269',
            'footer_text_color' => '#ffffff',
            'company_phone' => '+43 1 234 5678',
            'company_email' => 'info@example.com',
            'company_website' => 'https://example.com',
            'company_address' => 'Wien, Österreich',
            'social_instagram' => '#',
            'social_telegram' => '#',
            'social_whatsapp' => '#',
            'status_change_text' => 'Der Status Ihrer Serviceanfrage hat sich geändert.',
            'customer_welcome_text' => 'Willkommen bei Nexora Service Suite! Ihr Konto wurde erfolgreich erstellt.',
            'dashboard_link_text' => '🚀 Ihr Dashboard aufrufen'
        );
    }
    
    
    public function save_template_settings($settings) {
        global $wpdb;
        $table_name = $wpdb->prefix . 'nexora_email_templates';
        $table_exists = $wpdb->get_var("SHOW TABLES LIKE '$table_name'") == $table_name;
        
        if (!$table_exists) {
            $this->create_template_settings_table();
        }
        $existing = $wpdb->get_row("SELECT id FROM $table_name WHERE template_type = 'master' LIMIT 1");
        
        if ($existing) {
            $result = $wpdb->update(
                $table_name,
                array(
                    'header_logo' => $settings['header_logo'],
                    'header_background_color' => $settings['header_background_color'],
                    'header_text_color' => $settings['header_text_color'],
                    'header_subtitle' => $settings['header_subtitle'],
                    'footer_background_color' => $settings['footer_background_color'],
                    'footer_text_color' => $settings['footer_text_color'],
                    'company_phone' => $settings['company_phone'],
                    'company_email' => $settings['company_email'],
                    'company_website' => $settings['company_website'],
                    'company_address' => $settings['company_address'],
                    'social_instagram' => $settings['social_instagram'],
                    'social_telegram' => $settings['social_telegram'],
                    'social_whatsapp' => $settings['social_whatsapp'],
                    'status_change_text' => $settings['status_change_text'],
                    'customer_welcome_text' => $settings['customer_welcome_text'],
                    'dashboard_link_text' => $settings['dashboard_link_text'],
                    'updated_at' => current_time('mysql')
                ),
                array('template_type' => 'master'),
                array('%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s'),
                array('%s')
            );
        } else {
            $result = $wpdb->insert(
                $table_name,
                array(
                    'template_type' => 'master',
                    'header_logo' => $settings['header_logo'],
                    'header_background_color' => $settings['header_background_color'],
                    'header_text_color' => $settings['header_text_color'],
                    'header_subtitle' => $settings['header_subtitle'],
                    'footer_background_color' => $settings['footer_background_color'],
                    'footer_text_color' => $settings['footer_text_color'],
                    'company_phone' => $settings['company_phone'],
                    'company_email' => $settings['company_email'],
                    'company_website' => $settings['company_website'],
                    'company_address' => $settings['company_address'],
                    'social_instagram' => $settings['social_instagram'],
                    'social_telegram' => $settings['social_telegram'],
                    'social_whatsapp' => $settings['social_whatsapp'],
                    'status_change_text' => $settings['status_change_text'],
                    'customer_welcome_text' => $settings['customer_welcome_text'],
                    'dashboard_link_text' => $settings['dashboard_link_text'],
                    'created_at' => current_time('mysql'),
                    'updated_at' => current_time('mysql')
                ),
                array('%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s')
            );
        }
        
        return $result !== false;
    }
    
    
    private function create_template_settings_table() {
        global $wpdb;
        $table_name = $wpdb->prefix . 'nexora_email_templates';
        
        $charset_collate = $wpdb->get_charset_collate();
        
        $sql = "CREATE TABLE $table_name (
            id mediumint(9) NOT NULL AUTO_INCREMENT,
            template_type varchar(50) NOT NULL,
            header_logo varchar(255) DEFAULT '',
            header_background_color varchar(7) DEFAULT '#273269',
            header_text_color varchar(7) DEFAULT '#ffffff',
            header_subtitle text DEFAULT '',
            footer_background_color varchar(7) DEFAULT '#273269',
            footer_text_color varchar(7) DEFAULT '#ffffff',
            company_phone varchar(50) DEFAULT '',
            company_email varchar(100) DEFAULT '',
            company_website varchar(255) DEFAULT '',
            company_address text DEFAULT '',
            social_instagram varchar(255) DEFAULT '',
            social_telegram varchar(255) DEFAULT '',
            social_whatsapp varchar(255) DEFAULT '',
            status_change_text text DEFAULT '',
            customer_welcome_text text DEFAULT '',
            dashboard_link_text text DEFAULT '',
            created_at datetime DEFAULT CURRENT_TIMESTAMP,
            updated_at datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            PRIMARY KEY (id),
            UNIQUE KEY template_type (template_type)
        ) $charset_collate;";
        
        require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
        dbDelta($sql);
    }
    
    
    public function ajax_get_template_settings() {
        if (!wp_verify_nonce($_POST['nonce'], 'nexora_email_nonce')) {
            wp_die('Security check failed');
        }
        if (!current_user_can('manage_options')) {
            wp_die('Insufficient permissions');
        }
        
        $settings = $this->get_template_settings();
        
        wp_send_json_success($settings);
    }
    
    
    public function ajax_save_template_settings() {
        if (!wp_verify_nonce($_POST['nonce'], 'nexora_email_nonce')) {
            wp_die('Security check failed');
        }
        if (!current_user_can('manage_options')) {
            wp_die('Insufficient permissions');
        }
        
        $settings = array(
            'header_logo' => sanitize_text_field($_POST['header_logo']),
            'header_background_color' => sanitize_hex_color($_POST['header_background_color']),
            'header_text_color' => sanitize_hex_color($_POST['header_text_color']),
            'header_subtitle' => sanitize_text_field($_POST['header_subtitle']),
            'footer_background_color' => sanitize_hex_color($_POST['footer_background_color']),
            'footer_text_color' => sanitize_hex_color($_POST['footer_text_color']),
            'company_phone' => sanitize_text_field($_POST['company_phone']),
            'company_email' => sanitize_email($_POST['company_email']),
            'company_website' => esc_url_raw($_POST['company_website']),
            'company_address' => sanitize_text_field($_POST['company_address']),
            'social_instagram' => esc_url_raw($_POST['social_instagram']),
            'social_telegram' => esc_url_raw($_POST['social_telegram']),
            'social_whatsapp' => esc_url_raw($_POST['social_whatsapp']),
            'status_change_text' => sanitize_textarea_field($_POST['status_change_text']),
            'customer_welcome_text' => sanitize_textarea_field($_POST['customer_welcome_text']),
            'dashboard_link_text' => sanitize_text_field($_POST['dashboard_link_text'])
        );
        
        $result = $this->save_template_settings($settings);
        
        if ($result) {
            wp_send_json_success('Template settings saved successfully');
        } else {
            wp_send_json_error('Failed to save template settings');
        }
    }
    
    
    public function ajax_reset_template_settings() {
        if (!wp_verify_nonce($_POST['nonce'], 'nexora_email_nonce')) {
            wp_die('Security check failed');
        }
        if (!current_user_can('manage_options')) {
            wp_die('Insufficient permissions');
        }
        
        global $wpdb;
        $table_name = $wpdb->prefix . 'nexora_email_templates';
        $wpdb->delete($table_name, array('template_type' => 'master'), array('%s'));
        
        wp_send_json_success('Template settings reset to defaults');
    }
}
new Nexora_Email_Template_Customizer();
