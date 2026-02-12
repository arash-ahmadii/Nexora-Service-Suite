<?php

if (!defined('ABSPATH')) {
    exit;
}
$nonce = wp_create_nonce('nexora_email_templates_nonce');
?>

<div class="wrap">
    <h1>📧 E-Mail-Vorlagen verwalten</h1>
    <p>Hier können Sie die E-Mail-Vorlagen für verschiedene Ereignisse anpassen und bearbeiten.</p>
    
    <div class="Nexora Service Suite-email-templates-container">
        
        <div class="templates-list-section">
            <h2>Verfügbare Vorlagen</h2>
            <div class="templates-grid" id="templates-grid">
                
                <div class="loading-message">
                    <span class="spinner is-active"></span>
                    Lade Vorlagen...
                </div>
            </div>
        </div>
        
        
        <div class="template-editor-section" id="template-editor" style="display: none;">
            <div class="editor-header">
                <h2 id="editor-title">Vorlage bearbeiten</h2>
                <button type="button" class="button" id="close-editor">← Zurück zur Liste</button>
            </div>
            
            <form id="template-form">
                <input type="hidden" id="template-id" name="template_id">
                <input type="hidden" name="nonce" value="<?php echo esc_attr($nonce); ?>">
                
                <table class="form-table">
                    <tr>
                        <th scope="row">
                            <label for="template-title">Titel der Vorlage</label>
                        </th>
                        <td>
                            <input type="text" id="template-title" name="title" class="regular-text" required>
                            <p class="description">Der interne Name dieser Vorlage</p>
                        </td>
                    </tr>
                    <tr>
                        <th scope="row">
                            <label for="template-subject">E-Mail-Betreff</label>
                        </th>
                        <td>
                            <input type="text" id="template-subject" name="subject" class="large-text" required style="font-family: Arial, sans-serif;">
                            <p class="description">
                                Der Betreff der E-Mail (verfügbare Variablen: {customer_name}, {request_id}, etc.)<br>
                                <strong>💡 Tipp:</strong> Sie können Emojis und Sonderzeichen verwenden - diese werden automatisch korrekt kodiert.
                            </p>
                            <div class="subject-preview" id="subject-preview" style="margin-top: 10px; padding: 8px; background: #f0f0f0; border: 1px solid #ddd; border-radius: 4px; font-family: Arial, sans-serif; display: none;">
                                <strong>Vorschau:</strong> <span id="preview-text"></span>
                            </div>
                        </td>
                    </tr>
                    <tr>
                        <th scope="row">
                            <label for="template-message">E-Mail-Nachricht</label>
                        </th>
                        <td>
                            <textarea id="template-message" name="message" rows="15" class="large-text" required></textarea>
                            <p class="description">
                                Der Hauptinhalt der E-Mail. Verfügbare Variablen:
                                <br><strong>Kunde:</strong> {customer_name}, {customer_email}
                                <br><strong>Anfrage:</strong> {request_id}, {service_type}, {request_date}, {current_status}
                                <br><strong>Status:</strong> {old_status}, {new_status}, {change_date}, {status_description}
                                <br><strong>Unternehmen:</strong> {company_name}, {company_email}, {company_phone}
                            </p>
                        </td>
                    </tr>
                </table>
                
                <div class="editor-actions">
                    <button type="submit" class="button button-primary">💾 Speichern</button>
                    <button type="button" class="button" id="reset-template">🔄 Zurücksetzen</button>
                    <button type="button" class="button" id="preview-template">👁️ Vorschau</button>
                </div>
            </form>
        </div>
    </div>
</div>

<div id="preview-modal" class="Nexora Service Suite-modal" style="display: none;">
    <div class="modal-content">
        <div class="modal-header">
            <h3>E-Mail-Vorschau</h3>
            <button type="button" class="modal-close">&times;</button>
        </div>
        <div class="modal-body">
            <div id="preview-content">
                
            </div>
        </div>
        <div class="modal-footer">
            <button type="button" class="button" id="close-preview">Schließen</button>
        </div>
    </div>
</div>

<style>
.Nexora Service Suite-email-templates-container {
    max-width: 1200px;
}

.templates-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
    gap: 20px;
    margin-top: 20px;
}

.template-card {
    border: 1px solid #ddd;
    border-radius: 8px;
    padding: 20px;
    background: #fff;
    box-shadow: 0 2px 4px rgba(0,0,0,0.1);
    transition: all 0.3s ease;
    cursor: pointer;
}

