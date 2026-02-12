<?php

if (!defined('ABSPATH')) {
    exit;
}

class Nexora_Service_Approval_Card {
    
    
    public static function render($approval_data, $is_customer = false) {
        $status = $approval_data['status'] ?? 'pending';
        $service_title = $approval_data['service_title'] ?? '';
        $service_cost = $approval_data['service_cost'] ?? '0.00';
        $service_quantity = $approval_data['service_quantity'] ?? '1';
        $admin_note = $approval_data['admin_note'] ?? '';
        $approval_id = $approval_data['id'] ?? '';
        
        $card_class = 'service-approval-card';
        $card_class .= ' status-' . $status;
        
        if ($is_customer && $status === 'pending') {
            $card_class .= ' customer-pending';
        }
        
        ob_start();
        ?>
        <div class="<?php echo esc_attr($card_class); ?>" data-approval-id="<?php echo esc_attr($approval_id); ?>" data-table-type="<?php echo esc_attr($approval_data['table_type'] ?? 'service_requests'); ?>">
            
            <div class="approval-card-header">
                <div class="approval-icon">
                    <?php if ($status === 'pending'): ?>
                        🎯
                    <?php elseif ($status === 'approved'): ?>
                        ✅
                    <?php elseif ($status === 'rejected'): ?>
                        ❌
                    <?php endif; ?>
                </div>
                <div class="approval-title">
                    <?php if ($status === 'pending'): ?>
                        Service zur Freigabe
                    <?php elseif ($status === 'approved'): ?>
                        Service genehmigt
                    <?php elseif ($status === 'rejected'): ?>
                        Service abgelehnt
                    <?php endif; ?>
                </div>
                <div class="approval-status status-<?php echo esc_attr($status); ?>">
                    <?php if ($status === 'pending'): ?>
                        Wartend
                    <?php elseif ($status === 'approved'): ?>
                        Genehmigt
                    <?php elseif ($status === 'rejected'): ?>
                        Abgelehnt
                    <?php endif; ?>
                </div>
            </div>
            
            
            <div class="approval-service-details">
                <div class="service-info-row">
                    <span class="service-label">Service:</span>
                    <span class="service-value"><?php echo esc_html($service_title); ?></span>
                </div>
                <div class="service-info-row">
                    <span class="service-label">Preis:</span>
                    <span class="service-value price">€<?php echo esc_html($service_cost); ?></span>
                </div>
                <div class="service-info-row">
                    <span class="service-label">Anzahl:</span>
                    <span class="service-value"><?php echo esc_html($service_quantity); ?></span>
                </div>
            </div>
            
            
            <?php if (!empty($admin_note)): ?>
                <div class="approval-admin-note">
                    <div class="note-label">Notiz vom Admin:</div>
                    <div class="note-content"><?php echo esc_html($admin_note); ?></div>
                </div>
            <?php endif; ?>
            
            
            <?php if (!empty($approval_data['customer_response'])): ?>
                <div class="approval-customer-response">
                    <div class="response-label">Ihre Antwort:</div>
                    <div class="response-content"><?php echo esc_html($approval_data['customer_response']); ?></div>
                </div>
            <?php endif; ?>
            
            
            <?php if ($is_customer && $status === 'pending'): ?>
                <div class="approval-actions">
                    <button type="button" class="approval-btn approve-btn" onclick="approveService(<?php echo esc_attr($approval_data['request_id'] ?? $approval_id); ?>, '<?php echo esc_attr($approval_data['table_type'] ?? 'complete_service_requests'); ?>')">
                        <i class="fas fa-check"></i>
                        ✅ Bestätigen
                    </button>
                    <button type="button" class="approval-btn reject-btn" onclick="rejectService(<?php echo esc_attr($approval_data['request_id'] ?? $approval_id); ?>, '<?php echo esc_attr($approval_data['table_type'] ?? 'complete_service_requests'); ?>')">
                        <i class="fas fa-times"></i>
                        ❌ Ablehnen
                    </button>
                </div>
                
                
                <div class="approval-response-input" style="display: none;">
                    <textarea 
                        class="response-note-textarea" 
                        placeholder="Fügen Sie hier eine Notiz hinzu (optional)..."
                        rows="3"
                    ></textarea>
                    <div class="response-input-actions">
                        <button type="button" class="response-submit-btn" onclick="submitServiceResponse()">
                            Bestätigen
                        </button>
                        <button type="button" class="response-cancel-btn" onclick="cancelServiceResponse()">
                            Abbrechen
                        </button>
                    </div>
                </div>
            <?php endif; ?>
            
            
            <div class="approval-timestamp">
                <?php 
                $created_at = $approval_data['created_at'] ?? '';
                if ($created_at) {
                    echo 'Erstellt: ' . date('d.m.Y H:i', strtotime($created_at));
                }
                ?>
            </div>
        </div>
        <?php
        return ob_get_clean();
    }
    
    
    public static function generate_chat_message($approval_data, $is_customer = false) {
        $status = $approval_data['status'] ?? 'pending';
        $service_title = $approval_data['service_title'] ?? '';
        $service_cost = $approval_data['service_cost'] ?? '0.00';
        $admin_note = $approval_data['admin_note'] ?? '';
        
        $message = '';
        
        if ($status === 'pending') {
            if ($is_customer) {
                $message = "🎯 Ein neuer Service wartet auf Ihre Freigabe:\n";
                $message .= "📋 {$service_title}\n";
                $message .= "💰 Preis: €{$service_cost}\n";
                if (!empty($admin_note)) {
                    $message .= "📝 Notiz: {$admin_note}\n";
                }
                $message .= "\nBitte bestätigen oder lehnen Sie diesen Service ab.";
            } else {
                $message = "📤 Service '{$service_title}' (€{$service_cost}) wurde zur Kundenfreigabe gesendet.";
                if (!empty($admin_note)) {
                    $message .= " Notiz: {$admin_note}";
                }
            }
        } elseif ($status === 'approved') {
            $message = "✅ Service '{$service_title}' wurde vom Kunden genehmigt.";
        } elseif ($status === 'rejected') {
            $message = "❌ Service '{$service_title}' wurde vom Kunden abgelehnt.";
        }
        
        return $message;
    }
}
add_action('wp_head', function() {
    ?>
    <style>
    
    .service-approval-card {
        background: white;
        border: 2px solid #e2e8f0;
        border-radius: 12px;
        padding: 16px;
        margin: 12px 0;
        box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
        position: relative;
        overflow: hidden;
    }
    
    .service-approval-card::before {
        content: '';
        position: absolute;
        top: 0;
        left: 0;
        right: 0;
        height: 4px;
        background: linear-gradient(90deg, #667eea, #764ba2);
    }
    
    
    .service-approval-card.status-pending {
        border-color: #fbbf24;
        background: linear-gradient(135deg, #fef3c7 0%, #fde68a 100%);
    }
    
    .service-approval-card.status-approved {
        border-color: #10b981;
        background: linear-gradient(135deg, #d1fae5 0%, #a7f3d0 100%);
    }
    
    .service-approval-card.status-rejected {
        border-color: #ef4444;
        background: linear-gradient(135deg, #fee2e2 0%, #fecaca 100%);
    }
    
    
    .approval-card-header {
        display: flex;
        align-items: center;
        gap: 12px;
        margin-bottom: 16px;
        padding-bottom: 12px;
        border-bottom: 1px solid rgba(0, 0, 0, 0.1);
    }
    
    .approval-icon {
        font-size: 24px;
        width: 40px;
        height: 40px;
        display: flex;
        align-items: center;
        justify-content: center;
        background: rgba(255, 255, 255, 0.8);
        border-radius: 50%;
        box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
    }
    
    .approval-title {
        flex: 1;
        font-weight: 700;
        font-size: 16px;
        color: #1e293b;
    }
    
    .approval-status {
        padding: 4px 12px;
        border-radius: 20px;
        font-size: 12px;
        font-weight: 600;
        text-transform: uppercase;
        letter-spacing: 0.5px;
    }
    
    .approval-status.status-pending {
        background: #fbbf24;
        color: #92400e;
    }
    
    .approval-status.status-approved {
        background: #10b981;
        color: white;
    }
    
    .approval-status.status-rejected {
        background: #ef4444;
        color: white;
    }
    
    
    .approval-service-details {
        margin-bottom: 16px;
    }
    
    .service-info-row {
        display: flex;
        justify-content: space-between;
        align-items: center;
        padding: 8px 0;
        border-bottom: 1px solid rgba(0, 0, 0, 0.05);
    }
    
    .service-info-row:last-child {
        border-bottom: none;
    }
    
    .service-label {
        font-weight: 600;
        color: #64748b;
        font-size: 14px;
    }
    
    .service-value {
        font-weight: 700;
        color: #1e293b;
        font-size: 14px;
    }
    
    .service-value.price {
        color: #059669;
        font-size: 16px;
    }
    
    
    .approval-admin-note {
        background: rgba(255, 255, 255, 0.7);
        border-radius: 8px;
        padding: 12px;
        margin-bottom: 16px;
        border-left: 4px solid #667eea;
    }
    
    .note-label {
        font-weight: 600;
        color: #475569;
        font-size: 12px;
        text-transform: uppercase;
        letter-spacing: 0.5px;
        margin-bottom: 4px;
    }
    
    .note-content {
        color: #1e293b;
        font-size: 14px;
        line-height: 1.5;
    }
    
    
    .approval-customer-response {
        background: rgba(255, 255, 255, 0.7);
        border-radius: 8px;
        padding: 12px;
        margin-bottom: 16px;
        border-left: 4px solid #10b981;
    }
    
    .response-label {
        font-weight: 600;
        color: #475569;
        font-size: 12px;
        text-transform: uppercase;
        letter-spacing: 0.5px;
        margin-bottom: 4px;
    }
    
    .response-content {
        color: #1e293b;
        font-size: 14px;
        line-height: 1.5;
    }
    
    
    .approval-actions {
        display: flex;
        gap: 12px;
        margin-bottom: 16px;
    }
    
    .approval-btn {
        flex: 1;
        padding: 10px 16px;
        border: none;
        border-radius: 8px;
        font-size: 14px;
        font-weight: 600;
        cursor: pointer;
        transition: all 0.2s;
        display: flex;
        align-items: center;
        justify-content: center;
        gap: 8px;
    }
    
    .approve-btn {
        background: linear-gradient(135deg, #10b981, #059669);
        color: white;
    }
    
    .approve-btn:hover {
        transform: translateY(-1px);
        box-shadow: 0 4px 12px rgba(16, 185, 129, 0.4);
    }
    
    .reject-btn {
        background: linear-gradient(135deg, #ef4444, #dc2626);
        color: white;
    }
    
    .reject-btn:hover {
        transform: translateY(-1px);
        box-shadow: 0 4px 12px rgba(239, 68, 68, 0.4);
    }
    
    
    .approval-response-input {
        background: rgba(255, 255, 255, 0.8);
        border-radius: 8px;
        padding: 16px;
        margin-bottom: 16px;
        border: 2px solid #e2e8f0;
    }
    
    .response-note-textarea {
        width: 100%;
        padding: 12px;
        border: 1px solid #d1d5db;
        border-radius: 6px;
        font-family: inherit;
        font-size: 14px;
        line-height: 1.5;
        resize: vertical;
        margin-bottom: 12px;
    }
    
    .response-note-textarea:focus {
        outline: none;
        border-color: #667eea;
        box-shadow: 0 0 0 3px rgba(102, 126, 234, 0.1);
    }
    
    .response-input-actions {
        display: flex;
        gap: 8px;
        justify-content: flex-end;
    }
    
    .response-submit-btn {
        padding: 8px 16px;
        background: #667eea;
        color: white;
        border: none;
        border-radius: 6px;
        font-size: 14px;
        font-weight: 600;
        cursor: pointer;
        transition: background-color 0.2s;
    }
    
    .response-submit-btn:hover {
        background: #5a67d8;
    }
    
    .response-cancel-btn {
        padding: 8px 16px;
        background: #9ca3af;
        color: white;
        border: none;
        border-radius: 6px;
        font-size: 14px;
        font-weight: 600;
        cursor: pointer;
        transition: background-color 0.2s;
    }
    
    .response-cancel-btn:hover {
        background: #6b7280;
    }
    
    
    .approval-timestamp {
        text-align: right;
        font-size: 11px;
        color: #9ca3af;
        font-style: italic;
    }
    
    
    @media (max-width: 768px) {
        .service-approval-card {
            padding: 12px;
        }
        
        .approval-card-header {
            flex-direction: column;
            align-items: flex-start;
            gap: 8px;
        }
        
        .approval-actions {
            flex-direction: column;
        }
        
        .service-info-row {
            flex-direction: column;
            align-items: flex-start;
            gap: 4px;
        }
    }
    
    
    .service-approval-card {
        animation: cardSlideIn 0.3s ease-out;
    }
    
    @keyframes cardSlideIn {
        from {
            opacity: 0;
            transform: translateY(20px);
        }
        to {
            opacity: 1;
            transform: translateY(0);
        }
    }
    
    
    .service-approval-card:hover {
        transform: translateY(-2px);
        box-shadow: 0 4px 16px rgba(0, 0, 0, 0.15);
        transition: all 0.3s ease;
    }
    </style>
    <?php
});
add_action('wp_footer', function() {
    ?>
    <script>
    let currentApprovalId = null;
    let currentAction = null;
    
    function approveService(approvalId) {
        currentApprovalId = approvalId;
        currentAction = 'approve';
        showResponseInput();
    }
    
    function rejectService(approvalId) {
        currentApprovalId = approvalId;
        currentAction = 'reject';
        showResponseInput();
    }
    
    function showResponseInput() {
        const responseInput = document.querySelector('.approval-response-input');
        if (responseInput) {
            responseInput.style.display = 'block';
            responseInput.querySelector('.response-note-textarea').focus();
        }
    }
    
    function hideResponseInput() {
        const responseInput = document.querySelector('.approval-response-input');
        if (responseInput) {
            responseInput.style.display = 'none';
            responseInput.querySelector('.response-note-textarea').value = '';
        }
    }
    
    function submitServiceResponse() {
        if (!currentApprovalId || !currentAction) {
            alert('Fehler: Ungültige Aktion');
            return;
        }
        
        const noteTextarea = document.querySelector('.response-note-textarea');
        const customerNote = noteTextarea ? noteTextarea.value : '';
        
        const action = currentAction === 'approve' ? 'nexora_approve_service' : 'nexora_reject_service';
        const formData = new FormData();
        formData.append('action', action);
        formData.append('request_id', currentApprovalId);
        formData.append('customer_note', customerNote);
        formData.append('nonce', nexora_ajax.nonce);
        jQuery.ajax({
            url: nexora_ajax.ajax_url,
            type: 'POST',
            data: formData,
            processData: false,
            contentType: false,
            success: function(response) {
                if (response.success) {
                    const successMessage = currentAction === 'approve' ? 
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
            error: function(xhr, status, error) {
                console.error('Service response error:', error);
                alert('Fehler beim Verarbeiten der Anfrage.');
            },
            complete: function() {
                currentApprovalId = null;
                currentAction = null;
                hideResponseInput();
            }
        });
    }
    
    function cancelServiceResponse() {
        currentApprovalId = null;
        currentAction = null;
        hideResponseInput();
    }
    document.addEventListener('click', function(e) {
        const responseInput = document.querySelector('.approval-response-input');
        if (responseInput && !responseInput.contains(e.target)) {
            const approveBtn = document.querySelector('.approve-btn');
            const rejectBtn = document.querySelector('.reject-btn');
            
            if (!approveBtn?.contains(e.target) && !rejectBtn?.contains(e.target)) {
                cancelServiceResponse();
            }
        }
    });
    </script>
    <?php
});
?>
