<?php
if (!defined('ABSPATH')) {
    $wp_path = dirname(__FILE__);
    for ($i = 0; $i < 5; $i++) {
        if (file_exists($wp_path . '/wp-config.php')) {
            define('ABSPATH', $wp_path . '/');
            break;
        }
        $wp_path = dirname($wp_path);
    }
    
    if (!defined('ABSPATH')) {
        $possible_paths = [
            dirname(__FILE__) . '/../../',
            dirname(__FILE__) . '/../../../',
            dirname(__FILE__) . '/../../../../',
            '/home/seo2se/public_html/',
            '/home/seo2se/public_html/wp-content/',
        ];
        
        foreach ($possible_paths as $path) {
            if (file_exists($path . 'wp-config.php')) {
                define('ABSPATH', $path);
                break;
            }
        }
    }
}
if (defined('ABSPATH') && file_exists(ABSPATH . 'wp-load.php')) {
    require_once(ABSPATH . 'wp-load.php');
} else {
    $wp_load_path = dirname(__FILE__);
    for ($i = 0; $i < 5; $i++) {
        if (file_exists($wp_load_path . '/wp-load.php')) {
            require_once($wp_load_path . '/wp-load.php');
            break;
        }
        $wp_load_path = dirname($wp_load_path);
    }
}
$current_user_id = get_current_user_id();
if (!$current_user_id) {
    wp_die('Bitte melden Sie sich an, um auf diese Seite zuzugreifen.');
}
if (!current_user_can('manage_options')) {
    if ($request_id > 0) {
        global $wpdb;
        $anfragen_table = $wpdb->prefix . 'nexora_service_requests';
        $user_request = $wpdb->get_row($wpdb->prepare(
            "SELECT user_id FROM $anfragen_table WHERE id = %d", 
            $request_id
        ), ARRAY_A);
        
        if (!$user_request || $user_request['user_id'] != $current_user_id) {
            wp_die('Sie haben keine Berechtigung, diese Anfrage zu bearbeiten.');
        }
    }
}
$request_id = isset($_GET['id']) ? intval($_GET['id']) : 0;
$request_data = [];
$customer_data = [];
$device_data = [];
function parse_custom_device_info($description) {
    $custom_info = [
        'device_type_custom' => '',
        'device_brand_custom' => '',
        'device_series_custom' => '',
        'device_model_custom' => '',
        'description_clean' => $description
    ];
    
    if (empty($description)) {
        return $custom_info;
    }
    $lines = explode("\n", $description);
    $custom_lines = [];
    $description_lines = [];
    
    foreach ($lines as $line) {
        $line = trim($line);
        if (empty($line)) continue;
        if (strpos($line, '|') !== false) {
            $parts = explode('|', $line);
            foreach ($parts as $part) {
                $part = trim($part);
                if (preg_match('/^(Gerätetyp|Marke|Serie|Modell):\s*(.+)$/', $part, $matches)) {
                    $custom_lines[] = $part;
                    $field = strtolower($matches[1]);
                    $value = trim($matches[2]);
                    
                    switch ($field) {
                        case 'gerätetyp':
                            $custom_info['device_type_custom'] = $value;
                            break;
                        case 'marke':
                            $custom_info['device_brand_custom'] = $value;
                            break;
                        case 'serie':
                            $custom_info['device_series_custom'] = $value;
                            break;
                        case 'modell':
                            $custom_info['device_model_custom'] = $value;
                            break;
                    }
                }
            }
            continue;
        }
        elseif (preg_match('/^(Gerätetyp|Marke|Serie|Modell):\s*(.+)$/', $line, $matches)) {
            $custom_lines[] = $line;
            $field = strtolower($matches[1]);
            $value = trim($matches[2]);
            
            switch ($field) {
                case 'gerätetyp':
                    $custom_info['device_type_custom'] = $value;
                    break;
                case 'marke':
                    $custom_info['device_brand_custom'] = $value;
                    break;
                case 'serie':
                    $custom_info['device_series_custom'] = $value;
                    break;
                case 'modell':
                    $custom_info['device_model_custom'] = $value;
                    break;
            }
        } else {
            $description_lines[] = $line;
        }
    }
    $custom_info['description_clean'] = implode("\n", $description_lines);
    
    return $custom_info;
}

