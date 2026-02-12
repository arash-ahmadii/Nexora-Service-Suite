<?php

if (!defined('ABSPATH')) exit;

class Nexora_Device_Manager {
    private $table;
    private static $instance = null;
    
    public static function get_instance() {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }
    
    public function __construct() {
        global $wpdb;
        $this->table = $wpdb->prefix . 'nexora_devices';
        error_log("🏗️ Nexora Service Suite Device Manager Constructor - Table: " . $this->table);
        add_action('wp_ajax_nexora_device_crud', [$this, 'ajax_device_crud']);
    }

    public function render_admin_page() {
        global $wpdb;
        error_log("🔍 Nexora Service Suite Device Manager - Database Info:");
        error_log("   Database: " . $wpdb->dbname);
        error_log("   Table: " . $this->table);
        error_log("   Full Table Name: " . $this->table);
        
        include NEXORA_PLUGIN_DIR . 'templates/device-manager/device-manager-admin.php';
    }
    public function create_device($data) {
        global $wpdb;
        $name = trim($data['name'] ?? '');
        $type = $data['type'] ?? '';
        $parent_id = isset($data['parent_id']) ? intval($data['parent_id']) : null;
        $slug = $data['slug'] ?? '';
        if (!$name || !$type) return new WP_Error('missing_fields', 'Name und Typ sind erforderlich.');
        if (!$this->validate_parent($type, $parent_id)) return new WP_Error('invalid_parent', 'Ungültiger Eltern-Eintrag.');
        if (!$slug) $slug = $this->generate_unique_slug($name, $parent_id, $type);
        $res = $wpdb->insert($this->table, [
            'name' => $name,
            'slug' => $slug,
            'parent_id' => $parent_id,
            'type' => $type,
            'created_at' => current_time('mysql'),
            'updated_at' => current_time('mysql'),
        ]);
        if (!$res) return new WP_Error('db_error', 'Fehler beim Speichern.');
        return $wpdb->insert_id;
    }

    public function update_device($id, $data) {
        global $wpdb;
        $row = $this->get_device($id);
        if (!$row) return new WP_Error('not_found', 'Eintrag nicht gefunden.');
        $name = trim($data['name'] ?? $row->name);
        $type = $data['type'] ?? $row->type;
        $parent_id = isset($data['parent_id']) ? intval($data['parent_id']) : $row->parent_id;
        $slug = $data['slug'] ?? $row->slug;
        if (!$name || !$type) return new WP_Error('missing_fields', 'Name und Typ sind erforderlich.');
        if (!$this->validate_parent($type, $parent_id, $id)) return new WP_Error('invalid_parent', 'Ungültiger Eltern-Eintrag.');
        if (!$slug) $slug = $this->generate_unique_slug($name, $parent_id, $type, $id);
        $res = $wpdb->update($this->table, [
            'name' => $name,
            'slug' => $slug,
            'parent_id' => $parent_id,
            'type' => $type,
            'updated_at' => current_time('mysql'),
        ], ['id' => $id]);
        if ($res === false) return new WP_Error('db_error', 'Fehler beim Speichern.');
        return true;
    }

    public function delete_device($id, $cascade = false) {
        global $wpdb;
        if (!$cascade && $this->has_children($id)) return new WP_Error('has_children', 'Dieser Eintrag hat untergeordnete Elemente.');
        if ($cascade) {
            $children = $this->get_children($id);
            foreach ($children as $child) {
                $this->delete_device($child->id, true);
            }
        }
        $wpdb->delete($this->table, ['id' => $id]);
        return true;
    }

