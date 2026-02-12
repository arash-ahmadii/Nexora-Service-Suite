
window.NexoraChat = {
    config: {
        pollInterval: 8000,
        maxFileSize: 5 * 1024 * 1024,
        allowedFileTypes: ['jpg', 'jpeg', 'png', 'gif', 'pdf', 'doc', 'docx', 'txt'],
        maxMessageLength: 1000
    },
    sessions: {},
    isInitialized: false
};

function initNexoraChat() {
    if (window.NexoraChat.isInitialized) {
        return;
    }
    if (typeof jQuery === 'undefined') {
        console.error('Nexora Service Suite Chat: jQuery is required but not loaded');
        return;
    }
    if (typeof nexora_chat_ajax === 'undefined') {
        console.error('Nexora Service Suite Chat: AJAX configuration not found');
        return;
    }
    
    window.NexoraChat.isInitialized = true;
    console.log('Nexora Service Suite Chat System initialized');
}

function initUserChat(sessionId) {
    if (window.NexoraChat.sessions[sessionId]) {
        return;
    }
    
    window.NexoraChat.sessions[sessionId] = {
        sessionId: sessionId,
        isTyping: false,
        lastMessageId: 0,
        pollInterval: null,
        type: 'user',
        intervalMs: 8000
    };
    
    loadChatMessages(sessionId);
    startMessagePolling(sessionId);
    setupUserChatEventListeners(sessionId);
    
    console.log('User chat initialized for session:', sessionId);
}

function initAdminChat(sessionId) {
    if (window.NexoraChat.sessions[sessionId]) {
        return;
    }
    
    window.NexoraChat.sessions[sessionId] = {
        sessionId: sessionId,
        isTyping: false,
        lastMessageId: 0,
        pollInterval: null,
        type: 'admin',
        intervalMs: 3000
    };
    
    loadChatMessages(sessionId);
    startMessagePolling(sessionId);
    setupAdminChatEventListeners(sessionId);
    
    console.log('Admin chat initialized for session:', sessionId);
}

function loadChatMessages(sessionId) {
    const session = window.NexoraChat.sessions[sessionId];
    if (!session) return;
    
    const messagesContainer = document.getElementById(`chat-messages-${sessionId}`) || 
                             document.getElementById(`admin-chat-messages-${sessionId}`);
    
    if (!messagesContainer) return;
    
    messagesContainer.innerHTML = '<div class="chat-loading">Loading messages...</div>';
    
    jQuery.ajax({
        url: nexora_chat_ajax.ajax_url,
        type: 'POST',
        data: {
            action: 'nexora_chat_get_messages',
            session_id: sessionId,
            nonce: nexora_chat_ajax.nonce || nexora_chat_ajax.user_nonce || nexora_chat_ajax.chat_nonce
        },
        success: function(response) {
            if (response.success) {
                displayMessages(sessionId, response.data);
                scrollToBottom(sessionId);
            } else {
                messagesContainer.innerHTML = '<div class="chat-error">Error loading messages: ' + response.data + '</div>';
            }
        },
        error: function(xhr, status, error) {
            console.error('Chat load error:', error);
            messagesContainer.innerHTML = '<div class="chat-error">Error loading messages.</div>';
        }
    });
}

