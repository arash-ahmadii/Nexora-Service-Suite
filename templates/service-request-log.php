<?php
if (!defined('ABSPATH')) exit;
include __DIR__ . '/service-request-form/service-request-form-data.php';

$request_id = isset($_GET['id']) ? intval($_GET['id']) : 0;
if (!$request_id) {
    wp_die('Ungültige Anfrage-ID');
}
global $wpdb;
$request = $wpdb->get_row($wpdb->prepare(
    "SELECT r.*, u.display_name, ss.title as status_title
     FROM {$wpdb->prefix}nexora_service_requests r
     LEFT JOIN {$wpdb->users} u ON r.user_id = u.ID
     LEFT JOIN {$wpdb->prefix}nexora_service_status ss ON r.status_id = ss.id
     WHERE r.id = %d",
    $request_id
));

if (!$request) {
    wp_die('Anfrage nicht gefunden');
}
?>

<div class="wrap Nexora Service Suite-admin">
    <?php
    $admin_menu = new Nexora_Admin_Menu();
    $admin_menu->render_admin_header();
    ?>
    
    <?php echo do_shortcode('[nexora_approval_banner]'); ?>
    
    <div class="Nexora Service Suite-log-container">
        
        <div class="Nexora Service Suite-request-header">
            <div class="request-info">
                <h1>📋 Aktivitäts-Log: Anfrage #<?php echo $request_id; ?></h1>
                <div class="request-details">
                    <div class="detail-item">
                        <span class="label">Modell:</span>
                        <span class="value"><?php echo esc_html($request->model ?: '-'); ?></span>
                    </div>
                    <div class="detail-item">
                        <span class="label">Kunde:</span>
                        <span class="value"><?php echo esc_html($request->display_name ?: '-'); ?></span>
                    </div>
                    <div class="detail-item">
                        <span class="label">Status:</span>
                        <span class="value status-<?php echo $request->status_id; ?>"><?php echo esc_html($request->status_title ?: '-'); ?></span>
                    </div>
                    <div class="detail-item">
                        <span class="label">Erstellt:</span>
                        <span class="value"><?php echo esc_html($request->created_at ?: '-'); ?></span>
                    </div>
                </div>
            </div>
            <div class="request-actions">
                <a href="<?php echo admin_url('admin.php?page=nexora_service_request_form&id=' . $request_id); ?>" class="Nexora Service Suite-btn Nexora Service Suite-btn-primary">
                    <span class="btn-icon">✏️</span>
                    Anfrage bearbeiten
                </a>
                <button id="Nexora Service Suite-export-logs" class="Nexora Service Suite-btn Nexora Service Suite-btn-secondary">
                    <span class="btn-icon">📥</span>
                    Logs exportieren
                </button>
                <button id="Nexora Service Suite-test-log" class="Nexora Service Suite-btn Nexora Service Suite-btn-secondary">
                    <span class="btn-icon">🧪</span>
                    Test Log
                </button>
            </div>
        </div>

        
        <div class="Nexora Service Suite-logs-container">
            <div class="logs-header">
                <h2>📜 Aktivitäts-Verlauf</h2>
                <div class="logs-info">
                    <span id="Nexora Service Suite-logs-count">Lade...</span>
                </div>
            </div>
            
            <div id="Nexora Service Suite-logs-list" class="logs-list">
                <div class="loading-spinner">
                    <div class="spinner"></div>
                    <span>Lade Aktivitäts-Logs...</span>
                </div>
            </div>
            
            
            <div class="Nexora Service Suite-logs-pagination">
                <button id="Nexora Service Suite-prev-logs" class="Nexora Service Suite-btn Nexora Service Suite-btn-secondary" disabled>
                    <span class="btn-icon">←</span>
                    Vorherige
                </button>
                
                <div class="pagination-info">
                    <span id="Nexora Service Suite-logs-page-info">Seite 1 von 1</span>
                </div>
                
                <button id="Nexora Service Suite-next-logs" class="Nexora Service Suite-btn Nexora Service Suite-btn-secondary" disabled>
                    Nächste
                    <span class="btn-icon">→</span>
                </button>
            </div>
        </div>
    </div>
