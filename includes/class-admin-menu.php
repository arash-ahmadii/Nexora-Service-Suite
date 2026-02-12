<?php
class Nexora_Admin_Menu {
    public function __construct() {
        add_action('admin_menu', array($this, 'add_admin_menus'));
        add_action('admin_enqueue_scripts', array($this, 'enqueue_admin_assets'));
        add_action('admin_footer_text', array($this, 'remove_admin_footer_text'));
    }
    
    public function add_admin_menus() {
        $user = wp_get_current_user();
        $is_admin = current_user_can('manage_options');
        $caps = array(
            'dashboard' => 'reparaturdienst_dashboard',
            'services' => 'reparaturdienst_services',
            'devices' => 'reparaturdienst_devices',
            'status' => 'reparaturdienst_status',
            'requests' => 'reparaturdienst_requests',
            'users' => 'reparaturdienst_users',
            'messages' => 'reparaturdienst_messages',
            'settings' => 'reparaturdienst_settings',
        );
        if (!$is_admin) {
            $has_plugin_access = false;
            foreach ($caps as $cap) {
                if (current_user_can($cap)) {
                    $has_plugin_access = true;
                    break;
                }
            }
            if (!$has_plugin_access) {
                add_action('admin_menu', function() {
                    add_menu_page('No Access', 'Nexora Service Suite', 'read', 'Nexora Service Suite-no-access', function() {
                        echo '<div class="wrap"><h2>Access denied</h2><p>You do not have permission to access this area.</p></div>';
                    });
                });
                return;
            }
        }
        add_menu_page(
            'Nexora Service Suite Management',
            'Nexora Service Suite',
            $is_admin ? 'manage_options' : 'read',
            'Nexora Service Suite-main',
            array($this, 'render_dashboard_page'),
            'dashicons-admin-tools',
            30
        );
        if ($is_admin || current_user_can($caps['dashboard'])) {
            add_submenu_page('Nexora Service Suite-main', 'Dashboard', 'Dashboard', $is_admin ? 'manage_options' : $caps['dashboard'], 'Nexora Service Suite-main', array($this, 'render_dashboard_page'));
        }
        if ($is_admin || current_user_can($caps['services'])) {
            add_submenu_page('Nexora Service Suite-main', 'Manage Services', 'Services', $is_admin ? 'manage_options' : $caps['services'], 'Nexora Service Suite-services', array($this, 'render_services_page'));
        }
        if ($is_admin || current_user_can($caps['status'])) {
            add_submenu_page('Nexora Service Suite-main', 'Service Statuses', 'Statuses', $is_admin ? 'manage_options' : $caps['status'], 'Nexora Service Suite-service-status', array($this, 'render_service_status_page'));
        }
        if ($is_admin || current_user_can($caps['requests'])) {
            add_submenu_page('Nexora Service Suite-main', 'Service Requests', 'Requests', $is_admin ? 'manage_options' : $caps['requests'], 'Nexora Service Suite-service-request', array($this, 'render_service_request_page'));
        }
        if ($is_admin || current_user_can($caps['users'])) {
            add_submenu_page('Nexora Service Suite-main', 'Manage Customers', 'Customers', $is_admin ? 'manage_options' : $caps['users'], 'Nexora Service Suite-users', array($this, 'render_users_page'));
        }
        if ($is_admin || current_user_can($caps['users'])) {
            add_submenu_page('Nexora Service Suite-main', 'Benefits Management', 'Benefits', $is_admin ? 'manage_options' : $caps['users'], 'Nexora Service Suite-eltern', array($this, 'render_eltern_page'));
        }
        if ($is_admin || current_user_can($caps['messages'])) {
            add_submenu_page('Nexora Service Suite-main', 'Notifications', 'Notifications', $is_admin ? 'manage_options' : $caps['messages'], 'Nexora Service Suite-admin-notifications', array($this, 'render_admin_notifications_page'));
        }
        if ($is_admin || current_user_can($caps['settings'])) {
            add_submenu_page('Nexora Service Suite-main', 'Email Management', 'Email Management', $is_admin ? 'manage_options' : $caps['settings'], 'Nexora Service Suite-email-management', array($this, 'render_email_management_page'));
        }
        if ($is_admin || current_user_can($caps['settings'])) {
            add_submenu_page('Nexora Service Suite-main', 'Email Templates', 'Email Templates', $is_admin ? 'manage_options' : $caps['settings'], 'Nexora Service Suite-email-templates', array($this, 'render_email_templates_page'));
        }
        if ($is_admin || current_user_can($caps['devices'])) {
            add_submenu_page('Nexora Service Suite-main', 'Asset Manager', 'Assets', $is_admin ? 'manage_options' : $caps['devices'], 'Nexora Service Suite-device-manager', array($this, 'render_device_manager_page'));
        }
        add_submenu_page(
            null,
            'Anfrage hinzufügen/bearbeiten',
            '',
            $is_admin ? 'manage_options' : $caps['requests'],
            'nexora_service_request_form',
            array($this, 'render_service_request_create_page')
        );

        add_submenu_page(
            null,
            'Service-Anfrage Logs',
            '',
            $is_admin ? 'manage_options' : $caps['requests'],
            'Nexora Service Suite-service-request-log',
            array($this, 'render_service_request_log_page')
        );

        add_submenu_page(
            null,
            'Rechnung drucken',
            '',
            $is_admin ? 'manage_options' : $caps['requests'],
            'Nexora Service Suite-invoice-template',
            array($this, 'render_invoice_template_page')
        );

        add_submenu_page(
            null,
            'Neue Rechnung',
            '',
            $is_admin ? 'manage_options' : $caps['requests'],
            'Nexora Service Suite-new-invoice-template',
            array($this, 'render_new_invoice_template_page')
        );
        add_submenu_page(
            'Nexora Service Suite-main',
            'Debug Status Usage',
            'Debug Status',
            'manage_options',
            'Nexora Service Suite-debug-status',
            array($this, 'render_debug_status_page')
        );
        add_submenu_page(
            'Nexora Service Suite-main',
            'System Repair',
            '🔧 System Repair',
            'manage_options',
            'Nexora Service Suite-repair-system',
            array($this, 'render_repair_system_page')
        );
        add_submenu_page(
            'Nexora Service Suite-main',
            'Easy Registration Form',
            '🚀 Easy Form',
            'manage_options',
            'Nexora Service Suite-easy-form',
            array($this, 'render_easy_form_page')
        );
    }

