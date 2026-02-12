<?php

class Nexora_Activity_Logger {
    
    private $table_name;
    
    public function __construct() {
        global $wpdb;
        $this->table_name = $wpdb->prefix . 'nexora_activity_logs';
        
        $this->init_hooks();
    }
    
    private function init_hooks() {
        add_action('wp_ajax_nexora_get_request_logs', array($this, 'ajax_get_request_logs'));
        add_action('wp_ajax_nexora_export_request_logs', array($this, 'ajax_export_request_logs'));
        add_action('wp_ajax_nexora_create_test_log', array($this, 'ajax_create_test_log'));
    }
    
    
    public function log_activity($request_id, $action_type, $action_description, $old_value = null, $new_value = null) {
        global $wpdb;
        
        $user_id = get_current_user_id();
        $ip_address = $this->get_client_ip();
        $user_agent = $_SERVER['HTTP_USER_AGENT'] ?? '';
        
        $data = array(
            'request_id' => intval($request_id),
            'user_id' => intval($user_id),
            'action_type' => sanitize_text_field($action_type),
            'action_description' => sanitize_textarea_field($action_description),
            'old_value' => $old_value ? sanitize_textarea_field($old_value) : null,
            'new_value' => $new_value ? sanitize_textarea_field($new_value) : null,
            'ip_address' => $ip_address,
            'user_agent' => $user_agent,
            'created_at' => current_time('mysql')
        );
        
        $result = $wpdb->insert($this->table_name, $data);
        
        if ($result) {
            return $wpdb->insert_id;
        }
        
        return false;
    }
    
    
    public function log_status_change($request_id, $old_status_id, $new_status_id) {
        global $wpdb;
        
        $old_status = $wpdb->get_var($wpdb->prepare(
            "SELECT title FROM {$wpdb->prefix}nexora_service_status WHERE id = %d",
            $old_status_id
        ));
        
        $new_status = $wpdb->get_var($wpdb->prepare(
            "SELECT title FROM {$wpdb->prefix}nexora_service_status WHERE id = %d",
            $new_status_id
        ));
        
        $description = sprintf(
            'Status geändert von "%s" zu "%s"',
            $old_status ?: 'Unbekannt',
            $new_status ?: 'Unbekannt'
        );
        
        return $this->log_activity(
            $request_id,
            'status_change',
            $description,
            $old_status,
            $new_status
        );
    }
    
    
    public function log_comment_added($request_id, $comment_text) {
        return $this->log_activity(
            $request_id,
            'comment_added',
            'Kommentar hinzugefügt',
            null,
            $comment_text
        );
    }
    
    
    public function log_request_created($request_id, $request_data) {
        $description = sprintf(
            'Neue Service-Anfrage erstellt: %s - %s',
            $request_data['serial'],
            $request_data['model']
        );
        
        return $this->log_activity(
            $request_id,
            'request_created',
            $description,
            null,
            json_encode($request_data)
        );
    }
    
    
    public function log_request_updated($request_id, $old_data, $new_data) {
        $changes = array();
        
        foreach ($new_data as $field => $new_value) {
            if (isset($old_data[$field]) && $old_data[$field] !== $new_value) {
                $changes[$field] = array(
                    'old' => $old_data[$field],
                    'new' => $new_value
                );
            }
        }
        
        if (!empty($changes)) {
            foreach ($changes as $field => $change) {
                $field_description = $this->get_field_description($field, $change['old'], $change['new']);
                
                $this->log_activity(
                    $request_id,
                    'field_updated',
                    $field_description,
                    $change['old'],
                    $change['new']
                );
            }
            
            return true;
        }
        
        return false;
    }
    
    
    private function get_field_description($field, $old_value, $new_value) {
        $field_names = array(
            'serial' => 'Seriennummer',
            'model' => 'Modell',
            'description' => 'Beschreibung',
            'service_description' => 'Service-Beschreibung',
            'priority' => 'Priorität',
            'assigned_to' => 'Zugewiesen an',
            'estimated_completion' => 'Geschätzter Abschluss',
            'brand_level_1_id' => 'Gerätetyp',
            'brand_level_2_id' => 'Marke',
            'brand_level_3_id' => 'Serie',
            'user_id' => 'Zugewiesener Benutzer',
            'order_id' => 'Bestellnummer',
            'service_id' => 'Service'
        );
        
        $field_name = $field_names[$field] ?? $field;
        
        if (empty($old_value) && !empty($new_value)) {
            return "{$field_name} hinzugefügt";
        } elseif (!empty($old_value) && empty($new_value)) {
            return "{$field_name} entfernt";
        } else {
            return "{$field_name} geändert";
        }
    }
    
    
    public function log_services_updated($request_id, $old_services, $new_services) {
        $old_services_indexed = array();
        $new_services_indexed = array();
        foreach ($old_services as $service) {
            if (isset($service['service_id'])) {
                $old_services_indexed[$service['service_id']] = $service;
            }
        }
        foreach ($new_services as $service) {
            if (isset($service['service_id'])) {
                $new_services_indexed[$service['service_id']] = $service;
            }
        }
        foreach ($new_services_indexed as $service_id => $service) {
            if (!isset($old_services_indexed[$service_id])) {
                $service_title = $this->get_service_title($service_id);
                $description = "Service hinzugefügt: \"{$service_title}\"";
                
                $this->log_activity(
                    $request_id,
                    'service_added',
                    $description,
                    null,
                    json_encode($service)
                );
            }
        }
        foreach ($old_services_indexed as $service_id => $service) {
            if (!isset($new_services_indexed[$service_id])) {
                $service_title = $this->get_service_title($service_id);
                $description = "Service entfernt: \"{$service_title}\"";
                
                $this->log_activity(
                    $request_id,
                    'service_removed',
                    $description,
                    json_encode($service),
                    null
                );
            }
        }
        foreach ($new_services_indexed as $service_id => $new_service) {
            if (isset($old_services_indexed[$service_id])) {
                $old_service = $old_services_indexed[$service_id];
                $service_title = $this->get_service_title($service_id);
                $changes = array();
                
                if (($old_service['service_cost'] ?? '') != ($new_service['service_cost'] ?? '')) {
                    $changes[] = sprintf(
                        "Preis: %s EUR → %s EUR",
                        number_format((float)($old_service['service_cost'] ?? 0), 2, ',', '.'),
                        number_format((float)($new_service['service_cost'] ?? 0), 2, ',', '.')
                    );
                }
                
                if (($old_service['quantity'] ?? '') != ($new_service['quantity'] ?? '')) {
                    $changes[] = sprintf(
                        "Menge: %s → %s",
                        $old_service['quantity'] ?? '0',
                        $new_service['quantity'] ?? '0'
                    );
                }
                
                if (($old_service['description'] ?? '') != ($new_service['description'] ?? '')) {
                    $changes[] = sprintf(
                        "Beschreibung: \"%s\" → \"%s\"",
                        $old_service['description'] ?? '',
                        $new_service['description'] ?? ''
                    );
                }
                
                if (!empty($changes)) {
                    $description = "Service \"{$service_title}\" geändert: " . implode(', ', $changes);
                    
                    $this->log_activity(
                        $request_id,
                        'service_modified',
                        $description,
                        json_encode($old_service),
                        json_encode($new_service)
                    );
                }
            }
        }
    }
    
    
    private function get_service_title($service_id) {
        global $wpdb;
        
        $title = $wpdb->get_var($wpdb->prepare(
            "SELECT title FROM {$wpdb->prefix}nexora_services WHERE id = %d",
            $service_id
        ));
        
        return $title ?: "Service #{$service_id}";
    }
    
    
    public function log_invoice_created($request_id, $invoice_data) {
        $description = sprintf(
            'Rechnung erstellt: %s EUR',
            number_format($invoice_data['total'], 2, ',', '.')
        );
        
        return $this->log_activity(
            $request_id,
            'invoice_created',
            $description,
            null,
            json_encode($invoice_data)
        );
    }
    
    
    public function log_request_deleted($request_id, $request_data) {
        $description = sprintf(
            'Service-Anfrage gelöscht: %s - %s',
            $request_data['serial'],
            $request_data['model']
        );
        
        return $this->log_activity(
            $request_id,
            'request_deleted',
            $description,
            json_encode($request_data),
            null
        );
    }
    
    
    public function get_request_logs($request_id, $limit = 50, $offset = 0) {
        global $wpdb;
        
        $query = $wpdb->prepare(
            "SELECT l.*, u.display_name as user_name, u.user_email
             FROM {$this->table_name} l
             LEFT JOIN {$wpdb->users} u ON l.user_id = u.ID
             WHERE l.request_id = %d
             ORDER BY l.created_at DESC
             LIMIT %d OFFSET %d",
            $request_id,
            $limit,
            $offset
        );
        
        return $wpdb->get_results($query);
    }
    
    
    public function get_request_logs_count($request_id) {
        global $wpdb;
        
        return $wpdb->get_var($wpdb->prepare(
            "SELECT COUNT(*) FROM {$this->table_name} WHERE request_id = %d",
            $request_id
        ));
    }
    
    
    public function ajax_get_request_logs() {
        check_ajax_referer('nexora_nonce', 'nonce');
        
        if (!current_user_can('manage_options') && !current_user_can('edit_posts')) {
            wp_send_json_error('Keine Berechtigung');
        }
        
        $request_id = intval($_POST['request_id']);
        $page = intval($_POST['page'] ?? 1);
        $per_page = intval($_POST['per_page'] ?? 20);
        $offset = ($page - 1) * $per_page;
        
        $logs = $this->get_request_logs($request_id, $per_page, $offset);
        $total = $this->get_request_logs_count($request_id);
        $processed_logs = array();
        foreach ($logs as $log) {
            $processed_logs[] = $this->process_log_for_display($log);
        }
        
        wp_send_json_success(array(
            'logs' => $processed_logs,
            'total' => $total,
            'page' => $page,
            'per_page' => $per_page,
            'total_pages' => ceil($total / $per_page)
        ));
    }
    
    
    private function process_log_for_display($log) {
        $processed = array(
            'id' => $log->id,
            'action_type' => $log->action_type,
            'created_at' => $log->created_at,
            'user_name' => $log->user_name ?: 'System',
            'ip_address' => $log->ip_address,
            'description' => $log->action_description
        );
        if ($log->action_type === 'status_change') {
            $processed['old_value'] = $log->old_value ? "Status: \"{$log->old_value}\"" : null;
            $processed['new_value'] = $log->new_value ? "Status: \"{$log->new_value}\"" : null;
        } elseif ($log->action_type === 'field_updated') {
            if (strpos($log->action_description, 'Zugewiesen an') !== false) {
                $old_value = $log->old_value;
                $new_value = $log->new_value;
                
                if ($old_value && is_numeric($old_value)) {
                    $old_user = get_user_by('id', $old_value);
                    $old_value = $old_user ? $old_user->display_name : "Benutzer ID: {$old_value}";
                }
                if ($new_value && is_numeric($new_value)) {
                    $new_user = get_user_by('id', $new_value);
                    $new_value = $new_user ? $new_user->display_name : "Benutzer ID: {$new_value}";
                }
                
                $processed['old_value'] = $old_value ? "Vorher: \"{$old_value}\"" : null;
                $processed['new_value'] = $new_value ? "Nachher: \"{$new_value}\"" : null;
            } else {
                $processed['old_value'] = $log->old_value ? "Vorher: \"{$log->old_value}\"" : null;
                $processed['new_value'] = $log->new_value ? "Nachher: \"{$log->new_value}\"" : null;
            }
        } elseif (in_array($log->action_type, ['service_added', 'service_removed', 'service_modified'])) {
            if ($log->old_value) {
                $old_service = json_decode($log->old_value, true);
                if ($old_service) {
                    $processed['old_value'] = $this->format_service_data($old_service);
                }
            }
            if ($log->new_value) {
                $new_service = json_decode($log->new_value, true);
                if ($new_service) {
                    $processed['new_value'] = $this->format_service_data($new_service);
                }
            }
        } else {
            $processed['old_value'] = $log->old_value;
            $processed['new_value'] = $log->new_value;
        }
        
        return $processed;
    }
    
    
    private function format_service_data($service_data) {
        $parts = array();
        
        if (isset($service_data['quantity'])) {
            $parts[] = "Menge: {$service_data['quantity']}";
        }
        
        if (isset($service_data['service_cost'])) {
            $cost = number_format((float)$service_data['service_cost'], 2, ',', '.');
            $parts[] = "Preis: {$cost} EUR";
        }
        
        if (isset($service_data['description']) && !empty($service_data['description'])) {
            $parts[] = "Beschreibung: \"{$service_data['description']}\"";
        }
        
        return implode(', ', $parts);
    }
    
    
    public function ajax_export_request_logs() {
        check_ajax_referer('nexora_nonce', 'nonce');
        
        if (!current_user_can('manage_options')) {
            wp_send_json_error('Keine Berechtigung');
        }
        
        $request_id = intval($_POST['request_id']);
        $logs = $this->get_request_logs($request_id, 1000, 0);
        
        $filename = 'activity_logs_request_' . $request_id . '_' . date('Y-m-d_H-i-s') . '.csv';
        
        header('Content-Type: text/csv; charset=utf-8');
        header('Content-Disposition: attachment; filename="' . $filename . '"');
        
        $output = fopen('php:
        fputcsv($output, array(
            'ID',
            'Datum',
            'Benutzer',
            'Aktion',
            'Beschreibung',
            'Alter Wert',
            'Neuer Wert',
            'IP-Adresse'
        ));
        foreach ($logs as $log) {
            fputcsv($output, array(
                $log->id,
                $log->created_at,
                $log->user_name,
                $log->action_type,
                $log->action_description,
                $log->old_value,
                $log->new_value,
                $log->ip_address
            ));
        }
        
        fclose($output);
        exit;
    }
    
    
    public function ajax_create_test_log() {
        check_ajax_referer('nexora_nonce', 'nonce');
        
        if (!current_user_can('manage_options')) {
            wp_send_json_error('Keine Berechtigung');
        }
        
        $request_id = intval($_POST['request_id']);
        
        $log_id = $this->create_test_log($request_id);
        
        if ($log_id) {
            wp_send_json_success(array(
                'message' => 'Test-Log erfolgreich erstellt.',
                'log_id' => $log_id
            ));
        } else {
            wp_send_json_error('Fehler beim Erstellen des Test-Logs.');
        }
    }
    
    
    public function create_test_log($request_id) {
        return $this->log_activity(
            $request_id,
            'test_action',
            'Test-Aktivität erstellt für Debugging-Zwecke',
            'Alter Test-Wert',
            'Neuer Test-Wert'
        );
    }
    
    
    private function get_client_ip() {
        $ip_keys = array('HTTP_CLIENT_IP', 'HTTP_X_FORWARDED_FOR', 'REMOTE_ADDR');
        
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
        
        return $_SERVER['REMOTE_ADDR'] ?? '';
    }
    
    
    public function get_action_type_label($action_type) {
        $labels = array(
            'request_created' => 'Anfrage erstellt',
            'request_updated' => 'Anfrage aktualisiert',
            'request_deleted' => 'Anfrage gelöscht',
            'status_change' => 'Status geändert',
            'comment_added' => 'Kommentar hinzugefügt',
            'invoice_created' => 'Rechnung erstellt',
            'invoice_updated' => 'Rechnung aktualisiert',
            'invoice_deleted' => 'Rechnung gelöscht',
            'file_uploaded' => 'Datei hochgeladen',
            'file_deleted' => 'Datei gelöscht',
            'user_assigned' => 'Benutzer zugewiesen',
            'priority_changed' => 'Priorität geändert',
            'deadline_set' => 'Deadline gesetzt',
            'deadline_updated' => 'Deadline aktualisiert',
            'notification_sent' => 'Benachrichtigung gesendet'
        );
        
        return $labels[$action_type] ?? $action_type;
    }
    
    
    public function get_action_type_icon($action_type) {
        $icons = array(
            'request_created' => '📝',
            'request_updated' => '✏️',
            'request_deleted' => '🗑️',
            'status_change' => '🔄',
            'comment_added' => '💬',
            'invoice_created' => '🧾',
            'invoice_updated' => '📄',
            'invoice_deleted' => '🗑️',
            'file_uploaded' => '📎',
            'file_deleted' => '🗑️',
            'user_assigned' => '👤',
            'priority_changed' => '⚡',
            'deadline_set' => '⏰',
            'deadline_updated' => '⏰',
            'notification_sent' => '📧'
        );
        
        return $icons[$action_type] ?? '📋';
    }
} 