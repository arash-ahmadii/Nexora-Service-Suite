<?php
class Nexora_Service_Status_Handler {
    private $table_name;
    
    public function __construct() {
        global $wpdb;
        $this->table_name = $wpdb->prefix . 'nexora_service_status';
        
        $this->init_hooks();
    }
    
    private function sanitize_hex_color($color) {
        if ('' === $color) {
            return '';
        }
        if (preg_match('|^#([A-Fa-f0-9]{3}){1,2}$|', $color)) {
            return $color;
        }
        
        return '#0073aa';
    }
    
    private function init_hooks() {
        add_action('wp_ajax_nexora_add_service_status', array($this, 'ajax_add_service_status'));
        add_action('wp_ajax_nexora_update_service_status', array($this, 'ajax_update_service_status'));
        add_action('wp_ajax_nexora_delete_service_status', array($this, 'ajax_delete_service_status'));
        add_action('wp_ajax_nexora_bulk_delete_service_status', array($this, 'ajax_bulk_delete_service_status'));
        add_action('wp_ajax_nexora_get_service_statuses', array($this, 'ajax_get_service_statuses'));
    }
    
    public function ajax_add_service_status() {
        $this->verify_nonce();
        
        global $wpdb;

         if($_POST['is_default'])
        {
            $wpdb->update(
            $this->table_name,
            ['is_default' => 0],
            array('is_default' =>1),
            array('%s'),
            array('%d'));
        }
        
        $data = array(
            'title' => sanitize_text_field($_POST['title']),
            'color' => $this->sanitize_hex_color($_POST['color']),
            'is_default' => isset($_POST['is_default']) ? intval($_POST['is_default']) : 0

        );
        
        $result = $wpdb->insert($this->table_name, $data);
        
        if ($result) {
            wp_send_json_success(array(
                'message' => 'Dienstleistungsstatus wurde erfolgreich hinzugefügt.',
                'id' => $wpdb->insert_id
            ));
        } else {
            wp_send_json_error('Fehler beim Hinzufügen des Dienstleistungsstatus.');
        }
    }
    
    public function ajax_update_service_status() {
        $this->verify_nonce();
        
        global $wpdb;
        
        $status_id = intval($_POST['id']);

        if($_POST['is_default'])
        {
            $wpdb->update(
            $this->table_name,
            ['is_default' => 0],
            array('is_default' =>1),
            array('%s'),
            array('%d'));
        }

        
        $data = array(
            'title' => sanitize_text_field($_POST['title']),
            'color' => $this->sanitize_hex_color($_POST['color']),
            'is_default' => isset($_POST['is_default']) ? intval($_POST['is_default']) : 0

        );
        
        $result = $wpdb->update(
            $this->table_name,
            $data,
            array('id' => $status_id),
            array('%s'),
            array('%d')
        );
        
        if ($result !== false) {
            wp_send_json_success('Dienstleistungsstatus wurde erfolgreich aktualisiert.');
        } else {
            wp_send_json_error('Fehler beim Aktualisieren des Dienstleistungsstatus.');
        }
    }
    
    public function ajax_delete_service_status() {
        $this->verify_nonce();
        
        global $wpdb;
        
        $status_id = intval($_POST['id']);
        $requests_table = $wpdb->prefix . 'nexora_service_requests';
        $in_use = $wpdb->get_var($wpdb->prepare(
            "SELECT COUNT(*) FROM $requests_table WHERE status_id = %d",
            $status_id
        ));
        
        if ($in_use > 0) {
            $replacement_status = $wpdb->get_var($wpdb->prepare(
                "SELECT id FROM {$this->table_name} WHERE id != %d ORDER BY id LIMIT 1",
                $status_id
            ));
            
            if (!$replacement_status) {
                wp_send_json_error('Kein Ersatzstatus verfügbar. Mindestens ein Status muss bestehen bleiben.');
            }
            $wpdb->update(
                $requests_table,
                array('status_id' => $replacement_status),
                array('status_id' => $status_id),
                array('%d'),
                array('%d')
            );
        }
        
        $result = $wpdb->delete(
            $this->table_name,
            array('id' => $status_id),
            array('%d')
        );
        
        if ($result) {
            $message = $in_use > 0 
                ? "Status wurde erfolgreich gelöscht. {$in_use} Anfragen wurden auf einen anderen Status aktualisiert."
                : 'Dienstleistungsstatus wurde erfolgreich gelöscht.';
            wp_send_json_success($message);
        } else {
            wp_send_json_error('Fehler beim Löschen des Dienstleistungsstatus.');
        }
    }
    