    public function get_device($id) {
        global $wpdb;
        return $wpdb->get_row($wpdb->prepare("SELECT * FROM {$this->table} WHERE id = %d", $id));
    }
    public function get_devices($type, $parent_id = null) {
        global $wpdb;
        $sql = "SELECT * FROM {$this->table} WHERE type = %s";
        $params = [$type];
        if (!is_null($parent_id)) {
            $sql .= " AND parent_id = %d";
            $params[] = $parent_id;
        }
        $sql .= " ORDER BY name ASC";
        return $wpdb->get_results($wpdb->prepare($sql, ...$params));
    }
    public function get_children($parent_id) {
        global $wpdb;
        return $wpdb->get_results($wpdb->prepare("SELECT * FROM {$this->table} WHERE parent_id = %d", $parent_id));
    }
    public function has_children($id) {
        global $wpdb;
        return (int)$wpdb->get_var($wpdb->prepare("SELECT COUNT(*) FROM {$this->table} WHERE parent_id = %d", $id)) > 0;
    }
    public function exists_name($name, $parent_id, $type, $exclude_id = null) {
        global $wpdb;
        $sql = "SELECT COUNT(*) FROM {$this->table} WHERE name = %s AND type = %s AND ";
        $params = [$name, $type];
        if (is_null($parent_id)) {
            $sql .= "parent_id IS NULL";
        } else {
            $sql .= "parent_id = %d";
            $params[] = $parent_id;
        }
        if ($exclude_id) {
            $sql .= " AND id != %d";
            $params[] = $exclude_id;
        }
        return (int)$wpdb->get_var($wpdb->prepare($sql, ...$params)) > 0;
    }
    public function generate_unique_slug($name, $parent_id, $type, $exclude_id = null) {
        $slug = sanitize_title($name);
        $base = $slug;
        $i = 1;
        if (empty($slug) || $slug === '-') {
            $slug = sanitize_title($name . '-' . $type);
        }
        if ($parent_id) {
            $parent = $this->get_device($parent_id);
            if ($parent) {
                $parent_slug = sanitize_title($parent->name);
                $slug = $parent_slug . '-' . $slug;
            }
        }
        
        $original_slug = $slug;
        while ($this->exists_slug($slug, $parent_id, $type, $exclude_id)) {
            $slug = $original_slug . '-' . $i++;
        }
        return $slug;
    }
    public function exists_slug($slug, $parent_id, $type, $exclude_id = null) {
        global $wpdb;
        $sql = "SELECT COUNT(*) FROM {$this->table} WHERE slug = %s AND type = %s AND ";
        $params = [$slug, $type];
        if (is_null($parent_id)) {
            $sql .= "parent_id IS NULL";
        } else {
            $sql .= "parent_id = %d";
            $params[] = $parent_id;
        }
        if ($exclude_id) {
            $sql .= " AND id != %d";
            $params[] = $exclude_id;
        }
        return (int)$wpdb->get_var($wpdb->prepare($sql, ...$params)) > 0;
    }
    public function validate_parent($type, $parent_id, $self_id = null) {
        if ($type === 'type') return is_null($parent_id);
        if (is_null($parent_id)) return false;
        $parent = $this->get_device($parent_id);
        if (!$parent) return false;
        if ($self_id && $parent_id == $self_id) return false;
        if ($type === 'brand' && $parent->type !== 'type') return false;
        if ($type === 'series' && $parent->type !== 'brand') return false;
        if ($type === 'model' && $parent->type !== 'series') return false;
        return true;
    }
    public function get_statistics() {
        global $wpdb;
        $types_count = (int)$wpdb->get_var("SELECT COUNT(*) FROM {$this->table} WHERE type = 'type'");
        $brands_count = (int)$wpdb->get_var("SELECT COUNT(*) FROM {$this->table} WHERE type = 'brand'");
        $series_count = (int)$wpdb->get_var("SELECT COUNT(*) FROM {$this->table} WHERE type = 'series'");
        $models_count = (int)$wpdb->get_var("SELECT COUNT(*) FROM {$this->table} WHERE type = 'model'");
        
        return [
            'types' => $types_count,
            'brands' => $brands_count,
            'series' => $series_count,
            'models' => $models_count
        ];
    }
    public function ajax_device_crud() {
        if (!current_user_can('manage_options')) wp_send_json_error('Keine Berechtigung.');
        check_ajax_referer('nexora_device_nonce', 'nonce');
        $action = $_POST['action_type'] ?? '';
        $data = $_POST['data'] ?? [];
        $id = isset($_POST['id']) ? intval($_POST['id']) : null;
        $cascade = isset($_POST['cascade']) && $_POST['cascade'] == '1';
        switch ($action) {
            case 'create':
                $res = $this->create_device($data);
                break;
            case 'update':
                $res = $this->update_device($id, $data);
                break;
            case 'delete':
                $res = $this->delete_device($id, $cascade);
                break;
            case 'list':
                $type = $data['type'] ?? '';
                $parent_id = $data['parent_id'] ?? null;
                $devices = $this->get_devices($type, $parent_id);
                foreach ($devices as &$dev) {
                    $dev = (array)$dev;
                    if ($dev['parent_id']) {
                        $parent = $this->get_device($dev['parent_id']);
                        $dev['parent_name'] = $parent ? $parent->name : '';
                    } else {
                        $dev['parent_name'] = '';
                    }
                }
                $res = $devices;
                break;
            case 'get':
                $res = $this->get_device($id);
                break;
            case 'statistics':
                $res = $this->get_statistics();
                break;
            default:
                $res = new WP_Error('invalid_action', 'Ungültige Aktion.');
        }
        if (is_wp_error($res)) {
            wp_send_json_error($res->get_error_message());
        } else {
            wp_send_json_success($res);
        }
    }
} 