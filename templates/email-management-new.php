<?php

if (!defined('ABSPATH')) {
    exit;
}
global $wpdb;
$table_name = $wpdb->prefix . 'simple_smtp_settings';
$table_exists = $wpdb->get_var("SHOW TABLES LIKE '$table_name'") == $table_name;

if ($table_exists) {
    $results = $wpdb->get_results("SELECT * FROM $table_name LIMIT 1");
    if ($results && !empty($results[0])) {
        $current_settings = (array) $results[0];
        $system_status = array(
            'enabled' => $current_settings['enabled'] ?? 0,
            'host' => $current_settings['host'] ?? '',
            'port' => $current_settings['port'] ?? 587,
            'encryption' => $current_settings['encryption'] ?? 'tls',
            'username' => $current_settings['username'] ?? '',
            'password_set' => !empty($current_settings['password']),
            'sender_name' => $current_settings['sender_name'] ?? '',
            'sender_email' => $current_settings['sender_email'] ?? '',
            'reply_to' => $current_settings['sender_email'] ?? ''
        );
    } else {
        $current_settings = array(
            'enabled' => 0,
            'host' => '',
            'port' => 587,
            'username' => '',
            'password' => '',
            'encryption' => 'tls',
            'sender_name' => '',
            'sender_email' => ''
        );
        $system_status = array(
            'enabled' => 0,
            'host' => '',
            'port' => 587,
            'encryption' => 'tls',
            'username' => '',
            'password_set' => false,
            'sender_name' => '',
            'sender_email' => '',
            'reply_to' => ''
        );
    }
} else {
    $current_settings = array();
    $system_status = array(
        'enabled' => 0,
        'host' => '❌ Table not found',
        'port' => 0,
        'encryption' => '',
        'username' => '',
        'password_set' => false,
        'sender_name' => '',
        'sender_email' => '',
        'reply_to' => ''
    );
}
$nonce = wp_create_nonce('nexora_email_nonce');
?>
<script>
var ajaxurl = '<?php echo admin_url('admin-ajax.php'); ?>';
var nexora_ajax = {
    nonce: '<?php echo $nonce; ?>'
};
</script>

