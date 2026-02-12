<?php
class Nexora_User_Service_Request {
    
    public function __construct() {
        add_action('init', array($this, 'init_hooks'));
        if (get_option('nexora_flush_rewrite_rules', false) === false) {
            add_action('init', array($this, 'flush_rewrite_rules'), 20);
            update_option('nexora_flush_rewrite_rules', true);
        }
    }
    
    public function init_hooks() {
        add_action('init', array($this, 'add_endpoints'), 10);
        add_action('init', array($this, 'add_custom_rewrite_rules'), 20);
        add_action('init', array($this, 'debug_rewrite_rules'), 30);
        add_filter('query_vars', array($this, 'add_query_vars'));
        add_action('template_redirect', array($this, 'template_redirect'));
        add_action('wp_ajax_nexora_get_brand_children', array($this, 'get_brand_children'));
        add_action('wp_ajax_nopriv_nexora_get_brand_children', array($this, 'get_brand_children'));
        add_action('wp_ajax_nexora_submit_service_request', array($this, 'submit_service_request'));
        add_action('wp_ajax_nopriv_nexora_submit_service_request', array($this, 'submit_service_request'));
    }
    
    public function add_endpoints() {
        add_rewrite_endpoint('service-request', EP_ROOT | EP_PAGES);
    }
    
    public function add_custom_rewrite_rules() {
        add_rewrite_rule(
            '^my-account/service-request/?$',
            'index.php?pagename=my-account&service-request=1',
            'top'
        );
    }
    
    public function flush_rewrite_rules() {
        flush_rewrite_rules();
    }
    
    public function enqueue_scripts() {
        if (is_account_page()) {
            
            wp_localize_script('Nexora Service Suite-user-request-js', 'nexora_user_ajax', array(
                'ajax_url' => admin_url('admin-ajax.php'),
                'nonce' => wp_create_nonce('nexora_user_nonce')
            ));
        }
    }
    
    public function get_brand_children() {
        check_ajax_referer('nexora_user_nonce', 'nonce');
        
        $parent_id = intval($_POST['parent_id']);
        
        global $wpdb;
        $children = $wpdb->get_results($wpdb->prepare(
            "SELECT id, name FROM {$wpdb->prefix}nexora_brands WHERE parent_id = %d",
            $parent_id
        ));
        
        wp_send_json_success($children);
    }
    
    public function submit_service_request() {
        check_ajax_referer('nexora_user_nonce', 'nonce');
        
        if (!is_user_logged_in()) {
            wp_send_json_error('Benutzer ist nicht angemeldet');
        }
        
        $user_id = get_current_user_id();
        $service_id = intval($_POST['service_id']);
        $description = sanitize_textarea_field($_POST['description']);
        
        $device_type_id = intval($_POST['device_type_id']);
        $device_brand_id = intval($_POST['device_brand_id']);
        $device_series_id = intval($_POST['device_series_id']);
        $device_model_id = intval($_POST['device_model_id']);
        if (!$device_type_id || !$device_brand_id || !$device_series_id || !$device_model_id) {
            wp_send_json_error('Bitte wählen Sie Gerätetyp, Marke, Serie und Modell.');
        }
        global $wpdb;
        $device_type_name = $wpdb->get_var($wpdb->prepare("SELECT name FROM {$wpdb->prefix}nexora_device_types WHERE id = %d", $device_type_id));
        $device_brand_name = $wpdb->get_var($wpdb->prepare("SELECT name FROM {$wpdb->prefix}nexora_device_brands WHERE id = %d", $device_brand_id));
        $device_series_name = $wpdb->get_var($wpdb->prepare("SELECT name FROM {$wpdb->prefix}nexora_device_series WHERE id = %d", $device_series_id));
        $device_model_name = $wpdb->get_var($wpdb->prepare("SELECT name FROM {$wpdb->prefix}nexora_device_models WHERE id = %d", $device_model_id));
        
        $model = $device_type_name . ' ' . $device_brand_name . ' ' . $device_series_name . ' ' . $device_model_name;
        $default_status = $wpdb->get_var("SELECT id FROM {$wpdb->prefix}nexora_service_status WHERE title = 'Neu' LIMIT 1");
        if (!$default_status) {
            $default_status = 1;
        }
        $result = $wpdb->insert(
            $wpdb->prefix . 'nexora_service_requests',
            array(
                'device_type_id' => $device_type_id,
                'device_brand_id' => $device_brand_id,
                'device_series_id' => $device_series_id,
                'device_model_id' => $device_model_id,
                'description' => $description,
                'user_id' => $user_id,
                'service_id' => $service_id,
                'status_id' => $default_status,
                'created_at' => current_time('mysql'),
                'updated_at' => current_time('mysql')
            ),
            array('%d', '%d', '%d', '%d', '%s', '%d', '%d', '%d', '%s', '%s')
        );
        
        if ($result) {
            $request_id = $wpdb->insert_id;
            if (class_exists('Nexora_Activity_Logger')) {
                $logger = new Nexora_Activity_Logger();
                $logger->log_request_created($request_id, array(
                    'serial' => '',
                    'model' => $model,
                    'description' => $description
                ));
            }
            if (class_exists('Nexora_Admin_Notifications')) {
                Nexora_Admin_Notifications::notify_new_service_request($request_id, $user_id, '', $model);
            }
            do_action('nexora_service_request_created', $request_id, $user_id);
            
            wp_send_json_success('Ihre Anfrage wurde erfolgreich übermittelt');
        } else {
            wp_send_json_error('Fehler bei der Anfrageübermittlung');
        }
    }
    
