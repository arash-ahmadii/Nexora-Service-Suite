<?php

if (!defined('ABSPATH')) {
    exit;
}
?>

<div id="service-approval-modal" class="Nexora Service Suite-modal" style="display: none;">
    <div class="Nexora Service Suite-modal-overlay"></div>
    <div class="Nexora Service Suite-modal-content">
        <div class="Nexora Service Suite-modal-header">
            <h3>📤 Service zur Freigabe senden</h3>
            <button type="button" class="Nexora Service Suite-modal-close" onclick="closeServiceApprovalModal()">
                <i class="fas fa-times"></i>
            </button>
        </div>
        
        <div class="Nexora Service Suite-modal-body">
            <form id="service-approval-form">
                <input type="hidden" id="approval-request-id" name="request_id">
                <input type="hidden" id="approval-service-id" name="service_id">
                
                
                <div class="service-details-section">
                    <h4>Service-Details</h4>
                    <div class="service-info-grid">
                        <div class="service-info-item">
                            <label>Service-Name:</label>
                            <span id="approval-service-title"></span>
                        </div>
                        <div class="service-info-item">
                            <label>Preis:</label>
                            <span id="approval-service-cost"></span>
                        </div>
                        <div class="service-info-item">
                            <label>Anzahl:</label>
                            <span id="approval-service-quantity"></span>
                        </div>
                        <div class="service-info-item full-width">
                            <label>Beschreibung:</label>
                            <span id="approval-service-description"></span>
                        </div>
                    </div>
                </div>
                
                
                <div class="admin-note-section">
                    <label for="approval-admin-note">Notiz für den Kunden:</label>
                    <textarea 
                        id="approval-admin-note" 
                        name="admin_note" 
                        rows="4" 
                        placeholder="Fügen Sie hier eine Notiz für den Kunden hinzu (optional)..."
                    ></textarea>
                </div>
                
                
                <div class="confirmation-section">
                    <p class="confirmation-text">
                        <i class="fas fa-info-circle"></i>
                        Dieser Service wird dem Kunden zur Freigabe gesendet. Der Kunde kann den Service genehmigen oder ablehnen.
                    </p>
                </div>
            </form>
        </div>
        
        <div class="Nexora Service Suite-modal-footer">
            <button type="button" class="Nexora Service Suite-btn Nexora Service Suite-btn-secondary" onclick="closeServiceApprovalModal()">
                Abbrechen
            </button>
            <button type="button" class="Nexora Service Suite-btn Nexora Service Suite-btn-primary" onclick="sendServiceForApproval()">
                <i class="fas fa-paper-plane"></i>
                📤 Zur Freigabe senden
            </button>
        </div>
    </div>
</div>

<style>

.Nexora Service Suite-modal {
    position: fixed;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    z-index: 9999;
    display: none;
}

.Nexora Service Suite-modal.show {
    display: block;
}

.Nexora Service Suite-modal-overlay {
    position: absolute;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    background: rgba(0, 0, 0, 0.7);
    backdrop-filter: blur(8px);
}

.Nexora Service Suite-modal-content {
    position: absolute;
    top: 50%;
    left: 50%;
    transform: translate(-50%, -50%);
    background: rgba(26, 31, 43, 0.95);
    backdrop-filter: blur(20px);
    border: 1px solid rgba(255, 255, 255, 0.2);
    border-radius: 16px;
    box-shadow: 0 20px 40px rgba(0, 0, 0, 0.5);
    width: 90%;
    max-width: 600px;
    max-height: 90vh;
    overflow: hidden;
    animation: modalSlideIn 0.3s ease-out;
}

@keyframes modalSlideIn {
    from {
        opacity: 0;
        transform: translate(-50%, -60%);
    }
    to {
        opacity: 1;
        transform: translate(-50%, -50%);
    }
}

.Nexora Service Suite-modal-header {
    background: rgba(108, 93, 211, 0.3);
    backdrop-filter: blur(20px);
    border-bottom: 1px solid rgba(255, 255, 255, 0.12);
    color: #FFFFFF;
    padding: 20px;
    display: flex;
    justify-content: space-between;
    align-items: center;
}

