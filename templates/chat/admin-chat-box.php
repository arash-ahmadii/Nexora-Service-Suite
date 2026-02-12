<?php

if (!defined('ABSPATH')) {
    exit;
}

$request_id = isset($request_id) ? intval($request_id) : 0;
if (!$request_id) {
    return;
}
$chat_manager = new Nexora_Chat_Manager();
$session = $chat_manager->get_or_create_session($request_id, null, get_current_user_id());

if (!$session) {
    echo '<div class="chat-error">Fehler beim Laden des Chats.</div>';
    return;
}

$session_id = $session->id;
$user_id = get_current_user_id();
global $wpdb;
$user_info = $wpdb->get_row($wpdb->prepare(
    "SELECT u.display_name, u.user_email 
     FROM {$wpdb->prefix}users u 
     INNER JOIN {$wpdb->prefix}nexora_service_requests sr ON u.ID = sr.user_id 
     WHERE sr.id = %d",
    $request_id
));
?>

<div class="Nexora Service Suite-admin-chat-container" data-session-id="<?php echo esc_attr($session_id); ?>" data-request-id="<?php echo esc_attr($request_id); ?>">
    <div class="admin-chat-header">
        <div class="chat-title">
            <i class="fas fa-comments"></i>
            <span>Chat mit Kunde</span>
        </div>
        <div class="customer-info">
            <span class="customer-name"><?php echo esc_html($user_info->display_name ?? 'Unbekannt'); ?></span>
            <span class="customer-email"><?php echo esc_html($user_info->user_email ?? ''); ?></span>
        </div>
        <div class="chat-status">
            <span class="status-indicator online"></span>
            <span class="status-text">Online</span>
        </div>
    </div>
    
    <div class="admin-chat-messages" id="admin-chat-messages-<?php echo esc_attr($session_id); ?>">
        
    </div>
    
    <div class="admin-chat-input-container">
        <div class="admin-chat-input-wrapper">
            <div class="file-upload-area" id="admin-file-upload-<?php echo esc_attr($session_id); ?>">
                <input type="file" id="admin-file-input-<?php echo esc_attr($session_id); ?>" accept=".jpg,.jpeg,.png,.gif,.pdf,.doc,.docx,.txt" style="display: none;">
                <button type="button" class="file-upload-btn" onclick="document.getElementById('admin-file-input-<?php echo esc_attr($session_id); ?>').click()">
                    <i class="fas fa-paperclip"></i>
                </button>
            </div>
            
            <div class="message-input-wrapper">
                <textarea 
                    id="admin-message-input-<?php echo esc_attr($session_id); ?>" 
                    class="message-input" 
                    placeholder="Nachricht an Kunde eingeben..."
                    rows="1"
                ></textarea>
                <button type="button" class="send-btn" onclick="sendAdminChatMessage(<?php echo esc_attr($session_id); ?>)">
                    Senden
                </button>
            </div>
        </div>
        
        <div class="admin-chat-actions">
            <div class="file-info" id="admin-file-info-<?php echo esc_attr($session_id); ?>" style="display: none;">
                <span class="file-name"></span>
                <button type="button" class="remove-file-btn" onclick="removeAdminSelectedFile(<?php echo esc_attr($session_id); ?>)">
                    <i class="fas fa-times"></i>
                </button>
            </div>
            
            <div class="chat-tools">
                <button type="button" class="chat-tool-btn" onclick="markAllAsRead(<?php echo esc_attr($session_id); ?>)" title="Alle als gelesen markieren">
                    <i class="fas fa-check-double"></i>
                </button>
                <button type="button" class="chat-tool-btn" onclick="refreshChat(<?php echo esc_attr($session_id); ?>)" title="Chat aktualisieren">
                    <i class="fas fa-sync-alt"></i>
                </button>
            </div>
        </div>
    </div>
</div>

<style>