    public function enqueue_admin_assets($hook) {
        if (strpos($hook, 'Nexora Service Suite') !== false) {
            wp_enqueue_style(
                'font-awesome',
                'https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css',
                array(),
                '6.4.2'
            );
            wp_enqueue_style(
                'Nexora Service Suite-admin-css',
                NEXORA_PLUGIN_URL . 'assets/css/admin.css',
                array(),
                filemtime(NEXORA_PLUGIN_DIR . 'assets/css/admin.css')
            );
            
            wp_enqueue_script(
                'Nexora Service Suite-admin-js',
                NEXORA_PLUGIN_URL . 'assets/js/admin.js',
                array('jquery'),
                filemtime(NEXORA_PLUGIN_DIR . 'assets/js/admin.js'),
                true
            );

            wp_localize_script('Nexora Service Suite-admin-js', 'nexora_ajax', array(
                'ajax_url' => admin_url('admin-ajax.php'),
                'nonce' => wp_create_nonce('nexora_nonce')
            ));
        }
        if (strpos($hook, 'Nexora Service Suite-email-management') !== false) {
            wp_enqueue_style(
                'Nexora Service Suite-smtp-settings-css',
                NEXORA_PLUGIN_URL . 'assets/css/smtp-settings.css',
                array(),
                filemtime(NEXORA_PLUGIN_DIR . 'assets/css/smtp-settings.css')
            );
            
            wp_enqueue_script(
                'Nexora Service Suite-smtp-settings-js',
                NEXORA_PLUGIN_URL . 'assets/js/smtp-settings.js',
                array('jquery'),
                filemtime(NEXORA_PLUGIN_DIR . 'assets/js/smtp-settings.js'),
                true
            );
            
            wp_localize_script('Nexora Service Suite-smtp-settings-js', 'nexora_smtp_ajax', array(
                'ajax_url' => admin_url('admin-ajax.php'),
                'nonce' => wp_create_nonce('nexora_smtp_nonce')
            ));
        }
        if ($hook === 'toplevel_page_nexora-main' || $hook === 'nexora_page_nexora-main') {
            wp_enqueue_style(
                'Nexora Service Suite-dashboard-css',
                NEXORA_PLUGIN_URL . 'assets/css/admin-dashboard.css',
                array(),
                filemtime(NEXORA_PLUGIN_DIR . 'assets/css/admin-dashboard.css')
            );
            
            wp_enqueue_script(
                'Nexora Service Suite-dashboard-js',
                NEXORA_PLUGIN_URL . 'assets/js/admin-dashboard.js',
                array('jquery'),
                filemtime(NEXORA_PLUGIN_DIR . 'assets/js/admin-dashboard.js'),
                true
            );
        }
    }
    
