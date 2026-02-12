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
if (!is_user_logged_in() || !current_user_can('manage_options')) {
    wp_die('Access denied. You must be logged in as an administrator to view this page.');
}

global $wpdb;
$benefit_type_filter = isset($_GET['benefit_type']) ? sanitize_text_field($_GET['benefit_type']) : '';
$date_from = isset($_GET['date_from']) ? sanitize_text_field($_GET['date_from']) : '';
$date_to = isset($_GET['date_to']) ? sanitize_text_field($_GET['date_to']) : '';
$user_filter = isset($_GET['user_filter']) ? intval($_GET['user_filter']) : 0;
$users_with_benefits_ids = $wpdb->get_col("
    SELECT DISTINCT u.ID
    FROM {$wpdb->users} u
    INNER JOIN {$wpdb->usermeta} um3 ON u.ID = um3.user_id AND um3.meta_key = 'benefit_type'
    WHERE (um3.meta_value = 'discount' OR um3.meta_value = 'commission')
");

if (empty($users_with_benefits_ids)) {
    $requests = array();
} else {
    $user_ids_placeholders = implode(',', array_fill(0, count($users_with_benefits_ids), '%d'));
    
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
    $query_params = array_merge($users_with_benefits_ids, []);
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
}
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
if (current_user_can('manage_options')) {
    error_log('Nexora Service Suite eltern.php - Found ' . count($users_with_benefits) . ' users with benefits');
    foreach($users_with_benefits as $user) {
        error_log('User: ' . $user->user_login . ' - Benefit: ' . $user->benefit_type);
    }
    $all_benefit_users = $wpdb->get_results("
        SELECT u.ID, u.user_login, um.meta_value as benefit_type
        FROM {$wpdb->users} u
        INNER JOIN {$wpdb->usermeta} um ON u.ID = um.user_id AND um.meta_key = 'benefit_type'
        WHERE um.meta_value != ''
        ORDER BY u.user_login
    ");
    error_log('Nexora Service Suite eltern.php - All users with benefit_type: ' . count($all_benefit_users));
    foreach($all_benefit_users as $user) {
        error_log('All benefit user: ' . $user->user_login . ' - Benefit: ' . $user->benefit_type);
    }
}
if (current_user_can('manage_options')) {
    echo "";
    foreach($requests as $req) {
        echo "";
    }
}
$total_requests = count($requests);
$discount_requests = 0;
$commission_requests = 0;
$total_discount_amount = 0;
$total_commission_amount = 0;
$total_paid_amount = 0;
$total_unpaid_amount = 0;

foreach ($requests as $request) {
    if ($request->benefit_type === 'discount') {
        $discount_requests++;
    } else {
        $commission_requests++;
    }
}

?>

<!DOCTYPE html>
<html lang="de">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Eltern - Vorteile Management</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Inter', Arial, sans-serif;
            background: #0B0F19;
            color: #FFFFFF;
            overflow-x: hidden;
            min-height: 100vh;
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

        .nav-link.active {
            background: rgba(108, 93, 211, 0.2);
            color: #6c5dd3;
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

        
        .dashboard-container {
            padding: 20px;
            width: calc(100% - 80px);
            max-width: calc(100% - 80px);
            min-width: 0;
            margin-left: 80px;
            overflow-x: hidden;
            box-sizing: border-box;
        }

        
        .stats-row {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: 20px;
            margin-bottom: 30px;
        }

        .stat-card {
            background: linear-gradient(135deg, rgba(255, 255, 255, 0.1), rgba(255, 255, 255, 0.05));
            backdrop-filter: blur(10px);
            border: 1px solid rgba(255, 255, 255, 0.1);
            border-radius: 20px;
            padding: 25px;
            transition: all 0.3s ease;
            position: relative;
            overflow: hidden;
        }

        .stat-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 20px 40px rgba(0, 0, 0, 0.3);
        }

        .stat-card::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 3px;
            background: linear-gradient(90deg, #6c5dd3, #ff6b6b, #4ecdc4, #45b7d1);
        }

        .stat-icon {
            width: 60px;
            height: 60px;
            border-radius: 15px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 24px;
            margin-bottom: 15px;
            transition: all 0.3s ease;
        }

        .stat-icon.purple {
            background: linear-gradient(135deg, #6c5dd3, #8b7ed8);
            color: white;
        }

        .stat-icon.blue {
            background: linear-gradient(135deg, #4ecdc4, #44a08d);
            color: white;
        }

        .stat-icon.green {
            background: linear-gradient(135deg, #45b7d1, #96c93d);
            color: white;
        }

        .stat-icon.orange {
            background: linear-gradient(135deg, #ff6b6b, #ee5a24);
            color: white;
        }

        .stat-content h3 {
            font-size: 14px;
            color: #a0aec0;
            margin-bottom: 8px;
            font-weight: 500;
        }

        .stat-value {
            font-size: 32px;
            font-weight: 700;
            color: #ffffff;
            margin-bottom: 8px;
        }

        .stat-change {
            display: flex;
            align-items: center;
            gap: 5px;
            font-size: 12px;
            font-weight: 600;
        }

        .stat-change.positive {
            color: #4ecdc4;
        }

        .stat-change.negative {
            color: #ff6b6b;
        }

        
        .dashboard-grid {
            display: grid;
            grid-template-columns: 1fr;
            gap: 20px;
        }

        
        .filters-card {
            background: linear-gradient(135deg, rgba(255, 255, 255, 0.1), rgba(255, 255, 255, 0.05));
            backdrop-filter: blur(10px);
            border: 1px solid rgba(255, 255, 255, 0.1);
            border-radius: 20px;
            padding: 25px;
            margin-bottom: 20px;
        }

        .filters-card h3 {
            color: #ffffff;
            margin-bottom: 20px;
            font-size: 18px;
            font-weight: 600;
        }

        .filter-row {
            display: grid;
            grid-template-columns: repeat(4, 1fr);
            gap: 15px;
            align-items: end;
        }

        @media (max-width: 1200px) {
            .filter-row {
                grid-template-columns: repeat(2, 1fr);
            }
        }

        @media (max-width: 768px) {
            .filter-row {
                grid-template-columns: 1fr;
            }
        }

        .form-group {
            display: flex;
            flex-direction: column;
        }

        .form-group label {
            color: #a0aec0;
            font-size: 12px;
            font-weight: 600;
            margin-bottom: 8px;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .form-group input,
        .form-group select {
            background: rgba(255, 255, 255, 0.1);
            border: 1px solid rgba(255, 255, 255, 0.2);
            border-radius: 10px;
            padding: 12px 15px;
            color: #ffffff;
            font-size: 14px;
            transition: all 0.3s ease;
        }

        .form-group input:focus,
        .form-group select:focus {
            outline: none;
            border-color: #6c5dd3;
            background: rgba(255, 255, 255, 0.15);
        }

        .form-group input::placeholder {
            color: #a0aec0;
        }

        .btn {
            background: linear-gradient(135deg, #6c5dd3, #8b7ed8);
            border: none;
            border-radius: 10px;
            padding: 12px 20px;
            color: white;
            font-size: 14px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 10px 20px rgba(108, 93, 211, 0.3);
        }

        .btn-secondary {
            background: linear-gradient(135deg, #a0aec0, #718096);
        }

        .btn-secondary:hover {
            box-shadow: 0 10px 20px rgba(160, 174, 192, 0.3);
        }

        
        .users-table-card {
            background: rgba(255, 255, 255, 0.05);
            border-radius: 16px;
            padding: 24px;
            backdrop-filter: blur(20px);
            border: 1px solid rgba(255, 255, 255, 0.1);
        }

        .users-table {
            width: 100%;
            border-collapse: collapse;
            background: rgba(255, 255, 255, 0.05);
            border-radius: 12px;
            overflow: hidden;
        }

        .users-table th {
            background: rgba(108, 99, 255, 0.1);
            color: #fcdc24 !important;
            padding: 15.68px 11.76px;
            text-align: left;
            font-weight: 600;
            border-bottom: 1px solid rgba(255, 255, 255, 0.1);
            font-size: 11px;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .users-table th:nth-child(1) { width: 5%; }  
        .users-table th:nth-child(2) { width: 8%; }  
        .users-table th:nth-child(3) { width: 22%; } 
        .users-table th:nth-child(4) { width: 13%; } 
        .users-table th:nth-child(5) { width: 6%; }  
        .users-table th:nth-child(6) { width: 14%; } 
        .users-table th:nth-child(7) { width: 8%; }  

        .users-table td {
            padding: 15.68px 11.76px;
            border-bottom: 1px solid rgba(255, 255, 255, 0.05);
            color: #FFFFFF;
            vertical-align: middle;
            font-size: 12px;
        }

        .users-table tr:hover {
            background: rgba(255, 255, 255, 0.05) !important;
        }

        .user-info {
            display: flex;
            align-items: center;
            gap: 12px;
        }

        .user-avatar {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            background: linear-gradient(135deg, #6c5dd3, #8b7ed8);
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-weight: bold;
            font-size: 14px;
        }

        .user-details h4 {
            color: #ffffff;
            font-size: 14px;
            font-weight: 600;
            margin-bottom: 4px;
        }

        .user-details p {
            color: #a0aec0;
            font-size: 12px;
        }

        .benefit-badge {
            padding: 6px 12px;
            border-radius: 20px;
            font-size: 11px;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .benefit-badge.discount {
            background: linear-gradient(135deg, #ff6b6b, #ee5a24);
            color: white;
        }

        .benefit-badge.commission {
            background: linear-gradient(135deg, #4ecdc4, #44a08d);
            color: white;
        }

        .benefit-percentage {
            font-size: 18px;
            font-weight: 700;
            color: #6c5dd3;
        }

        .status-badge {
            padding: 6px 12px;
            border-radius: 20px;
            font-size: 11px;
            font-weight: 600;
            background: linear-gradient(135deg, #45b7d1, #96c93d);
            color: white;
        }

        .action-btn {
            display: flex;
            align-items: center;
            justify-content: center;
            width: 24px;
            height: 24px;
            min-width: 8px;
            padding: 2px 2px;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            transition: all 0.3s ease;
            font-size: 11px;
            color: white;
            background: rgba(255, 255, 255, 0.1);
            border: 1px solid rgba(255, 255, 255, 0.2);
        }

        .action-btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.3);
        }

        .action-btn:active {
            transform: translateY(0);
        }

        .edit-btn {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            border-color: #667eea;
        }

        .edit-btn:hover {
            background: linear-gradient(135deg, #5a6fd8 0%, #6a4190 100%);
        }

        .delete-btn {
            background: linear-gradient(135deg, #FF6B6B 0%, #FF8E8E 100%);
            border-color: #FF6B6B;
        }

        .delete-btn:hover {
            background: linear-gradient(135deg, #e55a5a 0%, #e57d7d 100%);
        }

        .print-btn {
            background: linear-gradient(135deg, #F59E0B 0%, #D97706 100%);
            border-color: #F59E0B;
        }

        .print-btn:hover {
            background: linear-gradient(135deg, #d48a0a 0%, #c26a05 100%);
        }

        .log-btn {
            background: linear-gradient(135deg, #8B5CF6 0%, #A855F7 100%);
            border-color: #8B5CF6;
        }

        .log-btn:hover {
            background: linear-gradient(135deg, #7c4dd8 0%, #9645e0 100%);
        }

        .action-btn i {
            font-size: 12px;
            line-height: 1;
        }

        
        .request-checkbox {
            width: 16px;
            height: 16px;
            accent-color: #6c5dd3;
            cursor: pointer;
        }

        .select-all-checkbox {
            width: 18px;
            height: 18px;
            accent-color: #6c5dd3;
            cursor: pointer;
        }

        
        .bulk-actions {
            display: none;
            background: rgba(108, 93, 211, 0.1);
            border: 1px solid rgba(108, 93, 211, 0.3);
            border-radius: 8px;
            padding: 12px 16px;
            margin-bottom: 16px;
            align-items: center;
            gap: 12px;
        }

        .bulk-actions.show {
            display: flex;
        }

        .bulk-actions-info {
            color: #fcdc24;
            font-weight: 600;
            font-size: 14px;
        }

        .bulk-action-btn {
            background: linear-gradient(135deg, #6c5dd3, #8b7ed8);
            border: none;
            border-radius: 6px;
            padding: 8px 16px;
            color: white;
            font-size: 12px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
            display: flex;
            align-items: center;
            gap: 6px;
        }

        .bulk-action-btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(108, 93, 211, 0.3);
        }

        .bulk-action-btn.danger {
            background: linear-gradient(135deg, #ff6b6b, #ff8e8e);
        }

        .bulk-action-btn.danger:hover {
            box-shadow: 0 4px 12px rgba(255, 107, 107, 0.3);
        }

        
        @keyframes spin {
            0% { transform: rotate(0deg); }
            100% { transform: rotate(360deg); }
        }

        
        .analytics-dashboard {
            background: linear-gradient(135deg, rgba(108, 93, 211, 0.1), rgba(255, 255, 255, 0.05));
            border-radius: 20px;
            padding: 30px;
            margin-bottom: 30px;
            backdrop-filter: blur(10px);
            border: 1px solid rgba(255, 255, 255, 0.1);
            box-shadow: 0 20px 40px rgba(0, 0, 0, 0.1);
        }

        .dashboard-header {
            text-align: center;
            margin-bottom: 40px;
        }

        .dashboard-header h2 {
            color: #ffffff;
            font-size: 28px;
            font-weight: 700;
            margin-bottom: 10px;
            text-shadow: 0 2px 4px rgba(0, 0, 0, 0.3);
        }

        .dashboard-header p {
            color: #a0aec0;
            font-size: 16px;
            margin: 0;
        }

        .analytics-grid {
            display: grid;
            grid-template-columns: 1fr 1fr 1fr;
            gap: 15px;
            margin-top: 20px;
        }

        .analytics-card {
            background: rgba(255, 255, 255, 0.05);
            border-radius: 12px;
            padding: 15px;
            backdrop-filter: blur(10px);
            border: 1px solid rgba(255, 255, 255, 0.1);
            transition: all 0.3s ease;
            position: relative;
            overflow: hidden;
        }

        .analytics-card::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 3px;
            background: linear-gradient(90deg, #6c5dd3, #8b7ed8, #6c5dd3);
            background-size: 200% 100%;
            animation: shimmer 2s infinite;
        }

        @keyframes shimmer {
            0% { background-position: -200% 0; }
            100% { background-position: 200% 0; }
        }

        .analytics-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 25px 50px rgba(108, 93, 211, 0.2);
        }

        .card-header {
            display: flex;
            align-items: center;
            margin-bottom: 12px;
        }

        .card-icon {
            width: 35px;
            height: 35px;
            border-radius: 8px;
            display: flex;
            align-items: center;
            justify-content: center;
            margin-right: 10px;
            font-size: 16px;
            color: white;
        }

        .total-debt .card-icon {
            background: linear-gradient(135deg, #ff6b6b, #ff8e8e);
        }

        .chart-card .card-icon {
            background: linear-gradient(135deg, #6c5dd3, #8b7ed8);
        }

        .card-title h3 {
            color: #ffffff;
            font-size: 14px;
            font-weight: 600;
            margin: 0 0 3px 0;
        }

        .card-title p {
            color: #a0aec0;
            font-size: 11px;
            margin: 0;
        }

        .debt-amount {
            text-align: center;
            margin-bottom: 12px;
        }

        .currency {
            font-size: 16px;
            color: #ff6b6b;
            font-weight: 600;
            margin-right: 3px;
        }

        .amount {
            font-size: 28px;
            color: #ffffff;
            font-weight: 700;
            text-shadow: 0 2px 4px rgba(0, 0, 0, 0.3);
        }

        .debt-progress {
            margin-top: 20px;
        }

        .progress-bar {
            width: 100%;
            height: 8px;
            background: rgba(255, 255, 255, 0.1);
            border-radius: 4px;
            overflow: hidden;
        }

        .progress-fill {
            height: 100%;
            background: linear-gradient(90deg, #ff6b6b, #ff8e8e);
            border-radius: 4px;
            width: 0%;
            transition: width 2s ease-in-out;
            animation: progressFill 2s ease-in-out;
        }

        @keyframes progressFill {
            from { width: 0%; }
            to { width: var(--progress-width, 0%); }
        }

        .chart-container {
            position: relative;
            height: 120px;
            width: 100%;
        }

        .chart-container canvas {
            max-width: 100%;
            max-height: 100%;
        }

        
        .debt-details {
            margin-top: 10px;
        }

        .debt-item {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 5px;
            font-size: 11px;
        }

        .debt-item .label {
            color: #a0aec0;
        }

        .debt-item .value {
            color: #ffffff;
            font-weight: 600;
        }

        
        .top-debtors {
            margin-top: 10px;
        }

        .debtor-item {
            display: flex;
            align-items: center;
            margin-bottom: 8px;
            padding: 5px;
            background: rgba(255, 255, 255, 0.05);
            border-radius: 6px;
            font-size: 11px;
        }

        .debtor-item .rank {
            color: #6c5dd3;
            font-weight: 700;
            margin-right: 8px;
            min-width: 15px;
        }

        .debtor-item .name {
            color: #ffffff;
            flex: 1;
            margin-right: 8px;
        }

        .debtor-item .amount {
            color: #ff6b6b;
            font-weight: 600;
        }

        
        .summary-stats {
            margin-top: 10px;
        }

        .stat-item {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 6px;
            font-size: 11px;
        }

        .stat-item .label {
            color: #a0aec0;
        }

        .stat-item .value {
            color: #6c5dd3;
            font-weight: 600;
        }

        
        @media (max-width: 1200px) {
            .analytics-grid {
                grid-template-columns: 1fr 1fr;
            }
            
            .total-debt {
                grid-column: 1 / -1;
            }
        }

        @media (max-width: 768px) {
            .analytics-grid {
                grid-template-columns: 1fr;
            }
            
            .analytics-dashboard {
                padding: 20px;
            }
            
            .dashboard-header h2 {
                font-size: 24px;
            }
        }

        
        .payment-status-dropdown {
            position: relative;
            display: inline-block;
        }

        .payment-status-btn {
            background: #dc3545;
            color: white;
            border: none;
            padding: 8px 12px;
            border-radius: 6px;
            cursor: pointer;
            font-size: 12px;
            font-weight: 600;
            transition: all 0.3s ease;
            display: flex;
            align-items: center;
            gap: 6px;
        }

        .payment-status-btn.paid {
            background: #28a745;
        }

        .payment-status-btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.2);
        }

        .payment-status-btn i {
            font-size: 10px;
        }

        .payment-dropdown {
            position: absolute;
            top: 100%;
            left: 0;
            background: white;
            border: 1px solid #e2e8f0;
            border-radius: 6px;
            box-shadow: 0 10px 25px rgba(0, 0, 0, 0.1);
            z-index: 1000;
            min-width: 150px;
            display: none;
        }

        .payment-dropdown.show {
            display: block;
        }

        .payment-option {
            padding: 8px 12px;
            cursor: pointer;
            transition: background-color 0.2s ease;
            display: flex;
            align-items: center;
            gap: 6px;
            font-size: 10px;
            font-weight: 500;
        }

        .payment-option:hover {
            background: #f8f9fa;
        }

        .payment-option.not-paid {
            color: #dc3545;
        }

        .payment-option.paid {
            color: #28a745;
        }

        .payment-option i {
            font-size: 10px;
        }

        
        .log-btn {
            background: linear-gradient(135deg, #8B5CF6 0%, #A855F7 100%);
            border-color: #8B5CF6;
            color: white;
            border: none;
            padding: 8px 12px;
            border-radius: 6px;
            cursor: pointer;
            font-size: 12px;
            font-weight: 600;
            transition: all 0.3s ease;
            display: inline-flex;
            align-items: center;
            gap: 6px;
            margin-left: 8px;
        }

        .log-btn:hover {
            background: linear-gradient(135deg, #7c4dd8 0%, #9645e0 100%);
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(139, 92, 246, 0.3);
        }

        .log-btn i {
            font-size: 10px;
        }

        
        .log-modal {
            display: none;
            position: fixed;
            z-index: 10000;
            left: 0;
            top: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(0, 0, 0, 0.5);
        }

        .log-modal.show {
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .log-modal-content {
            background: #1a202c;
            border-radius: 12px;
            padding: 30px;
            max-width: 800px;
            width: 90%;
            max-height: 80vh;
            overflow-y: auto;
            position: relative;
            box-shadow: 0 20px 40px rgba(0, 0, 0, 0.3);
        }

        .log-modal-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 25px;
            padding-bottom: 15px;
            border-bottom: 1px solid rgba(255, 255, 255, 0.1);
        }

        .log-modal-title {
            color: #ffffff;
            font-size: 20px;
            font-weight: 600;
            margin: 0;
        }

        .log-modal-close {
            background: none;
            border: none;
            color: #a0aec0;
            font-size: 24px;
            cursor: pointer;
            padding: 0;
            width: 30px;
            height: 30px;
            display: flex;
            align-items: center;
            justify-content: center;
            border-radius: 50%;
            transition: all 0.3s ease;
        }

        .log-modal-close:hover {
            background: rgba(255, 255, 255, 0.1);
            color: #ffffff;
        }

        .log-entry {
            background: rgba(255, 255, 255, 0.05);
            border-radius: 8px;
            padding: 15px;
            margin-bottom: 15px;
            border-left: 4px solid #6c5dd3;
        }

        .log-entry.payment {
            border-left-color: #28a745;
        }

        .log-entry.status {
            border-left-color: #f39c12;
        }

        .log-entry.user {
            border-left-color: #007bff;
        }

        .log-entry.request {
            border-left-color: #17a2b8;
        }

        .log-entry-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 8px;
        }

        .log-entry-type {
            background: #6c5dd3;
            color: white;
            padding: 4px 8px;
            border-radius: 4px;
            font-size: 11px;
            font-weight: 600;
            text-transform: uppercase;
        }

        .log-entry.payment .log-entry-type {
            background: #28a745;
        }

        .log-entry.status .log-entry-type {
            background: #f39c12;
        }

        .log-entry.user .log-entry-type {
            background: #007bff;
        }

        .log-entry.request .log-entry-type {
            background: #17a2b8;
        }

        .log-entry-time {
            color: #a0aec0;
            font-size: 12px;
        }

        .log-entry-content {
            color: #ffffff;
            font-size: 14px;
            line-height: 1.5;
        }

        .log-entry-details {
            color: #718096;
            font-size: 12px;
            margin-top: 5px;
        }

        .no-logs {
            text-align: center;
            padding: 40px 20px;
            color: #a0aec0;
        }

        .no-logs i {
            font-size: 3rem;
            margin-bottom: 15px;
            opacity: 0.5;
        }

        .no-data {
            text-align: center;
            padding: 60px 20px;
            color: #a0aec0;
        }

        .no-data i {
            font-size: 4rem;
            margin-bottom: 20px;
            opacity: 0.5;
        }

        .no-data h3 {
            color: #ffffff;
            margin-bottom: 10px;
        }

        @keyframes slideInLeft {
            to {
                opacity: 1;
                transform: translateX(0);
            }
        }

        @media (max-width: 1200px) {
            .dashboard-container {
                width: calc(100% - 80px);
                max-width: calc(100% - 80px);
                margin-left: 80px;
                overflow-x: hidden;
            }
        }

        @media (max-width: 1200px) {
            .users-table th:nth-child(1) { width: 5%; }
            .users-table th:nth-child(2) { width: 8%; }
            .users-table th:nth-child(3) { width: 20%; }
            .users-table th:nth-child(4) { width: 12%; }
            .users-table th:nth-child(5) { width: 5%; }
            .users-table th:nth-child(6) { width: 12%; }
            .users-table th:nth-child(7) { width: 8%; }
            
            .users-table th,
            .users-table td {
                padding: 12px 8px;
            }
        }

        @media (max-width: 768px) {
            .dashboard-container {
                width: calc(100% - 80px);
                max-width: calc(100% - 80px);
                margin-left: 80px;
                overflow-x: auto;
                padding: 16px;
            }

            .stats-row {
                grid-template-columns: 1fr;
            }

            .filter-row {
                grid-template-columns: 1fr;
            }

            .users-table {
                font-size: 11px;
                min-width: 800px;
            }

            .users-table th,
            .users-table td {
                padding: 8px;
            }

            .users-table th:nth-child(1) { width: 5%; }
            .users-table th:nth-child(2) { width: 8%; }
            .users-table th:nth-child(3) { width: 18%; }
            .users-table th:nth-child(4) { width: 11%; }
            .users-table th:nth-child(5) { width: 4%; }
            .users-table th:nth-child(6) { width: 12%; }
            .users-table th:nth-child(7) { width: 8%; }
            
            .users-table th,
            .users-table td {
                padding: 8px;
            }
            
            .action-btn {
                width: 20px;
                height: 20px;
                font-size: 10px;
            }
        }
    </style>
</head>
<body>
    
    <nav class="vertical-nav">
        <div class="nav-toggle">
            <i class="fas fa-bars"></i>
            <span class="nav-label">Menü</span>
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
                <a href="<?php echo admin_url('admin.php?page=Nexora Service Suite-service-request'); ?>" class="nav-link" title="Anfragen">
                    <i class="fas fa-clipboard-list"></i>
                    <span class="nav-text">Anfragen</span>
                </a>
            </li>
            
            <li class="nav-item">
                <a href="<?php echo admin_url('admin.php?page=Nexora Service Suite-users'); ?>" class="nav-link" title="Benutzer">
                    <i class="fas fa-users"></i>
                    <span class="nav-text">Benutzer</span>
                </a>
            </li>
            
            <li class="nav-item">
                <a href="<?php echo admin_url('admin.php?page=Nexora Service Suite-eltern'); ?>" class="nav-link active" title="Eltern">
                    <i class="fas fa-hand-holding-usd"></i>
                    <span class="nav-text">Eltern</span>
                </a>
            </li>
            
            <li class="nav-item">
                <a href="<?php echo admin_url('admin.php?page=Nexora Service Suite-device-manager'); ?>" class="nav-link" title="Geräteverwaltung">
                    <i class="fas fa-mobile-alt"></i>
                    <span class="nav-text">Geräteverwaltung</span>
                </a>
            </li>
            
            <li class="nav-item">
                <a href="<?php echo admin_url('admin.php?page=Nexora Service Suite-admin-notifications'); ?>" class="nav-link" title="Nachrichten">
                    <i class="fas fa-bell"></i>
                    <span class="nav-text">Nachrichten</span>
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
        
        <?php if (isset($column_missing) && $column_missing): ?>
        <div class="alert alert-warning" style="background: #fff3cd; border: 1px solid #ffeaa7; color: #856404; padding: 15px; border-radius: 5px; margin: 20px 0;">
            <strong>⚠️ Database Update Required</strong><br>
            Die erforderlichen Datenbankspalten fehlen. Bitte führen Sie die Datei <code>ddd.php</code> aus, um die Datenbank zu aktualisieren.
        </div>
        <?php endif; ?>
        
        
        <div class="analytics-dashboard">
            <div class="dashboard-header">
                <h2><i class="fas fa-chart-line" style="margin-right: 10px; color: #6c5dd3;"></i> Analytische Übersicht</h2>
                <p>Detaillierte Analyse der Vorteile und Leistungen</p>
            </div>
            
            
            <div class="analytics-grid">
                
                <div class="analytics-card total-debt">
                    <div class="card-header">
                        <div class="card-icon">
                            <i class="fas fa-money-bill-wave"></i>
                        </div>
                        <div class="card-title">
                            <h3>Gesamtschulden</h3>
                            <p>Summe aller Provisionen</p>
                        </div>
                    </div>
                    <div class="card-content">
                        <div class="debt-amount" id="total-debt-amount">
                            <span class="currency">€</span>
                            <span class="amount">0</span>
                        </div>
                        <div class="debt-details" id="debt-details">
                            <div class="debt-item">
                                <span class="label">Höchste Schuld:</span>
                                <span class="value" id="highest-debt">€0</span>
                            </div>
                            <div class="debt-item">
                                <span class="label">Anzahl Konten:</span>
                                <span class="value" id="debt-accounts">0</span>
                            </div>
                        </div>
                    </div>
                </div>

                
                <div class="analytics-card chart-card">
                    <div class="card-header">
                        <div class="card-icon">
                            <i class="fas fa-trophy"></i>
                        </div>
                        <div class="card-title">
                            <h3>Top Schuldner</h3>
                            <p>Höchste Provisionen</p>
                        </div>
                    </div>
                    <div class="card-content">
                        <div class="top-debtors" id="top-debtors">
                            <div class="debtor-item">
                                <span class="rank">1.</span>
                                <span class="name">-</span>
                                <span class="amount">€0</span>
                            </div>
                            <div class="debtor-item">
                                <span class="rank">2.</span>
                                <span class="name">-</span>
                                <span class="amount">€0</span>
                            </div>
                            <div class="debtor-item">
                                <span class="rank">3.</span>
                                <span class="name">-</span>
                                <span class="amount">€0</span>
                            </div>
                        </div>
                    </div>
                </div>

                
                <div class="analytics-card chart-card">
                    <div class="card-header">
                        <div class="card-icon">
                            <i class="fas fa-chart-line"></i>
                        </div>
                        <div class="card-title">
                            <h3>Zusammenfassung</h3>
                            <p>Übersicht der Vorteile</p>
                        </div>
                    </div>
                    <div class="card-content">
                        <div class="summary-stats" id="summary-stats">
                            <div class="stat-item">
                                <span class="label">Rabatt-Anfragen:</span>
                                <span class="value" id="discount-count">0</span>
                            </div>
                            <div class="stat-item">
                                <span class="label">Provision-Anfragen:</span>
                                <span class="value" id="commission-count">0</span>
                            </div>
                            <div class="stat-item">
                                <span class="label">Gesamt-Anfragen:</span>
                                <span class="value" id="total-count">0</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        
        <div class="filters-card">
            <h3><i class="fas fa-filter" style="margin-right: 10px; color: #6c5dd3;"></i> Filter</h3>
            <div class="filter-row">
                <div class="form-group">
                    <label for="benefit_type">Vorteil-Typ</label>
                    <select id="benefit_type">
                        <option value="">Alle Typen</option>
                        <option value="discount" <?php echo $benefit_type_filter === 'discount' ? 'selected' : ''; ?>>Rabatt</option>
                        <option value="commission" <?php echo $benefit_type_filter === 'commission' ? 'selected' : ''; ?>>Provision</option>
                    </select>
                </div>
                <div class="form-group">
                    <label for="user_filter">Benutzer</label>
                    <select id="user_filter">
                        <option value="">Alle Benutzer</option>
                        <?php foreach ($users_with_benefits as $user): ?>
                            <option value="<?php echo esc_attr($user->ID); ?>" <?php echo $user_filter == $user->ID ? 'selected' : ''; ?>>
                                <?php echo esc_html($user->user_login); ?> 
                                (<?php echo esc_html($user->benefit_type === 'discount' ? 'Rabatt' : 'Provision'); ?>)
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="form-group">
                    <label for="date_from">Von Datum</label>
                    <input type="date" id="date_from" value="<?php echo esc_attr($date_from); ?>">
                </div>
                <div class="form-group">
                    <label for="date_to">Bis Datum</label>
                    <input type="date" id="date_to" value="<?php echo esc_attr($date_to); ?>">
                </div>
            </div>
            
            
            <div class="filter-actions" style="margin-top: 15px; display: flex; gap: 10px; justify-content: flex-end;">
                <button type="button" id="apply-filters" class="btn" onclick="applyFilters()">
                    <i class="fas fa-search"></i>
                    Filtern
                </button>
                <button type="button" id="reset-filters" class="btn btn-secondary" onclick="resetFilters()">
                    <i class="fas fa-times"></i>
                    Zurücksetzen
                </button>
            </div>
        </div>

        
        <?php if ($benefit_type_filter || $user_filter || $date_from || $date_to): ?>
        <div class="active-filters" style="background: rgba(108, 93, 211, 0.1); border: 1px solid rgba(108, 93, 211, 0.3); border-radius: 8px; padding: 12px 16px; margin-bottom: 16px;">
            <div style="display: flex; align-items: center; gap: 12px; flex-wrap: wrap;">
                <span style="color: #fcdc24; font-weight: 600; font-size: 14px;">
                    <i class="fas fa-filter" style="margin-right: 6px;"></i>
                    Aktive Filter:
                </span>
                <?php if ($benefit_type_filter): ?>
                    <span class="filter-tag" style="background: #6c5dd3; color: white; padding: 4px 8px; border-radius: 4px; font-size: 12px;">
                        <?php echo $benefit_type_filter === 'discount' ? 'Rabatt' : 'Provision'; ?>
                    </span>
                <?php endif; ?>
                <?php if ($user_filter): ?>
                    <?php 
                    $selected_user = null;
                    foreach ($users_with_benefits as $user) {
                        if ($user->ID == $user_filter) {
                            $selected_user = $user;
                            break;
                        }
                    }
                    ?>
                    <span class="filter-tag" style="background: #6c5dd3; color: white; padding: 4px 8px; border-radius: 4px; font-size: 12px;">
                        Benutzer: <?php echo $selected_user ? esc_html($selected_user->user_login) : 'Unbekannt'; ?>
                    </span>
                <?php endif; ?>
                <?php if ($date_from): ?>
                    <span class="filter-tag" style="background: #6c5dd3; color: white; padding: 4px 8px; border-radius: 4px; font-size: 12px;">
                        Von: <?php echo esc_html($date_from); ?>
                    </span>
                <?php endif; ?>
                <?php if ($date_to): ?>
                    <span class="filter-tag" style="background: #6c5dd3; color: white; padding: 4px 8px; border-radius: 4px; font-size: 12px;">
                        Bis: <?php echo esc_html($date_to); ?>
                    </span>
                <?php endif; ?>
                <a href="?" style="color: #ff6b6b; text-decoration: none; font-size: 12px; font-weight: 600;">
                    <i class="fas fa-times"></i> Alle Filter entfernen
                </a>
            </div>
        </div>
        <?php endif; ?>

        
        <div id="bulk-actions" class="bulk-actions">
            <span class="bulk-actions-info" id="bulk-actions-info">0 ausgewählt</span>
            <button class="bulk-action-btn" onclick="bulkMarkAsPaid()">
                <i class="fas fa-check"></i>
                Als bezahlt markieren
            </button>
            <button class="bulk-action-btn" onclick="bulkMarkAsUnpaid()">
                <i class="fas fa-times"></i>
                Als nicht bezahlt markieren
            </button>
            <button class="bulk-action-btn danger" onclick="bulkDelete()">
                <i class="fas fa-trash"></i>
                Ausgewählte löschen
            </button>
        </div>

        
        <?php if (!empty($requests)): ?>
            <div class="users-table-card">
                <h3 style="color: #ffffff; margin-bottom: 20px; font-size: 18px; font-weight: 600;">
                    <i class="fas fa-clipboard-list" style="margin-right: 10px; color: #6c5dd3;"></i>
                    Anfragen von Benutzern mit Vorteilen
                </h3>
                <table class="users-table">
                    <thead>
                        <tr>
                            <th>
                                <input type="checkbox" id="select-all-requests" class="select-all-checkbox" title="Alle auswählen">
                            </th>
                            <th>ID</th>
                            <th>Benutzer</th>
                            <th>Vorteil-Typ</th>
                            <th>Prozentsatz</th>
                            <th>Anfrage Datum</th>
                            <th>Aktionen</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($requests as $request): ?>
                            <tr>
                                <td>
                                    <div style="text-align: center;">
                                        <input type="checkbox" class="request-checkbox" data-request-id="<?php echo esc_attr($request->request_id); ?>" data-user-id="<?php echo esc_attr($request->user_id); ?>">
                                    </div>
                                </td>
                                <td>
                                    <div style="text-align: center;">
                                        <span style="color: #6c5dd3; font-size: 14px; font-weight: 700; background: rgba(108, 93, 211, 0.1); padding: 4px 8px; border-radius: 6px; display: inline-block;">
                                            #<?php echo esc_html($request->request_id); ?>
                                        </span>
                                    </div>
                                </td>
                                <td>
                                    <div class="user-info">
                                        <div class="user-avatar">
                                            <?php echo strtoupper(substr($request->user_login, 0, 2)); ?>
                                        </div>
                                        <div class="user-details">
                                            <h4><?php echo esc_html($request->user_login); ?></h4>
                                            <p><?php echo esc_html($request->user_email); ?></p>
                                        </div>
                                    </div>
                                </td>
                                <td>
                                    <span class="benefit-badge <?php echo esc_attr($request->benefit_type); ?>">
                                        <?php echo $request->benefit_type === 'discount' ? 'Rabatt' : 'Provision'; ?>
                                    </span>
                                </td>
                                <td>
                                    <span class="benefit-percentage">
                                        <?php 
                                        if ($request->benefit_type === 'discount') {
                                            echo esc_html($request->discount_percentage) . '%';
                                        } else {
                                            echo esc_html($request->commission_percentage) . '%';
                                        }
                                        ?>
                                    </span>
                                </td>
                                <td>
                                    <?php if ($request->created_at): ?>
                                        <div style="text-align: center;">
                                            <div style="color: #6c5dd3; font-size: 10px; font-weight: 500; background: rgba(108, 93, 211, 0.1); padding: 2px 4px; border-radius: 3px; display: inline-block;">
                                                <i class="fas fa-calendar-alt" style="margin-right: 2px; font-size: 8px;"></i>
                                                <?php echo date('d.m.Y', strtotime($request->created_at)); ?>
                                            </div>
                                            <div style="color: #a0aec0; font-size: 9px; margin-top: 1px;">
                                                <?php echo date('H:i', strtotime($request->created_at)); ?>
                                            </div>
                                        </div>
                                    <?php else: ?>
                                        <span style="color: #a0aec0; font-style: italic; font-size: 12px;">-</span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <div style="display: flex; align-items: center; gap: 8px;">
                                        <?php if ($request->benefit_type === 'commission'): ?>
                                            
                                            <?php 
                                            $payment_status = $request->payment_status ?: 'not_paid';
                                            $is_paid = $payment_status === 'paid';
                                            ?>
                                            <div class="payment-status-dropdown">
                                                <button class="payment-status-btn <?php echo $is_paid ? 'paid' : 'not-paid'; ?>" onclick="togglePaymentDropdown(<?php echo $request->user_id; ?>)">
                                                    <i class="fas <?php echo $is_paid ? 'fa-check-circle' : 'fa-times-circle'; ?>"></i>
                                                    <?php echo $is_paid ? 'Bezahlt' : 'Nicht bezahlt'; ?>
                                                    <i class="fas fa-chevron-down"></i>
                                                </button>
                                                <div class="payment-dropdown" id="payment-dropdown-<?php echo $request->user_id; ?>">
                                                    <div class="payment-option not-paid" onclick="updatePaymentStatus(<?php echo $request->user_id; ?>, 'not_paid')">
                                                        <i class="fas fa-times-circle"></i>
                                                        Nicht bezahlt
                                                    </div>
                                                    <div class="payment-option paid" onclick="updatePaymentStatus(<?php echo $request->user_id; ?>, 'paid')">
                                                        <i class="fas fa-check-circle"></i>
                                                        Bezahlt
                                                    </div>
                                                </div>
                                            </div>
                                        <?php else: ?>
                                            
                                            <button class="action-btn" onclick="viewUserDetails(<?php echo $request->user_id; ?>)">
                                                <i class="fas fa-eye" style="margin-right: 5px;"></i>
                                                Details
                                            </button>
                                        <?php endif; ?>
                                        
                                        
                                        <button class="log-btn" onclick="showUserLog(<?php echo $request->user_id; ?>, '<?php echo esc_js($request->user_login); ?>')">
                                            <i class="fas fa-history"></i>
                                            Log
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php else: ?>
            <div class="users-table-card">
                <div class="no-data">
                    <i class="fas fa-clipboard-list"></i>
                    <h3>Keine Anfragen von Benutzern mit Vorteilen gefunden</h3>
                    <p>Es wurden keine Service-Anfragen von Benutzern mit Rabatten oder Provisionen gefunden.</p>
                </div>
            </div>
        <?php endif; ?>
    </div>

    
    <div id="logModal" class="log-modal">
        <div class="log-modal-content">
            <div class="log-modal-header">
                <h2 class="log-modal-title" id="logModalTitle">Benutzer Log</h2>
                <button class="log-modal-close" onclick="closeLogModal()">&times;</button>
            </div>
            <div id="logModalBody">
                
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script>
        function viewUserDetails(userId) {
            window.open('<?php echo admin_url('admin.php?page=Nexora Service Suite-users'); ?>&action=edit&user_id=' + userId, '_blank');
        }
        function togglePaymentDropdown(userId) {
            const dropdown = document.getElementById('payment-dropdown-' + userId);
            const allDropdowns = document.querySelectorAll('.payment-dropdown');
            allDropdowns.forEach(dd => {
                if (dd.id !== 'payment-dropdown-' + userId) {
                    dd.classList.remove('show');
                }
            });
            dropdown.classList.toggle('show');
        }
        function updatePaymentStatus(userId, status) {
            const btn = document.querySelector('#payment-dropdown-' + userId).previousElementSibling;
            const dropdown = document.getElementById('payment-dropdown-' + userId);
            if (status === 'paid') {
                btn.className = 'payment-status-btn paid';
                btn.innerHTML = '<i class="fas fa-check-circle"></i>Bezahlt<i class="fas fa-chevron-down"></i>';
            } else {
                btn.className = 'payment-status-btn not-paid';
                btn.innerHTML = '<i class="fas fa-times-circle"></i>Nicht bezahlt<i class="fas fa-chevron-down"></i>';
            }
            dropdown.classList.remove('show');
            fetch('<?php echo admin_url('admin-ajax.php'); ?>', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                },
                body: new URLSearchParams({
                    'action': 'update_payment_status',
                    'user_id': userId,
                    'status': status,
                    'nonce': '<?php echo wp_create_nonce('update_payment_status'); ?>'
                })
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    const notification = document.createElement('div');
                    notification.style.cssText = 'position: fixed; top: 20px; right: 20px; background: #28a745; color: white; padding: 15px; border-radius: 5px; z-index: 9999;';
                    notification.textContent = 'Zahlungsstatus erfolgreich aktualisiert!';
                    document.body.appendChild(notification);
                    
                    setTimeout(() => {
                        notification.remove();
                    }, 3000);
                } else {
                    console.error('Error updating payment status:', data.data);
                }
            })
            .catch(error => {
                console.error('Error:', error);
            });
        }
        document.addEventListener('click', function(event) {
            if (!event.target.closest('.payment-status-dropdown')) {
                const allDropdowns = document.querySelectorAll('.payment-dropdown');
                allDropdowns.forEach(dropdown => {
                    dropdown.classList.remove('show');
                });
            }
        });
        function showUserLog(userId, userName) {
            const modal = document.getElementById('logModal');
            const title = document.getElementById('logModalTitle');
            const body = document.getElementById('logModalBody');
            
            title.textContent = `Log für Benutzer: ${userName}`;
            body.innerHTML = '<div style="text-align: center; padding: 20px;"><i class="fas fa-spinner fa-spin"></i> Lade Logs...</div>';
            
            modal.classList.add('show');
            fetch('<?php echo admin_url('admin-ajax.php'); ?>', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                },
                body: new URLSearchParams({
                    'action': 'get_user_logs',
                    'user_id': userId,
                    'nonce': '<?php echo wp_create_nonce('get_user_logs'); ?>'
                })
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    displayLogs(data.data);
                } else {
                    body.innerHTML = '<div class="no-logs"><i class="fas fa-exclamation-triangle"></i><h3>Fehler beim Laden der Logs</h3><p>' + data.data + '</p></div>';
                }
            })
            .catch(error => {
                console.error('Error:', error);
                body.innerHTML = '<div class="no-logs"><i class="fas fa-exclamation-triangle"></i><h3>Fehler beim Laden der Logs</h3><p>Ein unbekannter Fehler ist aufgetreten.</p></div>';
            });
        }
        function displayLogs(logs) {
            const body = document.getElementById('logModalBody');
            
            if (logs.length === 0) {
                body.innerHTML = '<div class="no-logs"><i class="fas fa-history"></i><h3>Keine Logs gefunden</h3><p>Für diesen Benutzer sind noch keine Aktivitäten aufgezeichnet.</p></div>';
                return;
            }
            
            let html = '';
            logs.forEach(log => {
                const typeClass = log.type || 'user';
                const typeText = getLogTypeText(log.type);
                const time = new Date(log.timestamp).toLocaleString('de-DE');
                
                html += `
                    <div class="log-entry ${typeClass}">
                        <div class="log-entry-header">
                            <span class="log-entry-type">${typeText}</span>
                            <span class="log-entry-time">${time}</span>
                        </div>
                        <div class="log-entry-content">${log.message}</div>
                        <div class="log-entry-details">
                            ${log.details ? log.details : ''}
                            ${log.admin ? `Geändert von: ${log.admin}` : ''}
                        </div>
                    </div>
                `;
            });
            
            body.innerHTML = html;
        }
        function getLogTypeText(type) {
            const types = {
                'payment': 'Zahlung',
                'status': 'Status',
                'user': 'Benutzer',
                'benefit': 'Vorteil',
                'request': 'Service-Anfrage'
            };
            return types[type] || 'Allgemein';
        }
        function closeLogModal() {
            const modal = document.getElementById('logModal');
            modal.classList.remove('show');
        }
        document.addEventListener('click', function(event) {
            const modal = document.getElementById('logModal');
            if (event.target === modal) {
                closeLogModal();
            }
        });
        document.addEventListener('DOMContentLoaded', function() {
            initializeAnalytics();
            const statCards = document.querySelectorAll('.stat-card');
            statCards.forEach(card => {
                card.addEventListener('mouseenter', function() {
                    this.style.transform = 'translateY(-5px)';
                });
                card.addEventListener('mouseleave', function() {
                    this.style.transform = 'translateY(0)';
                });
            });
            const benefitTypeSelect = document.getElementById('benefit_type');
            const userFilterSelect = document.getElementById('user_filter');
            const dateFromInput = document.getElementById('date_from');
            const dateToInput = document.getElementById('date_to');
            
            [benefitTypeSelect, userFilterSelect, dateFromInput, dateToInput].forEach(element => {
                if (element) {
                    element.addEventListener('change', function() {
                        applyFilters();
                    });
                }
            });
        function applyFilters() {
                console.log('Apply filters called');
                
                const benefitTypeSelect = document.getElementById('benefit_type');
                const userFilterSelect = document.getElementById('user_filter');
                const dateFromInput = document.getElementById('date_from');
                const dateToInput = document.getElementById('date_to');
                
                const filters = {
                    benefit_type: benefitTypeSelect ? benefitTypeSelect.value : '',
                    user_filter: userFilterSelect ? userFilterSelect.value : '',
                    date_from: dateFromInput ? dateFromInput.value : '',
                    date_to: dateToInput ? dateToInput.value : ''
                };
                
                console.log('Filters:', filters);
                const tableBody = document.querySelector('.users-table tbody');
                if (tableBody) {
                    tableBody.innerHTML = `
                        <tr>
                            <td colspan="7" style="text-align: center; padding: 40px;">
                                <div style="display: flex; align-items: center; justify-content: center; gap: 10px;">
                                    <div style="width: 20px; height: 20px; border: 2px solid #6c5dd3; border-top: 2px solid transparent; border-radius: 50%; animation: spin 1s linear infinite;"></div>
                                    <span>Lade gefilterte Daten...</span>
                                </div>
                            </td>
                        </tr>
                    `;
                }
                console.log('Making AJAX call...');
                fetch('<?php echo admin_url('admin-ajax.php'); ?>', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/x-www-form-urlencoded',
                    },
                    body: new URLSearchParams({
                        'action': 'eltern_filter_requests',
                        'benefit_type': filters.benefit_type,
                        'user_filter': filters.user_filter,
                        'date_from': filters.date_from,
                        'date_to': filters.date_to,
                        'nonce': '<?php echo wp_create_nonce('eltern_filter_requests'); ?>'
                    })
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        updateTable(data.data.requests);
                        updateStats(data.data.stats);
                        updateActiveFilters(filters);
                    } else {
                        showNotification('Fehler beim Filtern: ' + (data.data || 'Unbekannter Fehler'), 'error');
                    }
                })
                .catch(error => {
                    console.error('Filter error:', error);
                    showNotification('Verbindungsfehler beim Filtern', 'error');
                });
            }
        function updateTable(requests) {
                const tableBody = document.querySelector('.users-table tbody');
                if (!tableBody) return;

                if (requests.length === 0) {
                    tableBody.innerHTML = `
                        <tr>
                            <td colspan="7" style="text-align: center; padding: 40px; color: #a0aec0;">
                                <i class="fas fa-search" style="font-size: 2rem; margin-bottom: 10px; opacity: 0.5;"></i>
                                <h3 style="margin: 10px 0;">Keine Anfragen gefunden</h3>
                                <p>Versuchen Sie andere Filterkriterien.</p>
                            </td>
                        </tr>
                    `;
                    return;
                }

                let html = '';
                requests.forEach(request => {
                    html += `
                        <tr>
                            <td>
                                <div style="text-align: center;">
                                    <input type="checkbox" class="request-checkbox" data-request-id="${request.request_id}" data-user-id="${request.user_id}">
                                </div>
                            </td>
                            <td>
                                <div style="text-align: center;">
                                    <span style="color: #6c5dd3; font-size: 14px; font-weight: 700; background: rgba(108, 93, 211, 0.1); padding: 4px 8px; border-radius: 6px; display: inline-block;">
                                        #${request.request_id}
                                    </span>
                                </div>
                            </td>
                            <td>
                                <div class="user-info">
                                    <div class="user-avatar">
                                        ${request.user_login.substring(0, 2).toUpperCase()}
                                    </div>
                                    <div class="user-details">
                                        <h4>${request.user_login}</h4>
                                        <p>${request.user_email}</p>
                                    </div>
                                </div>
                            </td>
                            <td>
                                <span class="benefit-badge ${request.benefit_type}">
                                    ${request.benefit_type === 'discount' ? 'Rabatt' : 'Provision'}
                                </span>
                            </td>
                            <td>
                                <span class="benefit-percentage">
                                    ${request.benefit_type === 'discount' ? request.discount_percentage : request.commission_percentage}%
                                </span>
                            </td>
                            <td>
                                ${request.created_at ? `
                                    <div style="text-align: center;">
                                        <div style="color: #6c5dd3; font-size: 10px; font-weight: 500; background: rgba(108, 93, 211, 0.1); padding: 2px 4px; border-radius: 3px; display: inline-block;">
                                            <i class="fas fa-calendar-alt" style="margin-right: 2px; font-size: 8px;"></i>
                                            ${new Date(request.created_at).toLocaleDateString('de-DE')}
                                        </div>
                                        <div style="color: #a0aec0; font-size: 9px; margin-top: 1px;">
                                            ${new Date(request.created_at).toLocaleTimeString('de-DE', {hour: '2-digit', minute: '2-digit'})}
                                        </div>
                                    </div>
                                ` : '<span style="color: #a0aec0; font-style: italic; font-size: 12px;">-</span>'}
                            </td>
                            <td>
                                <div style="display: flex; align-items: center; gap: 8px;">
                                    ${request.benefit_type === 'commission' ? `
                                        <div class="payment-status-dropdown">
                                            <button class="payment-status-btn ${request.payment_status === 'paid' ? 'paid' : 'not-paid'}" onclick="togglePaymentDropdown(${request.user_id})">
                                                <i class="fas ${request.payment_status === 'paid' ? 'fa-check-circle' : 'fa-times-circle'}"></i>
                                                ${request.payment_status === 'paid' ? 'Bezahlt' : 'Nicht bezahlt'}
                                                <i class="fas fa-chevron-down"></i>
                                            </button>
                                            <div class="payment-dropdown" id="payment-dropdown-${request.user_id}">
                                                <div class="payment-option not-paid" onclick="updatePaymentStatus(${request.user_id}, 'not_paid')">
                                                    <i class="fas fa-times-circle"></i>
                                                    Nicht bezahlt
                                                </div>
                                                <div class="payment-option paid" onclick="updatePaymentStatus(${request.user_id}, 'paid')">
                                                    <i class="fas fa-check-circle"></i>
                                                    Bezahlt
                                                </div>
                                            </div>
                                        </div>
                                    ` : `
                                        <button class="action-btn" onclick="viewUserDetails(${request.user_id})">
                                            <i class="fas fa-eye" style="margin-right: 5px;"></i>
                                            Details
                                        </button>
                                    `}
                                    
                                    <button class="log-btn" onclick="showUserLog(${request.user_id}, '${request.user_login}')">
                                        <i class="fas fa-history"></i>
                                        Log
                                    </button>
                                </div>
                            </td>
                        </tr>
                    `;
                });
                
                tableBody.innerHTML = html;
                initializeCheckboxes();
            }
        function updateStats(stats) {
                const statValues = document.querySelectorAll('.stat-value');
                if (statValues.length >= 3) {
                    statValues[0].textContent = stats.discount_requests || 0;
                    statValues[1].textContent = stats.commission_requests || 0;
                    statValues[2].textContent = stats.total_requests || 0;
                }
            }
        function updateActiveFilters(filters) {
                console.log('Update active filters called with:', filters);
                
                const activeFiltersDiv = document.querySelector('.active-filters');
                if (!activeFiltersDiv) {
                    console.log('Active filters div not found');
                    return;
                }

                const hasFilters = filters.benefit_type || filters.user_filter || filters.date_from || filters.date_to;
                console.log('Has filters:', hasFilters);
                
                if (hasFilters) {
                    let filterTags = '';
                    
                    if (filters.benefit_type) {
                        filterTags += `<span class="filter-tag" style="background: #6c5dd3; color: white; padding: 4px 8px; border-radius: 4px; font-size: 12px; margin-right: 8px;">
                            ${filters.benefit_type === 'discount' ? 'Rabatt' : 'Provision'}
                        </span>`;
                    }
                    
                    if (filters.user_filter) {
                        const selectedUser = userFilterSelect.options[userFilterSelect.selectedIndex];
                        filterTags += `<span class="filter-tag" style="background: #6c5dd3; color: white; padding: 4px 8px; border-radius: 4px; font-size: 12px; margin-right: 8px;">
                            Benutzer: ${selectedUser.text}
                        </span>`;
                    }
                    
                    if (filters.date_from) {
                        filterTags += `<span class="filter-tag" style="background: #6c5dd3; color: white; padding: 4px 8px; border-radius: 4px; font-size: 12px; margin-right: 8px;">
                            Von: ${filters.date_from}
                        </span>`;
                    }
                    
                    if (filters.date_to) {
                        filterTags += `<span class="filter-tag" style="background: #6c5dd3; color: white; padding: 4px 8px; border-radius: 4px; font-size: 12px; margin-right: 8px;">
                            Bis: ${filters.date_to}
                        </span>`;
                    }
                    
                    activeFiltersDiv.innerHTML = `
                        <div style="display: flex; align-items: center; gap: 12px; flex-wrap: wrap;">
                            <span style="color: #fcdc24; font-weight: 600; font-size: 14px;">
                                <i class="fas fa-filter" style="margin-right: 6px;"></i>
                                Aktive Filter:
                            </span>
                            ${filterTags}
                            <a href="#" onclick="resetFilters(); return false;" style="color: #ff6b6b; text-decoration: none; font-size: 12px; font-weight: 600;">
                                <i class="fas fa-times"></i> Alle Filter entfernen
                            </a>
                        </div>
                    `;
                    activeFiltersDiv.style.display = 'block';
                } else {
                    activeFiltersDiv.style.display = 'none';
                }
            }
        function initializeAnalytics() {
            console.log('Initializing analytics dashboard...');
            const analyticsData = calculateAnalyticsData();
            updateTotalDebt(analyticsData.totalDebt, analyticsData.highestDebt, analyticsData.debtAccounts);
            updateTopDebtors(analyticsData.topDebtors);
            updateSummaryStats(analyticsData.summary);
        }
        function calculateAnalyticsData() {
            const requests = <?php echo json_encode($requests); ?>;
            const users = <?php echo json_encode($users_with_benefits); ?>;
            
            let totalDebt = 0;
            let highestDebt = 0;
            let debtAccounts = 0;
            const userDebts = {};
            let discountCount = 0;
            let commissionCount = 0;
            requests.forEach(request => {
                const user = users.find(u => u.ID == request.user_id);
                if (!user) return;
                
                if (request.benefit_type === 'commission') {
                    const commissionAmount = parseFloat(request.commission_percentage) || 0;
                    totalDebt += commissionAmount;
                    commissionCount++;
                    
                    if (!userDebts[user.user_login]) {
                        userDebts[user.user_login] = 0;
                        debtAccounts++;
                    }
                    userDebts[user.user_login] += commissionAmount;
                    
                    if (userDebts[user.user_login] > highestDebt) {
                        highestDebt = userDebts[user.user_login];
                    }
                } else if (request.benefit_type === 'discount') {
                    discountCount++;
                }
            });
            const topDebtors = Object.entries(userDebts)
                .sort(([,a], [,b]) => b - a)
                .slice(0, 3)
                .map(([name, amount]) => ({ name, amount }));
            
            return {
                totalDebt: totalDebt,
                highestDebt: highestDebt,
                debtAccounts: debtAccounts,
                topDebtors: topDebtors,
                summary: {
                    discountCount: discountCount,
                    commissionCount: commissionCount,
                    totalCount: requests.length
                }
            };
        }
        function updateTotalDebt(amount, highestDebt, debtAccounts) {
            const debtElement = document.getElementById('total-debt-amount');
            
            if (debtElement) {
                const amountSpan = debtElement.querySelector('.amount');
                if (amountSpan) {
                    animateNumber(amountSpan, 0, amount, 2000);
                }
            }
            const highestDebtElement = document.getElementById('highest-debt');
            const debtAccountsElement = document.getElementById('debt-accounts');
            
            if (highestDebtElement) {
                highestDebtElement.textContent = '€' + highestDebt.toLocaleString();
            }
            
            if (debtAccountsElement) {
                debtAccountsElement.textContent = debtAccounts;
            }
        }
        function updateTopDebtors(topDebtors) {
            const topDebtorsContainer = document.getElementById('top-debtors');
            if (!topDebtorsContainer) return;
            
            const debtorItems = topDebtorsContainer.querySelectorAll('.debtor-item');
            
            debtorItems.forEach((item, index) => {
                const nameElement = item.querySelector('.name');
                const amountElement = item.querySelector('.amount');
                
                if (topDebtors[index]) {
                    nameElement.textContent = topDebtors[index].name;
                    amountElement.textContent = '€' + topDebtors[index].amount.toLocaleString();
                } else {
                    nameElement.textContent = '-';
                    amountElement.textContent = '€0';
                }
            });
        }
        function updateSummaryStats(summary) {
            const discountCountElement = document.getElementById('discount-count');
            const commissionCountElement = document.getElementById('commission-count');
            const totalCountElement = document.getElementById('total-count');
            
            if (discountCountElement) {
                discountCountElement.textContent = summary.discountCount;
            }
            
            if (commissionCountElement) {
                commissionCountElement.textContent = summary.commissionCount;
            }
            
            if (totalCountElement) {
                totalCountElement.textContent = summary.totalCount;
            }
        }
        function animateNumber(element, start, end, duration) {
            const startTime = performance.now();
            
            function updateNumber(currentTime) {
                const elapsed = currentTime - startTime;
                const progress = Math.min(elapsed / duration, 1);
                const current = start + (end - start) * easeOutCubic(progress);
                
                element.textContent = Math.round(current).toLocaleString();
                
                if (progress < 1) {
                    requestAnimationFrame(updateNumber);
                }
            }
            
            requestAnimationFrame(updateNumber);
        }
        function easeOutCubic(t) {
            return 1 - Math.pow(1 - t, 3);
        }
        function initializeCheckboxes() {
                const selectAllCheckbox = document.getElementById('select-all-requests');
                const requestCheckboxes = document.querySelectorAll('.request-checkbox');
                const bulkActions = document.getElementById('bulk-actions');
                const bulkActionsInfo = document.getElementById('bulk-actions-info');
                if (selectAllCheckbox) {
                    selectAllCheckbox.addEventListener('change', function() {
                        requestCheckboxes.forEach(checkbox => {
                            checkbox.checked = this.checked;
                        });
                        updateBulkActions();
                    });
                }
                requestCheckboxes.forEach(checkbox => {
                    checkbox.addEventListener('change', function() {
                        updateBulkActions();
                        updateSelectAllCheckbox();
                    });
                });

                function updateBulkActions() {
                    const checkedBoxes = document.querySelectorAll('.request-checkbox:checked');
                    const count = checkedBoxes.length;
                    
                    if (count > 0) {
                        bulkActions.classList.add('show');
                        bulkActionsInfo.textContent = `${count} ausgewählt`;
                    } else {
                        bulkActions.classList.remove('show');
                    }
                }

                function updateSelectAllCheckbox() {
                    const checkedBoxes = document.querySelectorAll('.request-checkbox:checked');
                    const totalBoxes = requestCheckboxes.length;
                    
                    if (checkedBoxes.length === 0) {
                        selectAllCheckbox.indeterminate = false;
                        selectAllCheckbox.checked = false;
                    } else if (checkedBoxes.length === totalBoxes) {
                        selectAllCheckbox.indeterminate = false;
                        selectAllCheckbox.checked = true;
                    } else {
                        selectAllCheckbox.indeterminate = true;
                        selectAllCheckbox.checked = false;
                    }
                }
            }
            initializeCheckboxes();
        });
        function resetFilters() {
            console.log('Reset filters called');
            const benefitTypeSelect = document.getElementById('benefit_type');
            const userFilterSelect = document.getElementById('user_filter');
            const dateFromInput = document.getElementById('date_from');
            const dateToInput = document.getElementById('date_to');
            
            if (benefitTypeSelect) benefitTypeSelect.value = '';
            if (userFilterSelect) userFilterSelect.value = '';
            if (dateFromInput) dateFromInput.value = '';
            if (dateToInput) dateToInput.value = '';
            const activeFiltersDiv = document.querySelector('.active-filters');
            if (activeFiltersDiv) {
                activeFiltersDiv.style.display = 'none';
            }
            console.log('Making reset AJAX call...');
            fetch('<?php echo admin_url('admin-ajax.php'); ?>', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                },
                body: new URLSearchParams({
                    'action': 'eltern_filter_requests',
                    'benefit_type': '',
                    'user_filter': '',
                    'date_from': '',
                    'date_to': '',
                    'nonce': '<?php echo wp_create_nonce('eltern_filter_requests'); ?>'
                })
            })
            .then(response => {
                console.log('Reset response status:', response.status);
                return response.json();
            })
            .then(data => {
                console.log('Reset response data:', data);
                if (data.success) {
                    updateTable(data.data.requests);
                    updateStats(data.data.stats);
                    updateActiveFilters({
                        benefit_type: '',
                        user_filter: '',
                        date_from: '',
                        date_to: ''
                    });
                    console.log('Reset successful');
                } else {
                    console.error('Reset failed:', data);
                    showNotification('Fehler beim Zurücksetzen: ' + (data.data || 'Unbekannter Fehler'), 'error');
                }
            })
            .catch(error => {
                console.error('Reset error:', error);
                showNotification('Verbindungsfehler beim Zurücksetzen', 'error');
            });
        }
        function bulkMarkAsPaid() {
            const checkedBoxes = document.querySelectorAll('.request-checkbox:checked');
            if (checkedBoxes.length === 0) {
                alert('Bitte wählen Sie mindestens eine Anfrage aus.');
                return;
            }

            if (confirm(`Möchten Sie ${checkedBoxes.length} ausgewählte Anfragen als bezahlt markieren?`)) {
                const userIds = Array.from(checkedBoxes).map(cb => cb.dataset.userId);
                updateBulkPaymentStatus(userIds, 'paid');
            }
        }

        function bulkMarkAsUnpaid() {
            const checkedBoxes = document.querySelectorAll('.request-checkbox:checked');
            if (checkedBoxes.length === 0) {
                alert('Bitte wählen Sie mindestens eine Anfrage aus.');
                return;
            }

            if (confirm(`Möchten Sie ${checkedBoxes.length} ausgewählte Anfragen als nicht bezahlt markieren?`)) {
                const userIds = Array.from(checkedBoxes).map(cb => cb.dataset.userId);
                updateBulkPaymentStatus(userIds, 'not_paid');
            }
        }

        function bulkDelete() {
            const checkedBoxes = document.querySelectorAll('.request-checkbox:checked');
            if (checkedBoxes.length === 0) {
                alert('Bitte wählen Sie mindestens eine Anfrage aus.');
                return;
            }

            if (confirm(`Möchten Sie ${checkedBoxes.length} ausgewählte Anfragen wirklich löschen? Diese Aktion kann nicht rückgängig gemacht werden.`)) {
                const requestIds = Array.from(checkedBoxes).map(cb => cb.dataset.requestId);
                deleteBulkRequests(requestIds);
            }
        }

        function updateBulkPaymentStatus(userIds, status) {
            const bulkActions = document.getElementById('bulk-actions');
            const originalContent = bulkActions.innerHTML;
            bulkActions.innerHTML = '<span class="bulk-actions-info">Aktualisiere...</span>';
            fetch('<?php echo admin_url('admin-ajax.php'); ?>', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                },
                body: new URLSearchParams({
                    'action': 'bulk_update_payment_status',
                    'user_ids': JSON.stringify(userIds),
                    'status': status,
                    'nonce': '<?php echo wp_create_nonce('bulk_update_payment_status'); ?>'
                })
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    showNotification(`${userIds.length} Anfragen erfolgreich aktualisiert!`, 'success');
                    document.querySelectorAll('.request-checkbox:checked').forEach(cb => {
                        cb.checked = false;
                    });
                    document.getElementById('select-all-requests').checked = false;
                    document.getElementById('bulk-actions').classList.remove('show');
                    setTimeout(() => {
                        location.reload();
                    }, 1000);
                } else {
                    showNotification('Fehler beim Aktualisieren: ' + (data.data || 'Unbekannter Fehler'), 'error');
                    bulkActions.innerHTML = originalContent;
                }
            })
            .catch(error => {
                console.error('Error:', error);
                showNotification('Verbindungsfehler beim Aktualisieren', 'error');
                bulkActions.innerHTML = originalContent;
            });
        }

        function deleteBulkRequests(requestIds) {
            const bulkActions = document.getElementById('bulk-actions');
            const originalContent = bulkActions.innerHTML;
            bulkActions.innerHTML = '<span class="bulk-actions-info">Lösche...</span>';
            fetch('<?php echo admin_url('admin-ajax.php'); ?>', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                },
                body: new URLSearchParams({
                    'action': 'bulk_delete_requests',
                    'request_ids': JSON.stringify(requestIds),
                    'nonce': '<?php echo wp_create_nonce('bulk_delete_requests'); ?>'
                })
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    showNotification(`${requestIds.length} Anfragen erfolgreich gelöscht!`, 'success');
                    requestIds.forEach(id => {
                        const checkbox = document.querySelector(`[data-request-id="${id}"]`);
                        if (checkbox) {
                            checkbox.closest('tr').remove();
                        }
                    });
                    document.getElementById('bulk-actions').classList.remove('show');
                    setTimeout(() => {
                        location.reload();
                    }, 1000);
                } else {
                    showNotification('Fehler beim Löschen: ' + (data.data || 'Unbekannter Fehler'), 'error');
                    bulkActions.innerHTML = originalContent;
                }
            })
            .catch(error => {
                console.error('Error:', error);
                showNotification('Verbindungsfehler beim Löschen', 'error');
                bulkActions.innerHTML = originalContent;
            });
        }

        function showNotification(message, type = 'info') {
            const notification = document.createElement('div');
            notification.style.cssText = 'position: fixed; top: 20px; right: 20px; background: ' + 
                (type === 'success' ? '#28a745' : type === 'error' ? '#dc3545' : '#6c5dd3') + 
                '; color: white; padding: 15px; border-radius: 5px; z-index: 9999; font-weight: 600;';
            notification.textContent = message;
            document.body.appendChild(notification);
            
            setTimeout(() => {
                notification.remove();
            }, 3000);
        }
    </script>
</body>
</html>