.Nexora Service Suite-admin-chat-container {
    background: rgba(26, 31, 43, 0.2);
    backdrop-filter: blur(20px);
    border: 1px solid rgba(255, 255, 255, 0.2);
    border-radius: 16px;
    box-shadow: 0 8px 32px rgba(0, 0, 0, 0.3);
    margin: 20px 0;
    overflow: hidden;
}

.admin-chat-header {
    background: rgba(108, 93, 211, 0.2);
    backdrop-filter: blur(20px);
    border-bottom: 1px solid rgba(255, 255, 255, 0.12);
    color: #FFFFFF;
    padding: 16px 20px;
    display: flex;
    justify-content: space-between;
    align-items: center;
}

.chat-title {
    display: flex;
    align-items: center;
    gap: 8px;
    font-weight: 600;
    font-size: 16px;
    color: #FFFFFF;
}

.customer-info {
    display: flex;
    flex-direction: column;
    align-items: center;
    gap: 2px;
}

.customer-name {
    font-weight: 600;
    font-size: 14px;
    color: #FFFFFF;
}

.customer-email {
    font-size: 12px;
    opacity: 0.8;
    color: #a0aec0;
}

.chat-status {
    display: flex;
    align-items: center;
    gap: 6px;
    font-size: 14px;
    color: #FFFFFF;
}

.status-indicator {
    width: 8px;
    height: 8px;
    border-radius: 50%;
    background: #4ade80;
    animation: pulse 2s infinite;
}

@keyframes pulse {
    0% { opacity: 1; }
    50% { opacity: 0.5; }
    100% { opacity: 1; }
}

.admin-chat-messages {
    height: 350px;
    overflow-y: auto;
    padding: 16px;
    background: rgba(26, 31, 43, 0.1);
    backdrop-filter: blur(10px);
}

.admin-chat-message {
    margin-bottom: 16px;
    display: flex;
    align-items: flex-start;
    gap: 12px;
}

.admin-chat-message.admin {
    flex-direction: row-reverse;
}

.admin-message-avatar {
    width: 36px;
    height: 36px;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 14px;
    font-weight: 600;
    color: white;
    flex-shrink: 0;
    background: rgba(108, 93, 211, 0.8);
    backdrop-filter: blur(10px);
    border: 1px solid rgba(255, 255, 255, 0.2);
}

.admin-message-avatar.admin {
    background: rgba(108, 93, 211, 0.8);
    backdrop-filter: blur(10px);
}

.admin-message-avatar.user {
    background: rgba(102, 126, 234, 0.8);
    backdrop-filter: blur(10px);
    border: 1px solid rgba(255, 255, 255, 0.2);
}

.admin-message-content {
    max-width: 70%;
    background: rgba(255, 255, 255, 0.1);
    backdrop-filter: blur(10px);
    padding: 12px 16px;
    border-radius: 18px;
    border: 1px solid rgba(255, 255, 255, 0.2);
    position: relative;
    color: #FFFFFF;
}

.admin-chat-message.admin .admin-message-content {
    background: rgba(108, 93, 211, 0.2);
    backdrop-filter: blur(10px);
    color: #FFFFFF;
}

.admin-message-text {
    margin: 0;
    line-height: 1.4;
    word-wrap: break-word;
    color: #FFFFFF;
}

.admin-message-time {
    font-size: 11px;
    opacity: 0.7;
    margin-top: 4px;
    text-align: right;
    color: #a0aec0;
}

.admin-chat-message.admin .admin-message-time {
    text-align: left;
}

.admin-message-file {
    margin-top: 8px;
    padding: 8px 12px;
    background: rgba(255, 255, 255, 0.1);
    border-radius: 8px;
    display: flex;
    align-items: center;
    gap: 8px;
    text-decoration: none;
    color: inherit;
    border: 1px solid rgba(255, 255, 255, 0.2);
}

.admin-message-file:hover {
    background: rgba(255, 255, 255, 0.2);
}

.file-icon {
    font-size: 16px;
}

