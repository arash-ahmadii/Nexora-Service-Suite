<?php
if (class_exists('Nexora_User_Registration')) {
    if (!Nexora_User_Registration::user_has_access()) {
        $approval_message = Nexora_User_Registration::get_approval_status_message();
        if ($approval_message) {
            echo '<div class="Nexora Service Suite-approval-message error" style="background: #ffebee; color: #c62828; padding: 15px; border-radius: 4px; margin: 20px 0; border: 1px solid #ef9a9a;">';
            echo '<strong>Zugriff verweigert:</strong> ' . esc_html($approval_message);
            echo '</div>';
            return;
        }
    }
}
global $wpdb;
$services_count = (int)$wpdb->get_var("SELECT COUNT(*) FROM {$wpdb->prefix}nexora_services");
$requests_count = (int)$wpdb->get_var("SELECT COUNT(*) FROM {$wpdb->prefix}nexora_service_requests");
$brands_count = (int)$wpdb->get_var("SELECT COUNT(*) FROM {$wpdb->prefix}nexora_brands");
$customers_count = (int)$wpdb->get_var("SELECT COUNT(*) FROM {$wpdb->prefix}nexora_customer_info");
$pending_requests = (int)$wpdb->get_var("SELECT COUNT(*) FROM {$wpdb->prefix}nexora_service_requests WHERE status = 'pending'");
$in_progress_requests = (int)$wpdb->get_var("SELECT COUNT(*) FROM {$wpdb->prefix}nexora_service_requests WHERE status = 'in_progress'");
$completed_requests = (int)$wpdb->get_var("SELECT COUNT(*) FROM {$wpdb->prefix}nexora_service_requests WHERE status = 'completed'");
$devices_count = (int)$wpdb->get_var("SELECT COUNT(*) FROM {$wpdb->prefix}nexora_devices");
$smartphones_count = (int)$wpdb->get_var("SELECT COUNT(*) FROM {$wpdb->prefix}nexora_devices WHERE device_type = 'smartphone'");
$laptops_count = (int)$wpdb->get_var("SELECT COUNT(*) FROM {$wpdb->prefix}nexora_devices WHERE device_type = 'laptop'");
$tablets_count = (int)$wpdb->get_var("SELECT COUNT(*) FROM {$wpdb->prefix}nexora_devices WHERE device_type = 'tablet'");
$total_users = (int)$wpdb->get_var("SELECT COUNT(*) FROM {$wpdb->users}");
$approved_users = (int)$wpdb->get_var("SELECT COUNT(*) FROM {$wpdb->prefix}nexora_user_status WHERE destination_status_id = 1");
$recent_requests = $wpdb->get_results("
    SELECT sr.*, u.display_name 
    FROM {$wpdb->prefix}nexora_service_requests sr
    LEFT JOIN {$wpdb->users} u ON sr.user_id = u.ID
    ORDER BY sr.created_at DESC LIMIT 5
");
$monthly_data = $wpdb->get_results("
    SELECT DATE_FORMAT(created_at, '%Y-%m') as month, COUNT(*) as count
    FROM {$wpdb->prefix}nexora_service_requests
    WHERE created_at >= DATE_SUB(NOW(), INTERVAL 12 MONTH)
    GROUP BY DATE_FORMAT(created_at, '%Y-%m')
    ORDER BY month ASC
");
$months = [];
$request_counts = [];
foreach ($monthly_data as $data) {
    $months[] = date('M', strtotime($data->month . '-01'));
    $request_counts[] = (int)$data->count;
}
$plugin_url = plugin_dir_url(dirname(__FILE__));
?>

<!DOCTYPE html>
<html lang="de">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Nexora Service Suite Dashboard</title>
    
    
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    
    
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    
    
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    
    
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
    

    
    @keyframes float {
        0%, 100% { transform: translateY(0px) rotate(0deg); }
        50% { transform: translateY(-30px) rotate(180deg); }
    }
    
    .dashboard-container {
        padding: 24px;
        width: calc(100% - 80px);
        max-width: calc(100% - 80px);
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
    
    .search-box {
        display: flex;
        align-items: center;
        gap: 12px;
        background: rgba(255, 255, 255, 0.1);
        padding: 8px 16px;
        border-radius: 8px;
        border: 1px solid rgba(255, 255, 255, 0.2);
    }
    
    .search-box input {
        background: transparent;
        border: none;
        color: #FFFFFF;
        outline: none;
        width: 200px;
    }
    
    .search-box input::placeholder {
        color: #CBD5E0;
    }
    
    .user-menu {
        display: flex;
        align-items: center;
        gap: 8px;
        color: #CBD5E0;
        cursor: pointer;
    }
    
    .stats-row {
        display: grid;
        grid-template-columns: repeat(4, 1fr);
        gap: 24px;
        margin-bottom: 32px;
    }
    
    .stat-card {
        display: flex;
        align-items: center;
        gap: 16px;
    }
    
    .stat-icon {
        width: 48px;
        height: 48px;
        border-radius: 12px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 20px;
        color: white;
    }
    
    .stat-icon.purple { background: linear-gradient(135deg, #6C5DD3, #8B5CF6); }
    .stat-icon.blue { background: linear-gradient(135deg, #00E5FF, #06B6D4); }
    .stat-icon.green { background: linear-gradient(135deg, #4ECDC4, #10B981); }
    .stat-icon.orange { background: linear-gradient(135deg, #FFB347, #F59E0B); }
    
    .stat-content h3 {
        font-size: 12px;
        color: #CBD5E0;
        margin-bottom: 4px;
        font-weight: 500;
    }
    
    .stat-value {
        font-size: 20px;
        font-weight: 700;
        margin-bottom: 4px;
    }
    
    .stat-change {
        font-size: 11px;
        font-weight: 600;
        display: flex;
        align-items: center;
        gap: 4px;
    }
    
    .stat-change.positive { color: #4ECDC4; }
    .stat-change.negative { color: #FF6B6B; }
    
    .dashboard-grid {
        display: grid;
        grid-template-columns: 2fr 1fr 1fr;
        gap: 24px;
        margin-bottom: 32px;
    }
    
    .welcome-card {
        grid-row: span 2;
        position: relative;
        overflow: hidden;
    }
    
    .welcome-content {
        position: relative;
        z-index: 2;
    }
    
    .welcome-content h2 {
        font-size: 24px;
        font-weight: 700;
        margin-bottom: 8px;
    }
    
    .welcome-content p {
        color: #CBD5E0;
        margin-bottom: 24px;
    }
    
    .welcome-btn {
        background: linear-gradient(135deg, #6C5DD3, #8B5CF6);
        border: none;
        color: white;
        padding: 12px 24px;
        border-radius: 8px;
        font-weight: 600;
        cursor: pointer;
        display: flex;
        align-items: center;
        gap: 8px;
        transition: all 0.3s ease;
    }
    
    .welcome-btn:hover {
        transform: translateY(-2px);
        box-shadow: 0 8px 25px rgba(108, 93, 211, 0.4);
    }
    
    .welcome-image {
        position: absolute;
        top: 0;
        right: 0;
        width: 100%;
        height: 100%;
        z-index: 1;
    }
    

    
    .satisfaction-card h3 {
        font-size: 18px;
        font-weight: 600;
        margin-bottom: 20px;
    }
    
    .gauge-container {
        display: flex;
        justify-content: center;
        margin-bottom: 16px;
    }
    
    .gauge {
        width: 120px;
        height: 120px;
        position: relative;
        display: flex;
        align-items: center;
        justify-content: center;
    }
    
    .gauge-fill {
        width: 100%;
        height: 100%;
        border: 8px solid rgba(255, 255, 255, 0.1);
        border-top: 8px solid #4ECDC4;
        border-radius: 50%;
        transform: rotate(-90deg);
        animation: gaugeFill 2s ease-out;
    }
    
    @keyframes gaugeFill {
        from { transform: rotate(-90deg) scale(0.8); }
        to { transform: rotate(-90deg) scale(1); }
    }
    
    .gauge-text {
        position: absolute;
        font-size: 24px;
        font-weight: 700;
        color: #4ECDC4;
    }
    
    .gauge-emoji {
        position: absolute;
        top: 50%;
        left: 50%;
        transform: translate(-50%, -50%);
        font-size: 20px;
        margin-top: 20px;
    }
    
    .satisfaction-card p {
        text-align: center;
        color: #CBD5E0;
        font-size: 14px;
    }
    
    .referral-card h3 {
        font-size: 18px;
        font-weight: 600;
        margin-bottom: 20px;
    }
    
    .referral-stats {
        margin-bottom: 24px;
    }
    
    .referral-item {
        display: flex;
        justify-content: space-between;
        margin-bottom: 12px;
    }
    
    .referral-item .label {
        color: #CBD5E0;
        font-size: 14px;
    }
    
    .referral-item .value {
        font-weight: 600;
        font-size: 14px;
    }
    
    .safety-gauge {
        text-align: center;
    }
    
    .gauge-label {
        font-size: 16px;
        font-weight: 600;
        margin-bottom: 12px;
        color: #4ECDC4;
    }
    
    .gauge-bar {
        width: 100%;
        height: 8px;
        background: rgba(255, 255, 255, 0.1);
        border-radius: 4px;
        overflow: hidden;
        margin-bottom: 8px;
    }
    
    .gauge-fill.green {
        height: 100%;
        background: linear-gradient(90deg, #4ECDC4, #10B981);
        border-radius: 4px;
        animation: gaugeBarFill 2s ease-out;
    }
    
    @keyframes gaugeBarFill {
        from { width: 0%; }
        to { width: 93%; }
    }
    
    .gauge-score {
        font-size: 12px;
        color: #CBD5E0;
    }
    
    .charts-grid {
        display: grid;
        grid-template-columns: 2fr 1fr;
        gap: 24px;
        margin-bottom: 32px;
    }
    
    .chart-card {
        min-height: 400px;
    }
    
    .chart-card.large {
        grid-column: span 2;
    }
    
    .chart-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 24px;
    }
    
    .chart-header h3 {
        font-size: 18px;
        font-weight: 600;
    }
    
    .chart-legend {
        display: flex;
        gap: 16px;
    }
    
    .legend-item {
        display: flex;
        align-items: center;
        gap: 8px;
        font-size: 14px;
        color: #CBD5E0;
    }
    
    .legend-dot {
        width: 12px;
        height: 12px;
        border-radius: 50%;
    }
    
    .legend-dot.purple { background: #6C5DD3; }
    .legend-dot.blue { background: #00E5FF; }
    
    .chart-container {
        height: 300px;
        position: relative;
    }
    
    .chart-stats {
        display: grid;
        grid-template-columns: repeat(4, 1fr);
        gap: 16px;
        margin-top: 24px;
    }
    
    .stat-item {
        display: flex;
        align-items: center;
        gap: 8px;
        font-size: 12px;
        color: #CBD5E0;
    }
    
    .analytics-grid {
        display: grid;
        grid-template-columns: repeat(2, 1fr);
        gap: 24px;
    }
    
    .projects-card h3 {
        font-size: 18px;
        font-weight: 600;
        margin-bottom: 20px;
    }
    
    .project-count {
        color: #CBD5E0;
        font-size: 14px;
        font-weight: 400;
    }
    
    .project-list {
        display: flex;
        flex-direction: column;
        gap: 16px;
    }
    
    .project-item {
        display: flex;
        justify-content: space-between;
        align-items: center;
    }
    
    .project-info h4 {
        font-size: 14px;
        font-weight: 600;
        margin-bottom: 4px;
    }
    
    .project-info p {
        font-size: 12px;
        color: #CBD5E0;
    }
    
    .project-progress {
        display: flex;
        align-items: center;
        gap: 12px;
    }
    
    .progress-bar {
        width: 80px;
        height: 6px;
        background: rgba(255, 255, 255, 0.1);
        border-radius: 3px;
        overflow: hidden;
    }
    
    .progress-fill {
        height: 100%;
        background: linear-gradient(90deg, #6C5DD3, #8B5CF6);
        border-radius: 3px;
        transition: width 1s ease;
    }
    
    .progress-text {
        font-size: 12px;
        font-weight: 600;
        color: #6C5DD3;
        min-width: 30px;
    }
    
    .orders-card h3 {
        font-size: 18px;
        font-weight: 600;
        margin-bottom: 20px;
    }
    
    .order-change {
        color: #4ECDC4;
        font-size: 14px;
        font-weight: 400;
    }
    
    .order-list {
        display: flex;
        flex-direction: column;
        gap: 16px;
    }
    
    .order-item {
        display: flex;
        align-items: center;
        gap: 16px;
    }
    
    .order-icon {
        width: 40px;
        height: 40px;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 16px;
        color: white;
        background: linear-gradient(135deg, #6C5DD3, #8B5CF6);
    }
    
    .order-info h4 {
        font-size: 14px;
        font-weight: 600;
        margin-bottom: 4px;
    }
    
    .order-info p {
        font-size: 12px;
        color: #CBD5E0;
    }
    
    .order-status {
        padding: 4px 12px;
        border-radius: 20px;
        font-size: 11px;
        font-weight: 600;
        text-transform: uppercase;
        margin-left: auto;
    }
    
    .order-status.completed { background: rgba(78, 205, 196, 0.2); color: #4ECDC4; }
    .order-status.pending { background: rgba(255, 193, 7, 0.2); color: #FFB347; }
    .order-status.in-progress { background: rgba(0, 229, 255, 0.2); color: #00E5FF; }
    
    @media (max-width: 1200px) {
        .dashboard-container {
            width: calc(100% - 80px);
            max-width: calc(100% - 80px);
            margin-left: 80px;
            overflow-x: hidden;
        }
        
        .dashboard-grid {
            grid-template-columns: 1fr;
        }
        
        .welcome-card {
            grid-row: span 1;
        }
        
        .charts-grid {
            grid-template-columns: 1fr;
        }
        
        .chart-card.large {
            grid-column: span 1;
        }
        
        .analytics-grid {
            grid-template-columns: 1fr;
        }
    }
    
    @media (max-width: 768px) {
        .dashboard-container {
            width: calc(100% - 80px);
            max-width: calc(100% - 80px);
            margin-left: 80px;
            overflow-x: hidden;
            padding: 16px;
        }
        
        .stats-row {
            grid-template-columns: repeat(2, 1fr);
        }
        
        .chart-stats {
            grid-template-columns: repeat(2, 1fr);
        }
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
    
    @keyframes slideInLeft {
        to {
            opacity: 1;
            transform: translateX(0);
        }
    }
    
    
    .dashboard-container {
        margin-left: 80px;
        width: calc(100% - 80px);
        max-width: calc(100% - 80px);
        overflow-x: hidden;
    }
    </style>
</head>
<body class="Nexora Service Suite-dashboard">
    

    
    
    <nav class="vertical-nav">
        <div class="nav-toggle">
            <i class="fas fa-bars"></i>
            <span class="nav-label">Menü</span>
        </div>
        
        <ul class="nav-menu">
            <li class="nav-item">
                <a href="<?php echo admin_url('admin.php?page=Nexora Service Suite-main'); ?>" class="nav-link active" title="Dashboard">
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
                <a href="<?php echo admin_url('admin.php?page=Nexora Service Suite-service-request'); ?>" class="nav-link" title="Anfragen" id="nav-anfragen">
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
                <a href="<?php echo admin_url('admin.php?page=Nexora Service Suite-eltern'); ?>" class="nav-link" title="Eltern">
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
        
        
        <div class="stats-row">
            <div class="stat-card span-4">
                <div class="stat-icon purple">
                    <i class="fas fa-euro-sign"></i>
                </div>
                <div class="stat-content">
                    <h3>Heutiges Geld</h3>
                    <div class="stat-value">€<?php echo number_format($requests_count * 50); ?></div>
                    <div class="stat-change positive">
                        <i class="fas fa-arrow-up"></i>
                        <span>+<?php echo $pending_requests > 0 ? round(($pending_requests / $requests_count) * 100) : 0; ?>%</span>
                    </div>
                </div>
            </div>
            
            <div class="stat-card span-4">
                <div class="stat-icon blue">
                    <i class="fas fa-users"></i>
                </div>
                <div class="stat-content">
                    <h3>Heutige Benutzer</h3>
                    <div class="stat-value"><?php echo number_format($customers_count); ?></div>
                    <div class="stat-change positive">
                        <i class="fas fa-arrow-up"></i>
                        <span>+<?php echo $approved_users > 0 ? round(($approved_users / $customers_count) * 100) : 0; ?>%</span>
                    </div>
                </div>
            </div>
            
            <div class="stat-card span-4">
                <div class="stat-icon green">
                    <i class="fas fa-user-plus"></i>
                </div>
                <div class="stat-content">
                    <h3>Neue Kunden</h3>
                    <div class="stat-value">+<?php echo number_format($approved_users); ?></div>
                    <div class="stat-change negative">
                        <i class="fas fa-arrow-down"></i>
                        <span>-<?php echo ($total_users - $approved_users) > 0 ? round((($total_users - $approved_users) / $total_users) * 100) : 0; ?>%</span>
                    </div>
                </div>
            </div>
        </div>
        
        
        <div class="dashboard-grid">
            
            
            <div class="welcome-card span-4">
                <div class="welcome-content">
                    <h2>Willkommen zurück!</h2>
                    <p>Schön, Sie wiederzusehen! Hier ist Ihre Reparatur-Service-Übersicht.</p>
                    <button class="welcome-btn">
                        <i class="fas fa-microphone"></i>
                        Zum Aufnehmen tippen →
                    </button>
                </div>

            </div>
            
            
            <div class="satisfaction-card span-4">
                <h3>Zufriedenheitsrate</h3>
                <div class="gauge-container">
                    <div class="gauge" data-value="95">
                        <div class="gauge-fill"></div>
                        <div class="gauge-text">95%</div>
                        <div class="gauge-emoji">😊</div>
                    </div>
                </div>
                <p>Basierend auf Kundenfeedback</p>
            </div>
            
            
            <div class="referral-card span-4">
                <h3>Empfehlungsverfolgung</h3>
                <div class="referral-stats">
                    <div class="referral-item">
                        <span class="label">Eingeladen</span>
                        <span class="value"><?php echo $customers_count; ?> Personen</span>
                    </div>
                    <div class="referral-item">
                        <span class="label">Bonus</span>
                        <span class="value"><?php echo number_format($requests_count * 10); ?></span>
                    </div>
                </div>
                <div class="safety-gauge">
                    <div class="gauge-label">Sicherheit 9.3</div>
                    <div class="gauge-bar">
                        <div class="gauge-fill green" style="width: 93%"></div>
                    </div>
                    <div class="gauge-score">Gesamtpunktzahl</div>
                </div>
            </div>
        </div>
        
        
        <div class="charts-grid">
            
            
            <div class="chart-card span-4">
                <div class="chart-header">
                    <h3>Serviceanfragen Übersicht</h3>
                    <div class="chart-legend">
                        <span class="legend-item">
                            <span class="legend-dot purple"></span>
                            Anfragen
                        </span>
                        <span class="legend-item">
                            <span class="legend-dot blue"></span>
                            Abgeschlossen
                        </span>
                    </div>
                </div>
                <div class="chart-container">
                    <canvas id="salesChart"></canvas>
                </div>
            </div>
            
            
            <div class="chart-card span-4">
                <div class="chart-header">
                    <h3>Aktive Benutzer</h3>
                </div>
                <div class="chart-container">
                    <canvas id="usersChart"></canvas>
                </div>
                <div class="stat-item">
                    <i class="fas fa-users"></i>
                    <span>Benutzer <?php echo number_format($total_users); ?></span>
                </div>
                <div class="stat-item">
                    <i class="fas fa-mouse-pointer"></i>
                    <span>Klicks <?php echo number_format($requests_count * 100); ?></span>
                </div>
                <div class="stat-item">
                    <i class="fas fa-shopping-cart"></i>
                    <span>Verkäufe €<?php echo number_format($requests_count * 50); ?></span>
                </div>
                <div class="stat-item">
                    <i class="fas fa-box"></i>
                    <span>Artikel <?php echo $devices_count; ?></span>
                </div>
            </div>
        </div>
        
        
        <div class="analytics-grid">
            
            
            <div class="projects-card span-4">
                <h3>Projekte (<?php echo $completed_requests; ?> diesen Monat erledigt)</h3>
                <div class="project-list">
                    <div class="project-item">
                        <div class="project-info">
                            <h4>Smartphone-Reparaturen</h4>
                            <p>Fortschritt</p>
                        </div>
                        <div class="project-progress">
                            <div class="progress-bar">
                                <div class="progress-fill" style="width: <?php echo $devices_count > 0 ? ($smartphones_count / $devices_count) * 100 : 0; ?>%"></div>
                            </div>
                            <span class="progress-text"><?php echo $devices_count > 0 ? round(($smartphones_count / $devices_count) * 100) : 0; ?>%</span>
                        </div>
                    </div>
                    
                    <div class="project-item">
                        <div class="project-info">
                            <h4>Laptop-Services</h4>
                            <p>Fortschritt</p>
                        </div>
                        <div class="project-progress">
                            <div class="progress-bar">
                                <div class="progress-fill" style="width: <?php echo $devices_count > 0 ? ($laptops_count / $devices_count) * 100 : 0; ?>%"></div>
                            </div>
                            <span class="progress-text"><?php echo $devices_count > 0 ? round(($laptops_count / $devices_count) * 100) : 0; ?>%</span>
                        </div>
                    </div>
                    
                    <div class="project-item">
                        <div class="project-info">
                            <h4>Tablet-Reparaturen</h4>
                            <p>Fortschritt</p>
                        </div>
                        <div class="project-progress">
                            <div class="progress-bar">
                                <div class="progress-fill" style="width: <?php echo $devices_count > 0 ? ($tablets_count / $devices_count) * 100 : 0; ?>%"></div>
                            </div>
                            <span class="progress-text"><?php echo $devices_count > 0 ? round(($tablets_count / $devices_count) * 100) : 0; ?>%</span>
                        </div>
                    </div>
                </div>
            </div>
            
            
            <div class="orders-card span-4">
                <h3>Bestellungen Übersicht (+<?php echo $pending_requests > 0 ? round(($pending_requests / $requests_count) * 100) : 0; ?>% diesen Monat)</h3>
                <div class="order-list">
                    <?php if (!empty($recent_requests)): ?>
                        <?php foreach (array_slice($recent_requests, 0, 3) as $request): ?>
                            <div class="order-item">
                                <div class="order-icon">
                                    <i class="fas fa-tools"></i>
                                </div>
                                <div class="order-info">
                                    <h4>€<?php echo number_format(rand(50, 200)); ?>, <?php echo esc_html($request->title ?: 'Service Request'); ?></h4>
                                    <p><?php echo date('d M g:i A', strtotime($request->created_at ?: 'now')); ?></p>
                                </div>
                                <div class="order-status <?php echo esc_attr($request->status ?: 'pending'); ?>">
                                    <?php 
                                    $status_text = '';
                                    switch($request->status) {
                                        case 'pending': $status_text = 'Ausstehend'; break;
                                        case 'in_progress': $status_text = 'In Bearbeitung'; break;
                                        case 'completed': $status_text = 'Abgeschlossen'; break;
                                        default: $status_text = 'Ausstehend';
                                    }
                                    echo esc_html($status_text);
                                    ?>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <div class="order-item">
                            <div class="order-icon">
                                <i class="fas fa-tools"></i>
                            </div>
                            <div class="order-info">
                                <h4>€150, Smartphone Repair</h4>
                                <p>22 DEC 7:20 PM</p>
                            </div>
                            <div class="order-status completed">Abgeschlossen</div>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>

    
    <script>
    window.nexoraDashboardData = {
        salesChart: {
            labels: <?php echo json_encode($months); ?>,
            data: <?php echo json_encode($request_counts); ?>
        },
        usersChart: {
            labels: ['Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat', 'Sun'],
            data: [<?php echo $requests_count; ?>, <?php echo $customers_count; ?>, <?php echo $devices_count; ?>, <?php echo $services_count; ?>, <?php echo $brands_count; ?>, <?php echo $total_users; ?>, <?php echo $approved_users; ?>]
        }
    };
    </script>
    
    
    <script src="<?php echo esc_url($plugin_url . 'assets/js/admin-dashboard.js'); ?>"></script>
    
    <script>
    document.addEventListener('DOMContentLoaded', function() {
        console.log('Dashboard geladen, initialisiere Diagramme...');
        function loadNotificationCounts() {
            fetch(ajaxurl, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                },
                body: 'action=get_new_requests_count&nonce=<?php echo wp_create_nonce("nexora_notifications_nonce"); ?>'
            })
            .then(response => response.json())
            .then(data => {
                if(data.success && data.data.count > 0) {
                    document.getElementById('nav-anfragen-badge').textContent = data.data.count;
                    document.getElementById('nav-anfragen-badge').style.display = 'flex';
                } else {
                    document.getElementById('nav-anfragen-badge').style.display = 'none';
                }
            });
            fetch(ajaxurl, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                },
                body: 'action=nexora_get_new_users_count&nonce=<?php echo wp_create_nonce("nexora_nonce"); ?>'
            })
            .then(response => response.json())
            .then(data => {
                if(data.success && data.data.count > 0) {
                    document.getElementById('nav-benutzer-badge').textContent = data.data.count;
                    document.getElementById('nav-benutzer-badge').style.display = 'flex';
                } else {
                    document.getElementById('nav-benutzer-badge').style.display = 'none';
                }
            });
            fetch(ajaxurl, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                },
                body: 'action=nexora_get_admin_notifications&nonce=<?php echo wp_create_nonce("nexora_nonce"); ?>'
            })
            .then(response => response.json())
            .then(data => {
                if(data.success && data.data.length > 0) {
                    document.getElementById('nav-nachrichten-badge').textContent = data.data.length;
                    document.getElementById('nav-nachrichten-badge').style.display = 'flex';
                } else {
                    document.getElementById('nav-nachrichten-badge').style.display = 'none';
                }
            });
        }
        loadNotificationCounts();
        setInterval(loadNotificationCounts, 60000);
        const salesCtx = document.getElementById('salesChart');
        if (salesCtx && window.nexoraDashboardData.salesChart) {
            new Chart(salesCtx, {
                type: 'line',
                data: {
                    labels: window.nexoraDashboardData.salesChart.labels,
                    datasets: [{
                        label: 'Service Requests',
                        data: window.nexoraDashboardData.salesChart.data,
                        borderColor: '#6C5DD3',
                        backgroundColor: 'rgba(108, 93, 211, 0.1)',
                        borderWidth: 3,
                        fill: true,
                        tension: 0.4,
                        pointBackgroundColor: '#6C5DD3',
                        pointBorderColor: '#FFFFFF',
                        pointBorderWidth: 2,
                        pointRadius: 6,
                        pointHoverRadius: 8
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: { display: false }
                    },
                    scales: {
                        x: {
                            grid: { color: 'rgba(255, 255, 255, 0.1)' },
                            ticks: { color: '#CBD5E0' }
                        },
                        y: {
                            grid: { color: 'rgba(255, 255, 255, 0.1)' },
                            ticks: { color: '#CBD5E0' }
                        }
                    }
                }
            });
        }
        const usersCtx = document.getElementById('usersChart');
        if (usersCtx && window.nexoraDashboardData.usersChart) {
            new Chart(usersCtx, {
                type: 'bar',
                data: {
                    labels: window.nexoraDashboardData.usersChart.labels,
                    datasets: [{
                        label: 'Active Users',
                        data: window.nexoraDashboardData.usersChart.data,
                        backgroundColor: '#00E5FF',
                        borderColor: '#00E5FF',
                        borderWidth: 0,
                        borderRadius: 4
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: { display: false }
                    },
                    scales: {
                        x: {
                            grid: { color: 'rgba(255, 255, 255, 0.1)' },
                            ticks: { color: '#CBD5E0' }
                        },
                        y: {
                            grid: { color: 'rgba(255, 255, 255, 0.1)' },
                            ticks: { color: '#CBD5E0' }
                        }
                    }
                }
            });
        }
    });
    </script>
</body>
</html>