<div class="wrap">
    <h1>📧 E-Mail-Verwaltung - Independent System</h1>
    
    <div class="Nexora Service Suite-admin-container">
        
        <div class="system-status-overview">
            <h2>🔍 System Status & Configuration</h2>
            <div class="status-grid">
                <div class="status-item">
                    <span class="status-label">System Status:</span>
                    <span class="status-value <?php echo $current_settings['enabled'] ? 'status-enabled' : 'status-disabled'; ?>">
                        <?php echo $current_settings['enabled'] ? '✅ Enabled' : '❌ Disabled'; ?>
                    </span>
                </div>
                <div class="status-item">
                    <span class="status-label">SMTP Host:</span>
                    <span class="status-value"><?php echo esc_html($current_settings['host']); ?></span>
                </div>
                <div class="status-item">
                    <span class="status-label">SMTP Port:</span>
                    <span class="status-value"><?php echo esc_html($current_settings['port']); ?></span>
                </div>
                <div class="status-item">
                    <span class="status-label">Encryption:</span>
                    <span class="status-value"><?php echo esc_html($current_settings['encryption']); ?></span>
                </div>
                <div class="status-item">
                    <span class="status-label">Username:</span>
                    <span class="status-value"><?php echo esc_html($current_settings['username']); ?></span>
                </div>
                <div class="status-item">
                    <span class="status-label">Password Set:</span>
                    <span class="status-value"><?php echo !empty($current_settings['password']) ? '✅ Yes' : '❌ No'; ?></span>
                </div>
                <div class="status-item">
                    <span class="status-label">Sender Name:</span>
                    <span class="status-value"><?php echo esc_html($current_settings['sender_name']); ?></span>
                </div>
                <div class="status-item">
                    <span class="status-label">Sender Email:</span>
                    <span class="status-value"><?php echo esc_html($current_settings['sender_email']); ?></span>
                </div>
                <div class="status-item">
                    <span class="status-label">Reply-To:</span>
                    <span class="status-value"><?php echo esc_html($current_settings['sender_email']); ?></span>
                </div>
            </div>
        </div>

        
        <div class="smtp-configuration">
            <h2>⚙️ SMTP-Konfiguration</h2>
            <form id="smtp-config-form" class="smtp-form">
                <div class="form-row">
                    <div class="form-group">
                        <label for="smtp-enabled">
                            <input type="checkbox" id="smtp-enabled" name="enabled" <?php checked($current_settings['enabled'], true); ?>>
                            SMTP aktivieren
                        </label>
                    </div>
                </div>
                
                <div class="form-row">
                    <div class="form-group">
                        <label for="smtp-host">SMTP Host *</label>
                        <input type="text" id="smtp-host" name="host" value="<?php echo esc_attr($current_settings['host']); ?>" required>
                    </div>
                    <div class="form-group">
                        <label for="smtp-port">Port *</label>
                        <input type="number" id="smtp-port" name="port" value="<?php echo esc_attr($current_settings['port']); ?>" required>
                    </div>
                </div>
                
                <div class="form-row">
                    <div class="form-group">
                        <label for="smtp-username">Benutzername *</label>
                        <input type="text" id="smtp-username" name="username" value="<?php echo esc_attr($current_settings['username']); ?>" required>
                    </div>
                    <div class="form-group">
                        <label for="smtp-password">Passwort *</label>
                        <input type="password" id="smtp-password" name="password" value="<?php echo esc_attr($current_settings['password']); ?>" required>
                    </div>
                </div>
                
                <div class="form-row">
                    <div class="form-group">
                        <label for="smtp-encryption">Verschlüsselung</label>
                        <select id="smtp-encryption" name="encryption">
                            <option value="tls" <?php selected($current_settings['encryption'], 'tls'); ?>>TLS</option>
                            <option value="ssl" <?php selected($current_settings['encryption'], 'ssl'); ?>>SSL</option>
                            <option value="" <?php selected($current_settings['encryption'], ''); ?>>Keine</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label for="smtp-auth-mode">Authentifizierung</label>
                        <select id="smtp-auth-mode" name="auth_mode">
                            <option value="login" <?php selected($current_settings['auth_mode'], 'login'); ?>>Login</option>
                            <option value="plain" <?php selected($current_settings['auth_mode'], 'plain'); ?>>Plain</option>
                        </select>
                    </div>
                </div>
                
                <div class="form-row">
                    <div class="form-group">
                        <label for="sender-name">Absender Name</label>
                        <input type="text" id="sender-name" name="sender_name" value="<?php echo esc_attr($current_settings['sender_name']); ?>">
                    </div>
                    <div class="form-group">
                        <label for="sender-email">Absender E-Mail</label>
                        <input type="email" id="sender-email" name="sender_email" value="<?php echo esc_attr($current_settings['sender_email']); ?>">
                    </div>
                </div>
                
                <div class="form-row">
                    <div class="form-group">
                        <label for="reply-to">Reply-To E-Mail</label>
                        <input type="email" id="reply-to" name="reply_to" value="<?php echo esc_attr($current_settings['reply_to']); ?>">
                    </div>
                </div>
                
                <div class="form-actions">
                    <button type="submit" class="button button-primary">💾 Einstellungen speichern</button>
                    <button type="button" id="reset-settings" class="button button-secondary">🔄 Zurücksetzen</button>
                </div>
            </form>
        </div>

        
        <div class="test-email-section">
            <h2>🧪 Test E-Mail System</h2>
            <form id="test-email-form" class="test-email-form">
                <div class="form-row">
                    <div class="form-group">
                        <label for="test-email-to">Test E-Mail an:</label>
                        <input type="email" id="test-email-to" name="test_email_to" placeholder="test@example.com" required>
                    </div>
                    <div class="form-group">
                        <label for="test-email-subject">Betreff:</label>
                        <input type="text" id="test-email-subject" name="test_email_subject" value="🧪 Test E-Mail - Independent System" required>
                    </div>
                </div>
                
                <div class="form-row">
                    <div class="form-group">
                        <label for="test-email-message">Nachricht:</label>
                        <textarea id="test-email-message" name="test_email_message" rows="4" placeholder="Ihre Test-Nachricht...">Dies ist eine Test-E-Mail vom Independent Email System.</textarea>
                    </div>
                </div>
                
                <div class="form-actions">
                    <button type="submit" id="send-test-email" class="button button-primary">🚀 Test E-Mail senden</button>
                    <button type="button" id="test-smtp-connection" class="button button-secondary">🔌 SMTP-Verbindung testen</button>
                </div>
            </form>
        </div>

        
        <div class="real-time-logs">
            <h2>📝 Real-time System Logs</h2>
            <div class="log-controls">
                <button type="button" id="refresh-logs" class="button button-secondary">🔄 Logs aktualisieren</button>
                <button type="button" id="clear-logs" class="button button-secondary">🗑️ Logs löschen</button>
                <button type="button" id="export-logs" class="button button-secondary">📥 Logs exportieren</button>
            </div>
            
            <div id="log-container" class="log-container">
                <div class="log-entry log-info">
                    <span class="log-time"><?php echo date('H:i:s'); ?></span>
                    <span class="log-message">System bereit - Logs werden geladen...</span>
                </div>
            </div>
        </div>

        
        <div class="database-management">
            <h2>🗄️ Database Management</h2>
            <div class="db-controls">
                <button type="button" id="create-email-tables" class="button button-primary">
                    🗄️ Email Tabellen erstellen
                </button>
                <button type="button" id="check-db-status" class="button button-secondary">
                    🔍 Datenbank Status prüfen
                </button>
                <button type="button" id="debug-database-tables" class="button button-secondary">
                    🔬 Vollständige Tabellen-Analyse
                </button>
                <button type="button" id="test-simple-ajax" class="button button-secondary">
                    🧪 Einfacher AJAX Test
                </button>
            </div>
            
            <div id="db-status-display" class="db-status-display" style="display: none;">
                <h4>📊 Datenbank Status</h4>
                <div id="db-status-content"></div>
            </div>
            
            <div id="db-creation-logs" class="db-creation-logs" style="display: none;">
                <h4>📝 Tabellen-Erstellung Logs</h4>
                <div id="db-logs-content"></div>
            </div>
            
            <div id="db-debug-analysis" class="db-debug-analysis" style="display: none;">
                <h4>🔬 Vollständige Tabellen-Analyse</h4>
                <div id="db-debug-content"></div>
            </div>
        </div>

        
        <div class="admin-email-management">
            <h2>👥 Admin E-Mail Verwaltung</h2>
            <p class="description">Verwalten Sie die E-Mail-Adressen der Administratoren, die Benachrichtigungen erhalten sollen.</p>
            
            
            <div class="add-admin-email-form">
                <h3>➕ Neue Admin E-Mail hinzufügen</h3>
                <form id="add-admin-email-form" class="admin-email-form">
                    <div class="form-row">
                        <div class="form-group">
                            <label for="admin-email-address">E-Mail-Adresse *</label>
                            <input type="email" id="admin-email-address" name="email_address" placeholder="admin@example.com" required>
                        </div>
                        <div class="form-group">
                            <label for="admin-display-name">Anzeigename *</label>
                            <input type="text" id="admin-display-name" name="display_name" placeholder="Hauptadministrator" required>
                        </div>
                    </div>
                    
                    <div class="form-row">
                        <div class="form-group">
                            <label for="admin-role">Rolle</label>
                            <select id="admin-role" name="role">
                                <option value="primary">Primär (Hauptadministrator)</option>
                                <option value="secondary">Sekundär</option>
                                <option value="support">Support</option>
                                <option value="billing">Billing</option>
                            </select>
                        </div>
                        <div class="form-group">
                            <label for="admin-notification-types">Benachrichtigungstypen</label>
                            <select id="admin-notification-types" name="notification_types[]" multiple>
                                <option value="all" selected>Alle Benachrichtigungen</option>
                                <option value="customer_registration">Kundenregistrierung</option>
                                <option value="service_status_change">Service-Status-Änderung</option>
                                <option value="invoice_generated">Rechnung generiert</option>
                                <option value="payment_received">Zahlung erhalten</option>
                                <option value="support_requests">Support-Anfragen</option>
                                <option value="billing_issues">Billing-Probleme</option>
                            </select>
                        </div>
                    </div>
                    
                    <div class="form-actions">
                        <button type="submit" class="button button-primary">💾 Admin E-Mail speichern</button>
                        <button type="reset" class="button button-secondary">🔄 Zurücksetzen</button>
                    </div>
                </form>
            </div>
            
            
            <div class="admin-emails-list">
                <h3>📋 Aktuelle Admin E-Mails</h3>
                <div class="list-controls">
                    <button type="button" id="refresh-admin-emails" class="button button-secondary">🔄 Liste aktualisieren</button>
                </div>
                
                <div id="admin-emails-container" class="admin-emails-container">
                    <div class="loading-message">🔄 Lade Admin E-Mails...</div>
                </div>
            </div>
        </div>

        
        <div class="email-queue-management">
            <h2>📧 E-Mail-Warteschlange & Status</h2>
            <p class="description">Überwachen Sie den Status aller E-Mails: in der Warteschlange, gesendet, fehlgeschlagen oder ausstehend.</p>
            
            
            <div class="queue-controls">
                <button type="button" id="refresh-email-queue" class="button button-secondary">🔄 Warteschlange aktualisieren</button>
                <button type="button" id="clear-failed-emails" class="button button-secondary">🗑️ Fehlgeschlagene E-Mails löschen</button>
                <button type="button" id="retry-failed-emails" class="button button-primary">🔄 Fehlgeschlagene E-Mails erneut versuchen</button>
            </div>
            
            
            <div class="queue-statistics">
                <div class="stat-item">
                    <span class="stat-label">📊 Gesamt:</span>
                    <span class="stat-value" id="total-emails">0</span>
                </div>
                <div class="stat-item">
                    <span class="stat-label">⏳ In Warteschlange:</span>
                    <span class="stat-value" id="pending-emails">0</span>
                </div>
                <div class="stat-item">
                    <span class="stat-label">✅ Gesendet:</span>
                    <span class="stat-value" id="sent-emails">0</span>
                </div>
                <div class="stat-item">
                    <span class="stat-label">❌ Fehlgeschlagen:</span>
                    <span class="stat-value" id="failed-emails">0</span>
                </div>
            </div>
            
            
            <div class="email-queue-list">
                <h3>📋 E-Mail-Warteschlange Details</h3>
                
                
                <div class="queue-filters">
                    <select id="status-filter">
                        <option value="all">Alle Status</option>
                        <option value="pending">In Warteschlange</option>
                        <option value="sent">Gesendet</option>
                        <option value="failed">Fehlgeschlagen</option>
                    </select>
                    
                    <select id="type-filter">
                        <option value="all">Alle Typen</option>
                        <option value="notification">Benachrichtigung</option>
                        <option value="test">Test</option>
                        <option value="system">System</option>
                        <option value="error">Fehler</option>
                    </select>
                    
                    <input type="date" id="date-filter" placeholder="Datum filtern">
                    
                    <button type="button" id="apply-filters" class="button button-secondary">🔍 Filter anwenden</button>
                    <button type="button" id="clear-filters" class="button button-secondary">🔄 Filter zurücksetzen</button>
                </div>
                
                <div id="email-queue-container" class="email-queue-container">
                    <div class="loading-message">🔄 Lade E-Mail-Warteschlange...</div>
                </div>
            </div>
        </div>

        
        <div class="email-template-customization">
            <h2>🎨 E-Mail Template Anpassung</h2>
            <p class="description">Passen Sie das Design und den Inhalt Ihrer E-Mail-Templates an. Ändern Sie Farben, Logos, Texte und Kontaktinformationen.</p>
            
            <form id="template-customization-form" class="template-customization-form">
                
                <div class="template-section">
                    <h3>📋 Header-Einstellungen</h3>
                    <div class="form-row">
                        <div class="form-group">
                            <label for="header-logo">Logo-Dateiname:</label>
                            <input type="text" id="header-logo" name="header_logo" placeholder="eccoripair.webp" required>
                            <small>Datei muss im Verzeichnis assets/images/ liegen</small>
                        </div>
                        <div class="form-group">
                            <label for="header-background-color">Hintergrundfarbe:</label>
                            <input type="color" id="header-background-color" name="header_background_color" value="#273269">
                        </div>
                        <div class="form-group">
                            <label for="header-text-color">Textfarbe:</label>
                            <input type="color" id="header-text-color" name="header_text_color" value="#ffffff">
                        </div>
                    </div>
                    <div class="form-row">
                        <div class="form-group">
                            <label for="header-subtitle">Untertitel:</label>
                            <input type="text" id="header-subtitle" name="header_subtitle" placeholder="Qualitätsdienstleistungen zu fairen Preisen">
                        </div>
                    </div>
                </div>
                
                
                <div class="template-section">
                    <h3>📋 Footer-Einstellungen</h3>
                    <div class="form-row">
                        <div class="form-group">
                            <label for="footer-background-color">Hintergrundfarbe:</label>
                            <input type="color" id="footer-background-color" name="footer_background_color" value="#273269">
                        </div>
                        <div class="form-group">
                            <label for="footer-text-color">Textfarbe:</label>
                            <input type="color" id="footer-text-color" name="footer_text_color" value="#ffffff">
                        </div>
                    </div>
                </div>
                
                
                <div class="template-section">
                    <h3>🏢 Firmeninformationen</h3>
                    <div class="form-row">
                        <div class="form-group">
                            <label for="company-phone">Telefon:</label>
                            <input type="text" id="company-phone" name="company_phone" placeholder="+43 1 234 5678">
                        </div>
                        <div class="form-group">
                            <label for="company-email">E-Mail:</label>
                            <input type="email" id="company-email" name="company_email" placeholder="info@example.com">
                        </div>
                    </div>
                    <div class="form-row">
                        <div class="form-group">
                            <label for="company-website">Website:</label>
                            <input type="url" id="company-website" name="company_website" placeholder="https://example.com">
                        </div>
                        <div class="form-group">
                            <label for="company-address">Adresse:</label>
                            <input type="text" id="company-address" name="company_address" placeholder="Wien, Österreich">
                        </div>
                    </div>
                </div>
                
                
                <div class="template-section">
                    <h3>📱 Social Media Links</h3>
                    <div class="form-row">
                        <div class="form-group">
                            <label for="social-instagram">Instagram URL:</label>
                            <input type="url" id="social-instagram" name="social_instagram" placeholder="https://instagram.com/Nexora Service Suite">
                        </div>
                        <div class="form-group">
                            <label for="social-telegram">Telegram URL:</label>
                            <input type="url" id="social-telegram" name="social_telegram" placeholder="https://t.me/Nexora Service Suite">
                        </div>
                    </div>
                    <div class="form-row">
                        <div class="form-group">
                            <label for="social-whatsapp">WhatsApp URL:</label>
                            <input type="url" id="social-whatsapp" name="social_whatsapp" placeholder="https://wa.me/4312345678">
                        </div>
                    </div>
                </div>
                
                
                <div class="template-section">
                    <h3>✍️ E-Mail-Inhalt Anpassung</h3>
                    <div class="form-row">
                        <div class="form-group">
                            <label for="status-change-text">Status-Änderung Text:</label>
                            <textarea id="status-change-text" name="status_change_text" rows="3" placeholder="Der Status Ihrer Serviceanfrage hat sich geändert."></textarea>
                        </div>
                        <div class="form-group">
                            <label for="customer-welcome-text">Kunden-Willkommen Text:</label>
                            <textarea id="customer-welcome-text" name="customer_welcome_text" rows="3" placeholder="Willkommen bei Nexora Service Suite! Ihr Konto wurde erfolgreich erstellt."></textarea>
                        </div>
                    </div>
                    <div class="form-row">
                        <div class="form-group">
                            <label for="dashboard-link-text">Dashboard-Link Text:</label>
                            <input type="text" id="dashboard-link-text" name="dashboard_link_text" placeholder="🚀 Ihr Dashboard aufrufen">
                        </div>
                    </div>
                </div>
                
                
                <div class="form-actions">
                    <button type="submit" class="button button-primary">💾 Template-Einstellungen speichern</button>
                    <button type="button" id="reset-template-settings" class="button button-secondary">🔄 Zurücksetzen</button>
                    <button type="button" id="preview-template" class="button button-secondary">👁️ Vorschau</button>
                </div>
            </form>
        </div>
        
        
        <div id="template-preview" class="template-preview" style="display: none;">
            <h3>👁️ Template-Vorschau</h3>
            <div id="preview-content"></div>
        </div>

        
        <div class="system-information">
            <h2>ℹ️ System Information</h2>
            <div class="info-grid">
                <div class="info-item">
                    <span class="info-label">Konfigurationsdatei:</span>
                    <span class="info-value"><?php echo esc_html($system_status['config_file']); ?></span>
                </div>
                <div class="info-item">
                    <span class="info-label">Log-Datei:</span>
                    <span class="info-value"><?php echo esc_html($system_status['log_file']); ?></span>
                </div>
                <div class="info-item">
                    <span class="info-label">PHP Version:</span>
                    <span class="info-value"><?php echo PHP_VERSION; ?></span>
                </div>
                <div class="info-item">
                    <span class="info-label">WordPress Version:</span>
                    <span class="info-value"><?php echo get_bloginfo('version'); ?></span>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