.template-card:hover {
    box-shadow: 0 4px 8px rgba(0,0,0,0.15);
    transform: translateY(-2px);
}

.template-card h3 {
    margin: 0 0 10px 0;
    color: #2c3e50;
    font-size: 18px;
}

.template-card .template-subject {
    color: #7f8c8d;
    font-size: 14px;
    margin-bottom: 10px;
    font-style: italic;
}

.template-card .template-preview {
    color: #34495e;
    font-size: 13px;
    line-height: 1.4;
    max-height: 100px;
    overflow: hidden;
    text-overflow: ellipsis;
    display: -webkit-box;
    -webkit-line-clamp: 4;
    -webkit-box-orient: vertical;
}

.template-editor-section {
    background: #fff;
    border: 1px solid #ddd;
    border-radius: 8px;
    padding: 30px;
    margin-top: 20px;
}

.editor-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 30px;
    padding-bottom: 20px;
    border-bottom: 2px solid #ecf0f1;
}

.editor-actions {
    margin-top: 30px;
    padding-top: 20px;
    border-top: 1px solid #ecf0f1;
}

.editor-actions .button {
    margin-right: 10px;
}

.loading-message {
    text-align: center;
    padding: 40px;
    color: #7f8c8d;
}

.Nexora Service Suite-modal {
    position: fixed;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    background: rgba(0,0,0,0.5);
    z-index: 9999;
    display: flex;
    align-items: center;
    justify-content: center;
}

.modal-content {
    background: #fff;
    border-radius: 8px;
    max-width: 800px;
    width: 90%;
    max-height: 80vh;
    overflow: hidden;
    box-shadow: 0 10px 30px rgba(0,0,0,0.3);
}

.modal-header {
    padding: 20px;
    border-bottom: 1px solid #ddd;
    display: flex;
    justify-content: space-between;
    align-items: center;
}

.modal-close {
    background: none;
    border: none;
    font-size: 24px;
    cursor: pointer;
    color: #999;
}

.modal-body {
    padding: 20px;
    max-height: 60vh;
    overflow-y: auto;
}

.modal-footer {
    padding: 20px;
    border-top: 1px solid #ddd;
    text-align: right;
}

#preview-content {
    background: #f8f9fa;
    padding: 20px;
    border-radius: 4px;
    font-family: Arial, sans-serif;
    line-height: 1.6;
    white-space: pre-wrap;
}

.success-message {
    background: #d4edda;
    color: #155724;
    padding: 10px 15px;
    border-radius: 4px;
    margin: 10px 0;
    border: 1px solid #c3e6cb;
}

.error-message {
    background: #f8d7da;
    color: #721c24;
    padding: 10px 15px;
    border-radius: 4px;
    margin: 10px 0;
    border: 1px solid #f5c6cb;
}
</style>