.admin-chat-input-container {
    padding: 16px;
    background: rgba(26, 31, 43, 0.1);
    backdrop-filter: blur(10px);
}

.admin-chat-input-wrapper {
    display: flex;
    align-items: flex-end;
    gap: 12px;
    border: 1px solid rgba(255, 255, 255, 0.2);
    border-radius: 24px;
    padding: 8px 12px;
    background: rgba(255, 255, 255, 0.05);
    backdrop-filter: blur(10px);
}

.file-upload-btn {
    background: none;
    border: none;
    color: #a0aec0;
    cursor: pointer;
    padding: 8px;
    border-radius: 50%;
    transition: all 0.2s;
}

.file-upload-btn:hover {
    background: rgba(255, 255, 255, 0.1);
    color: #FFFFFF;
}

.message-input-wrapper {
    flex: 1;
    display: flex;
    align-items: flex-end;
    gap: 8px;
}

.message-input {
    flex: 1;
    border: none;
    background: none;
    resize: none;
    outline: none;
    font-family: inherit;
    font-size: 14px;
    line-height: 1.4;
    max-height: 100px;
    min-height: 20px;
    color: #FFFFFF;
}

.message-input::placeholder {
    color: #a0aec0;
}

.send-btn {
    background: rgba(108, 93, 211, 0.8);
    backdrop-filter: blur(10px);
    border: 1px solid rgba(255, 255, 255, 0.2);
    color: #FFFFFF;
    cursor: pointer;
    padding: 8px 16px;
    border-radius: 20px;
    transition: all 0.2s;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 14px;
    font-weight: 500;
}

.send-btn:hover {
    background: rgba(108, 93, 211, 1);
    transform: scale(1.05);
    box-shadow: 0 4px 12px rgba(108, 93, 211, 0.4);
}

.send-btn:disabled {
    opacity: 0.5;
    cursor: not-allowed;
    transform: none;
}

.admin-chat-actions {
    margin-top: 8px;
    display: flex;
    justify-content: space-between;
    align-items: center;
}

.file-info {
    display: flex;
    align-items: center;
    gap: 8px;
    padding: 8px 12px;
    background: rgba(255, 255, 255, 0.1);
    backdrop-filter: blur(10px);
    border-radius: 8px;
    font-size: 14px;
    border: 1px solid rgba(255, 255, 255, 0.2);
}

.file-name {
    flex: 1;
    color: #FFFFFF;
}

.remove-file-btn {
    background: none;
    border: none;
    color: #ff6b6b;
    cursor: pointer;
    padding: 4px;
    border-radius: 4px;
    transition: background-color 0.2s;
}

.remove-file-btn:hover {
    background: rgba(255, 107, 107, 0.2);
}

.chat-tools {
    display: flex;
    gap: 8px;
}

.chat-tool-btn {
    background: rgba(255, 255, 255, 0.1);
    backdrop-filter: blur(10px);
    border: 1px solid rgba(255, 255, 255, 0.2);
    color: #a0aec0;
    cursor: pointer;
    padding: 8px 12px;
    border-radius: 6px;
    transition: all 0.2s;
    font-size: 14px;
}

.chat-tool-btn:hover {
    background: #e2e8f0;
    color: #334155;
}

.chat-loading {
    text-align: center;
    padding: 20px;
    color: #64748b;
}

.chat-error {
    text-align: center;
    padding: 20px;
    color: #dc2626;
    background: #fef2f2;
    border-radius: 8px;
    margin: 20px 0;
}

.admin-chat-messages::-webkit-scrollbar {
    width: 6px;
}

.admin-chat-messages::-webkit-scrollbar-track {
    background: #f1f5f9;
}

.admin-chat-messages::-webkit-scrollbar-thumb {
    background: #cbd5e1;
    border-radius: 3px;
}

.admin-chat-messages::-webkit-scrollbar-thumb:hover {
    background: #94a3b8;
}