.Nexora Service Suite-admin-container {
    max-width: 1200px;
    margin: 20px 0;
}

.system-status-overview,
.smtp-configuration,
.test-email-section,
.real-time-logs,
.system-information {
    background: #fff;
    border: 1px solid #ccd0d4;
    border-radius: 4px;
    padding: 20px;
    margin-bottom: 20px;
}

.status-grid,
.info-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
    gap: 15px;
    margin-top: 15px;
}

.status-item,
.info-item {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 10px;
    background: #f8f9fa;
    border-radius: 4px;
    border: 1px solid #e9ecef;
}

.status-label,
.info-label {
    font-weight: 600;
    color: #495057;
}

.status-value,
.info-value {
    color: #6c757d;
    font-family: 'Courier New', monospace;
    background: #fff;
    padding: 4px 8px;
    border-radius: 3px;
    border: 1px solid #dee2e6;
}

.status-enabled {
    color: #28a745;
    font-weight: 600;
}

.status-disabled {
    color: #dc3545;
    font-weight: 600;
}

.smtp-form {
    margin-top: 15px;
}

.form-row {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
    gap: 15px;
    margin-bottom: 15px;
}

.form-group {
    display: flex;
    flex-direction: column;
}

.form-group label {
    margin-bottom: 5px;
    font-weight: 600;
    color: #495057;
}

.form-group input,
.form-group select,
.form-group textarea {
    padding: 8px 12px;
    border: 1px solid #ced4da;
    border-radius: 4px;
    font-size: 14px;
}

.form-group textarea {
    resize: vertical;
    min-height: 80px;
}

.form-actions {
    display: flex;
    gap: 10px;
    margin-top: 20px;
}

.log-controls {
    display: flex;
    gap: 10px;
    margin-bottom: 15px;
}

.log-container {
    background: #f8f9fa;
    border: 1px solid #e9ecef;
    border-radius: 4px;
    padding: 15px;
    max-height: 400px;
    overflow-y: auto;
    font-family: 'Courier New', monospace;
    font-size: 13px;
}

.log-entry {
    margin-bottom: 8px;
    padding: 4px 0;
}

.log-time {
    color: #6c757d;
    margin-right: 10px;
}

.log-info {
    color: #17a2b8;
}

.log-success {
    color: #28a745;
}

.log-error {
    color: #dc3545;
}

.log-warning {
    color: #ffc107;
}

.database-management {
    background: #fff;
    border: 1px solid #ccd0d4;
    border-radius: 4px;
    padding: 20px;
    margin-bottom: 20px;
}

.db-controls {
    display: flex;
    gap: 10px;
    margin-bottom: 15px;
}

.db-status-display,
.db-creation-logs {
    background: #f8f9fa;
    border: 1px solid #e9ecef;
    border-radius: 4px;
    padding: 15px;
    margin-top: 15px;
}

.db-status-display h4,
.db-creation-logs h4 {
    margin-top: 0;
    color: #495057;
    font-size: 14px;
    font-weight: 600;
}

#db-status-content,
#db-logs-content,
#db-debug-content {
    font-family: 'Courier New', monospace;
    font-size: 12px;
    background: #fff;
    padding: 10px;
    border-radius: 3px;
    border: 1px solid #dee2e6;
    max-height: 300px;
    overflow-y: auto;
}

.db-debug-analysis {
    background: #f8f9fa;
    border: 1px solid #e9ecef;
    border-radius: 4px;
    padding: 15px;
    margin-top: 15px;
}

.db-debug-analysis h4 {
    margin-top: 0;
    color: #495057;
    font-size: 14px;
    font-weight: 600;
}

.admin-email-management {
    background: #fff;
    border: 1px solid #ccd0d4;
    border-radius: 4px;
    padding: 20px;
    margin-bottom: 20px;
}

.admin-email-management h2 {
    margin-top: 0;
    color: #495057;
    font-size: 18px;
    font-weight: 600;
}

.admin-email-management .description {
    color: #6c757d;
    margin-bottom: 15px;
    font-size: 14px;
}

.add-admin-email-form h3 {
    margin-top: 0;
    margin-bottom: 15px;
    color: #495057;
    font-size: 16px;
    font-weight: 600;
}

.admin-email-form .form-row {
    grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
    gap: 15px;
    margin-bottom: 15px;
}

.admin-email-form .form-group label {
    font-weight: 600;
    color: #495057;
}

.admin-email-form .form-group input,
.admin-email-form .form-group select {
    padding: 8px 12px;
    border: 1px solid #ced4da;
    border-radius: 4px;
    font-size: 14px;
}

.admin-email-form .form-group select {
    padding: 8px 12px;
    border: 1px solid #ced4da;
    border-radius: 4px;
    font-size: 14px;
}

.admin-email-form .form-group select[multiple] {
    height: 100px; 
}

.admin-email-form .form-actions {
    display: flex;
    gap: 10px;
    margin-top: 20px;
}

.admin-email-form .form-actions button {
    flex: 1; 
}

.admin-emails-list h3 {
    margin-top: 0;
    margin-bottom: 15px;
    color: #495057;
    font-size: 16px;
    font-weight: 600;
}

.admin-emails-list .list-controls {
    display: flex;
    gap: 10px;
    margin-bottom: 15px;
}

.admin-emails-container {
    background: #f8f9fa;
    border: 1px solid #e9ecef;
    border-radius: 4px;
    padding: 15px;
    min-height: 150px; 
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 14px;
    color: #6c757d;
}

.admin-emails-container .loading-message {
    text-align: center;
}

.admin-emails-table {
    width: 100%;
    overflow-x: auto;
}

.admin-emails-table table {
    width: 100%;
    border-collapse: collapse;
    margin-top: 10px;
}

.admin-emails-table th {
    background: #f1f1f1;
    padding: 10px;
    text-align: left;
    font-weight: 600;
    border: 1px solid #ddd;
}

.admin-emails-table td {
    padding: 10px;
    border: 1px solid #ddd;
    vertical-align: middle;
}

.admin-emails-table .actions {
    white-space: nowrap;
}