    public function ajax_bulk_delete_service_status() {
        $this->verify_nonce();
        
        global $wpdb;
        
        if (!isset($_POST['ids']) || !is_array($_POST['ids'])) {
            wp_send_json_error('Keine gültigen Status-IDs übermittelt.');
        }
        
        $status_ids = array_map('intval', $_POST['ids']);
        $deleted_count = 0;
        $updated_requests = 0;
        $replacement_status = $wpdb->get_var($wpdb->prepare(
            "SELECT id FROM {$this->table_name} WHERE id NOT IN (" . implode(',', array_fill(0, count($status_ids), '%d')) . ") ORDER BY id LIMIT 1",
            ...$status_ids
        ));
        
        if (!$replacement_status) {
            wp_send_json_error('Kein Ersatzstatus verfügbar. Mindestens ein Status muss bestehen bleiben.');
        }
        $requests_table = $wpdb->prefix . 'nexora_service_requests';
        $in_use_statuses = $wpdb->get_results($wpdb->prepare(
            "SELECT status_id, COUNT(*) as count FROM $requests_table WHERE status_id IN (" . implode(',', array_fill(0, count($status_ids), '%d')) . ") GROUP BY status_id",
            ...$status_ids
        ));
        foreach ($in_use_statuses as $usage) {
            $wpdb->update(
                $requests_table,
                array('status_id' => $replacement_status),
                array('status_id' => $usage->status_id),
                array('%d'),
                array('%d')
            );
            $updated_requests += $usage->count;
        }
        foreach ($status_ids as $status_id) {
            $result = $wpdb->delete(
                $this->table_name,
                array('id' => $status_id),
                array('%d')
            );
            if ($result) {
                $deleted_count++;
            }
        }
        
        if ($deleted_count > 0) {
            $message = "{$deleted_count} Status wurden erfolgreich gelöscht.";
            if ($updated_requests > 0) {
                $message .= " {$updated_requests} Anfragen wurden auf einen anderen Status aktualisiert.";
            }
            wp_send_json_success($message);
        } else {
            wp_send_json_error('Fehler beim Löschen der Status.');
        }
    }
    
    public function ajax_get_service_statuses() {
        $this->verify_nonce();
        
        global $wpdb;
        $status_id = isset($_POST['id']) ? intval($_POST['id']) : 0;
        $page = isset($_POST['page']) ? intval($_POST['page']) : 1;
        $per_page = isset($_POST['per_page']) ? intval($_POST['per_page']) : 10;
        $search = isset($_POST['search']) ? sanitize_text_field($_POST['search']) : '';
        
        if ($status_id > 0) {
            $status = $wpdb->get_row($wpdb->prepare(
                "SELECT * FROM {$this->table_name} WHERE id = %d",
                $status_id
            ));
            
            if ($status) {
                wp_send_json_success(array($status));
            } else {
                wp_send_json_error('Der gewünschte Status wurde nicht gefunden.');
            }
        } else {
            $offset = ($page - 1) * $per_page;
            
            $where = '';
            $params = array();
            
            if (!empty($search)) {
                $where = " WHERE title LIKE %s";
                $params[] = '%' . $wpdb->esc_like($search) . '%';
            }
            
            $query = "SELECT * FROM {$this->table_name} {$where} ORDER BY id LIMIT %d, %d";
            $params[] = $offset;
            $params[] = $per_page;
            
            $statuses = $wpdb->get_results($wpdb->prepare($query, $params));
            
            $total_query = "SELECT COUNT(*) FROM {$this->table_name} {$where}";
            $total = $wpdb->get_var($wpdb->prepare($total_query, $params));
            
            wp_send_json_success(array(
                'statuses' => $statuses,
                'total' => $total,
                'page' => $page,
                'per_page' => $per_page,
                'total_pages' => ceil($total / $per_page)
            ));
        }
    }
    
    private function verify_nonce() {
        if (!isset($_POST['nonce']) || !wp_verify_nonce($_POST['nonce'], 'nexora_nonce')) {
            wp_send_json_error('Nonce verification failed', 403);
        }
        
        if (!current_user_can('manage_options')) {
            wp_send_json_error('Unbefugter Zugriff', 403);
        }
    }
}