@media (max-width: 768px) {
    .admin-chat-messages {
        height: 300px;
    }
    
    .admin-message-content {
        max-width: 85%;
    }
    
    .admin-chat-input-wrapper {
        padding: 6px 10px;
    }
    
    .admin-chat-header {
        flex-direction: column;
        gap: 8px;
        text-align: center;
    }
}
</style>

<script>
let adminChatSessions = {};
function initAdminChat(sessionId) {
    if (adminChatSessions[sessionId]) {
        return;
    }
    
    adminChatSessions[sessionId] = {
        sessionId: sessionId,
        isTyping: false,
        lastMessageId: 0,
        pollInterval: null
    };
    loadAdminChatMessages(sessionId);
    startAdminMessagePolling(sessionId);
    setupAdminChatEventListeners(sessionId);
}
function loadAdminChatMessages(sessionId) {
    const messagesContainer = document.getElementById(`admin-chat-messages-${sessionId}`);
    if (!messagesContainer) return;
    
    messagesContainer.innerHTML = '<div class="chat-loading">Nachrichten werden geladen...</div>';
    
    jQuery.ajax({
        url: nexora_chat_ajax.ajax_url,
        type: 'POST',
        data: {
            action: 'nexora_chat_get_messages',
            session_id: sessionId,
            nonce: nexora_chat_ajax.nonce
        },
        success: function(response) {
            if (response.success) {
                displayAdminMessages(sessionId, response.data);
                scrollAdminToBottom(sessionId);
            } else {
                messagesContainer.innerHTML = '<div class="chat-error">Fehler beim Laden der Nachrichten: ' + response.data + '</div>';
            }
        },
        error: function() {
            messagesContainer.innerHTML = '<div class="chat-error">Fehler beim Laden der Nachrichten.</div>';
        }
    });
}
function displayAdminMessages(sessionId, messages) {
    const messagesContainer = document.getElementById(`admin-chat-messages-${sessionId}`);
    if (!messagesContainer) return;
    
    if (messages.length === 0) {
        messagesContainer.innerHTML = '<div class="chat-loading">Noch keine Nachrichten. Starten Sie eine Unterhaltung mit dem Kunden!</div>';
        return;
    }
    
    let html = '';
    messages.forEach(function(message) {
        const isAdmin = message.sender_type === 'admin';
        const avatarText = isAdmin ? 'Admin' : 'Kunde';
        
        html += `
            <div class="admin-chat-message ${isAdmin ? 'admin' : 'user'}">
                <div class="admin-message-avatar ${isAdmin ? 'admin' : 'user'}">${avatarText.charAt(0)}</div>
                <div class="admin-message-content">
                    <div class="admin-message-text">${escapeHtml(message.message)}</div>
                    ${message.file_name ? `
                        <a href="${message.file_url}" target="_blank" class="admin-message-file">
                            <i class="fas fa-file file-icon"></i>
                            <span>${escapeHtml(message.file_name)}</span>
                        </a>
                    ` : ''}
                    <div class="admin-message-time">${message.time_formatted}</div>
                </div>
            </div>
        `;
    });
    
    messagesContainer.innerHTML = html;
}
function sendAdminChatMessage(sessionId) {
    const messageInput = document.getElementById(`admin-message-input-${sessionId}`);
    const fileInput = document.getElementById(`admin-file-input-${sessionId}`);
    const sendBtn = document.querySelector(`[onclick="sendAdminChatMessage(${sessionId})"]`);
    
    const message = messageInput.value.trim();
    const file = fileInput.files[0];
    
    if (!message && !file) {
        return;
    }
    sendBtn.disabled = true;
    
    const formData = new FormData();
    formData.append('action', 'nexora_chat_send_message');
    formData.append('session_id', sessionId);
    formData.append('message', message);
    formData.append('nonce', nexora_chat_ajax.nonce);
    
    if (file) {
        formData.append('file', file);
    }
    
    jQuery.ajax({
        url: nexora_chat_ajax.ajax_url,
        type: 'POST',
        data: formData,
        processData: false,
        contentType: false,
        success: function(response) {
            if (response.success) {
                messageInput.value = '';
                fileInput.value = '';
                removeAdminSelectedFile(sessionId);
                loadAdminChatMessages(sessionId);
            } else {
                alert('Fehler beim Senden der Nachricht: ' + response.data);
            }
        },
        error: function() {
            alert('Fehler beim Senden der Nachricht.');
        },
        complete: function() {
            sendBtn.disabled = false;
        }
    });
}
function setupAdminChatEventListeners(sessionId) {
    const messageInput = document.getElementById(`admin-message-input-${sessionId}`);
    const fileInput = document.getElementById(`admin-file-input-${sessionId}`);
    messageInput.addEventListener('keypress', function(e) {
        if (e.key === 'Enter' && !e.shiftKey) {
            e.preventDefault();
            sendAdminChatMessage(sessionId);
        }
    });
    messageInput.addEventListener('input', function() {
        this.style.height = 'auto';
        this.style.height = Math.min(this.scrollHeight, 100) + 'px';
    });
    fileInput.addEventListener('change', function() {
        if (this.files[0]) {
            showAdminSelectedFile(sessionId, this.files[0]);
        }
    });
}
function showAdminSelectedFile(sessionId, file) {
    const fileInfo = document.getElementById(`admin-file-info-${sessionId}`);
    const fileName = fileInfo.querySelector('.file-name');
    
    fileName.textContent = file.name;
    fileInfo.style.display = 'flex';
}
function removeAdminSelectedFile(sessionId) {
    const fileInput = document.getElementById(`admin-file-input-${sessionId}`);
    const fileInfo = document.getElementById(`admin-file-info-${sessionId}`);
    
    fileInput.value = '';
    fileInfo.style.display = 'none';
}
function startAdminMessagePolling(sessionId) {
    if (adminChatSessions[sessionId].pollInterval) {
        clearInterval(adminChatSessions[sessionId].pollInterval);
    }
    
    adminChatSessions[sessionId].pollInterval = setInterval(function() {
        checkForNewAdminMessages(sessionId);
    }, 3000);
}
function checkForNewAdminMessages(sessionId) {
    jQuery.ajax({
        url: nexora_chat_ajax.ajax_url,
        type: 'POST',
        data: {
            action: 'nexora_chat_get_messages',
            session_id: sessionId,
            nonce: nexora_chat_ajax.nonce
        },
        success: function(response) {
            if (response.success && response.data.length > 0) {
                const lastMessage = response.data[response.data.length - 1];
                if (lastMessage.id > adminChatSessions[sessionId].lastMessageId) {
                    displayAdminMessages(sessionId, response.data);
                    scrollAdminToBottom(sessionId);
                    adminChatSessions[sessionId].lastMessageId = lastMessage.id;
                }
            }
        }
    });
}
function scrollAdminToBottom(sessionId) {
    const messagesContainer = document.getElementById(`admin-chat-messages-${sessionId}`);
    if (messagesContainer) {
        messagesContainer.scrollTop = messagesContainer.scrollHeight;
    }
}
function markAllAsRead(sessionId) {
    jQuery.ajax({
        url: nexora_chat_ajax.ajax_url,
        type: 'POST',
        data: {
            action: 'nexora_chat_mark_read',
            session_id: sessionId,
            nonce: nexora_chat_ajax.nonce
        },
        success: function(response) {
            if (response.success) {
                console.log('Messages marked as read');
            }
        }
    });
}
function refreshChat(sessionId) {
    loadAdminChatMessages(sessionId);
}
function escapeHtml(text) {
    const div = document.createElement('div');
    div.textContent = text;
    return div.innerHTML;
}
document.addEventListener('DOMContentLoaded', function() {
    const chatContainer = document.querySelector('.Nexora Service Suite-admin-chat-container');
    if (chatContainer) {
        const sessionId = chatContainer.dataset.sessionId;
        initAdminChat(sessionId);
    }
});
</script>
