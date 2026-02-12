<?php
class Nexora_Service_Handler {
    private $table_name;
    
    public function __construct() {
        global $wpdb;
        $this->table_name = $wpdb->prefix . 'nexora_services';
        
        $this->init_hooks();
    }
    
    private function init_hooks() {
        add_action('wp_ajax_nexora_add_service', array($this, 'ajax_add_service'));
        add_action('wp_ajax_nexora_update_service', array($this, 'ajax_update_service'));
        add_action('wp_ajax_nexora_delete_service', array($this, 'ajax_delete_service'));
        add_action('wp_ajax_nexora_get_services', array($this, 'ajax_get_services'));
        add_action('wp_ajax_nexora_change_service_status', array($this, 'ajax_change_service_status'));
        add_action('wp_ajax_nexora_search_services', array($this, 'ajax_nexora_search_services'));
        add_action('wp_ajax_nexora_get_service_customer_info', array($this, 'ajax_get_service_customer_info'));
        add_action('wp_ajax_nexora_test_db', array($this, 'ajax_test_db'));
        add_action('wp_ajax_nexora_create_sample_services', array($this, 'ajax_create_sample_services'));
    }

    public function ajax_nexora_search_services() {
        global $wpdb;
        $q = sanitize_text_field($_POST['q'] ?? '');
        $like = '%' . $wpdb->esc_like($q) . '%';
        $services = $wpdb->get_results(
            $wpdb->prepare(
                "SELECT id, title, cost FROM {$wpdb->prefix}nexora_services WHERE status = 1 AND title LIKE %s LIMIT 20", 
                $like
            ),
            ARRAY_A
        );
        wp_send_json_success($services);
    }
    
    public function ajax_add_service() {

        $this->verify_nonce();

        global $wpdb;
        
        $data = array(
            'title' => sanitize_text_field($_POST['title']),
            'description' => sanitize_textarea_field($_POST['description']),
            'cost' => floatval($_POST['cost']),
            'status' => sanitize_textarea_field($_POST['status']),
            'user_id' => isset($_POST['user_id']) ? intval($_POST['user_id']) : null
        );
        
      
        $format = array('%s', '%s', '%f', '%s', '%d');
        
        $result = $wpdb->insert($this->table_name, $data, $format);
        

        if ($result) {
            wp_send_json_success(array(
                'message' => 'Service erfolgreich hinzugefügt.',
                'id' => $wpdb->insert_id
            ));
        } else {
            wp_send_json_error('Fehler beim Hinzufügen des Services.');
        }
    }
    
    public function ajax_update_service() {
        $this->verify_nonce();

        
  
        global $wpdb;
        
        $service_id = intval($_POST['id']);
        
        $data = array(
            'title' => sanitize_text_field($_POST['title']),
            'description' => sanitize_textarea_field($_POST['description']),
            'cost' => floatval($_POST['cost']),
            'status' => sanitize_textarea_field($_POST['status']),
            'user_id' => isset($_POST['user_id']) ? intval($_POST['user_id']) : null
        );
        
        $format = array('%s', '%s', '%f', '%s', '%d');
        $where = array('id' => $service_id);
        $where_format = array('%d');
        
        $result = $wpdb->update($this->table_name, $data, $where, $format, $where_format);
        
        if ($result !== false) {
            wp_send_json_success('Service erfolgreich aktualisiert.');
        } else {
            wp_send_json_error('Fehler beim Aktualisieren des Services.');
        }
    }
    
    public function ajax_delete_service() {
        $this->verify_nonce();
        
        global $wpdb; 
        $service_id = intval($_POST['id']);

            $result = $wpdb->delete($this->table_name, array('id' => $service_id), array('%d'));
            if ($result) {
                wp_send_json_success('Service erfolgreich gelöscht.');
            } else {
                wp_send_json_error('Fehler beim Löschen des Services.');
            }
}
        
    
    
    public function ajax_get_services() {
        $this->verify_nonce();
        
        global $wpdb;
        
        $page = isset($_POST['page']) ? intval($_POST['page']) : 1;
        $per_page = isset($_POST['per_page']) ? intval($_POST['per_page']) : 10;
        $offset = ($page - 1) * $per_page;
        
        $search = isset($_POST['search']) ? sanitize_text_field($_POST['search']) : '';
        $status_filter = isset($_POST['status_filter']) ? sanitize_text_field($_POST['status_filter']) : '';
        $query = "SELECT s.*, u.user_login, u.user_email, u.display_name
                  FROM {$this->table_name} s
                  LEFT JOIN {$wpdb->users} u ON s.user_id = u.ID";
        
        $where = array();
        $params = array();
        
        if (!empty($search)) {
            $where[] = "(s.title LIKE %s OR s.description LIKE %s)";
            $params[] = "%{$search}%";
            $params[] = "%{$search}%";
        }
        
        if (!empty($status_filter)) {
            $where[] = "s.status = %s";
            $params[] = $status_filter;
        }
        
        if (!empty($where)) {
            $query .= " WHERE " . implode(' AND ', $where);
        }
        $total_query = "SELECT COUNT(1) FROM ({$query}) AS total";
        $total = $wpdb->get_var($wpdb->prepare($total_query, $params));
        $query .= " ORDER BY s.id DESC LIMIT %d, %d";
        $params[] = $offset;
        $params[] = $per_page;
        
        $services = $wpdb->get_results($wpdb->prepare($query, $params));
        error_log('Nexora Service Suite Services Query: ' . $query);
        error_log('Nexora Service Suite Services Params: ' . print_r($params, true));
        error_log('Nexora Service Suite Services Found: ' . count($services));
        error_log('Nexora Service Suite Services Total: ' . $total);
        
        wp_send_json_success(array(
            'services' => $services,
            'total' => $total,
            'page' => $page,
            'per_page' => $per_page,
            'total_pages' => ceil($total / $per_page)
        ));
    }
    