</div>

<style>

.Nexora Service Suite-log-container {
    max-width: 1200px;
    margin: 0 auto;
}

.Nexora Service Suite-request-header {
    background: white;
    border-radius: 12px;
    padding: 24px;
    margin-bottom: 24px;
    box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
    display: flex;
    justify-content: space-between;
    align-items: flex-start;
    flex-wrap: wrap;
    gap: 20px;
}

.request-info h1 {
    margin: 0 0 16px 0;
    color: var(--text-primary);
    font-size: 1.5rem;
}

.request-details {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
    gap: 12px;
}

.detail-item {
    display: flex;
    align-items: center;
    gap: 8px;
}

.detail-item .label {
    font-weight: 600;
    color: var(--text-secondary);
    min-width: 80px;
}

.detail-item .value {
    color: var(--text-primary);
}

.status-1 { color: #d97706; }
.status-2 { color: #2563eb; }
.status-3 { color: #059669; }
.status-4 { color: #dc2626; }

.request-actions {
    display: flex;
    gap: 12px;
    flex-wrap: wrap;
}

.Nexora Service Suite-logs-container {
    background: white;
    border-radius: 12px;
    padding: 24px;
    box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
}

.logs-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 24px;
    padding-bottom: 16px;
    border-bottom: 1px solid var(--border-color);
}

.logs-header h2 {
    margin: 0;
    color: var(--text-primary);
}

.logs-info {
    color: var(--text-secondary);
    font-size: 0.875rem;
}

.logs-list {
    min-height: 400px;
}

.loading-spinner {
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    padding: 60px 20px;
    color: var(--text-secondary);
}

.spinner {
    width: 40px;
    height: 40px;
    border: 3px solid #f3f3f3;
    border-top: 3px solid var(--primary-color);
    border-radius: 50%;
    animation: spin 1s linear infinite;
    margin-bottom: 16px;
}

@keyframes spin {
    0% { transform: rotate(0deg); }
    100% { transform: rotate(360deg); }
}

.log-item {
    display: flex;
    align-items: flex-start;
    gap: 16px;
    padding: 16px;
    border-radius: 8px;
    margin-bottom: 12px;
    background: #f8fafc;
    border-left: 4px solid var(--primary-color);
    transition: all 0.2s ease;
}

.log-item:hover {
    background: #f1f5f9;
    transform: translateX(4px);
}

.log-icon {
    font-size: 1.25rem;
    margin-top: 2px;
    flex-shrink: 0;
}

.log-content {
    flex: 1;
}

.log-header {
    display: flex;
    justify-content: space-between;
    align-items: flex-start;
    margin-bottom: 8px;
    flex-wrap: wrap;
    gap: 8px;
}

.log-description {
    font-weight: 500;
    color: var(--text-primary);
    margin: 0;
}

.log-meta {
    display: flex;
    gap: 16px;
    font-size: 0.875rem;
    color: var(--text-secondary);
    flex-wrap: wrap;
}

.log-user {
    display: flex;
    align-items: center;
    gap: 4px;
}

.log-user::before {
    content: '👤';
}

.log-time {
    display: flex;
    align-items: center;
    gap: 4px;
}

.log-time::before {
    content: '🕒';
}

.log-ip {
    display: flex;
    align-items: center;
    gap: 4px;
}

.log-ip::before {
    content: '🌐';
}

.log-details {
    margin-top: 8px;
    padding: 12px;
    background: white;
    border-radius: 6px;
    border: 1px solid var(--border-color);
    font-size: 0.875rem;
    color: var(--text-secondary);
}

.log-details strong {
    color: var(--text-primary);
}

.Nexora Service Suite-logs-pagination {
    display: flex;
    justify-content: center;
    align-items: center;
    gap: 16px;
    margin-top: 24px;
    padding-top: 24px;
    border-top: 1px solid var(--border-color);
}

.pagination-info {
    color: var(--text-secondary);
    font-size: 0.875rem;
}

.no-logs {
    text-align: center;
    padding: 60px 20px;
    color: var(--text-secondary);
}

.no-logs .icon {
    font-size: 3rem;
    margin-bottom: 16px;
}

.no-logs h3 {
    margin: 0 0 8px 0;
    color: var(--text-primary);
}

.no-logs p {
    margin: 0;
}

@media (max-width: 768px) {
    .Nexora Service Suite-request-header {
        flex-direction: column;
        align-items: stretch;
    }
    
    .request-actions {
        justify-content: stretch;
    }
    
    .request-actions .Nexora Service Suite-btn {
        flex: 1;
        justify-content: center;
    }
    
    .logs-header {
        flex-direction: column;
        align-items: flex-start;
        gap: 12px;
    }
    
    .log-header {
        flex-direction: column;
        align-items: flex-start;
    }
    
    .log-meta {
        flex-direction: column;
        gap: 8px;
    }
}
</style>

<script>
jQuery(document).ready(function($) {
    const ajaxUrl = '<?php echo admin_url('admin-ajax.php'); ?>';
    const nonce = '<?php echo wp_create_nonce('nexora_nonce'); ?>';
    const requestId = <?php echo $request_id; ?>;
    
    let currentPage = 1;
    let totalPages = 1;
    
    function loadLogs(page = 1) {
        $('#Nexora Service Suite-logs-list').html(`
            <div class="loading-spinner">
                <div class="spinner"></div>
                <span>Lade Aktivitäts-Logs...</span>
            </div>
        `);
        
        $.ajax({
            url: ajaxUrl,
            type: 'POST',
            data: {
                action: 'nexora_get_request_logs',
                request_id: requestId,
                page: page,
                per_page: 20,
                nonce: nonce
            },
            success: function(response) {
                if (response.success) {
                    const logs = response.data.logs;
                    currentPage = response.data.page;
                    totalPages = response.data.total_pages;
                    $('#Nexora Service Suite-logs-page-info').text(`Seite ${currentPage} von ${totalPages}`);
                    $('#Nexora Service Suite-prev-logs').prop('disabled', currentPage <= 1);
                    $('#Nexora Service Suite-next-logs').prop('disabled', currentPage >= totalPages);
                    $('#Nexora Service Suite-logs-count').text(`${response.data.total} Aktivitäten gefunden`);
                    
                    if (logs.length > 0) {
                        let html = '';
                        logs.forEach(log => {
                            const actionLabel = getActionTypeLabel(log.action_type);
                            const actionIcon = getActionTypeIcon(log.action_type);
                            
                            html += `
                                <div class="log-item">
                                    <div class="log-icon">${actionIcon}</div>
                                    <div class="log-content">
                                        <div class="log-header">
                                            <div class="log-description">${log.action_description}</div>
                                            <div class="log-meta">
                                                <span class="log-user">${log.user_name || 'System'}</span>
                                                <span class="log-time">${formatDate(log.created_at)}</span>
                                                <span class="log-ip">${log.ip_address}</span>
                                            </div>
                                        </div>
                                        ${log.old_value || log.new_value ? `
                                            <div class="log-details">
                                                ${log.old_value ? `<strong>Alter Wert:</strong> ${log.old_value}<br>` : ''}
                                                ${log.new_value ? `<strong>Neuer Wert:</strong> ${log.new_value}` : ''}
                                            </div>
                                        ` : ''}
                                    </div>
                                </div>
                            `;
                        });
                        $('#Nexora Service Suite-logs-list').html(html);
                    } else {
                        $('#Nexora Service Suite-logs-list').html(`
                            <div class="no-logs">
                                <div class="icon">📋</div>
                                <h3>Keine Aktivitäten gefunden</h3>
                                <p>Für diese Anfrage wurden noch keine Aktivitäten protokolliert.</p>
                            </div>
                        `);
                    }
                } else {
                    $('#Nexora Service Suite-logs-list').html(`
                        <div class="no-logs">
                            <div class="icon">⚠️</div>
                            <h3>Fehler beim Laden der Logs</h3>
                            <p>Bitte versuchen Sie es erneut.</p>
                        </div>
                    `);
                }
            },
            error: function() {
                $('#Nexora Service Suite-logs-list').html(`
                    <div class="no-logs">
                        <div class="icon">⚠️</div>
                        <h3>Fehler beim Laden der Logs</h3>
                        <p>Bitte versuchen Sie es erneut.</p>
                    </div>
                `);
            }
        });
    }
    
    function getActionTypeLabel(actionType) {
        const labels = {
            'request_created': 'Anfrage erstellt',
            'request_updated': 'Anfrage aktualisiert',
            'request_deleted': 'Anfrage gelöscht',
            'status_change': 'Status geändert',
            'comment_added': 'Kommentar hinzugefügt',
            'invoice_created': 'Rechnung erstellt',
            'invoice_updated': 'Rechnung aktualisiert',
            'invoice_deleted': 'Rechnung gelöscht',
            'file_uploaded': 'Datei hochgeladen',
            'file_deleted': 'Datei gelöscht',
            'user_assigned': 'Benutzer zugewiesen',
            'priority_changed': 'Priorität geändert',
            'deadline_set': 'Deadline gesetzt',
            'deadline_updated': 'Deadline aktualisiert',
            'notification_sent': 'Benachrichtigung gesendet'
        };
        return labels[actionType] || actionType;
    }
    
    function getActionTypeIcon(actionType) {
        const icons = {
            'request_created': '📝',
            'request_updated': '✏️',
            'request_deleted': '🗑️',
            'status_change': '🔄',
            'comment_added': '💬',
            'invoice_created': '🧾',
            'invoice_updated': '📄',
            'invoice_deleted': '🗑️',
            'file_uploaded': '📎',
            'file_deleted': '🗑️',
            'user_assigned': '👤',
            'priority_changed': '⚡',
            'deadline_set': '⏰',
            'deadline_updated': '⏰',
            'notification_sent': '📧'
        };
        return icons[actionType] || '📋';
    }
    
    function formatDate(dateString) {
        const date = new Date(dateString);
        return date.toLocaleString('de-DE', {
            year: 'numeric',
            month: '2-digit',
            day: '2-digit',
            hour: '2-digit',
            minute: '2-digit'
        });
    }
    $('#Nexora Service Suite-prev-logs').on('click', function() {
        if (currentPage > 1) {
            loadLogs(currentPage - 1);
        }
    });
    
    $('#Nexora Service Suite-next-logs').on('click', function() {
        if (currentPage < totalPages) {
            loadLogs(currentPage + 1);
        }
    });
    
    $('#Nexora Service Suite-export-logs').on('click', function() {
        const link = document.createElement('a');
        link.href = ajaxUrl;
        link.download = `activity_logs_request_${requestId}_${new Date().toISOString().slice(0, 10)}.csv`;
        
        const form = document.createElement('form');
        form.method = 'POST';
        form.action = ajaxUrl;
        
        const fields = {
            action: 'nexora_export_request_logs',
            request_id: requestId,
            nonce: nonce
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
    
    $('#Nexora Service Suite-test-log').on('click', function() {
        $.ajax({
            url: ajaxUrl,
            type: 'POST',
            data: {
                action: 'nexora_create_test_log',
                request_id: requestId,
                nonce: nonce
            },
            success: function(response) {
                if (response.success) {
                    alert('Test-Log erfolgreich erstellt!');
                    loadLogs();
                } else {
                    alert('Fehler beim Erstellen des Test-Logs: ' + response.data);
                }
            },
            error: function() {
                alert('Fehler beim Erstellen des Test-Logs');
            }
        });
    });
    loadLogs();
});
</script> 