function displayMessages(sessionId, messages) {
    const session = window.NexoraChat.sessions[sessionId];
    if (!session) return;
    
    const messagesContainer = document.getElementById(`chat-messages-${sessionId}`) || 
                             document.getElementById(`admin-chat-messages-${sessionId}`);
    
    if (!messagesContainer) return;
    
    if (messages.length === 0) {
        const emptyMessage = session.type === 'admin' ? 
            'No messages yet. Start a conversation with the customer!' :
            'No messages yet. Start a conversation!';
        messagesContainer.innerHTML = '<div class="chat-loading">' + emptyMessage + '</div>';
        return;
    }
    
    let html = '';
    messages.forEach(function(message) {
        const isUser = message.sender_type === 'user';
        const isAdmin = message.sender_type === 'admin';
        let avatarText, avatarClass, messageClass;
        if (session.type === 'admin') {
            avatarText = isAdmin ? 'Admin' : 'Customer';
            avatarClass = isAdmin ? 'admin' : 'user';
            messageClass = isAdmin ? 'admin' : 'user';
        } else {
            avatarText = isUser ? 'Sie' : 'Support';
            avatarClass = isUser ? 'user' : 'admin';
            messageClass = isUser ? 'user' : 'admin';
        }
        
        const containerClass = session.type === 'admin' ? 'admin-chat-message' : 'chat-message';
        const contentClass = session.type === 'admin' ? 'admin-message-content' : 'message-content';
        const textClass = session.type === 'admin' ? 'admin-message-text' : 'message-text';
        const timeClass = session.type === 'admin' ? 'admin-message-time' : 'message-time';
        const fileClass = session.type === 'admin' ? 'admin-message-file' : 'message-file';
        const avatarElementClass = session.type === 'admin' ? 'admin-message-avatar' : 'message-avatar';
        let messageContent;
        console.log('Message type:', message.message_type);
        console.log('Message content:', message.message);
        
        if (message.message_type === 'service_approval') {
            messageContent = message.message;
            console.log('Rendering service approval card as HTML');
        } else if (message.message && message.message.includes('service-approval-card')) {
            messageContent = message.message;
            console.log('Fallback: treating message as HTML due to service-approval-card content');
        } else {
            messageContent = escapeHtml(message.message);
            console.log('Escaping regular message');
        }
        
        html += `
            <div class="${containerClass} ${messageClass}">
                <div class="${avatarElementClass} ${avatarClass}">${avatarText.charAt(0)}</div>
                <div class="${contentClass}">
                    <div class="${textClass}">${messageContent}</div>
                    ${message.file_name ? `
                        <a href="${message.file_url}" target="_blank" class="${fileClass}">
                            <i class="fas fa-file file-icon"></i>
                            <span>${escapeHtml(message.file_name)}</span>
                        </a>
                    ` : ''}
                    <div class="${timeClass}">${message.time_formatted}</div>
                </div>
            </div>
        `;
    });
    
    messagesContainer.innerHTML = html;
    if (messages.length > 0) {
        session.lastMessageId = messages[messages.length - 1].id;
    }
}

function sendChatMessage(sessionId) {
    const session = window.NexoraChat.sessions[sessionId];
    if (!session) return;
    
    const messageInput = document.getElementById(`message-input-${sessionId}`) || 
                        document.getElementById(`admin-message-input-${sessionId}`);
    const fileInput = document.getElementById(`file-input-${sessionId}`) || 
                     document.getElementById(`admin-file-input-${sessionId}`);
    const sendBtn = document.querySelector(`[onclick="sendChatMessage(${sessionId})"]`) ||
                   document.querySelector(`[onclick="sendAdminChatMessage(${sessionId})"]`);
    
    const message = messageInput.value.trim();
    const file = fileInput ? fileInput.files[0] : null;
    
    if (!message && !file) {
        return;
    }
    if (message.length > window.NexoraChat.config.maxMessageLength) {
        alert('Nachricht ist zu lang (max. ' + window.NexoraChat.config.maxMessageLength + ' Zeichen)');
        return;
    }
    sendBtn.disabled = true;
    
    const formData = new FormData();
    formData.append('action', 'nexora_chat_send_message');
    formData.append('session_id', sessionId);
    formData.append('message', message);
    formData.append('nonce', nexora_chat_ajax.nonce || nexora_chat_ajax.user_nonce || nexora_chat_ajax.chat_nonce);
    
    if (file) {
        const validation = validateFile(file);
        if (!validation.valid) {
            alert(validation.error);
            sendBtn.disabled = false;
            return;
        }
        formData.append('file', file);
    }
    
    console.log('Sending chat message:', {
        sessionId: sessionId,
        message: message,
        hasFile: !!file,
        nonce: nexora_chat_ajax.nonce || nexora_chat_ajax.user_nonce || nexora_chat_ajax.chat_nonce
    });
    
    jQuery.ajax({
        url: nexora_chat_ajax.ajax_url,
        type: 'POST',
        data: formData,
        processData: false,
        contentType: false,
        success: function(response) {
            console.log('Chat send response:', response);
            if (response.success) {
                messageInput.value = '';
                if (fileInput) {
                    fileInput.value = '';
                }
                removeSelectedFile(sessionId);
                loadChatMessages(sessionId);
            } else {
                console.error('Chat send error response:', response);
                alert('Fehler beim Senden der Nachricht: ' + response.data);
            }
        },
        error: function(xhr, status, error) {
            console.error('Chat send error:', error, xhr.responseText);
            alert('Fehler beim Senden der Nachricht: ' + error);
        },
        complete: function() {
            sendBtn.disabled = false;
        }
    });
}