    public function render_dashboard_page() {
        require_once NEXORA_PLUGIN_DIR . 'templates/dashboard.php';
    }
    
    public function render_services_page() {
        require_once NEXORA_PLUGIN_DIR . 'templates/services-list.php';
    }
    
    public function render_service_status_page() {
        require_once NEXORA_PLUGIN_DIR . 'templates/service-status-list.php';
    }

    public function render_service_request_page() {
        require_once NEXORA_PLUGIN_DIR . 'templates/service-request-list.php';
    }
    
    public function render_service_request_create_page() {
        wp_enqueue_script(
            'Nexora Service Suite-service-request-form-js',
            NEXORA_PLUGIN_URL . 'assets/js/service-request-form.js',
            array('jquery'),
            filemtime(NEXORA_PLUGIN_DIR . 'assets/js/service-request-form.js'),
            true
        );
        
        wp_localize_script('Nexora Service Suite-service-request-form-js', 'nexora_ajax', array(
            'ajax_url' => admin_url('admin-ajax.php'),
            'nonce' => wp_create_nonce('nexora_nonce')
        ));
        
        require_once NEXORA_PLUGIN_DIR . 'templates/service-request-form.php';
    }
    
    public function render_service_request_log_page() {
        require_once NEXORA_PLUGIN_DIR . 'templates/service-request-log.php';
    }

    public function render_invoice_template_page() {
        require_once NEXORA_PLUGIN_DIR . 'templates/invoice-template.php';
    }
    
    public function render_new_invoice_template_page() {
        require_once NEXORA_PLUGIN_DIR . 'templates/invoice-template.php';
    }
    
    public function render_brands_page() {
        require_once NEXORA_PLUGIN_DIR . 'templates/brands-list.php';
    }

    public function render_invoices_page() {
        require_once NEXORA_PLUGIN_DIR . 'templates/invoice-list.php';
    }

    public function render_users_page() {
        require_once NEXORA_PLUGIN_DIR . 'templates/users-list.php';
    }

    public function render_admin_notifications_page() {
        require_once NEXORA_PLUGIN_DIR . 'templates/admin-notifications.php';
    }

    public function render_settings_page() {
        require_once NEXORA_PLUGIN_DIR . 'templates/settings.php';
    }
    
    public function render_email_management_page() {
        require_once NEXORA_PLUGIN_DIR . 'templates/email-management-new.php';
    }
    

    public function render_device_manager_page() {
        $manager = Nexora_Device_Manager::get_instance();
        $manager->render_admin_page();
    }
    
