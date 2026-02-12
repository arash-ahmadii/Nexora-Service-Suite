<?php
/**
 * Plugin Name: Nexora Service Suite
 * Plugin URI: https://example.com
 * Description: A professional WordPress plugin for managing service and repair requests. Customers can submit requests and administrators can track, manage and complete them.
 * Version: 1.1
 * Requires at least: 5.0
 * Tested up to: 6.4
 * Requires PHP: 7.4
 * Author: DashWeb
 * Author URI: https://example.com
 * License: GPL-2.0-or-later
 * License URI: https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain: nexora-service-suite
 * Domain Path: /languages
 * Network: false
 */

defined('ABSPATH') or die('No direct access allowed.');

define('NEXORA_PLUGIN_DIR', plugin_dir_path(__FILE__));
define('NEXORA_PLUGIN_URL', plugin_dir_url(__FILE__));
add_action('wp_ajax_nexora_*', 'nexora_ajax_error_handler');
add_action('wp_ajax_nopriv_nexora_*', 'nexora_ajax_error_handler');
add_action('wp_ajax_eltern_filter_requests', 'eltern_filter_requests_ajax');
add_action('wp_ajax_bulk_update_payment_status', 'bulk_update_payment_status_ajax');
add_action('wp_ajax_bulk_delete_requests', 'bulk_delete_requests_ajax');

function nexora_ajax_error_handler() {
    if (wp_doing_ajax()) {
        error_reporting(E_ERROR | E_PARSE | E_COMPILE_ERROR);
        ini_set('display_errors', 0);
    }
}

register_activation_hook(__FILE__, 'nexora_install_tables');
register_deactivation_hook(__FILE__, 'nexora_deactivate');

function nexora_install_tables() {
    global $wpdb;

    
    $charset_collate = $wpdb->get_charset_collate();
    
   $sql = '';
    $service_status_table = $wpdb->prefix . 'nexora_service_status';
    $sql .= "CREATE TABLE $service_status_table (
        id INT NOT NULL AUTO_INCREMENT,
        title VARCHAR(100) NOT NULL,
        color VARCHAR(7) DEFAULT '#0073aa',
        is_default TINYINT(1) DEFAULT 0,
        created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
        updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        PRIMARY KEY (id),
        UNIQUE KEY unique_title (title)
    ) $charset_collate;";
    $brands_table = $wpdb->prefix . 'nexora_brands';
    $sql .= "CREATE TABLE $brands_table (
        id INT NOT NULL AUTO_INCREMENT,
        name VARCHAR(100) NOT NULL,
        parent_id INT DEFAULT NULL,
        PRIMARY KEY (id),
        FOREIGN KEY (parent_id) REFERENCES $brands_table(id) ON DELETE SET NULL
    ) $charset_collate;";
    $services_table = $wpdb->prefix . 'nexora_services';
    $sql .= "CREATE TABLE $services_table (
        id INT NOT NULL AUTO_INCREMENT,
        title VARCHAR(100) NOT NULL,
        description TEXT,
        cost DECIMAL(10,2) DEFAULT 0,
        status ENUM('active','inactive') NOT NULL DEFAULT 'active',
        user_id bigint(20) DEFAULT NULL,
        created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
        updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        PRIMARY KEY (id),
        FOREIGN KEY (user_id) REFERENCES {$wpdb->users}(ID) ON DELETE SET NULL
    ) $charset_collate;";
    $user_status_table = $wpdb->prefix . 'nexora_user_status';
    $sql .= "CREATE TABLE $user_status_table (
        id mediumint(9) NOT NULL AUTO_INCREMENT,
        user_id bigint(20) NOT NULL,
        source_status_id bigint(20) NOT NULL,
        destination_status_id bigint(20) NOT NULL,
        PRIMARY KEY (id)
    ) $charset_collate;";
    $service_requests_table = $wpdb->prefix . 'nexora_service_requests';
    $sql .= "CREATE TABLE $service_requests_table (
        id mediumint(9) NOT NULL AUTO_INCREMENT,
        serial VARCHAR(100) NOT NULL,
        model VARCHAR(100) NOT NULL,
        description TEXT,
        user_id bigint(20) NOT NULL,
        service_id bigint(20) DEFAULT NULL,
        status_id bigint(20) NOT NULL,
        order_id BIGINT(20)  NULL,
        brand_level_1_id INT DEFAULT NULL,
        brand_level_2_id INT DEFAULT NULL,
        brand_level_3_id INT DEFAULT NULL,
        created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
        updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        PRIMARY KEY (id)
    ) $charset_collate;";
    $service_details_table = $wpdb->prefix . 'nexora_service_details';
    $sql .= "CREATE TABLE $service_details_table (
        id BIGINT(20) NOT NULL AUTO_INCREMENT,
        request_id BIGINT(20) NOT NULL,
        service_id BIGINT(20) NOT NULL,
        service_title VARCHAR(255) NOT NULL,
        quantity INT NOT NULL DEFAULT 1,
        note TEXT,
        created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
        updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        PRIMARY KEY (id),
        KEY idx_request_id (request_id),
        KEY idx_service_id (service_id),
        FOREIGN KEY (request_id) REFERENCES $service_requests_table(id) ON DELETE CASCADE
    ) $charset_collate;";
    $invoice_services_table = $wpdb->prefix . 'nexora_faktor_services';
    $sql .= "CREATE TABLE $invoice_services_table (
        id BIGINT(20) NOT NULL AUTO_INCREMENT,
        request_id mediumint(9) NOT NULL,
        service_id BIGINT(20) NOT NULL,
        service_title VARCHAR(255) NOT NULL,
        service_cost DECIMAL(10,2) DEFAULT 0.00,
        quantity INT NOT NULL DEFAULT 1,
        description TEXT,
        created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
        updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        PRIMARY KEY (id),
        KEY idx_request_id (request_id),
        KEY idx_service_id (service_id),
        FOREIGN KEY (request_id) REFERENCES $service_requests_table(id) ON DELETE CASCADE,
        FOREIGN KEY (service_id) REFERENCES {$wpdb->prefix}nexora_services(id) ON DELETE CASCADE
    ) $charset_collate;";
    $request_comments_table = $wpdb->prefix . 'nexora_request_comments';
    $sql .= "CREATE TABLE $request_comments_table (
        id BIGINT(20) NOT NULL AUTO_INCREMENT,
        request_id BIGINT(20) NOT NULL,
        user_id BIGINT(20) NOT NULL,
        comment_text TEXT NOT NULL,
        created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
        PRIMARY KEY (id)
    ) $charset_collate;";
    $user_show_status_table = $wpdb->prefix . 'nexora_user_show_status';
    $sql .= "CREATE TABLE $user_show_status_table (
        id mediumint(9) NOT NULL AUTO_INCREMENT,
        user_id bigint(20) NOT NULL,
        status_id bigint(20) NOT NULL,
        PRIMARY KEY (id)
    ) $charset_collate;";

 $logs_table = $wpdb->prefix . 'nexora_logs';
    $sql .= "CREATE TABLE $logs_table (
        id mediumint(9) NOT NULL AUTO_INCREMENT,
        salt VARCHAR(100) NOT NULL,
        description TEXT,
        creator_user_id bigint(20) NOT NULL,
        created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
        PRIMARY KEY (id)
    ) $charset_collate;";

     $viewed_logs_table = $wpdb->prefix . 'nexora_viewed_logs';
    $sql .= "CREATE TABLE $viewed_logs_table (
        id mediumint(9) NOT NULL AUTO_INCREMENT,
        creator_user_id bigint(20) NOT NULL,
        creator_log_id bigint(20) NOT NULL,
        created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
        PRIMARY KEY (id)
    ) $charset_collate;";
    $customer_info_table = $wpdb->prefix . 'nexora_customer_info';
    $sql .= "CREATE TABLE $customer_info_table (
        id mediumint(9) NOT NULL AUTO_INCREMENT,
        user_id bigint(20) NOT NULL,
        customer_type ENUM('business', 'private') NOT NULL DEFAULT 'business',
        customer_number VARCHAR(50),
        company_name VARCHAR(255),
        company_name_2 VARCHAR(255),
        street VARCHAR(255) NOT NULL,
        address_addition VARCHAR(255),
        postal_code VARCHAR(20) NOT NULL,
        city VARCHAR(100) NOT NULL,
        country VARCHAR(3) NOT NULL DEFAULT 'DE',
        industry VARCHAR(100),
        vat_id VARCHAR(50),
        salutation ENUM('Herr', 'Frau', 'Divers') NOT NULL,
        phone VARCHAR(50) NOT NULL,
        newsletter TINYINT(1) DEFAULT 0,
        created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
        updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        PRIMARY KEY (id),
        UNIQUE KEY user_id (user_id),
        FOREIGN KEY (user_id) REFERENCES {$wpdb->users}(ID) ON DELETE CASCADE
    ) $charset_collate;";
    $admin_notifications_table = $wpdb->prefix . 'nexora_admin_notifications';
    $sql .= "CREATE TABLE $admin_notifications_table (
        id BIGINT(20) NOT NULL AUTO_INCREMENT,
        type VARCHAR(50) NOT NULL,
        message TEXT NOT NULL,
        related_id BIGINT(20) DEFAULT NULL,
        user_id BIGINT(20) DEFAULT NULL,
        status ENUM('unread','read') NOT NULL DEFAULT 'unread',
        created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
        PRIMARY KEY (id)
    ) $charset_collate;";
    $activity_logs_table = $wpdb->prefix . 'nexora_activity_logs';
    $sql .= "CREATE TABLE $activity_logs_table (
        id BIGINT(20) NOT NULL AUTO_INCREMENT,
        request_id BIGINT(20) NOT NULL,
        user_id BIGINT(20) NOT NULL,
        action_type VARCHAR(50) NOT NULL,
        action_description TEXT NOT NULL,
        old_value TEXT DEFAULT NULL,
        new_value TEXT DEFAULT NULL,
        ip_address VARCHAR(45) DEFAULT NULL,
        user_agent TEXT DEFAULT NULL,
        created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
        PRIMARY KEY (id),
        KEY request_id (request_id),
        KEY user_id (user_id),
        KEY action_type (action_type),
        KEY created_at (created_at),
        FOREIGN KEY (request_id) REFERENCES {$wpdb->prefix}nexora_service_requests(id) ON DELETE CASCADE,
        FOREIGN KEY (user_id) REFERENCES {$wpdb->users}(ID) ON DELETE CASCADE
    ) $charset_collate;";

    require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
    dbDelta($sql);
    if (!empty($wpdb->last_error)) {
        error_log('Nexora Service Suite Plugin Installation Error: ' . $wpdb->last_error);
    }
    if (class_exists('Nexora_Repair_System')) {
        $repair = new Nexora_Repair_System();
        if (method_exists($repair, 'create_missing_tables')) {
            $repair->create_missing_tables();
        }
    }
    flush_rewrite_rules();

    $admin_users = get_users([
    'role'   => 'administrator',
    'fields' => 'ID' 
    ]);

    if (!empty($admin_users)) {
        foreach ($admin_users as $user_id) {
            update_user_meta(
                $user_id,
                'nexora_kind_user',
                sanitize_text_field('admin')
            );
        }
    
    } 
}

function nexora_deactivate() {
    flush_rewrite_rules();
    delete_option('nexora_flush_rewrite_rules');
}

register_uninstall_hook(__FILE__, 'nexora_uninstall');

function nexora_uninstall() {
    global $wpdb;
    
    $tables = array(
        $wpdb->prefix . 'nexora_service_status',
        $wpdb->prefix . 'nexora_brands',
        $wpdb->prefix . 'nexora_services',
        $wpdb->prefix . 'nexora_user_status',
        $wpdb->prefix . 'nexora_service_requests',
        $wpdb->prefix . 'nexora_service_details',
        $wpdb->prefix . 'nexora_faktor_services',
        $wpdb->prefix . 'nexora_request_comments',
        $wpdb->prefix . 'nexora_user_show_status',
        $wpdb->prefix . 'nexora_logs',
        $wpdb->prefix . 'nexora_viewed_logs',
        $wpdb->prefix . 'nexora_customer_info',
        $wpdb->prefix . 'nexora_admin_notifications',
        $wpdb->prefix . 'nexora_activity_logs'
    );
    
    foreach ($tables as $table) {
        $wpdb->query("DROP TABLE IF EXISTS $table");
    }

}
require_once NEXORA_PLUGIN_DIR . 'includes/class-error-handler.php';