.Nexora Service Suite-modal-header h3 {
    margin: 0;
    font-size: 18px;
    font-weight: 600;
    color: #FFFFFF;
}

.Nexora Service Suite-modal-close {
    background: none;
    border: none;
    color: #FFFFFF;
    font-size: 20px;
    cursor: pointer;
    padding: 8px;
    border-radius: 50%;
    transition: background-color 0.2s;
}

.Nexora Service Suite-modal-close:hover {
    background: rgba(255, 255, 255, 0.2);
}

.Nexora Service Suite-modal-body {
    padding: 20px;
    max-height: 60vh;
    overflow-y: auto;
    background: rgba(26, 31, 43, 0.1);
    backdrop-filter: blur(10px);
}

.Nexora Service Suite-modal-footer {
    padding: 20px;
    border-top: 1px solid rgba(255, 255, 255, 0.12);
    display: flex;
    justify-content: flex-end;
    gap: 12px;
    background: rgba(26, 31, 43, 0.2);
    backdrop-filter: blur(10px);
}

.service-details-section {
    margin-bottom: 24px;
}

.service-details-section h4 {
    margin: 0 0 16px 0;
    color: #FFFFFF;
    font-size: 16px;
    font-weight: 600;
}

.service-info-grid {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 16px;
}

.service-info-item {
    display: flex;
    flex-direction: column;
    gap: 4px;
}

.service-info-item.full-width {
    grid-column: 1 / -1;
}

.service-info-item label {
    font-size: 12px;
    color: #a0aec0;
    font-weight: 500;
    text-transform: uppercase;
    letter-spacing: 0.5px;
}

.service-info-item span {
    font-size: 14px;
    color: #FFFFFF;
    font-weight: 500;
    padding: 8px 12px;
    background: rgba(255, 255, 255, 0.1);
    backdrop-filter: blur(10px);
    border-radius: 6px;
    border: 1px solid rgba(255, 255, 255, 0.2);
}

.admin-note-section {
    margin-bottom: 24px;
}

.admin-note-section label {
    display: block;
    margin-bottom: 8px;
    font-weight: 600;
    color: #FFFFFF;
}

.admin-note-section textarea {
    width: 100%;
    padding: 12px;
    border: 1px solid rgba(255, 255, 255, 0.2);
    border-radius: 8px;
    font-family: inherit;
    font-size: 14px;
    line-height: 1.5;
    resize: vertical;
    transition: border-color 0.2s;
    background: rgba(255, 255, 255, 0.05);
    backdrop-filter: blur(10px);
    color: #FFFFFF;
}

.admin-note-section textarea:focus {
    outline: none;
    border-color: rgba(108, 93, 211, 0.8);
    box-shadow: 0 0 0 3px rgba(108, 93, 211, 0.2);
}

.admin-note-section textarea::placeholder {
    color: #a0aec0;
}

.confirmation-section {
    background: rgba(108, 93, 211, 0.1);
    backdrop-filter: blur(10px);
    border: 1px solid rgba(108, 93, 211, 0.3);
    border-radius: 8px;
    padding: 16px;
}

.confirmation-text {
    margin: 0;
    color: #FFFFFF;
    font-size: 14px;
    display: flex;
    align-items: flex-start;
    gap: 8px;
}

.confirmation-text i {
    margin-top: 2px;
    color: #6c5dd3;
}

.Nexora Service Suite-btn {
    padding: 10px 20px;
    border: none;
    border-radius: 8px;
    font-size: 14px;
    font-weight: 600;
    cursor: pointer;
    transition: all 0.2s;
    display: inline-flex;
    align-items: center;
    gap: 8px;
    text-decoration: none;
}

.Nexora Service Suite-btn-primary {
    background: rgba(108, 93, 211, 0.8);
    backdrop-filter: blur(10px);
    border: 1px solid rgba(255, 255, 255, 0.2);
    color: #FFFFFF;
}

.Nexora Service Suite-btn-primary:hover {
    background: rgba(108, 93, 211, 1);
    transform: translateY(-1px);
    box-shadow: 0 4px 12px rgba(108, 93, 211, 0.4);
}

.Nexora Service Suite-btn-secondary {
    background: rgba(255, 255, 255, 0.1);
    backdrop-filter: blur(10px);
    border: 1px solid rgba(255, 255, 255, 0.2);
    color: #a0aec0;
}

