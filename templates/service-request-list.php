<!DOCTYPE html>
<html lang="de">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Serviceanfragen - Nexora Service Suite Dashboard</title>
    
    
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
    
    
    .Nexora Service Suite-dashboard-nav {
        display: flex;
        background: rgba(255, 255, 255, 0.05);
        border-radius: 16px;
        padding: 8px;
        box-shadow: 0 4px 24px rgba(76,110,245,0.08);
        margin-bottom: 24px;
        flex-wrap: wrap;
        gap: 4px;
        backdrop-filter: blur(20px);
        border: 1px solid rgba(255, 255, 255, 0.1);
    }
    
    .Nexora Service Suite-nav-item {
        padding: 12px 20px;
        border-radius: 12px;
        color: #6c63ff;
        font-weight: 600;
        cursor: pointer;
        transition: all 0.3s ease;
        text-decoration: none;
        display: flex;
        align-items: center;
        gap: 8px;
        white-space: nowrap;
        position: relative;
    }
    
    .Nexora Service Suite-nav-item i {
        font-size: 14px;
    }
    
    .Nexora Service Suite-nav-item.active {
        background: #6c63ff;
        color: #fff;
        box-shadow: 0 2px 8px rgba(108, 99, 255, 0.3);
    }
    
    .Nexora Service Suite-nav-item:hover:not(.active) {
        background: rgba(108, 99, 255, 0.1);
        color: #6c63ff;
}

.Nexora Service Suite-modern-filters {
    display: flex;
    flex-wrap: wrap;
    gap: 1rem;
    margin-bottom: 2rem;
    padding: 1.5rem;
        background: rgba(255, 255, 255, 0.05);
        border-radius: 16px;
        border: 1px solid rgba(255, 255, 255, 0.1);
        backdrop-filter: blur(20px);
}

.filter-group {
    display: flex;
    gap: 1rem;
    align-items: center;
}

    .modern-select {
        background: rgba(255, 255, 255, 0.1);
        border: 1px solid rgba(255, 255, 255, 0.2);
        border-radius: 8px;
        color: #FFFFFF;
        padding: 8px 12px;
        font-size: 14px;
    outline: none;
        transition: all 0.3s ease;
    }
    
    .modern-select:focus {
        border-color: #6c63ff;
        box-shadow: 0 0 0 3px rgba(108, 99, 255, 0.1);
    }
    
    .Nexora Service Suite-btn {
        padding: 10px 20px;
    border: none;
    border-radius: 8px;
        font-weight: 600;
    cursor: pointer;
        transition: all 0.3s ease;
    display: flex;
    align-items: center;
    gap: 8px;
        font-size: 14px;
    }
    
    .Nexora Service Suite-btn-primary {
        background: linear-gradient(135deg, #6C5DD3, #8B5CF6);
        color: white;
        box-shadow: 0 4px 15px rgba(108, 93, 211, 0.3);
    }
    
    .Nexora Service Suite-btn-primary:hover {
        transform: translateY(-2px);
        box-shadow: 0 6px 20px rgba(108, 93, 211, 0.4);
    }
    
    .Nexora Service Suite-btn-secondary {
        background: rgba(255, 255, 255, 0.1);
        color: #FFFFFF;
        border: 1px solid rgba(255, 255, 255, 0.2);
    }
    
    .Nexora Service Suite-btn-secondary:hover {
        background: rgba(255, 255, 255, 0.2);
        border-color: rgba(255, 255, 255, 0.3);
    }
    
    .Nexora Service Suite-btn-danger {
        background: rgba(220, 53, 69, 0.9);
        color: #FFFFFF;
        border: 1px solid rgba(220, 53, 69, 0.8);
    }
    
    .Nexora Service Suite-btn-danger:hover {
        background: rgba(220, 53, 69, 1);
        border-color: rgba(220, 53, 69, 1);
        transform: translateY(-1px);
    }
    
    
.Nexora Service Suite-modern-table-container {
        background: rgba(255, 255, 255, 0.05);
        border-radius: 16px;
        padding: 24px;
        backdrop-filter: blur(20px);
        border: 1px solid rgba(255, 255, 255, 0.1);
}

.table-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
        margin-bottom: 24px;
        background: transparent;
}

.table-info {
        color: #CBD5E0;
        font-size: 14px;
}

    .table-actions {
    display: flex;
    gap: 12px;
}

.Nexora Service Suite-modern-table {
    width: 100%;
    border-collapse: collapse;
        background: rgba(255, 255, 255, 0.05);
        border-radius: 12px;
        overflow: hidden;
}

.Nexora Service Suite-modern-table th {
        background: rgba(108, 99, 255, 0.1);
        color: #fcdc24 !important;
        padding: 15.68px 11.76px;
    text-align: left;
    font-weight: 600;
        border-bottom: 1px solid rgba(255, 255, 255, 0.1);
}

.Nexora Service Suite-modern-table td {
        padding: 15.68px 11.76px;
        border-bottom: 1px solid rgba(255, 255, 255, 0.05);
        color: #FFFFFF;
    }
    
    .Nexora Service Suite-modern-table tr:hover {
        background: rgba(255, 255, 255, 0.05) !important;
    }
    
.sortable {
    cursor: pointer;
    position: relative;
}
    
.sort-arrow {
        margin-left: 8px;
        opacity: 0.5;
    }
    
    .sortable:hover .sort-arrow {
        opacity: 1;
}

.status-badge {
        padding: 4px 12px;
        border-radius: 20px;
        font-size: 12px;
        font-weight: 600;
    text-transform: uppercase;
}

.status-pending {
        background: rgba(255, 193, 7, 0.2);
        color: #FFC107;
        border: 1px solid rgba(255, 193, 7, 0.3);
}

.status-in_progress {
        background: rgba(0, 123, 255, 0.2);
        color: #007BFF;
        border: 1px solid rgba(0, 123, 255, 0.3);
}

