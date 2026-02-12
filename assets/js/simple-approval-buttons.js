

(function($) {
    'use strict';
    window.showNotification = function(message, type = 'info') {
        const notificationClass = `notification-${type}`;
        const icon = type === 'success' ? '✅' : type === 'error' ? '❌' : 'ℹ️';
        
        const $notification = $(`
            <div class="Nexora Service Suite-notification ${notificationClass}">
                <span class="notification-icon">${icon}</span>
                <span class="notification-message">${message}</span>
                <button class="notification-close">×</button>
            </div>
        `);
        $('.Nexora Service Suite-notification').remove();
        $('body').append($notification);
        setTimeout(() => {
            $notification.addClass('show');
        }, 100);
        setTimeout(() => {
            $notification.removeClass('show');
            setTimeout(() => {
                $notification.remove();
            }, 300);
        }, 5000);
        $notification.find('.notification-close').on('click', function() {
            $notification.removeClass('show');
            setTimeout(() => {
                $notification.remove();
            }, 300);
        });
    };
    window.approveService = function(requestId, tableType = 'complete_service_requests') {
        console.log('approveService called with:', requestId, tableType);
        const formData = new FormData();
        formData.append('action', 'nexora_approve_service');
        formData.append('request_id', requestId);
        formData.append('customer_note', '');
        formData.append('table_type', tableType);
        formData.append('nonce', nexora_ajax.nonce);
        
        $.ajax({
            url: nexora_ajax.ajax_url,
            type: 'POST',
            data: formData,
            processData: false,
            contentType: false,
            success: function(response) {
                console.log('Approve response:', response);
                if (response.success) {
                    showNotification('Service erfolgreich genehmigt!', 'success');
                    location.reload();
                } else {
                    showNotification('Fehler: ' + response.data, 'error');
                }
            },
            error: function(xhr, status, error) {
                console.error('Approve error:', error);
                showNotification('Fehler beim Genehmigen des Services: ' + error, 'error');
            }
        });
    };

    window.rejectService = function(requestId, tableType = 'complete_service_requests') {
        console.log('rejectService called with:', requestId, tableType);
        const formData = new FormData();
        formData.append('action', 'nexora_reject_service');
        formData.append('request_id', requestId);
        formData.append('customer_note', '');
        formData.append('table_type', tableType);
        formData.append('nonce', nexora_ajax.nonce);
        
        $.ajax({
            url: nexora_ajax.ajax_url,
            type: 'POST',
            data: formData,
            processData: false,
            contentType: false,
            success: function(response) {
                console.log('Reject response:', response);
                if (response.success) {
                    showNotification('Service erfolgreich abgelehnt!', 'success');
                    location.reload();
                } else {
                    showNotification('Fehler: ' + response.data, 'error');
                }
            },
            error: function(xhr, status, error) {
                console.error('Reject error:', error);
                showNotification('Fehler beim Ablehnen des Services: ' + error, 'error');
            }
        });
    };
    $(document).ready(function() {
        console.log('Simple Approval Buttons initialized');
        if (typeof nexora_ajax === 'undefined') {
            console.error('nexora_ajax object not found');
            return;
        }
        
        console.log('AJAX URL:', nexora_ajax.ajax_url);
        console.log('Nonce:', nexora_ajax.nonce);
        if (typeof window.approveService === 'function') {
            console.log('✅ approveService function is available');
        } else {
            console.error('❌ approveService function not found');
        }
        
        if (typeof window.rejectService === 'function') {
            console.log('✅ rejectService function is available');
        } else {
            console.error('❌ rejectService function not found');
        }
    });

})(jQuery);