require_once NEXORA_PLUGIN_DIR . 'includes/class-admin-menu.php';
require_once NEXORA_PLUGIN_DIR . 'includes/class-service-handler.php';
require_once NEXORA_PLUGIN_DIR . 'includes/class-service-status-handler.php';
require_once NEXORA_PLUGIN_DIR . 'includes/class-email-templates-manager.php';
require_once NEXORA_PLUGIN_DIR . 'includes/class-user-profile.php';
require_once NEXORA_PLUGIN_DIR . 'includes/class-service-request.php';
require_once NEXORA_PLUGIN_DIR . 'includes/class-invoice-request.php';
require_once NEXORA_PLUGIN_DIR . 'includes/class-customer.php';
require_once NEXORA_PLUGIN_DIR . 'includes/class-user-registration.php';
require_once NEXORA_PLUGIN_DIR . 'includes/class-user-service-request.php';
require_once NEXORA_PLUGIN_DIR . 'includes/class-admin-notifications.php';
require_once NEXORA_PLUGIN_DIR . 'includes/class-invoice-generator.php';
require_once NEXORA_PLUGIN_DIR . 'includes/class-pdf-invoice-generator.php';
require_once NEXORA_PLUGIN_DIR . 'includes/class-admin-settings.php';
require_once NEXORA_PLUGIN_DIR . 'includes/class-activity-logger.php';
require_once NEXORA_PLUGIN_DIR . 'includes/class-repair-system.php';
require_once NEXORA_PLUGIN_DIR . 'includes/class-customer-number-manager.php';
require_once NEXORA_PLUGIN_DIR . 'includes/class-easy-registration.php';
require_once NEXORA_PLUGIN_DIR . 'includes/device-manager/class-device-manager.php';
require_once NEXORA_PLUGIN_DIR . 'includes/device-manager/DeviceManager_AJAX.php';
require_once NEXORA_PLUGIN_DIR . 'includes/class-chat-manager.php';
require_once NEXORA_PLUGIN_DIR . 'includes/chat/Chat_AJAX.php';
require_once NEXORA_PLUGIN_DIR . 'includes/class-service-approval-manager.php';
require_once NEXORA_PLUGIN_DIR . 'includes/email/class-notification-settings.php';
require_once NEXORA_PLUGIN_DIR . 'includes/email/class-simple-notification-control.php';
        require_once NEXORA_PLUGIN_DIR . 'includes/email/class-independent-email-system.php';
        require_once NEXORA_PLUGIN_DIR . 'includes/email/Independent_Email_AJAX.php';
        require_once NEXORA_PLUGIN_DIR . 'includes/email/class-email-database-manager.php';
        require_once NEXORA_PLUGIN_DIR . 'includes/email/class-email-template-manager.php';
        require_once NEXORA_PLUGIN_DIR . 'includes/email/class-email-template-ajax.php';
        require_once NEXORA_PLUGIN_DIR . 'includes/email/class-automatic-email-notifications.php';
require_once NEXORA_PLUGIN_DIR . 'includes/email/class-email-template-customizer.php';
new Nexora_Activity_Logger();
function nexora_migrate_service_status_colors() {
    global $wpdb;
    $table_name = $wpdb->prefix . 'nexora_service_status';
    $column_exists = $wpdb->get_results("SHOW COLUMNS FROM $table_name LIKE 'color'");
    
    if (empty($column_exists)) {
        $wpdb->query("ALTER TABLE $table_name ADD COLUMN color VARCHAR(7) DEFAULT '#0073aa' AFTER title");
        $wpdb->query("UPDATE $table_name SET color = '#0073aa' WHERE color IS NULL OR color = ''");
        $wpdb->query("UPDATE $table_name SET color = '#28a745' WHERE title LIKE '%fertig%' OR title LIKE '%complete%' OR title LIKE '%done%'");
        $wpdb->query("UPDATE $table_name SET color = '#ffc107' WHERE title LIKE '%bearbeitung%' OR title LIKE '%processing%' OR title LIKE '%in progress%'");
        $wpdb->query("UPDATE $table_name SET color = '#dc3545' WHERE title LIKE '%storniert%' OR title LIKE '%cancelled%' OR title LIKE '%abgebrochen%'");
        $wpdb->query("UPDATE $table_name SET color = '#17a2b8' WHERE title LIKE '%wartend%' OR title LIKE '%waiting%' OR title LIKE '%pending%'");
    }
}

if (is_admin()) {
    new Nexora_Admin_Menu();
    new Nexora_Service_Handler();
    new Nexora_Service_Status_Handler();
    nexora_migrate_service_status_colors();
    
    new Nexora_User_Profile();
    new Nexora_Service_Request();
    new Nexora_Admin_Notifications();
    new Nexora_Admin_Settings();
    new Nexora_Repair_System();
    new Nexora_Notification_Settings();
    new Nexora_Simple_Notification_Control();
            new Nexora_Independent_Email_AJAX();
    new Nexora_Email_Template_Manager();
    new Nexora_Email_Template_AJAX();
    new Nexora_Email_Templates_Manager();
    Nexora_Device_Manager::get_instance();
}
require_once NEXORA_PLUGIN_DIR . 'includes/email/class-independent-email-system.php';
require_once NEXORA_PLUGIN_DIR . 'includes/email/class-automatic-email-notifications.php';
new Nexora_Automatic_Email_Notifications();

new Nexora_Customer();
new Nexora_User_Registration();
new Nexora_User_Service_Request();
new Nexora_Chat_Manager();
new Nexora_Chat_AJAX();
new Nexora_Service_Approval_Manager();
add_action('wp_enqueue_scripts', 'nexora_enqueue_chat_assets');
add_action('admin_enqueue_scripts', 'nexora_enqueue_chat_assets');
add_action('wp_enqueue_scripts', 'nexora_enqueue_chat_assets');

function nexora_enqueue_chat_assets() {
    if (is_account_page() || 
        (is_admin() && isset($_GET['page']) && strpos($_GET['page'], 'Nexora Service Suite') !== false) ||
        (function_exists('is_account_page') && is_account_page()) ||
        (isset($_SERVER['REQUEST_URI']) && strpos($_SERVER['REQUEST_URI'], '/my-account/') !== false)) {
        wp_enqueue_style(
            'Nexora Service Suite-chat-css',
            NEXORA_PLUGIN_URL . 'assets/css/chat-system.css',
            array(),
            filemtime(NEXORA_PLUGIN_DIR . 'assets/css/chat-system.css')
        );
        
        wp_enqueue_script(
            'Nexora Service Suite-chat-js',
            NEXORA_PLUGIN_URL . 'assets/js/chat-system.js',
            array('jquery'),
            filemtime(NEXORA_PLUGIN_DIR . 'assets/js/chat-system.js'),
            true
        );
        
        wp_localize_script('Nexora Service Suite-chat-js', 'nexora_chat_ajax', array(
            'ajax_url' => admin_url('admin-ajax.php'),
            'nonce' => wp_create_nonce('nexora_nonce'),
            'user_nonce' => wp_create_nonce('nexora_user_nonce'),
            'chat_nonce' => wp_create_nonce('nexora_chat_nonce')
        ));
        wp_enqueue_style(
            'Nexora Service Suite-service-approval-css',
            NEXORA_PLUGIN_URL . 'assets/css/service-approval-system.css',
            array(),
            filemtime(NEXORA_PLUGIN_DIR . 'assets/css/service-approval-system.css')
        );
        
        wp_enqueue_script(
            'Nexora Service Suite-service-approval-js',
            NEXORA_PLUGIN_URL . 'assets/js/service-approval-system.js',
            array('jquery'),
            filemtime(NEXORA_PLUGIN_DIR . 'assets/js/service-approval-system.js'),
            true
        );
        
        wp_localize_script('Nexora Service Suite-service-approval-js', 'nexora_ajax', array(
            'ajax_url' => admin_url('admin-ajax.php'),
            'nonce' => wp_create_nonce('nexora_nonce')
        ));
    }
}
new Nexora_Invoice_Generator();
add_action('wp_ajax_nopriv_heartbeat', 'nexora_suppress_ajax_errors', 1);
add_action('wp_ajax_heartbeat', 'nexora_suppress_ajax_errors', 1);

function nexora_suppress_ajax_errors() {
    if (defined('DOING_AJAX') && DOING_AJAX && isset($_POST['action']) && strpos($_POST['action'], 'Nexora Service Suite') !== false) {
        error_reporting(0);
        ini_set('display_errors', 0);
    }
}

add_action('wp_ajax_nexora_delete_user', function() {
    if (!current_user_can('delete_users')) {
        wp_send_json_error('Unbefugter Zugriff');
    }
    $user_id = intval($_POST['user_id']);
    if ($user_id && $user_id != get_current_user_id()) {
        require_once(ABSPATH.'wp-admin/includes/user.php');
        $result = wp_delete_user($user_id);
        if ($result) {
            wp_send_json_success();
        } else {
            wp_send_json_error('Fehler beim Löschen des Benutzers');
        }
    } else {
        wp_send_json_error('Ungültige ID oder Selbstlöschung nicht erlaubt');
    }
});

add_action('wp_ajax_nexora_add_user', function() {
    if (!current_user_can('create_users')) {
        wp_send_json_error('Unbefugter Zugriff');
    }
    parse_str($_POST['data'], $data);
    if (empty($data['user_login']) || empty($data['user_email']) || empty($data['user_pass'])) {
        wp_send_json_error('Alle Felder sind erforderlich');
    }
    if (username_exists($data['user_login']) || email_exists($data['user_email'])) {
        wp_send_json_error('Benutzername oder E-Mail bereits vorhanden');
    }
    $user_id = wp_create_user($data['user_login'], $data['user_pass'], $data['user_email']);
    if (is_wp_error($user_id)) {
        wp_send_json_error($user_id->get_error_message());
    }
    if (!empty($data['role'])) {
        $user = new WP_User($user_id);
        $user->set_role($data['role']);
    }
    wp_send_json_success();
});

add_action('wp_ajax_nexora_add_role', function() {
    if (!current_user_can('manage_options')) {
        wp_send_json_error('Unbefugter Zugriff');
    }
    parse_str($_POST['data'], $data);
    $role_name = sanitize_key($data['role_name'] ?? '');
    $role_label = sanitize_text_field($data['role_label'] ?? '');
    $capabilities = isset($data['capabilities']) ? (array)$data['capabilities'] : [];
    if (!$role_name || !$role_label) {
        wp_send_json_error('Rollenname und Anzeigename sind erforderlich');
    }
    if (get_role($role_name)) {
        wp_send_json_error('Diese Rolle existiert bereits');
    }
    $caps = [];
    foreach ($capabilities as $cap) {
        $caps[sanitize_key($cap)] = true;
    }
    $result = add_role($role_name, $role_label, $caps);
    if ($result) {
        wp_send_json_success();
    } else {
        wp_send_json_error('Fehler beim Hinzufügen der Rolle');
    }
});

add_action('wp_ajax_nexora_approve_user', function() {
    if (!current_user_can('edit_users')) {
        wp_send_json_error('Unbefugter Zugriff');
    }
    $user_id = intval($_POST['user_id']);
    if ($user_id) {
        update_user_meta($user_id, 'user_approved', 'yes');
        wp_send_json_success();
    } else {
        wp_send_json_error('Benutzer-ID ist ungültig');
    }
});