.status-completed {
        background: rgba(40, 167, 69, 0.2);
        color: #28A745;
        border: 1px solid rgba(40, 167, 69, 0.3);
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
        font-size: 14px;
        line-height: 1;
    }
    
    
    .log-modal-overlay {
    position: fixed;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
        background: rgba(0, 0, 0, 0.8);
        z-index: 10000;
        display: flex;
        align-items: center;
        justify-content: center;
        backdrop-filter: blur(10px);
    }
    
    .log-modal-content {
        background: rgba(26, 31, 43, 0.95);
        border: 1px solid rgba(255, 255, 255, 0.2);
        border-radius: 16px;
    width: 90%;
        max-width: 800px;
        max-height: 80vh;
    overflow: hidden;
        box-shadow: 0 20px 60px rgba(0, 0, 0, 0.5);
        backdrop-filter: blur(20px);
}

    .log-modal-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
        padding: 20px 24px;
        border-bottom: 1px solid rgba(255, 255, 255, 0.1);
        background: rgba(108, 99, 255, 0.1);
    }
    
    .log-modal-header h3 {
    margin: 0;
        color: #fcdc24;
        font-size: 18px;
    font-weight: 600;
}

    .log-modal-close {
    background: none;
    border: none;
        color: #FFFFFF;
        font-size: 24px;
    cursor: pointer;
        padding: 0;
        width: 32px;
        height: 32px;
        border-radius: 6px;
        transition: all 0.3s ease;
    }
    
    .log-modal-close:hover {
        background: rgba(255, 255, 255, 0.1);
        transform: scale(1.1);
    }
    
    .log-modal-body {
        padding: 24px;
        max-height: 60vh;
    overflow-y: auto;
}

    .log-loading {
        text-align: center;
        color: #CBD5E0;
        font-style: italic;
    }
    
    .log-entry {
        background: rgba(255, 255, 255, 0.05);
        border: 1px solid rgba(255, 255, 255, 0.1);
    border-radius: 8px;
    padding: 16px;
        margin-bottom: 12px;
        transition: all 0.3s ease;
    }
    
    .log-entry:hover {
        background: rgba(255, 255, 255, 0.08);
        transform: translateX(4px);
    }
    
    .log-timestamp {
        color: #6c63ff;
        font-size: 12px;
    font-weight: 600;
        margin-bottom: 8px;
    }
    
    .log-message {
        color: #FFFFFF;
        font-size: 14px;
    margin-bottom: 8px;
        line-height: 1.5;
    }
    
    .log-user {
        color: #CBD5E0;
        font-size: 12px;
        font-style: italic;
    }
    
    .log-old-value {
        background: rgba(255, 107, 107, 0.1);
        border-left: 3px solid #FF6B6B;
        padding: 8px 12px;
        margin: 8px 0;
        border-radius: 4px;
        font-size: 12px;
        color: #FF6B6B;
    }
    
    .log-new-value {
        background: rgba(40, 167, 69, 0.1);
        border-left: 3px solid #28A745;
        padding: 8px 12px;
        margin: 8px 0;
    border-radius: 4px;
        font-size: 12px;
        color: #28A745;
    }
    
    .log-timestamp i,
    .log-message i,
    .log-user i {
        margin-right: 6px;
        opacity: 0.7;
}

