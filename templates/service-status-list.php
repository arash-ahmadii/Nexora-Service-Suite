<!DOCTYPE html>
<html lang="de">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Manage service statuses - Nexora Service Suite Dashboard</title>
    
    
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
    

    
    
    .status-management-container {
        background: rgba(255, 255, 255, 0.05);
        border-radius: 16px;
        padding: 24px;
        backdrop-filter: blur(20px);
        border: 1px solid rgba(255, 255, 255, 0.1);
        margin: 0 0 24px 80px;
        width: calc(90% - 80px);
        max-width: calc(90% - 80px);
        box-sizing: border-box;
    }
    
    .status-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 24px;
    }
    
    .status-header h2 {
        margin: 0;
        color: #FFFFFF;
        font-size: 24px;
        font-weight: 600;
    }
    
    .add-status-btn {
        background: linear-gradient(135deg, #6C5DD3, #8B5CF6);
        color: white;
        border: none;
        padding: 12px 24px;
        border-radius: 8px;
        font-weight: 600;
        cursor: pointer;
        transition: all 0.3s ease;
        display: flex;
        align-items: center;
        gap: 8px;
    }
    
    .add-status-btn:hover {
        transform: translateY(-2px);
        box-shadow: 0 6px 20px rgba(108, 93, 211, 0.4);
    }
    
    .status-form {
        background: rgba(255, 255, 255, 0.05);
        border-radius: 12px;
        padding: 20px;
        margin-bottom: 24px;
        border: 1px solid rgba(255, 255, 255, 0.1);
        display: none;
    }
    
    .status-form.show {
        display: block;
    }
    
    .form-row {
        display: grid;
        grid-template-columns: 1fr 1fr 1fr auto;
        gap: 16px;
        align-items: end;
        margin-bottom: 16px;
    }
    
    .form-group {
        display: flex;
        flex-direction: column;
        gap: 8px;
    }
    
    .form-group label {
        color: #CBD5E0;
        font-size: 14px;
        font-weight: 500;
    }
    
    .form-group input,
    .form-group select {
        background: rgba(255, 255, 255, 0.1);
        border: 1px solid rgba(255, 255, 255, 0.2);
        border-radius: 8px;
        color: #FFFFFF;
        padding: 10px 12px;
        font-size: 14px;
        outline: none;
        transition: all 0.3s ease;
    }
    
    .form-group input:focus,
    .form-group select:focus {
        border-color: #6c63ff;
        box-shadow: 0 0 0 3px rgba(108, 99, 255, 0.1);
    }
    
    .color-picker {
        width: 50px;
        height: 40px;
        border: none;
        border-radius: 8px;
        cursor: pointer;
        background: transparent;
    }
    
    .form-actions {
        display: flex;
        gap: 12px;
        justify-content: flex-end;
    }
    
    .btn {
        padding: 10px 20px;
        border: none;
        border-radius: 8px;
        font-weight: 600;
        cursor: pointer;
        transition: all 0.3s ease;
        font-size: 14px;
    }
    
    .btn-primary {
        background: linear-gradient(135deg, #6C5DD3, #8B5CF6);
        color: white;
    }
    
    .btn-primary:hover {
        transform: translateY(-2px);
        box-shadow: 0 6px 20px rgba(108, 93, 211, 0.4);
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
    
    .btn-danger {
        background: #FF6B6B;
        color: white;
    }
    
    .btn-danger:hover {
        background: #FF5252;
        transform: translateY(-2px);
    }
    
    
    .status-list {
        background: rgba(255, 255, 255, 0.05);
        border-radius: 12px;
        overflow: hidden;
    }
    
    .status-table {
        width: 100%;
        border-collapse: collapse;
    }
    
    .status-table th {
        background: rgba(108, 99, 255, 0.1);
        color: #fcdc24 !important;
        padding: 16px 12px;
        text-align: left;
        font-weight: 600;
        border-bottom: 1px solid rgba(255, 255, 255, 0.1);
    }
    
    .status-table td {
        padding: 16px 12px;
        border-bottom: 1px solid rgba(255, 255, 255, 0.05);
        color: #FFFFFF;
    }
    
    .status-table tr:hover {
        background: rgba(255, 255, 255, 0.05);
    }
    
    .status-color {
        width: 20px;
        height: 20px;
        border-radius: 50%;
        display: inline-block;
        margin-right: 8px;
    }
    
    .status-actions {
        display: flex;
        gap: 8px;
    }
    
    
    .actions-cell {
        width: 137.2px;
        text-align: center;
    }
    
    .actions-container {
        display: flex;
        gap: 7.84px;
        justify-content: center;
        align-items: center;
    }
    
    .action-btn {
        display: flex;
        align-items: center;
        justify-content: center;
        width: 31.36px;
        height: 31.36px;
        min-width: 9.8px;
        padding: 2px 2px;
        border: none;
        border-radius: 5.88px;
        cursor: pointer;
        transition: all 0.3s ease;
        font-size: 13.72px;
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
    
    .action-btn i {
        font-size: 13.72px;
        line-height: 1;
    }
    
    
    @media (max-width: 768px) {
        .dashboard-container {
            padding: 16px;
        }
        
        .form-row {
            grid-template-columns: 1fr;
            gap: 12px;
        }
        
        .status-header {
            flex-direction: column;
            gap: 16px;
            align-items: stretch;
        }
        
        .status-table {
            font-size: 14px;
        }
        
        .status-table th,
        .status-table td {
            padding: 12px 8px;
        }
        
        .status-actions {
            flex-direction: column;
            gap: 4px;
        }
        
        .actions-container {
            gap: 3.92px;
        }
        
        .action-btn {
            width: 27.44px;
            height: 27.44px;
            font-size: 11.76px;
        }
        
        .actions-cell {
            width: 117.6px;
        }
            }
        
        
        .dashboard-container {
            width: calc(90% - 80px);
            max-width: calc(90% - 80px);
            min-width: 0;
            margin: 0;
            overflow-x: hidden;
            box-sizing: border-box;
        }
        
        
        @media (max-width: 1199px) {
            .dashboard-container {
                width: calc(90% - 80px);
                max-width: calc(90% - 80px);
                padding: 20px;
            }
            
            .form-row {
                grid-template-columns: 1fr 1fr;
                gap: 12px;
            }
        }
        
        @media (max-width: 767px) {
            .dashboard-container {
                width: calc(90% - 80px);
                max-width: calc(90% - 80px);
                padding: 16px;
            }
            
            .form-row {
                grid-template-columns: 1fr;
                gap: 16px;
            }
            
            .status-header {
                flex-direction: column;
                gap: 16px;
                align-items: flex-start;
            }
        }
        
        @media (max-width: 479px) {
            .dashboard-container {
                width: calc(90% - 80px);
                max-width: calc(90% - 80px);
                padding: 16px;
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
                <a href="<?php echo admin_url('admin.php?page=Nexora Service Suite-services'); ?>" class="nav-link" title="Services">
                    <i class="fas fa-cogs"></i>
                    <span class="nav-text">Services</span>
                </a>
            </li>
            
            <li class="nav-item">
                <a href="<?php echo admin_url('admin.php?page=Nexora Service Suite-service-status'); ?>" class="nav-link active" title="Status">
                    <i class="fas fa-tasks"></i>
                    <span class="nav-text">Status</span>
                </a>
            </li>
            
            <li class="nav-item">
                <a href="<?php echo admin_url('admin.php?page=Nexora Service Suite-service-request'); ?>" class="nav-link" title="Requests" id="nav-anfragen">
                    <i class="fas fa-clipboard-list"></i>
                    <span class="nav-text">Requests</span>
                    <span class="nav-badge awaiting-mod" id="nav-anfragen-badge" style="display: none;">0</span>
                </a>
            </li>
            
            <li class="nav-item">
                <a href="<?php echo admin_url('admin.php?page=Nexora Service Suite-users'); ?>" class="nav-link" title="Customers" id="nav-benutzer">
                    <i class="fas fa-users"></i>
                    <span class="nav-text">Customers</span>
                    <span class="nav-badge awaiting-mod" id="nav-benutzer-badge" style="display: none;">0</span>
                </a>
            </li>
            
            <li class="nav-item">
                <a href="<?php echo admin_url('admin.php?page=Nexora Service Suite-device-manager'); ?>" class="nav-link" title="Assets">
                    <i class="fas fa-mobile-alt"></i>
                    <span class="nav-text">Assets</span>
                </a>
            </li>
            
            <li class="nav-item">
                <a href="<?php echo admin_url('admin.php?page=Nexora Service Suite-admin-notifications'); ?>" class="nav-link" title="Notifications" id="nav-nachrichten">
                    <i class="fas fa-bell"></i>
                    <span class="nav-text">Notifications</span>
                    <span class="nav-badge awaiting-mod" id="nav-nachrichten-badge" style="display: none;">0</span>
                </a>
            </li>
            
            <li class="nav-item">
                <a href="<?php echo admin_url('admin.php?page=Nexora Service Suite-email-management'); ?>" class="nav-link" title="Email Management">
                    <i class="fas fa-envelope"></i>
                    <span class="nav-text">Email Management</span>
                </a>
            </li>
        </ul>
    </nav>
    
    <div class="dashboard-container" style="margin-left: 80px;">
        
        <div class="dashboard-topbar">
            <div class="breadcrumb">
                <i class="fas fa-home"></i> Dashboard / Service statuses
            </div>
            <div class="topbar-right">
                <div class="search-box">
                    <i class="fas fa-search"></i>
                    <input type="text" id="status-search-input" placeholder="Suchen...">
                </div>
                <div class="user-menu">
                    <i class="fas fa-user-circle"></i>
                    <span>Admin</span>
                </div>
            </div>
        </div>
        
        
        <div class="status-management-container">
            <div class="status-header">
                <h2><i class="fas fa-chart-line"></i> Manage service statuses</h2>
                <button class="add-status-btn" id="add-status-btn">
                    <i class="fas fa-plus"></i>
                    Add new status
                </button>
            </div>
            
            
            <div class="status-form" id="status-form">
                <form id="status-form-element">
                    <div class="form-row">
                        <div class="form-group">
                            <label for="status-title">Title</label>
                            <input type="text" id="status-title" name="title" required placeholder="e.g. In progress">
                        </div>
                        <div class="form-group">
                            <label for="status-color">Color</label>
                            <input type="color" id="status-color" name="color" value="#6c63ff" class="color-picker">
                        </div>
                        <div class="form-group">
                            <label class="checkbox-label">
                                <input type="checkbox" id="status-is-default" name="is_default">
                                <span class="checkbox-text">Set as default status</span>
                            </label>
                            <small class="form-help">This status will be selected automatically for new service requests</small>
                        </div>
                        <div class="form-actions">
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-save"></i>
                                Save
                            </button>
                            <button type="button" class="btn btn-secondary" id="cancel-status-btn">
                                <i class="fas fa-times"></i>
                                Cancel
                            </button>
                        </div>
                    </div>
                </form>
            </div>
            
            
            <div class="status-list">
                <table class="status-table">
                    <thead>
                        <tr>
                            <th>Farbe</th>
                            <th>Titel</th>
                            <th>Standard</th>
                            <th>Erstellt</th>
                            <th>Aktionen</th>
                        </tr>
                    </thead>
                    <tbody id="status-list-body">
                        
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <script>
    jQuery(document).ready(function($) {
        const ajaxUrl = '<?php echo admin_url('admin-ajax.php'); ?>';
        const nonce = '<?php echo wp_create_nonce('nexora_nonce'); ?>';
        
        let editingStatusId = null;
        $('#add-status-btn').on('click', function() {
            $('#status-form').addClass('show');
            editingStatusId = null;
            $('#status-form-element')[0].reset();
            $('#status-color').val('#6c63ff');
        });
        
        $('#cancel-status-btn').on('click', function() {
            $('#status-form').removeClass('show');
            editingStatusId = null;
        });
        $('#status-form-element').on('submit', function(e) {
            e.preventDefault();
            
            const formData = {
                action: editingStatusId ? 'nexora_update_service_status' : 'nexora_add_service_status',
                nonce: nonce,
                title: $('#status-title').val(),
                color: $('#status-color').val(),
                is_default: $('#status-is-default').is(':checked') ? 1 : 0
            };
            
            if (editingStatusId) {
                formData.id = editingStatusId;
            }
            
            $.ajax({
                url: ajaxUrl,
                type: 'POST',
                data: formData,
                success: function(response) {
                    if (response.success) {
                        showNotification('Status erfolgreich ' + (editingStatusId ? 'aktualisiert' : 'hinzugefügt'), 'success');
                        $('#status-form').removeClass('show');
                        editingStatusId = null;
                        loadStatuses();
                    } else {
                        showNotification('Fehler: ' + response.data, 'error');
                    }
                },
                error: function() {
                    showNotification('Fehler bei der Verbindung zum Server', 'error');
                }
            });
        });
        function loadStatuses() {
            $.ajax({
                url: ajaxUrl,
                type: 'POST',
                data: {
                    action: 'nexora_get_form_options',
                    nonce: nonce
                },
                success: function(response) {
                    if (response.success && response.data.statuses) {
                        renderStatusList(response.data.statuses);
                    }
                },
                error: function() {
                    showNotification('Fehler beim Laden der Status', 'error');
                }
            });
        }
        function renderStatusList(statuses) {
            let html = '';
            if (statuses.length > 0) {
                statuses.forEach(status => {
                    html += `
                        <tr>
                            <td>
                                <span class="status-color" style="background-color: ${status.color}"></span>
                            </td>
                            <td>${status.title}</td>
                            <td>${status.is_default == 1 ? '✅ Ja' : '❌ Nein'}</td>
                            <td>${status.created_at || '-'}</td>
                            <td class="actions-cell">
                                <div class="actions-container">
                                    <button type="button" class="action-btn edit-btn" onclick="editStatus(${status.id}, '${status.title}', '${status.color}', ${status.is_default || 0})" title="Bearbeiten">
                                        <i class="fas fa-edit"></i>
                                    </button>
                                    <button type="button" class="action-btn delete-btn" onclick="deleteStatus(${status.id})" title="Löschen">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                </div>
                            </td>
                        </tr>`;
                });
            } else {
                html = `
                    <tr>
                        <td colspan="6" style="text-align: center; padding: 3rem 1rem; color: #CBD5E0;">
                            <div style="font-size: 3rem; margin-bottom: 1rem;">📋</div>
                            <h3 style="margin: 0 0 0.5rem 0;">Keine Status gefunden</h3>
                            <p style="margin: 0;">Fügen Sie den ersten Status hinzu.</p>
                        </td>
                    </tr>`;
            }
            $('#status-list-body').html(html);
        }
        window.editStatus = function(id, title, color, is_default) {
            editingStatusId = id;
            $('#status-title').val(title);
            $('#status-color').val(color);
            $('#status-is-default').prop('checked', is_default == 1);
            $('#status-form').addClass('show');
        };
        window.deleteStatus = function(id) {
            if (confirm('Sind Sie sicher, dass Sie diesen Status löschen möchten?')) {
                $.ajax({
                    url: ajaxUrl,
                    type: 'POST',
                    data: {
                        action: 'nexora_delete_service_status',
                        id: id,
                        nonce: nonce
                    },
                    success: function(response) {
                        if (response.success) {
                            showNotification('Status erfolgreich gelöscht', 'success');
                            loadStatuses();
                        } else {
                            showNotification('Fehler: ' + response.data, 'error');
                        }
                    },
                    error: function() {
                        showNotification('Fehler bei der Verbindung zum Server', 'error');
                    }
                });
            }
        };
        function showNotification(message, type) {
            const notification = $(`
                <div class="notification ${type}" style="
                    position: fixed;
                    top: 20px;
                    right: 20px;
                    padding: 16px 24px;
                    border-radius: 8px;
                    color: white;
                    font-weight: 500;
                    z-index: 1000;
                    background: ${type === 'success' ? '#4ECDC4' : '#FF6B6B'};
                    box-shadow: 0 4px 12px rgba(0,0,0,0.15);
                ">
                    ${message}
                </div>
            `);
            
            $('body').append(notification);
            
            setTimeout(() => {
                notification.fadeOut(300, function() {
                    $(this).remove();
                });
            }, 3000);
        }
        $('#status-search-input').on('input', function() {
            const searchTerm = $(this).val().toLowerCase();
            filterStatuses(searchTerm);
        });
        function filterStatuses(searchTerm) {
            if (searchTerm === '') {
                loadStatuses();
                return;
            }
            $('#status-list-body tr').each(function() {
                const title = $(this).find('td:nth-child(2)').text().toLowerCase();
                
                if (title.includes(searchTerm)) {
                    $(this).show();
                } else {
                    $(this).hide();
                }
            });
        }
        loadStatuses();
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
