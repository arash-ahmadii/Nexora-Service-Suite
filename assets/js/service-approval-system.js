

(function($) {
    'use strict';
    class ServiceApprovalSystem {
        constructor() {
            this.init();
        }
        
        init() {
            this.bindEvents();
            this.initializeServiceRows();
        }
        
        bindEvents() {
            $(document).on('click', '.send-approval-btn', this.handleSendApproval.bind(this));
            $(document).on('click', '.Nexora Service Suite-modal-overlay', this.closeModal.bind(this));
            $(document).on('keydown', this.handleKeydown.bind(this));
            $(document).on('click', '.service-status-indicator', this.showStatusDetails.bind(this));
        }
        
        
        handleSendApproval(e) {
            e.preventDefault();
            
            const $btn = $(e.currentTarget);
            const requestId = $btn.data('request-id');
            const serviceId = $btn.data('service-id');
            const serviceData = $btn.data('service');
            
            if (!requestId || !serviceId) {
                console.error('Missing request or service ID');
                return;
            }
            
            this.openApprovalModal(requestId, serviceId, serviceData);
        }
        
        
        openApprovalModal(requestId, serviceId, serviceData) {
            $('#approval-request-id').val(requestId);
            $('#approval-service-id').val(serviceId);
            $('#approval-service-title').text(serviceData.title || 'N/A');
            $('#approval-service-cost').text('€' + (serviceData.cost || '0.00'));
            $('#approval-service-quantity').text(serviceData.quantity || '1');
            $('#approval-service-description').text(serviceData.description || 'Keine Beschreibung verfügbar');
            $('#approval-admin-note').val('');
            $('#service-approval-modal').show();
            $('body').addClass('modal-open');
            setTimeout(() => {
                $('#approval-admin-note').focus();
            }, 100);
        }
        
        
        closeModal() {
            $('#service-approval-modal').hide();
            $('body').removeClass('modal-open');
        }
        
        
        handleKeydown(e) {
            if (e.key === 'Escape') {
                this.closeModal();
            }
        }
        
        
        sendServiceForApproval() {
            const requestId = $('#approval-request-id').val();
            const serviceId = $('#approval-service-id').val();
            const adminNote = $('#approval-admin-note').val();
            
            if (!requestId || !serviceId) {
                alert('Fehler: Ungültige Service-Daten');
                return;
            }
            if (typeof nexora_ajax === 'undefined' && typeof nexora_chat_ajax === 'undefined') {
                alert('Fehler: AJAX-Konfiguration nicht gefunden');
                return;
            }
            
            const $submitBtn = $('.Nexora Service Suite-btn-primary');
            $submitBtn.addClass('loading').prop('disabled', true);
            const formData = new FormData();
            formData.append('action', 'nexora_send_service_approval');
            formData.append('request_id', requestId);
            formData.append('service_id', serviceId);
            formData.append('admin_note', adminNote);
            const nonce = (nexora_ajax && nexora_ajax.nonce) || (nexora_chat_ajax && nexora_chat_ajax.nonce);
            if (!nonce) {
                alert('Fehler: Sicherheitstoken nicht gefunden');
                return;
            }
            formData.append('nonce', nonce);
            const ajaxUrl = (nexora_ajax && nexora_ajax.ajax_url) || (nexora_chat_ajax && nexora_chat_ajax.ajax_url);
            if (!ajaxUrl) {
                alert('Fehler: AJAX-URL nicht gefunden');
                return;
            }
            
            $.ajax({
                url: ajaxUrl,
                type: 'POST',
                data: formData,
                processData: false,
                contentType: false,
                success: (response) => {
                    if (response.success) {
                        this.showNotification('Service erfolgreich zur Freigabe gesendet!', 'success');
                        this.closeModal();
                        this.updateServiceRowStatus(requestId, serviceId, 'pending');
                        if (typeof refreshServicesList === 'function') {
                            refreshServicesList();
                        } else {
                            location.reload();
                        }
                    } else {
                        this.showNotification('Fehler: ' + response.data, 'error');
                    }
                },
                error: (xhr, status, error) => {
                    this.showNotification('Fehler beim Senden des Services zur Freigabe.', 'error');
                },
                complete: () => {
                    $submitBtn.removeClass('loading').prop('disabled', false);
                }
            });
        }
        
        
                 updateServiceRowStatus(requestId, serviceId, status) {
             const $serviceRow = $(`.service-row[data-request-id="${requestId}"][data-service-id="${serviceId}"]`);
             
             if ($serviceRow.length) {
                 this.updateRowBackgroundColor($serviceRow, status);
                 const $approvalBtn = $serviceRow.find('.send-approval-btn');
                 
                 switch (status) {
                     case 'pending':
                         $approvalBtn.prop('disabled', true)
                                    .html('<i class="fas fa-clock"></i> Wartend auf Freigabe')
                                    .removeClass('send-approval-btn')
                                    .addClass('btn btn-secondary');
                         break;
                     case 'approved':
                         $approvalBtn.prop('disabled', true)
                                    .html('<i class="fas fa-check"></i> Genehmigt')
                                    .removeClass('send-approval-btn')
                                    .addClass('btn btn-success');
                         break;
                     case 'rejected':
                         $approvalBtn.prop('disabled', false)
                                    .html('<i class="fas fa-redo"></i> Erneut senden')
                                    .removeClass('btn btn-secondary btn-success')
                                    .addClass('send-approval-btn');
                         break;
                     case 'none':
                     default:
                         $approvalBtn.prop('disabled', false)
                                    .html('📤 Freigabe')
                                    .removeClass('btn btn-secondary btn-success')
                                    .addClass('send-approval-btn');
                         break;
                 }
             }
         }
        
        
                 updateRowBackgroundColor($row, status) {
             $row.removeClass('status-pending status-approved status-rejected status-none');
             $row.addClass(`status-${status}`);
         }
        
        
        initializeServiceRows() {
            $('.service-row').each((index, row) => {
                const $row = $(row);
                const requestId = $row.data('request-id');
                const serviceId = $row.data('service-id');
                
                if (requestId && serviceId) {
                    this.checkServiceApprovalStatus(requestId, serviceId, $row);
                }
            });
        }
        
        
        checkServiceApprovalStatus(requestId, serviceId, $row) {
            $.ajax({
                url: nexora_ajax.ajax_url,
                type: 'POST',
                data: {
                    action: 'nexora_get_service_approvals',
                    request_id: requestId,
                    nonce: nexora_ajax.nonce
                },
                success: (response) => {
                    if (response.success && response.data) {
                        const approval = response.data.find(a => a.service_id == serviceId);
                        if (approval) {
                            this.updateServiceRowStatus(requestId, serviceId, approval.status);
                        }
                    }
                },
                error: (xhr, status, error) => {
                    console.error('Error checking service approval status:', error);
                }
            });
        }
        
        
        showStatusDetails(e) {
            e.preventDefault();
            
            const $indicator = $(e.currentTarget);
            const status = $indicator.data('status');
            const details = $indicator.data('details');
            
            if (details) {
                this.showStatusModal(status, details);
            }
        }
        
        
        showStatusModal(status, details) {
            let statusText = '';
            let statusClass = '';
            
            switch (status) {
                case 'pending':
                    statusText = 'Waiting for customer approval';
                    statusClass = 'warning';
                    break;
                case 'approved':
                    statusText = 'Approved by customer';
                    statusClass = 'success';
                    break;
                case 'rejected':
                    statusText = 'Rejected by customer';
                    statusClass = 'error';
                    break;
            }
            
            const modalHtml = `
                <div class="status-details-modal">
                    <div class="modal-header ${statusClass}">
                        <h3>${statusText}</h3>
                        <button class="close-btn" onclick="this.closest('.status-details-modal').remove()">×</button>
                    </div>
                    <div class="modal-body">
                        <div class="status-details">
                            ${details}
                        </div>
                    </div>
                </div>
            `;
            $('.status-details-modal').remove();
            $('body').append(modalHtml);
        }
        
        
        showNotification(message, type = 'info') {
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
        }
        
        
        refreshServicesList() {
            if (typeof refreshServicesList === 'function') {
                refreshServicesList();
            } else {
                location.reload();
            }
        }
    }
    class CustomerServiceApproval {
        constructor() {
            this.init();
        }
        
        init() {
            this.bindEvents();
        }
        
        bindEvents() {
            $(document).on('click', '.approve-btn', this.handleApprove.bind(this));
            $(document).on('click', '.reject-btn', this.handleReject.bind(this));
            $(document).on('click', '.response-submit-btn', this.handleSubmitResponse.bind(this));
            $(document).on('click', '.response-cancel-btn', this.handleCancelResponse.bind(this));
        }
        
        
        handleApprove(e) {
            e.preventDefault();
            
            const $btn = $(e.currentTarget);
            const $card = $btn.closest('.service-approval-card');
            const approvalId = $card.data('approval-id');
            const tableType = $card.data('table-type') || 'service_requests';
            
            this.showResponseInput(approvalId, 'approve', tableType);
        }
        
        
        handleReject(e) {
            e.preventDefault();
            
            const $btn = $(e.currentTarget);
            const $card = $btn.closest('.service-approval-card');
            const approvalId = $card.data('approval-id');
            const tableType = $card.data('table-type') || 'service_requests';
            
            this.showResponseInput(approvalId, 'reject', tableType);
        }
        
        
        showResponseInput(approvalId, action, tableType) {
            const $card = $(`.service-approval-card[data-approval-id="${approvalId}"]`);
            const $responseInput = $card.find('.approval-response-input');
            
            if ($responseInput.length) {
                $responseInput.show();
                $responseInput.find('.response-note-textarea').focus();
                $responseInput.data('action', action);
                $responseInput.data('approval-id', approvalId);
                $responseInput.data('table-type', tableType);
            }
        }
        
        
        handleSubmitResponse(e) {
            e.preventDefault();
            
            const $btn = $(e.currentTarget);
            const $responseInput = $btn.closest('.approval-response-input');
            const action = $responseInput.data('action');
            const approvalId = $responseInput.data('approval-id');
            const tableType = $responseInput.data('table-type') || 'service_requests';
            const customerNote = $responseInput.find('.response-note-textarea').val();
            
            if (!approvalId || !action) {
                alert('Fehler: Ungültige Aktion');
                return;
            }
            
            const ajaxAction = action === 'approve' ? 'nexora_approve_service' : 'nexora_reject_service';
            const formData = new FormData();
            formData.append('action', ajaxAction);
            formData.append('approval_id', approvalId);
            formData.append('customer_note', customerNote);
            formData.append('table_type', tableType);
            formData.append('nonce', nexora_ajax.nonce || nexora_chat_ajax.nonce);
            $btn.prop('disabled', true).text('Verarbeite...');
            $.ajax({
                url: nexora_ajax.ajax_url || nexora_chat_ajax.ajax_url,
                type: 'POST',
                data: formData,
                processData: false,
                contentType: false,
                success: (response) => {
                    if (response.success) {
                        const successMessage = action === 'approve' ? 
                            'Service erfolgreich genehmigt!' : 
                            'Service erfolgreich abgelehnt!';
                        
                        alert(successMessage);
                        if (typeof refreshChatMessages === 'function') {
                            refreshChatMessages();
                        } else {
                            location.reload();
                        }
                    } else {
                        alert('Fehler: ' + response.data);
                    }
                },
                error: (xhr, status, error) => {
                    console.error('Service response error:', error);
                    alert('Error while processing the request.');
                },
                complete: () => {
                    $btn.prop('disabled', false).text('Bestätigen');
                    this.hideResponseInput($responseInput);
                }
            });
        }
        
        
        handleCancelResponse(e) {
            e.preventDefault();
            
            const $btn = $(e.currentTarget);
            const $responseInput = $btn.closest('.approval-response-input');
            
            this.hideResponseInput($responseInput);
        }
        
        
        hideResponseInput($responseInput) {
            $responseInput.hide();
            $responseInput.find('.response-note-textarea').val('');
            $responseInput.removeData('action approval-id');
        }
    }
    $(document).ready(function() {
        if ($('.send-approval-btn').length > 0) {
            window.serviceApprovalSystem = new ServiceApprovalSystem();
        }
        if ($('.service-approval-card').length > 0) {
            window.customerServiceApproval = new CustomerServiceApproval();
        }
    });
    window.openServiceApprovalModal = function(requestId, serviceId, serviceData) {
        if (window.serviceApprovalSystem) {
            window.serviceApprovalSystem.openApprovalModal(requestId, serviceId, serviceData);
        }
    };
    
    window.closeServiceApprovalModal = function() {
        if (window.serviceApprovalSystem) {
            window.serviceApprovalSystem.closeModal();
        }
    };
    
    window.sendServiceForApproval = function() {
        if (window.serviceApprovalSystem) {
            window.serviceApprovalSystem.sendServiceForApproval();
        }
    };
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
        if (window.customerServiceApproval) {
            window.customerServiceApproval.handleApproveService(requestId, tableType);
        } else {
            const formData = new FormData();
            formData.append('action', 'nexora_approve_service');
            formData.append('request_id', requestId);
            formData.append('customer_note', '');
            formData.append('table_type', tableType);
            formData.append('nonce', nexora_ajax.nonce || nexora_chat_ajax.nonce);
            
            $.ajax({
                url: nexora_ajax.ajax_url || nexora_chat_ajax.ajax_url,
                type: 'POST',
                data: formData,
                processData: false,
                contentType: false,
                success: function(response) {
                    if (response.success) {
                        this.showNotification('Service erfolgreich genehmigt!', 'success');
                        location.reload();
                    } else {
                        showNotification('Fehler: ' + response.data, 'error');
                    }
                },
                error: function() {
                    showNotification('Fehler beim Genehmigen des Services.', 'error');
                }
            });
        }
    };
    
    window.rejectService = function(requestId, tableType = 'complete_service_requests') {
        if (window.customerServiceApproval) {
            window.customerServiceApproval.handleRejectService(requestId, tableType);
        } else {
            const formData = new FormData();
            formData.append('action', 'nexora_reject_service');
            formData.append('request_id', requestId);
            formData.append('customer_note', '');
            formData.append('table_type', tableType);
            formData.append('nonce', nexora_ajax.nonce || nexora_chat_ajax.nonce);
            
            $.ajax({
                url: nexora_ajax.ajax_url || nexora_chat_ajax.ajax_url,
                type: 'POST',
                data: formData,
                processData: false,
                contentType: false,
                success: function(response) {
                    if (response.success) {
                        showNotification('Service erfolgreich abgelehnt!', 'success');
                        location.reload();
                    } else {
                        showNotification('Fehler: ' + response.data, 'error');
                    }
                },
                error: function() {
                    showNotification('Fehler beim Ablehnen des Services.', 'error');
                }
            });
        }
    };
    

    
    window.submitServiceResponse = function() {
        if (window.customerServiceApproval) {
            const $visibleInput = $('.approval-response-input:visible');
            if ($visibleInput.length) {
                $visibleInput.find('.response-submit-btn').click();
            }
        }
    };
    
    window.cancelServiceResponse = function() {
        if (window.customerServiceApproval) {
            const $visibleInput = $('.approval-response-input:visible');
            if ($visibleInput.length) {
                $visibleInput.find('.response-cancel-btn').click();
            }
        }
    };
    
})(jQuery);