.no-logs {
    text-align: center;
        color: #CBD5E0;
        font-style: italic;
    padding: 40px 20px;
    }
    
    .log-error {
        text-align: center;
        color: #FF6B6B;
        font-style: italic;
        padding: 40px 20px;
    }
    
    
    .notification {
        position: fixed;
        top: 20px;
        right: 20px;
        z-index: 10001;
        max-width: 400px;
        animation: slideInRight 0.3s ease;
    }
    
    .notification-content {
        background: rgba(26, 31, 43, 0.95);
        border: 1px solid rgba(255, 255, 255, 0.2);
        border-radius: 12px;
        padding: 16px 20px;
        backdrop-filter: blur(20px);
        box-shadow: 0 8px 32px rgba(0, 0, 0, 0.3);
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 16px;
    }
    
    .notification-success {
        border-left: 4px solid #28A745;
    }
    
    .notification-error {
        border-left: 4px solid #FF6B6B;
    }
    
    .notification-warning {
        border-left: 4px solid #FFC107;
    }
    
    .notification-info {
        border-left: 4px solid #6c63ff;
    }
    
    .notification-message {
        color: #FFFFFF;
        font-size: 14px;
        font-weight: 500;
        flex: 1;
    }
    
    .notification-close {
        background: none;
        border: none;
        color: #CBD5E0;
        font-size: 18px;
        cursor: pointer;
        padding: 0;
        width: 24px;
        height: 24px;
        border-radius: 4px;
        transition: all 0.3s ease;
        display: flex;
        align-items: center;
        justify-content: center;
    }
    
    .notification-close:hover {
        background: rgba(255, 255, 255, 0.1);
        color: #FFFFFF;
    }
    
    @keyframes slideInRight {
        from {
            transform: translateX(100%);
            opacity: 0;
        }
        to {
            transform: translateX(0);
            opacity: 1;
        }
    }
    
    
    .Nexora Service Suite-modern-pagination {
        display: flex;
        justify-content: center;
        align-items: center;
        gap: 16px;
        margin-top: 24px;
        padding: 16px 0;
        background: transparent;
    }
    
    .pagination-info {
        color: #CBD5E0;
        font-size: 14px;
    }
    
    
    .loading-row {
        text-align: center;
        padding: 40px;
        color: #CBD5E0;
    }
    
    .loading-spinner {
        border: 3px solid rgba(255, 255, 255, 0.1);
        border-top: 3px solid #6c63ff;
        border-radius: 50%;
        width: 30px;
        height: 30px;
        animation: spin 1s linear infinite;
        margin: 0 auto 16px;
    }
    
    @keyframes spin {
        0% { transform: rotate(0deg); }
        100% { transform: rotate(360deg); }
    }
    
    .fa-spin {
        animation: spin 1s linear infinite;
    }
    
            
        .modal-overlay {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0, 0, 0, 0.8);
            z-index: 10000;
            display: flex;
            align-items: center;
            justify-content: center;
            backdrop-filter: blur(10px);
        }
        
        .modal-content {
            background: rgba(26, 31, 43, 0.95);
            border: 1px solid rgba(255, 255, 255, 0.2);
            border-radius: 16px;
            width: 90%;
            max-width: 1200px;
            max-height: 90vh;
            overflow: hidden;
            box-shadow: 0 20px 60px rgba(0, 0, 0, 0.5);
            backdrop-filter: blur(20px);
        }
        
        .modal-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 20px 24px;
            border-bottom: 1px solid rgba(255, 255, 255, 0.1);
            background: rgba(108, 99, 255, 0.1);
        }
        
        .modal-header h2 {
            margin: 0;
            color: #fcdc24;
            font-size: 20px;
            font-weight: 600;
        }
        
        .modal-close {
            background: none;
            border: none;
            color: #FFFFFF;
            font-size: 24px;
            cursor: pointer;
            padding: 0;
            width: 32px;
            height: 32px;
            border-radius: 6px;
            transition: all 0.3s ease;
        }
        
        .modal-close:hover {
            background: rgba(255, 255, 255, 0.1);
            transform: scale(1.1);
        }
        
        .modal-body {
            padding: 24px;
            max-height: 70vh;
            overflow-y: auto;
        }
        
        .modal-footer {
            display: flex;
            justify-content: flex-end;
            gap: 12px;
            padding: 20px 24px;
            border-top: 1px solid rgba(255, 255, 255, 0.1);
            background: rgba(26, 31, 43, 0.5);
        }
        
        .form-section {
            margin-bottom: 30px;
            padding: 20px;
            background: rgba(255, 255, 255, 0.05);
            border-radius: 12px;
            border: 1px solid rgba(255, 255, 255, 0.1);
        }
        
        .form-section h3 {
            color: #6c63ff;
            font-size: 18px;
            font-weight: 600;
            margin-bottom: 20px;
            padding-bottom: 10px;
            border-bottom: 2px solid rgba(108, 99, 255, 0.2);
        }
        
        .form-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 20px;
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
        
        .form-control.error {
            border-color: #ef4444;
            box-shadow: 0 0 0 3px rgba(239, 68, 68, 0.2);
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
            min-width: 120px;
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
        
        .btn-secondary {
            background: rgba(255, 255, 255, 0.1);
            color: #FFFFFF;
            border: 1px solid rgba(255, 255, 255, 0.2);
        }
        
        .btn-secondary:hover {
            background: rgba(255, 255, 255, 0.2);
            border-color: rgba(255, 255, 255, 0.3);
        }

        
        @media (max-width: 1200px) {
            .dashboard-container {
                margin-left: 100px;
            }
        }
    
    @media (max-width: 768px) {
        .dashboard-container {
            padding: 16px;
            margin-left: 0;
        }
        
        .vertical-nav {
            width: 60px;
        }
        
        .vertical-nav:hover {
            width: 200px;
        }
        
        .Nexora Service Suite-modern-filters {
            flex-direction: column;
            gap: 1rem;
        }
        
        .filter-group {
            flex-direction: column;
            align-items: stretch;
        }
        
        .table-header {
            flex-direction: column;
            gap: 16px;
            align-items: stretch;
        }
        
        .Nexora Service Suite-modern-table {
            font-size: 14px;
        }
        
        .Nexora Service Suite-modern-table th,
        .Nexora Service Suite-modern-table td {
            padding: 12px 8px;
        }
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
        
        <div class="dashboard-topbar">
            <div class="breadcrumb">
                <i class="fas fa-home"></i> Dashboard / Serviceanfragen
            </div>
            <div class="topbar-right">
                <div class="search-box">
                    <i class="fas fa-search"></i>
                    <input type="text" id="service-request-search-input" placeholder="Suchen...">
                </div>
                <div class="user-menu">
                    <i class="fas fa-user-circle"></i>
                    <span>Admin</span>
                </div>
            </div>
        </div>
        

        

        
        <div class="Nexora Service Suite-bulk-actions" style="margin: 10px 0; display: none;">
            <button id="Nexora Service Suite-bulk-delete-requests" class="Nexora Service Suite-btn Nexora Service Suite-btn-secondary">
                <i class="fas fa-trash"></i>
                Ausgewählte löschen
            </button>
            <span id="Nexora Service Suite-selected-count" style="margin-left: 10px; color: #CBD5E0;"></span>
        </div>

        
        <div class="Nexora Service Suite-modern-table-container">
            <div class="table-header">
                <div class="table-info">
                    <span id="Nexora Service Suite-table-info">Lade Daten...</span>
                </div>
                <div class="table-actions">
                    <button id="Nexora Service Suite-add-request" class="Nexora Service Suite-btn Nexora Service Suite-btn-primary">
                        <i class="fas fa-plus"></i>
                        Neue Anfrage
                    </button>
                    <button id="Nexora Service Suite-refresh-requests" class="Nexora Service Suite-btn Nexora Service Suite-btn-secondary">
                        <i class="fas fa-sync-alt"></i>
                        Aktualisieren
                    </button>
                    <button id="Nexora Service Suite-delete-selected" class="Nexora Service Suite-btn Nexora Service Suite-btn-danger" style="display: none;">
                        <i class="fas fa-trash"></i>
                        Ausgewählte löschen
                    </button>
                </div>
            </div>

            <div class="Nexora Service Suite-table-wrapper">
                <table class="Nexora Service Suite-modern-table">
                    <thead>
                        <tr>
                            <th style="width: 30px;">
                                <input type="checkbox" id="Nexora Service Suite-select-all-requests" title="Alle auswählen">
                            </th>
                            <th class="sortable" data-sort="id">ID <span class="sort-arrow">↕</span></th>
                            <th class="sortable" data-sort="model">Modell <span class="sort-arrow">↕</span></th>
                            <th class="sortable" data-sort="display_name">Benutzer <span class="sort-arrow">↕</span></th>
                            <th>Status</th>
                            <th class="sortable" data-sort="device_type_display">Gerätetyp <span class="sort-arrow">↕</span></th>
                            <th class="sortable" data-sort="cost">Gesamtpreis <span class="sort-arrow">↕</span></th>
                            <th class="sortable" data-sort="created_at">Datum <span class="sort-arrow">↕</span></th>
                            <th>Aktionen</th>
                        </tr>
                    </thead>
                    <tbody id="Nexora Service Suite-request-list">
                        <tr>
                            <td colspan="9" class="loading-row">
                                <div class="loading-spinner"></div>
                                <span>Lade Anfragen...</span>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>

            
            <div class="Nexora Service Suite-modern-pagination">
                <button id="Nexora Service Suite-prev-page" class="Nexora Service Suite-btn Nexora Service Suite-btn-secondary" disabled>
                    <i class="fas fa-chevron-left"></i>
                    Vorherige
                </button>
                
                <div class="pagination-info">
                    <span id="Nexora Service Suite-page-info">Seite 1 von 1</span>
                </div>
                
                <button id="Nexora Service Suite-next-page" class="Nexora Service Suite-btn Nexora Service Suite-btn-secondary" disabled>
                    Nächste
                    <i class="fas fa-chevron-right"></i>
                </button>
            </div>
        </div>
    </div>

    
    <div id="new-request-modal" class="modal-overlay" style="display: none;">
        <div class="modal-content">
            <div class="modal-header">
                <h2>Neue Serviceanfrage erstellen</h2>
                <button type="button" class="modal-close" onclick="closeNewRequestModal()">&times;</button>
            </div>
            <div class="modal-body">
                <form id="new-request-form" class="Nexora Service Suite-form">
                    <?php wp_nonce_field('nexora_user_nonce', 'nonce'); ?>
                    
                    
                    
                    
                    <div class="form-section">
                        <h3>📱 Geräteinformationen</h3>
                        <div class="form-grid">
                            <div class="form-group">
                                <label for="modal_device_type_id">Gerätetyp *</label>
                                <select id="modal_device_type_id" name="device_type_id" class="form-control" required>
                                    <option value="">-- Bitte wählen --</option>
                                </select>
                            </div>
                            <div class="form-group">
                                <label for="modal_device_brand_id">Marke *</label>
                                <select id="modal_device_brand_id" name="device_brand_id" class="form-control" required disabled>
                                    <option value="">-- Bitte wählen --</option>
                                </select>
                            </div>
                            <div class="form-group">
                                <label for="modal_device_series_id">Serie</label>
                                <select id="modal_device_series_id" name="device_series_id" class="form-control" disabled>
                                    <option value="">-- Bitte wählen --</option>
                                </select>
                            </div>
                            <div class="form-group">
                                <label for="modal_device_model_id">Modell</label>
                                <select id="modal_device_model_id" name="device_model_id" class="form-control" disabled>
                                    <option value="">-- Bitte wählen --</option>
                                </select>
                            </div>
                            <div class="form-group">
                                <label for="modal_device_serial">Seriennummer</label>
                                <input type="text" id="modal_device_serial" name="device_serial" class="form-control">
                            </div>
                            <div class="form-group" style="grid-column: span 2;">
                                <label for="modal_device_description">Problembeschreibung</label>
                                <textarea id="modal_device_description" name="device_description" class="form-control" rows="4"></textarea>
                            </div>
                        </div>
                    </div>
                    
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" onclick="closeNewRequestModal()">Abbrechen</button>
                <button type="button" id="save-new-request-btn" class="btn btn-primary">Speichern</button>
            </div>
            
        </div>
    </div>

    
    <?php
    include __DIR__ . '/service-request-form/service-request-form-data.php';
    ?>
    
    
    <script src="<?php echo plugin_dir_url(dirname(__FILE__)) . 'assets/js/admin-dashboard.js'; ?>"></script>

<script>
jQuery(document).ready(function($) {
    const ajaxUrl = '<?php echo admin_url('admin-ajax.php'); ?>';
    const nonce = '<?php echo wp_create_nonce('nexora_nonce'); ?>';
    window.ajaxUrl = ajaxUrl;
    window.nonce = nonce;
    
    let currentPage = 1;
    let totalPages = 1;
    let currentSearch = '';
    let currentStatusFilter = '';
    let currentSortBy = 'id';
    let currentSortDir = 'asc';
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
                    window._nexora_statuses = response.data.statuses;
                    if (currentPage > 0) {
                        loadRequests(currentPage, currentSearch, currentStatusFilter, currentSortBy, currentSortDir);
                    }
                }
            },
            error: function(xhr, status, error) {
                console.error('Error loading statuses:', error);
                loadRequests(currentPage, currentSearch, currentStatusFilter, currentSortBy, currentSortDir);
            }
        });
    }
    loadStatuses();

    function loadRequests(page = 1, search = '', status = '', sortBy = currentSortBy, sortDir = currentSortDir) {
        $('#Nexora Service Suite-request-list').html(`
            <tr>
                <td colspan="9" class="loading-row">
                    <div class="loading-spinner"></div>
                    <span>Lade Anfragen...</span>
                </td>
            </tr>
        `);
        
        const requestData = {
            action: 'nexora_get_service_requests',
            page: page,
            per_page: 10,
            search: search,
            status_filter: status,
            order_by: sortBy,
            order_dir: sortDir,
            nonce: nonce
        };
        
        $.ajax({
            url: ajaxUrl,
            type: 'POST',
            data: requestData,
            success: function(response) {
                if (response.success) {
                    const requests = response.data.requests;
                    
                    currentPage = response.data.page;
                    totalPages = response.data.total_pages;
                    currentSearch = search;
                    currentStatusFilter = status;
                    currentSortBy = sortBy;
                    currentSortDir = sortDir;
                    $('#Nexora Service Suite-page-info').text(`Seite ${currentPage} von ${totalPages}`);
                    $('#Nexora Service Suite-prev-page').prop('disabled', currentPage <= 1);
                    $('#Nexora Service Suite-next-page').prop('disabled', currentPage >= totalPages);
                    $('#Nexora Service Suite-table-info').text(`${requests.length} Anfragen geladen`);

                    let html = '';
                    if (requests.length > 0) {
                        requests.forEach(request => {
                            html += `
                                <tr>
                                    <td>
                                        <input type="checkbox" class="Nexora Service Suite-request-checkbox" data-id="${request.id}" title="Anfrage auswählen">
                                    </td>
                                    <td>${request.id}</td>
                                    <td>${request.model || '-'}</td>
                                    <td>${request.display_name || '-'}</td>
                                    <td>
                                        <select class="status-dropdown-colored" data-request-id="${request.id}" data-current-status="${request.status_id}">
                                            ${window._nexora_statuses ? window._nexora_statuses.map(status => 
                                                `<option value="${status.id}" ${request.status_id == status.id ? 'selected' : ''} data-color="${status.color || '#0073aa'}">${status.title}</option>`
                                            ).join('') : 
                                            `<option value="" disabled>Status wird geladen...</option>`
                                            }
                                        </select>
                                    </td>
                                    <td>${request.device_info_formatted || '-'}</td>
                                    <td>${request.cost > 0 ? '€' + request.cost_formatted : '-'}</td>
                                    <td>${request.created_at || '-'}</td>
                                    <td class="actions-cell">
                                        <div class="actions-container">
                                            <button type="button" class="action-btn edit-btn" onclick="window.open('admin.php?page=nexora_service_request_form&id=${request.id}', '_blank')" title="Bearbeiten">
                                                <i class="fas fa-edit"></i>
                                            </button>
                                            <button type="button" class="action-btn delete-btn" data-id="${request.id}" title="Löschen">
                                                <i class="fas fa-trash"></i>
                                            </button>
                                            <button type="button" class="action-btn print-btn" title="Rechnung drucken" data-request-id="${request.id}">
                                                <i class="fas fa-print"></i>
                                            </button>
                                            <button type="button" class="action-btn log-btn" title="Log" data-request-id="${request.id}">
                                                <i class="fas fa-file-alt"></i>
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
                                    <h3 style="margin: 0 0 0.5rem 0;">Keine Anfragen gefunden</h3>
                                    <p style="margin: 0;">Versuchen Sie andere Suchkriterien.</p>
                                </td>
                            </tr>`;
                    }
                    $('#Nexora Service Suite-request-list').html(html);
                    $('.status-dropdown-colored').each(function() {
                        const $select = $(this);
                        const selectedOption = $select.find('option:selected');
                        const selectedColor = selectedOption.data('color') || '#0073aa';
                        
                        $select.css('background-color', selectedColor);
                        $select.css('color', 'white');
                        $select.css('font-weight', 'bold');
                        $select.css('text-shadow', '0 1px 2px rgba(0,0,0,0.2)');
                        });
                } else {
                        console.error('AJAX Error:', response);
                    $('#Nexora Service Suite-request-list').html(`
                        <tr>
                                <td colspan="9" style="text-align: center; padding: 3rem 1rem; color: #FF6B6B;">
                                <div style="font-size: 3rem; margin-bottom: 1rem;">⚠️</div>
                                <h3 style="margin: 0 0 0.5rem 0;">Fehler beim Laden der Daten</h3>
                                <p style="margin: 0;">Bitte versuchen Sie es erneut oder kontaktieren Sie den Administrator.</p>
                            </td>
                            </tr>`
                        );
                }
            },
            error: function(xhr, status, error) {
                    console.error('AJAX Error:', error);
                $('#Nexora Service Suite-request-list').html(`
                    <tr>
                            <td colspan="9" style="text-align: center; padding: 3rem 1rem; color: #FF6B6B;">
                            <div style="font-size: 3rem; margin-bottom: 1rem;">⚠️</div>
                                <h3 style="margin: 0 0 0.5rem 0;">Fehler bei der Verbindung zum Server</h3>
                                <p style="margin: 0;">Bitte versuchen Sie es erneut.</p>
                        </td>
                        </tr>`
                    );
            }
        });
    }
    $(document).on('change', '.status-dropdown-colored', function() {
        const select = $(this);
        const requestId = select.data('request-id');
        const newStatusId = select.val();
        const currentStatusId = select.data('current-status');
        if (newStatusId == currentStatusId) {
            return;
        }
        select.prop('disabled', true);
        const originalBackground = select.css('background-color');
        select.css('opacity', '0.6');
        
                    $.ajax({
                url: ajaxUrl,
                type: 'POST',
                data: {
                    action: 'nexora_update_request_status_only',
                    id: requestId,
                    status_id: newStatusId,
                    nonce: nonce
                },
            success: function(response) {
                if (response.success) {
                    select.data('current-status', newStatusId);
                    const selectedOption = select.find('option:selected');
                    const selectedColor = selectedOption.data('color') || '#0073aa';
                    
                    select.css('background-color', selectedColor);
                    select.css('color', 'white');
                    select.css('font-weight', 'bold');
                    select.css('text-shadow', '0 1px 2px rgba(0,0,0,0.2)');
                    showNotification('Status erfolgreich aktualisiert!', 'success');
                    localStorage.setItem('nexora_status_change', JSON.stringify({
                        request_id: requestId,
                        new_status_id: newStatusId,
                        timestamp: Date.now()
                    }));
                } else {
                    select.val(currentStatusId);
                    showNotification('Fehler beim Aktualisieren des Status: ' + (response.data || 'Unbekannter Fehler'), 'error');
                }
            },
            error: function(xhr, status, error) {
                console.error('Status update error:', error);
                select.val(currentStatusId);
                showNotification('Verbindungsfehler beim Aktualisieren des Status', 'error');
            },
            complete: function() {
                select.prop('disabled', false);
                select.css('opacity', '1');
            }
        });
    });
    $('#Nexora Service Suite-add-request').on('click', function() {
        openNewRequestModal();
    });

    $('#Nexora Service Suite-refresh-requests').on('click', function() {
        loadRequests(currentPage, currentSearch, currentStatusFilter, currentSortBy, currentSortDir);
    });
    $('#Nexora Service Suite-prev-page').on('click', function() {
        console.log('Prev clicked - currentPage:', currentPage, 'totalPages:', totalPages);
        if (currentPage > 1) {
            currentPage = currentPage - 1;
            console.log('Loading page:', currentPage);
            loadRequests(currentPage, currentSearch, currentStatusFilter, currentSortBy, currentSortDir);
        }
    });

    $('#Nexora Service Suite-next-page').on('click', function() {
        console.log('Next clicked - currentPage:', currentPage, 'totalPages:', totalPages);
        if (currentPage < totalPages) {
            currentPage = currentPage + 1;
            console.log('Loading page:', currentPage);
            loadRequests(currentPage, currentSearch, currentStatusFilter, currentSortBy, currentSortDir);
        }
    });
    $(document).on('click', '.sortable', function() {
        const $th = $(this);
        const sortBy = $th.data('sort');
        let sortDir = 'asc';
            
        if (currentSortBy === sortBy) {
            sortDir = currentSortDir === 'asc' ? 'desc' : 'asc';
        }

        currentSortBy = sortBy;
        currentSortDir = sortDir;
        currentPage = 1;
        loadRequests(1, currentSearch, currentStatusFilter, sortBy, sortDir);
    });
    $(document).on('click', '.log-btn', function(e) {
        e.preventDefault();
        const requestId = $(this).data('request-id');
        showLogPopup(requestId);
    });
    $(document).on('click', '.delete-btn', function(e) {
        e.preventDefault();
        const requestId = $(this).data('id');
        
        if (confirm('Sind Sie sicher, dass Sie diese Anfrage löschen möchten? Diese Aktion kann nicht rückgängig gemacht werden.')) {
            deleteRequest(requestId);
        }
    });
    $(document).on('click', '.print-btn', function(e) {
        e.preventDefault();
        
        const requestId = $(this).data('request-id');
        
        if (!requestId) {
            alert('Fehler: Keine Anfrage-ID gefunden');
            return;
        }
        const form = document.createElement('form');
        form.method = 'POST';
        form.action = ajaxUrl;
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
    });
    function showLogPopup(requestId) {
        const modalHtml = `
            <div id="log-modal" class="log-modal-overlay">
                <div class="log-modal-content">
                    <div class="log-modal-header">
                        <h3>📜 Log für Anfrage #${requestId}</h3>
                        <button class="log-modal-close">&times;</button>
            </div>
                    <div class="log-modal-body">
                        <div class="log-loading">Lade Logs...</div>
                    </div>
                </div>
            </div>
        `;
        $('body').append(modalHtml);
        $.ajax({
            url: ajaxUrl,
            type: 'POST',
            data: {
                action: 'nexora_get_request_logs',
                request_id: requestId,
                nonce: nonce
            },
            success: function(response) {
                console.log('Log response:', response);
                
                if (response.success && response.data && response.data.logs) {
                    const logs = response.data.logs;
                    let logsHtml = '';
                    
                    if (logs.length > 0) {
                        logs.forEach(log => {
                            logsHtml += `
                                <div class="log-entry">
                                    <div class="log-timestamp">
                                        <i class="fas fa-clock"></i>
                                        ${log.created_at || 'Unbekannt'}
                                            </div>
                                    <div class="log-message">
                                        <i class="fas fa-info-circle"></i>
                                        ${log.description || 'Keine Beschreibung'}
                                        </div>
                                    <div class="log-user">
                                        <i class="fas fa-user"></i>
                                        ${log.user_name || 'System'}
                                    </div>
                                    ${log.old_value ? `
                                                    <div class="log-old-value">
                                            <i class="fas fa-arrow-left"></i>
                                            <strong>Vorher:</strong> ${log.old_value}
                                                    </div>
                                    ` : ''}
                                    ${log.new_value ? `
                                                    <div class="log-new-value">
                                            <i class="fas fa-arrow-right"></i>
                                            <strong>Nachher:</strong> ${log.new_value}
                                        </div>
                                    ` : ''}
                                </div>
                            `;
                        });
                    } else {
                        logsHtml = '<div class="no-logs">Keine Logs gefunden.</div>';
                    }
                    
                    $('.log-modal-body').html(logsHtml);
                } else {
                    $('.log-modal-body').html('<div class="log-error">Fehler beim Laden der Logs: ' + (response.data?.message || 'Unbekannter Fehler') + '</div>');
                }
            },
            error: function(xhr, status, error) {
                console.error('Log AJAX error:', error);
                $('.log-modal-body').html('<div class="log-error">Verbindungsfehler beim Laden der Logs. Bitte versuchen Sie es erneut.</div>');
            }
        });
        $(document).on('click', '.log-modal-close, .log-modal-overlay', function(e) {
            if (e.target === this) {
                $('#log-modal').remove();
            }
        });
        $(document).on('keydown', function(e) {
            if (e.key === 'Escape') {
                $('#log-modal').remove();
            }
        });
    }
    function deleteRequest(requestId) {
        const $deleteBtn = $(`.delete-btn[data-id="${requestId}"]`);
        const originalText = $deleteBtn.html();
        $deleteBtn.html('🗑️⏳').prop('disabled', true);
        
            $.ajax({
                url: ajaxUrl,
                type: 'POST',
                data: {
                action: 'nexora_delete_service_request',
                request_id: requestId,
                    nonce: nonce
                },
                success: function(response) {
                    if (response.success) {
                    showNotification('Anfrage erfolgreich gelöscht!', 'success');
                    $(`.delete-btn[data-id="${requestId}"]`).closest('tr').fadeOut(300, function() {
                        $(this).remove();
                        loadRequests(currentPage, currentSearch, currentStatusFilter, currentSortBy, currentSortDir);
                    });
                    } else {
                    showNotification('Fehler beim Löschen der Anfrage: ' + (response.data?.message || 'Unbekannter Fehler'), 'error');
                    $deleteBtn.html(originalText).prop('disabled', false);
                    }
                },
                error: function(xhr, status, error) {
                console.error('Delete request error:', error);
                showNotification('Verbindungsfehler beim Löschen der Anfrage', 'error');
                $deleteBtn.html(originalText).prop('disabled', false);
                }
            });
        }
    function showNotification(message, type = 'info') {
        const notification = $(`
            <div class="notification notification-${type}">
                <div class="notification-content">
                    <span class="notification-message">${message}</span>
                    <button class="notification-close">&times;</button>
                </div>
            </div>
        `);
        
        $('body').append(notification);
        setTimeout(() => {
            notification.fadeOut(300, function() {
                $(this).remove();
            });
        }, 5000);
        notification.on('click', '.notification-close', function() {
            notification.fadeOut(300, function() {
                $(this).remove();
            });
        });
    }
    function printInvoice(requestId) {
        const $printBtn = $(`.print-btn[data-request-id="${requestId}"]`);
        const originalText = $printBtn.html();
        $printBtn.html('🖨️⏳').prop('disabled', true);
        
        try {
            const pluginUrl = '<?php echo plugin_dir_url(dirname(__FILE__)); ?>';
            const invoiceUrl = `${pluginUrl}templates/invoice-template.php?request_id=${requestId}`;
            
            console.log('Opening invoice:', invoiceUrl);
            
            const newWindow = window.open(invoiceUrl, '_blank', 'width=1000,height=800,scrollbars=yes,resizable=yes');
            
            if (newWindow) {
                showNotification('Rechnung wird geöffnet...', 'success');
                newWindow.focus();
                setTimeout(() => {
                    newWindow.print();
                }, 2000);
            } else {
                throw new Error('Popup blocked');
            }
        } catch (error) {
            console.error('Error opening invoice:', error);
            const fullUrl = `${window.location.origin}${pluginUrl}templates/invoice-template.php?request_id=${requestId}`;
            window.open(fullUrl, '_blank');
            
            showNotification('Popup wurde blockiert. Bitte erlauben Sie Popups für diese Seite.', 'error');
        }
        setTimeout(() => {
            $printBtn.html(originalText).prop('disabled', false);
        }, 1000);
    }
    function loadNotificationCounts() {
        $.post(ajaxUrl, {action: 'get_new_requests_count', nonce: nonce}, function(resp){
            if(resp.success && resp.data.count > 0) {
                $('#nav-anfragen-badge').text(resp.data.count).show();
            } else {
                $('#nav-anfragen-badge').hide();
            }
        });
        $.post(ajaxUrl, {action: 'nexora_get_new_users_count', nonce: nonce}, function(resp){
            if(resp.success && resp.data.count > 0) {
                $('#nav-benutzer-badge').text(resp.data.count).show();
            } else {
                $('#nav-benutzer-badge').hide();
            }
        });
        $.post(ajaxUrl, {action: 'nexora_get_admin_notifications', nonce: nonce}, function(resp){
            if(resp.success && resp.data.length > 0) {
                $('#nav-nachrichten-badge').text(resp.data.length).show();
            } else {
                $('#nav-nachrichten-badge').hide();
            }
        });
    }
    loadNotificationCounts();
    setInterval(loadNotificationCounts, 60000);
    $('#service-request-search-input').on('input', function() {
        const searchTerm = $(this).val().trim();
        currentSearch = searchTerm;
        currentPage = 1;
        loadRequests(currentPage, searchTerm, currentStatusFilter, currentSortBy, currentSortDir);
    });
    $(document).on('change', '#Nexora Service Suite-select-all-requests', function() {
        const isChecked = $(this).is(':checked');
        $('.Nexora Service Suite-request-checkbox').prop('checked', isChecked);
        updateDeleteSelectedButton();
    });
    $(document).on('change', '.Nexora Service Suite-request-checkbox', function() {
        updateDeleteSelectedButton();
        updateSelectAllCheckbox();
    });
    $(document).on('click', '#Nexora Service Suite-delete-selected', function() {
        const selectedIds = getSelectedRequestIds();
        
        if (selectedIds.length === 0) {
            showNotification('Keine Anfragen ausgewählt', 'warning');
            return;
        }
        
        const confirmMessage = `Sind Sie sicher, dass Sie ${selectedIds.length} ausgewählte Anfrage(n) löschen möchten? Diese Aktion kann nicht rückgängig gemacht werden.`;
        
        if (confirm(confirmMessage)) {
            deleteSelectedRequests(selectedIds);
        }
    });
    function updateDeleteSelectedButton() {
        const selectedCount = $('.Nexora Service Suite-request-checkbox:checked').length;
        const $deleteBtn = $('#Nexora Service Suite-delete-selected');
        
        if (selectedCount > 0) {
            $deleteBtn.show().text(`Ausgewählte löschen (${selectedCount})`);
        } else {
            $deleteBtn.hide();
        }
    }
    function updateSelectAllCheckbox() {
        const totalCheckboxes = $('.Nexora Service Suite-request-checkbox').length;
        const checkedCheckboxes = $('.Nexora Service Suite-request-checkbox:checked').length;
        const $selectAllCheckbox = $('#Nexora Service Suite-select-all-requests');
        
        if (checkedCheckboxes === 0) {
            $selectAllCheckbox.prop('indeterminate', false).prop('checked', false);
        } else if (checkedCheckboxes === totalCheckboxes) {
            $selectAllCheckbox.prop('indeterminate', false).prop('checked', true);
        } else {
            $selectAllCheckbox.prop('indeterminate', true).prop('checked', false);
        }
    }
    function getSelectedRequestIds() {
        const selectedIds = [];
        $('.Nexora Service Suite-request-checkbox:checked').each(function() {
            selectedIds.push($(this).data('id'));
        });
        return selectedIds;
    }
    function deleteSelectedRequests(requestIds) {
        const $deleteBtn = $('#Nexora Service Suite-delete-selected');
        const originalText = $deleteBtn.text();
        $deleteBtn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin"></i> Lösche...');
        let deletedCount = 0;
        let errorCount = 0;
        
        const deletePromises = requestIds.map(requestId => {
            return new Promise((resolve) => {
                $.ajax({
                    url: ajaxUrl,
                    type: 'POST',
                    data: {
                        action: 'nexora_delete_service_request',
                        request_id: requestId,
                        nonce: nonce
                    },
                    success: function(response) {
                        if (response.success) {
                            deletedCount++;
                            $(`.Nexora Service Suite-request-checkbox[data-id="${requestId}"]`).closest('tr').fadeOut(300, function() {
                                $(this).remove();
                            });
                        } else {
                            errorCount++;
                        }
                        resolve();
                    },
                    error: function() {
                        errorCount++;
                        resolve();
                    }
                });
            });
        });
        Promise.all(deletePromises).then(() => {
            $('#Nexora Service Suite-select-all-requests').prop('checked', false).prop('indeterminate', false);
            $('#Nexora Service Suite-delete-selected').hide();
            if (errorCount === 0) {
                showNotification(`${deletedCount} Anfrage(n) erfolgreich gelöscht!`, 'success');
            } else if (deletedCount === 0) {
                showNotification(`Fehler beim Löschen aller Anfragen`, 'error');
            } else {
                showNotification(`${deletedCount} Anfrage(n) gelöscht, ${errorCount} Fehler`, 'warning');
            }
            loadRequests(currentPage, currentSearch, currentStatusFilter, currentSortBy, currentSortDir);
        });
    }
    loadRequests();
    function openNewRequestModal() {
        $('#new-request-modal').show();
        loadModalData();
    }
    
    function closeNewRequestModal() {
        $('#new-request-modal').hide();
        $('#new-request-form')[0].reset();
    }
    
    
    function loadModalData() {
        $.post(ajaxUrl, {
            action: 'nexora_get_device_types',
            nonce: nonce
        }, function(response) {
            if (response.success) {
                let html = '<option value="">-- Bitte wählen --</option>';
                response.data.forEach(function(type) {
                    html += `<option value="${type.id}">${type.name}</option>`;
                });
                $('#modal_device_type_id').html(html);
            }
        });
        $.post(ajaxUrl, {
            action: 'nexora_get_services',
            nonce: nonce
        }, function(response) {
            if (response.success) {
                let html = '<option value="">-- Bitte wählen --</option>';
                response.data.forEach(function(service) {
                    html += `<option value="${service.id}">${service.title}</option>`;
                });
                $('#modal_service_id').html(html);
            }
        });
        $.post(ajaxUrl, {
            action: 'nexora_get_users',
            nonce: nonce
        }, function(response) {
            if (response.success) {
                let html = '<option value="">-- Bitte wählen --</option>';
                response.data.forEach(function(user) {
                    html += `<option value="${user.ID}">${user.display_name}</option>`;
                });
                $('#modal_assigned_to').html(html);
            }
        });
    }
    $(document).on('change', '#modal_device_type_id', function() {
        const typeId = $(this).val();
        if (typeId) {
            loadModalDeviceBrands(typeId);
        } else {
            $('#modal_device_brand_id').html('<option value="">-- Bitte wählen --</option>').prop('disabled', true);
            $('#modal_device_series_id').html('<option value="">-- Bitte wählen --</option>').prop('disabled', true);
            $('#modal_device_model_id').html('<option value="">-- Bitte wählen --</option>').prop('disabled', true);
        }
    });
    $(document).on('change', '#modal_device_brand_id', function() {
        const brandId = $(this).val();
        if (brandId) {
            loadModalDeviceSeries(brandId);
        } else {
            $('#modal_device_series_id').html('<option value="">-- Bitte wählen --</option>').prop('disabled', true);
            $('#modal_device_model_id').html('<option value="">-- Bitte wählen --</option>').prop('disabled', true);
        }
    });
    $(document).on('change', '#modal_device_series_id', function() {
        const seriesId = $(this).val();
        if (seriesId) {
            loadModalDeviceModels(seriesId);
        } else {
            $('#modal_device_model_id').html('<option value="">-- Bitte wählen --</option>').prop('disabled', true);
        }
    });
    
    function loadModalDeviceBrands(typeId) {
        $.post(ajaxUrl, {
            action: 'nexora_get_device_brands',
            nonce: nonce,
            type_id: typeId
        }, function(response) {
            let html = '<option value="">-- Bitte wählen --</option>';
            if (response.success && response.data.length) {
                response.data.forEach(function(brand) {
                    html += `<option value="${brand.id}">${brand.name}</option>`;
                });
            }
            $('#modal_device_brand_id').html(html).prop('disabled', false);
            $('#modal_device_series_id').html('<option value="">-- Bitte wählen --</option>').prop('disabled', true);
            $('#modal_device_model_id').html('<option value="">-- Bitte wählen --</option>').prop('disabled', true);
        });
    }
    
    function loadModalDeviceSeries(brandId) {
        $.post(ajaxUrl, {
            action: 'nexora_get_device_series',
            nonce: nonce,
            brand_id: brandId
        }, function(response) {
            let html = '<option value="">-- Bitte wählen --</option>';
            if (response.success && response.data.length) {
                response.data.forEach(function(series) {
                    html += `<option value="${series.id}">${series.name}</option>`;
                });
            }
            $('#modal_device_series_id').html(html).prop('disabled', false);
            $('#modal_device_model_id').html('<option value="">-- Bitte wählen --</option>').prop('disabled', true);
        });
    }
    
    function loadModalDeviceModels(seriesId) {
        $.post(ajaxUrl, {
            action: 'nexora_get_device_models',
            nonce: nonce,
            series_id: seriesId
        }, function(response) {
            let html = '<option value="">-- Bitte wählen --</option>';
            if (response.success && response.data.length) {
                response.data.forEach(function(model) {
                    html += `<option value="${model.id}">${model.name}</option>`;
                });
            }
            $('#modal_device_model_id').html(html).prop('disabled', false);
        });
    }
    
    function saveNewRequest() {
        const requiredFields = ['device_type_id', 'device_brand_id'];
        let isValid = true;
        
        requiredFields.forEach(function(field) {
            const value = $(`#modal_${field}`).val();
            if (!value) {
                isValid = false;
                $(`#modal_${field}`).addClass('error');
            } else {
                $(`#modal_${field}`).removeClass('error');
            }
        });
        
        if (!isValid) {
            showNotification('Bitte füllen Sie alle Pflichtfelder aus', 'error');
            return;
        }
        const formData = {
            action: 'nexora_create_service_request_from_admin',
            nonce: nonce,
            customer_name: '<?php echo wp_get_current_user()->display_name; ?>',
            customer_email: '<?php echo wp_get_current_user()->user_email; ?>',
            customer_phone: '',
            customer_number: '',
            salutation: 'Herr',
            customer_type: 'business',
            company_name: '',
            street: '',
            postal_code: '',
            city: '',
            country: 'DE',
            vat_id: '',
            device_type_id: $('#modal_device_type_id').val(),
            device_brand_id: $('#modal_device_brand_id').val(),
            device_series_id: $('#modal_device_series_id').val(),
            device_model_id: $('#modal_device_model_id').val(),
            device_serial: $('#modal_device_serial').val(),
            device_description: $('#modal_device_description').val()
        };
        const saveBtn = $('.modal-footer .btn-primary');
        const originalText = saveBtn.html();
        saveBtn.html('⏳ Speichern...').prop('disabled', true);
        $.post(ajaxUrl, formData, function(response) {
            if (response.success) {
                showNotification('Serviceanfrage erfolgreich erstellt!', 'success');
                closeNewRequestModal();
                loadRequests(currentPage, currentSearch, currentStatusFilter, currentSortBy, currentSortDir);
            } else {
                showNotification('Fehler beim Erstellen der Anfrage: ' + (response.data || 'Unbekannter Fehler'), 'error');
            }
        }).fail(function(xhr, status, error) {
            showNotification('Verbindungsfehler beim Erstellen der Anfrage: ' + error, 'error');
        }).always(function() {
            saveBtn.html(originalText).prop('disabled', false);
        });
    }
    $(document).on('click', '.modal-overlay', function(e) {
        if (e.target === this) {
            closeNewRequestModal();
        }
    });
    $(document).on('keydown', function(e) {
        if (e.key === 'Escape' && $('#new-request-modal').is(':visible')) {
            closeNewRequestModal();
        }
    });
    $(document).on('click', '#save-new-request-btn', function() {
        saveNewRequest();
    });
});
</script>
</body>
</html>