add_action('wp_ajax_nexora_reject_user', function() {
    if (!current_user_can('edit_users')) {
        wp_send_json_error('Unbefugter Zugriff');
    }
    $user_id = intval($_POST['user_id']);
    if ($user_id) {
        update_user_meta($user_id, 'user_approved', 'no');
        wp_send_json_success();
    } else {
        wp_send_json_error('Benutzer-ID ist ungültig');
    }
});
function nexora_add_endpoints() {
}
add_action('init', 'nexora_add_endpoints', 10);
function nexora_add_account_menu_items($items) {
    return $items;
}
add_filter('woocommerce_account_menu_items', 'nexora_add_account_menu_items');
function nexora_service_requests_endpoint() {
}
add_action('woocommerce_account_service-requests_endpoint', 'nexora_service_requests_endpoint');
function nexora_service_request_endpoint() {
}
add_action('woocommerce_account_service-request_endpoint', 'nexora_service_request_endpoint');
register_activation_hook(__FILE__, function() {
});
add_action('init', function() {
}, 20);
register_deactivation_hook(__FILE__, function() {
});
function nexora_add_my_requests_endpoint() {
    add_rewrite_endpoint('my-requests', EP_ROOT | EP_PAGES);
}
add_action('init', 'nexora_add_my_requests_endpoint');
function nexora_add_financial_accounts_endpoint() {
    add_rewrite_endpoint('financial-accounts', EP_ROOT | EP_PAGES);
}
add_action('init', 'nexora_add_financial_accounts_endpoint');
register_activation_hook(__FILE__, function() {
    nexora_add_my_requests_endpoint();
    nexora_add_financial_accounts_endpoint();
    flush_rewrite_rules();
});
add_action('init', function() {
    if (get_option('nexora_flush_rewrite_rules')) {
        flush_rewrite_rules();
        delete_option('nexora_flush_rewrite_rules');
    }
});
function nexora_is_commission_user() {
    if (!is_user_logged_in()) {
        return false;
    }
    
    $user_id = get_current_user_id();
    $benefit_type = get_user_meta($user_id, 'benefit_type', true);
    
    return $benefit_type === 'commission';
}
function nexora_add_my_requests_menu_item($items) {
    $new_items = [];
    foreach ($items as $key => $label) {
        if ($key === 'customer-logout') {
            $new_items['my-requests'] = 'Serviceanfragen';
            if (nexora_is_commission_user()) {
                $new_items['financial-accounts'] = 'Finanzkonten';
            }
        }
        $new_items[$key] = $label;
    }
    return $new_items;
}
add_filter('woocommerce_account_menu_items', 'nexora_add_my_requests_menu_item');
function nexora_my_requests_endpoint_content() {
    if (!is_user_logged_in()) {
        echo '<p>Bitte melden Sie sich in Ihrem Benutzerkonto an.</p>';
        return;
    }
    $user_id = get_current_user_id();
    global $wpdb;
    $requests = $wpdb->get_results(
        $wpdb->prepare(
            "SELECT sr.id, sr.model, sr.serial, sr.description, sr.service_description, sr.service_quantity, sr.created_at, sr.status_id,
                    COALESCE(ss.title, 'Neu') as status_title,
                    COALESCE(ss.color, '#0073aa') as status_color,
                    d1.name as brand_1_name, d2.name as brand_2_name, d3.name as brand_3_name,
                    s.title as service_title,
                    s.cost as service_cost
             FROM {$wpdb->prefix}nexora_service_requests sr
             LEFT JOIN {$wpdb->prefix}nexora_service_status ss ON ss.id = sr.status_id
             LEFT JOIN {$wpdb->prefix}nexora_devices d1 ON sr.brand_level_1_id = d1.id AND d1.type = 'type'
             LEFT JOIN {$wpdb->prefix}nexora_devices d2 ON sr.brand_level_2_id = d2.id AND d2.type = 'brand'
             LEFT JOIN {$wpdb->prefix}nexora_devices d3 ON sr.brand_level_3_id = d3.id AND d3.type = 'series'
             LEFT JOIN {$wpdb->prefix}nexora_services s ON sr.service_id = s.id
             WHERE sr.user_id = %d
             ORDER BY sr.id DESC",
            $user_id
        )
    );
    ?>
    <div class="Nexora Service Suite-requests-wrapper">
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 30px;">
            <h3>Meine Serviceanfragen</h3>
            <button id="add-new-request-btn" class="button button-primary" style="font-size: 16px; padding: 8px 16px; background: transparent; border: 1px solid #e1e5e9; color: #6f42c1; cursor: pointer; transition: all 0.3s ease; font-weight: 500; text-decoration: none; border-radius: 6px;" title="Neue Serviceanfrage erstellen" onmouseover="this.style.borderColor='#6f42c1'; this.style.backgroundColor='rgba(111, 66, 193, 0.05)'" onmouseout="this.style.borderColor='#e1e5e9'; this.style.backgroundColor='transparent'">
                Neue Serviceanfrage
            </button>
        </div>
        
        
        <div id="new-request-dropdown" style="display: none; margin-bottom: 30px; border: 1px solid #e1e5e9; border-radius: 8px; padding: 20px; background: transparent;">
            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px;">
                <h4 style="margin: 0; color: #333;">Neue Serviceanfrage</h4>
                <button id="close-dropdown-btn" class="button" style="background: #6c757d; color: white; border: none; padding: 5px 10px; border-radius: 4px; cursor: pointer;">✕</button>
            </div>
            
            <?php
            $user_id = get_current_user_id();
            $error = '';
            $success = '';
            if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['nexora_new_service_request_nonce']) && wp_verify_nonce($_POST['nexora_new_service_request_nonce'], 'nexora_new_service_request')) {
                $device_type_id = isset($_POST['device_type_id']) && $_POST['device_type_id'] !== 'custom' ? intval($_POST['device_type_id']) : null;
                $device_type_custom = isset($_POST['device_type_custom']) ? trim($_POST['device_type_custom']) : '';
                $device_brand_id = isset($_POST['device_brand_id']) && $_POST['device_brand_id'] !== 'custom' ? intval($_POST['device_brand_id']) : null;
                $device_brand_custom = isset($_POST['device_brand_custom']) ? trim($_POST['device_brand_custom']) : '';
                $device_series_id = isset($_POST['device_series_id']) && $_POST['device_series_id'] !== 'custom' ? intval($_POST['device_series_id']) : null;
                $device_series_custom = isset($_POST['device_series_custom']) ? trim($_POST['device_series_custom']) : '';
                $device_model_id = isset($_POST['device_model_id']) && $_POST['device_model_id'] !== 'custom' ? intval($_POST['device_model_id']) : null;
                $device_model_custom = isset($_POST['device_model_custom']) ? trim($_POST['device_model_custom']) : '';
                $serial = sanitize_text_field($_POST['serial'] ?? '');
                $description = sanitize_textarea_field($_POST['description'] ?? '');
                $model = '';
                if ($device_model_id && $device_model_id !== 'custom') {
                    global $wpdb;
                    $model = $wpdb->get_var($wpdb->prepare("SELECT name FROM {$wpdb->prefix}nexora_devices WHERE id = %d AND type = 'model'", $device_model_id));
                } elseif ($device_model_custom) {
                    $model = $device_model_custom;
                }
                error_log('Debug Model Values - device_model_id: ' . $device_model_id . ', device_model_custom: ' . $device_model_custom . ', final_model: ' . $model);
                $brand_level_1_id = $device_type_id;
                $brand_level_2_id = $device_brand_id;
                $brand_level_3_id = $device_series_id;
                $custom_info = [];
                if ($device_type_id === null && $device_type_custom) $custom_info[] = 'Gerätetyp: ' . $device_type_custom;
                if ($device_brand_id === null && $device_brand_custom) $custom_info[] = 'Marke: ' . $device_brand_custom;
                if ($device_series_id === null && $device_series_custom) $custom_info[] = 'Serie: ' . $device_series_custom;
                if ($device_model_id === null && $device_model_custom) $custom_info[] = 'Modell: ' . $device_model_custom;
                if (!empty($custom_info)) $description = implode(' | ', $custom_info) . "\n" . $description;
                $status_id = $wpdb->get_var("SELECT id FROM {$wpdb->prefix}nexora_service_status WHERE is_default = 1 LIMIT 1");
                if (!$status_id) $status_id = 1;
                if (empty($serial)) $error = 'Bitte geben Sie die Seriennummer ein.';
                elseif (empty($model)) $error = 'Bitte geben Sie das Modell ein oder wählen Sie es aus der Liste aus.';
                elseif (empty($description)) $error = 'Bitte geben Sie eine Beschreibung ein.';
                elseif (!$user_id) $error = 'Benutzer nicht erkannt.';
                elseif (!$status_id) $error = 'Status nicht gefunden.';
                else $error = '';

                if (!$error) {
                    $data = [
                        'serial' => $serial,
                        'model' => $model,
                        'description' => $description,
                        'user_id' => $user_id,
                        'status_id' => $status_id,
                        'brand_level_1_id' => $brand_level_1_id,
                        'brand_level_2_id' => $brand_level_2_id,
                        'brand_level_3_id' => $brand_level_3_id,
                        'created_at' => current_time('mysql'),
                        'updated_at' => current_time('mysql')
                    ];
                    
                    $res = $wpdb->insert($wpdb->prefix.'nexora_service_requests', $data);
                    if ($res) {
                        $request_id = $wpdb->insert_id;
                        do_action('nexora_service_request_created', $request_id, $user_id);
                        error_log('New service request hook triggered: nexora_service_request_created(' . $request_id . ', ' . $user_id . ')');
                                        if (isset($_FILES['attachments']) && !empty($_FILES['attachments']['name'][0])) {
                    $upload_dir = wp_upload_dir();
                    $nexora_dir = $upload_dir['basedir'] . '/Nexora Service Suite-attachments';
                    if (!file_exists($nexora_dir)) {
                        wp_mkdir_p($nexora_dir);
                    }
                    
                    $attachments_table = $wpdb->prefix . 'nexora_request_attachments';
                    
                    foreach ($_FILES['attachments']['tmp_name'] as $key => $tmp_name) {
                        if ($_FILES['attachments']['error'][$key] === UPLOAD_ERR_OK) {
                            $file_name = $_FILES['attachments']['name'][$key];
                            $file_size = $_FILES['attachments']['size'][$key];
                            $file_type = $_FILES['attachments']['type'][$key];
                                    $allowed_types = array('image/jpeg', 'image/jpg', 'image/png', 'image/gif');
                                    if (!in_array($file_type, $allowed_types)) {
                                        continue;
                                    }
                                    $unique_filename = time() . '_' . sanitize_file_name($file_name);
                                    $file_path = $nexora_dir . '/' . $unique_filename;
                                    if (move_uploaded_file($tmp_name, $file_path)) {
                                        $wpdb->insert(
                                            $attachments_table,
                                            array(
                                                'request_id' => $request_id,
                                                'file_name' => $file_name,
                                                'file_path' => $file_path,
                                                'file_size' => $file_size,
                                                'file_type' => $file_type,
                                                'uploaded_by' => $user_id,
                                                'created_at' => current_time('mysql')
                                            ),
                                            array('%d', '%s', '%d', '%s', '%d', '%s')
                                        );
                                    }
                                }
                            }
                        }
                        
                        $success = 'Serviceanfrage erfolgreich gespeichert!';
                    } else {
                        $error = 'Fehler beim Speichern der Anfrage.';
                    }
                }
            }
                $services = $wpdb->get_results("SELECT id, title FROM {$wpdb->prefix}nexora_services WHERE status = 'active'");
                $device_types = $wpdb->get_results("SELECT id, name FROM {$wpdb->prefix}nexora_devices WHERE type = 'type' ORDER BY name");
            
            if ($error) echo '<div class="woocommerce-error">'.esc_html($error).'</div>';
            if ($success) {
                echo '<script>
                    const redirectUrl = "' . home_url('/my-account/my-requests/') . '";
                    if (!document.querySelector(".popup-overlay")) {
                        setTimeout(function() {
                            const popup = document.createElement("div");
                            popup.style.cssText = "position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.5); z-index: 9999; display: flex; align-items: center; justify-content: center;";
                            
                            const popupContent = document.createElement("div");
                            popupContent.style.cssText = "background: white; padding: 30px; border-radius: 10px; max-width: 500px; text-align: center; box-shadow: 0 10px 30px rgba(0,0,0,0.3); transform: scale(0.7); opacity: 0; transition: all 0.3s ease;";
                            
                            popupContent.innerHTML = `
                                <div style="font-size: 24px; color: #28a745; margin-bottom: 20px;">✅</div>
                                <h3 style="color: #333; margin-bottom: 20px; font-size: 18px;">Erfolgreich übermittelt!</h3>
                                <p style="color: #666; line-height: 1.6; margin-bottom: 25px;">Ihre Anfrage wurde erfolgreich übermittelt. Unsere Mitarbeitenden werden sich nach Prüfung Ihrer Angaben innerhalb von 24 Stunden per E-Mail oder telefonisch mit Ihnen in Verbindung setzen und den weiteren Ablauf koordinieren.</p>
                                <button onclick="this.closest(\'.popup-overlay\').remove(); window.location.href=redirectUrl;" style="background: #0073aa; color: white; border: none; padding: 12px 24px; border-radius: 6px; font-size: 16px; cursor: pointer; transition: background 0.3s ease;">Verstanden</button>
                            `;
                            
                            popup.className = "popup-overlay";
                            popup.appendChild(popupContent);
                            document.body.appendChild(popup);
                            setTimeout(() => {
                                popupContent.style.transform = "scale(1)";
                                popupContent.style.opacity = "1";
                            }, 10);
                            popup.addEventListener("click", function(e) {
                                if (e.target === popup) {
                                    popup.remove();
                                    window.location.href = redirectUrl;
                                }
                            });
                            document.addEventListener("keydown", function(e) {
                                if (e.key === "Escape" && document.querySelector(".popup-overlay")) {
                                    document.querySelector(".popup-overlay").remove();
                                    window.location.href = redirectUrl;
                                }
                            });
                            function resetFormAndCloseDropdown() {
                                const form = document.querySelector("#new-request-dropdown form");
                                if (form) {
                                    form.reset();
                                }
                                const customInputs = ["#device_type_custom", "#device_brand_custom", "#device_series_custom", "#device_model_custom"];
                                customInputs.forEach(selector => {
                                    const input = document.querySelector(selector);
                                    if (input) {
                                        input.style.display = "none";
                                        input.value = "";
                                    }
                                });
                                const dropdowns = ["#device_type_id", "#device_brand_id", "#device_series_id", "#device_model_id"];
                                dropdowns.forEach(selector => {
                                    const dropdown = document.querySelector(selector);
                                    if (dropdown) {
                                        dropdown.selectedIndex = 0;
                                        if (selector !== "#device_type_id") {
                                            dropdown.disabled = true;
                                        }
                                    }
                                });
                                const fileInput = document.querySelector("#attachments");
                                if (fileInput) {
                                    fileInput.value = "";
                                }
                                const preview = document.querySelector("#attachments-preview");
                                if (preview) {
                                    preview.innerHTML = "";
                                }
                                document.getElementById("new-request-dropdown").style.display = "none";
                                if (document.getElementById("add-new-request-btn")) {
                                    document.getElementById("add-new-request-btn").style.display = "inline-block";
                                }
                                if (typeof loadDeviceTypes === "function") {
                                    loadDeviceTypes();
                                }
                                reattachButtonEventListeners();
                            }
                            function reattachButtonEventListeners() {
                                const addBtn = document.getElementById("add-new-request-btn");
                                const dropdown = document.getElementById("new-request-dropdown");
                                
                                if (addBtn && dropdown) {
                                    const newAddBtn = addBtn.cloneNode(true);
                                    addBtn.parentNode.replaceChild(newAddBtn, addBtn);
                                    newAddBtn.addEventListener("click", function() {
                                        dropdown.style.display = "block";
                                        newAddBtn.style.display = "none";
                                        if (typeof loadDeviceTypes === "function") {
                                            loadDeviceTypes();
                                        }
                                    });
                                    
                                    console.log("Button event listeners re-attached successfully");
                                }
                            }
                        }, 500);
                    }
                </script>';
            } else {
                echo '<form method="post" enctype="multipart/form-data" class="woocommerce-EditAccountForm edit-account">';
                wp_nonce_field('nexora_new_service_request', 'nexora_new_service_request_nonce');
                echo '<div class="form-row form-row-wide"><label for="device_type_id">Gerätetyp <span class="required">*</span></label><select name="device_type_id" id="device_type_id" required><option value="">Bitte wählen</option></select><input type="text" name="device_type_custom" id="device_type_custom" placeholder="Eigener Gerätetyp" style="display:none;margin-top:8px;width:100%"></div>';
                echo '<div class="form-row form-row-wide"><label for="device_brand_id">Marke <span class="required">*</span></label><select name="device_brand_id" id="device_brand_id" required disabled><option value="">Bitte wählen Sie zuerst einen Gerätetyp</option></select><input type="text" name="device_brand_custom" id="device_brand_custom" placeholder="Eigene Marke" style="display:none;margin-top:8px;width:100%"></div>';
                echo '<div class="form-row form-row-wide"><label for="device_series_id">Serie <span class="required">*</span></label><select name="device_series_id" id="device_series_id" required disabled><option value="">Bitte wählen Sie zuerst eine Marke</option></select><input type="text" name="device_series_custom" id="device_series_custom" placeholder="Eigene Serie" style="display:none;margin-top:8px;width:100%"></div>';
                echo '<div class="form-row form-row-wide"><label for="device_model_id">Modell <span class="required">*</span></label><select name="device_model_id" id="device_model_id" required disabled><option value="">Bitte wählen Sie zuerst eine Serie</option></select><input type="text" name="device_model_custom" id="device_model_custom" placeholder="Eigenes Modell" style="display:none;margin-top:8px;width:100%"></div>';
                echo '<div class="form-row form-row-wide">'
                    .'<label for="attachments">Bilder anhängen (max. 5)</label>'
                    .'<input type="file" name="attachments[]" id="attachments" accept="image/*" multiple style="display:block;margin-bottom:8px;" />'
                    .'<div id="attachments-preview" style="display:flex;gap:8px;flex-wrap:wrap;"></div>'
                .'</div>';
                echo '<p class="form-row form-row-wide"><label for="serial">Seriennummer</label><input type="text" name="serial" id="serial" value="'.esc_attr($_POST['serial'] ?? '').'" /></p>';
                echo '<p class="form-row form-row-wide"><label for="description">Beschreibung <span class="required">*</span></label><textarea name="description" id="description" required>'.esc_textarea($_POST['description'] ?? '').'</textarea></p>';
                echo '<p><button type="submit" class="button">Absenden</button></p>';
                echo '</form>';
            }
            ?>
        </div>
        
        <?php wp_nonce_field('nexora_nonce', 'nexora_nonce'); ?>
        
        
        <script>if(typeof window.ajaxurl === "undefined"){window.ajaxurl = "<?php echo admin_url('admin-ajax.php'); ?>";}</script>
        
        <style>
            .Nexora Service Suite-requests-wrapper {
                max-width: 1200px;
                margin: 0 auto;
                font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
            }
            
            .Nexora Service Suite-requests-wrapper h3 {
                color: #333;
                margin-bottom: 30px;
                font-size: 1.12rem;
                font-weight: 600;
            }
            
            .request-item {
                border: 1px solid #e1e5e9;
                border-radius: 8px;
                margin-bottom: 16px;
                box-shadow: 0 2px 4px rgba(0,0,0,0.05);
                transition: all 0.3s ease;
                overflow: hidden;
                background: transparent !important;
            }
            
            .request-item:hover {
                box-shadow: 0 4px 12px rgba(0,0,0,0.1);
                transform: translateY(-2px);
            }
            
            .request-header {
                padding: 20px;
                color: #333;
                cursor: pointer;
                display: flex;
                justify-content: space-between;
                align-items: center;
                transition: background 0.3s ease;
                background: transparent !important;
            }
            
            .request-header:hover {
                background: transparent !important;
            }
            
            .request-header-left {
                display: flex;
                align-items: center;
                gap: 16px;
            }
            
            .request-id {
                background: rgba(0,0,0,0.04);
                padding: 6px 12px;
                border-radius: 20px;
                font-weight: 600;
                font-size: 10px;
            }
            
            .request-model {
                font-size: 12px;
                font-weight: 600;
            }
            
            .request-date {
                font-size: 10px;
                opacity: 0.9;
            }
            
            .request-status {
                padding: 6px 16px;
                border-radius: 20px;
                font-size: 9px;
                font-weight: 600;
                text-transform: uppercase;
                letter-spacing: 0.5px;
                white-space: nowrap;
                min-width: 80px;
                text-align: center;
                box-shadow: 0 2px 4px rgba(0,0,0,0.15);
                text-shadow: 0 1px 2px rgba(0,0,0,0.2);
            }
            

            
            .request-details {
                padding: 0;
                max-height: 0;
                overflow: hidden;
                transition: all 0.6s cubic-bezier(0.4, 0, 0.2, 1);
                background: transparent !important;
                opacity: 0;
            }
            
            .request-details.active {
                padding: 24px;
                max-height: none;
                overflow: visible;
                opacity: 1;
                background: transparent !important;
            }
            
            .details-grid {
                display: grid;
                grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
                gap: 24px;
            }
            
            .detail-section {
                padding: 20px;
                border-radius: 8px;
                border: 1px solid #e1e5e9;
                background: transparent !important;
            }
            
            .detail-section h4 {
                margin: 0 0 16px 0;
                color: #333;
                font-size: 11px;
                font-weight: 600;
                border-bottom: 2px solid #667eea;
                padding-bottom: 8px;
            }
            
            .detail-row {
                display: flex;
                justify-content: space-between;
                margin-bottom: 12px;
                padding: 8px 0;
                border-bottom: 1px solid #f0f0f0;
            }
            
            .detail-row:last-child {
                border-bottom: none;
                margin-bottom: 0;
            }
            
            .detail-label {
                font-weight: 600;
                color: #666;
                min-width: 0;
                font-size: 9px;
            }
            
            .detail-value {
                color: #333;
                text-align: right;
                flex: 1;
                font-size: 9px;
            }
            
            .invoice-section {
                margin-top: 20px;
                padding-top: 20px;
                border-top: 1px solid #e1e5e9;
                background: transparent !important;
            }
            
            .invoice-item {
                display: flex;
                align-items: center;
                gap: 12px;
                padding: 12px;
                background: transparent !important;
                border-radius: 6px;
                margin-bottom: 8px;
            }
            
            .invoice-download-link {
                color: #667eea;
                text-decoration: none;
                font-weight: 500;
                display: flex;
                align-items: center;
                gap: 8px;
                padding: 8px 16px;
                border: 1px solid #667eea;
                border-radius: 6px;
                transition: all 0.3s ease;
                background: transparent !important;
            }
            
            .invoice-download-link:hover {
                background: #667eea;
                color: white;
                text-decoration: none;
            }
            
            .no-requests {
                text-align: center;
                padding: 60px 20px;
                color: #666;
            }
            
            .no-requests h3 {
                margin-bottom: 16px;
                color: #333;
                font-size: 1.12rem;
            }
            
            .no-requests, .no-requests p, .no-requests .button {
                font-size: 11px;
            }
            
            .expand-icon {
                transition: transform 0.3s ease;
                font-size: 14px;
            }
            
            .request-header.active .expand-icon {
                transform: rotate(180deg);
            }
            
            @media (max-width: 768px) {
                .request-header {
                    flex-direction: column;
                    gap: 12px;
                    align-items: flex-start;
                }
                
                .request-header-left {
                    width: 100%;
                    justify-content: space-between;
                }
                
                .details-grid {
                    grid-template-columns: 1fr;
                }
                
                .detail-row {
                    flex-direction: column;
                    gap: 4px;
                }
                
                .detail-value {
                    text-align: left;
                }
            }
            
            .services-table {
                margin-top: 8px;
            }
            
            .services-table table {
                width: 100%;
                border-collapse: collapse;
                font-size: 9px;
            }
            
            .services-table th {
                padding: 8px 4px;
                text-align: left;
                font-weight: 600;
                color: #666;
                border-bottom: 1px solid #e1e5e9;
            }
            
            .services-table-header {
                font-size: 10px;
                font-weight: 700;
                color: #333;
                text-transform: uppercase;
                letter-spacing: 0.5px;
            }
            
            .services-table td {
                padding: 6px 4px;
                border-bottom: 1px solid #f0f0f0;
            }
            
            .services-table th:nth-child(3),
            .services-table th:nth-child(4),
            .services-table th:nth-child(5),
            .services-table td:nth-child(3),
            .services-table td:nth-child(4),
            .services-table td:nth-child(5) {
                text-align: right;
            }
            
            .services-table tfoot tr {
                background: transparent;
                font-weight: 600;
            }
            
            .services-table tfoot td {
                border-bottom: none;
                padding-top: 8px;
            }
            
            .cancel-request-btn {
                background: #dc3545;
                color: white;
                border: none;
                padding: 6px 12px;
                border-radius: 4px;
                font-size: 10px;
                font-weight: 600;
                cursor: pointer;
                transition: all 0.3s ease;
            }
            
            .cancel-request-btn:hover {
                background: #c82333;
                transform: translateY(-1px);
            }
            
            .cancel-request-btn:disabled {
                background: #6c757d;
                cursor: not-allowed;
                transform: none;
            }
        </style>
        
        <?php if (!empty($requests)): ?>
            <?php foreach ($requests as $request): ?>
                <?php
                $invoices = $wpdb->get_results($wpdb->prepare(
                    "SELECT * FROM {$wpdb->prefix}nexora_request_invoices WHERE request_id = %d ORDER BY created_at DESC",
                    $request->id
                ));
                

                ?>
                <div class="request-item">
                    <div class="request-header" onclick="toggleRequestDetails(this)">
                        <div class="request-header-left">
                            <span class="request-id">#<?php echo esc_html($request->id); ?></span>
                            <div>
                                <div class="request-model"><?php echo esc_html($request->model); ?></div>
                                <div class="request-date"><?php echo date('d.m.Y H:i', strtotime($request->created_at)); ?></div>
                            </div>
                        </div>
                        <div style="display: flex; align-items: center; gap: 16px;">
                            <span class="request-status" style="background-color: <?php echo esc_attr($request->status_color); ?>; color: white;">
                                <?php echo esc_html($request->status_title); ?>
                            </span>
                            <?php if ($request->status_title !== 'Abgelehnt' && $request->status_title !== 'Abgeschlossen'): ?>
                                <button class="cancel-request-btn" onclick="cancelServiceRequest(<?php echo $request->id; ?>)">
                                    ❌ Stornieren
                                </button>
                            <?php endif; ?>
                            <span class="expand-icon">▼</span>
                        </div>
                    </div>
                    
                    <div class="request-details">
                        <div class="details-grid">
                            <div class="detail-section">
                                <h4>📋 Anfrage-Details</h4>
                                <div class="detail-row">
                                    <span class="detail-label">Anfrage-ID:</span>
                                    <span class="detail-value">#<?php echo esc_html($request->id); ?></span>
                                </div>
                                <div class="detail-row">
                                    <span class="detail-label">Modell:</span>
                                    <span class="detail-value"><?php echo esc_html($request->model); ?></span>
                                </div>
                                <div class="detail-row">
                                    <span class="detail-label">Seriennummer:</span>
                                    <span class="detail-value"><?php echo esc_html($request->serial); ?></span>
                                </div>
                                <div class="detail-row">
                                    <span class="detail-label">Erstellt am:</span>
                                    <span class="detail-value"><?php echo date('d.m.Y H:i', strtotime($request->created_at)); ?></span>
                                </div>
                                <div class="detail-row">
                                    <span class="detail-label">Status:</span>
                                    <span class="detail-value">
                                        <span class="request-status" style="background-color: <?php echo esc_attr($request->status_color); ?>; color: white;">
                                            <?php echo esc_html($request->status_title); ?>
                                        </span>
                                    </span>
                                </div>
                            </div>
                            
                            <div class="detail-section">
                                <h4>🔧 Geräte-Informationen</h4>
                                <?php
                                $custom_info = [];
                                if (!empty($request->description)) {
                                    $lines = explode("\n", $request->description);
                                    foreach ($lines as $line) {
                                        $line = trim($line);
                                        if (preg_match('/^(Gerätetyp|Marke|Serie|Modell):\s*(.+)$/', $line, $matches)) {
                                            $custom_info[strtolower($matches[1])] = trim($matches[2]);
                                        }
                                    }
                                }
                                if (empty($custom_info) && !empty($request->description)) {
                                    $lines = explode("\n", $request->description);
                                    foreach ($lines as $line) {
                                        $line = trim($line);
                                        if (strpos($line, '|') !== false) {
                                            $parts = explode('|', $line);
                                            foreach ($parts as $part) {
                                                $part = trim($part);
                                                if (preg_match('/^(Gerätetyp|Marke|Serie|Modell):\s*(.+)$/', $part, $matches)) {
                                                    $custom_info[strtolower($matches[1])] = trim($matches[2]);
                                                }
                                            }
                                        }
                                    }
                                }
                                ?>
                                <div class="detail-row">
                                    <span class="detail-label">Gerätetyp:</span>
                                    <span class="detail-value">
                                        <?php 
                                        if (!empty($request->brand_1_name)) {
                                            echo esc_html($request->brand_1_name);
                                        } elseif (!empty($custom_info['gerätetyp'])) {
                                            echo esc_html($custom_info['gerätetyp']) . ' <small style="color: #e74c3c;">(Benutzerdefiniert)</small>';
                                        } else {
                                            echo '-';
                                        }
                                        ?>
                                    </span>
                                </div>
                                <div class="detail-row">
                                    <span class="detail-label">Marke:</span>
                                    <span class="detail-value">
                                        <?php 
                                        if (!empty($request->brand_2_name)) {
                                            echo esc_html($request->brand_2_name);
                                        } elseif (!empty($custom_info['marke'])) {
                                            echo esc_html($custom_info['marke']) . ' <small style="color: #e74c3c;">(Benutzerdefiniert)</small>';
                                        } else {
                                            echo '-';
                                        }
                                        ?>
                                    </span>
                                </div>
                                <div class="detail-row">
                                    <span class="detail-label">Serie:</span>
                                    <span class="detail-value">
                                        <?php 
                                        if (!empty($request->brand_3_name)) {
                                            echo esc_html($request->brand_3_name);
                                        } elseif (!empty($custom_info['serie'])) {
                                            echo esc_html($custom_info['serie']) . ' <small style="color: #e74c3c;">(Benutzerdefiniert)</small>';
                                        } else {
                                            echo '-';
                                        }
                                        ?>
                                    </span>
                                </div>
                                <?php if (!empty($custom_info['modell'])): ?>
                                <div class="detail-row">
                                    <span class="detail-label">Modell:</span>
                                    <span class="detail-value">
                                        <?php echo esc_html($custom_info['modell']) . ' <small style="color: #e74c3c;">(Benutzerdefiniert)</small>'; ?>
                                    </span>
                                </div>
                                <?php endif; ?>
                                <div class="detail-row">
                                    <span class="detail-label">Service-Preis:</span>
                                    <span class="detail-value">
                                        <?php
                                        $services_data = $wpdb->get_var($wpdb->prepare(
                                            "SELECT services_data FROM {$wpdb->prefix}nexora_complete_service_requests WHERE request_id = %d",
                                            $request->id
                                        ));
                                        $services = $services_data ? json_decode($services_data, true) : [];
                                        if (!empty($services)):
                                        ?>
                                        <div class="services-table">
                                            <table>
                                                <thead>
                                                    <tr>
                                                        <th class="services-table-header">Titel</th>
                                                        <th class="services-table-header">Beschreibung</th>
                                                        <th class="services-table-header">Anzahl</th>
                                                        <th class="services-table-header">Preis (€)</th>
                                                        <th class="services-table-header">Gesamt (€)</th>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    <?php $sum = 0; foreach ($services as $srv):
                                                        $qty = isset($srv['quantity']) ? (int)$srv['quantity'] : 1;
                                                        $price = isset($srv['service_cost']) ? floatval($srv['service_cost']) : 0;
                                                        $total = $qty * $price;
                                                        $sum += $total;
                                                    ?>
                                                    <tr>
                                                        <td><?php echo esc_html($srv['service_title'] ?? ''); ?></td>
                                                        <td><?php echo esc_html($srv['description'] ?? ''); ?></td>
                                                        <td><?php echo $qty; ?></td>
                                                        <td><?php echo number_format($price, 2, ',', '.'); ?></td>
                                                        <td><?php echo number_format($total, 2, ',', '.'); ?></td>
                                                    </tr>
                                                    <?php endforeach; ?>
                                                </tbody>
                                                <tfoot>
                                                    <tr>
                                                        <td colspan="4">Summe:</td>
                                                        <td><?php echo number_format($sum, 2, ',', '.'); ?></td>
                                                    </tr>
                                                </tfoot>
                                            </table>
                                        </div>
                                        <?php else: ?>
                                            <span>Keine Services</span>
                                        <?php endif; ?>
                                    </span>
                                </div>
                            </div>
                            
                            <div class="detail-section" style="grid-column: 1 / -1;">
                                <h4>📝 Beschreibung</h4>
                                <div style="background: #f8f9fa; padding: 16px; border-radius: 6px; margin-top: 12px;">
                                    <?php echo nl2br(esc_html($request->description)); ?>
                                </div>
                            </div>
                            
                            
                            <?php if ($request->status_title !== 'Abgelehnt' && $request->status_title !== 'Abgeschlossen'): ?>
                                <div class="detail-section" style="grid-column: 1 / -1;">
                                    <?php 
                                    $request_id = $request->id;
                                    include NEXORA_PLUGIN_DIR . 'templates/chat/user-chat-box.php';
                                    ?>
                                </div>
                            <?php endif; ?>
                            
                            <?php if (!empty($invoices)): ?>
                                <div class="detail-section" style="grid-column: 1 / -1;">
                                    <h4>📄 Rechnungen</h4>
                                    <div class="invoice-section">
                                        <?php foreach ($invoices as $invoice): ?>
                                            <div class="invoice-item">
                                                <span>📄</span>
                                                <span><?php echo esc_html($invoice->file_name); ?></span>
                                                <a href="<?php echo wp_upload_dir()['baseurl'] . '/Nexora Service Suite-invoices/' . basename($invoice->file_path); ?>" 
                                                   target="_blank" 
                                                   class="invoice-download-link"
                                                   title="<?php echo esc_attr($invoice->file_name); ?>">
                                                    Herunterladen
                                                </a>
                                            </div>
                                        <?php endforeach; ?>
                                    </div>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php else: ?>
            <div class="no-requests">
                <h3>Keine Serviceanfragen gefunden</h3>
                <p>Sie haben noch keine Serviceanfragen erstellt.</p>

            </div>
        <?php endif; ?>
    </div>
    
    <script>
    let addBtn, dropdown, closeBtn;
    function setupDropdownFunctionality() {
        addBtn = document.getElementById('add-new-request-btn');
        dropdown = document.getElementById('new-request-dropdown');
        closeBtn = document.getElementById('close-dropdown-btn');
        
        if (addBtn && dropdown) {
            addBtn.removeEventListener('click', handleAddBtnClick);
            addBtn.addEventListener('click', handleAddBtnClick);
        }
        
        if (closeBtn && dropdown) {
            closeBtn.removeEventListener('click', handleCloseBtnClick);
            closeBtn.addEventListener('click', handleCloseBtnClick);
        }
    }
    function handleAddBtnClick() {
        if (dropdown) {
            dropdown.style.display = 'block';
            if (addBtn) addBtn.style.display = 'none';
            if (typeof loadDeviceTypes === 'function') {
                loadDeviceTypes();
            }
        }
    }
    function handleCloseBtnClick() {
        if (dropdown) {
            dropdown.style.display = 'none';
            if (addBtn) addBtn.style.display = 'inline-block';
        }
    }
    document.addEventListener('DOMContentLoaded', function() {
        setupDropdownFunctionality();
    });
    
    function toggleRequestDetails(header) {
        const details = header.nextElementSibling;
        const expandIcon = header.querySelector('.expand-icon');
        header.classList.toggle('active');
        details.classList.toggle('active');
        if (details.classList.contains('active')) {
            setTimeout(() => {
                details.scrollIntoView({ 
                    behavior: 'smooth', 
                    block: 'nearest',
                    inline: 'nearest'
                });
            }, 400);
        }
    }
    document.addEventListener('DOMContentLoaded', function() {
        const headers = document.querySelectorAll('.request-header');
        headers.forEach(header => {
            header.addEventListener('touchstart', function(e) {
                e.preventDefault();
                toggleRequestDetails(this);
            });
        });
    });
    function cancelServiceRequest(requestId) {
        if (!confirm('Sind Sie sicher, dass Sie diese Anfrage stornieren möchten? Diese Aktion kann nicht rückgängig gemacht werden.')) {
            return;
        }
        
        const button = event.target;
        const originalText = button.innerHTML;
        button.disabled = true;
        button.innerHTML = '⏳ Storniere...';
        
        const formData = new FormData();
        formData.append('action', 'cancel_service_request');
        formData.append('request_id', requestId);
        formData.append('nonce', document.getElementById('nexora_nonce').value);
        
        fetch(ajaxurl, {
            method: 'POST',
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                alert(data.data);
                location.reload();
            } else {
                alert('Fehler: ' + data.data);
                button.disabled = false;
                button.innerHTML = originalText;
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('Ein Fehler ist aufgetreten. Bitte versuchen Sie es erneut.');
            button.disabled = false;
            button.innerHTML = originalText;
        });
    }
    jQuery(function($){
        function loadDeviceTypes() {
            $.post(ajaxurl, {
                action: 'nexora_get_device_types',
                nonce: '<?php echo wp_create_nonce('nexora_nonce'); ?>'
            }, function(response) {
                let html = '<option value="">Bitte wählen</option>';
                if (response.success && response.data.length) {
                    response.data.forEach(function(type) {
                        html += `<option value="${type.id}">${type.name}</option>`;
                    });
                }
                html += '<option value="custom">Nicht gelistet? Typen</option>';
                $('#device_type_id').html(html).prop('disabled', false);
                $('#device_brand_id').html('<option value="">Bitte wählen Sie zuerst einen Gerätetyp</option>').prop('disabled', true);
                $('#device_series_id').html('<option value="">Bitte wählen Sie zuerst eine Marke</option>').prop('disabled', true);
                $('#device_model_id').html('<option value="">Bitte wählen Sie zuerst eine Serie</option>').prop('disabled', true);
                $('#device_type_custom').hide();
                $('#device_brand_custom').hide();
                $('#device_series_custom').hide();
                $('#device_model_custom').hide();
            });
        }
        function loadDeviceBrands(typeId) {
            if (!typeId || typeId === 'custom') {
                $('#device_brand_id').html('<option value="">Bitte wählen Sie zuerst einen Gerätetyp</option>').prop('disabled', true);
                $('#device_series_id').html('<option value="">Bitte wählen Sie zuerst eine Marke</option>').prop('disabled', true);
                $('#device_model_id').html('<option value="">Bitte wählen Sie zuerst eine Serie</option>').prop('disabled', true);
                $('#device_brand_custom').hide();
                $('#device_series_custom').hide();
                $('#device_model_custom').hide();
                return;
            }
            $.post(ajaxurl, {
                action: 'nexora_get_device_brands',
                nonce: '<?php echo wp_create_nonce('nexora_nonce'); ?>',
                type_id: typeId
            }, function(response) {
                let html = '<option value="">Bitte wählen</option>';
                if (response.success && response.data.length) {
                    response.data.forEach(function(brand) {
                        html += `<option value="${brand.id}">${brand.name}</option>`;
                    });
                }
                html += '<option value="custom">Nicht gelistet? Typen</option>';
                $('#device_brand_id').html(html).prop('disabled', false);
                $('#device_series_id').html('<option value="">Bitte wählen Sie zuerst eine Marke</option>').prop('disabled', true);
                $('#device_model_id').html('<option value="">Bitte wählen Sie zuerst eine Serie</option>').prop('disabled', true);
                $('#device_brand_custom').hide();
                $('#device_series_custom').hide();
                $('#device_model_custom').hide();
            });
        }
        function loadDeviceSeries(brandId) {
            if (!brandId || brandId === 'custom') {
                $('#device_series_id').html('<option value="">Bitte wählen Sie zuerst eine Marke</option>').prop('disabled', true);
                $('#device_model_id').html('<option value="">Bitte wählen Sie zuerst eine Serie</option>').prop('disabled', true);
                $('#device_series_custom').hide();
                $('#device_model_custom').hide();
                return;
            }
            $.post(ajaxurl, {
                action: 'nexora_get_device_series',
                nonce: '<?php echo wp_create_nonce('nexora_nonce'); ?>',
                brand_id: brandId
            }, function(response) {
                let html = '<option value="">Bitte wählen</option>';
                if (response.success && response.data.length) {
                    response.data.forEach(function(series) {
                        html += `<option value="${series.id}">${series.name}</option>`;
                    });
                }
                html += '<option value="custom">Nicht gelistet? Typen</option>';
                $('#device_series_id').html(html).prop('disabled', false);
                $('#device_model_id').html('<option value="">Bitte wählen Sie zuerst eine Serie</option>').prop('disabled', true);
                $('#device_series_custom').hide();
                $('#device_model_custom').hide();
            });
        }
        function loadDeviceModels(seriesId) {
            if (!seriesId || seriesId === 'custom') {
                $('#device_model_id').html('<option value="">Bitte wählen Sie zuerst eine Serie</option>').prop('disabled', true);
                $('#device_model_custom').hide();
                return;
            }
            $.post(ajaxurl, {
                action: 'nexora_get_device_models',
                nonce: '<?php echo wp_create_nonce('nexora_nonce'); ?>',
                series_id: seriesId
            }, function(response) {
                let html = '<option value="">Bitte wählen</option>';
                if (response.success && response.data.length) {
                    response.data.forEach(function(model) {
                        html += `<option value="${model.id}">${model.name}</option>`;
                    });
                }
                html += '<option value="custom">Nicht gelistet? Typen</option>';
                $('#device_model_id').html(html).prop('disabled', false);
                $('#device_model_custom').hide();
            });
        }
        $('#device_type_id').on('change', function() {
            const selectedValue = $(this).val();
            if (selectedValue === 'custom') {
                $('#device_type_custom').show();
                $('#device_brand_id').prop('disabled', true);
                $('#device_brand_custom').show();
                $('#device_series_id').prop('disabled', true);
                $('#device_series_custom').show();
                $('#device_model_id').prop('disabled', true);
                $('#device_model_custom').show();
            } else {
                $('#device_type_custom').hide();
                resetSubsequentStages('type');
                loadDeviceBrands(selectedValue);
            }
        });
        
        $('#device_brand_id').on('change', function() {
            const selectedValue = $(this).val();
            if (selectedValue === 'custom') {
                $('#device_brand_custom').show();
                $('#device_series_id').prop('disabled', true);
                $('#device_series_custom').show();
                $('#device_model_id').prop('disabled', true);
                $('#device_model_custom').show();
            } else {
                $('#device_brand_custom').hide();
                resetSubsequentStages('brand');
                loadDeviceSeries(selectedValue);
            }
        });
        
        $('#device_series_id').on('change', function() {
            const selectedValue = $(this).val();
            if (selectedValue === 'custom') {
                $('#device_series_custom').show();
                $('#device_model_id').prop('disabled', true);
                $('#device_model_custom').show();
            } else {
                $('#device_series_custom').hide();
                resetSubsequentStages('series');
                loadDeviceModels(selectedValue);
            }
        });
        
        $('#device_model_id').on('change', function() {
            const selectedValue = $(this).val();
            if (selectedValue === 'custom') {
                $('#device_model_custom').show();
            } else {
                $('#device_model_custom').hide();
            }
        });
        function resetSubsequentStages(startFrom) {
            switch(startFrom) {
                case 'type':
                    $('#device_brand_id').prop('disabled', true);
                    $('#device_brand_custom').hide();
                    $('#device_series_id').prop('disabled', true);
                    $('#device_series_custom').hide();
                    $('#device_model_id').prop('disabled', true);
                    $('#device_model_custom').hide();
                    break;
                case 'brand':
                    $('#device_series_id').prop('disabled', true);
                    $('#device_series_custom').hide();
                    $('#device_model_id').prop('disabled', true);
                    $('#device_model_custom').hide();
                    break;
                case 'series':
                    $('#device_model_id').prop('disabled', true);
                    $('#device_model_custom').hide();
                    break;
            }
        }
        loadDeviceTypes();
        $('#attachments').on('change', function(e) {
            const files = this.files;
            const preview = $('#attachments-preview');
            preview.empty();
            if (files.length > 5) {
                alert('Sie können maximal 5 Bilder hochladen.');
                this.value = '';
                return;
            }
            Array.from(files).forEach(file => {
                if (file.type.startsWith('image/')) {
                    const reader = new FileReader();
                    reader.onload = function(e) {
                        const img = $('<img>').attr('src', e.target.result).css({width:'70px',height:'70px',objectFit:'cover',border:'1px solid #ccc',borderRadius:'4px'});
                        preview.append(img);
                    };
                    reader.readAsDataURL(file);
                }
            });
        });
    });
    </script>
    <?php
}
add_action('woocommerce_account_my-requests_endpoint', 'nexora_my_requests_endpoint_content');
function nexora_financial_accounts_endpoint_content() {
    if (!is_user_logged_in()) {
        echo '<p>Bitte melden Sie sich in Ihrem Benutzerkonto an.</p>';
        return;
    }
    if (!nexora_is_commission_user()) {
        echo '<p>Sie haben keinen Zugriff auf diese Seite.</p>';
        return;
    }
    
    $user_id = get_current_user_id();
    global $wpdb;
    $user_benefit_type = get_user_meta($user_id, 'benefit_type', true);
    
    if ($user_benefit_type !== 'commission') {
        $requests = array();
    } else {
        $requests_query = "SELECT 
            sr.id as request_id,
            sr.user_id,
            sr.description,
            sr.model,
            sr.serial,
            sr.service_description,
            sr.service_quantity,
            sr.created_at,
            sr.status_id,
            u.user_login,
            u.user_email,
            um1.meta_value as first_name,
            um2.meta_value as last_name,
            um3.meta_value as benefit_type,
            um4.meta_value as discount_percentage,
            um5.meta_value as commission_percentage,
            um6.meta_value as payment_status,
            COALESCE(ss.title, 'Neu') as status_title,
            COALESCE(ss.color, '#0073aa') as status_color,
            d1.name as brand_1_name, 
            d2.name as brand_2_name, 
            d3.name as brand_3_name,
            s.title as service_title,
            s.cost as service_cost
        FROM {$wpdb->prefix}nexora_service_requests sr
        INNER JOIN {$wpdb->users} u ON sr.user_id = u.ID
        LEFT JOIN {$wpdb->usermeta} um1 ON u.ID = um1.user_id AND um1.meta_key = 'first_name'
        LEFT JOIN {$wpdb->usermeta} um2 ON u.ID = um2.user_id AND um2.meta_key = 'last_name'
        LEFT JOIN {$wpdb->usermeta} um3 ON u.ID = um3.user_id AND um3.meta_key = 'benefit_type'
        LEFT JOIN {$wpdb->usermeta} um4 ON u.ID = um4.user_id AND um4.meta_key = 'discount_percentage'
        LEFT JOIN {$wpdb->usermeta} um5 ON u.ID = um5.user_id AND um5.meta_key = 'commission_percentage'
        LEFT JOIN {$wpdb->usermeta} um6 ON u.ID = um6.user_id AND um6.meta_key = 'payment_status'
        LEFT JOIN {$wpdb->prefix}nexora_service_status ss ON ss.id = sr.status_id
        LEFT JOIN {$wpdb->prefix}nexora_devices d1 ON sr.brand_level_1_id = d1.id AND d1.type = 'type'
        LEFT JOIN {$wpdb->prefix}nexora_devices d2 ON sr.brand_level_2_id = d2.id AND d2.type = 'brand'
        LEFT JOIN {$wpdb->prefix}nexora_devices d3 ON sr.brand_level_3_id = d3.id AND d3.type = 'series'
        LEFT JOIN {$wpdb->prefix}nexora_services s ON sr.service_id = s.id
        WHERE sr.user_id = %d AND um3.meta_value = 'commission'
        ORDER BY sr.id DESC";
        
        $requests = $wpdb->get_results(
            $wpdb->prepare($requests_query, $user_id)
        );
    }
    $payment_status = get_user_meta($user_id, 'payment_status', true) ?: 'unpaid';
    $payment_date = get_user_meta($user_id, 'payment_date', true);
    if (empty($requests)) {
        echo '<div style="background: #fff3cd; padding: 10px; margin: 10px 0; border: 1px solid #ffeaa7; border-radius: 5px;">';
        echo '<strong>Debug:</strong> User ID: ' . $user_id . '<br>';
        echo 'User benefit_type: ' . $user_benefit_type . '<br>';
        $all_requests = $wpdb->get_results(
            $wpdb->prepare(
                "SELECT id, user_id FROM {$wpdb->prefix}nexora_service_requests WHERE user_id = %d",
                $user_id
            )
        );
        echo 'All requests for user: ' . count($all_requests) . '<br>';
        $benefit_meta = $wpdb->get_var(
            $wpdb->prepare(
                "SELECT meta_value FROM {$wpdb->usermeta} WHERE user_id = %d AND meta_key = 'benefit_type'",
                $user_id
            )
        );
        echo 'Benefit type from usermeta: ' . ($benefit_meta ?: 'NULL') . '<br>';
        echo '</div>';
    }
    
    ?>
    <div class="Nexora Service Suite-financial-accounts-wrapper">
        <div style="margin-bottom: 30px;">
            <h3>Meine Finanzkonten</h3>
            <p style="color: #666; margin-top: 10px;">Übersicht über Ihre Provision-Anfragen und deren Zahlungsstatus</p>
        </div>
        
        <?php if (empty($requests)): ?>
            <div style="text-align: center; padding: 40px; background: #f9f9f9; border-radius: 8px; margin: 20px 0;">
                <p style="color: #666; font-size: 16px;">Keine Provision-Anfragen gefunden.</p>
            </div>
        <?php else: ?>
            <div style="overflow-x: auto;">
                <table style="width: 100%; border-collapse: collapse; background: white; border-radius: 8px; overflow: hidden; box-shadow: 0 2px 10px rgba(0,0,0,0.1);">
                    <thead style="background: #f8f9fa;">
                        <tr>
                            <th style="padding: 15px; text-align: left; border-bottom: 1px solid #dee2e6; font-weight: 600; color: #495057;">ID</th>
                            <th style="padding: 15px; text-align: left; border-bottom: 1px solid #dee2e6; font-weight: 600; color: #495057;">Gerät</th>
                            <th style="padding: 15px; text-align: left; border-bottom: 1px solid #dee2e6; font-weight: 600; color: #495057;">Service</th>
                            <th style="padding: 15px; text-align: left; border-bottom: 1px solid #dee2e6; font-weight: 600; color: #495057;">Provision</th>
                            <th style="padding: 15px; text-align: left; border-bottom: 1px solid #dee2e6; font-weight: 600; color: #495057;">Status</th>
                            <th style="padding: 15px; text-align: left; border-bottom: 1px solid #dee2e6; font-weight: 600; color: #495057;">Datum</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($requests as $request): ?>
                            <tr style="border-bottom: 1px solid #f1f3f4;">
                                <td style="padding: 15px; color: #495057;">#<?php echo esc_html($request->request_id); ?></td>
                                <td style="padding: 15px; color: #495057;">
                                    <?php 
                                    $device_parts = array_filter([
                                        $request->brand_1_name,
                                        $request->brand_2_name, 
                                        $request->brand_3_name,
                                        $request->model
                                    ]);
                                    echo esc_html(implode(' ', $device_parts));
                                    ?>
                                </td>
                                <td style="padding: 15px; color: #495057;">
                                    <?php echo esc_html($request->service_title); ?>
                                    <?php if ($request->service_quantity > 1): ?>
                                        <span style="color: #6c757d;">(<?php echo esc_html($request->service_quantity); ?>x)</span>
                                    <?php endif; ?>
                                </td>
                                <td style="padding: 15px; color: #495057; font-weight: 600;">
                                    €<?php echo esc_html(number_format($request->commission_percentage, 2)); ?>
                                </td>
                                <td style="padding: 15px;">
                                    <?php 
                                    $request_payment_status = $request->payment_status ?: 'unpaid';
                                    $status_text = $request_payment_status === 'paid' ? 'Bezahlt' : 'Ausstehend';
                                    $status_color = $request_payment_status === 'paid' ? '#28a745' : '#dc3545';
                                    ?>
                                    <span style="padding: 6px 12px; border-radius: 20px; font-size: 12px; font-weight: 600; background: <?php echo $status_color; ?>20; color: <?php echo $status_color; ?>;">
                                        <?php echo $status_text; ?>
                                    </span>
                                    <?php if ($request_payment_status === 'paid' && $payment_date): ?>
                                        <br><small style="color: #6c757d; margin-top: 5px; display: block;">
                                            Bezahlt am: <?php echo esc_html(date('d.m.Y', strtotime($payment_date))); ?>
                                        </small>
                                    <?php endif; ?>
                                </td>
                                <td style="padding: 15px; color: #6c757d;">
                                    <?php echo esc_html(date('d.m.Y', strtotime($request->created_at))); ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>
    </div>
    <?php
}
add_action('woocommerce_account_financial-accounts_endpoint', 'nexora_financial_accounts_endpoint_content');
function nexora_flush_rewrite_rules_now() {
    update_option('nexora_flush_rewrite_rules', true);
}
function nexora_get_settings() {
    $defaults = [
        'primary_color' => '#0073aa',
        'button_color' => '#009688',
        'background_color' => '#ffffff',
        'text_color' => '#222222',
        'font_family' => 'inherit',
        'border_radius' => '4',
        'logo_url' => '',
        'font_size' => '16',
        'enable_front_form' => 1,
        'enable_user_list' => 1,
        'enable_brand_manage' => 1,
        'allow_user_add_brand' => 1,
        'allow_user_edit_request' => 0,
        'allow_user_delete_request' => 0,
        'show_status_to_user' => 1,
        'success_message' => 'Serviceanfrage wurde erfolgreich erstellt.',
        'error_message' => 'Fehler beim Speichern der Anfrage.',
        'form_help_text' => '',
        'button_text' => 'Absenden',
        'notify_admin_on_new' => 1,
        'notify_user_on_status' => 1,
        'email_template_admin' => 'Neue Anfrage: [model] ([user])',
        'email_template_user' => 'Ihre Anfrage wurde aktualisiert: [status]',
        'enable_logs' => 1,
        'allowed_roles_submit' => ['subscriber','customer'],
        'allowed_roles_manage' => ['administrator'],
        'require_serial' => 0,
        'max_description_length' => 1000,
        'allow_file_upload' => 0,
        'requests_per_page' => 20,
        'default_sort' => 'desc',
        'enable_filters' => 1,
    ];
    $settings = get_option('nexora_plugin_settings', []);
    return array_merge($defaults, (array)$settings);
}
add_action('admin_head', 'nexora_inject_dynamic_css');
add_action('wp_head', 'nexora_inject_dynamic_css');
function nexora_inject_dynamic_css() {
    $settings = nexora_get_settings();
    echo '<style type="text/css">';
    echo ':root {';
    echo '--Nexora Service Suite-primary: '.esc_attr($settings['primary_color']).';';
    echo '--Nexora Service Suite-button: '.esc_attr($settings['button_color']).';';
    echo '--Nexora Service Suite-bg: '.esc_attr($settings['background_color']).';';
    echo '--Nexora Service Suite-text: '.esc_attr($settings['text_color']).';';
    echo '--Nexora Service Suite-font: '.esc_attr($settings['font_family']).';';
    echo '--Nexora Service Suite-radius: '.esc_attr($settings['border_radius']).'px;';
    echo '--Nexora Service Suite-font-size: '.esc_attr($settings['font_size']).'px;';
    echo '}';
    echo '</style>';
}
add_action('wp_ajax_nexora_get_services', 'nexora_ajax_get_services');
function nexora_ajax_get_services() {
    if (!wp_verify_nonce($_POST['nonce'], 'nexora_nonce')) {
        wp_send_json_error('Invalid nonce');
        return;
    }
    
    global $wpdb;
    $services = $wpdb->get_results(
        "SELECT id, title, cost, description, status 
         FROM {$wpdb->prefix}nexora_services 
         WHERE status = 'active' 
         ORDER BY title ASC",
        ARRAY_A
    );
    
    if ($services === false) {
        wp_send_json_error('Database error: ' . $wpdb->last_error);
        return;
    }
    
    wp_send_json_success(array(
        'services' => $services,
        'total' => count($services)
    ));
}
add_action('wp_ajax_nexora_save_invoice_services', 'nexora_ajax_save_invoice_services');
function nexora_ajax_save_invoice_services() {
    if (!wp_verify_nonce($_POST['nonce'], 'nexora_nonce')) {
        wp_send_json_error('Invalid nonce');
        return;
    }
    
    global $wpdb;
    
    $request_id = intval($_POST['request_id']);
    $services = $_POST['services'];
    
    if (!$request_id) {
        wp_send_json_error('Invalid request ID');
        return;
    }
    $request_exists = $wpdb->get_var($wpdb->prepare(
        "SELECT id FROM {$wpdb->prefix}nexora_service_requests WHERE id = %d",
        $request_id
    ));
    
    if (!$request_exists) {
        wp_send_json_error('Request not found');
        return;
    }
    $wpdb->delete(
        $wpdb->prefix . 'nexora_faktor_services',
        array('request_id' => $request_id),
        array('%d')
    );
    $success_count = 0;
    foreach ($services as $service) {
        $service_id = intval($service['service_id']);
        $service_cost = floatval($service['service_cost']);
        $quantity = intval($service['quantity']);
        $description = sanitize_textarea_field($service['description']);
        $service_title = $wpdb->get_var($wpdb->prepare(
            "SELECT title FROM {$wpdb->prefix}nexora_services WHERE id = %d",
            $service_id
        ));
        
        if ($service_title) {
            $result = $wpdb->insert(
                $wpdb->prefix . 'nexora_faktor_services',
                array(
                    'request_id' => $request_id,
                    'service_id' => $service_id,
                    'service_title' => $service_title,
                    'service_cost' => $service_cost,
                    'quantity' => $quantity,
                    'description' => $description
                ),
                array('%d', '%d', '%s', '%f', '%d', '%s')
            );
            
            if ($result !== false) {
                $success_count++;
            }
        }
    }
    
    wp_send_json_success(array(
        'message' => "{$success_count} services saved successfully",
        'saved_count' => $success_count
    ));
}
add_action('wp_ajax_nexora_get_invoice_services', 'nexora_ajax_get_invoice_services');
function nexora_ajax_get_invoice_services() {
    if (!wp_verify_nonce($_POST['nonce'], 'nexora_nonce')) {
        wp_send_json_error('Invalid nonce');
        return;
    }
    
    global $wpdb;
    
    $request_id = intval($_POST['request_id']);
    
    if (!$request_id) {
        wp_send_json_error('Invalid request ID');
        return;
    }
    $services = $wpdb->get_results($wpdb->prepare(
        "SELECT id, request_id, service_id, service_title, service_cost, quantity, description 
         FROM {$wpdb->prefix}nexora_faktor_services 
         WHERE request_id = %d 
         ORDER BY id ASC",
        $request_id
    ), ARRAY_A);
    
    if ($services === false) {
        wp_send_json_error('Database error: ' . $wpdb->last_error);
        return;
    }
    
    wp_send_json_success($services);
}
add_action('wp_ajax_create_invoice_services_table', 'nexora_ajax_create_invoice_services_table');
function nexora_ajax_create_invoice_services_table() {
    global $wpdb;
    
    $table_name = $wpdb->prefix . 'nexora_faktor_services';
    $sql = "CREATE TABLE IF NOT EXISTS $table_name (
        id BIGINT(20) NOT NULL AUTO_INCREMENT,
        request_id mediumint(9) NOT NULL,
        service_id BIGINT(20) NOT NULL,
        service_title VARCHAR(255) NOT NULL,
        service_cost DECIMAL(10,2) DEFAULT 0.00,
        quantity INT NOT NULL DEFAULT 1,
        description TEXT,
        created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
        updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        PRIMARY KEY (id),
        KEY idx_request_id (request_id),
        KEY idx_service_id (service_id),
        FOREIGN KEY (request_id) REFERENCES {$wpdb->prefix}nexora_service_requests(id) ON DELETE CASCADE,
        FOREIGN KEY (service_id) REFERENCES {$wpdb->prefix}nexora_services(id) ON DELETE CASCADE
    ) " . $wpdb->get_charset_collate() . ";";
    require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
    $result = dbDelta($sql);
    if (empty($wpdb->last_error)) {
        $table_exists = $wpdb->get_var("SHOW TABLES LIKE '$table_name'") == $table_name;
        
        if ($table_exists) {
            wp_send_json_success(array(
                'message' => 'Faktor Services Table created successfully',
                'table_name' => $table_name,
                'sql_executed' => 'CREATE TABLE IF NOT EXISTS',
                'result' => $result
            ));
        } else {
            wp_send_json_error(array(
                'error' => 'Table was not created',
                'table_name' => $table_name,
                'sql_error' => $wpdb->last_error
            ));
        }
    } else {
        $sql_without_fk = "CREATE TABLE IF NOT EXISTS $table_name (
            id BIGINT(20) NOT NULL AUTO_INCREMENT,
            request_id mediumint(9) NOT NULL,
            service_id BIGINT(20) NOT NULL,
            service_title VARCHAR(255) NOT NULL,
            service_cost DECIMAL(10,2) DEFAULT 0.00,
            quantity INT NOT NULL DEFAULT 1,
            description TEXT,
            created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
            updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            PRIMARY KEY (id),
            KEY idx_request_id (request_id),
            KEY idx_service_id (service_id)
        ) " . $wpdb->get_charset_collate() . ";";
        
        $result2 = dbDelta($sql_without_fk);
        
        if (empty($wpdb->last_error)) {
            $table_exists = $wpdb->get_var("SHOW TABLES LIKE '$table_name'") == $table_name;
            
            if ($table_exists) {
                            wp_send_json_success(array(
                'message' => 'Faktor Services Table created successfully (without foreign keys)',
                'table_name' => $table_name,
                'sql_executed' => 'CREATE TABLE IF NOT EXISTS (without FK)',
                'result' => $result2,
                'warning' => 'Foreign keys were not created due to compatibility issues'
            ));
            } else {
                wp_send_json_error(array(
                    'error' => 'Table was not created even without foreign keys',
                    'table_name' => $table_name,
                    'sql_error' => $wpdb->last_error
                ));
            }
        } else {
            wp_send_json_error(array(
                'error' => 'Database error occurred',
                'table_name' => $table_name,
                'sql_error' => $wpdb->last_error
            ));
        }
    }
}