if ($request_id > 0) {
    global $wpdb;
    $anfragen_table = $wpdb->prefix . 'nexora_service_requests';
    $request_data = $wpdb->get_row($wpdb->prepare("SELECT * FROM $anfragen_table WHERE id = %d", $request_id), ARRAY_A);
    if ($request_data) {
        error_log("Form load - Request ID: {$request_id}, Current status_id from DB: " . ($request_data['status_id'] ?? 'NULL'));
        error_log("Form load - Full request_data: " . json_encode($request_data));
    } else {
        error_log("Form load - ERROR: No request data found for ID: {$request_id}");
    }
    
    if ($request_data) {
        $user_benefit_type = '';
        $user_benefit_percentage = 0;
        if ($request_data['user_id']) {
            $user_benefit_type = get_user_meta($request_data['user_id'], 'benefit_type', true);
            if ($user_benefit_type === 'discount') {
                $user_benefit_percentage = floatval(get_user_meta($request_data['user_id'], 'discount_percentage', true));
            } elseif ($user_benefit_type === 'commission') {
                $user_benefit_percentage = floatval(get_user_meta($request_data['user_id'], 'commission_percentage', true));
            }
        }
        $custom_device_info = parse_custom_device_info($request_data['description'] ?? '');
        $complete_table = $wpdb->prefix . 'nexora_complete_service_requests';
        $complete_data = $wpdb->get_row($wpdb->prepare("SELECT * FROM $complete_table WHERE request_id = %d", $request_id), ARRAY_A);
        if ($complete_data) {
            error_log("Form load - Complete data found: " . json_encode($complete_data));
        } else {
            error_log("Form load - No complete data found for request ID: {$request_id}");
        }
        
        if ($complete_data) {
            $customer_data = [
                'customer_name' => $complete_data['customer_name'],
                'customer_email' => $complete_data['customer_email'],
                'customer_phone' => $complete_data['customer_phone'],
                'customer_type' => $complete_data['customer_type'],
                'company_name' => $complete_data['company_name'],
                'street' => $complete_data['street'],
                'postal_code' => $complete_data['postal_code'],
                'city' => $complete_data['city'],
                'country' => $complete_data['country'],
                'vat_id' => $complete_data['vat_id'],
                'user_id' => $complete_data['user_id']
            ];
            
            $device_data = [
                'id' => $complete_data['device_id'],
                'type' => $complete_data['device_type'],
                'name' => $complete_data['device_brand'],
                'model' => $complete_data['device_model'],
                'serial' => $complete_data['device_serial'],
                'description' => $complete_data['device_description'],
                'brand_level_1_id' => $request_data['brand_level_1_id'] ?? 0,
                'brand_level_2_id' => $request_data['brand_level_2_id'] ?? 0,
                'brand_level_3_id' => $request_data['brand_level_3_id'] ?? 0
            ];
            $request_data['status_id'] = $complete_data['status_id'] ?? $request_data['status_id'];
            $request_data['priority'] = $complete_data['priority'] ?? $request_data['priority'];
            $request_data['assigned_to'] = $complete_data['assigned_to'] ?? $request_data['assigned_to'];
            $request_data['estimated_completion'] = $complete_data['estimated_completion'] ?? $request_data['estimated_completion'];
            if (empty($request_data['status_id']) && !empty($complete_data['status_id'])) {
                $request_data['status_id'] = $complete_data['status_id'];
                error_log("Form load - Fixed empty status_id with complete_data: " . $request_data['status_id']);
            }
            if (empty($request_data['priority']) && !empty($complete_data['priority'])) {
                $request_data['priority'] = $complete_data['priority'];
                error_log("Form load - Fixed empty priority with complete_data: " . $request_data['priority']);
            }
            error_log("Form load - Updated status_id: " . ($request_data['status_id'] ?? 'NULL'));
            error_log("Form load - Updated priority: " . ($request_data['priority'] ?? 'NULL'));
            error_log("Form load - Updated assigned_to: " . ($request_data['assigned_to'] ?? 'NULL'));
            error_log("Form load - Updated estimated_completion: " . ($request_data['estimated_completion'] ?? 'NULL'));
            $custom_device_info['description_clean'] = $complete_data['device_description'] ?? '';
        } else {
            $user_id = $request_data['user_id'];
            error_log("Form load - Using fallback status data from main table");
            error_log("Form load - Fallback status_id: " . ($request_data['status_id'] ?? 'NULL'));
            error_log("Form load - Fallback priority: " . ($request_data['priority'] ?? 'NULL'));
            error_log("Form load - Fallback assigned_to: " . ($request_data['assigned_to'] ?? 'NULL'));
            error_log("Form load - Fallback estimated_completion: " . ($request_data['estimated_completion'] ?? 'NULL'));
            if (empty($request_data['status_id'])) {
                error_log("Form load - WARNING: status_id is empty in main table");
            } else {
                error_log("Form load - Using status_id from main table: " . $request_data['status_id']);
            }
            if (empty($request_data['priority'])) {
                error_log("Form load - WARNING: priority is empty in main table");
            } else {
                error_log("Form load - Using priority from main table: " . $request_data['priority']);
            }
            $customer_table = $wpdb->prefix . 'nexora_customer_info';
            $customer_info = $wpdb->get_row($wpdb->prepare("SELECT * FROM $customer_table WHERE user_id = %d", $user_id), ARRAY_A);
            
            if ($customer_info) {
                $user = get_user_by('ID', $user_id);
                $customer_data = [
                    'customer_name' => $user ? $user->display_name : '',
                    'customer_email' => $user ? $user->user_email : '',
                    'customer_phone' => $customer_info['phone'] ?? '',
                    'customer_type' => $customer_info['customer_type'] ?? 'private',
                    'customer_number' => $customer_info['customer_number'] ?? '',
                    'company_name' => $customer_info['company_name'] ?? '',
                    'company_name_2' => $customer_info['company_name_2'] ?? '',
                    'street' => $customer_info['street'] ?? '',
                    'address_addition' => $customer_info['address_addition'] ?? '',
                    'postal_code' => $customer_info['postal_code'] ?? '',
                    'city' => $customer_info['city'] ?? '',
                    'country' => $customer_info['country'] ?? 'DE',
                    'industry' => $customer_info['industry'] ?? '',
                    'vat_id' => $customer_info['vat_id'] ?? '',
                    'salutation' => $customer_info['salutation'] ?? 'Herr',
                    'user_id' => $user_id
                ];
            } else {
                $user = get_user_by('ID', $user_id);
                if ($user) {
                    global $wpdb;
                    $customer_number = $wpdb->get_var($wpdb->prepare(
                        "SELECT customer_number FROM {$wpdb->users} WHERE ID = %d",
                        $user_id
                    ));
                    
                    $customer_data = [
                        'customer_name' => $user->display_name,
                        'customer_email' => $user->user_email,
                        'customer_phone' => get_user_meta($user_id, 'phone', true),
                        'customer_type' => get_user_meta($user_id, 'customer_type', true) ?: 'private',
                        'customer_number' => $customer_number ?: '',
                        'company_name' => get_user_meta($user_id, 'company_name', true),
                        'company_name_2' => get_user_meta($user_id, 'company_name_2', true),
                        'street' => get_user_meta($user_id, 'street', true),
                        'address_addition' => get_user_meta($user_id, 'postfach', true),
                        'postal_code' => get_user_meta($user_id, 'postal_code', true),
                        'city' => get_user_meta($user_id, 'city', true),
                        'country' => get_user_meta($user_id, 'country', true) ?: 'DE',
                        'industry' => get_user_meta($user_id, 'industry', true),
                        'vat_id' => get_user_meta($user_id, 'vat_id', true),
                        'salutation' => get_user_meta($user_id, 'salutation', true) ?: 'Herr',
                        'user_id' => $user_id
                    ];
                }
            }
            $device_data = [
                'brand_level_1_id' => $request_data['brand_level_1_id'] ?? 0,
                'brand_level_2_id' => $request_data['brand_level_2_id'] ?? 0,
                'brand_level_3_id' => $request_data['brand_level_3_id'] ?? 0
            ];
        }
        $device_data['custom_info'] = $custom_device_info;
    }
} else {
    $device_data = [
        'brand_level_1_id' => 0,
        'brand_level_2_id' => 0,
        'brand_level_3_id' => 0,
        'custom_info' => [
            'device_type_custom' => '',
            'device_brand_custom' => '',
            'device_series_custom' => '',
            'device_model_custom' => '',
            'description_clean' => ''
        ]
    ];
}
global $wpdb;
$services = $wpdb->get_results("SELECT id, title, cost FROM {$wpdb->prefix}nexora_services WHERE status = 'active' ORDER BY title");
$table_exists = $wpdb->get_var("SHOW TABLES LIKE '{$wpdb->prefix}nexora_services'");
$total_services = $wpdb->get_var("SELECT COUNT(*) FROM {$wpdb->prefix}nexora_services");
$active_services = $wpdb->get_var("SELECT COUNT(*) FROM {$wpdb->prefix}nexora_services WHERE status = 'active'");
if (empty($services)) {
    error_log("No services found. Table exists: " . ($table_exists ? 'yes' : 'no') . 
              ", Total: $total_services, Active: $active_services");
    if ($table_exists && $total_services == 0) {
        $default_services = [
            ['title' => 'Diagnose', 'description' => 'Gerätediagnose und Fehleranalyse', 'cost' => 25.00],
            ['title' => 'Reinigung', 'description' => 'Professionelle Gerätereinigung', 'cost' => 15.00],
            ['title' => 'Standardreparatur', 'description' => 'Standardreparatur kleinerer Defekte', 'cost' => 50.00],
            ['title' => 'Komplexe Reparatur', 'description' => 'Aufwändige Reparatur größerer Schäden', 'cost' => 120.00]
        ];
        
        foreach ($default_services as $service) {
            $wpdb->insert($wpdb->prefix . 'nexora_services', array_merge($service, [
                'status' => 'active',
                'created_at' => current_time('mysql'),
                'updated_at' => current_time('mysql')
            ]));
        }
        $services = $wpdb->get_results("SELECT id, title, cost FROM {$wpdb->prefix}nexora_services WHERE status = 'active' ORDER BY title");
        error_log("Created default services, now have: " . count($services));
    }
}
$existing_invoice_services = [];
if ($request_id > 0) {
    $complete_table = $wpdb->prefix . 'nexora_complete_service_requests';
    $complete_data = $wpdb->get_row($wpdb->prepare("SELECT services_data FROM $complete_table WHERE request_id = %d", $request_id), ARRAY_A);
    
    if ($complete_data && $complete_data['services_data']) {
        $services_json = json_decode($complete_data['services_data'], true);
        if ($services_json) {
            foreach ($services_json as &$service) {
                if (!isset($service['service_cost']) || empty($service['service_cost'])) {
                    $service_id = intval($service['service_id']);
                    if ($service_id > 0) {
                        $service_cost = $wpdb->get_var($wpdb->prepare(
                            "SELECT cost FROM {$wpdb->prefix}nexora_services WHERE id = %d",
                            $service_id
                        ));
                        if ($service_cost) {
                            $service['service_cost'] = floatval($service_cost);
                        }
                    }
                }
            }
            $existing_invoice_services = $services_json;
        }
    } else {
        $invoice_table = $wpdb->prefix . 'nexora_invoice_services';
        $existing_invoice_services = $wpdb->get_results($wpdb->prepare(
            "SELECT * FROM $invoice_table WHERE request_id = %d ORDER BY id",
            $request_id
        ), ARRAY_A);
    }
}
$status_table = $wpdb->prefix . 'nexora_service_status';
$statuses = $wpdb->get_results("SELECT * FROM $status_table WHERE 1 ORDER BY title");
?>
<!DOCTYPE html>
<html lang="de">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $request_id ? "Anfrage bearbeiten #$request_id" : "Neue Anfrage"; ?></title>
    
    
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    
    
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    
    <style>
        body { 
            font-family: 'Inter', Arial, sans-serif; 
            margin: 0; 
            background: #0B0F19; 
            color: #FFFFFF;
            overflow-x: hidden;
        }
        
        .glass-card {
            background: rgba(26, 31, 43, 0.2);
            backdrop-filter: blur(20px);
            border: 1px solid rgba(255, 255, 255, 0.2);
            border-radius: 16px;
            box-shadow: 0 8px 32px rgba(0, 0, 0, 0.3);
            padding: 24px;
            margin-bottom: 24px;
        }
        

        
        
        .vertical-nav {
            position: fixed;
            width: 80px;
            height: 100vh;
            z-index: 1000;
            background: rgba(26, 31, 43, 0.22);
            backdrop-filter: blur(20px);
            border-right: 1px solid rgba(255, 255, 255, 0.12);
            transition: all 0.4s cubic-bezier(0.4, 0, 0.2, 1);
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.35);
            padding: 16px 8px;
        }
        
        .vertical-nav:hover {
            width: 200px;
            box-shadow: 0 16px 40px rgba(0, 0, 0, 0.45);
        }
        
        .nav-toggle {
            display: flex;
            flex-direction: column;
            align-items: center;
            gap: 8px;
            padding: 16px 8px;
            border-radius: 12px;
            background: rgba(108, 93, 211, 0.2);
            margin-bottom: 20px;
            transition: all 0.3s ease;
            border-bottom: 1px solid rgba(255, 255, 255, 0.12);
        }
        
        .nav-toggle:hover {
            background: rgba(108, 93, 211, 0.4);
            transform: scale(1.05);
        }
        
        .nav-toggle i {
            font-size: 20px;
            color: #6c5dd3;
        }
        
        .nav-label {
            font-size: 12px;
            font-weight: 600;
            color: #ffffff;
            opacity: 0.9;
        }
        
        .nav-menu {
            list-style: none;
            margin: 0;
            padding: 0;
            display: flex;
            flex-direction: column;
            gap: 8px;
        }
        
        .nav-item {
            opacity: 0;
            transform: translateX(-20px);
            animation: slideInLeft 0.6s ease forwards;
        }
        
        .nav-item:nth-child(1) { animation-delay: 0.1s; }
        .nav-item:nth-child(2) { animation-delay: 0.2s; }
        .nav-item:nth-child(3) { animation-delay: 0.3s; }
        .nav-item:nth-child(4) { animation-delay: 0.4s; }
        .nav-item:nth-child(5) { animation-delay: 0.5s; }
        .nav-item:nth-child(6) { animation-delay: 0.6s; }
        .nav-item:nth-child(7) { animation-delay: 0.7s; }
        
        .nav-link {
            display: flex;
            align-items: center;
            gap: 12px;
            padding: 12px 16px;
            border-radius: 16px;
            color: #a0aec0;
            text-decoration: none;
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
            position: relative;
            overflow: hidden;
        }
        
        .nav-link::before {
            content: '';
            position: absolute;
            top: 0;
            left: -100%;
            width: 100%;
            height: 100%;
            background: linear-gradient(90deg, transparent, rgba(108, 93, 211, 0.1), transparent);
            transition: left 0.5s ease;
        }
        
        .nav-link:hover::before {
            left: 100%;
        }
        
        .nav-link:hover {
            background: rgba(108, 93, 211, 0.1);
            color: #ffffff;
            transform: translateX(4px);
        }
        
        .nav-link i {
            font-size: 18px;
            transition: all 0.3s ease;
            min-width: 20px;
        }
        
        .nav-link:hover i {
            color: #6c5dd3;
            transform: scale(1.1) rotate(5deg);
        }
        
        .nav-text {
            font-size: 14px;
            font-weight: 500;
            white-space: nowrap;
            opacity: 0;
            transition: all 0.3s ease;
            position: absolute;
            left: 45px !important;
            top: 50%;
            transform: translateY(-50%);
        }
        
        .vertical-nav:hover .nav-text {
            opacity: 1;
            transform: translateY(-50%);
        }
        
        
        .nav-badge {
            position: absolute;
            top: 8px;
            right: 8px;
            background: #e74c3c;
            color: white;
            border-radius: 50%;
            min-width: 18px;
            height: 18px;
            font-size: 11px;
            font-weight: 600;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 2px;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.3);
            animation: badgePulse 2s infinite;
        }
        
        .nav-badge.awaiting-mod {
            background: #e74c3c;
        }
        
        @keyframes badgePulse {
            0% { transform: scale(1); }
            50% { transform: scale(1.1); }
            100% { transform: scale(1); }
        }
        
        .nav-link.active {
            background: rgba(108, 93, 211, 0.3);
            color: #ffffff;
            box-shadow: 0 4px 20px rgba(108, 93, 211, 0.3);
        }
        
        @keyframes slideInLeft {
            to {
                opacity: 1;
                transform: translateX(0);
            }
        }
        
        .dashboard-container {
            padding: 24px;
            max-width: 1400px;
            margin: 0 auto;
            margin-left: 80px;
            overflow-x: hidden;
        }
        
        .dashboard-topbar {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 32px;
            padding: 16px 0;
        }
        
        .breadcrumb {
            color: #CBD5E0;
            font-size: 14px;
        }
        
        .topbar-right {
            display: flex;
            align-items: center;
            gap: 20px;
        }
        
        
        .section {
            background: rgba(26, 31, 43, 0.2);
            backdrop-filter: blur(20px);
            border: 1px solid rgba(255, 255, 255, 0.2);
            border-radius: 16px;
            box-shadow: 0 8px 32px rgba(0, 0, 0, 0.3);
            padding: 24px;
            margin-bottom: 24px;
            overflow-x: hidden;
        }
        
        .section-title {
            font-size: 20px;
            font-weight: 600;
            color: #FFFFFF;
            margin-bottom: 20px;
            padding-bottom: 10px;
            border-bottom: 2px solid rgba(255, 255, 255, 0.1);
        }
        
        .form-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 20px;
            overflow-x: hidden;
        }
        
        .form-group {
            margin-bottom: 20px;
        }
        
        .form-group label {
            display: block;
            font-weight: 500;
            color: #E2E8F0;
            margin-bottom: 8px;
            font-size: 14px;
        }
        
        .form-control {
            width: 100%;
            padding: 12px 16px;
            border: 1px solid rgba(255, 255, 255, 0.2);
            border-radius: 8px;
            background: rgba(255, 255, 255, 0.1);
            color: #FFFFFF;
            font-size: 14px;
            transition: all 0.3s ease;
            box-sizing: border-box;
            font-family: 'Inter', Arial, sans-serif;
        }
        
        .form-control:focus {
            outline: none;
            border-color: #6c5dd3;
            box-shadow: 0 0 0 3px rgba(108, 93, 211, 0.2);
            background: rgba(255, 255, 255, 0.15);
        }
        
        .form-control::placeholder {
            color: rgba(255, 255, 255, 0.5);
        }
        
        .form-control:disabled {
            opacity: 0.5;
            cursor: not-allowed;
        }
        
        
        .services-table {
            width: 100%;
            border-collapse: collapse;
            margin: 20px 0;
            overflow-x: hidden;
        }
        
        .services-table th,
        .services-table td {
            padding: 12px;
            text-align: left;
            border-bottom: 1px solid rgba(255, 255, 255, 0.1);
        }
        
        .services-table th {
            background: rgba(108, 93, 211, 0.2);
            color: #fcdc24 !important;
            font-weight: 600;
        }
        
        .services-table tr:hover {
            background: rgba(255, 255, 255, 0.05);
        }
        
        
        .footer {
            margin-top: 40px;
            padding: 20px 0;
            border-top: 1px solid rgba(255, 255, 255, 0.1);
        }
        
        .footer-buttons {
            display: flex;
            gap: 16px;
            justify-content: flex-start;
            margin-left: 160px; 
            flex-wrap: wrap;
        }
        
        .footer-buttons .btn {
            min-width: auto;
            flex-shrink: 0;
        }
        
        .form-text {
            color: #CBD5E0;
            font-size: 12px;
            margin-top: 4px;
        }
        
        
        .btn-group {
            display: flex;
            gap: 12px;
            flex-wrap: wrap;
            margin-bottom: 20px;
        }
        
        .btn {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            padding: 12px 20px;
            border: none;
            border-radius: 8px;
            font-size: 14px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
            text-decoration: none;
            min-width: 140px;
            justify-content: center;
            font-family: 'Inter', Arial, sans-serif;
        }
        
        .btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(0, 0, 0, 0.3);
        }
        
        .btn:active {
            transform: translateY(0);
        }
        
        .btn-primary {
            background: linear-gradient(135deg, #6c5dd3 0%, #8b7ae6 100%);
            color: white;
            box-shadow: 0 4px 15px rgba(108, 93, 211, 0.3);
        }
        
        .btn-success {
            background: linear-gradient(135deg, #22c55e 0%, #16a34a 100%);
            color: white;
            box-shadow: 0 4px 15px rgba(34, 197, 94, 0.3);
        }
        
        .btn-warning {
            background: linear-gradient(135deg, #f59e0b 0%, #d97706 100%);
            color: white;
            box-shadow: 0 4px 15px rgba(245, 158, 11, 0.3);
        }
        
        .btn-danger {
            background: linear-gradient(135deg, #ef4444 0%, #dc2626 100%);
            color: white;
            box-shadow: 0 4px 15px rgba(239, 68, 68, 0.3);
        }
        
        .btn-secondary {
            background: rgba(255, 255, 255, 0.1);
            color: #FFFFFF;
            border: 1px solid rgba(255, 255, 255, 0.2);
        }
        
        .btn-secondary:hover {
            background: rgba(255, 255, 255, 0.2);
            border-color: rgba(255, 255, 255, 0.3);
        }
        
        .btn-sm {
            padding: 8px 16px;
            font-size: 13px;
            min-width: auto;
        }
        
        
        .invoice-section {
            background: rgba(34, 197, 94, 0.1);
            border: 2px solid rgba(34, 197, 94, 0.3);
            border-radius: 16px;
            padding: 24px;
            margin-bottom: 24px;
        }
        
        .invoice-title {
            color: #22c55e;
            font-size: 22px;
            font-weight: 700;
            margin-bottom: 20px;
            display: flex;
            align-items: center;
            gap: 10px;
        }
        
        .invoice-title::before {
            content: "🧾";
            font-size: 24px;
        }
        
        
        .custom-field-container {
            margin-top: 8px;
            padding: 12px;
            background: rgba(239, 68, 68, 0.1);
            border: 1px solid rgba(239, 68, 68, 0.3);
            border-radius: 8px;
        }
        
        .custom-field-container label {
            font-size: 12px;
            color: #ef4444;
            margin-bottom: 4px;
        }
        
        
        @media (max-width: 768px) {
            .dashboard-container {
                margin-left: 0;
                padding: 16px;
            }
            
            .vertical-nav {
                transform: translateX(-100%);
            }
            
            .vertical-nav.mobile-open {
                transform: translateX(0);
            }
            
            .form-grid {
                grid-template-columns: 1fr;
            }
            
            .btn-group {
                flex-direction: column;
            }
            
            .btn {
                width: 100%;
                justify-content: center;
            }
        }
    </style>
</head>
<body>

    
    
    <nav class="vertical-nav">
        <div class="nav-toggle">
            <i class="fas fa-bars"></i>
            <span class="nav-label">Menu</span>
        </div>
        
        <ul class="nav-menu">
            <li class="nav-item">
                <a href="<?php echo admin_url('admin.php?page=Nexora Service Suite-main'); ?>" class="nav-link" title="Dashboard">
                    <i class="fas fa-tachometer-alt"></i>
                    <span class="nav-text">Dashboard</span>
                </a>
            </li>
            
            <li class="nav-item">
                <a href="<?php echo admin_url('admin.php?page=Nexora Service Suite-services'); ?>" class="nav-link" title="Dienstleistungen">
                    <i class="fas fa-cogs"></i>
                    <span class="nav-text">Dienstleistungen</span>
                </a>
            </li>
            
            <li class="nav-item">
                <a href="<?php echo admin_url('admin.php?page=Nexora Service Suite-service-status'); ?>" class="nav-link" title="Status">
                    <i class="fas fa-tasks"></i>
                    <span class="nav-text">Status</span>
                </a>
            </li>
            
            <li class="nav-item">
                <a href="<?php echo admin_url('admin.php?page=Nexora Service Suite-service-request'); ?>" class="nav-link active" title="Anfragen" id="nav-anfragen">
                    <i class="fas fa-clipboard-list"></i>
                    <span class="nav-text">Anfragen</span>
                    <span class="nav-badge awaiting-mod" id="nav-anfragen-badge" style="display: none;">0</span>
                </a>
            </li>
            
            <li class="nav-item">
                <a href="<?php echo admin_url('admin.php?page=Nexora Service Suite-users'); ?>" class="nav-link" title="Benutzer" id="nav-benutzer">
                    <i class="fas fa-users"></i>
                    <span class="nav-text">Benutzer</span>
                    <span class="nav-badge awaiting-mod" id="nav-benutzer-badge" style="display: none;">0</span>
                </a>
            </li>
            
            <li class="nav-item">
                <a href="<?php echo admin_url('admin.php?page=Nexora Service Suite-device-manager'); ?>" class="nav-link" title="Geräteverwaltung">
                    <i class="fas fa-mobile-alt"></i>
                    <span class="nav-text">Geräteverwaltung</span>
                </a>
            </li>
            
            <li class="nav-item">
                <a href="<?php echo admin_url('admin.php?page=Nexora Service Suite-admin-notifications'); ?>" class="nav-link" title="Nachrichten" id="nav-nachrichten">
                    <i class="fas fa-bell"></i>
                    <span class="nav-text">Nachrichten</span>
                    <span class="nav-badge awaiting-mod" id="nav-nachrichten-badge" style="display: none;">0</span>
                </a>
            </li>
            
            <li class="nav-item">
                <a href="<?php echo admin_url('admin.php?page=Nexora Service Suite-email-management'); ?>" class="nav-link" title="Email-Verwaltung">
                    <i class="fas fa-envelope"></i>
                    <span class="nav-text">Email-Verwaltung</span>
                </a>
            </li>
        </ul>
    </nav>
    
    
    <div class="dashboard-container">
        
        <div class="dashboard-topbar">
            <div class="breadcrumb">
                <i class="fas fa-clipboard-list"></i>
                <?php echo $request_id ? "Anfrage bearbeiten #$request_id" : "Neue Anfrage"; ?>
            </div>
            
            <div class="topbar-right">
                <div class="user-menu">
                    <i class="fas fa-user-circle"></i>
                    <span>Admin</span>
                </div>
            </div>
        </div>
        
        
        <div class="glass-card">
            <h1 style="margin: 0; color: #FFFFFF; font-size: 28px; font-weight: 600;">
                <?php echo $request_id ? "Anfrage bearbeiten #$request_id" : "Neue Anfrage"; ?>
            </h1>
            <p style="margin: 8px 0 0 0; color: #CBD5E0; font-size: 16px;">
                Öko Reparatur Service Management System
            </p>
        </div>
        
        
        <form id="serviceRequestForm" method="post" action="">
            <input type="hidden" name="request_id" value="<?php echo esc_attr($request_id); ?>">
            
            
            <div class="section">
                <h2 class="section-title">👤 Kundeninformationen</h2>
                <div class="form-grid">
                    <div class="form-group">
                        <label for="customer_name">Name *</label>
                        <input type="text" id="customer_name" name="customer_name" class="form-control" 
                               value="<?php echo esc_attr($customer_data['customer_name'] ?? ''); ?>" required>
                    </div>
                    
                    <div class="form-group">
                        <label for="customer_email">E-Mail *</label>
                        <input type="email" id="customer_email" name="customer_email" class="form-control" 
                               value="<?php echo esc_attr($customer_data['customer_email'] ?? ''); ?>" required>
                    </div>
                    
                    <div class="form-group">
                        <label for="customer_phone">Telefon</label>
                        <input type="tel" id="customer_phone" name="customer_phone" class="form-control" 
                               value="<?php echo esc_attr($customer_data['customer_phone'] ?? ''); ?>">
                    </div>
                    
                    <div class="form-group">
                        <label for="customer_number">Kundennummer</label>
                        <input type="text" id="customer_number" name="customer_number" class="form-control customer-number-input" 
                               value="<?php echo esc_attr($customer_data['customer_number'] ?? ''); ?>" 
                               data-original-value="<?php echo esc_attr($customer_data['customer_number'] ?? ''); ?>">
                        <small class="form-text text-muted">Kann von Administratoren bearbeitet werden</small>
                    </div>
                    
                    
                    <div class="form-group" style="grid-column: 1 / -1; margin-top: 20px; padding: 15px; background: #f8f9fa; border-radius: 8px; border: 2px solid #e9ecef;">
                        <h4 style="margin: 0 0 15px 0; color: #495057; font-size: 16px; font-weight: 600;">
                            🖨️ Manuelle Rechnungsdaten (Optional)
                        </h4>
                        <p style="margin: 0 0 15px 0; color: #6c757d; font-size: 14px;">
                            Diese Felder überschreiben die Kundendaten für die Rechnung. Wenn leer gelassen, werden die ursprünglichen Kundendaten verwendet.
                        </p>
                        <div style="display: grid; grid-template-columns: 1fr 1fr 1fr; gap: 15px;">
                            <div>
                                <label for="manual_customer_name" style="display: block; margin-bottom: 5px; font-weight: 500; color: #495057;">Name für Rechnung</label>
                                <input type="text" id="manual_customer_name" name="manual_customer_name" class="form-control" 
                                       value="<?php echo esc_attr($request_data['manual_customer_name'] ?? ''); ?>" 
                                       placeholder="Name für Rechnung">
                            </div>
                            <div>
                                <label for="manual_customer_lastname" style="display: block; margin-bottom: 5px; font-weight: 500; color: #495057;">Nachname für Rechnung</label>
                                <input type="text" id="manual_customer_lastname" name="manual_customer_lastname" class="form-control" 
                                       value="<?php echo esc_attr($request_data['manual_customer_lastname'] ?? ''); ?>" 
                                       placeholder="Nachname für Rechnung">
                            </div>
                            <div>
                                <label for="manual_customer_phone" style="display: block; margin-bottom: 5px; font-weight: 500; color: #495057;">Telefon für Rechnung</label>
                                <input type="tel" id="manual_customer_phone" name="manual_customer_phone" class="form-control" 
                                       value="<?php echo esc_attr($request_data['manual_customer_phone'] ?? ''); ?>" 
                                       placeholder="Telefon für Rechnung">
                            </div>
                        </div>
                    </div>
                    
                    <div class="form-group">
                        <label for="salutation">Anrede</label>
                        <select id="salutation" name="salutation" class="form-control">
                            <option value="Herr" <?php selected($customer_data['salutation'] ?? '', 'Herr'); ?>>Herr</option>
                            <option value="Frau" <?php selected($customer_data['salutation'] ?? '', 'Frau'); ?>>Frau</option>
                            <option value="Divers" <?php selected($customer_data['salutation'] ?? '', 'Divers'); ?>>Divers</option>
                        </select>
                    </div>
                    
                    <div class="form-group">
                        <label for="customer_type">Kundentyp</label>
                        <select id="customer_type" name="customer_type" class="form-control">
                            <option value="private" <?php selected($customer_data['customer_type'] ?? '', 'private'); ?>>Privat</option>
                            <option value="business" <?php selected($customer_data['customer_type'] ?? '', 'business'); ?>>Geschäft</option>
                        </select>
                    </div>
                    
                    <div class="form-group">
                        <label for="company_name">Firmenname</label>
                        <input type="text" id="company_name" name="company_name" class="form-control" 
                               value="<?php echo esc_attr($customer_data['company_name'] ?? ''); ?>">
                    </div>
                    
                    <div class="form-group">
                        <label for="company_name_2">Firmenname 2</label>
                        <input type="text" id="company_name_2" name="company_name_2" class="form-control" 
                               value="<?php echo esc_attr($customer_data['company_name_2'] ?? ''); ?>">
                    </div>
                    
                    <div class="form-group">
                        <label for="street">Straße</label>
                        <input type="text" id="street" name="street" class="form-control" 
                               value="<?php echo esc_attr($customer_data['street'] ?? ''); ?>">
                    </div>
                    
                    <div class="form-group">
                        <label for="address_addition">Adresszusatz</label>
                        <input type="text" id="address_addition" name="address_addition" class="form-control" 
                               value="<?php echo esc_attr($customer_data['address_addition'] ?? ''); ?>">
                    </div>
                    
                    <div class="form-group">
                        <label for="postal_code">PLZ</label>
                        <input type="text" id="postal_code" name="postal_code" class="form-control" 
                               value="<?php echo esc_attr($customer_data['postal_code'] ?? ''); ?>">
                    </div>
                    
                    <div class="form-group">
                        <label for="city">Stadt</label>
                        <input type="text" id="city" name="city" class="form-control" 
                               value="<?php echo esc_attr($customer_data['city'] ?? ''); ?>">
                    </div>
                    
                    <div class="form-group">
                        <label for="country">Land</label>
                        <input type="text" id="country" name="country" class="form-control" 
                               value="<?php echo esc_attr($customer_data['country'] ?? 'DE'); ?>">
                    </div>
                    
                    <div class="form-group">
                        <label for="industry">Branche</label>
                        <input type="text" id="industry" name="industry" class="form-control" 
                               value="<?php echo esc_attr($customer_data['industry'] ?? ''); ?>">
                    </div>
                    
                    <div class="form-group">
                        <label for="vat_id">USt-ID</label>
                        <input type="text" id="vat_id" name="vat_id" class="form-control" 
                               value="<?php echo esc_attr($customer_data['vat_id'] ?? ''); ?>">
                    </div>
                    
                    <input type="hidden" name="user_id" value="<?php echo esc_attr($customer_data['user_id'] ?? ''); ?>">
                </div>
            </div>
            
            
            <div class="section">
                <h2 class="section-title">📱 Geräteinformationen</h2>
                

                

                <div class="form-grid">
                    
                    <div class="form-group">
                        <label for="device_type_id">Gerätetyp</label>
                        <select id="device_type_id" name="device_type_id" class="form-control">
                            <option value="">-- Bitte wählen --</option>
                            <?php
                            $device_types = $wpdb->get_results("SELECT id, name FROM {$wpdb->prefix}nexora_devices WHERE type = 'type' ORDER BY name");
                            foreach ($device_types as $type): 
                                $selected = '';
                                if (isset($device_data['brand_level_1_id']) && $device_data['brand_level_1_id'] == $type->id) {
                                    $selected = 'selected';
                                }
                            ?>
                                <option value="<?php echo esc_attr($type->id); ?>" <?php echo $selected; ?>>
                                    <?php echo esc_html($type->name); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                        
                        <?php if (!empty($device_data['custom_info']['device_type_custom'])): ?>
                            <div class="custom-field-container" style="margin-top: 8px;">
                                <label for="device_type_custom" style="font-size: 12px; color: #e74c3c; margin-bottom: 4px;">Benutzerdefinierter Gerätetyp:</label>
                                <div style="display: flex; gap: 8px;">
                                    <input type="text" id="device_type_custom" name="device_type_custom" class="form-control" style="flex: 1; font-size: 12px;" 
                                           value="<?php echo esc_attr($device_data['custom_info']['device_type_custom']); ?>">
                                    <button type="button" class="btn btn-danger btn-sm remove-custom-field" data-field="device_type_custom" style="padding: 4px 8px;">
                                        <span>🗑️</span>
                                    </button>
                                </div>
                            </div>
                        <?php endif; ?>
                    </div>
                    
                    <div class="form-group">
                        <label for="device_brand_id">Marke</label>
                        <select id="device_brand_id" name="device_brand_id" class="form-control" <?php echo (empty($device_data['brand_level_1_id']) || $device_data['brand_level_1_id'] == 0) ? 'disabled' : ''; ?>>
                            <option value="">-- Bitte wählen --</option>
                            <?php 
                            if (!empty($device_data['brand_level_1_id']) && $device_data['brand_level_1_id'] != 0) {
                                $brands = $wpdb->get_results($wpdb->prepare(
                                    "SELECT id, name FROM {$wpdb->prefix}nexora_devices WHERE type = 'brand' AND parent_id = %d ORDER BY name",
                                    $device_data['brand_level_1_id']
                                ));
                                foreach ($brands as $brand): 
                                    $selected = '';
                                    if (isset($device_data['brand_level_2_id']) && $device_data['brand_level_2_id'] == $brand->id) {
                                        $selected = 'selected';
                                    }
                            ?>
                                    <option value="<?php echo esc_attr($brand->id); ?>" <?php echo $selected; ?>>
                                        <?php echo esc_html($brand->name); ?>
                                    </option>
                                <?php endforeach; 
                            }
                            ?>
                        </select>
                        
                        <?php if (!empty($device_data['custom_info']['device_brand_custom'])): ?>
                            <div class="custom-field-container" style="margin-top: 8px;">
                                <label for="device_brand_custom" style="font-size: 12px; color: #e74c3c; margin-bottom: 4px;">Benutzerdefinierte Marke:</label>
                                <div style="display: flex; gap: 8px;">
                                    <input type="text" id="device_brand_custom" name="device_brand_custom" class="form-control" style="flex: 1; font-size: 12px;" 
                                           value="<?php echo esc_attr($device_data['custom_info']['device_brand_custom']); ?>">
                                    <button type="button" class="btn btn-danger btn-sm remove-custom-field" data-field="device_brand_custom" style="padding: 4px 8px;">
                                        <span>🗑️</span>
                                    </button>
                                </div>
                            </div>
                        <?php endif; ?>
                    </div>
                    
                    <div class="form-group">
                        <label for="device_series_id">Serie</label>
                        <select id="device_series_id" name="device_series_id" class="form-control" <?php echo (empty($device_data['brand_level_2_id']) || $device_data['brand_level_2_id'] == 0) ? 'disabled' : ''; ?>>
                            <option value="">-- Bitte wählen --</option>
                            <?php 
                            if (!empty($device_data['brand_level_2_id']) && $device_data['brand_level_2_id'] != 0) {
                                $series = $wpdb->get_results($wpdb->prepare(
                                    "SELECT id, name FROM {$wpdb->prefix}nexora_devices WHERE type = 'series' AND parent_id = %d ORDER BY name",
                                    $device_data['brand_level_2_id']
                                ));
                                foreach ($series as $serie): 
                                    $selected = '';
                                    if (isset($device_data['brand_level_3_id']) && $device_data['brand_level_3_id'] == $serie->id) {
                                        $selected = 'selected';
                                    }
                            ?>
                                    <option value="<?php echo esc_attr($serie->id); ?>" <?php echo $selected; ?>>
                                        <?php echo esc_html($serie->name); ?>
                                    </option>
                                <?php endforeach; 
                            }
                            ?>
                        </select>
                        
                        <?php if (!empty($device_data['custom_info']['device_series_custom'])): ?>
                            <div class="custom-field-container" style="margin-top: 8px;">
                                <label for="device_series_custom" style="font-size: 12px; color: #e74c3c; margin-bottom: 4px;">Benutzerdefinierte Serie:</label>
                                <div style="display: flex; gap: 8px;">
                                    <input type="text" id="device_series_custom" name="device_series_custom" class="form-control" style="flex: 1; font-size: 12px;" 
                                           value="<?php echo esc_attr($device_data['custom_info']['device_series_custom']); ?>">
                                    <button type="button" class="btn btn-danger btn-sm remove-custom-field" data-field="device_series_custom" style="padding: 4px 8px;">
                                        <span>🗑️</span>
                                    </button>
                                </div>
                            </div>
                        <?php endif; ?>
                    </div>
                    
                    <div class="form-group">
                        <label for="device_model_id">Modell</label>
                        <select id="device_model_id" name="device_model_id" class="form-control" <?php echo (empty($device_data['brand_level_3_id']) || $device_data['brand_level_3_id'] == 0) ? 'disabled' : ''; ?>>
                            <option value="">-- Bitte wählen --</option>
                            <?php 
                            if (!empty($device_data['brand_level_3_id']) && $device_data['brand_level_3_id'] != 0) {
                                $models = $wpdb->get_results($wpdb->prepare(
                                    "SELECT id, name FROM {$wpdb->prefix}nexora_devices WHERE type = 'model' AND parent_id = %d ORDER BY name",
                                    $device_data['brand_level_3_id']
                                ));
                                foreach ($models as $model): 
                                    $selected = '';
                                    if (isset($request_data['model']) && $request_data['model'] == $model->name) {
                                        $selected = 'selected';
                                    }
                            ?>
                                    <option value="<?php echo esc_attr($model->id); ?>" <?php echo $selected; ?>>
                                        <?php echo esc_html($model->name); ?>
                                    </option>
                                <?php endforeach; 
                            }
                            ?>
                        </select>
                        
                        <?php if (!empty($device_data['custom_info']['device_model_custom'])): ?>
                            <div class="custom-field-container" style="margin-top: 8px;">
                                <label for="device_model_custom" style="font-size: 12px; color: #e74c3c; margin-bottom: 4px;">Benutzerdefiniertes Modell:</label>
                                <div style="display: flex; gap: 8px;">
                                    <input type="text" id="device_model_custom" name="device_model_custom" class="form-control" style="flex: 1; font-size: 12px;" 
                                           value="<?php echo esc_attr($device_data['custom_info']['device_model_custom']); ?>">
                                    <button type="button" class="btn btn-danger btn-sm remove-custom-field" data-field="device_model_custom" style="padding: 4px 8px;">
                                        <span>🗑️</span>
                                    </button>
                                </div>
                            </div>
                        <?php endif; ?>
                    </div>
                    
                    <div class="form-group">
                        <label for="device_serial">Seriennummer</label>
                        <input type="text" id="device_serial" name="device_serial" class="form-control" 
                               value="<?php echo esc_attr($request_data['serial'] ?? ''); ?>">
                    </div>
                    

                    
                    <div class="form-group" style="grid-column: span 2;">
                        <label for="device_description">Problembeschreibung</label>
                        <textarea id="device_description" name="device_description" class="form-control" rows="4"><?php echo esc_textarea($device_data['custom_info']['description_clean'] ?? ($request_data['description'] ?? '')); ?></textarea>
                    </div>
                    
                    
                    <?php if ($request_id > 0): ?>
                        <div class="form-group" style="grid-column: span 2;">
                            <label>📸 Gerätebilder</label>
                            <div class="device-images-container">
                                <?php
                                $attachments_table = $wpdb->prefix . 'nexora_request_attachments';
                                $device_images = $wpdb->get_results($wpdb->prepare(
                                    "SELECT * FROM $attachments_table WHERE request_id = %d ORDER BY created_at ASC",
                                    $request_id
                                ));
                                
                                if (!empty($device_images)): ?>
                                    <div class="images-grid">
                                        <?php foreach ($device_images as $image): ?>
                                            <div class="image-item">
                                                <img src="<?php echo esc_url(wp_upload_dir()['baseurl'] . '/Nexora Service Suite-attachments/' . basename($image->file_path)); ?>" 
                                                     alt="<?php echo esc_attr($image->file_name); ?>"
                                                     class="device-image"
                                                     onclick="openImageModal(this.src, '<?php echo esc_js($image->file_name); ?>')">
                                                <div class="image-info">
                                                    <span class="image-name"><?php echo esc_html($image->file_name); ?></span>
                                                    <button type="button" class="btn btn-danger btn-sm delete-image" 
                                                            data-image-id="<?php echo esc_attr($image->id); ?>"
                                                            onclick="deleteDeviceImage(<?php echo esc_js($image->id); ?>)">
                                                        🗑️
                                                    </button>
                                                </div>
                                            </div>
                                        <?php endforeach; ?>
                                    </div>
                                <?php else: ?>
                                    <p class="no-images">Keine Bilder vorhanden</p>
                                <?php endif; ?>
                            </div>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
            
            
            <div class="invoice-section">
                <h2 class="invoice-title">Rechnungsservices</h2>
                
                
                <div class="btn-group">
                    <button type="button" id="addServiceBtn" class="btn btn-success">
                        <span>➕</span>
                        <span>Service hinzufügen</span>
                    </button>
                    
                    <button type="button" id="saveServicesBtn" class="btn btn-primary">
                        <span>💾</span>
                        <span>Services speichern</span>
                    </button>
                    
                    <button type="button" id="refreshApprovalBtn" class="btn btn-info">
                        <span>🔄</span>
                        <span>Status aktualisieren</span>
                    </button>
                    
                    <button type="button" id="testBtn" class="btn btn-warning">
                        <span>🧪</span>
                        <span>Test</span>
                    </button>
                    
                    <button type="button" id="clearBtn" class="btn btn-secondary">
                        <span>🗑️</span>
                        <span>Löschen</span>
                    </button>
                </div>
                
                
                <table class="services-table" id="servicesTable" style="overflow-x: hidden;">
                    <thead>
                        <tr>
                            <th>Service</th>
                            <th>Preis (€)</th>
                            <th>Anzahl</th>
                            <th>Beschreibung</th>
                            <th>Aktionen</th>
                        </tr>
                    </thead>
                    <tbody id="servicesTableBody">
                        
                    </tbody>
                </table>
                
                
                <div class="summary-box" id="summaryBox">
                    <h3 class="summary-title">
                        <span>📊</span>
                        <span>Zusammenfassung</span>
                    </h3>
                    <div class="summary-stats" id="summaryStats">
                        
                    </div>
                </div>
                

            </div>
            
            
            <?php if ($request_id): ?>
                <div class="section">
                    <h2 class="section-title">💬 Chat mit Kunde</h2>
                    <?php 
                    $request_id = $request_id;
                    include NEXORA_PLUGIN_DIR . 'templates/chat/admin-chat-box.php';
                    ?>
                </div>
            <?php endif; ?>
            
            
            <div class="section">
                <h2 class="section-title">📊 Status & Priorität</h2>
                <div class="form-grid">
                    <div class="form-group">
                        <label for="status">Status</label>
                        <div style="display: flex; gap: 10px; align-items: center;">
                            <select id="status" name="status" class="form-control" style="flex: 1;">
                                <option value="">-- Bitte wählen --</option>
                                <?php
                                error_log("Form display - Current status_id: " . ($request_data['status_id'] ?? 'NULL'));
                                error_log("Form display - Available statuses: " . count($statuses));
                                error_log("Form display - Request data keys: " . implode(', ', array_keys($request_data)));
                                if (empty($request_data['status_id'])) {
                                    error_log("Form display - WARNING: status_id is empty, trying to get from database");
                                    $direct_status = $wpdb->get_var($wpdb->prepare(
                                        "SELECT status_id FROM {$wpdb->prefix}nexora_service_requests WHERE id = %d",
                                        $request_id
                                    ));
                                    if ($direct_status) {
                                        $request_data['status_id'] = $direct_status;
                                        error_log("Form display - Fixed status_id from direct DB query: " . $direct_status);
                                    }
                                }
                                
                                foreach ($statuses as $status): 
                                    $is_selected = ($request_data['status_id'] ?? '') == $status->id;
                                    if ($is_selected) {
                                        error_log("Form display - Status {$status->id} ({$status->title}) is selected");
                                    }
                                ?>
                                    <option value="<?php echo esc_attr($status->id); ?>" 
                                            <?php selected($request_data['status_id'] ?? '', $status->id); ?>>
                                        <?php echo esc_html($status->title); ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                            <button type="button" id="refreshStatusBtn" class="btn btn-secondary" style="padding: 8px 12px; white-space: nowrap;" title="Status aus der Liste aktualisieren">
                                🔄 Aktualisieren
                            </button>
                        </div>
                    </div>
                    
                    <div class="form-group">
                        <label for="priority">Priorität</label>
                        <select id="priority" name="priority" class="form-control">
                            <option value="">-- Bitte wählen --</option>
                            <?php
                            $priorities = [
                                ['value' => 'low', 'name' => 'Niedrig'],
                                ['value' => 'medium', 'name' => 'Mittel'],
                                ['value' => 'high', 'name' => 'Hoch'],
                                ['value' => 'urgent', 'name' => 'Dringend']
                            ];
                            foreach ($priorities as $priority) {
                                $selected = ($request_data['priority'] ?? '') == $priority['value'] ? 'selected' : '';
                                echo "<option value='{$priority['value']}' {$selected}>{$priority['name']}</option>";
                            }
                            ?>
                        </select>
                    </div>
                    
                    <div class="form-group">
                        <label for="assigned_to">Zugewiesen an</label>
                        <select id="assigned_to" name="assigned_to" class="form-control">
                            <option value="">-- Bitte wählen --</option>
                            <?php
                            $users = get_users(['role__in' => ['administrator', 'editor']]);
                            foreach ($users as $user) {
                                $selected = ($request_data['assigned_to'] ?? '') == $user->ID ? 'selected' : '';
                                echo "<option value='{$user->ID}' {$selected}>{$user->display_name}</option>";
                            }
                            ?>
                        </select>
                    </div>
                    
                    <div class="form-group">
                        <label for="estimated_completion">Geschätzte Fertigstellung</label>
                        <input type="date" id="estimated_completion" name="estimated_completion" class="form-control" 
                               value="<?php echo esc_attr($request_data['estimated_completion'] ?? ''); ?>">
                    </div>
                </div>
            </div>
        </form>
    </div>
    
    <div class="footer">
        <div class="footer-buttons">
            <button type="submit" form="serviceRequestForm" class="btn btn-primary">
                <span>💾</span>
                <span>Speichern</span>
            </button>
            <button type="button" id="createInvoiceBtn" class="btn btn-success">
                <span>🧾</span>
                <span>Rechnung erstellen</span>
            </button>
            <a href="<?php echo admin_url('admin.php?page=Nexora Service Suite-service-request'); ?>" class="btn btn-secondary">
                <span>⬅️</span>
                <span>Zurück</span>
            </a>
        </div>
    </div>
    
    
    <div id="imageModal" class="image-modal">
        <span class="image-modal-close" onclick="closeImageModal()">&times;</span>
        <div class="image-modal-content">
            <img id="modalImage" src="" alt="">
        </div>
    </div>

    <script>
        function openImageModal(src, filename) {
            document.getElementById('modalImage').src = src;
            document.getElementById('modalImage').alt = filename;
            document.getElementById('imageModal').style.display = 'block';
        }
        
        function closeImageModal() {
            document.getElementById('imageModal').style.display = 'none';
        }
        window.onclick = function(event) {
            const modal = document.getElementById('imageModal');
            if (event.target === modal) {
                closeImageModal();
            }
        }
        function deleteDeviceImage(imageId) {
            if (confirm('Sind Sie sicher, dass Sie dieses Bild löschen möchten?')) {
                jQuery.ajax({
                    url: '<?php echo admin_url('admin-ajax.php'); ?>',
                    type: 'POST',
                    data: {
                        action: 'delete_device_image',
                        image_id: imageId,
                        nonce: '<?php echo wp_create_nonce('delete_device_image_nonce'); ?>'
                    },
                    success: function(response) {
                        if (response.success) {
                            jQuery(`[data-image-id="${imageId}"]`).closest('.image-item').remove();
                            if (jQuery('.image-item').length === 0) {
                                jQuery('.images-grid').html('<p class="no-images">Keine Bilder vorhanden</p>');
                            }
                            
                            alert('Bild erfolgreich gelöscht!');
                        } else {
                            alert('Fehler beim Löschen des Bildes: ' + response.data);
                        }
                    },
                    error: function() {
                        alert('Fehler beim Löschen des Bildes.');
                    }
                });
            }
        }
        class InvoiceServicesManager {
            constructor() {
                this.services = [];
                this.rowCounter = 1;
                this.availableServices = <?php echo json_encode($services); ?>;
                this.debugLogs = [];
                this.userBenefitType = '<?php echo $user_benefit_type ?? ''; ?>';
                this.userBenefitPercentage = <?php echo $user_benefit_percentage ?? 0; ?>;
                
                this.init();
            }
            
            init() {
                this.log('InvoiceServicesManager initialisiert');
                this.setupEventListeners();
                this.loadExistingServices();
                this.updateSummary();
            }
            
            log(message, type = 'INFO') {
                const timestamp = new Date().toLocaleTimeString();
                const logEntry = `[${timestamp}] ${type}: ${message}`;
                this.debugLogs.push(logEntry);
                
                if (this.debugLogs.length > 50) {
                    this.debugLogs.shift();
                }
                
                console.log(logEntry);
            }
            

            
            setupEventListeners() {
                document.getElementById('addServiceBtn').addEventListener('click', () => {
                    this.log('Service hinzufügen Button geklickt');
                    this.addServiceRow();
                });
                document.getElementById('saveServicesBtn').addEventListener('click', () => {
                    this.log('Services speichern Button geklickt');
                    this.saveServices();
                });
                document.getElementById('testBtn').addEventListener('click', () => {
                    this.log('Test Button geklickt');
                    this.runTests();
                });
                document.getElementById('clearBtn').addEventListener('click', () => {
                    this.log('Löschen Button geklickt');
                    this.clearAll();
                });
                document.getElementById('refreshApprovalBtn').addEventListener('click', () => {
                    this.log('Status aktualisieren Button geklickt');
                    this.refreshApprovalStatus();
                });
                document.getElementById('serviceRequestForm').addEventListener('submit', (e) => {
                    e.preventDefault();
                    this.log('Hauptformular Speichern Button geklickt');
                    this.saveMainForm();
                });
                document.querySelectorAll('.customer-number-input').forEach(function(input) {
                    input.addEventListener('change', function() {
                        const originalValue = this.getAttribute('data-original-value');
                        const currentValue = this.value;
                        
                        if (originalValue && currentValue !== originalValue) {
                            if (!confirm('Warnung: Sie ändern die Kundennummer von "' + originalValue + '" zu "' + currentValue + '".\n\nSind Sie sicher, dass Sie fortfahren möchten?')) {
                                this.value = originalValue;
                                return;
                            }
                            this.setAttribute('data-original-value', currentValue);
                        }
                    });
                });
                
                this.log('Event Listeners eingerichtet');
            }
            
            addServiceRow(serviceData = null) {
                this.log(`Neue Service-Zeile hinzufügen (ID: ${this.rowCounter})`);
                
                const rowId = `service-row-${this.rowCounter++}`;
                const row = document.createElement('tr');
                row.id = rowId;
                row.className = 'service-row';
                const serviceOptions = this.availableServices.map(service => 
                    `<option value="${service.id}" data-cost="${service.cost || 0}">${service.title}</option>`
                ).join('');
                
                row.innerHTML = `
                    <td>
                        <select class="service-select" data-row="${rowId}">
                            <option value="">-- Service auswählen --</option>
                            ${serviceOptions}
                        </select>
                    </td>
                    <td>
                        <input type="number" class="service-cost" step="0.01" min="0" value="0.00" readonly>
                    </td>
                    <td>
                        <input type="number" class="service-quantity" min="1" value="1">
                    </td>
                    <td>
                        <textarea class="service-description" rows="2" placeholder="Beschreibung..."></textarea>
                    </td>
                    <td>
                        <div class="service-actions" style="display: flex; gap: 8px; flex-wrap: wrap;">
                            <button type="button" class="btn btn-danger remove-service-btn" data-row="${rowId}" style="font-size: 11px; padding: 4px 8px;">
                                🗑️ Löschen
                            </button>
                            <button type="button" class="send-approval-btn" data-row="${rowId}" style="font-size: 11px; padding: 4px 8px;" title="Zur Freigabe senden">
                                📤 Freigabe
                            </button>
                        </div>
                    </td>
                `;
                
                document.getElementById('servicesTableBody').appendChild(row);
                this.setupRowEventListeners(row, rowId);
                if (serviceData) {
                    row.querySelector('.service-select').value = serviceData.service_id;
                    row.querySelector('.service-cost').value = serviceData.service_cost || '0.00';
                    row.querySelector('.service-quantity').value = serviceData.quantity || '1';
                    row.querySelector('.service-description').value = serviceData.description || '';
                    this.log(`Initialdaten für Zeile ${rowId} gesetzt`);
                }
                this.updateRowApprovalStatus(row, rowId);
                
                this.updateSummary();
                this.log(`Service-Zeile ${rowId} hinzugefügt`);
            }
            
            setupRowEventListeners(row, rowId) {
                const serviceSelect = row.querySelector('.service-select');
                serviceSelect.addEventListener('change', (e) => {
                    const selectedValue = e.target.value;
                    this.log(`Service geändert in Zeile ${rowId}: ${selectedValue}`);
                    
                    const selectedService = this.availableServices.find(s => s.id == selectedValue);
                    const costInput = row.querySelector('.service-cost');
                    
                    if (selectedService) {
                        costInput.value = selectedService.cost || 0;
                        this.log(`Preis für ${selectedService.title}: ${selectedService.cost}`);
                    } else {
                        costInput.value = '0.00';
                        this.log('Kein Service ausgewählt');
                    }
                    this.updateRowApprovalStatus(row, rowId);
                    
                    this.updateSummary();
                });
                row.querySelector('.service-quantity').addEventListener('input', () => {
                    this.updateSummary();
                });
                
                row.querySelector('.service-description').addEventListener('input', () => {
                    this.updateSummary();
                });
                row.querySelector('.remove-service-btn').addEventListener('click', () => {
                    this.log(`Zeile ${rowId} löschen`);
                    row.remove();
                    this.updateSummary();
                });
                const approvalBtn = row.querySelector('.send-approval-btn');
                if (approvalBtn) {
                    approvalBtn.addEventListener('click', () => {
                        this.handleSendForApproval(row, rowId);
                    });
                }
            }
            
            updateSummary() {
                const rows = document.querySelectorAll('#servicesTableBody tr');
                let totalServices = 0;
                let totalQuantity = 0;
                let totalCost = 0;
                
                this.log(`Zusammenfassung aktualisieren - ${rows.length} Zeilen`);
                
                rows.forEach((row, index) => {
                    const cost = parseFloat(row.querySelector('.service-cost').value) || 0;
                    const quantity = parseInt(row.querySelector('.service-quantity').value) || 0;
                    const serviceId = row.querySelector('.service-select').value;
                    
                    this.log(`Zeile ${index + 1}: Service=${serviceId}, Preis=${cost}, Anzahl=${quantity}`);
                    
                    if (serviceId) {
                        totalServices++;
                        totalQuantity += quantity;
                        totalCost += (cost * quantity);
                    }
                });
                let benefitAmount = 0;
                let finalCost = totalCost;
                if (this.userBenefitPercentage > 0) {
                    if (this.userBenefitType === 'discount') {
                        benefitAmount = (totalCost * this.userBenefitPercentage) / 100;
                        finalCost = totalCost - benefitAmount;
                    } else if (this.userBenefitType === 'commission') {
                        benefitAmount = (totalCost * this.userBenefitPercentage) / 100;
                    }
                }
                
                this.log(`Endsumme: ${totalServices} Services, ${totalQuantity} Anzahl, €${totalCost.toFixed(2)} Gesamtpreis`);
                if (this.userBenefitPercentage > 0) {
                    const benefitTypeText = this.userBenefitType === 'discount' ? 'Rabatt' : 'Provision';
                    this.log(`${benefitTypeText}: ${this.userBenefitPercentage}% = €${benefitAmount.toFixed(2)}, Endpreis: €${finalCost.toFixed(2)}`);
                }
                
                const summaryBox = document.getElementById('summaryBox');
                const summaryStats = document.getElementById('summaryStats');
                
                if (totalServices > 0) {
                    let summaryHTML = `
                        <div class="stat-item">
                            <div class="stat-value">${totalServices}</div>
                            <div class="stat-label">Services</div>
                        </div>
                        <div class="stat-item">
                            <div class="stat-value">${totalQuantity}</div>
                            <div class="stat-label">Anzahl</div>
                        </div>`;
                    
                    if (this.userBenefitPercentage > 0) {
                        const benefitTypeText = this.userBenefitType === 'discount' ? 'Rabatt' : 'Provision';
                        const benefitColor = this.userBenefitType === 'discount' ? '#28a745' : '#f39c12';
                        
                        if (this.userBenefitType === 'discount') {
                            summaryHTML += `
                            <div class="stat-item">
                                <div class="stat-value">€${totalCost.toFixed(2)}</div>
                                <div class="stat-label">Zwischensumme</div>
                            </div>
                            <div class="stat-item" style="color: ${benefitColor};">
                                <div class="stat-value">-€${benefitAmount.toFixed(2)}</div>
                                <div class="stat-label">${benefitTypeText} (${this.userBenefitPercentage}%)</div>
                            </div>
                            <div class="stat-item" style="color: #007bff; font-weight: bold;">
                                <div class="stat-value">€${finalCost.toFixed(2)}</div>
                                <div class="stat-label">Gesamtpreis (inkl. ${benefitTypeText})</div>
                            </div>`;
                        } else {
                            summaryHTML += `
                            <div class="stat-item">
                                <div class="stat-value">€${totalCost.toFixed(2)}</div>
                                <div class="stat-label">Gesamtpreis</div>
                            </div>
                            <div class="stat-item" style="color: ${benefitColor};">
                                <div class="stat-value">€${benefitAmount.toFixed(2)}</div>
                                <div class="stat-label">${benefitTypeText} (${this.userBenefitPercentage}%)</div>
                            </div>`;
                        }
                    } else {
                        summaryHTML += `
                        <div class="stat-item">
                            <div class="stat-value">€${totalCost.toFixed(2)}</div>
                            <div class="stat-label">Gesamtpreis</div>
                        </div>`;
                    }
                    
                    summaryStats.innerHTML = summaryHTML;
                    summaryBox.style.display = 'block';
                } else {
                    summaryBox.style.display = 'none';
                }
            }
            
            saveServices() {
                this.log('Services speichern gestartet');
                
                const services = [];
                const rows = document.querySelectorAll('#servicesTableBody tr');
                
                rows.forEach((row, index) => {
                    const serviceId = row.querySelector('.service-select').value;
                    if (serviceId) {
                        const serviceData = {
                            service_id: serviceId,
                            service_cost: row.querySelector('.service-cost').value,
                            quantity: row.querySelector('.service-quantity').value,
                            description: row.querySelector('.service-description').value
                        };
                        services.push(serviceData);
                        this.log(`Service ${index + 1}: ${JSON.stringify(serviceData)}`);
                    }
                });
                
                this.log(`${services.length} Services zum Speichern bereit`);
                const formData = new FormData(document.getElementById('serviceRequestForm'));
                const requestId = <?php echo $request_id; ?>;
                const saveData = {
                    action: 'save_service_request_data',
                    request_id: requestId,
                    customer_data: {
                        customer_name: formData.get('customer_name'),
                        customer_email: formData.get('customer_email'),
                        customer_phone: formData.get('customer_phone'),
                        customer_type: formData.get('customer_type'),
                        company_name: formData.get('company_name'),
                        street: formData.get('street'),
                        postal_code: formData.get('postal_code'),
                        city: formData.get('city'),
                        country: formData.get('country'),
                        vat_id: formData.get('vat_id'),
                        user_id: formData.get('user_id')
                    },
                    device_data: {
                        device_type_id: formData.get('device_type_id'),
                        device_brand_id: formData.get('device_brand_id'),
                        device_series_id: formData.get('device_series_id'),
                        device_model_id: formData.get('device_model_id'),
                        device_type_custom: formData.get('device_type_custom'),
                        device_brand_custom: formData.get('device_brand_custom'),
                        device_series_custom: formData.get('device_series_custom'),
                        device_model_custom: formData.get('device_model_custom'),
                        device_serial: formData.get('device_serial'),
                        device_description: formData.get('device_description')
                    },
                    status_data: {
                        status: formData.get('status') || '<?php echo $request_data['status_id'] ?? ''; ?>',
                        priority: formData.get('priority') || '<?php echo $request_data['priority'] ?? ''; ?>',
                        assigned_to: formData.get('assigned_to') || '<?php echo $request_data['assigned_to'] ?? ''; ?>',
                        estimated_completion: formData.get('estimated_completion') || '<?php echo $request_data['estimated_completion'] ?? ''; ?>'
                    },
                    services: services,
                    nonce: '<?php echo wp_create_nonce("save_service_request_nonce"); ?>'
                };
                
                this.log('Daten für Speicherung vorbereitet');
                jQuery.ajax({
                    url: '<?php echo admin_url('admin-ajax.php'); ?>',
                    type: 'POST',
                    data: saveData,
                    success: (response) => {
                        this.log('AJAX Response erhalten: ' + JSON.stringify(response));
                        if (response.success) {
                            this.log('Daten erfolgreich gespeichert', 'SUCCESS');
                            alert('Daten erfolgreich gespeichert!');
                        } else {
                            this.log('Fehler beim Speichern: ' + response.data, 'ERROR');
                            alert('Fehler beim Speichern: ' + response.data);
                        }
                    },
                    error: (xhr, status, error) => {
                        this.log('AJAX Fehler: ' + error, 'ERROR');
                        alert('Fehler beim Speichern der Daten.');
                    }
                });
            }
            
            saveMainForm() {
                this.log('Hauptformular speichern gestartet');
                const formData = new FormData(document.getElementById('serviceRequestForm'));
                const requestId = <?php echo $request_id; ?>;
                const services = [];
                const rows = document.querySelectorAll('#servicesTableBody tr');
                
                rows.forEach((row, index) => {
                    const serviceId = row.querySelector('.service-select').value;
                    if (serviceId) {
                        const serviceData = {
                            service_id: serviceId,
                            service_cost: row.querySelector('.service-cost').value,
                            quantity: row.querySelector('.service-quantity').value,
                            description: row.querySelector('.service-description').value
                        };
                        services.push(serviceData);
                        this.log(`Service ${index + 1} für Hauptformular: ${JSON.stringify(serviceData)}`);
                    }
                });
                
                this.log(`${services.length} Services vom Hauptformular gefunden`);
                const saveData = {
                    action: 'nexora_update_service_request',
                    id: requestId,
                    serial: formData.get('device_serial'),
                    model: formData.get('device_model_custom') || formData.get('device_model_id'),
                    description: formData.get('device_description'),
                    user_id: formData.get('user_id'),
                    status_id: formData.get('status'),
                    brand_level_1_id: formData.get('device_type_id'),
                    brand_level_2_id: formData.get('device_brand_id'),
                    brand_level_3_id: formData.get('device_series_id'),
                    manual_customer_name: formData.get('manual_customer_name'),
                    manual_customer_lastname: formData.get('manual_customer_lastname'),
                    manual_customer_phone: formData.get('manual_customer_phone'),
                    additional_services: services.map(service => ({
                        id: service.service_id,
                        title: service.description,
                        qty: service.quantity,
                        note: service.description
                    })),
                    nonce: '<?php echo wp_create_nonce("nexora_nonce"); ?>'
                };
                
                this.log('Hauptformular Daten für Speicherung vorbereitet');
                jQuery.ajax({
                    url: '<?php echo admin_url('admin-ajax.php'); ?>',
                    type: 'POST',
                    data: saveData,
                    success: (response) => {
                        this.log('AJAX Response erhalten: ' + JSON.stringify(response));
                        if (response.success) {
                            this.log('Hauptformular erfolgreich gespeichert', 'SUCCESS');
                            const newStatus = formData.get('status');
                            if (newStatus) {
                                this.updateCurrentPageStatus(newStatus);
                            }
                            
                            alert('Daten erfolgreich gespeichert!');
                        } else {
                            this.log('Fehler beim Speichern: ' + response.data, 'ERROR');
                            alert('Fehler beim Speichern: ' + response.data);
                        }
                    },
                    error: (xhr, status, error) => {
                        this.log('AJAX Fehler: ' + error, 'ERROR');
                        alert('Fehler beim Speichern der Daten.');
                    }
                });
            }
            
            updateCurrentPageStatus(newStatusId) {
                const statusSelect = document.getElementById('status');
                if (statusSelect) {
                    statusSelect.value = newStatusId;
                    this.log(`Status dropdown updated to: ${newStatusId}`);
                }
                this.log(`Current page status updated to ID: ${newStatusId}`);
            }
            
            loadExistingServices() {
                const existingServices = <?php echo json_encode($existing_invoice_services); ?>;
                this.log(`${existingServices.length} bestehende Services laden`);
                
                existingServices.forEach(service => {
                    if (!service.service_cost && service.service_id) {
                        const serviceSelect = document.querySelector(`#Nexora Service Suite-request-service option[value="${service.service_id}"]`);
                        if (serviceSelect) {
                            service.service_cost = serviceSelect.dataset.cost || 0;
                        }
                    }
                    this.addServiceRow(service);
                });
                
                this.log('Bestehende Services geladen');
                setTimeout(() => {
                    this.updateSummary();
                    this.updateAllRowsApprovalStatus();
                }, 100);
            }
            
            updateAllRowsApprovalStatus() {
                const rows = document.querySelectorAll('#servicesTableBody tr');
                rows.forEach((row, index) => {
                    const rowId = row.id;
                    this.updateRowApprovalStatus(row, rowId);
                });
            }
            refreshApprovalStatus() {
                this.log('Refreshing approval status for all rows');
                this.updateAllRowsApprovalStatus();
            }
            
            clearAll() {
                this.log('Alle Services löschen');
                document.getElementById('servicesTableBody').innerHTML = '';
                this.updateSummary();
                this.log('Alle Services gelöscht');
            }
            
            handleSendForApproval(row, rowId) {
                const serviceSelect = row.querySelector('.service-select');
                const serviceCost = row.querySelector('.service-cost');
                const serviceQuantity = row.querySelector('.service-quantity');
                const serviceDescription = row.querySelector('.service-description');
                
                if (!serviceSelect.value) {
                    alert('Bitte wählen Sie zuerst einen Service aus.');
                    return;
                }
                const selectedService = this.availableServices.find(s => s.id == serviceSelect.value);
                if (!selectedService) {
                    alert('Service nicht gefunden.');
                    return;
                }
                
                const serviceData = {
                    id: selectedService.id,
                    title: selectedService.title,
                    cost: serviceCost.value,
                    quantity: serviceQuantity.value,
                    description: serviceDescription.value
                };
                const requestId = this.getRequestId();
                if (!requestId) {
                    alert('Request ID nicht gefunden.');
                    return;
                }
                
                this.log(`Service zur Freigabe senden: ${selectedService.title} (Request: ${requestId})`);
                if (typeof openServiceApprovalModal === 'function') {
                    openServiceApprovalModal(requestId, selectedService.id, serviceData);
                    setTimeout(() => {
                        this.updateRowApprovalStatus(row, rowId);
                    }, 1000);
                } else {
                    alert('Service Approval System ist nicht verfügbar.');
                }
            }
            
            getRequestId() {
                const urlParams = new URLSearchParams(window.location.search);
                let requestId = urlParams.get('id') || urlParams.get('request_id');
                
                if (!requestId) {
                    const form = document.getElementById('serviceRequestForm');
                    const hiddenInput = form ? form.querySelector('input[name="request_id"]') : null;
                    requestId = hiddenInput ? hiddenInput.value : null;
                }
                
                return requestId;
            }
            
            updateRowApprovalStatus(row, rowId) {
                const requestId = this.getRequestId();
                const serviceSelect = row.querySelector('.service-select');
                const approvalBtn = row.querySelector('.send-approval-btn');
                
                if (!requestId || !serviceSelect.value) {
                    this.setRowStatus(row, 'none', 'Freigabe');
                    return;
                }
                this.checkApprovalStatus(requestId, serviceSelect.value, (status) => {
                    this.setRowStatus(row, status, this.getStatusText(status));
                });
            }
            
            setRowStatus(row, status, buttonText) {
                row.classList.remove('status-pending', 'status-approved', 'status-rejected', 'status-none');
                row.classList.add(`status-${status}`);
                const approvalBtn = row.querySelector('.send-approval-btn');
                if (approvalBtn) {
                    approvalBtn.classList.remove('status-pending', 'status-approved', 'status-rejected');
                    approvalBtn.classList.add(`status-${status}`);
                    approvalBtn.innerHTML = this.getStatusIcon(status) + ' ' + buttonText;
                    if (status === 'approved' || status === 'rejected') {
                        approvalBtn.disabled = true;
                        approvalBtn.title = `Status: ${buttonText}`;
                    } else {
                        approvalBtn.disabled = false;
                        approvalBtn.title = status === 'pending' ? 'Ausstehend' : 'Zur Freigabe senden';
                    }
                }
            }
            
            getStatusText(status) {
                const statusTexts = {
                    'pending': 'Ausstehend',
                    'approved': 'Genehmigt',
                    'rejected': 'Abgelehnt',
                    'none': 'Freigabe'
                };
                return statusTexts[status] || 'Freigabe';
            }
            
            getStatusIcon(status) {
                const statusIcons = {
                    'pending': '⏳',
                    'approved': '✅',
                    'rejected': '❌',
                    'none': '📤'
                };
                return statusIcons[status] || '📤';
            }
            
            checkApprovalStatus(requestId, serviceId, callback) {
                if (typeof nexora_ajax === 'undefined') {
                    callback('none');
                    return;
                }
                const formData = new FormData();
                formData.append('action', 'nexora_get_service_approval_status');
                formData.append('request_id', requestId);
                formData.append('service_id', serviceId);
                formData.append('table_type', 'complete_service_requests');
                formData.append('nonce', nexora_ajax.nonce);
                
                fetch(nexora_ajax.ajax_url, {
                    method: 'POST',
                    body: formData
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        callback(data.data.status);
                    } else {
                        callback('none');
                    }
                })
                .catch(error => {
                    console.error('Error checking approval status:', error);
                    callback('none');
                });
            }
            
            runTests() {
                this.log('Tests ausführen');
                this.log('Test 1: Service hinzufügen');
                this.addServiceRow();
                this.log('Test 2: Zusammenfassung aktualisieren');
                this.updateSummary();
                this.log('Test 3: Services speichern');
                this.saveServices();
                
                this.log('Alle Tests abgeschlossen', 'SUCCESS');
                alert('Tests abgeschlossen! Überprüfen Sie das Debug-Panel.');
            }
        }
        function checkForStatusUpdates() {
            const statusChange = localStorage.getItem('nexora_status_change');
            if (statusChange) {
                try {
                    const change = JSON.parse(statusChange);
                    const currentRequestId = <?php echo $request_id; ?>;
                    
                    console.log('Checking status change:', change);
                    console.log('Current request ID:', currentRequestId);
                    if (change.request_id == currentRequestId && (Date.now() - change.timestamp) < 30000) {
                        const statusSelect = document.getElementById('status');
                        if (statusSelect && statusSelect.value != change.new_status_id) {
                            console.log('Updating status from', statusSelect.value, 'to', change.new_status_id);
                            statusSelect.value = change.new_status_id;
                            alert('Status wurde automatisch von der Anfragen-Liste aktualisiert.');
                            localStorage.removeItem('nexora_status_change');
                        }
                    } else if (change.request_id == currentRequestId) {
                        console.log('Status change too old, ignoring');
                        localStorage.removeItem('nexora_status_change');
                    }
                } catch (e) {
                    console.error('Error parsing status change from localStorage:', e);
                }
            }
        }
        function refreshStatusFromDatabase() {
            const currentRequestId = <?php echo $request_id; ?>;
            const refreshBtn = document.getElementById('refreshStatusBtn');
            
            if (refreshBtn) {
                refreshBtn.disabled = true;
                refreshBtn.innerHTML = '⏳ Lädt...';
            }
            
            jQuery.ajax({
                url: '<?php echo admin_url('admin-ajax.php'); ?>',
                type: 'POST',
                data: {
                    action: 'nexora_get_current_status',
                    request_id: currentRequestId,
                    nonce: '<?php echo wp_create_nonce('nexora_nonce'); ?>'
                },
                success: function(response) {
                    if (response.success && response.data.status_id) {
                        const statusSelect = document.getElementById('status');
                        const oldValue = statusSelect.value;
                        const newValue = response.data.status_id;
                        
                        if (oldValue != newValue) {
                            statusSelect.value = newValue;
                            alert(`Status aktualisiert von "${getStatusTitle(oldValue)}" zu "${getStatusTitle(newValue)}"`);
                        } else {
                            alert('Status ist bereits aktuell.');
                        }
                    } else {
                        alert('Fehler beim Laden des aktuellen Status.');
                    }
                },
                error: function() {
                    alert('Fehler beim Aktualisieren des Status.');
                },
                complete: function() {
                    if (refreshBtn) {
                        refreshBtn.disabled = false;
                        refreshBtn.innerHTML = '🔄 Aktualisieren';
                    }
                }
            });
        }
        function getStatusTitle(statusId) {
            const statusSelect = document.getElementById('status');
            const option = statusSelect.querySelector(`option[value="${statusId}"]`);
            return option ? option.textContent : 'Unbekannt';
        }
        document.addEventListener('DOMContentLoaded', function() {
            console.log('Document ready - initializing InvoiceServicesManager');
            checkForStatusUpdates();
            setInterval(checkForStatusUpdates, 5000);
            const refreshStatusBtn = document.getElementById('refreshStatusBtn');
            if (refreshStatusBtn) {
                refreshStatusBtn.addEventListener('click', function() {
                    console.log('Manual status refresh requested');
                    refreshStatusFromDatabase();
                });
            }
            console.log('Checking elements:');
            console.log('addServiceBtn:', document.getElementById('addServiceBtn'));
            console.log('saveServicesBtn:', document.getElementById('saveServicesBtn'));
            console.log('testBtn:', document.getElementById('testBtn'));
            console.log('clearBtn:', document.getElementById('clearBtn'));
            console.log('servicesTableBody:', document.getElementById('servicesTableBody'));
            console.log('summaryBox:', document.getElementById('summaryBox'));
            console.log('summaryStats:', document.getElementById('summaryStats'));
            
            try {
                window.invoiceManager = new InvoiceServicesManager();
                console.log('InvoiceServicesManager initialized successfully');
            } catch (error) {
                console.error('Error initializing InvoiceServicesManager:', error);
            }
        });
        jQuery(document).ready(function($) {
            const initialDeviceType = $('#device_type_id').val();
            const initialDeviceBrand = $('#device_brand_id').val();
            const initialDeviceSeries = $('#device_series_id').val();
            const initialDeviceModel = $('#device_model_id').val();
            loadDeviceTypes();
            $('#device_type_id').on('change', function() {
                const selectedValue = $(this).val();
                if (selectedValue) {
                    loadDeviceBrands(selectedValue);
                } else {
                    resetSubsequentStages('type');
                }
            });
            $('#device_brand_id').on('change', function() {
                const selectedValue = $(this).val();
                if (selectedValue) {
                    loadDeviceSeries(selectedValue);
                } else {
                    resetSubsequentStages('brand');
                }
            });
            $('#device_series_id').on('change', function() {
                const selectedValue = $(this).val();
                if (selectedValue) {
                    loadDeviceModels(selectedValue);
                } else {
                    resetSubsequentStages('series');
                }
            });
            $('.remove-custom-field').on('click', function() {
                const fieldName = $(this).data('field');
                const container = $(this).closest('.form-group');
                
                if (confirm('Sind Sie sicher, dass Sie dieses benutzerdefinierte Feld löschen möchten?')) {
                    container.remove();
                }
            });
            function loadDeviceTypes() {
                $.post('<?php echo admin_url('admin-ajax.php'); ?>', {
                    action: 'nexora_get_device_types',
                    nonce: '<?php echo wp_create_nonce('nexora_nonce'); ?>'
                }, function(response) {
                    if (response.success && response.data.length) {
                        let html = '<option value="">-- Bitte wählen --</option>';
                        response.data.forEach(function(type) {
                            const selected = (initialDeviceType == type.id) ? ' selected' : '';
                            html += `<option value="${type.id}"${selected}>${type.name}</option>`;
                        });
                        $('#device_type_id').html(html);
                        if (initialDeviceType) {
                            loadDeviceBrands(initialDeviceType);
                        }
                    }
                });
            }
            
            function loadDeviceBrands(typeId) {
                $.post('<?php echo admin_url('admin-ajax.php'); ?>', {
                    action: 'nexora_get_device_brands',
                    nonce: '<?php echo wp_create_nonce('nexora_nonce'); ?>',
                    type_id: typeId
                }, function(response) {
                    let html = '<option value="">-- Bitte wählen --</option>';
                    if (response.success && response.data.length) {
                        response.data.forEach(function(brand) {
                            const selected = (initialDeviceBrand == brand.id) ? ' selected' : '';
                            html += `<option value="${brand.id}"${selected}>${brand.name}</option>`;
                        });
                    }
                    $('#device_brand_id').html(html).prop('disabled', false);
                    resetSubsequentStages('brand');
                    if (initialDeviceBrand) {
                        loadDeviceSeries(initialDeviceBrand);
                    }
                });
            }
            
            function loadDeviceSeries(brandId) {
                $.post('<?php echo admin_url('admin-ajax.php'); ?>', {
                    action: 'nexora_get_device_series',
                    nonce: '<?php echo wp_create_nonce('nexora_nonce'); ?>',
                    brand_id: brandId
                }, function(response) {
                    let html = '<option value="">-- Bitte wählen --</option>';
                    if (response.success && response.data.length) {
                        response.data.forEach(function(series) {
                            const selected = (initialDeviceSeries == series.id) ? ' selected' : '';
                            html += `<option value="${series.id}"${selected}>${series.name}</option>`;
                        });
                    }
                    $('#device_series_id').html(html).prop('disabled', false);
                    resetSubsequentStages('series');
                    if (initialDeviceSeries) {
                        loadDeviceModels(initialDeviceSeries);
                    }
                });
            }
            
            function loadDeviceModels(seriesId) {
                $.post('<?php echo admin_url('admin-ajax.php'); ?>', {
                    action: 'nexora_get_device_models',
                    nonce: '<?php echo wp_create_nonce('nexora_nonce'); ?>',
                    series_id: seriesId
                }, function(response) {
                    let html = '<option value="">-- Bitte wählen --</option>';
                    if (response.success && response.data.length) {
                        response.data.forEach(function(model) {
                            const selected = (initialDeviceModel == model.id) ? ' selected' : '';
                            html += `<option value="${model.id}"${selected}>${model.name}</option>`;
                        });
                    }
                    $('#device_model_id').html(html).prop('disabled', false);
                });
            }
            
            function resetSubsequentStages(startFrom) {
                switch(startFrom) {
                    case 'type':
                        $('#device_brand_id').prop('disabled', true);
                        $('#device_series_id').prop('disabled', true);
                        $('#device_model_id').prop('disabled', true);
                        break;
                    case 'brand':
                        $('#device_series_id').prop('disabled', true);
                        $('#device_model_id').prop('disabled', true);
                        break;
                    case 'series':
                        $('#device_model_id').prop('disabled', true);
                        break;
                }
            }
            $('#createInvoiceBtn').on('click', function(e) {
                e.preventDefault();
                
                const requestId = <?php echo $request_id; ?>;
                
                if (!requestId) {
                    alert('Fehler: Keine Anfrage-ID gefunden');
                    return;
                }
                const $btn = $(this);
                const originalText = $btn.html();
                $btn.html('<span>🖨️⏳</span><span>Wird erstellt...</span>').prop('disabled', true);
                const form = document.createElement('form');
                form.method = 'POST';
                form.action = '<?php echo admin_url('admin-ajax.php'); ?>';
                form.target = '_blank';
                
                const fields = {
                    action: 'generate_pdf_invoice',
                    request_id: requestId,
                    nonce: '<?php echo wp_create_nonce('nexora_pdf_invoice_nonce'); ?>'
                };
                
                Object.keys(fields).forEach(key => {
                    const input = document.createElement('input');
                    input.type = 'hidden';
                    input.name = key;
                    input.value = fields[key];
                    form.appendChild(input);
                });
                
                document.body.appendChild(form);
                form.submit();
                document.body.removeChild(form);
                setTimeout(() => {
                    $btn.html(originalText).prop('disabled', false);
                }, 2000);
            });
        });
    </script>
    
    
    <?php include_once(NEXORA_PLUGIN_DIR . 'templates/service-approval-modal.php'); ?>
    
    <script>
    var nexora_ajax = {
        ajax_url: '<?php echo admin_url('admin-ajax.php'); ?>',
        nonce: '<?php echo wp_create_nonce('nexora_nonce'); ?>'
    };
    function loadNotificationCounts() {
        $.post(ajaxurl, {action: 'get_new_requests_count', nonce: '<?php echo wp_create_nonce("nexora_notifications_nonce"); ?>'}, function(resp){
            if(resp.success && resp.data.count > 0) {
                $('#nav-anfragen-badge').text(resp.data.count).show();
            } else {
                $('#nav-anfragen-badge').hide();
            }
        });
        $.post(ajaxurl, {action: 'nexora_get_new_users_count', nonce: '<?php echo wp_create_nonce("nexora_notifications_nonce"); ?>'}, function(resp){
            if(resp.success && resp.data.count > 0) {
                $('#nav-benutzer-badge').text(resp.data.count).show();
            } else {
                $('#nav-benutzer-badge').hide();
            }
        });
    }
    loadNotificationCounts();
    setInterval(loadNotificationCounts, 60000);

    </script>
</body>
</html> 