    public function render_repair_system_page() {
        if (!class_exists('Nexora_Repair_System')) {
            require_once NEXORA_PLUGIN_DIR . 'includes/class-repair-system.php';
        }
        
        $repair_system = new Nexora_Repair_System();
        $repair_system->render_repair_page();
    }
    
    public function render_eltern_page() {
        include_once(plugin_dir_path(__FILE__) . '../templates/eltern.php');
    }
    
    public function render_easy_form_page() {
        ?>
        <div class="wrap">
            <h1>🚀 Easy Registration Form</h1>
            <p>Verwenden Sie diesen Shortcode, um das Easy Registration Formular auf Ihrer Website anzuzeigen:</p>
            
            <div class="notice notice-info">
                <p><strong>Shortcode:</strong> <code>[nexora_easy_form]</code></p>
            </div>
            
            <div class="card">
                <h2>📋 Verwendung</h2>
                <p>Fügen Sie den Shortcode <code>[nexora_easy_form]</code> in eine beliebige Seite oder einen Beitrag ein.</p>
                
                <h3>🎯 Features:</h3>
                <ul>
                    <li>✅ Schnelle Registrierung mit minimalen Informationen</li>
                    <li>✅ Automatic customer number generation</li>
                    <li>✅ Integrated service request flow</li>
                    <li>✅ User-friendly interface</li>
                    <li>✅ Responsive Design</li>
                </ul>
                
                <h3>📱 Benötigte Informationen:</h3>
                <ul>
                    <li>Name & Nachname</li>
                    <li>Telefonnummer</li>
                    <li>E-Mail-Adresse</li>
                    <li>Postleitzahl</li>
                </ul>
            </div>
            
            <div class="card">
                <h2>🔧 Vorschau</h2>
                <p>Hier ist eine Vorschau des Formulars:</p>
                <?php echo do_shortcode('[nexora_easy_form]'); ?>
            </div>
        </div>
        <?php
    }
    
