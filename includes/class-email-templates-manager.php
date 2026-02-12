<?php

if (!defined('ABSPATH')) {
    exit;
}

class Nexora_Email_Templates_Manager {
    
    private $template_manager;
    
    public function __construct() {
        $this->template_manager = new Nexora_Email_Template_Manager();
        add_action('wp_ajax_nexora_get_email_template', array($this, 'ajax_get_email_template'));
        add_action('wp_ajax_nexora_save_email_template', array($this, 'ajax_save_email_template'));
        add_action('wp_ajax_nexora_reset_email_template', array($this, 'ajax_reset_email_template'));
        add_action('wp_ajax_nexora_get_all_email_templates', array($this, 'ajax_get_all_email_templates'));
    }
    
    
    public function get_all_templates() {
        $messages = $this->template_manager->get_messages();
        $templates = array();
        
        foreach ($messages as $key => $message) {
            $templates[] = array(
                'id' => $key,
                'title' => $message['title'],
                'subject' => $message['subject'],
                'message' => $message['message'],
                'type' => 'message'
            );
        }
        
        return $templates;
    }
    
    
    public function get_template($template_id) {
        $message = $this->template_manager->get_message($template_id);
        
        if (!$message) {
            return false;
        }
        
        return array(
            'id' => $template_id,
            'title' => $message['title'],
            'subject' => $message['subject'],
            'message' => $message['message'],
            'type' => 'message'
        );
    }
    
    
    public function save_template($template_id, $template_data) {
        $message_data = array(
            'title' => sanitize_text_field($template_data['title']),
            'subject' => sanitize_text_field($template_data['subject']),
            'message' => sanitize_textarea_field($template_data['message'])
        );
        
        return $this->template_manager->save_message($template_id, $message_data);
    }
    
    
    public function reset_template($template_id) {
        $default_messages = $this->get_default_messages();
        
        if (isset($default_messages[$template_id])) {
            return $this->template_manager->save_message($template_id, $default_messages[$template_id]);
        }
        
        return false;
    }
    
    
    private function get_default_messages() {
        return array(
            'service_request_new' => array(
                'title' => 'Neue Serviceanfrage',
                'subject' => 'Ihre neue Serviceanfrage wurde registriert',
                'message' => 'Hallo {customer_name},

Ihre Serviceanfrage wurde erfolgreich registriert und wird derzeit geprüft.

Anfrage-Details:
• Anfrage-Nummer: {request_id}
• Service-Typ: {service_type}
• Registrierungsdatum: {request_date}
• Aktueller Status: {current_status}

Wir werden uns so schnell wie möglich mit Ihnen in Verbindung setzen.

Vielen Dank,
{company_name}'
            ),
            'service_status_change' => array(
                'title' => 'Service-Statusänderung',
                'subject' => 'Der Status Ihrer Serviceanfrage hat sich geändert',
                'message' => 'Hallo {customer_name},

Der Status Ihrer Serviceanfrage hat sich geändert.

Änderungsdetails:
• Anfrage-Nummer: {request_id}
• Vorheriger Status: {old_status}
• Neuer Status: {new_status}
• Änderungsdatum: {change_date}
• Beschreibung: {status_description}

Für weitere Details besuchen Sie bitte Ihr Benutzerpanel.

Vielen Dank,
{company_name}'
            ),
            'customer_welcome' => array(
                'title' => 'Willkommen neuer Kunde',
                'subject' => 'Willkommen in der {company_name} Familie!',
                'message' => 'Hallo {customer_name},

Willkommen in der {company_name} Familie!

Wir freuen uns, Sie als neuen Kunden begrüßen zu dürfen. Ihr Konto wurde erfolgreich erstellt und Sie können sich ab sofort in unserem System anmelden.

Sie haben in unserem Webportal ein Benutzerkonto erstellt und können über den folgenden Link auf Ihr Dashboard zugreifen:

https://example.com/my-account/

Bei Fragen oder Problemen stehen wir Ihnen gerne zur Verfügung.

Vielen Dank für Ihr Vertrauen,
{company_name}'
            ),
            'admin_notification' => array(
                'title' => 'Admin-Benachrichtigung',
                'subject' => 'Neue Benachrichtigung: {event_type}',
                'message' => 'Hallo {admin_name},

Ein neues Ereignis ist aufgetreten, das Ihre Aufmerksamkeit erfordert.

Ereignisdetails:
• Ereignistyp: {event_type}
• Beschreibung: {event_description}
• Datum: {event_date}
• Benutzer: {user_name}
• E-Mail: {user_email}

Bitte überprüfen Sie dies so schnell wie möglich.

Vielen Dank,
{company_name} System'
            ),
            'invoice_generated' => array(
                'title' => 'Neue Rechnung',
                'subject' => 'Ihre neue Rechnung ist bereit',
                'message' => 'Hallo {customer_name},

Ihre neue Rechnung ist bereit.

Rechnungsdetails:
• Rechnungsnummer: {invoice_number}
• Gesamtbetrag: {total_amount}
• Ausstellungsdatum: {invoice_date}
• Fälligkeitsdatum: {due_date}

Um die Rechnung anzuzeigen und herunterzuladen, besuchen Sie bitte Ihr Benutzerpanel.

Vielen Dank,
{company_name}'
            )
        );
    }
    
    
    public function ajax_get_all_email_templates() {
        check_ajax_referer('nexora_email_templates_nonce', 'nonce');
        
        if (!current_user_can('manage_options')) {
            wp_send_json_error('Unauthorized access');
            return;
        }
        
        $templates = $this->get_all_templates();
        wp_send_json_success($templates);
    }
    
    
    public function ajax_get_email_template() {
        check_ajax_referer('nexora_email_templates_nonce', 'nonce');
        
        if (!current_user_can('manage_options')) {
            wp_send_json_error('Unauthorized access');
            return;
        }
        
        $template_id = sanitize_text_field($_POST['template_id'] ?? '');
        
        if (empty($template_id)) {
            wp_send_json_error('Template ID is required');
            return;
        }
        
        $template = $this->get_template($template_id);
        
        if ($template) {
            wp_send_json_success($template);
        } else {
            wp_send_json_error('Template not found');
        }
    }
    
    
    public function ajax_save_email_template() {
        check_ajax_referer('nexora_email_templates_nonce', 'nonce');
        
        if (!current_user_can('manage_options')) {
            wp_send_json_error('Unauthorized access');
            return;
        }
        
        $template_id = sanitize_text_field($_POST['template_id'] ?? '');
        $template_data = array(
            'title' => sanitize_text_field($_POST['title'] ?? ''),
            'subject' => sanitize_text_field($_POST['subject'] ?? ''),
            'message' => sanitize_textarea_field($_POST['message'] ?? '')
        );
        
        if (empty($template_id) || empty($template_data['title']) || empty($template_data['subject']) || empty($template_data['message'])) {
            wp_send_json_error('All fields are required');
            return;
        }
        
        $result = $this->save_template($template_id, $template_data);
        
        if ($result) {
            wp_send_json_success('Template saved successfully');
        } else {
            wp_send_json_error('Failed to save template');
        }
    }
    
    
    public function ajax_reset_email_template() {
        check_ajax_referer('nexora_email_templates_nonce', 'nonce');
        
        if (!current_user_can('manage_options')) {
            wp_send_json_error('Unauthorized access');
            return;
        }
        
        $template_id = sanitize_text_field($_POST['template_id'] ?? '');
        
        if (empty($template_id)) {
            wp_send_json_error('Template ID is required');
            return;
        }
        
        $result = $this->reset_template($template_id);
        
        if ($result) {
            wp_send_json_success('Template reset to default successfully');
        } else {
            wp_send_json_error('Failed to reset template');
        }
    }
}
