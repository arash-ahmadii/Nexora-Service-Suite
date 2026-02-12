<!DOCTYPE html>
<html lang="de">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Geräteverwaltung - Nexora Service Suite Dashboard</title>
    
    
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    
    
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    
    
    <script>
        var ajaxurl = '<?php echo admin_url('admin-ajax.php'); ?>';
        var nexora_admin = {
            nonce: '<?php echo wp_create_nonce('nexora_device_nonce'); ?>'
        };
    </script>

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
        background: #ff4757;
        color: white;
        border-radius: 50%;
        min-width: 18px;
        height: 18px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 11px;
        font-weight: bold;
        z-index: 10;
        animation: badgePulse 2s infinite;
    }
    
    .nav-badge.awaiting-mod {
        background: #ff6b35;
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
        padding: 24px;
        max-width: 1400px;
        margin: 0 auto;
        margin-left: 80px;
        width: calc(100% - 80px);
        overflow-x: hidden;
        box-sizing: border-box;
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
    
    
    .page-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 24px;
        padding-bottom: 20px;
        border-bottom: 1px solid rgba(255, 255, 255, 0.1);
    }
    
    .page-title {
        color: #fcdc24;
        font-size: 28px;
        font-weight: 600;
        margin: 0;
    }
    
    
    .nav-tab-wrapper {
        display: flex;
        gap: 8px;
        margin-bottom: 24px;
        background: rgba(26, 31, 43, 0.3);
        padding: 8px;
        border-radius: 12px;
        border: 1px solid rgba(255, 255, 255, 0.1);
    }
    
    .nav-tab {
        padding: 12px 20px;
        border: none;
        background: transparent;
        color: rgba(255, 255, 255, 0.7);
        border-radius: 8px;
        cursor: pointer;
        transition: all 0.3s ease;
        font-weight: 500;
        text-decoration: none;
    }
    
    .nav-tab:hover {
        background: rgba(255, 255, 255, 0.1);
        color: #ffffff;
    }
    
    .nav-tab-active {
        background: rgba(108, 93, 211, 0.3);
        color: #6c5dd3;
        box-shadow: 0 4px 12px rgba(108, 93, 211, 0.3);
    }
    

    
    
    .Nexora Service Suite-modern-table-container {
        margin-bottom: 24px;
    }
    
    .table-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 20px;
        padding-bottom: 16px;
        border-bottom: 1px solid rgba(255, 255, 255, 0.1);
        background: #00017842 !important;
    }
    
    .table-info {
        color: rgba(255, 255, 255, 0.8);
        font-size: 14px;
    }
    
    .Nexora Service Suite-table-wrapper {
        overflow-x: auto;
        border-radius: 12px;
        border: 1px solid rgba(255, 255, 255, 0.1);
        background: rgba(255, 255, 255, 0.02);
    }
    
    .Nexora Service Suite-modern-table {
        width: 100%;
        border-collapse: collapse;
        background: rgba(255, 255, 255, 0.02);
        border-radius: 12px;
        overflow: hidden;
    }
    
    .Nexora Service Suite-modern-table th {
        background: rgba(255, 255, 255, 0.05);
        padding: 16px 12px;
        text-align: left;
        font-weight: 600;
        color: #fcdc24 !important;
        border-bottom: 1px solid rgba(255, 255, 255, 0.1);
    }
    
    .Nexora Service Suite-modern-table td {
        padding: 16px 12px;
        border-bottom: 1px solid rgba(255, 255, 255, 0.05);
    }
    
    .Nexora Service Suite-modern-table tr:hover {
        background: rgba(255, 255, 255, 0.5);
    }
    
    
    .Nexora Service Suite-modern-form-section {
        margin-top: 24px;
    }
    
    .Nexora Service Suite-modern-form-container {
        background: rgba(26, 31, 43, 0.3);
        border-radius: 12px;
        border: 1px solid rgba(255, 255, 255, 0.1);
        overflow: hidden;
    }
    
    .form-header {
        background: rgba(108, 93, 211, 0.2);
        padding: 20px 24px;
        border-bottom: 1px solid rgba(255, 255, 255, 0.1);
    }
    
    .form-header h3 {
        margin: 0;
        font-size: 18px;
        font-weight: 600;
        color: #ffffff;
    }
    
    .form-body {
        padding: 24px;
        background: rgba(26, 31, 43, 0.2);
    }
    
    .form-group {
        margin-bottom: 20px;
    }
    
    .form-group label {
        display: block;
        margin-bottom: 8px;
        color: rgba(255, 255, 255, 0.8);
        font-weight: 500;
    }
    
    .form-group input,
    .form-group select {
        width: 100%;
        padding: 12px 16px;
        border: 1px solid rgba(255, 255, 255, 0.2);
        border-radius: 8px;
        background: rgba(255, 255, 255, 0.1);
        color: #ffffff;
        font-size: 14px;
        transition: all 0.3s ease;
        box-sizing: border-box;
    }
    
    .form-group input:focus,
    .form-group select:focus {
        outline: none;
        border-color: #6c5dd3;
        box-shadow: 0 0 0 3px rgba(108, 93, 211, 0.2);
    }
    
    .form-group input::placeholder {
        color: rgba(255, 255, 255, 0.5);
    }
    
    
    .button {
        display: inline-flex;
        align-items: center;
        gap: 8px;
        padding: 12px 20px;
        border: none;
        border-radius: 8px;
        font-weight: 600;
        text-decoration: none;
        transition: all 0.3s ease;
        cursor: pointer;
        font-size: 14px;
        background: rgba(108, 93, 211, 0.3);
        color: #ffffff;
        border: 1px solid rgba(108, 93, 211, 0.3);
    }
    
    .button:hover {
        background: rgba(108, 93, 211, 0.5);
        transform: translateY(-2px);
        box-shadow: 0 8px 25px rgba(108, 93, 211, 0.3);
    }
    
    .button-primary {
        background: linear-gradient(135deg, #6C5DD3, #8B5CF6);
        border-color: transparent;
    }
    
    .button-primary:hover {
        background: linear-gradient(135deg, #5B4BC4, #7C3AED);
    }
    
    .button-secondary {
        background: rgba(255, 255, 255, 0.1);
        border-color: rgba(255, 255, 255, 0.2);
    }
    
    .button-secondary:hover {
        background: rgba(255, 255, 255, 0.2);
    }
    
    .button-danger {
        background: rgba(239, 68, 68, 0.3);
        border-color: rgba(239, 68, 68, 0.3);
        color: #FCA5A5;
    }
    
    .button-danger:hover {
        background: rgba(239, 68, 68, 0.5);
        color: #ffffff;
    }
    
    .button-small {
        padding: 8px 16px;
        font-size: 12px;
    }
    
    
    .Nexora Service Suite-modern-pagination {
        display: flex;
        justify-content: center;
        align-items: center;
        gap: 16px;
        padding: 20px;
        border-top: 1px solid rgba(255, 255, 255, 0.1);
        background: rgba(255, 255, 255, 0.05);
    }
    
    .pagination-info {
        color: rgba(255, 255, 255, 0.8);
        font-size: 14px;
    }
    
    
    .loading-spinner {
        width: 40px;
        height: 40px;
        border: 4px solid rgba(255, 255, 255, 0.1);
        border-left: 4px solid #6c5dd3;
        animation: spin 1s linear infinite;
        margin: 20px auto;
    }
    
    @keyframes spin {
        0% { transform: rotate(0deg); }
        100% { transform: rotate(360deg); }
    }
    
    .loading-row {
        text-align: center;
        padding: 40px;
        color: rgba(255, 255, 255, 0.6);
    }
    
    
    .Nexora Service Suite-empty {
        text-align: center;
        padding: 60px 20px;
        color: rgba(255, 255, 255, 0.6);
        font-size: 16px;
    }
    
    
    .database-info {
        background: rgba(26, 31, 43, 0.3);
        border: 1px solid rgba(255, 255, 255, 0.1);
        border-radius: 12px;
        padding: 20px;
        margin: 20px 0;
        font-family: 'Courier New', monospace;
        font-size: 13px;
        color: rgba(255, 255, 255, 0.8);
    }
    
    .database-info strong {
        color: #fcdc24;
    }
    
    
    .stats-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
        gap: 20px;
        margin-bottom: 24px;
        width: 100%;
        box-sizing: border-box;
    }
    
    .stat-card {
        background: rgba(26, 31, 43, 0.3);
        border: 1px solid rgba(255, 255, 255, 0.1);
        border-radius: 12px;
        padding: 24px;
        text-align: center;
        transition: all 0.3s ease;
        width: 100%;
        box-sizing: border-box;
        min-width: 0;
    }
    
    .stat-card:hover {
        transform: translateY(-4px);
        box-shadow: 0 8px 25px rgba(0, 0, 0, 0.2);
        border-color: rgba(108, 93, 211, 0.3);
    }
    
    .stat-icon {
        width: 60px;
        height: 60px;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        margin: 0 auto 16px;
        font-size: 24px;
        color: white;
        background: linear-gradient(135deg, #6C5DD3, #8B5CF6);
    }
    
    .stat-number {
        font-size: 32px;
        font-weight: 700;
        color: #fcdc24;
        margin-bottom: 8px;
    }
    
    .stat-label {
        color: rgba(255, 255, 255, 0.8);
        font-size: 14px;
        font-weight: 500;
    }
    
    
    .stats-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
        gap: 20px;
        margin-bottom: 24px;
        width: 100%;
        box-sizing: border-box;
    }
    
    .stat-card {
        background: rgba(26, 31, 43, 0.3);
        border: 1px solid rgba(255, 255, 255, 0.1);
        border-radius: 12px;
        padding: 24px;
        text-align: center;
        transition: all 0.3s ease;
        width: 100%;
        box-sizing: border-box;
        min-width: 0;
    }
    
    .stat-card:hover {
        transform: translateY(-4px);
        box-shadow: 0 8px 25px rgba(0, 0, 0, 0.2);
        border-color: rgba(108, 93, 211, 0.3);
    }
    
    .stat-icon {
        width: 60px;
        height: 60px;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        margin: 0 auto 16px;
        font-size: 24px;
        color: white;
        background: linear-gradient(135deg, #6C5DD3, #8B5CF6);
    }
    
    .stat-number {
        font-size: 32px;
        font-weight: 700;
        color: #fcdc24;
        margin-bottom: 8px;
    }
    
    .stat-label {
        color: rgba(255, 255, 255, 0.8);
        font-size: 14px;
        font-weight: 500;
    }
    
    
    @media (max-width: 1200px) {
        .dashboard-container {
            max-width: calc(100% - 80px);
            margin-left: 80px;
            overflow-x: hidden;
        }
        
        .stats-grid {
            grid-template-columns: repeat(auto-fit, minmax(180px, 1fr));
            gap: 16px;
        }
    }
    
    @media (max-width: 768px) {
        .dashboard-container {
            max-width: calc(100% - 80px);
            margin-left: 80px;
            overflow-x: hidden;
            padding: 16px;
        }
        
        .stats-grid {
            grid-template-columns: 1fr;
            gap: 16px;
        }
        
        .device-table th,
        .device-table td {
            padding: 12px 16px;
        }
        
        .device-list-header {
            flex-direction: column;
            gap: 16px;
            align-items: stretch;
        }
        
        .device-list-actions {
            justify-content: center;
        }
    }
    
    @media (max-width: 480px) {
        .dashboard-container {
            max-width: calc(100% - 80px);
            margin-left: 80px;
            overflow-x: hidden;
            padding: 12px;
        }
        
        .page-title {
            font-size: 24px;
        }
        
        .modal-container {
            min-width: 90vw;
            margin: 20px;
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
                    <span class="nav-badge awaiting-mod" id="nav-anfragen-badge" style="display: none;">0</span>
                </a>
            </li>
            
            <li class="nav-item">
                <a href="<?php echo admin_url('admin.php?page=Nexora Service Suite-users'); ?>" class="nav-link" title="Benutzer">
                    <i class="fas fa-users"></i>
                    <span class="nav-text">Benutzer</span>
                    <span class="nav-badge awaiting-mod" id="nav-benutzer-badge" style="display: none;">0</span>
                </a>
            </li>
            
            <li class="nav-item">
                <a href="<?php echo admin_url('admin.php?page=Nexora Service Suite-device-manager'); ?>" class="nav-link active" title="Geräteverwaltung">
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
        
        
        <div class="dashboard-topbar">
            <div class="breadcrumb">
                <i class="fas fa-home"></i> Dashboard / Geräteverwaltung
            </div>
            <div class="topbar-right">
                <div class="search-box">
                    <i class="fas fa-search"></i>
                    <input type="text" placeholder="Suchen...">
                </div>
                <div class="user-menu">
                    <i class="fas fa-user-circle"></i>
                    <span>Admin</span>
                </div>
            </div>
        </div>

        
        <div class="page-header glass-card">
            <h1 class="page-title">Geräteverwaltung</h1>
        </div>

        
        <div class="stats-grid">
            <div class="stat-card glass-card">
                <div class="stat-icon">
                    <i class="fas fa-tags"></i>
                </div>
                <div class="stat-number" id="total-types">-</div>
                <div class="stat-label">Gerätetyp</div>
            </div>
            
            <div class="stat-card glass-card">
                <div class="stat-icon">
                    <i class="fas fa-crown"></i>
                </div>
                <div class="stat-number" id="total-brands">-</div>
                <div class="stat-label">Marke</div>
            </div>
            
            <div class="stat-card glass-card">
                <div class="stat-icon">
                    <i class="fas fa-layer-group"></i>
                </div>
                <div class="stat-number" id="total-series">-</div>
                <div class="stat-label">Serie</div>
            </div>
            
            <div class="stat-card glass-card">
                <div class="stat-icon">
                    <i class="fas fa-mobile-alt"></i>
                </div>
                <div class="stat-number" id="total-models">-</div>
                <div class="stat-label">Gerätemodell</div>
            </div>
        </div>

        
        
        <nav class="nav-tab-wrapper glass-card">
            <?php 
            $tabs = [
                'type' => 'Gerätetyp',
                'brand' => 'Marke',
                'series' => 'Serie',
                'model' => 'Gerätemodell',
            ];
            $current_tab = $_GET['tab'] ?? 'type';
            
            foreach ($tabs as $key => $label): ?>
                <a href="<?php echo admin_url('admin.php?page=Nexora Service Suite-device-manager&tab=' . $key); ?>" class="nav-tab <?php if ($current_tab === $key) echo 'nav-tab-active'; ?>">
                    <?php echo esc_html($label); ?>
                </a>
            <?php endforeach; ?>
        </nav>
        
        
        <div class="Nexora Service Suite-admin-content" id="Nexora Service Suite-device-manager-app" data-tab="<?php echo esc_attr($current_tab); ?>">
            

            
            
            <div class="Nexora Service Suite-modern-table-container glass-card">
                <div class="table-header">
                    <div class="table-info">
                        <span id="device-table-info">Lade Geräte...</span>
                    </div>
                </div>
                <div class="Nexora Service Suite-table-wrapper">
                    <table class="Nexora Service Suite-modern-table">
                        <thead>
                            <tr>
                                <th>Name</th>
                                <?php if ($current_tab !== 'type'): ?>
                                    <th>Eltern</th>
                                <?php endif; ?>
                                <th>Slug</th>
                                <th>Aktionen</th>
                            </tr>
                        </thead>
                        <tbody id="Nexora Service Suite-device-list">
                            <tr class="loading-row">
                                <td colspan="<?php echo $current_tab !== 'type' ? '4' : '3'; ?>">
                                    <div class="loading-spinner"></div>
                                    <div>Lade Geräte...</div>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
                <div class="Nexora Service Suite-modern-pagination">
                    <button id="device-prev-page" class="button button-secondary" disabled>← Zurück</button>
                    <span class="pagination-info" id="device-page-info">Seite 1 von 1</span>
                    <button id="device-next-page" class="button button-secondary" disabled>Weiter →</button>
                </div>
            </div>
            
            
            <div class="Nexora Service Suite-modern-form-section">
                <div class="Nexora Service Suite-modern-form-container glass-card">
                    <div class="form-header">
                        <h3>
                            <?php
                            switch ($current_tab) {
                                case 'type': echo 'Neuen Gerätetyp hinzufügen'; break;
                                case 'brand': echo 'Neue Marke hinzufügen'; break;
                                case 'series': echo 'Neue Serie hinzufügen'; break;
                                case 'model': echo 'Neues Modell hinzufügen'; break;
                            }
                            ?>
                        </h3>
                    </div>
                    <div class="form-body">
                        <form id="Nexora Service Suite-device-form">
                            <div class="form-group">
                                <label for="device-name">Name *</label>
                                <input type="text" id="device-name" name="name" required>
                            </div>
                            
                            <?php if ($current_tab !== 'type'): ?>
                            <div class="form-group">
                                <label for="device-parent">Eltern *</label>
                                <select id="device-parent" name="parent_id" required>
                                    <option value="">Bitte wählen...</option>
                                </select>
                            </div>
                            <?php endif; ?>
                            
                            <div class="form-group">
                                <label for="device-slug">Slug</label>
                                <input type="text" id="device-slug" name="slug" placeholder="Wird automatisch generiert">
                            </div>
                            
                            <div class="form-group">
                                <button type="submit" class="button button-primary" id="save-device-btn">Speichern</button>
                                <button type="button" class="button button-secondary" id="cancel-edit-btn" style="display: none;">Abbrechen</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
            
        </div>
        
    </div>

    <script>
        jQuery(document).ready(function($) {
            const app = $('#Nexora Service Suite-device-manager-app');
            if (!app.length) return;
            
            const tab = app.data('tab');
            const list = $('#Nexora Service Suite-device-list');
            const form = $('#Nexora Service Suite-device-form');
            const parentSelect = $('#device-parent');
            const saveBtn = $('#save-device-btn');
            const cancelBtn = $('#cancel-edit-btn');
            const searchInput = $('#device-search');
            const searchBtn = $('#device-search-btn');
            
            let editingId = null;
            let currentPage = 1;
            let totalPages = 1;
            let searchTerm = '';
            function loadStatistics() {
                $.post(ajaxurl, {
                    action: 'nexora_device_crud',
                    nonce: nexora_admin.nonce,
                    action_type: 'statistics'
                }, function(res) {
                    if (res.success) {
                        $('#total-types').text(res.data.types || 0);
                        $('#total-brands').text(res.data.brands || 0);
                        $('#total-series').text(res.data.series || 0);
                        $('#total-models').text(res.data.models || 0);
                    }
                });
            }
            function loadList() {
                console.log('Loading list for tab:', tab);
                list.html('<tr class="loading-row"><td colspan="' + (tab !== 'type' ? '4' : '3') + '"><div class="loading-spinner"></div><div>Lade Daten...</div></td></tr>');
                
                const postData = {
                    action: 'nexora_device_crud',
                    nonce: nexora_admin.nonce,
                    action_type: 'list',
                    data: { 
                        type: tab,
                        page: currentPage,
                        search: searchTerm
                    }
                };
                
                console.log('Sending AJAX request:', postData);
                
                $.post(ajaxurl, postData, function(res) {
                    console.log('AJAX response:', res);
                    if (!res.success) {
                        console.error('AJAX error:', res.data);
                        list.html('<tr><td colspan="' + (tab !== 'type' ? '4' : '3') + '"><div class="Nexora Service Suite-error">' + res.data + '</div></td></tr>');
                        return;
                    }
                    renderList(res.data.items || res.data);
                    updatePagination(res.data.total_pages || 1, res.data.current_page || 1);
                }).fail(function(xhr, status, error) {
                    console.error('AJAX request failed:', status, error);
                    console.error('Response:', xhr.responseText);
                    list.html('<tr><td colspan="' + (tab !== 'type' ? '4' : '3') + '"><div class="Nexora Service Suite-error">AJAX Request fehlgeschlagen: ' + error + '</div></td></tr>');
                });
            }

            function loadParentDropdown() {
                if (tab === 'type') return parentSelect.html('');
                
                parentSelect.html('<option value="">Bitte wählen...</option>');
                $.post(ajaxurl, {
                    action: 'nexora_device_crud',
                    nonce: nexora_admin.nonce,
                    action_type: 'list',
                    data: { type: getParentType(tab) }
                }, function(res) {
                    if (!res.success) return;
                    res.data.forEach(function(item) {
                        const optionText = item.name + ' (' + (item.slug || item.name) + ')';
                        parentSelect.append('<option value="' + item.id + '">' + optionText + '</option>');
                    });
                });
            }

            function getParentType(type) {
                if (type === 'brand') return 'type';
                if (type === 'series') return 'brand';
                if (type === 'model') return 'series';
                return null;
            }
            function renderList(items) {
                if (!items.length) {
                    list.html('<tr><td colspan="' + (tab !== 'type' ? '4' : '3') + '"><div class="Nexora Service Suite-empty">Keine Einträge gefunden.</div></td></tr>');
                    return;
                }
                
                let html = '';
                items.forEach(function(item) {
                    html += '<tr>' +
                        '<td>' + escapeHtml(item.name) + '</td>' +
                        (tab !== 'type' ? '<td>' + escapeHtml(item.parent_name || '-') + '</td>' : '') +
                        '<td>' + escapeHtml(item.slug) + '</td>' +
                        '<td>' +
                        '<button class="button button-small edit-device" data-id="' + item.id + '">Bearbeiten</button> ' +
                        '<button class="button button-small button-danger delete-device" data-id="' + item.id + '">Löschen</button>' +
                        '</td></tr>';
                });
                list.html(html);
            }
            function updatePagination(total, current) {
                totalPages = total;
                currentPage = current;
                
                $('#device-page-info').text('Seite ' + current + ' von ' + total);
                $('#device-prev-page').prop('disabled', current <= 1);
                $('#device-next-page').prop('disabled', current >= total);
            }
            form.on('submit', function(e) {
                e.preventDefault();
                saveBtn.prop('disabled', true);
                
                const data = form.serializeArray().reduce((obj, f) => (obj[f.name] = f.value, obj), {});
                const action_type = editingId ? 'update' : 'create';
                if (editingId) data.id = editingId;
                
                $.post(ajaxurl, {
                    action: 'nexora_device_crud',
                    nonce: nexora_admin.nonce,
                    action_type: action_type,
                    data: data,
                    id: editingId
                }, function(res) {
                    saveBtn.prop('disabled', false);
                    if (!res.success) {
                        alert(res.data);
                        return;
                    }
                    
                    form[0].reset();
                    editingId = null;
                    cancelBtn.hide();
                    loadList();
                    loadParentDropdown();
                    loadStatistics();
                });
            });
            list.on('click', '.edit-device', function() {
                const id = $(this).data('id');
                $.post(ajaxurl, {
                    action: 'nexora_device_crud',
                    nonce: nexora_admin.nonce,
                    action_type: 'get',
                    id: id
                }, function(res) {
                    if (!res.success) {
                        alert(res.data);
                        return;
                    }
                    
                    const d = res.data;
                    editingId = d.id;
                    form.find('#device-name').val(d.name);
                    form.find('#device-slug').val(d.slug);
                    if (tab !== 'type') form.find('#device-parent').val(d.parent_id);
                    cancelBtn.show();
                });
            });
            cancelBtn.on('click', function() {
                editingId = null;
                form[0].reset();
                cancelBtn.hide();
            });
            list.on('click', '.delete-device', function() {
                const id = $(this).data('id');
                if (!confirm('Soll dieser Eintrag gelöscht werden? Wenn untergeordnete Einträge existieren, werden sie ebenfalls gelöscht.')) return;
                
                $.post(ajaxurl, {
                    action: 'nexora_device_crud',
                    nonce: nexora_admin.nonce,
                    action_type: 'delete',
                    id: id,
                    cascade: 1
                }, function(res) {
                    if (!res.success) {
                        alert(res.data);
                        return;
                    }
                    loadList();
                    loadParentDropdown();
                    loadStatistics();
                });
            });
            searchBtn.on('click', function() {
                searchTerm = searchInput.val().trim();
                currentPage = 1;
                loadList();
            });

            searchInput.on('keypress', function(e) {
                if (e.which === 13) {
                    searchBtn.click();
                }
            });
            $('#device-prev-page').on('click', function() {
                if (currentPage > 1) {
                    currentPage--;
                    loadList();
                }
            });

            $('#device-next-page').on('click', function() {
                if (currentPage < totalPages) {
                    currentPage++;
                    loadList();
                }
            });
            function escapeHtml(str) {
                return String(str).replace(/[&<>"]/g, function(s) {
                    return ({'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;'}[s]);
                });
            }
            loadList();
            loadParentDropdown();
            loadStatistics();
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

        });
    </script>
</body>
</html> 