function setupUserChatEventListeners(sessionId) {
    const messageInput = document.getElementById(`message-input-${sessionId}`);
    const fileInput = document.getElementById(`file-input-${sessionId}`);
    
    if (messageInput) {
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
    
    if (fileInput) {
        fileInput.addEventListener('change', function() {
            if (this.files[0]) {
                showSelectedFile(sessionId, this.files[0]);
            }
        });
    }
}

function setupAdminChatEventListeners(sessionId) {
    const messageInput = document.getElementById(`admin-message-input-${sessionId}`);
    const fileInput = document.getElementById(`admin-file-input-${sessionId}`);
    
    if (messageInput) {
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
    
    if (fileInput) {
        fileInput.addEventListener('change', function() {
            if (this.files[0]) {
                showSelectedFile(sessionId, this.files[0]);
            }
        });
    }
}

function showSelectedFile(sessionId, file) {
    const session = window.NexoraChat.sessions[sessionId];
    if (!session) return;
    
    const fileInfo = document.getElementById(`file-info-${sessionId}`) || 
                    document.getElementById(`admin-file-info-${sessionId}`);
    const fileName = fileInfo ? fileInfo.querySelector('.file-name') : null;
    
    if (fileName) {
        fileName.textContent = file.name;
        fileInfo.style.display = 'flex';
    }
}

function removeSelectedFile(sessionId) {
    const session = window.NexoraChat.sessions[sessionId];
    if (!session) return;
    
    const fileInput = document.getElementById(`file-input-${sessionId}`) || 
                     document.getElementById(`admin-file-input-${sessionId}`);
    const fileInfo = document.getElementById(`file-info-${sessionId}`) || 
                    document.getElementById(`admin-file-info-${sessionId}`);
    
    if (fileInput) {
        fileInput.value = '';
    }
    if (fileInfo) {
        fileInfo.style.display = 'none';
    }
}

function startMessagePolling(sessionId) {
    const session = window.NexoraChat.sessions[sessionId];
    if (!session) return;
    
    if (session.pollInterval) {
        clearInterval(session.pollInterval);
    }
    
    const intervalMs = session.intervalMs || window.NexoraChat.config.pollInterval;
    session.pollInterval = setInterval(function() {
        checkForNewMessages(sessionId);
    }, intervalMs);
}

function checkForNewMessages(sessionId) {
    const session = window.NexoraChat.sessions[sessionId];
    if (!session) return;
    
    jQuery.ajax({
        url: nexora_chat_ajax.ajax_url,
        type: 'POST',
        data: {
            action: 'nexora_chat_get_messages',
            session_id: sessionId,
            nonce: nexora_chat_ajax.nonce || nexora_chat_ajax.user_nonce || nexora_chat_ajax.chat_nonce
        },
        success: function(response) {
            if (response.success && response.data.length > 0) {
                const lastMessage = response.data[response.data.length - 1];
                if (lastMessage.id > session.lastMessageId) {
                    displayMessages(sessionId, response.data);
                    scrollToBottom(sessionId);
                    session.lastMessageId = lastMessage.id;
                }
            }
        },
        error: function(xhr, status, error) {
            console.error('Chat poll error:', error);
        }
    });
}

function scrollToBottom(sessionId) {
    const session = window.NexoraChat.sessions[sessionId];
    if (!session) return;
    
    const messagesContainer = document.getElementById(`chat-messages-${sessionId}`) || 
                             document.getElementById(`admin-chat-messages-${sessionId}`);
    
    if (messagesContainer) {
        messagesContainer.scrollTop = messagesContainer.scrollHeight;
    }
}

function markMessagesAsRead(sessionId) {
    jQuery.ajax({
        url: nexora_chat_ajax.ajax_url,
        type: 'POST',
        data: {
            action: 'nexora_chat_mark_read',
            session_id: sessionId,
            nonce: nexora_chat_ajax.nonce || nexora_chat_ajax.user_nonce || nexora_chat_ajax.chat_nonce
        },
        success: function(response) {
            if (response.success) {
                console.log('Messages marked as read');
            }
        },
        error: function(xhr, status, error) {
            console.error('Mark read error:', error);
        }
    });
}

function refreshChat(sessionId) {
    loadChatMessages(sessionId);
}

function validateFile(file) {
    const allowedTypes = window.NexoraChat.config.allowedFileTypes;
    const maxSize = window.NexoraChat.config.maxFileSize;
    if (file.size > maxSize) {
        return {
            valid: false,
            error: 'Datei ist zu groß (max. 5MB)'
        };
    }
    const fileExtension = file.name.split('.').pop().toLowerCase();
    if (!allowedTypes.includes(fileExtension)) {
        return {
            valid: false,
            error: 'Dateityp nicht erlaubt. Erlaubte Typen: ' + allowedTypes.join(', ')
        };
    }
    
    return { valid: true };
}

function escapeHtml(text) {
    const div = document.createElement('div');
    div.textContent = text;
    return div.innerHTML;
}

function formatFileSize(bytes) {
    if (bytes === 0) return '0 Bytes';
    
    const k = 1024;
    const sizes = ['Bytes', 'KB', 'MB', 'GB'];
    const i = Math.floor(Math.log(bytes) / Math.log(k));
    
    return parseFloat((bytes / Math.pow(k, i)).toFixed(2)) + ' ' + sizes[i];
}

function getFileTypeIcon(filename) {
    const extension = filename.split('.').pop().toLowerCase();
    
    const iconMap = {
        'jpg': 'fa-image',
        'jpeg': 'fa-image',
        'png': 'fa-image',
        'gif': 'fa-image',
        'pdf': 'fa-file-pdf',
        'doc': 'fa-file-word',
        'docx': 'fa-file-word',
        'txt': 'fa-file-alt'
    };
    
    return iconMap[extension] || 'fa-file';
}

function cleanupChatSession(sessionId) {
    const session = window.NexoraChat.sessions[sessionId];
    if (session && session.pollInterval) {
        clearInterval(session.pollInterval);
        delete window.NexoraChat.sessions[sessionId];
    }
}
function sendAdminChatMessage(sessionId) {
    sendChatMessage(sessionId);
}

function removeAdminSelectedFile(sessionId) {
    removeSelectedFile(sessionId);
}

function markAllAsRead(sessionId) {
    markMessagesAsRead(sessionId);
}
document.addEventListener('DOMContentLoaded', function() {
    initNexoraChat();
    const userChatContainers = document.querySelectorAll('.Nexora Service Suite-chat-container');
    if ('IntersectionObserver' in window) {
        const observer = new IntersectionObserver((entries, obs) => {
            entries.forEach(entry => {
                if (entry.isIntersecting) {
                    const container = entry.target;
                    const sessionId = container.dataset.sessionId;
                    if (sessionId) {
                        initUserChat(sessionId);
                        obs.unobserve(container);
                    }
                }
            });
        }, { root: null, rootMargin: '0px', threshold: 0.1 });
        userChatContainers.forEach(c => observer.observe(c));
    } else {
        userChatContainers.forEach(function(container) {
            const sessionId = container.dataset.sessionId;
            if (!sessionId) return;
            const initOnFirstInteraction = () => {
                initUserChat(sessionId);
                container.removeEventListener('click', initOnFirstInteraction);
            };
            container.addEventListener('click', initOnFirstInteraction);
        });
    }
    const adminChatContainers = document.querySelectorAll('.Nexora Service Suite-admin-chat-container');
    adminChatContainers.forEach(function(container) {
        const sessionId = container.dataset.sessionId;
        if (sessionId) {
            initAdminChat(sessionId);
        }
    });
});
window.addEventListener('beforeunload', function() {
    Object.keys(window.NexoraChat.sessions).forEach(function(sessionId) {
        cleanupChatSession(sessionId);
    });
});
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
    console.log('approveService called with requestId:', requestId, 'tableType:', tableType);
    
    if (window.customerServiceApproval) {
        console.log('Using customerServiceApproval system');
        window.customerServiceApproval.handleApproveService(requestId, tableType);
    } else {
        console.log('Using fallback approve system');
        const formData = new FormData();
        formData.append('action', 'nexora_approve_service');
        formData.append('request_id', requestId);
        formData.append('customer_note', '');
        formData.append('table_type', tableType);
        formData.append('nonce', nexora_chat_ajax.nonce || nexora_chat_ajax.user_nonce || nexora_chat_ajax.chat_nonce);
        
        console.log('Sending approve AJAX request...');
        
        jQuery.ajax({
            url: nexora_chat_ajax.ajax_url,
            type: 'POST',
            data: formData,
            processData: false,
            contentType: false,
            success: function(response) {
                console.log('Approve AJAX response:', response);
                if (response.success) {
                    showNotification('Service erfolgreich genehmigt!', 'success');
                    location.reload();
                } else {
                    showNotification('Fehler: ' + response.data, 'error');
                }
            },
            error: function(xhr, status, error) {
                console.error('Approve AJAX error:', {xhr, status, error});
                showNotification('Fehler beim Genehmigen des Services.', 'error');
            }
        });
    }
};

window.rejectService = function(requestId, tableType = 'complete_service_requests') {
    console.log('rejectService called with requestId:', requestId, 'tableType:', tableType);
    
    if (window.customerServiceApproval) {
        console.log('Using customerServiceApproval system');
        window.customerServiceApproval.handleRejectService(requestId, tableType);
    } else {
        console.log('Using fallback reject system');
        const formData = new FormData();
        formData.append('action', 'nexora_reject_service');
        formData.append('request_id', requestId);
        formData.append('customer_note', '');
        formData.append('table_type', tableType);
        formData.append('nonce', nexora_chat_ajax.nonce || nexora_chat_ajax.user_nonce || nexora_chat_ajax.chat_nonce);
        
        console.log('Sending reject AJAX request...');
        
        jQuery.ajax({
            url: nexora_chat_ajax.ajax_url,
            type: 'POST',
            data: formData,
            processData: false,
            contentType: false,
            success: function(response) {
                console.log('Reject AJAX response:', response);
                if (response.success) {
                    showNotification('Service erfolgreich abgelehnt!', 'success');
                    location.reload();
                } else {
                    showNotification('Fehler: ' + response.data, 'error');
                }
            },
            error: function(xhr, status, error) {
                console.error('Reject AJAX error:', {xhr, status, error});
                showNotification('Fehler beim Ablehnen des Services.', 'error');
            }
        });
    }
};