    private function process_brand_input($select_value, $custom_value, $parent_id) {
        global $wpdb;
        
        if (!empty($select_value)) {
            return intval($select_value);
        }
        
        if (!empty($custom_value)) {
            $custom_name = sanitize_text_field($custom_value);
            $existing = $wpdb->get_var($wpdb->prepare(
                "SELECT id FROM {$wpdb->prefix}nexora_brands WHERE name = %s AND parent_id " . ($parent_id ? "= %d" : "IS NULL"),
                $custom_name,
                $parent_id
            ));
            
            if ($existing) {
                return intval($existing);
            }
            $result = $wpdb->insert(
                $wpdb->prefix . 'nexora_brands',
                array(
                    'name' => $custom_name,
                    'parent_id' => $parent_id
                ),
                array('%s', '%d')
            );
            
            if ($result) {
                return $wpdb->insert_id;
            }
        }
        
        return false;
    }
    
    public function add_query_vars($vars) {
        $vars[] = 'service-request';
        return $vars;
    }
    
    public function template_redirect() {
        if (isset($_GET['service-request']) || get_query_var('service-request')) {
            $this->service_request_endpoint_content();
            exit;
        }
        if (strpos($_SERVER['REQUEST_URI'], '/my-account/service-request') !== false) {
            $this->service_request_endpoint_content();
            exit;
        }
    }
    
    public function service_request_shortcode($atts) {
        if (!is_user_logged_in()) {
            return '<p>Bitte melden Sie sich zuerst in Ihrem Benutzerkonto an.</p>';
        }
        
        ob_start();
        $this->service_request_endpoint_content();
        return ob_get_clean();
    }
    
    public function test_endpoint() {
        wp_send_json_success(array(
            'message' => 'Endpoint is working!',
            'user_id' => get_current_user_id(),
            'is_logged_in' => is_user_logged_in(),
            'endpoint_url' => home_url('/my-account/service-request/'),
            'rewrite_rules' => get_option('rewrite_rules'),
            'current_url' => $_SERVER['REQUEST_URI'],
            'is_account_page' => is_account_page(),
            'is_service_request' => isset($_GET['service-request'])
        ));
    }
    public function debug_rewrite_rules() {
        if (isset($_GET['debug_nexora']) && current_user_can('manage_options')) {
            global $wp_rewrite;
            echo '<h2>Debug Nexora Service Suite Rewrite Rules</h2>';
            echo '<h3>Current URL: ' . $_SERVER['REQUEST_URI'] . '</h3>';
            echo '<h3>Is Account Page: ' . (is_account_page() ? 'Yes' : 'No') . '</h3>';
            echo '<h3>Is Service Request: ' . (isset($_GET['service-request']) ? 'Yes' : 'No') . '</h3>';
            echo '<h3>Rewrite Rules:</h3>';
            echo '<pre>';
            print_r($wp_rewrite->rules);
            echo '</pre>';
            echo '<h3>Query Vars:</h3>';
            echo '<pre>';
            print_r($GLOBALS['wp_query']->query_vars);
            echo '</pre>';
            exit;
        }
    }
    