<script>
jQuery(document).ready(function($) {
    let currentTemplate = null;
    loadTemplates();
    $('#close-editor').on('click', function() {
        $('#template-editor').hide();
        $('.templates-list-section').show();
    });
    $('#template-form').on('submit', function(e) {
        e.preventDefault();
        saveTemplate();
    });
    $('#reset-template').on('click', function() {
        if (confirm('Sind Sie sicher, dass Sie diese Vorlage auf die Standardeinstellungen zurücksetzen möchten?')) {
            resetTemplate();
        }
    });
    $('#preview-template').on('click', function() {
        previewTemplate();
    });
    $('.modal-close, #close-preview').on('click', function() {
        $('#preview-modal').hide();
    });
    function loadTemplates() {
        $.ajax({
            url: ajaxurl,
            type: 'POST',
            data: {
                action: 'nexora_get_all_email_templates',
                nonce: '<?php echo esc_js($nonce); ?>'
            },
            success: function(response) {
                if (response.success) {
                    displayTemplates(response.data);
                } else {
                    showError('Fehler beim Laden der Vorlagen: ' + response.data);
                }
            },
            error: function() {
                showError('Fehler beim Laden der Vorlagen');
            }
        });
    }
    function displayTemplates(templates) {
        const grid = $('#templates-grid');
        grid.empty();
        
        if (templates.length === 0) {
            grid.html('<div class="loading-message">Keine Vorlagen gefunden</div>');
            return;
        }
        
        templates.forEach(function(template) {
            const card = $(`
                <div class="template-card" data-template-id="${template.id}">
                    <h3>${template.title}</h3>
                    <div class="template-subject">${template.subject}</div>
                    <div class="template-preview">${template.message.substring(0, 200)}...</div>
                </div>
            `);
            
            card.on('click', function() {
                editTemplate(template.id);
            });
            
            grid.append(card);
        });
    }
    function editTemplate(templateId) {
        $.ajax({
            url: ajaxurl,
            type: 'POST',
            data: {
                action: 'nexora_get_email_template',
                template_id: templateId,
                nonce: '<?php echo esc_js($nonce); ?>'
            },
            success: function(response) {
                if (response.success) {
                    currentTemplate = response.data;
                    loadTemplateIntoEditor(response.data);
                    $('.templates-list-section').hide();
                    $('#template-editor').show();
                } else {
                    showError('Fehler beim Laden der Vorlage: ' + response.data);
                }
            },
            error: function() {
                showError('Fehler beim Laden der Vorlage');
            }
        });
    }
    $('#template-subject').on('input', function() {
        const subject = $(this).val();
        if (subject.trim()) {
            $('#preview-text').text(subject);
            $('#subject-preview').show();
        } else {
            $('#subject-preview').hide();
        }
    });
    function loadTemplateIntoEditor(template) {
        $('#template-id').val(template.id);
        $('#template-title').val(template.title);
        $('#template-subject').val(template.subject);
        $('#template-message').val(template.message);
        $('#editor-title').text('Vorlage bearbeiten: ' + template.title);
    }
    function saveTemplate() {
        const formData = {
            action: 'nexora_save_email_template',
            template_id: $('#template-id').val(),
            title: $('#template-title').val(),
            subject: $('#template-subject').val(),
            message: $('#template-message').val(),
            nonce: '<?php echo esc_js($nonce); ?>'
        };
        
        $.ajax({
            url: ajaxurl,
            type: 'POST',
            data: formData,
            success: function(response) {
                if (response.success) {
                    showSuccess('Vorlage erfolgreich gespeichert!');
                    loadTemplates();
                } else {
                    showError('Fehler beim Speichern: ' + response.data);
                }
            },
            error: function() {
                showError('Fehler beim Speichern der Vorlage');
            }
        });
    }
    function resetTemplate() {
        $.ajax({
            url: ajaxurl,
            type: 'POST',
            data: {
                action: 'nexora_reset_email_template',
                template_id: $('#template-id').val(),
                nonce: '<?php echo esc_js($nonce); ?>'
            },
            success: function(response) {
                if (response.success) {
                    showSuccess('Vorlage erfolgreich zurückgesetzt!');
                    editTemplate($('#template-id').val());
                } else {
                    showError('Fehler beim Zurücksetzen: ' + response.data);
                }
            },
            error: function() {
                showError('Fehler beim Zurücksetzen der Vorlage');
            }
        });
    }
    function previewTemplate() {
        const subject = $('#template-subject').val();
        const message = $('#template-message').val();
        let previewMessage = message
            .replace(/{customer_name}/g, 'Max Mustermann')
            .replace(/{request_id}/g, '#12345')
            .replace(/{service_type}/g, 'Reparatur')
            .replace(/{request_date}/g, new Date().toLocaleDateString('de-DE'))
            .replace(/{current_status}/g, 'In Bearbeitung')
            .replace(/{old_status}/g, 'Neu')
            .replace(/{new_status}/g, 'In Bearbeitung')
            .replace(/{change_date}/g, new Date().toLocaleDateString('de-DE'))
            .replace(/{status_description}/g, 'Ihre Anfrage wird derzeit bearbeitet')
            .replace(/{company_name}/g, 'Nexora Service Suite')
            .replace(/{company_email}/g, 'info@example.com')
            .replace(/{company_phone}/g, '+43 123 456 789');
        
        $('#preview-content').html(`
            <strong>Betreff:</strong> ${subject}<br><br>
            <strong>Nachricht:</strong><br>
            ${previewMessage}
        `);
        
        $('#preview-modal').show();
    }
    function showSuccess(message) {
        $('.success-message, .error-message').remove();
        $('.Nexora Service Suite-email-templates-container').prepend(`<div class="success-message">${message}</div>`);
        setTimeout(function() {
            $('.success-message').fadeOut();
        }, 3000);
    }
    function showError(message) {
        $('.success-message, .error-message').remove();
        $('.Nexora Service Suite-email-templates-container').prepend(`<div class="error-message">${message}</div>`);
        setTimeout(function() {
            $('.error-message').fadeOut();
        }, 5000);
    }
});
</script>
