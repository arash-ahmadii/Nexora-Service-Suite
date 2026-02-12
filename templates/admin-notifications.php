<!DOCTYPE html>
<html lang="de">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Administrative Nachrichten - Nexora Service Suite Service</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body.Nexora Service Suite-notifications {
            font-family: 'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
            background: linear-gradient(135deg, #0f0f23 0%, #1a1a2e 25%, #16213e 50%, #0f3460 75%, #533483 100%);
            color: #ffffff;
            min-height: 100vh;
            overflow-x: hidden;
            position: relative;
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
        
        @keyframes slideInLeft {
            to {
                opacity: 1;
                transform: translateX(0);
            }
        }
        
        
        .notifications-container {
            width: calc(90% - 80px);
            max-width: calc(90% - 80px);
            min-width: 0;
            margin: 0 auto 0 80px;
            padding: 24px;
            overflow-x: hidden;
            box-sizing: border-box;
        }
        
        
        .notifications-header {
            margin-bottom: 32px;
            animation: fadeInUp 0.8s ease-out;
        }
        
        .notifications-title {
            font-size: 32px;
            font-weight: 700;
            color: #ffffff;
            margin-bottom: 8px;
            background: linear-gradient(135deg, #6c5dd3, #8b5cf6);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
        }
        
        .notifications-subtitle {
            color: #a0aec0;
            font-size: 16px;
            opacity: 0.8;
        }
        
        
        .notifications-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(350px, 1fr));
            gap: 24px;
            margin-bottom: 32px;
        }
        
        
        .notification-card {
            background: rgba(26, 31, 43, 0.22);
            backdrop-filter: blur(22px);
            border: 1px solid rgba(255, 255, 255, 0.12);
            border-radius: 16px;
            padding: 24px;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.35);
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
            animation: fadeInUp 0.8s ease-out;
            animation-fill-mode: both;
            display: flex;
            gap: 15px;
            align-items: flex-start;
        }
        
        .notification-checkbox {
            margin-top: 5px;
        }
        
        .notification-checkbox input[type="checkbox"] {
            width: 18px;
            height: 18px;
            cursor: pointer;
        }
        
        .notification-content {
            flex: 1;
        }
        
        .notification-card:hover {
            transform: translateY(-4px) scale(1.02);
            box-shadow: 0 20px 50px rgba(0, 0, 0, 0.55);
            border-color: rgba(108, 93, 211, 0.3);
        }
        
        .notification-header {
            display: flex;
            align-items: center;
            justify-content: space-between;
            margin-bottom: 16px;
        }
        
        .notification-type {
            display: flex;
            align-items: center;
            gap: 8px;
            padding: 8px 16px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }
        
        .notification-type.new-request {
            background: linear-gradient(135deg, #10b981, #059669);
            color: #ffffff;
        }
        
        .notification-type.new-invoice {
            background: linear-gradient(135deg, #f59e0b, #d97706);
            color: #ffffff;
        }
        
        .notification-type.new-registration {
            background: linear-gradient(135deg, #3b82f6, #2563eb);
            color: #ffffff;
        }
        
        .notification-type.status-change {
            background: linear-gradient(135deg, #8b5cf6, #7c3aed);
            color: #ffffff;
        }
        
        .notification-type.system {
            background: linear-gradient(135deg, #6b7280, #4b5563);
            color: #ffffff;
        }
        
        .notification-time {
            color: #9ca3af;
            font-size: 12px;
            font-weight: 500;
        }
        
        .notification-message {
            color: #ffffff;
            font-size: 14px;
            line-height: 1.6;
            margin-bottom: 20px;
        }
        
        .notification-actions {
            display: flex;
            gap: 12px;
            align-items: center;
        }
        
        .mark-read-btn {
            background: linear-gradient(135deg, #6c5dd3, #8b5cf6);
            color: #ffffff;
            border: none;
            padding: 10px 20px;
            border-radius: 12px;
            font-size: 14px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
            box-shadow: 0 4px 16px rgba(108, 93, 211, 0.3);
        }
        
        .mark-read-btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 24px rgba(108, 93, 211, 0.4);
        }
        
        .mark-read-btn:active {
            transform: translateY(0);
        }
        
        
        .bulk-actions {
            background: rgba(26, 31, 43, 0.3);
            padding: 15px;
            border-radius: 12px;
            border: 1px solid rgba(255, 255, 255, 0.1);
        }
        
        .bulk-actions button {
            padding: 8px 16px;
            border: none;
            border-radius: 8px;
            cursor: pointer;
            font-size: 14px;
            font-weight: 500;
            transition: all 0.3s ease;
        }
        
        .bulk-actions button:disabled {
            opacity: 0.5;
            cursor: not-allowed;
        }
        
        .bulk-actions .btn-secondary {
            background: rgba(255, 255, 255, 0.1);
            color: #CBD5E0;
        }
        
        .bulk-actions .btn-primary {
            background: #6c5dd3;
            color: white;
        }
        
        .bulk-actions .btn-danger {
            background: #e53e3e;
            color: white;
        }
        
        .bulk-actions .btn-secondary:hover:not(:disabled) {
            background: rgba(255, 255, 255, 0.2);
        }
        
        .bulk-actions .btn-primary:hover:not(:disabled) {
            background: #5a4bc2;
        }
        
        .bulk-actions .btn-danger:hover:not(:disabled) {
            background: #c53030;
        }
        
        
        .Nexora Service Suite-loading {
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 60px 20px;
            color: #a0aec0;
            font-size: 16px;
            background: rgba(26, 31, 43, 0.22);
            backdrop-filter: blur(22px);
            border: 1px solid rgba(255, 255, 255, 0.12);
            border-radius: 16px;
            animation: fadeInUp 0.8s ease-out;
        }
        
        .Nexora Service Suite-loading::before {
            content: '';
            width: 20px;
            height: 20px;
            border: 2px solid #6c5dd3;
            border-top: 2px solid transparent;
            border-radius: 50%;
            animation: spin 1s linear infinite;
            margin-right: 12px;
        }
        
        @keyframes spin {
            0% { transform: rotate(0deg); }
            100% { transform: rotate(360deg); }
        }
        
        
        .Nexora Service Suite-message.warning {
            background: rgba(245, 158, 11, 0.1);
            border: 1px solid rgba(245, 158, 11, 0.3);
            color: #fbbf24;
            padding: 24px;
            border-radius: 16px;
            text-align: center;
            font-size: 16px;
            animation: fadeInUp 0.8s ease-out;
        }
        
        
        @keyframes fadeInUp {
            from {
                opacity: 0;
                transform: translateY(30px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }
        
        
        @media (max-width: 1199px) {
            .notifications-container {
                width: calc(90% - 80px);
                max-width: calc(90% - 80px);
                padding: 20px;
            }
            
            .notifications-grid {
                grid-template-columns: repeat(auto-fit, minmax(350px, 1fr));
                gap: 20px;
            }
        }
        
        @media (max-width: 767px) {
            .notifications-container {
                width: calc(90% - 80px);
                max-width: calc(90% - 80px);
                padding: 16px;
            }
            
            .notifications-grid {
                grid-template-columns: 1fr;
                gap: 16px;
            }
            
            .notifications-title {
                font-size: 24px;
            }
        }
        
        @media (max-width: 479px) {
            .notifications-container {
                width: calc(90% - 80px);
                max-width: calc(90% - 80px);
                padding: 16px;
            }
            
            .notification-card {
                padding: 16px;
            }
        }
        
        
        #wpwrap {
            background: black;
        }
    </style>
</head>
<body class="Nexora Service Suite-notifications">

    
    
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
                <a href="<?php echo admin_url('admin.php?page=Nexora Service Suite-device-manager'); ?>" class="nav-link" title="Geräteverwaltung">
                    <i class="fas fa-mobile-alt"></i>
                    <span class="nav-text">Geräteverwaltung</span>
                </a>
            </li>
            
            <li class="nav-item">
                <a href="<?php echo admin_url('admin.php?page=Nexora Service Suite-admin-notifications'); ?>" class="nav-link active" title="Nachrichten" id="nav-nachrichten">
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

    
    <div class="notifications-container">
        
        <div class="notifications-header">
            <h1 class="notifications-title">Administrative Nachrichten</h1>
            <p class="notifications-subtitle">Verwalten Sie alle Systembenachrichtigungen und Updates</p>
            
            
            <div class="bulk-actions" style="margin-top: 20px; display: flex; gap: 10px; align-items: center;">
                <button id="select-all-btn" class="btn btn-secondary">
                    <i class="fas fa-check-square"></i> Alle auswählen
                </button>
                <button id="mark-all-read-btn" class="btn btn-primary">
                    <i class="fas fa-check"></i> Alle als gelesen markieren
                </button>
                <button id="delete-all-btn" class="btn btn-danger">
                    <i class="fas fa-trash"></i> Alle löschen
                </button>
                <span id="selected-count" style="margin-left: 10px; color: #666;"></span>
            </div>
        </div>

        
        <div id="Nexora Service Suite-notifications-list">
            <div class="Nexora Service Suite-loading">Nachrichten werden geladen...</div>
        </div>
    </div>

    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script>
        jQuery(document).ready(function($){
            function loadNotificationCounts() {
                $.post(ajaxurl, {action: 'get_new_requests_count', nonce: '<?php echo wp_create_nonce("nexora_notifications_nonce"); ?>'}, function(resp){
                    if(resp.success && resp.data.count > 0) {
                        $('#nav-anfragen-badge').text(resp.data.count).show();
                    } else {
                        $('#nav-anfragen-badge').hide();
                    }
                });
                $.post(ajaxurl, {action: 'nexora_get_new_users_count', nonce: '<?php echo wp_create_nonce("nexora_nonce"); ?>'}, function(resp){
                    if(resp.success && resp.data.count > 0) {
                        $('#nav-benutzer-badge').text(resp.data.count).show();
                    } else {
                        $('#nav-benutzer-badge').hide();
                    }
                });
                $.post(ajaxurl, {action: 'nexora_get_admin_notifications', nonce: '<?php echo wp_create_nonce("nexora_nonce"); ?>'}, function(resp){
                    if(resp.success && resp.data.length > 0) {
                        $('#nav-nachrichten-badge').text(resp.data.length).show();
                    } else {
                        $('#nav-nachrichten-badge').hide();
                    }
                });
            }
            loadNotificationCounts();
            setInterval(loadNotificationCounts, 60000);
            
            function loadNotifications() {
                $('#Nexora Service Suite-notifications-list').html('<div class="Nexora Service Suite-loading">Nachrichten werden geladen...</div>');
                
                $.post(ajaxurl, {action: 'nexora_get_admin_notifications'}, function(resp){
                    if(resp.success && resp.data.length > 0) {
                        let html = '<div class="notifications-grid">';
                        
                        resp.data.forEach(function(n, index){
                            let typeClass = 'system';
                            let typeIcon = 'fas fa-info-circle';
                            
                            if (n.type.includes('Anfrage')) {
                                typeClass = 'new-request';
                                typeIcon = 'fas fa-clipboard-list';
                            } else if (n.type.includes('Rechnung')) {
                                typeClass = 'new-invoice';
                                typeIcon = 'fas fa-file-invoice';
                            } else if (n.type.includes('Registrierung')) {
                                typeClass = 'new-registration';
                                typeIcon = 'fas fa-user-plus';
                            } else if (n.type.includes('Status')) {
                                typeClass = 'status-change';
                                typeIcon = 'fas fa-exchange-alt';
                            }
                            let date = new Date(n.created_at);
                            let formattedDate = date.toLocaleDateString('de-DE', {
                                day: '2-digit',
                                month: '2-digit',
                                year: 'numeric',
                                hour: '2-digit',
                                minute: '2-digit'
                            });
                            
                            html += `
                                <div class="notification-card" style="animation-delay: ${index * 0.1}s">
                                    <div class="notification-checkbox">
                                        <input type="checkbox" class="notification-select" data-id="${n.id}">
                                    </div>
                                    <div class="notification-content">
                                        <div class="notification-header">
                                            <div class="notification-type ${typeClass}">
                                                <i class="${typeIcon}"></i>
                                                ${n.type}
                                            </div>
                                            <div class="notification-time">${formattedDate}</div>
                                        </div>
                                        <div class="notification-message">${n.message}</div>
                                        <div class="notification-actions">
                                            <button class="mark-read-btn" data-id="${n.id}">
                                                <i class="fas fa-check"></i> Gelesen
                                            </button>
                                            <button class="delete-notification-btn" data-id="${n.id}">
                                                <i class="fas fa-trash"></i> Löschen
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            `;
                        });
                        
                        html += '</div>';
                        $('#Nexora Service Suite-notifications-list').html(html);
                    } else {
                        $('#Nexora Service Suite-notifications-list').html(`
                            <div class="Nexora Service Suite-message warning">
                                <i class="fas fa-info-circle"></i>
                                Keine neuen Nachrichten vorhanden.
                            </div>
                        `);
                    }
                }).fail(function(){
                    $('#Nexora Service Suite-notifications-list').html(`
                        <div class="Nexora Service Suite-message warning">
                            <i class="fas fa-exclamation-triangle"></i>
                            Fehler beim Laden der Nachrichten.
                        </div>
                    `);
                });
            }
            loadNotifications();
            let selectedNotifications = [];
            $('#select-all-btn').on('click', function() {
                const allChecked = $('.notification-select:checked').length === $('.notification-select').length;
                $('.notification-select').prop('checked', !allChecked);
                updateSelectedCount();
            });
            function updateSelectedCount() {
                selectedNotifications = $('.notification-select:checked').map(function() {
                    return $(this).data('id');
                }).get();
                
                $('#selected-count').text(`${selectedNotifications.length} ausgewählt`);
                if (selectedNotifications.length > 0) {
                    $('#mark-all-read-btn, #delete-all-btn').prop('disabled', false);
                } else {
                    $('#mark-all-read-btn, #delete-all-btn').prop('disabled', true);
                }
            }
            $(document).on('change', '.notification-select', updateSelectedCount);
            $('#mark-all-read-btn').on('click', function() {
                if (selectedNotifications.length === 0) return;
                
                const promises = selectedNotifications.map(id => {
                    return $.post(ajaxurl, {
                        action: 'nexora_mark_notification_read',
                        id: id
                    });
                });
                
                Promise.all(promises).then(() => {
                    showNotification('Alle ausgewählten Nachrichten wurden als gelesen markiert', 'success');
                    loadNotifications();
                    updateSelectedCount();
                });
            });
            $('#delete-all-btn').on('click', function() {
                if (selectedNotifications.length === 0) return;
                
                if (!confirm(`Möchten Sie wirklich ${selectedNotifications.length} Nachrichten löschen?`)) {
                    return;
                }
                
                const promises = selectedNotifications.map(id => {
                    return $.post(ajaxurl, {
                        action: 'nexora_delete_notification',
                        id: id
                    });
                });
                
                Promise.all(promises).then(() => {
                    showNotification('Alle ausgewählten Nachrichten wurden gelöscht', 'success');
                    loadNotifications();
                    updateSelectedCount();
                });
            });
            $(document).on('click', '.mark-read-btn', function(){
                var $btn = $(this);
                var id = $btn.data('id');
                $btn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin"></i> Wird markiert...');
                
                $.post(ajaxurl, {action:'nexora_mark_notification_read', id:id}, function(){
                    $btn.closest('.notification-card').fadeOut(300, function(){
                        $(this).remove();
                        updateSelectedCount();
                        if ($('.notification-card').length === 0) {
                            $('#Nexora Service Suite-notifications-list').html(`
                                <div class="Nexora Service Suite-message warning">
                                    <i class="fas fa-info-circle"></i>
                                    Keine neuen Nachrichten vorhanden.
                                </div>
                            `);
                        }
                    });
                }).fail(function(){
                    $btn.prop('disabled', false).html('<i class="fas fa-check"></i> Gelesen');
                    alert('Fehler beim Markieren der Nachricht als gelesen.');
                });
            });
            $(document).on('click', '.delete-notification-btn', function(){
                var $btn = $(this);
                var id = $btn.data('id');
                
                if (!confirm('Möchten Sie diese Nachricht wirklich löschen?')) {
                    return;
                }
                $btn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin"></i> Wird gelöscht...');
                
                $.post(ajaxurl, {action:'nexora_delete_notification', id:id}, function(){
                    $btn.closest('.notification-card').fadeOut(300, function(){
                        $(this).remove();
                        updateSelectedCount();
                        if ($('.notification-card').length === 0) {
                            $('#Nexora Service Suite-notifications-list').html(`
                                <div class="Nexora Service Suite-message warning">
                                    <i class="fas fa-info-circle"></i>
                                    Keine neuen Nachrichten vorhanden.
                                </div>
                            `);
                        }
                    });
                }).fail(function(){
                    $btn.prop('disabled', false).html('<i class="fas fa-trash"></i> Löschen');
                    alert('Fehler beim Löschen der Nachricht.');
                });
            });
        });
    </script>
</body>
</html> 