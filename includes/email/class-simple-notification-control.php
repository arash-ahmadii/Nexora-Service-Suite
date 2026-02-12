<?php

if (!defined('ABSPATH')) {
    exit;
}

class Nexora_Simple_Notification_Control {
    
    private $table_name;
    
    public function __construct() {
        global $wpdb;
        $this->table_name = $wpdb->prefix . 'nexora_simple_notifications';
    }
    
    
    public function get_all_changeable_fields() {
        return [
            'service_requests' => [
                'request_created' => 'Serviceanfrage erstellt',
                'request_updated' => 'Serviceanfrage aktualisiert',
                'status_changed' => 'Status geändert',
                'priority_changed' => 'Priorität geändert',
                'assigned_technician' => 'Techniker zugewiesen',
                'estimated_completion_changed' => 'Geschätzte Fertigstellung geändert'
            ],
            'services' => [
                'service_added' => 'Service hinzugefügt',
                'service_removed' => 'Service entfernt',
                'service_updated' => 'Service aktualisiert',
                'service_cost_changed' => 'Servicekosten geändert'
            ],
            'users_system' => [
                'customer_registered' => 'Kunde registriert',
                'customer_updated' => 'Kunde aktualisiert',
                'device_added' => 'Gerät hinzugefügt',
                'device_updated' => 'Gerät aktualisiert'
            ]
        ];
    }
    
    
    public function create_default_settings() {
        global $wpdb;
        
        $fields = $this->get_all_changeable_fields();
        $roles = [
            'administrator' => 'Administrator',
            'editor' => 'Editor',
            'author' => 'Autor',
            'contributor' => 'Mitwirkender',
            'subscriber' => 'Abonnent',
            'customer' => 'Kunde'
        ];
        
        foreach ($roles as $role_key => $role_name) {
            foreach ($fields as $category => $category_fields) {
                foreach ($category_fields as $field_key => $field_name) {
                    $this->update_field_setting($role_key, $field_key, true);
                }
            }
        }
        
        return true;
    }
    
    
    public function update_field_setting($role, $field_name, $enabled) {
        global $wpdb;
        
        $data = [
            'user_role' => sanitize_text_field($role),
            'field_name' => sanitize_text_field($field_name),
            'enabled' => $enabled ? 1 : 0,
            'updated_at' => current_time('mysql')
        ];
        
        $existing = $wpdb->get_row($wpdb->prepare(
            "SELECT id FROM {$this->table_name} WHERE user_role = %s AND field_name = %s",
            $role, $field_name
        ));
        
        if ($existing) {
            return $wpdb->update(
                $this->table_name,
                $data,
                ['id' => $existing->id]
            ) !== false;
        } else {
            $data['created_at'] = current_time('mysql');
            return $wpdb->insert($this->table_name, $data) !== false;
        }
    }
    
    
    public function save_settings($notifications) {
        global $wpdb;
        
        if (!is_array($notifications)) {
            return false;
        }
        
        $success = true;
        
        foreach ($notifications as $category => $events) {
            if (is_array($events)) {
                foreach ($events as $event => $enabled) {
                    $result = $this->update_field_setting('customer', $event, $enabled == '1');
                    if (!$result) {
                        $success = false;
                    }
                }
            }
        }
        
        return $success;
    }
    
    
    public function get_field_setting($role, $field_name) {
        global $wpdb;
        
        $result = $wpdb->get_var($wpdb->prepare(
            "SELECT enabled FROM {$this->table_name} WHERE user_role = %s AND field_name = %s",
            $role, $field_name
        ));
        
        return $result === '1';
    }
    
    
    public function get_role_field_settings($role) {
        global $wpdb;
        
        $results = $wpdb->get_results($wpdb->prepare(
            "SELECT field_name, enabled FROM {$this->table_name} WHERE user_role = %s",
            $role
        ));
        
        $settings = [];
        foreach ($results as $result) {
            $settings[$result->field_name] = (bool) $result->enabled;
        }
        
        return $settings;
    }
    
    
    public function create_tables() {
        global $wpdb;
        
        $charset_collate = $wpdb->get_charset_collate();
        
        $sql = "CREATE TABLE {$this->table_name} (
            id int(11) NOT NULL AUTO_INCREMENT,
            user_role varchar(50) NOT NULL,
            field_name varchar(100) NOT NULL,
            enabled tinyint(1) DEFAULT 1,
            created_at timestamp DEFAULT CURRENT_TIMESTAMP,
            updated_at timestamp DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            PRIMARY KEY (id),
            UNIQUE KEY unique_role_field (user_role, field_name)
        ) $charset_collate;";
        
        require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
        
        $result = dbDelta($sql);
        
        return !empty($result);
    }
}