    public function ajax_change_service_status() {
        $this->verify_nonce();
        
        global $wpdb;
        
        $service_id = intval($_POST['id']);
        $new_status = intval($_POST['status_id']);
        
        $result = $wpdb->update(
            $this->table_name,
            array('status_service_id' => $new_status),
            array('id' => $service_id),
            array('%d'),
            array('%d')
        );
        
        if ($result !== false) {
            wp_send_json_success('Service-Status erfolgreich geändert.');
        } else {
            wp_send_json_error('Fehler beim Ändern des Service-Status.');
        }
    }
    
    public function ajax_get_service_customer_info() {
        $this->verify_nonce();
        
        global $wpdb;
        $service_id = intval($_POST['service_id']);
        
        $service = $wpdb->get_row($wpdb->prepare(
            "SELECT s.*, u.user_login, u.user_email, u.display_name 
             FROM {$this->table_name} s 
             LEFT JOIN {$wpdb->users} u ON s.user_id = u.ID 
             WHERE s.id = %d",
            $service_id
        ));
        
        if ($service) {
            wp_send_json_success($service);
        } else {
            wp_send_json_error('Service nicht gefunden.');
        }
    }
    
    public function ajax_test_db() {
        $this->verify_nonce();
        
        global $wpdb;
        $table_exists = $wpdb->get_var("SHOW TABLES LIKE '{$this->table_name}'") == $this->table_name;
        
        if (!$table_exists) {
            wp_send_json_error('Tabelle ' . $this->table_name . ' existiert nicht.');
            return;
        }
        $columns = $wpdb->get_results("DESCRIBE {$this->table_name}");
        $column_names = array_column($columns, 'Field');
        $required_columns = ['id', 'title', 'description', 'cost', 'status', 'created_at'];
        $missing_columns = array_diff($required_columns, $column_names);
        
        if (!empty($missing_columns)) {
            wp_send_json_error('Fehlende Spalten: ' . implode(', ', $missing_columns));
            return;
        }
        $count = $wpdb->get_var("SELECT COUNT(*) FROM {$this->table_name}");
        
        wp_send_json_success(array(
            'message' => 'Datenbank-Verbindung erfolgreich',
            'table_exists' => true,
            'columns' => $column_names,
            'row_count' => intval($count)
        ));
    }
    
    public function ajax_create_sample_services() {
        $this->verify_nonce();
        
        global $wpdb;
        $table_exists = $wpdb->get_var("SHOW TABLES LIKE '{$this->table_name}'") == $this->table_name;
        
        if (!$table_exists) {
            wp_send_json_error('Tabelle existiert nicht. Bitte aktivieren Sie das Plugin neu.');
            return;
        }
        $sample_services = array(
            array(
                'title' => 'iPhone Reparatur',
                'description' => 'Professionelle Reparatur von iPhone Geräten aller Modelle',
                'cost' => 89.99,
                'status' => 'active',
                'created_at' => current_time('mysql')
            ),
            array(
                'title' => 'Samsung Display Austausch',
                'description' => 'Austausch von Samsung Smartphone Displays',
                'cost' => 129.99,
                'status' => 'active',
                'created_at' => current_time('mysql')
            ),
            array(
                'title' => 'Laptop Reinigung',
                'description' => 'Professionelle Reinigung und Wartung von Laptops',
                'cost' => 49.99,
                'status' => 'active',
                'created_at' => current_time('mysql')
            ),
            array(
                'title' => 'Tablet Reparatur',
                'description' => 'Reparatur von iPad und Android Tablets',
                'cost' => 79.99,
                'status' => 'active',
                'created_at' => current_time('mysql')
            ),
            array(
                'title' => 'PC Hardware Upgrade',
                'description' => 'Upgrade von RAM, SSD und anderen PC Komponenten',
                'cost' => 149.99,
                'status' => 'active',
                'created_at' => current_time('mysql')
            )
        );
        
        $inserted_count = 0;
        $errors = array();
        
        foreach ($sample_services as $service) {
            $result = $wpdb->insert($this->table_name, $service, array('%s', '%s', '%f', '%s', '%s'));
            if ($result) {
                $inserted_count++;
            } else {
                $errors[] = 'Fehler beim Einfügen von: ' . $service['title'];
            }
        }
        
        if ($inserted_count > 0) {
            wp_send_json_success(array(
                'message' => "{$inserted_count} Beispieldienstleistungen erfolgreich erstellt.",
                'inserted_count' => $inserted_count,
                'errors' => $errors
            ));
        } else {
            wp_send_json_error('Keine Beispieldaten konnten erstellt werden: ' . implode(', ', $errors));
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