jQuery(document).ready(function($) {
    'use strict';
    const loadCriticalData = function() {
        $.ajax({
            url: nexora_notifications.ajax_url,
            type: 'POST',
            data: {
                action: 'get_new_requests_count',
                nonce: nexora_notifications.nonce
            },
            success: function(response) {
                if (response.success) {
                    updateMenuBadge(response.data.count);
                }
            }
        });
    };
    loadCriticalData();
    $(document).on('change', '.request-status-select', function() {
        const select = $(this);
        const requestId = select.data('request-id');
        const newStatusId = select.val();
        const statusSelector = select.closest('.status-selector');
        const statusBadge = statusSelector.find('.status-badge');
        statusSelector.addClass('status-updating');
        select.prop('disabled', true);
        
        $.ajax({
            url: nexora_notifications.ajax_url,
            type: 'POST',
            data: {
                action: 'nexora_update_request_status',
                request_id: requestId,
                status_id: newStatusId,
                nonce: nexora_notifications.nonce
            },
            success: function(response) {
                if (response.success) {
                    updateStatusBadge(statusBadge, newStatusId, select);
                    updateMenuBadge(response.data.new_count);
                    showNotification(response.data.message, 'success');
                    statusSelector.addClass('status-updated');
                    setTimeout(function() {
                        statusSelector.removeClass('status-updated');
                    }, 500);
                } else {
                    showNotification(response.data, 'error');
                    select.val(select.find('option[selected]').val());
                }
            },
            error: function() {
                showNotification('Fehler bei der Verbindung zum Server', 'error');
                select.val(select.find('option[selected]').val());
            },
            complete: function() {
                statusSelector.removeClass('status-updating');
                select.prop('disabled', false);
            }
        });
    });
    function updateStatusBadge(badge, statusId, select) {
        const newStatusName = select.find('option:selected').text();
        badge.removeClass().addClass('status-badge status-' + statusId);
        badge.text(newStatusName);
        select.find('option').removeAttr('selected');
        select.find('option:selected').attr('selected', 'selected');
    }
    function updateMenuBadge(newCount) {
        const menuItem = $('a[href*="Nexora Service Suite-service-request"]');
        const existingBadge = menuItem.find('.awaiting-mod');
        
        if (newCount > 0) {
            if (existingBadge.length > 0) {
                existingBadge.text(newCount);
            } else {
                menuItem.append('<span class="awaiting-mod">' + newCount + '</span>');
            }
        } else {
            existingBadge.remove();
        }
    }
    function showNotification(message, type) {
        $('.Nexora Service Suite-notification').remove();
        
        const notification = $('<div class="Nexora Service Suite-notification Nexora Service Suite-notification-' + type + '">' + message + '</div>');
        $('body').append(notification);
        notification.fadeIn(300);
        setTimeout(function() {
            notification.fadeOut(300, function() {
                $(this).remove();
            });
        }, 3000);
    }
    setInterval(function() {
        $.ajax({
            url: nexora_notifications.ajax_url,
            type: 'POST',
            data: {
                action: 'get_new_requests_count',
                nonce: nexora_notifications.nonce
            },
            success: function(response) {
                if (response.success) {
                    updateMenuBadge(response.data.count);
                }
            }
        });
    }, 60000);
    $('<style>')
        .prop('type', 'text/css')
        .html(`
            .Nexora Service Suite-notification {
                position: fixed;
                top: 32px;
                right: 20px;
                padding: 12px 20px;
                border-radius: 8px;
                color: white;
                font-weight: 500;
                z-index: 999999;
                box-shadow: 0 4px 20px rgba(0, 0, 0, 0.3);
                display: none;
                max-width: 300px;
                word-wrap: break-word;
            }
            
            .Nexora Service Suite-notification-success {
                background: #27ae60;
                border-left: 4px solid #2ecc71;
            }
            
            .Nexora Service Suite-notification-error {
                background: #e74c3c;
                border-left: 4px solid #c0392b;
            }
            
            .Nexora Service Suite-notification-warning {
                background: #f39c12;
                border-left: 4px solid #e67e22;
            }
            
            .Nexora Service Suite-notification-info {
                background: #3498db;
                border-left: 4px solid #2980b9;
            }
            
            
            .status-selector {
                transition: all 0.3s ease;
            }
            
            .request-status-select {
                transition: all 0.3s ease;
            }
            
            .request-status-select:focus {
                border-color: #667eea;
                box-shadow: 0 0 0 2px rgba(102, 126, 234, 0.2);
            }
            
            .status-badge {
                transition: all 0.3s ease;
            }
            
            
            .status-selector:hover .request-status-select {
                border-color: #667eea;
            }
            
            
            .request-status-select:disabled {
                background: #f8f9fa;
                color: #6c757d;
                cursor: not-allowed;
            }
            
            
            .status-updating .request-status-select {
                background: #f8f9fa;
                color: #6c757d;
            }
            
            
            .awaiting-mod {
                animation: badgePulse 2s infinite;
            }
            
            @keyframes badgePulse {
                0% { transform: scale(1); }
                50% { transform: scale(1.1); }
                100% { transform: scale(1); }
            }
            
            
            @media (max-width: 768px) {
                .Nexora Service Suite-notification {
                    top: 46px;
                    right: 10px;
                    left: 10px;
                    max-width: none;
                }
                
                .request-status-select {
                    font-size: 14px;
                    padding: 8px 10px;
                }
                
                .status-badge {
                    font-size: 10px;
                    padding: 3px 6px;
                }
            }
        `)
        .appendTo('head');
    $('.status-selector').each(function() {
        const selector = $(this);
        const tooltip = $('<div class="status-tooltip">Klicken Sie zum Ändern des Status</div>');
        
        selector.append(tooltip);
        
        selector.on('mouseenter', function() {
            tooltip.fadeIn(200);
        }).on('mouseleave', function() {
            tooltip.fadeOut(200);
        });
    });
    $('<style>')
        .prop('type', 'text/css')
        .html(`
            .status-tooltip {
                position: absolute;
                top: -30px;
                left: 50%;
                transform: translateX(-50%);
                background: #333;
                color: white;
                padding: 4px 8px;
                border-radius: 4px;
                font-size: 11px;
                white-space: nowrap;
                z-index: 1000;
                display: none;
                pointer-events: none;
            }
            
            .status-tooltip::after {
                content: '';
                position: absolute;
                top: 100%;
                left: 50%;
                transform: translateX(-50%);
                border: 4px solid transparent;
                border-top-color: #333;
            }
        `)
        .appendTo('head');
    $(document).on('keydown', function(e) {
        if (e.ctrlKey && e.shiftKey && e.keyCode === 82) {
            e.preventDefault();
            $.ajax({
                url: nexora_notifications.ajax_url,
                type: 'POST',
                data: {
                    action: 'get_new_requests_count',
                    nonce: nexora_notifications.nonce
                },
                success: function(response) {
                    if (response.success) {
                        updateMenuBadge(response.data.count);
                        showNotification('Zähler aktualisiert', 'info');
                    }
                }
            });
        }
    });
    if ($('.wp-list-table').length) {
        const refreshButton = $('<button class="button refresh-counts" title="Zähler aktualisieren (Ctrl+Shift+R)">🔄</button>');
        const debugButton = $('<button class="button debug-notifications" title="Debug-Informationen">🐛</button>');
        
        $('.tablenav.top').prepend(debugButton);
        $('.tablenav.top').prepend(refreshButton);
        
        refreshButton.on('click', function() {
            $.ajax({
                url: nexora_notifications.ajax_url,
                type: 'POST',
                data: {
                    action: 'get_new_requests_count',
                    nonce: nexora_notifications.nonce
                },
                success: function(response) {
                    if (response.success) {
                        updateMenuBadge(response.data.count);
                        showNotification('Zähler aktualisiert', 'info');
                    }
                }
            });
        });
        
        debugButton.on('click', function() {
            $.ajax({
                url: nexora_notifications.ajax_url,
                type: 'POST',
                data: {
                    action: 'debug_notifications',
                    nonce: nexora_notifications.nonce
                },
                success: function(response) {
                    if (response.success) {
                        const debugInfo = response.data;
                        let debugText = `🔍 Debug-Informationen:\n`;
                        debugText += `📊 Gesamte Anfragen: ${debugInfo.total_requests}\n`;
                        debugText += `🆕 Neue Anfragen: ${debugInfo.new_requests}\n`;
                        debugText += `🆔 Neue Status-ID: ${debugInfo.new_status_id}\n`;
                        debugText += `❓ Anfragen ohne Status: ${debugInfo.requests_without_status}\n`;
                        debugText += `📋 Status: ${debugInfo.statuses.map(s => `${s.id}:${s.title}`).join(', ')}`;
                        
                        alert(debugText);
                    }
                }
            });
        });
    }
}); 