.admin-emails-table .actions button {
    margin-right: 5px;
    margin-bottom: 2px;
}

.status-active {
    color: #28a745;
    font-weight: 600;
    background: #d4edda;
    padding: 4px 8px;
    border-radius: 3px;
    border: 1px solid #c3e6cb;
}

.status-inactive {
    color: #6c757d;
    font-weight: 600;
    background: #f8f9fa;
    padding: 4px 8px;
    border-radius: 3px;
    border: 1px solid #dee2e6;
}

.no-emails {
    text-align: center;
    color: #6c757d;
    font-style: italic;
    padding: 20px;
}

.admin-email-form .form-group select[multiple] {
    min-height: 100px;
    max-height: 150px;
}

.admin-email-form .form-group select[multiple] option {
    padding: 4px 8px;
    margin: 1px 0;
}

.admin-email-form .form-group select[multiple] option:checked {
    background: #0073aa;
    color: white;
}

.email-template-customization {
    background: #fff;
    border: 1px solid #ccd0d4;
    border-radius: 4px;
    padding: 20px;
    margin: 20px 0;
    box-shadow: 0 1px 1px rgba(0,0,0,.04);
}

.template-section {
    background: #f8f9fa;
    border: 1px solid #e9ecef;
    border-radius: 8px;
    padding: 20px;
    margin-bottom: 20px;
}

.template-section h3 {
    margin-top: 0;
    margin-bottom: 15px;
    color: #495057;
    font-size: 16px;
    font-weight: 600;
    border-bottom: 2px solid #dee2e6;
    padding-bottom: 10px;
}

.template-customization-form .form-row {
    grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
    gap: 15px;
    margin-bottom: 15px;
}

.template-customization-form .form-group label {
    font-weight: 600;
    color: #495057;
    margin-bottom: 8px;
    display: block;
}

.template-customization-form .form-group small {
    color: #6c757d;
    font-size: 12px;
    margin-top: 5px;
    display: block;
}

.template-customization-form .form-group input[type="color"] {
    width: 100%;
    height: 40px;
    border: 1px solid #ced4da;
    border-radius: 4px;
    cursor: pointer;
}

.template-preview {
    background: #fff;
    border: 1px solid #ccd0d4;
    border-radius: 4px;
    padding: 20px;
    margin: 20px 0;
    box-shadow: 0 1px 1px rgba(0,0,0,.04);
}

.template-preview h3 {
    margin-top: 0;
    margin-bottom: 15px;
    color: #495057;
    font-size: 16px;
    font-weight: 600;
}

#preview-content {
    background: #f8f9fa;
    border: 1px solid #e9ecef;
    border-radius: 4px;
    padding: 20px;
    max-height: 500px;
    overflow-y: auto;
}

.email-queue-management {
    background: #fff;
    border: 1px solid #ccd0d4;
    border-radius: 4px;
    padding: 20px;
    margin: 20px 0;
    box-shadow: 0 1px 1px rgba(0,0,0,.04);
}

.queue-controls {
    margin-bottom: 20px;
    display: flex;
    gap: 10px;
    flex-wrap: wrap;
}

.queue-statistics {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
    gap: 15px;
    margin-bottom: 25px;
}

.stat-item {
    background: #f8f9fa;
    padding: 15px;
    border-radius: 4px;
    text-align: center;
    border: 1px solid #e1e5e9;
}

.stat-label {
    display: block;
    font-weight: 600;
    color: #50575e;
    margin-bottom: 5px;
}

.stat-value {
    display: block;
    font-size: 24px;
    font-weight: bold;
    color: #0073aa;
}

.queue-filters {
    margin-bottom: 20px;
    display: flex;
    gap: 10px;
    flex-wrap: wrap;
    align-items: center;
}

.queue-filters select,
.queue-filters input {
    padding: 8px 12px;
    border: 1px solid #ccd0d4;
    border-radius: 4px;
    min-width: 150px;
}

.email-queue-container {
    margin-top: 15px;
}

.email-queue-table {
    margin-top: 15px;
}

.email-queue-table table {
    width: 100%;
    border-collapse: collapse;
}

.email-queue-table th,
.email-queue-table td {
    padding: 12px;
    text-align: left;
    border-bottom: 1px solid #e1e5e9;
}

.email-queue-table th {
    background: #f8f9fa;
    font-weight: 600;
}

.status-pending {
    color: #f39c12;
    font-weight: 600;
}

.status-sent {
    color: #27ae60;
    font-weight: 600;
}

.status-failed {
    color: #e74c3c;
    font-weight: 600;
}

.email-type-notification {
    background: #e3f2fd;
    color: #1976d2;
    padding: 4px 8px;
    border-radius: 3px;
    font-size: 12px;
}

.email-type-test {
    background: #f3e5f5;
    color: #7b1fa2;
    padding: 4px 8px;
    border-radius: 3px;
    font-size: 12px;
}

.email-type-system {
    background: #e8f5e8;
    color: #388e3c;
    padding: 4px 8px;
    border-radius: 3px;
    font-size: 12px;
}

.email-type-error {
    background: #ffebee;
    color: #d32f2f;
    padding: 4px 8px;
    border-radius: 3px;
    font-size: 12px;
}
</style>

