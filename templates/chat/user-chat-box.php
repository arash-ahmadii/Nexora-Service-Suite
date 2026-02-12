<?php

if (!defined('ABSPATH')) {
    exit;
}

$request_id = isset($request_id) ? intval($request_id) : 0;
if (!$request_id) {
    return;
}
$chat_manager = new Nexora_Chat_Manager();
$user_id = get_current_user_id();
$session = $chat_manager->get_or_create_session($request_id, $user_id);

if (!$session) {
    echo '<div class="chat-error">Fehler beim Laden des Chats. Request ID: ' . $request_id . ', User ID: ' . $user_id . '</div>';
    return;
}

$session_id = $session->id;
$user_id = get_current_user_id();
?>

<div class="Nexora Service Suite-chat-container" data-session-id="<?php echo esc_attr($session_id); ?>" data-request-id="<?php echo esc_attr($request_id); ?>">
    <div class="chat-header">
        <div class="chat-title">
            <i class="fas fa-comments"></i>
            <span>Chat mit Support</span>
        </div>
        <div class="chat-status">
            <span class="status-indicator online"></span>
            <span class="status-text">Online</span>
        </div>
    </div>
    
    <div class="chat-messages" id="chat-messages-<?php echo esc_attr($session_id); ?>">
        
    </div>
    
    <div class="chat-input-container">
        <div class="chat-input-wrapper">
            <div class="message-input-wrapper">
                <textarea 
                    id="message-input-<?php echo esc_attr($session_id); ?>" 
                    class="message-input" 
                    placeholder="Nachricht eingeben..."
                    rows="1"
                ></textarea>
                <button type="button" class="send-btn" onclick="sendChatMessage(<?php echo esc_attr($session_id); ?>)">
                    Senden
                </button>
            </div>
        </div>
        
        <div class="chat-actions">
            
        </div>
    </div>
</div>

<script>
let chatSessions = {};
function initChat(sessionId) {
    if (chatSessions[sessionId]) {
        return;
    }
    
    chatSessions[sessionId] = {
        sessionId: sessionId,
        isTyping: false,
        lastMessageId: 0,
        pollInterval: null
    };
    loadChatMessages(sessionId);
    startMessagePolling(sessionId);
    setupChatEventListeners(sessionId);
}
function loadChatMessages(sessionId) {
    const messagesContainer = document.getElementById(`chat-messages-${sessionId}`);
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
                displayMessages(sessionId, response.data);
                scrollToBottom(sessionId);
            } else {
                messagesContainer.innerHTML = '<div class="chat-error">Fehler beim Laden der Nachrichten: ' + response.data + '</div>';
            }
        },
        error: function() {
            messagesContainer.innerHTML = '<div class="chat-error">Fehler beim Laden der Nachrichten.</div>';
        }
    });
}
function displayMessages(sessionId, messages) {
    const messagesContainer = document.getElementById(`chat-messages-${sessionId}`);
    if (!messagesContainer) return;
    
    if (messages.length === 0) {
        messagesContainer.innerHTML = '<div class="chat-loading">Noch keine Nachrichten. Starten Sie eine Unterhaltung!</div>';
        return;
    }
    
    let html = '';
    messages.forEach(function(message) {
        const isUser = message.sender_type === 'user';
        const avatarText = isUser ? 'Sie' : 'Support';
        
        html += `
            <div class="chat-message ${isUser ? 'user' : 'admin'}">
                <div class="message-avatar ${isUser ? 'user' : 'admin'}">${avatarText.charAt(0)}</div>
                <div class="message-content">
                    <div class="message-text">${escapeHtml(message.message)}</div>
                    ${message.file_name ? `
                        <a href="${message.file_url}" target="_blank" class="message-file">
                            <i class="fas fa-file file-icon"></i>
                            <span>${escapeHtml(message.file_name)}</span>
                        </a>
                    ` : ''}
                    <div class="message-time">${message.time_formatted}</div>
                </div>
            </div>
        `;
    });
    
    messagesContainer.innerHTML = html;
}
function sendChatMessage(sessionId) {
    const messageInput = document.getElementById(`message-input-${sessionId}`);
    const sendBtn = messageInput.closest('.message-input-wrapper').querySelector('.send-btn');
    
    const message = messageInput.value.trim();
    
    if (!message) {
        return;
    }
    if (typeof nexora_chat_ajax === 'undefined') {
        alert('Chat system not initialized properly');
        return;
    }
    sendBtn.disabled = true;
    
    const formData = new FormData();
    formData.append('action', 'nexora_chat_send_message');
    formData.append('session_id', sessionId);
    formData.append('message', message);
    formData.append('nonce', nexora_chat_ajax.nonce);
    
    jQuery.ajax({
        url: nexora_chat_ajax.ajax_url,
        type: 'POST',
        data: formData,
        processData: false,
        contentType: false,
        success: function(response) {
            if (response.success) {
                messageInput.value = '';
                loadChatMessages(sessionId);
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
    function setupChatEventListeners(sessionId) {
        const messageInput = document.getElementById(`message-input-${sessionId}`);
        messageInput.addEventListener('keypress', function(e) {
            if (e.key === 'Enter' && !e.shiftKey) {
                e.preventDefault();
                sendChatMessage(sessionId);
            }
        });
        messageInput.addEventListener('input', function() {
            this.style.height = 'auto';
            this.style.height = Math.min(this.scrollHeight, 100) + 'px';
        });
    }
function startMessagePolling(sessionId) {
    if (chatSessions[sessionId].pollInterval) {
        clearInterval(chatSessions[sessionId].pollInterval);
    }
    
    chatSessions[sessionId].pollInterval = setInterval(function() {
        checkForNewMessages(sessionId);
    }, 3000);
}
function checkForNewMessages(sessionId) {
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
                if (lastMessage.id > chatSessions[sessionId].lastMessageId) {
                    displayMessages(sessionId, response.data);
                    scrollToBottom(sessionId);
                    chatSessions[sessionId].lastMessageId = lastMessage.id;
                }
            }
        }
    });
}
function scrollToBottom(sessionId) {
    const messagesContainer = document.getElementById(`chat-messages-${sessionId}`);
    if (messagesContainer) {
        messagesContainer.scrollTop = messagesContainer.scrollHeight;
    }
}
function escapeHtml(text) {
    const div = document.createElement('div');
    div.textContent = text;
    return div.innerHTML;
}
document.addEventListener('DOMContentLoaded', function() {
    const chatContainer = document.querySelector('.Nexora Service Suite-chat-container');
    
    if (chatContainer) {
        const sessionId = chatContainer.dataset.sessionId;
        if (typeof nexora_chat_ajax === 'undefined') {
            window.nexora_chat_ajax = {
                ajax_url: '<?php echo admin_url('admin-ajax.php'); ?>',
                nonce: '<?php echo wp_create_nonce('nexora_nonce'); ?>'
            };
        }
        
        initChat(sessionId);
    }
});
</script>