    public function service_request_endpoint_content() {
        ob_start();
        global $wpdb;
        $services = $wpdb->get_results("SELECT id, title, cost FROM {$wpdb->prefix}nexora_services WHERE status = 'active'");
        $brands_level1 = $wpdb->get_results("SELECT id, name FROM {$wpdb->prefix}nexora_brands WHERE parent_id IS NULL");
        $user_id = get_current_user_id();
        $previous_requests = $wpdb->get_results($wpdb->prepare(
            "SELECT sr.*, s.title as service_title, s.cost as service_cost, ss.title as status_title, ss.color as status_color 
             FROM {$wpdb->prefix}nexora_service_requests sr
             LEFT JOIN {$wpdb->prefix}nexora_services s ON sr.service_id = s.id
             LEFT JOIN {$wpdb->prefix}nexora_service_status ss ON sr.status_id = ss.id
             WHERE sr.user_id = %d 
             ORDER BY sr.created_at DESC 
             LIMIT 10",
            $user_id
        ));
        ?>
        <div class="Nexora Service Suite-service-request-container">
            <h2>Neue Serviceanfrage erstellen</h2>
            <form id="Nexora Service Suite-service-request-form" class="Nexora Service Suite-form">
                <?php wp_nonce_field('nexora_user_nonce', 'nonce'); ?>
                <div class="form-row">
                    <div class="form-group">
                        <label for="service_id">Serviceart *</label>
                        <select name="service_id" id="service_id" required>
                            <option value="">Bitte wählen Sie aus</option>
                            <?php foreach ($services as $service): ?>
                                <option value="<?php echo esc_attr($service->id); ?>" data-cost="<?php echo esc_attr($service->cost); ?>">
                                    <?php echo esc_html($service->title); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                        <div id="Nexora Service Suite-service-cost-display" style="margin-top:8px; color:#0073aa; font-weight:600; display:none;"></div>
                    </div>
                </div>
                <div class="form-row">
                    <div class="form-group">
                        <label for="device_type_id">Gerätetyp *</label>
                        <select name="device_type_id" id="device_type_id" required>
                            <option value="">Bitte wählen Sie aus</option>
                        </select>
                    </div>
                </div>
                <div class="form-row">
                    <div class="form-group">
                        <label for="device_brand_id">Marke *</label>
                        <select name="device_brand_id" id="device_brand_id" required disabled>
                            <option value="">Bitte wählen Sie zuerst einen Gerätetyp</option>
                        </select>
                    </div>
                </div>
                <div class="form-row">
                    <div class="form-group">
                        <label for="device_series_id">Serie *</label>
                        <select name="device_series_id" id="device_series_id" required disabled>
                            <option value="">Bitte wählen Sie zuerst eine Marke</option>
                        </select>
                    </div>
                </div>
                <div class="form-row">
                    <div class="form-group">
                        <label for="device_model_id">Gerätemodell *</label>
                        <select name="device_model_id" id="device_model_id" required disabled>
                            <option value="">Bitte wählen Sie zuerst eine Serie</option>
                        </select>
                    </div>
                </div>
                <div class="form-row">
                    <div class="form-group">
                        <label for="serial">Seriennummer</label>
                        <input type="text" name="serial" id="serial" placeholder="Seriennummer des Geräts (optional)">
                    </div>
                </div>
                <div class="form-row">
                    <div class="form-group">
                        <label for="description">Beschreibung *</label>
                        <textarea name="description" id="description" rows="5" placeholder="Detaillierte Beschreibung oder Problembeschreibung" required></textarea>
                    </div>
                </div>
                <div class="form-actions">
                    <button type="submit" class="button-primary">Serviceanfrage senden</button>
                </div>
            </form>
            <?php if (!empty($previous_requests)): ?>
                <div class="previous-requests">
                    <h3>Frühere Anfragen</h3>
                    <div class="requests-list">
                        <?php foreach ($previous_requests as $request): ?>
                            <div class="request-item">
                                <div class="request-header">
                                    <span class="request-id">#<?php echo esc_html($request->id); ?></span>
                                    <span class="request-date"><?php echo date('d.m.Y', strtotime($request->created_at)); ?></span>
                                    <span class="request-status" style="background-color: <?php echo esc_attr($request->status_color ?: '#0073aa'); ?>; color: white;">
                                        <?php echo esc_html($request->status_title); ?>
                                    </span>
                                </div>
                                <div class="request-details">
                                    <p><strong>Service:</strong> <?php echo esc_html($request->service_title); ?></p>
                                    <p><strong>Preis:</strong> <?php echo ($request->service_cost !== null ? esc_html($request->service_cost) . ' €' : '-'); ?></p>
                                    <p><strong>Modell:</strong> <?php echo esc_html($request->model); ?></p>
                                    <?php if ($request->serial): ?>
                                        <p><strong>Seriennummer:</strong> <?php echo esc_html($request->serial); ?></p>
                                    <?php endif; ?>
                                    <p><strong>Beschreibung:</strong> <?php echo esc_html($request->description); ?></p>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            <?php endif; ?>
        </div>
        <?php
        $content = ob_get_clean();
        if (class_exists('Nexora_Customer')) {
            Nexora_Customer::render_user_dashboard($content);
        } else {
            echo $content;
        }
    }
} 