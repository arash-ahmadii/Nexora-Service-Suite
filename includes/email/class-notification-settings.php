<?php

if (!defined('ABSPATH')) {
    exit;
}

class Nexora_Notification_Settings {
    
    private $table_name;
    private $simple_table_name;
    
    public function __construct() {
        global $wpdb;
        $this->table_name = $wpdb->prefix . 'nexora_notification_settings';
        $this->simple_table_name = $wpdb->prefix . 'nexora_simple_notifications';
    }
    
    
    public function get_available_events() {
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
    
    
    public function get_user_roles() {
        return [
            'administrator' => 'Administrator',
            'editor' => 'Editor',
            'author' => 'Autor',
            'contributor' => 'Mitwirkender',
            'subscriber' => 'Abonnent',
            'customer' => 'Kunde'
        ];
    }
    
    
    public function get_channels() {
        return [
            'email' => 'E-Mail',
            'admin_notification' => 'Admin-Benachrichtigung'
        ];
    }
    
    
    public function create_default_settings() {
        global $wpdb;
        
        $events = $this->get_available_events();
        $roles = $this->get_user_roles();
        $channels = $this->get_channels();
        
        foreach ($roles as $role_key => $role_name) {
            foreach ($events as $category => $category_events) {
                foreach ($category_events as $event_key => $event_name) {
                    foreach ($channels as $channel_key => $channel_name) {
                        $this->update_setting($role_key, $event_key, $channel_key, true);
                    }
                }
            }
        }
        
        return true;
    }
    
    
    public function update_setting($role, $event, $channel, $enabled) {
        global $wpdb;
        
        $data = [
            'user_role' => sanitize_text_field($role),
            'event_type' => sanitize_text_field($event),
            'channel' => sanitize_text_field($channel),
            'enabled' => $enabled ? 1 : 0,
            'updated_at' => current_time('mysql')
        ];
        
        $existing = $wpdb->get_row($wpdb->prepare(
            "SELECT id FROM {$this->table_name} WHERE user_role = %s AND event_type = %s AND channel = %s",
            $role, $event, $channel
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
    
    
    public function get_setting($role, $event, $channel) {
        global $wpdb;
        
        $result = $wpdb->get_var($wpdb->prepare(
            "SELECT enabled FROM {$this->table_name} WHERE user_role = %s AND event_type = %s AND channel = %s",
            $role, $event, $channel
        ));
        
        return $result === '1';
    }
    
    
    public function create_tables() {
        global $wpdb;
        
        $charset_collate = $wpdb->get_charset_collate();
        $sql = "CREATE TABLE {$this->table_name} (
            id int(11) NOT NULL AUTO_INCREMENT,
            user_role varchar(50) NOT NULL,
            event_type varchar(100) NOT NULL,
            channel varchar(50) NOT NULL,
            enabled tinyint(1) DEFAULT 1,
            created_at timestamp DEFAULT CURRENT_TIMESTAMP,
            updated_at timestamp DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            PRIMARY KEY (id),
            UNIQUE KEY unique_role_event_channel (user_role, event_type, channel)
        ) $charset_collate;";
        $sql2 = "CREATE TABLE {$this->simple_table_name} (
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
        
        $result1 = dbDelta($sql);
        $result2 = dbDelta($sql2);
        
        return !empty($result1) && !empty($result2);
    }
    
    
    public function get_role_settings($role) {
        global $wpdb;
        
        $results = $wpdb->get_results($wpdb->prepare(
            "SELECT event_type, channel, enabled FROM {$this->table_name} WHERE user_role = %s",
            $role
        ));
        
        $settings = [];
        foreach ($results as $result) {
            $settings[$result->event_type][$result->channel] = (bool) $result->enabled;
        }
        
        return $settings;
    }

    
    public function get_settings($role) {
        return $this->get_role_settings($role);
    }

    
    public function update_settings($role, $settings) {
        global $wpdb;
        $wpdb->query('START TRANSACTION');
        
        try {
            foreach ($settings as $event_key => $event_settings) {
                if (isset($event_settings['enabled']) && $event_settings['enabled']) {
                    foreach ($event_settings['channels'] as $channel) {
                        $this->update_setting($role, $event_key, $channel, true);
                    }
                } else {
                    foreach ($this->get_channels() as $channel_key => $channel_name) {
                        $this->update_setting($role, $event_key, $channel_key, false);
                    }
                }
            }
            $wpdb->query('COMMIT');
            return true;
            
        } catch (Exception $e) {
            $wpdb->query('ROLLBACK');
            error_log('Error updating notification settings: ' . $e->getMessage());
            return false;
        }
    }
}