add_action('wp_ajax_nexora_create_invoice_services_table', 'nexora_ajax_create_invoice_services_table');
add_action('wp_ajax_cancel_service_request', 'nexora_ajax_cancel_service_request');

function nexora_ajax_cancel_service_request() {
    if (!wp_verify_nonce($_POST['nonce'], 'nexora_nonce')) {
        wp_send_json_error('Sicherheitsüberprüfung fehlgeschlagen.');
    }
    if (!is_user_logged_in()) {
        wp_send_json_error('Sie müssen angemeldet sein.');
    }
    
    $user_id = get_current_user_id();
    $request_id = intval($_POST['request_id']);
    
    global $wpdb;
    $request = $wpdb->get_row($wpdb->prepare(
        "SELECT id, status_id FROM {$wpdb->prefix}nexora_service_requests WHERE id = %d AND user_id = %d",
        $request_id,
        $user_id
    ));
    
    if (!$request) {
        wp_send_json_error('Anfrage nicht gefunden oder Sie haben keine Berechtigung.');
    }
    $cancelled_status_id = $wpdb->get_var(
        "SELECT id FROM {$wpdb->prefix}nexora_service_status WHERE title = 'Abgelehnt'"
    );
    
    if (!$cancelled_status_id) {
        wp_send_json_error('Status "Abgelehnt" nicht gefunden.');
    }
    $result = $wpdb->update(
        $wpdb->prefix . 'nexora_service_requests',
        array('status_id' => $cancelled_status_id),
        array('id' => $request_id),
        array('%d'),
        array('%d')
    );
    
    if ($result !== false) {
        wp_send_json_success('Anfrage erfolgreich storniert. Ein Administrator wird sie überprüfen und löschen.');
    } else {
        wp_send_json_error('Fehler beim Stornieren der Anfrage.');
    }
}
function eltern_filter_requests_ajax() {
    if (!wp_verify_nonce($_POST['nonce'], 'eltern_filter_requests')) {
        wp_send_json_error('Sicherheitsüberprüfung fehlgeschlagen.');
    }
    if (!current_user_can('manage_options')) {
        wp_send_json_error('Keine Berechtigung.');
    }
    
    global $wpdb;
    $benefit_type_filter = isset($_POST['benefit_type']) ? sanitize_text_field($_POST['benefit_type']) : '';
    $user_filter = isset($_POST['user_filter']) ? intval($_POST['user_filter']) : 0;
    $date_from = isset($_POST['date_from']) ? sanitize_text_field($_POST['date_from']) : '';
    $date_to = isset($_POST['date_to']) ? sanitize_text_field($_POST['date_to']) : '';
    $users_with_benefits = $wpdb->get_results("
        SELECT DISTINCT u.ID, u.user_login, u.user_email,
               COALESCE(um1.meta_value, '') as first_name,
               COALESCE(um2.meta_value, '') as last_name,
               um3.meta_value as benefit_type
        FROM {$wpdb->users} u
        INNER JOIN {$wpdb->usermeta} um3 ON u.ID = um3.user_id AND um3.meta_key = 'benefit_type'
        LEFT JOIN {$wpdb->usermeta} um1 ON u.ID = um1.user_id AND um1.meta_key = 'first_name'
        LEFT JOIN {$wpdb->usermeta} um2 ON u.ID = um2.user_id AND um2.meta_key = 'last_name'
        WHERE (um3.meta_value = 'discount' OR um3.meta_value = 'commission')
        ORDER BY u.user_login
    ");
    if (empty($users_with_benefits)) {
        wp_send_json_success(array(
            'requests' => array(),
            'stats' => array(
                'total_requests' => 0,
                'discount_requests' => 0,
                'commission_requests' => 0
            )
        ));
    }
    $user_ids = array_map(function($user) { return $user->ID; }, $users_with_benefits);
    $user_ids_placeholders = implode(',', array_fill(0, count($user_ids), '%d'));

    $requests_query = "SELECT 
        sr.id as request_id,
        sr.user_id,
        sr.description,
        sr.created_at,
        sr.status_id,
        u.user_login,
        u.user_email,
        um1.meta_value as first_name,
        um2.meta_value as last_name,
        um3.meta_value as benefit_type,
        um4.meta_value as discount_percentage,
        um5.meta_value as commission_percentage,
        um6.meta_value as payment_status
    FROM {$wpdb->prefix}nexora_service_requests sr
    INNER JOIN {$wpdb->users} u ON sr.user_id = u.ID
    LEFT JOIN {$wpdb->usermeta} um1 ON u.ID = um1.user_id AND um1.meta_key = 'first_name'
    LEFT JOIN {$wpdb->usermeta} um2 ON u.ID = um2.user_id AND um2.meta_key = 'last_name'
    LEFT JOIN {$wpdb->usermeta} um3 ON u.ID = um3.user_id AND um3.meta_key = 'benefit_type'
    LEFT JOIN {$wpdb->usermeta} um4 ON u.ID = um4.user_id AND um4.meta_key = 'discount_percentage'
    LEFT JOIN {$wpdb->usermeta} um5 ON u.ID = um5.user_id AND um5.meta_key = 'commission_percentage'
    LEFT JOIN {$wpdb->usermeta} um6 ON u.ID = um6.user_id AND um6.meta_key = 'payment_status'
    WHERE sr.user_id IN ($user_ids_placeholders)";

    $where_conditions = [];
    $query_params = array_merge($user_ids, []);
    if ($benefit_type_filter) {
        $where_conditions[] = "um3.meta_value = %s";
        $query_params[] = $benefit_type_filter;
    }
    if ($user_filter > 0) {
        $where_conditions[] = "sr.user_id = %d";
        $query_params[] = $user_filter;
    }
    if ($date_from) {
        $where_conditions[] = "DATE(sr.created_at) >= %s";
        $query_params[] = $date_from;
    }

    if ($date_to) {
        $where_conditions[] = "DATE(sr.created_at) <= %s";
        $query_params[] = $date_to;
    }
    if (!empty($where_conditions)) {
        $requests_query .= " AND " . implode(' AND ', $where_conditions);
    }

    $requests_query .= " ORDER BY sr.created_at DESC";

    $requests = $wpdb->get_results($wpdb->prepare($requests_query, $query_params));
    $total_requests = count($requests);
    $discount_requests = 0;
    $commission_requests = 0;

    foreach ($requests as $request) {
        if ($request->benefit_type === 'discount') {
            $discount_requests++;
        } else {
            $commission_requests++;
        }
    }

    $stats = array(
        'total_requests' => $total_requests,
        'discount_requests' => $discount_requests,
        'commission_requests' => $commission_requests
    );

    wp_send_json_success(array(
        'requests' => $requests,
        'stats' => $stats
    ));
}
function bulk_update_payment_status_ajax() {
    if (!wp_verify_nonce($_POST['nonce'], 'bulk_update_payment_status')) {
        wp_send_json_error('Sicherheitsüberprüfung fehlgeschlagen.');
    }
    if (!current_user_can('manage_options')) {
        wp_send_json_error('Keine Berechtigung.');
    }
    
    $user_ids = json_decode(stripslashes($_POST['user_ids']), true);
    $status = sanitize_text_field($_POST['status']);
    
    if (!is_array($user_ids) || empty($user_ids)) {
        wp_send_json_error('Ungültige Benutzer-IDs.');
    }
    
    global $wpdb;
    
    $updated = 0;
    foreach ($user_ids as $user_id) {
        $user_id = intval($user_id);
        if ($user_id > 0) {
            $result = update_user_meta($user_id, 'payment_status', $status);
            if ($result !== false) {
                $updated++;
            }
        }
    }
    
    wp_send_json_success("$updated Benutzer erfolgreich aktualisiert.");
}
function bulk_delete_requests_ajax() {
    if (!wp_verify_nonce($_POST['nonce'], 'bulk_delete_requests')) {
        wp_send_json_error('Sicherheitsüberprüfung fehlgeschlagen.');
    }
    if (!current_user_can('manage_options')) {
        wp_send_json_error('Keine Berechtigung.');
    }
    
    $request_ids = json_decode(stripslashes($_POST['request_ids']), true);
    
    if (!is_array($request_ids) || empty($request_ids)) {
        wp_send_json_error('Ungültige Anfrage-IDs.');
    }
    
    global $wpdb;
    
    $deleted = 0;
    foreach ($request_ids as $request_id) {
        $request_id = intval($request_id);
        if ($request_id > 0) {
            $result = $wpdb->delete(
                $wpdb->prefix . 'nexora_service_requests',
                array('id' => $request_id),
                array('%d')
            );
            if ($result !== false) {
                $deleted++;
            }
        }
    }
    
    wp_send_json_success("$deleted Anfragen erfolgreich gelöscht.");
}