    public function render_debug_status_page() {
        global $wpdb;
        
        echo '<div class="wrap">';
        echo '<h1>Debug Status Usage</h1>';
        $statuses = $wpdb->get_results("SELECT * FROM {$wpdb->prefix}nexora_service_status ORDER BY id");
        echo '<h3>All Statuses:</h3>';
        echo '<table class="wp-list-table widefat fixed striped">';
        echo '<thead><tr><th>ID</th><th>Title</th><th>Is Default</th></tr></thead>';
        echo '<tbody>';
        foreach ($statuses as $status) {
            echo '<tr>';
            echo '<td>' . esc_html($status->id) . '</td>';
            echo '<td>' . esc_html($status->title) . '</td>';
            echo '<td>' . ($status->is_default ? 'Yes' : 'No') . '</td>';
            echo '</tr>';
        }
        echo '</tbody></table>';
        echo '<h3>Status Usage in Service Requests:</h3>';
        $usage = $wpdb->get_results("
            SELECT 
                sr.status_id,
                ss.title as status_title,
                COUNT(*) as usage_count
            FROM {$wpdb->prefix}nexora_service_requests sr
            LEFT JOIN {$wpdb->prefix}nexora_service_status ss ON sr.status_id = ss.id
            GROUP BY sr.status_id
            ORDER BY usage_count DESC
        ");
        
        echo '<table class="wp-list-table widefat fixed striped">';
        echo '<thead><tr><th>Status ID</th><th>Status Title</th><th>Usage Count</th></tr></thead>';
        echo '<tbody>';
        foreach ($usage as $item) {
            echo '<tr>';
            echo '<td>' . esc_html($item->status_id) . '</td>';
            echo '<td>' . esc_html($item->status_title) . '</td>';
            echo '<td>' . esc_html($item->usage_count) . '</td>';
            echo '</tr>';
        }
        echo '</tbody></table>';
        echo '<h3>Sample Service Requests:</h3>';
        $requests = $wpdb->get_results("
            SELECT 
                sr.id,
                sr.serial,
                sr.model,
                ss.title as status_title,
                sr.status_id
            FROM {$wpdb->prefix}nexora_service_requests sr
            LEFT JOIN {$wpdb->prefix}nexora_service_status ss ON sr.status_id = ss.id
            LIMIT 10
        ");
        
        echo '<table class="wp-list-table widefat fixed striped">';
        echo '<thead><tr><th>Request ID</th><th>Serial</th><th>Model</th><th>Status ID</th><th>Status Title</th></tr></thead>';
        echo '<tbody>';
        foreach ($requests as $request) {
            echo '<tr>';
            echo '<td>' . esc_html($request->id) . '</td>';
            echo '<td>' . esc_html($request->serial) . '</td>';
            echo '<td>' . esc_html($request->model) . '</td>';
            echo '<td>' . esc_html($request->status_id) . '</td>';
            echo '<td>' . esc_html($request->status_title) . '</td>';
            echo '</tr>';
        }
        echo '</tbody></table>';
        
        echo '</div>';
    }

    
    public function render_admin_header() {
        $user = wp_get_current_user();
        $is_admin = current_user_can('manage_options');
        $caps = array(
            'dashboard' => 'reparaturdienst_dashboard',
            'services' => 'reparaturdienst_services',
            'devices' => 'reparaturdienst_devices',
            'status' => 'reparaturdienst_status',
            'requests' => 'reparaturdienst_requests',
            'users' => 'reparaturdienst_users',
            'messages' => 'reparaturdienst_messages',
            'settings' => 'reparaturdienst_settings',
        );
        $current_page = isset($_GET['page']) ? $_GET['page'] : '';
        $is_dashboard_active = ($current_page == 'Nexora Service Suite-main');
        $is_services_active = ($current_page == 'Nexora Service Suite-services');
        $is_brands_active = ($current_page == 'Nexora Service Suite-device-manager');
        $is_status_active = ($current_page == 'Nexora Service Suite-service-status');
        $is_requests_active = ($current_page == 'Nexora Service Suite-service-request' || $current_page == 'nexora_service_request_form');
        $is_users_active = ($current_page == 'Nexora Service Suite-users');
        $is_notifications_active = ($current_page == 'Nexora Service Suite-admin-notifications');
        $is_settings_active = ($current_page == 'Nexora Service Suite-settings');
        $is_email_management_active = ($current_page == 'Nexora Service Suite-email-management');
        $is_repair_system_active = ($current_page == 'Nexora Service Suite-repair-system');
        $is_easy_form_active = ($current_page == 'Nexora Service Suite-easy-form');
        ?>
        
        <style>
        .Nexora Service Suite-admin-header { margin-bottom: 32px; }
        </style>
        <?php
    }

    function add_service_status_field_to_user_profile($user) {
        global $wpdb;
        
        $statuses = $wpdb->get_results("SELECT id, title FROM {$wpdb->prefix}nexora_service_status");
        ?>
        <h3><?php _e("Service Status", "Nexora Service Suite"); ?></h3>
        <table class="form-table">
            <tr>
                <th><label for="service_status"><?php _e("Service Status"); ?></label></th>
                <td>
                    <select name="service_status" id="service_status">
                        <option value=""><?php _e("Select Status"); ?></option>
                        <?php
                        foreach ($statuses as $status) {
                            $selected = (get_user_meta($user->ID, 'service_status', true) == $status->id) ? 'selected' : '';
                            echo '<option value="' . esc_attr($status->id) . '" ' . $selected . '>' . esc_html($status->title) . '</option>';
                        }
                        ?>
                    </select>
                </td>
            </tr>
        </table>
        <?php
    }
    
    
    public function render_email_templates_page() {
        $this->render_admin_header();
        include NEXORA_PLUGIN_DIR . 'templates/email-templates.php';
    }
    
    
    public function remove_admin_footer_text() {
        if (isset($_GET['page']) && strpos($_GET['page'], 'Nexora Service Suite') !== false) {
            return '';
        }
        return null;
    }
}