.Nexora Service Suite-btn-secondary:hover {
    background: rgba(255, 255, 255, 0.2);
    color: #FFFFFF;
}

@media (max-width: 768px) {
    .Nexora Service Suite-modal-content {
        width: 95%;
        margin: 20px;
    }
    
    .service-info-grid {
        grid-template-columns: 1fr;
    }
    
    .Nexora Service Suite-modal-footer {
        flex-direction: column;
    }
    
    .Nexora Service Suite-btn {
        width: 100%;
        justify-content: center;
    }
}

.Nexora Service Suite-btn.loading {
    opacity: 0.7;
    cursor: not-allowed;
}

.Nexora Service Suite-btn.loading::after {
    content: '';
    width: 16px;
    height: 16px;
    border: 2px solid transparent;
    border-top: 2px solid currentColor;
    border-radius: 50%;
    animation: spin 1s linear infinite;
    margin-left: 8px;
}

@keyframes spin {
    to {
        transform: rotate(360deg);
    }
}
</style>

<script>
let currentApprovalData = {};

function openServiceApprovalModal(requestId, serviceId, serviceData) {
    currentApprovalData = {
        request_id: requestId,
        service_id: serviceId,
        service_data: serviceData
    };
    document.getElementById('approval-request-id').value = requestId;
    document.getElementById('approval-service-id').value = serviceId;
    document.getElementById('approval-service-title').textContent = serviceData.title || 'N/A';
    document.getElementById('approval-service-cost').textContent = '€' + (serviceData.cost || '0.00');
    document.getElementById('approval-service-quantity').textContent = serviceData.quantity || '1';
    document.getElementById('approval-service-description').textContent = serviceData.description || 'Keine Beschreibung verfügbar';
    document.getElementById('approval-admin-note').value = '';
    document.getElementById('service-approval-modal').style.display = 'block';
    document.body.style.overflow = 'hidden';
}

function closeServiceApprovalModal() {
    document.getElementById('service-approval-modal').style.display = 'none';
    document.body.style.overflow = '';
    currentApprovalData = {};
}

function sendServiceForApproval() {
    const form = document.getElementById('service-approval-form');
    const submitBtn = document.querySelector('.Nexora Service Suite-btn-primary');
    const adminNote = document.getElementById('approval-admin-note').value;
    if (!currentApprovalData.request_id || !currentApprovalData.service_id) {
        alert('Fehler: Ungültige Service-Daten');
        return;
    }
    submitBtn.classList.add('loading');
    submitBtn.disabled = true;
    const formData = new FormData();
    formData.append('action', 'nexora_send_service_approval');
    formData.append('request_id', currentApprovalData.request_id);
    formData.append('service_id', currentApprovalData.service_id);
    formData.append('admin_note', adminNote);
    formData.append('nonce', nexora_ajax.nonce || nexora_chat_ajax.nonce);
    jQuery.ajax({
        url: nexora_ajax.ajax_url || nexora_chat_ajax.ajax_url,
        type: 'POST',
        data: formData,
        processData: false,
        contentType: false,
        success: function(response) {
            if (response.success) {
                alert('Service erfolgreich zur Freigabe gesendet!');
                closeServiceApprovalModal();
                if (typeof refreshServicesList === 'function') {
                    refreshServicesList();
                } else {
                    location.reload();
                }
            } else {
                alert('Fehler: ' + response.data);
            }
        },
        error: function(xhr, status, error) {
            console.error('Service approval error:', error);
            alert('Fehler beim Senden des Services zur Freigabe.');
        },
        complete: function() {
            submitBtn.classList.remove('loading');
            submitBtn.disabled = false;
        }
    });
}
document.addEventListener('DOMContentLoaded', function() {
    const modal = document.getElementById('service-approval-modal');
    const overlay = modal.querySelector('.Nexora Service Suite-modal-overlay');
    
    overlay.addEventListener('click', function() {
        closeServiceApprovalModal();
    });
    document.addEventListener('keydown', function(e) {
        if (e.key === 'Escape' && modal.style.display === 'block') {
            closeServiceApprovalModal();
        }
    });
});
</script>