<script>
jQuery(document).ready(function($) {
    function loadSmtpSettings() {
        console.log('=== Loading SMTP Settings ===');
        
        $.ajax({
            url: ajaxurl,
            type: 'POST',
            data: {
                action: 'get_independent_smtp_settings',
                nonce: nexora_ajax.nonce
            },
            success: function(response) {
                console.log('Load settings response:', response);
                
                if (response.success && response.data) {
                    const settings = response.data;
                    console.log('Settings loaded:', settings);
                    if (settings.enabled !== undefined) {
                        $('#smtp-enabled').prop('checked', settings.enabled);
                    }
                    if (settings.host) {
                        $('#smtp-host').val(settings.host);
                    }
                    if (settings.port) {
                        $('#smtp-port').val(settings.port);
                    }
                    if (settings.encryption) {
                        $('#smtp-encryption').val(settings.encryption);
                    }
                    if (settings.username) {
                        $('#smtp-username').val(settings.username);
                    }
                    if (settings.auth_mode) {
                        $('#smtp-auth-mode').val(settings.auth_mode);
                    }
                    if (settings.sender_name) {
                        $('#smtp-sender-name').val(settings.sender_name);
                    }
                    if (settings.sender_email) {
                        $('#smtp-sender-email').val(settings.sender_email);
                    }
                    if (settings.reply_to) {
                        $('#smtp-reply-to').val(settings.reply_to);
                    }
                    
                    addLogEntry('✅ SMTP settings loaded successfully', 'success');
                    console.log('Form populated with settings');
                    
                } else {
                    addLogEntry('❌ Failed to load SMTP settings', 'error');
                    console.log('Failed to load settings:', response);
                }
            },
            error: function(xhr, status, error) {
                console.log('Load settings error:', {xhr, status, error});
                addLogEntry('❌ Error loading SMTP settings: ' + error, 'error');
            }
        });
    }
    loadSmtpSettings();
    $('#smtp-config-form').on('submit', function(e) {
        e.preventDefault();
        
        const formData = new FormData(this);
        formData.append('action', 'save_independent_smtp_settings');
        formData.append('nonce', nexora_ajax.nonce);
        
        $.ajax({
            url: ajaxurl,
            type: 'POST',
            data: formData,
            processData: false,
            contentType: false,
            success: function(response) {
                if (response.success) {
                    addLogEntry('✅ ' + response.data.message, 'success');
                    setTimeout(() => loadSmtpSettings(), 1000);
                } else {
                    addLogEntry('❌ ' + response.data, 'error');
                }
            },
            error: function() {
                addLogEntry('❌ Server error occurred', 'error');
            }
        });
    });
    $('#reset-settings').on('click', function() {
        if (confirm('Sind Sie sicher, dass Sie alle Einstellungen zurücksetzen möchten?')) {
            $.ajax({
                url: ajaxurl,
                type: 'POST',
                data: {
                    action: 'reset_independent_smtp_settings',
                    nonce: nexora_ajax.nonce
                },
                success: function(response) {
                    if (response.success) {
                        addLogEntry('✅ ' + response.data.message, 'success');
                        setTimeout(() => loadSmtpSettings(), 1000);
                    } else {
                        addLogEntry('❌ ' + response.data, 'error');
                    }
                },
                error: function() {
                    addLogEntry('❌ Server error occurred', 'error');
                }
            });
        }
    });
    $('#test-smtp-connection').on('click', function() {
        addLogEntry('🔌 Testing SMTP connection...', 'info');
        
        $.ajax({
            url: ajaxurl,
            type: 'POST',
            data: {
                action: 'test_independent_smtp_connection',
                nonce: nexora_ajax.nonce
            },
            success: function(response) {
                if (response.success) {
                    addLogEntry('✅ ' + response.data.message, 'success');
                } else {
                    addLogEntry('❌ ' + response.data, 'error');
                }
            },
            error: function() {
                addLogEntry('❌ Server error occurred', 'error');
            }
        });
    });
    $('#test-email-form').on('submit', function(e) {
        e.preventDefault();
        
        const testEmailTo = $('#test-email-to').val();
        const testEmailSubject = $('#test-email-subject').val();
        const testEmailMessage = $('#test-email-message').val();
        
        addLogEntry('📧 Sending test email to: ' + testEmailTo, 'info');
        
        $.ajax({
            url: ajaxurl,
            type: 'POST',
            data: {
                action: 'send_independent_test_email',
                nonce: nexora_ajax.nonce,
                test_email_to: testEmailTo,
                test_email_subject: testEmailSubject,
                test_email_message: testEmailMessage
            },
            success: function(response) {
                if (response.success) {
                    addLogEntry('✅ ' + response.data.message, 'success');
                } else {
                    addLogEntry('❌ ' + response.data, 'error');
                }
            },
            error: function() {
                addLogEntry('❌ Server error occurred', 'error');
            }
        });
    });
    $('#refresh-logs').on('click', function() {
        addLogEntry('🔄 Logs refreshed at ' + new Date().toLocaleTimeString(), 'info');
    });
    $('#clear-logs').on('click', function() {
        if (confirm('Sind Sie sicher, dass Sie alle Logs löschen möchten?')) {
            $('#log-container').html(`
                <div class="log-entry log-info">
                    <span class="log-time">${new Date().toLocaleTimeString()}</span>
                    <span class="log-message">Logs gelöscht - System bereit</span>
                </div>
            `);
        }
    });
    $('#export-logs').on('click', function() {
        const logs = $('#log-container').text();
        const blob = new Blob([logs], { type: 'text/plain' });
        const url = URL.createObjectURL(blob);
        const a = document.createElement('a');
        a.href = url;
        a.download = `Nexora Service Suite-logs-${new Date().toISOString().slice(0, 19).replace(/:/g, '-')}.txt`;
        a.click();
        URL.revokeObjectURL(url);
        
        addLogEntry('📥 Logs exported successfully', 'success');
    });
    $('#create-email-tables').on('click', function() {
        const $button = $(this);
        const originalText = $button.text();
        console.log('=== JAVASCRIPT DEBUG: Create tables button clicked ===');
        console.log('ajaxurl:', ajaxurl);
        console.log('nexora_ajax:', nexora_ajax);
        console.log('nonce:', nexora_ajax.nonce);
        
        $button.html('<span class="email-loading"></span>Erstelle Tabellen...');
        $button.prop('disabled', true);
        
        addLogEntry('🗄️ Creating email database tables...', 'info');
        addLogEntry('🔍 Debug: Nonce = ' + nexora_ajax.nonce, 'info');
        addLogEntry('🔍 Debug: Action = create_email_database_tables', 'info');
        
        const ajaxData = {
            action: 'create_email_database_tables',
            nonce: nexora_ajax.nonce
        };
        
        console.log('AJAX data being sent:', ajaxData);
        
        $.ajax({
            url: ajaxurl,
            type: 'POST',
            data: ajaxData,
            success: function(response) {
                console.log('AJAX success response:', response);
                
                if (response.success) {
                    addLogEntry('✅ ' + response.data.message, 'success');
                    $('#db-creation-logs').show();
                    $('#db-logs-content').html(response.data.logs.join('<br>'));
                    const results = response.data.results;
                    let resultsHtml = '<strong>Table Creation Results:</strong><br>';
                    for (const [table, success] of Object.entries(results)) {
                        const status = success ? '✅ SUCCESS' : '❌ FAILED';
                        resultsHtml += `${table}: ${status}<br>`;
                    }
                    $('#db-logs-content').prepend(resultsHtml + '<br>');
                    
                } else {
                    addLogEntry('❌ ' + response.data, 'error');
                }
            },
            error: function(xhr, status, error) {
                console.log('AJAX error:', {xhr, status, error});
                console.log('Response text:', xhr.responseText);
                
                addLogEntry('❌ AJAX Error: ' + status + ' - ' + error, 'error');
                addLogEntry('🔍 Response: ' + xhr.responseText, 'error');
            },
            complete: function() {
                $button.html(originalText);
                $button.prop('disabled', false);
            }
        });
    });
    $('#check-db-status').on('click', function() {
        const $button = $(this);
        const originalText = $button.text();
        
        $button.html('<span class="email-loading"></span>Prüfe Status...');
        $button.prop('disabled', true);
        
        addLogEntry('🔍 Checking database status...', 'info');
        
        $.ajax({
            url: ajaxurl,
            type: 'POST',
            data: {
                action: 'get_email_database_status',
                nonce: nexora_ajax.nonce
            },
            success: function(response) {
                if (response.success) {
                    addLogEntry('✅ Database status retrieved', 'success');
                    $('#db-status-display').show();
                    const status = response.data;
                    
                    let statusHtml = `
                        <strong>Database Status:</strong><br>
                        Total Tables: ${status.total_tables}<br>
                        Existing Tables: ${status.existing_tables}<br>
                        Missing Tables: ${status.missing_tables}<br>
                        All Tables Exist: ${status.all_exist ? '✅ Yes' : '❌ No'}<br><br>
                        <strong>Table Details:</strong><br>
                    `;
                    
                    for (const [table, exists] of Object.entries(status.tables_exist)) {
                        const tableName = table.replace(/^.*_/, '');
                        const status = exists ? '✅ EXISTS' : '❌ MISSING';
                        statusHtml += `${tableName}: ${status}<br>`;
                    }
                    
                    $('#db-status-content').html(statusHtml);
                    
                } else {
                    addLogEntry('❌ ' + response.data, 'error');
                }
            },
            error: function() {
                addLogEntry('❌ Server error occurred', 'error');
            },
            complete: function() {
                $button.html(originalText);
                $button.prop('disabled', false);
            }
        });
    });
    $('#debug-database-tables').on('click', function() {
        const $button = $(this);
        const originalText = $button.text();
        console.log('=== JAVASCRIPT DEBUG: Debug button clicked ===');
        console.log('ajaxurl:', ajaxurl);
        console.log('nexora_ajax:', nexora_ajax);
        console.log('nonce:', nexora_ajax.nonce);
        
        $button.html('<span class="email-loading"></span>Analysiere...');
        $button.prop('disabled', true);
        
        addLogEntry('🔬 Starting comprehensive database analysis...', 'info');
        addLogEntry('🔍 Debug: Action = debug_database_tables', 'info');
        addLogEntry('🔍 Debug: Nonce = ' + nexora_ajax.nonce, 'info');
        
        const ajaxData = {
            action: 'debug_database_tables',
            nonce: nexora_ajax.nonce
        };
        
        console.log('AJAX data being sent:', ajaxData);
        
        $.ajax({
            url: ajaxurl,
            type: 'POST',
            data: ajaxData,
            timeout: 30000,
            success: function(response) {
                console.log('AJAX success response:', response);
                console.log('Response type:', typeof response);
                console.log('Response.success:', response.success);
                console.log('Response.data:', response.data);
                
                if (response.success) {
                    addLogEntry('✅ Database analysis completed', 'success');
                    console.log('Showing db-debug-analysis element');
                    $('#db-debug-analysis').show();
                    console.log('Element visible:', $('#db-debug-analysis').is(':visible'));
                    let debugInfo;
                    if (response.data && response.data.data) {
                        debugInfo = response.data.data;
                        console.log('Using nested data structure');
                    } else {
                        debugInfo = response.data;
                        console.log('Using direct data structure');
                    }
                    
                    console.log('Debug info:', debugInfo);
                    console.log('Debug info type:', typeof debugInfo);
                    console.log('Debug info keys:', Object.keys(debugInfo));
                    
                    let debugHtml = '<div class="debug-section">';
                    if (debugInfo.basic_info) {
                        debugHtml += `
                            <h5>🎯 Grundinformationen:</h5>
                            <strong>Methode:</strong> ${debugInfo.basic_info.method_called}<br>
                            <strong>Zeitstempel:</strong> ${debugInfo.basic_info.timestamp}<br>
                            <strong>Benutzer ID:</strong> ${debugInfo.basic_info.user_id}<br>
                            <strong>Kann verwalten:</strong> ${debugInfo.basic_info.user_can_manage_options ? '✅ Ja' : '❌ Nein'}<br><br>
                        `;
                    } else {
                        debugHtml += '<h5>❌ Basic Info nicht verfügbar</h5><br>';
                    }
                    if (debugInfo.current_method) {
                        debugHtml += `
                            <h5>🎯 Aktuelle Methode:</h5>
                            <strong>${debugInfo.current_method}</strong><br>
                            <em>${debugInfo.method_description}</em><br><br>
                        `;
                    } else {
                        debugHtml += '<h5>❌ Current Method nicht verfügbar</h5><br>';
                    }
                    if (debugInfo.save_flow) {
                        debugHtml += '<h5>💾 Speicher-Flow:</h5>';
                        for (const [step, description] of Object.entries(debugInfo.save_flow)) {
                            debugHtml += `${step}. ${description}<br>`;
                        }
                        debugHtml += '<br>';
                    } else {
                        debugHtml += '<h5>❌ Save Flow nicht verfügbar</h5><br>';
                    }
                    if (debugInfo.load_flow) {
                        debugHtml += '<h5>📥 Lade-Flow:</h5>';
                        for (const [step, description] of Object.entries(debugInfo.load_flow)) {
                            debugHtml += `${step}. ${description}<br>`;
                        }
                        debugHtml += '<br>';
                    } else {
                        debugHtml += '<h5>❌ Load Flow nicht verfügbar</h5><br>';
                    }
                    if (debugInfo.wordpress_options && debugInfo.wordpress_options.nexora_smtp_settings) {
                        debugHtml += '<h5>⚙️ WordPress Options:</h5>';
                        const options = debugInfo.wordpress_options.nexora_smtp_settings;
                        debugHtml += `nexora_smtp_settings: ${options.exists ? '✅ EXISTS' : '❌ NOT FOUND'}<br>`;
                        debugHtml += `Settings count: ${options.count}<br>`;
                        if (options.exists && options.count > 0) {
                            debugHtml += '<strong>Available settings:</strong><br>';
                            options.keys.forEach(key => {
                                debugHtml += `  - ${key}<br>`;
                            });
                        }
                        debugHtml += '<br>';
                    } else {
                        debugHtml += '<h5>❌ WordPress Options nicht verfügbar</h5><br>';
                    }
                    if (debugInfo.wordpress_options && debugInfo.wordpress_options.nexora_smtp_settings) {
                        debugHtml += '<h5>⚙️ WordPress Options Details:</h5>';
                        const options = debugInfo.wordpress_options.nexora_smtp_settings;
                        debugHtml += `<strong>Settings exist:</strong> ${options.exists ? '✅ Yes' : '❌ No'}<br>`;
                        debugHtml += `<strong>Total settings:</strong> ${options.count}<br>`;
                        if (options.exists && options.count > 0) {
                            debugHtml += '<strong>Current settings:</strong><br>';
                            options.keys.forEach(key => {
                                debugHtml += `  • ${key}<br>`;
                            });
                        }
                        debugHtml += '<br>';
                    }
                    debugHtml += '<h5>🗄️ Database Tables Analysis:</h5>';
                    debugHtml += '<strong>Current System:</strong> Custom Database Tables<br>';
                    debugHtml += '<strong>Storage Location:</strong> nexora_email_smtp_settings table<br>';
                    debugHtml += '<strong>Table Structure:</strong> Direct columns (enabled, host, port, etc.)<br><br>';
                    
                    debugHtml += '<strong>Required Tables:</strong><br>';
                    debugHtml += '• nexora_email_smtp_settings - SMTP settings (Direct columns)<br>';
                    debugHtml += '• nexora_email_logs - Email logs<br>';
                    debugHtml += '• nexora_email_templates - Email templates<br>';
                    debugHtml += '• nexora_email_notifications - Notifications<br>';
                    debugHtml += '• nexora_email_queue - Email queue<br><br>';
                    
                    debugHtml += '<strong>Current vs Required:</strong><br>';
                    debugHtml += '✅ <strong>Current:</strong> Custom Database Tables<br>';
                    debugHtml += '❌ <strong>Old:</strong> WordPress Options (wp_options table)<br>';
                    debugHtml += '🔄 <strong>Status:</strong> Using Custom Database Tables<br><br>';
                    
                    debugHtml += '<strong>Migration Completed:</strong><br>';
                    debugHtml += '1. ✅ Custom tables created<br>';
                    debugHtml += '2. ✅ Code updated to use custom tables<br>';
                    debugHtml += '3. ✅ WordPress Options dependency removed<br>';
                    debugHtml += '4. ✅ System now fully independent<br>';
                    
                    debugHtml += '</div>';
                    debugHtml += '<div class="debug-section">';
                    debugHtml += '<h5>🔍 Raw Response Debug:</h5>';
                    debugHtml += `<strong>Response Type:</strong> ${typeof response}<br>`;
                    debugHtml += `<strong>Response Success:</strong> ${response.success}<br>`;
                    debugHtml += `<strong>Response Data Type:</strong> ${typeof response.data}<br>`;
                    debugHtml += `<strong>Response Data Keys:</strong> ${Object.keys(response.data).join(', ')}<br>`;
                    debugHtml += `<strong>Nested Data Keys:</strong> ${response.data.data ? Object.keys(response.data.data).join(', ') : 'None'}<br>`;
                    debugHtml += '</div>';
                    
                    console.log('Generated HTML:', debugHtml);
                    console.log('Setting HTML to db-debug-content');
                    
                    $('#db-debug-content').html(debugHtml);
                    
                    console.log('HTML set successfully');
                    console.log('Content length:', $('#db-debug-content').html().length);
                    
                } else {
                    addLogEntry('❌ ' + response.data, 'error');
                }
            },
            error: function(xhr, status, error) {
                console.log('AJAX error:', {xhr, status, error});
                console.log('Response text:', xhr.responseText);
                console.log('Status code:', xhr.status);
                
                addLogEntry('❌ AJAX Error: ' + status + ' - ' + error, 'error');
                addLogEntry('🔍 Response: ' + xhr.responseText, 'error');
                addLogEntry('🔍 Status: ' + xhr.status, 'error');
            },
            complete: function() {
                $button.html(originalText);
                $button.prop('disabled', false);
            }
        });
    });
    $('#test-simple-ajax').on('click', function() {
        const $button = $(this);
        const originalText = $button.text();
        
        console.log('=== TEST: Simple AJAX button clicked ===');
        
        $button.html('<span class="email-loading"></span>Teste...');
        $button.prop('disabled', true);
        
        addLogEntry('🧪 Testing simple AJAX...', 'info');
        
        $.ajax({
            url: ajaxurl,
            type: 'POST',
            data: {
                action: 'test_simple_ajax'
            },
            timeout: 10000,
            success: function(response) {
                console.log('Simple AJAX success:', response);
                addLogEntry('✅ Simple AJAX test successful: ' + response.data.message, 'success');
                addLogEntry('🔍 User ID: ' + response.data.user_id, 'info');
                addLogEntry('🔍 Timestamp: ' + response.data.timestamp, 'info');
            },
            error: function(xhr, status, error) {
                console.log('Simple AJAX error:', {xhr, status, error});
                addLogEntry('❌ Simple AJAX test failed: ' + status + ' - ' + error, 'error');
                addLogEntry('🔍 Response: ' + xhr.responseText, 'error');
                addLogEntry('🔍 Status: ' + xhr.status, 'error');
            },
            complete: function() {
                $button.html(originalText);
                $button.prop('disabled', false);
            }
        });
    });
    function addLogEntry(message, type = 'info') {
        const time = new Date().toLocaleTimeString('de-DE');
        const logEntry = $('<div class="log-entry log-' + type + '"></div>').html(`
            <span class="log-time">${time}</span>
            <span class="log-message">${message}</span>
        `);
        
        $('#log-container').append(logEntry);
        $('#log-container').scrollTop($('#log-container')[0].scrollHeight);
    }
    addLogEntry('🚀 Independent Email System loaded successfully', 'success');
    addLogEntry('📊 Current system status: ' + ($('#smtp-enabled').is(':checked') ? 'Enabled' : 'Disabled'), 'info');
    function loadEmailQueue() {
        console.log('=== Loading Email Queue ===');
        
        $.ajax({
            url: ajaxurl,
            type: 'POST',
            data: {
                action: 'get_email_queue',
                nonce: nexora_ajax.nonce
            },
            success: function(response) {
                console.log('Load email queue response:', response);
                
                if (response.success && response.data) {
                    displayEmailQueue(response.data.emails);
                    updateQueueStatistics(response.data.statistics);
                    addLogEntry('✅ Email queue loaded successfully', 'success');
                } else {
                    displayEmailQueue([]);
                    updateQueueStatistics({total: 0, pending: 0, sent: 0, failed: 0});
                    addLogEntry('❌ Failed to load email queue: ' + response.data, 'error');
                }
            },
            error: function(xhr, status, error) {
                console.log('Load email queue error:', {xhr, status, error});
                displayEmailQueue([]);
                updateQueueStatistics({total: 0, pending: 0, sent: 0, failed: 0});
                addLogEntry('❌ Error loading email queue: ' + error, 'error');
            }
        });
    }
    function displayEmailQueue(emails) {
        const container = $('#email-queue-container');
        
        if (!emails || emails.length === 0) {
            container.html('<div class="no-emails">Keine E-Mails in der Warteschlange gefunden.</div>');
            return;
        }
        
        let html = '<div class="email-queue-table">';
        html += '<table class="wp-list-table widefat fixed striped">';
        html += '<thead><tr>';
        html += '<th>Typ</th>';
        html += '<th>Empfänger</th>';
        html += '<th>Betreff</th>';
        html += '<th>Status</th>';
        html += '<th>SMTP Host</th>';
        html += '<th>Erstellt</th>';
        html += '<th>Verarbeitungszeit</th>';
        html += '<th>Details</th>';
        html += '</tr></thead><tbody>';
        
        emails.forEach(function(email) {
            const typeClass = 'email-type-' + (email.email_type || 'notification');
            const typeLabel = getEmailTypeLabel(email.email_type);
            const statusClass = 'status-' + (email.status || 'pending');
            const statusLabel = getStatusLabel(email.status);
            
            html += '<tr>';
            html += '<td><span class="' + typeClass + '">' + typeLabel + '</span></td>';
            html += '<td>' + escapeHtml(email.recipient_email) + '</td>';
            html += '<td>' + escapeHtml(email.subject || 'Kein Betreff') + '</td>';
            html += '<td><span class="' + statusClass + '">' + statusLabel + '</span></td>';
            html += '<td>' + escapeHtml(email.smtp_host || 'N/A') + '</td>';
            html += '<td>' + formatDate(email.sent_at) + '</td>';
            html += '<td>' + (email.processing_time_ms || 'N/A') + 'ms</td>';
            html += '<td>';
            
            if (email.error_message) {
                html += '<button type="button" class="button button-small button-link" onclick="showEmailDetails(' + email.id + ')">🔍 Details</button>';
            }
            
            html += '</td>';
            html += '</tr>';
        });
        
        html += '</tbody></table></div>';
        
        container.html(html);
    }
    function updateQueueStatistics(stats) {
        $('#total-emails').text(stats.total || 0);
        $('#pending-emails').text(stats.pending || 0);
        $('#sent-emails').text(stats.sent || 0);
        $('#failed-emails').text(stats.failed || 0);
    }
    function getEmailTypeLabel(type) {
        const labels = {
            'notification': 'Benachrichtigung',
            'test': 'Test',
            'system': 'System',
            'error': 'Fehler'
        };
        return labels[type] || type;
    }
    function getStatusLabel(status) {
        const labels = {
            'pending': 'In Warteschlange',
            'sent': 'Gesendet',
            'failed': 'Fehlgeschlagen'
        };
        return labels[status] || status;
    }
    function formatDate(dateString) {
        if (!dateString) return 'N/A';
        const date = new Date(dateString);
        return date.toLocaleString('de-DE');
    }
    function showEmailDetails(emailId) {
        alert('Email details for ID: ' + emailId);
    }
    function loadAdminEmails() {
        console.log('=== Loading Admin Emails ===');
        
        $.ajax({
            url: ajaxurl,
            type: 'POST',
            data: {
                action: 'get_admin_emails',
                nonce: nexora_ajax.nonce
            },
            success: function(response) {
                console.log('Load admin emails response:', response);
                
                if (response.success && response.data) {
                    displayAdminEmails(response.data.admin_emails);
                    addLogEntry('✅ Admin emails loaded successfully', 'success');
                } else {
                    displayAdminEmails([]);
                    addLogEntry('❌ Failed to load admin emails: ' + response.data, 'error');
                }
            },
            error: function(xhr, status, error) {
                console.log('Load admin emails error:', {xhr, status, error});
                displayAdminEmails([]);
                addLogEntry('❌ Error loading admin emails: ' + error, 'error');
            }
        });
    }
    function displayAdminEmails(adminEmails) {
        const container = $('#admin-emails-container');
        
        if (!adminEmails || adminEmails.length === 0) {
            container.html('<div class="no-emails">Keine Admin E-Mails gefunden. Fügen Sie die erste hinzu.</div>');
            return;
        }
        
        let html = '<div class="admin-emails-table">';
        html += '<table class="wp-list-table widefat fixed striped">';
        html += '<thead><tr>';
        html += '<th>E-Mail-Adresse</th>';
        html += '<th>Anzeigename</th>';
        html += '<th>Rolle</th>';
        html += '<th>Status</th>';
        html += '<th>Benachrichtigungen</th>';
        html += '<th>Aktionen</th>';
        html += '</tr></thead><tbody>';
        
        adminEmails.forEach(function(email) {
            const roleLabels = {
                'primary': 'Primär',
                'secondary': 'Sekundär',
                'support': 'Support',
                'billing': 'Billing'
            };
            
            const roleLabel = roleLabels[email.role] || email.role;
            const statusText = email.is_active ? 'Aktiv' : 'Inaktiv';
            const statusClass = email.is_active ? 'status-active' : 'status-inactive';
            
            let notificationTypes = 'Alle';
            if (email.notification_types && email.notification_types !== '["all"]') {
                try {
                    const types = JSON.parse(email.notification_types);
                    if (Array.isArray(types) && types.length > 0) {
                        notificationTypes = types.join(', ');
                    }
                } catch (e) {
                    notificationTypes = 'Fehler beim Parsen';
                }
            }
            
            html += '<tr>';
            html += '<td>' + escapeHtml(email.email_address) + '</td>';
            html += '<td>' + escapeHtml(email.display_name) + '</td>';
            html += '<td>' + escapeHtml(roleLabel) + '</td>';
            html += '<td><span class="' + statusClass + '">' + statusText + '</span></td>';
            html += '<td>' + escapeHtml(notificationTypes) + '</td>';
            html += '<td class="actions">';
            if (email.is_active) {
                html += '<button type="button" class="button button-small deactivate-email" data-id="' + email.id + '">🚫 Deaktivieren</button>';
            } else {
                html += '<button type="button" class="button button-small activate-email" data-id="' + email.id + '">✅ Aktivieren</button>';
            }
            if (email.role !== 'primary' || adminEmails.filter(e => e.role === 'primary' && e.is_active).length > 1) {
                html += ' <button type="button" class="button button-small button-link-delete delete-email" data-id="' + email.id + '">🗑️ Löschen</button>';
            }
            
            html += '</td>';
            html += '</tr>';
        });
        
        html += '</tbody></table></div>';
        
        container.html(html);
        bindAdminEmailEvents();
    }
    function bindAdminEmailEvents() {
        $('.deactivate-email, .activate-email').on('click', function() {
            const emailId = $(this).data('id');
            const newStatus = $(this).hasClass('deactivate-email') ? 0 : 1;
            const action = newStatus ? 'aktivieren' : 'deaktivieren';
            
            if (confirm('Sind Sie sicher, dass Sie diese Admin E-Mail ' + action + ' möchten?')) {
                toggleAdminEmailStatus(emailId, newStatus);
            }
        });
        $('.delete-email').on('click', function() {
            const emailId = $(this).data('id');
            
            if (confirm('Sind Sie sicher, dass Sie diese Admin E-Mail löschen möchten? Diese Aktion kann nicht rückgängig gemacht werden.')) {
                deleteAdminEmail(emailId);
            }
        });
    }
    function toggleAdminEmailStatus(emailId, newStatus) {
        $.ajax({
            url: ajaxurl,
            type: 'POST',
            data: {
                action: 'toggle_admin_email_status',
                nonce: nexora_ajax.nonce,
                email_id: emailId,
                new_status: newStatus
            },
            success: function(response) {
                if (response.success) {
                    addLogEntry('✅ ' + response.data.message, 'success');
                    loadAdminEmails();
                } else {
                    addLogEntry('❌ ' + response.data, 'error');
                }
            },
            error: function() {
                addLogEntry('❌ Server error occurred', 'error');
            }
        });
    }
    function deleteAdminEmail(emailId) {
        $.ajax({
            url: ajaxurl,
            type: 'POST',
            data: {
                action: 'delete_admin_email',
                nonce: nexora_ajax.nonce,
                email_id: emailId
            },
            success: function(response) {
                if (response.success) {
                    addLogEntry('✅ ' + response.data.message, 'success');
                    loadAdminEmails();
                } else {
                    addLogEntry('❌ ' + response.data, 'error');
                }
            },
            error: function() {
                addLogEntry('❌ Server error occurred', 'error');
            }
        });
    }
    function bindEmailQueueEvents() {
        $('#refresh-email-queue').on('click', function() {
            loadEmailQueue();
        });
        $('#clear-failed-emails').on('click', function() {
            if (confirm('Sind Sie sicher, dass Sie alle fehlgeschlagenen E-Mails löschen möchten?')) {
                clearFailedEmails();
            }
        });
        $('#retry-failed-emails').on('click', function() {
            if (confirm('Sind Sie sicher, dass Sie alle fehlgeschlagenen E-Mails erneut versuchen möchten?')) {
                retryFailedEmails();
            }
        });
        $('#apply-filters').on('click', function() {
            applyEmailQueueFilters();
        });
        $('#clear-filters').on('click', function() {
            clearEmailQueueFilters();
        });
    }
    function clearFailedEmails() {
        $.ajax({
            url: ajaxurl,
            type: 'POST',
            data: {
                action: 'clear_failed_emails',
                nonce: nexora_ajax.nonce
            },
            success: function(response) {
                if (response.success) {
                    addLogEntry('✅ Failed emails cleared successfully', 'success');
                    loadEmailQueue();
                } else {
                    addLogEntry('❌ ' + response.data, 'error');
                }
            },
            error: function() {
                addLogEntry('❌ Server error occurred', 'error');
            }
        });
    }
    function retryFailedEmails() {
        $.ajax({
            url: ajaxurl,
            type: 'POST',
            data: {
                action: 'retry_failed_emails',
                nonce: nexora_ajax.nonce
            },
            success: function(response) {
                if (response.success) {
                    addLogEntry('✅ Failed emails retry initiated', 'success');
                    loadEmailQueue();
                } else {
                    addLogEntry('❌ ' + response.data, 'error');
                }
            },
            error: function() {
                addLogEntry('❌ Server error occurred', 'error');
            }
        });
    }
    function applyEmailQueueFilters() {
        const statusFilter = $('#status-filter').val();
        const typeFilter = $('#type-filter').val();
        const dateFilter = $('#date-filter').val();
        loadEmailQueueWithFilters(statusFilter, typeFilter, dateFilter);
    }
    function clearEmailQueueFilters() {
        $('#status-filter').val('all');
        $('#type-filter').val('all');
        $('#date-filter').val('');
        loadEmailQueue();
    }
    function loadEmailQueueWithFilters(status, type, date) {
        $.ajax({
            url: ajaxurl,
            type: 'POST',
            data: {
                action: 'get_email_queue',
                nonce: nexora_ajax.nonce,
                status_filter: status,
                type_filter: type,
                date_filter: date
            },
            success: function(response) {
                if (response.success && response.data) {
                    displayEmailQueue(response.data.emails);
                    updateQueueStatistics(response.data.statistics);
                }
            },
            error: function() {
                addLogEntry('❌ Error applying filters', 'error');
            }
        });
    }
    $('#add-admin-email-form').on('submit', function(e) {
        e.preventDefault();
        
        const formData = new FormData(this);
        formData.append('action', 'save_admin_email');
        formData.append('nonce', nexora_ajax.nonce);
        const selectedTypes = $('#admin-notification-types').val();
        if (selectedTypes && selectedTypes.length > 0) {
            formData.set('notification_types', selectedTypes);
        }
        
        $.ajax({
            url: ajaxurl,
            type: 'POST',
            data: formData,
            processData: false,
            contentType: false,
            success: function(response) {
                if (response.success) {
                    addLogEntry('✅ ' + response.data.message, 'success');
                    $('#add-admin-email-form')[0].reset();
                    loadAdminEmails();
                } else {
                    addLogEntry('❌ ' + response.data, 'error');
                }
            },
            error: function() {
                addLogEntry('❌ Server error occurred', 'error');
            }
        });
    });
    $('#refresh-admin-emails').on('click', function() {
        loadAdminEmails();
        addLogEntry('🔄 Admin emails list refreshed', 'info');
    });
    function escapeHtml(text) {
        const map = {
            '&': '&amp;',
            '<': '&lt;',
            '>': '&gt;',
            '"': '&quot;',
            "'": '&#039;'
        };
        return text.replace(/[&<>"']/g, function(m) { return map[m]; });
    }
    function loadTemplateSettings() {
        console.log('=== Loading Template Settings ===');
        
        $.ajax({
            url: ajaxurl,
            type: 'POST',
            data: {
                action: 'nexora_get_template_settings',
                nonce: nexora_ajax.nonce
            },
            success: function(response) {
                console.log('Load template settings response:', response);
                
                if (response.success && response.data) {
                    populateTemplateForm(response.data);
                    addLogEntry('✅ Template settings loaded successfully', 'success');
                } else {
                    addLogEntry('❌ Failed to load template settings: ' + response.data, 'error');
                }
            },
            error: function(xhr, status, error) {
                console.log('Load template settings error:', {xhr, status, error});
                addLogEntry('❌ Error loading template settings: ' + error, 'error');
            }
        });
    }
    function populateTemplateForm(settings) {
        $('#header-logo').val(settings.header_logo);
        $('#header-background-color').val(settings.header_background_color);
        $('#header-text-color').val(settings.header_text_color);
        $('#header-subtitle').val(settings.header_subtitle);
        $('#footer-background-color').val(settings.footer_background_color);
        $('#footer-text-color').val(settings.footer_text_color);
        $('#company-phone').val(settings.company_phone);
        $('#company-email').val(settings.company_email);
        $('#company-website').val(settings.company_website);
        $('#company-address').val(settings.company_address);
        $('#social-instagram').val(settings.social_instagram);
        $('#social-telegram').val(settings.social_telegram);
        $('#social-whatsapp').val(settings.social_whatsapp);
        $('#status-change-text').val(settings.status_change_text);
        $('#customer-welcome-text').val(settings.customer_welcome_text);
        $('#dashboard-link-text').val(settings.dashboard_link_text);
    }
    $('#template-customization-form').on('submit', function(e) {
        e.preventDefault();
        
        const formData = new FormData(this);
        formData.append('action', 'nexora_save_template_settings');
        formData.append('nonce', nexora_ajax.nonce);
        
        addLogEntry('💾 Saving template settings...', 'info');
        
        $.ajax({
            url: ajaxurl,
            type: 'POST',
            data: formData,
            processData: false,
            contentType: false,
            success: function(response) {
                if (response.success) {
                    addLogEntry('✅ ' + response.data, 'success');
                } else {
                    addLogEntry('❌ ' + response.data, 'error');
                }
            },
            error: function() {
                addLogEntry('❌ Server error occurred', 'error');
            }
        });
    });
    $('#reset-template-settings').on('click', function() {
        if (confirm('Sind Sie sicher, dass Sie alle Template-Einstellungen zurücksetzen möchten?')) {
            $.ajax({
                url: ajaxurl,
                type: 'POST',
                data: {
                    action: 'nexora_reset_template_settings',
                    nonce: nexora_ajax.nonce
                },
                success: function(response) {
                    if (response.success) {
                        addLogEntry('✅ ' + response.data, 'success');
                        setTimeout(() => loadTemplateSettings(), 1000);
                    } else {
                        addLogEntry('❌ ' + response.data, 'error');
                    }
                },
                error: function() {
                    addLogEntry('❌ Server error occurred', 'error');
                }
            });
        }
    });
    $('#preview-template').on('click', function() {
        const settings = {
            header_logo: $('#header-logo').val(),
            header_background_color: $('#header-background-color').val(),
            header_text_color: $('#header-text-color').val(),
            header_subtitle: $('#header-subtitle').val(),
            footer_background_color: $('#footer-background-color').val(),
            footer_text_color: $('#footer-text-color').val(),
            company_phone: $('#company-phone').val(),
            company_email: $('#company-email').val(),
            company_website: $('#company-website').val(),
            company_address: $('#company-address').val(),
            social_instagram: $('#social-instagram').val(),
            social_telegram: $('#social-telegram').val(),
            social_whatsapp: $('#social-whatsapp').val(),
            status_change_text: $('#status-change-text').val(),
            customer_welcome_text: $('#customer-welcome-text').val(),
            dashboard_link_text: $('#dashboard-link-text').val()
        };
        const previewHtml = generateTemplatePreview(settings);
        $('#preview-content').html(previewHtml);
        $('#template-preview').show();
        
        addLogEntry('👁️ Template preview generated', 'success');
    });
    function generateTemplatePreview(settings) {
        const logoUrl = '<?php echo plugin_dir_url(__FILE__); ?>../assets/images/' + (settings.header_logo || 'eccoripair.webp');
        
        return `
        <div style="max-width: 600px; margin: 0 auto; border: 2px solid #ddd; border-radius: 8px; overflow: hidden;">
            
            <div style="background-color: ${settings.header_background_color || '#273269'}; padding: 30px; text-align: center;">
                <div style="margin-bottom: 20px;">
                    <img src="${logoUrl}" alt="Logo" style="max-width: 150px; height: auto; border-radius: 6px;">
                </div>
                <div style="color: ${settings.header_text_color || '#ffffff'}; font-size: 16px; font-weight: 500;">
                    ${settings.header_subtitle || 'Qualitätsdienstleistungen zu fairen Preisen'}
                </div>
            </div>
            
            
            <div style="padding: 30px; background: #fff;">
                <h3 style="color: #2c3e50; text-align: center; margin-bottom: 20px;">Template-Vorschau</h3>
                <p style="color: #34495e; line-height: 1.6;">
                    Dies ist eine Vorschau Ihres angepassten E-Mail-Templates. 
                    Alle Änderungen werden in den tatsächlichen E-Mails angewendet.
                </p>
                
                <div style="background: #f8f9fa; border: 1px solid #dee2e6; border-radius: 8px; padding: 20px; margin: 20px 0;">
                    <h4 style="color: #273269; margin-top: 0;">Beispiel-Inhalt:</h4>
                    <p><strong>Status-Änderung Text:</strong> ${settings.status_change_text || 'Der Status Ihrer Serviceanfrage hat sich geändert.'}</p>
                    <p><strong>Dashboard-Link Text:</strong> ${settings.dashboard_link_text || '🚀 Ihr Dashboard aufrufen'}</p>
                </div>
            </div>
            
            
            <div style="background-color: ${settings.footer_background_color || '#273269'}; padding: 30px; text-align: center; color: ${settings.footer_text_color || '#ffffff'};">
                <h4 style="margin-top: 0; margin-bottom: 20px;">Kontaktinformationen</h4>
                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 15px; margin-bottom: 20px;">
                    <div>
                        <strong>📞 Telefon:</strong><br>
                        ${settings.company_phone || '+43 1 234 5678'}
                    </div>
                    <div>
                        <strong>📧 E-Mail:</strong><br>
                        ${settings.company_email || 'info@example.com'}
                    </div>
                </div>
                <div style="margin-bottom: 20px;">
                    <strong>🌐 Website:</strong> ${settings.company_website || 'https://example.com'}<br>
                    <strong>📍 Adresse:</strong> ${settings.company_address || 'Wien, Österreich'}
                </div>
                <div style="font-size: 14px; opacity: 0.9;">
                    © ${new Date().getFullYear()} Nexora. Alle Rechte vorbehalten.
                </div>
            </div>
        </div>`;
    }
    loadTemplateSettings();
    loadAdminEmails();
});
</script>
