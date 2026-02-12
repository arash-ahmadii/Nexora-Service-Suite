<!DOCTYPE html>
<html lang="de">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dienstleistungen - Nexora Service Suite Dashboard</title>
    
    
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    
    
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    
    
    <script>
        var ajaxurl = '<?php echo admin_url('admin-ajax.php'); ?>';
        var nexora_admin = {
            nonce: '<?php echo wp_create_nonce('nexora_service_nonce'); ?>'
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
    
    .search-box i {
        color: #CBD5E0;
        font-size: 16px;
    }
    
    
    .search-filters-container {
        display: flex;
        align-items: center;
        gap: 16px;
    }
    
    .filters-dropdown {
        position: relative;
    }
    
    .filters-toggle {
        display: flex;
        align-items: center;
        gap: 10px;
        padding: 12px 18px;
        background: rgba(255, 255, 255, 0.1);
        backdrop-filter: blur(20px);
        border: 1px solid rgba(255, 255, 255, 0.2);
        border-radius: 10px;
        color: #FFFFFF;
        font-size: 14px;
        font-weight: 500;
        cursor: pointer;
        transition: all 0.3s ease;
        min-width: 100px;
        justify-content: center;
    }
    
    .filters-toggle:hover {
        background: rgba(255, 255, 255, 0.15);
        border-color: rgba(255, 255, 255, 0.3);
        transform: translateY(-1px);
        box-shadow: 0 4px 15px rgba(0, 0, 0, 0.2);
    }
    
    .filters-toggle.active {
        background: rgba(252, 220, 36, 0.15);
        border-color: rgba(252, 220, 36, 0.4);
        color: #fcdc24;
    }
    
    .filters-toggle i {
        font-size: 12px;
        transition: transform 0.3s ease;
    }
    
    .filters-toggle.active i.fa-chevron-down {
        transform: rotate(180deg);
    }
    
    .filters-toggle .fa-filter {
        color: #fcdc24;
        font-size: 14px;
    }
    
    .filters-panel {
        position: absolute;
        top: 100%;
        right: 0;
        margin-top: 8px;
        background: rgba(26, 31, 43, 0.98);
        backdrop-filter: blur(25px);
        border: 1px solid rgba(255, 255, 255, 0.15);
        border-radius: 16px;
        padding: 20px;
        min-width: 320px;
        box-shadow: 0 20px 60px rgba(0, 0, 0, 0.4);
        opacity: 0;
        visibility: hidden;
        transform: translateY(-10px);
        transition: all 0.3s ease;
        z-index: 1000;
    }
    
    .filters-panel.active {
        opacity: 1;
        visibility: visible;
        transform: translateY(0);
    }
    
    .filter-group {
        display: flex !important;
        flex-direction: row !important;
        gap: 20px !important;
        align-items: flex-start !important;
        flex-wrap: nowrap !important;
        box-sizing: border-box !important;
        position: relative !important;
        float: none !important;
        margin: 0 !important;
        padding: 0 !important;
        width: auto !important;
        background: transparent !important;
        border: none !important;
        box-shadow: none !important;
    }
    
    .filter-item {
        display: flex;
        flex-direction: column;
        gap: 8px;
        min-width: 120px;
    }
    
    .filter-item label {
        color: #fcdc24;
        font-size: 11px;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: 1px;
        margin: 0;
    }
    
    .filter-item .modern-select {
        background: rgba(255, 255, 255, 0.08);
        border: 1px solid rgba(255, 255, 255, 0.15);
        border-radius: 10px;
        color: #FFFFFF;
        padding: 10px 14px;
        font-size: 14px;
        font-weight: 500;
        outline: none;
        transition: all 0.3s ease;
        min-width: 120px;
        box-sizing: border-box;
    }
    
    .filter-item .modern-select:focus {
        border-color: #fcdc24;
        box-shadow: 0 0 0 3px rgba(252, 220, 36, 0.15);
        background: rgba(255, 255, 255, 0.12);
    }
    
    .filter-item .modern-select:hover {
        background: rgba(255, 255, 255, 0.12);
        border-color: rgba(255, 255, 255, 0.25);
        transform: translateY(-1px);
    }
    
    .filter-item .Nexora Service Suite-btn-secondary {
        background: rgba(255, 255, 255, 0.1);
        backdrop-filter: blur(20px);
        color: #FFFFFF;
        border: 1px solid rgba(255, 255, 255, 0.2);
        border-radius: 10px;
        padding: 10px 16px;
        font-weight: 600;
        font-size: 13px;
        transition: all 0.3s ease;
        white-space: nowrap;
        margin-top: 8px;
    }
    
    .filter-item .Nexora Service Suite-btn-secondary:hover {
        background: rgba(255, 255, 255, 0.15);
        border-color: rgba(255, 255, 255, 0.3);
        transform: translateY(-2px);
        box-shadow: 0 8px 25px rgba(0, 0, 0, 0.3);
    }
    
    .modern-select {
        background: rgba(255, 255, 255, 0.1);
        backdrop-filter: blur(20px);
        border: 1px solid rgba(255, 255, 255, 0.2);
        border-radius: 8px;
        color: #FFFFFF;
        padding: 10px 16px;
        font-size: 14px;
        outline: none;
        transition: all 0.3s ease;
        min-width: 120px;
    }
    
    .modern-select:focus {
        border-color: #fcdc24;
        box-shadow: 0 0 0 2px rgba(252, 220, 36, 0.2);
        background: rgba(255, 255, 255, 0.15);
    }
    
    .modern-select:hover {
        background: rgba(255, 255, 255, 0.15);
        border-color: rgba(255, 255, 255, 0.3);
    }
    
    .modern-select option {
        background: #1a1f2b;
        color: #FFFFFF;
        padding: 8px;
    }
    
    
    .Nexora Service Suite-modern-table-container {
        background: rgba(26, 31, 43, 0.2);
        backdrop-filter: blur(20px);
        border: 1px solid rgba(255, 255, 255, 0.2);
        border-radius: 16px;
        box-shadow: 0 8px 32px rgba(0, 0, 0, 0.3);
        overflow: hidden;
    }
    
    .table-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        padding: 24px;
        border-bottom: 1px solid rgba(255, 255, 255, 0.1);
        background: rgba(255, 255, 255, 0.05);
    }
    
    .table-info {
        color: #CBD5E0;
        font-size: 14px;
    }
    
    .table-actions {
        display: flex;
        gap: 12px;
    }
    
    .Nexora Service Suite-btn {
        display: inline-flex;
        align-items: center;
        gap: 8px;
        padding: 10px 20px;
        border: none;
        border-radius: 8px;
        font-size: 14px;
        font-weight: 500;
        text-decoration: none;
        cursor: pointer;
        transition: all 0.3s ease;
        font-family: 'Inter', Arial, sans-serif;
    }
    
    .Nexora Service Suite-btn-primary {
        background: linear-gradient(135deg, #6c5dd3 0%, #8b7ae6 100%);
        color: #FFFFFF;
        box-shadow: 0 4px 15px rgba(108, 93, 211, 0.3);
    }
    
    .Nexora Service Suite-btn-primary:hover {
        transform: translateY(-2px);
        box-shadow: 0 6px 20px rgba(108, 93, 211, 0.4);
    }
    
    .Nexora Service Suite-btn-secondary {
        background: rgba(255, 255, 255, 0.1);
        backdrop-filter: blur(20px);
        color: #FFFFFF;
        border: 1px solid rgba(255, 255, 255, 0.2);
    }
    
    .Nexora Service Suite-btn-secondary:hover {
        background: rgba(255, 255, 255, 0.2);
        border-color: rgba(255, 255, 255, 0.3);
        transform: translateY(-1px);
    }
    
    .Nexora Service Suite-btn-small {
        padding: 8px 16px;
        font-size: 13px;
    }
    
    .btn-icon {
        font-size: 16px;
    }
    
    .Nexora Service Suite-table-wrapper {
        overflow-x: auto;
    }
    
    .Nexora Service Suite-modern-table {
        width: 100%;
        border-collapse: collapse;
        background: transparent;
    }
    
    .Nexora Service Suite-modern-table th {
        background: rgba(255, 255, 255, 0.05);
        color: #fcdc24 !important;
        font-weight: 600;
        text-align: left;
        padding: 16px 24px;
        border-bottom: 1px solid rgba(255, 255, 255, 0.1);
        font-size: 14px;
    }
    
    .Nexora Service Suite-modern-table td {
        padding: 16px 24px;
        border-bottom: 1px solid rgba(255, 255, 255, 0.05);
        color: #E2E8F0;
        font-size: 14px;
    }
    
    .Nexora Service Suite-modern-table tr:hover {
        background: rgba(255, 255, 255, 0.05);
    }
    
    .loading-row {
        text-align: center;
        padding: 40px;
        color: #CBD5E0;
    }
    
    .loading-spinner {
        display: inline-block;
        width: 20px;
        height: 20px;
        border: 2px solid rgba(255, 255, 255, 0.3);
        border-radius: 50%;
        border-top-color: #6c5dd3;
        animation: spin 1s ease-in-out infinite;
        margin-right: 12px;
    }
    
    @keyframes spin {
        to { transform: rotate(360deg); }
    }
    
    
    .Nexora Service Suite-modern-pagination {
        display: flex;
        justify-content: space-between;
        align-items: center;
        padding: 24px;
        border-top: 1px solid rgba(255, 255, 255, 0.1);
        background: rgba(255, 255, 255, 0.05);
    }
    
    .pagination-info {
        color: #CBD5E0;
        font-size: 14px;
    }
    
    
    .status-badge {
        display: inline-block;
        padding: 4px 12px;
        border-radius: 20px;
        font-size: 12px;
        font-weight: 600;
        text-transform: uppercase;
    }
    
    .status-active {
        background: rgba(34, 197, 94, 0.2);
        color: #22c55e;
        border: 1px solid rgba(34, 197, 94, 0.3);
    }
    
    .status-inactive {
        background: rgba(239, 68, 68, 0.2);
        color: #ef4444;
        border: 1px solid rgba(239, 68, 68, 0.3);
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
        
        .search-filters-container {
            flex-direction: column;
            gap: 12px;
        }
        
        .filters-panel {
            right: auto;
            left: 0;
            min-width: 100%;
            max-width: 100%;
            padding: 16px;
        }
        
        .filter-group {
            flex-direction: column;
            gap: 16px;
            align-items: stretch;
        }
        
        .filter-item {
            min-width: 100%;
        }
        
        .filter-item .modern-select {
            min-width: 100%;
        }
        
        .filter-item .Nexora Service Suite-btn-secondary {
            width: 100%;
            justify-content: center;
        }
        
        .table-header {
            flex-direction: column;
            gap: 16px;
            align-items: stretch;
        }
        
        .table-actions {
            justify-content: center;
        }
    }

    
    .service-modal {
        display: none;
        position: fixed;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        z-index: 10000;
    }

    .modal-overlay {
        position: absolute;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        background: rgba(0, 0, 0, 0.7);
        backdrop-filter: blur(8px);
    }

    .modal-container {
        position: absolute;
        top: 50%;
        left: 50%;
        transform: translate(-50%, -50%);
        background: rgba(26, 31, 43, 0.95);
        backdrop-filter: blur(20px);
        border: 1px solid rgba(255, 255, 255, 0.2);
        border-radius: 16px;
        box-shadow: 0 20px 60px rgba(0, 0, 0, 0.5);
        width: 90%;
        max-width: 500px;
        max-height: 90vh;
        overflow: hidden;
        animation: modalSlideIn 0.3s ease-out;
    }

    @keyframes modalSlideIn {
        from {
            opacity: 0;
            transform: translate(-50%, -50%) scale(0.9);
        }
        to {
            opacity: 1;
            transform: translate(-50%, -50%) scale(1);
        }
    }

    .modal-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        padding: 24px;
        border-bottom: 1px solid rgba(255, 255, 255, 0.1);
        background: rgba(255, 255, 255, 0.05);
    }

    .modal-header h2 {
        margin: 0;
        color: #FFFFFF;
        font-size: 20px;
        font-weight: 600;
    }

    .modal-close {
        background: none;
        border: none;
        color: #CBD5E0;
        font-size: 24px;
        cursor: pointer;
        padding: 8px;
        border-radius: 8px;
        transition: all 0.3s ease;
        width: 40px;
        height: 40px;
        display: flex;
        align-items: center;
        justify-content: center;
    }

    .modal-close:hover {
        background: rgba(255, 255, 255, 0.1);
        color: #FFFFFF;
    }

    .modal-body {
        padding: 24px;
        max-height: calc(90vh - 120px);
        overflow-y: auto;
    }

    
    .form-group {
        margin-bottom: 20px;
    }

    .form-group label {
        display: block;
        margin-bottom: 8px;
        color: #E2E8F0;
        font-weight: 500;
        font-size: 14px;
    }

    .form-group input,
    .form-group textarea,
    .form-group select {
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

    .form-group input:focus,
    .form-group textarea:focus,
    .form-group select:focus {
        outline: none;
        border-color: #6c5dd3;
        box-shadow: 0 0 0 3px rgba(108, 93, 211, 0.2);
        background: rgba(255, 255, 255, 0.15);
    }

    .form-group input::placeholder,
    .form-group textarea::placeholder {
        color: rgba(255, 255, 255, 0.5);
    }

    .form-group textarea {
        resize: vertical;
        min-height: 100px;
    }

    .form-actions {
        display: flex;
        justify-content: flex-end;
        gap: 12px;
        margin-top: 32px;
        padding-top: 20px;
        border-top: 1px solid rgba(255, 255, 255, 0.1);
    }

    
    .notification {
        position: fixed;
        top: 20px;
        right: 20px;
        background: rgba(26, 31, 43, 0.95);
        backdrop-filter: blur(20px);
        border: 1px solid rgba(255, 255, 255, 0.2);
        border-radius: 12px;
        padding: 16px 20px;
        color: #FFFFFF;
        font-size: 14px;
        font-weight: 500;
        z-index: 10001;
        display: flex;
        align-items: center;
        gap: 12px;
        box-shadow: 0 8px 32px rgba(0, 0, 0, 0.3);
        animation: notificationSlideIn 0.3s ease-out;
        max-width: 400px;
    }

    @keyframes notificationSlideIn {
        from {
            opacity: 0;
            transform: translateX(100%);
        }
        to {
            opacity: 1;
            transform: translateX(0);
        }
    }

    .notification-success {
        border-left: 4px solid #22c55e;
        background: rgba(34, 197, 94, 0.1);
    }

    .notification-error {
        border-left: 4px solid #ef4444;
        background: rgba(239, 68, 68, 0.1);
    }

    .notification-info {
        border-left: 4px solid #3b82f6;
        background: rgba(59, 130, 246, 0.1);
    }

    .notification button {
        background: none;
        border: none;
        color: #CBD5E0;
        font-size: 18px;
        cursor: pointer;
        padding: 4px;
        border-radius: 4px;
        transition: all 0.3s ease;
        margin-left: auto;
    }

    .notification button:hover {
        color: #FFFFFF;
        background: rgba(255, 255, 255, 0.1);
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
                <a href="<?php echo admin_url('admin.php?page=Nexora Service Suite-services'); ?>" class="nav-link active" title="Dienstleistungen">
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
                <i class="fas fa-tools"></i>
                Dienstleistungen
            </div>
            
            <div class="topbar-right">
                <div class="search-filters-container">
                    <div class="search-box">
                        <i class="fas fa-search"></i>
                        <input type="text" id="Nexora Service Suite-service-search" placeholder="Dienstleistung suchen..." />
                    </div>
                    
                    <div class="filters-dropdown">
                        <button class="filters-toggle" id="filters-toggle">
                            <i class="fas fa-filter"></i>
                            <span>Filter</span>
                            <i class="fas fa-chevron-down"></i>
                        </button>
                        
                        <div class="filters-panel" id="filters-panel">
                            <div class="filter-group">
                                <div class="filter-item">
                                    <label for="Nexora Service Suite-customer-type-filter">Kundentyp:</label>
                                    <select id="Nexora Service Suite-customer-type-filter" class="modern-select">
                                        <option value="">Alle Kunden</option>
                                        <option value="business">Geschäftskunden</option>
                                        <option value="private">Privatkunden</option>
                                    </select>
                                </div>
                                
                                <div class="filter-item">
                                    <label for="Nexora Service Suite-status-filter">Status:</label>
                                    <select id="Nexora Service Suite-status-filter" class="modern-select">
                                        <option value="">Alle Status</option>
                                        <option value="active">Aktiv</option>
                                        <option value="inactive">Inaktiv</option>
                                    </select>
                                </div>
                                
                                <div class="filter-item">
                                    <button id="Nexora Service Suite-reset-filter-btn" class="Nexora Service Suite-btn Nexora Service Suite-btn-secondary">
                                        <span class="btn-icon">🔄</span>
                                        Zurücksetzen
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        
        <div class="Nexora Service Suite-modern-table-container">
            <div class="table-header">
                <div class="table-info">
                    <span id="Nexora Service Suite-table-info">Lade Daten...</span>
                </div>
                <div class="table-actions">
                    <button class="Nexora Service Suite-btn Nexora Service Suite-btn-primary" id="Nexora Service Suite-add-service">
                        <span class="btn-icon">➕</span>
                        Neue Dienstleistung
                    </button>
                    <button class="Nexora Service Suite-btn Nexora Service Suite-btn-secondary" id="Nexora Service Suite-refresh-services">
                        <span class="btn-icon">🔄</span>
                        Aktualisieren
                    </button>
                </div>
            </div>

            <div class="Nexora Service Suite-table-wrapper">
                <table class="Nexora Service Suite-modern-table">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Titel</th>
                            <th>Beschreibung</th>
                            <th>Kunde</th>
                            <th>Kundentyp</th>
                            <th>Kosten</th>
                            <th>Status</th>
                            <th>Erstellt am</th>
                            <th>Aktionen</th>
                        </tr>
                    </thead>
                    <tbody id="Nexora Service Suite-service-list">
                        <tr>
                            <td colspan="9" class="loading-row">
                                <div class="loading-spinner"></div>
                                <span>Lade Dienstleistungen...</span>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>

            
            <div class="Nexora Service Suite-modern-pagination">
                <button id="Nexora Service Suite-prev-page" class="Nexora Service Suite-btn Nexora Service Suite-btn-secondary" disabled>
                    <span class="btn-icon">←</span>
                    Vorherige
                </button>
                
                <div class="pagination-info">
                    <span id="Nexora Service Suite-page-info">Seite 1 von 1</span>
                </div>
                
                <button id="Nexora Service Suite-next-page" class="Nexora Service Suite-btn Nexora Service Suite-btn-secondary" disabled>
                    Nächste
                    <span class="btn-icon">→</span>
                </button>
            </div>
        </div>
    </div>

    
    <?php include __DIR__ . '/services-list/services-list-access.php'; ?>

    <script>
        jQuery(document).ready(function($) {
            const ajaxUrl = '<?php echo admin_url('admin-ajax.php'); ?>';
            const nonce = '<?php echo wp_create_nonce('nexora_nonce'); ?>';
            
            let currentPage = 1;
            let totalPages = 1;
            let currentSearch = '';
            let currentStatusFilter = '';
            console.log('🔍 Debug: WordPress AJAX URL:', ajaxUrl);
            console.log('🔍 Debug: Nonce:', nonce);
            function testDatabaseConnection() {
                console.log('🧪 Testing database connection...');
                $.ajax({
                    url: ajaxurl,
                    type: 'POST',
                    data: {
                        action: 'nexora_test_db',
                        nonce: nonce
                    },
                    success: function(response) {
                        console.log('✅ Database test response:', response);
                        if (response.success) {
                            loadServices();
                        } else {
                            showError('Datenbank-Verbindung fehlgeschlagen: ' + response.data);
                        }
                    },
                    error: function(xhr, status, error) {
                        console.error('❌ Database test failed:', error);
                        showError('Verbindungsfehler: ' + error);
                    }
                });
            }

            function showError(message) {
                $('#Nexora Service Suite-service-list').html(`
                    <tr>
                        <td colspan="9" style="text-align: center; padding: 3rem 1rem; color: #ef4444;">
                            <div style="font-size: 3rem; margin-bottom: 1rem;">⚠️</div>
                            <h3 style="margin: 0 0 0.5rem 0;">Fehler</h3>
                            <p style="margin: 0;">${message}</p>
                        </td>
                    </tr>
                `);
            }

            function loadServices(page = 1, search = '', status = '') {
                console.log('🔄 Loading services...', { page, search, status });
                $('#Nexora Service Suite-service-list').html(`
                    <tr>
                        <td colspan="9" class="loading-row">
                            <div class="loading-spinner"></div>
                            <span>Lade Dienstleistungen...</span>
                        </td>
                    </tr>
                `);
                
                $.ajax({
                    url: ajaxurl,
                    type: 'POST',
                    data: {
                        action: 'nexora_get_services',
                        page: page,
                        per_page: 10,
                        search: search,
                        status_filter: status,
                        nonce: nonce
                    },
                    success: function(response) {
                        console.log('✅ AJAX Response:', response);
                        if (response.success) {
                            const services = response.data.services;
                            
                            currentPage = response.data.page;
                            totalPages = response.data.total_pages;
                            currentSearch = search;
                            currentStatusFilter = status;
                            $('#Nexora Service Suite-page-info').text(`Seite ${currentPage} von ${totalPages}`);
                            $('#Nexora Service Suite-prev-page').prop('disabled', currentPage <= 1);
                            $('#Nexora Service Suite-next-page').prop('disabled', currentPage >= totalPages);
                            $('#Nexora Service Suite-table-info').text(`${services.length} Dienstleistungen geladen`);

                            let html = '';
                            if (services.length > 0) {
                                services.forEach(service => {
                                    const customerName = service.company_name || service.user_login || '-';
                                    const customerType = service.customer_type || '-';
                                    const userInfo = service.user_login ? 
                                        `<br><small style="color: #CBD5E0;">Benutzer: ${service.user_login}</small>` : '';
                                    
                                    html += `
                                        <tr>
                                            <td><strong>#${service.id}</strong></td>
                                            <td>
                                                <div>
                                                    <strong>${service.title}</strong>
                                                    ${userInfo}
                                                </div>
                                            </td>
                                            <td>${service.description || '-'}</td>
                                            <td>${customerName}</td>
                                            <td>${customerType}</td>
                                            <td><strong>${service.cost} €</strong></td>
                                            <td>
                                                <span class="status-badge status-${service.status}">
                                                    ${service.status === 'active' ? 'Aktiv' : 'Inaktiv'}
                                                </span>
                                            </td>
                                            <td>${new Date(service.created_at).toLocaleDateString('de-AT')}</td>
                                            <td class="actions-cell">
                                                <div class="actions-container">
                                                    <button type="button" class="action-btn edit-btn" data-id="${service.id}" title="Bearbeiten">
                                                        <i class="fas fa-edit"></i>
                                                    </button>
                                                    <button type="button" class="action-btn delete-btn" data-id="${service.id}" title="Löschen">
                                                        <i class="fas fa-trash"></i>
                                                    </button>
                                                </div>
                                            </td>
                                        </tr>`;
                                });
                            } else {
                                html = `
                                    <tr>
                                        <td colspan="9" style="text-align: center; padding: 3rem 1rem; color: #CBD5E0;">
                                            <div style="font-size: 3rem; margin-bottom: 1rem;">📋</div>
                                            <h3 style="margin: 0 0 0.5rem 0;">Keine Dienstleistungen gefunden</h3>
                                            <p style="margin: 0;">Versuchen Sie andere Suchkriterien oder fügen Sie eine neue Dienstleistung hinzu.</p>
                                            <button class="Nexora Service Suite-btn Nexora Service Suite-btn-primary" onclick="createSampleServices()" style="margin-top: 16px;">
                                                <span class="btn-icon">➕</span>
                                                Beispieldaten erstellen
                                            </button>
                                        </td>
                                    </tr>`;
                            }
                            $('#Nexora Service Suite-service-list').html(html);
                        } else {
                            console.error('❌ AJAX Error:', response);
                            showError('Fehler beim Laden der Daten: ' + response.data);
                        }
                    },
                    error: function(xhr, status, error) {
                        console.error('💥 Connection Error:', xhr, status, error);
                        showError('Verbindungsfehler: ' + error);
                    }
                });
            }
            testDatabaseConnection();
            $('#Nexora Service Suite-service-search').on('input', function() {
                const search = $(this).val();
                const status = $('#Nexora Service Suite-status-filter').val();
                loadServices(1, search, status);
            });
            $('#Nexora Service Suite-status-filter').on('change', function() {
                const search = $('#Nexora Service Suite-service-search').val();
                const status = $(this).val();
                loadServices(1, search, status);
            });
            $('#Nexora Service Suite-reset-filter-btn').click(function(e) {
                e.preventDefault();
                $('#Nexora Service Suite-service-search').val('');
                $('#Nexora Service Suite-customer-type-filter').val('');
                $('#Nexora Service Suite-status-filter').val('');
                loadServices(1, '', '');
            });
            $('#Nexora Service Suite-refresh-services').click(function(e) {
                e.preventDefault();
                loadServices(currentPage, currentSearch, currentStatusFilter);
            });
            $('#Nexora Service Suite-next-page').click(function() {
                if (currentPage < totalPages) {
                    loadServices(currentPage + 1, currentSearch, currentStatusFilter);
                }
            });

            $('#Nexora Service Suite-prev-page').click(function() {
                if (currentPage > 1) {
                    loadServices(currentPage - 1, currentSearch, currentStatusFilter);
                }
            });
            $('#Nexora Service Suite-add-service').click(function(e) {
                e.preventDefault();
                showServiceModal();
            });
            function showServiceModal(serviceData = null) {
                const isEdit = serviceData !== null;
                const modalTitle = isEdit ? 'Dienstleistung bearbeiten' : 'Neue Dienstleistung';
                
                const modalHtml = `
                    <div id="service-modal" class="service-modal">
                        <div class="modal-overlay"></div>
                        <div class="modal-container">
                            <div class="modal-header">
                                <h2>${modalTitle}</h2>
                                <button class="modal-close" onclick="closeServiceModal()">×</button>
                            </div>
                            <div class="modal-body">
                                <form id="service-form">
                                    <input type="hidden" id="service-id" value="${serviceData ? serviceData.id : ''}">
                                    
                                    <div class="form-group">
                                        <label for="service-title">Titel *</label>
                                        <input type="text" id="service-title" required value="${serviceData ? serviceData.title : ''}" placeholder="z.B. iPhone Reparatur">
                                    </div>
                                    
                                    <div class="form-group">
                                        <label for="service-description">Beschreibung</label>
                                        <textarea id="service-description" rows="4" placeholder="Beschreibung der Dienstleistung">${serviceData ? serviceData.description : ''}</textarea>
                                    </div>
                                    
                                    <div class="form-group">
                                        <label for="service-cost">Kosten (€) *</label>
                                        <input type="number" id="service-cost" step="0.01" min="0" required value="${serviceData ? serviceData.cost : ''}" placeholder="0.00">
                                    </div>
                                    
                                    <div class="form-group">
                                        <label for="service-status">Status *</label>
                                        <select id="service-status" required>
                                            <option value="">Status wählen</option>
                                            <option value="active" ${serviceData && serviceData.status === 'active' ? 'selected' : ''}>Aktiv</option>
                                            <option value="inactive" ${serviceData && serviceData.status === 'inactive' ? 'selected' : ''}>Inaktiv</option>
                                        </select>
                                    </div>
                                    
                                    <div class="form-actions">
                                        <button type="button" class="Nexora Service Suite-btn Nexora Service Suite-btn-secondary" onclick="closeServiceModal()">
                                            Abbrechen
                                        </button>
                                        <button type="submit" class="Nexora Service Suite-btn Nexora Service Suite-btn-primary">
                                            <span class="btn-icon">💾</span>
                                            ${isEdit ? 'Aktualisieren' : 'Speichern'}
                                        </button>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>
                `;
                
                $('body').append(modalHtml);
                $('#service-modal').fadeIn(300);
            }
            window.closeServiceModal = function() {
                $('#service-modal').fadeOut(300, function() {
                    $(this).remove();
                });
            }
            $(document).on('submit', '#service-form', function(e) {
                e.preventDefault();
                
                const serviceId = $('#service-id').val();
                const isEdit = serviceId !== '';
                
                const formData = {
                    title: $('#service-title').val(),
                    description: $('#service-description').val(),
                    cost: $('#service-cost').val(),
                    status: $('#service-status').val(),
                    nonce: nonce
                };
                
                if (isEdit) {
                    formData.id = serviceId;
                    formData.action = 'nexora_update_service';
                } else {
                    formData.action = 'nexora_add_service';
                }
                const submitBtn = $(this).find('button[type="submit"]');
                const originalText = submitBtn.html();
                submitBtn.html('<span class="btn-icon">⏳</span> Speichern...').prop('disabled', true);
                
                $.ajax({
                    url: ajaxurl,
                    type: 'POST',
                    data: formData,
                    success: function(response) {
                        if (response.success) {
                            showNotification(`✅ Dienstleistung wurde erfolgreich ${isEdit ? 'aktualisiert' : 'hinzugefügt'}!`, 'success');
                            closeServiceModal();
                            loadServices(currentPage, currentSearch, currentStatusFilter);
                        } else {
                            showNotification('❌ Fehler: ' + response.data, 'error');
                        }
                    },
                    error: function(xhr, status, error) {
                        showNotification('❌ Verbindungsfehler: ' + error, 'error');
                    },
                    complete: function() {
                        submitBtn.html(originalText).prop('disabled', false);
                    }
                });
            });
            $(document).on('click', '.edit-btn', function(e) {
                e.preventDefault();
                const serviceId = $(this).data('id');
                const serviceRow = $(this).closest('tr');
                const serviceData = {
                    id: serviceId,
                    title: serviceRow.find('td:eq(1) strong').text(),
                    description: serviceRow.find('td:eq(2)').text(),
                    cost: serviceRow.find('td:eq(5) strong').text().replace('€', '').trim(),
                    status: serviceRow.find('td:eq(6) .status-active').length > 0 ? 'active' : 'inactive'
                };
                
                showServiceModal(serviceData);
            });
            $(document).on('click', '.delete-btn', function(e) {
                e.preventDefault();
                const serviceId = $(this).data('id');
                
                if (confirm('Sind Sie sicher, dass Sie diese Dienstleistung löschen möchten? Diese Aktion kann nicht rückgängig gemacht werden.')) {
                    $.ajax({
                        url: ajaxurl,
                        type: 'POST',
                        data: {
                            action: 'nexora_delete_service',
                            id: serviceId,
                            nonce: nonce
                        },
                        success: function(response) {
                            if (response.success) {
                                showNotification('✅ Dienstleistung wurde erfolgreich gelöscht!', 'success');
                                loadServices(currentPage, currentSearch, currentStatusFilter);
                            } else {
                                showNotification('❌ Fehler beim Löschen: ' + response.data, 'error');
                            }
                        },
                        error: function(xhr, status, error) {
                            showNotification('❌ Verbindungsfehler: ' + error, 'error');
                        }
                    });
                }
            });
            function showNotification(message, type = 'info') {
                const notification = $(`
                    <div class="notification notification-${type}">
                        <span>${message}</span>
                        <button onclick="$(this).parent().fadeOut(300, function() { $(this).remove(); })">×</button>
                    </div>
                `);
                
                $('body').append(notification);
                notification.fadeIn(300);
                setTimeout(() => {
                    notification.fadeOut(300, function() {
                        $(this).remove();
                    });
                }, 5000);
            }
            window.createSampleServices = function() {
                console.log('🔄 Creating sample services...');
                $.ajax({
                    url: ajaxurl,
                    type: 'POST',
                    data: {
                        action: 'nexora_create_sample_services',
                        nonce: nonce
                    },
                    success: function(response) {
                        console.log('✅ Sample services response:', response);
                        if (response.success) {
                            alert('Beispieldaten wurden erfolgreich erstellt!');
                            loadServices();
                        } else {
                            alert('Fehler beim Erstellen der Beispieldaten: ' + response.data);
                        }
                    },
                    error: function(xhr, status, error) {
                        console.error('❌ Create sample services failed:', error);
                        alert('Fehler beim Erstellen der Beispieldaten: ' + error);
                    }
                });
            };
            $('#filters-toggle').on('click', function(e) {
                e.preventDefault();
                e.stopPropagation();
                
                const $toggle = $(this);
                const $panel = $('#filters-panel');
                
                $toggle.toggleClass('active');
                $panel.toggleClass('active');
            });
            $(document).on('click', function(e) {
                if (!$(e.target).closest('.filters-dropdown').length) {
                    $('#filters-toggle').removeClass('active');
                    $('#filters-panel').removeClass('active');
                }
            });
            $(document).on('keydown', function(e) {
                if (e.key === 'Escape') {
                    $('#filters-toggle').removeClass('active');
                    $('#filters-panel').removeClass('active');
                }
            });
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