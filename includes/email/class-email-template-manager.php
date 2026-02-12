<?php

if (!defined('ABSPATH')) {
    exit;
}

class Nexora_Email_Template_Manager {
    
    private $default_templates = array();
    private $default_messages = array();
    
    public function __construct() {
        $this->init_default_templates();
        $this->init_default_messages();
    }
    
    
    private function init_default_templates() {
        $this->default_templates = array(
            'master_template' => array(
                'name' => 'E-Mail-Hauptvorlage',
                'description' => 'Hauptvorlage, die für alle E-Mails verwendet wird',
                'html' => $this->get_master_template_html(),
                'css' => $this->get_master_template_css()
            )
        );
    }
    
    
    private function init_default_messages() {
        $this->default_messages = array(
            'service_request_new' => array(
                'title' => 'New service request',
                'subject' => 'Your new service request has been received',
                'message' => 'Hello {customer_name},

Your service request has been created successfully and is currently being reviewed.

Request details:
• Request ID: {request_id}
• Service type: {service_type}
• Created at: {request_date}
• Current status: {current_status}

We will contact you as soon as possible.

Best regards,
{company_name}'
            ),
            'service_status_change' => array(
                'title' => 'Service status update',
                'subject' => 'The status of your service request has changed',
                'message' => 'Hello {customer_name},

The status of your service request has changed.

Change details:
• Request ID: {request_id}
• Previous status: {old_status}
• New status: {new_status}
• Changed at: {change_date}
• Description: {status_description}

For more details, please log in to your customer dashboard.

Best regards,
{company_name}'
            ),
            'customer_welcome' => array(
                'title' => 'Welcome new customer',
                'subject' => 'Welcome to the {company_name} family!',
                'message' => 'Hello {customer_name},

Welcome to the {company_name} family!

We are happy to have you on board. Your account has been created successfully and you can now log in to our portal.

You can access your dashboard using the link below:

https://example.com/my-account/

If you have any questions, feel free to contact our support team.

Thank you for your trust,
{company_name}'
            ),
            'admin_notification' => array(
                'title' => 'Admin notification',
                'subject' => 'New notification: {event_type}',
                'message' => 'Hello {admin_name},

A new event has occurred that requires your attention.

Event details:
• Type: {event_type}
• Description: {event_description}
• Date: {event_date}
• User: {user_name}
• Email: {user_email}

Please review this as soon as possible.

Best regards,
{company_name} System'
            ),
            'invoice_generated' => array(
                'title' => 'New invoice',
                'subject' => 'Your new invoice is ready',
                'message' => 'Hello {customer_name},

Your new invoice is now available.

Invoice details:
• Invoice number: {invoice_number}
• Total amount: {total_amount}
• Issue date: {invoice_date}
• Due date: {due_date}

To view or download the invoice, please log in to your customer dashboard.

Best regards,
{company_name}'
            )
        );
    }
    
    
    private function get_master_template_html() {
        return '
        <!DOCTYPE html>
        <html lang="en" dir="ltr">
        <head>
            <meta charset="UTF-8">
            <meta name="viewport" content="width=device-width, initial-scale=1.0">
            <title>{email_subject}</title>
        </head>
        <body style="margin: 0; padding: 0; font-family: \'Segoe UI\', Tahoma, Geneva, Verdana, sans-serif; background-color: #f8f9fa; line-height: 1.6;">
            <div class="email-container" style="max-width: 650px; margin: 0 auto; background-color: #ffffff; box-shadow: 0 4px 20px rgba(0, 0, 0, 0.1); border-radius: 12px; overflow: hidden;">
                
                
                <div class="email-header" style="background-color: #273269; padding: 40px 30px; text-align: center;">
                    <div class="logo" style="margin-bottom: 25px;">
                        <img src="{logo_url}" alt="Nexora Service Suite" style="max-width: 200px; height: auto; border-radius: 8px;">
                    </div>
                    <div class="header-subtitle" style="color: #ffffff; font-size: 18px; font-weight: 500; opacity: 0.95;">
                        {company_slogan}
                    </div>
                </div>
                
                
                <div class="email-content" style="padding: 50px 40px;">
                    <div class="greeting" style="margin-bottom: 35px;">
                        <h2 style="color: #2c3e50; margin: 0 0 25px 0; font-size: 26px; font-weight: 600; text-align: center;">
                            {greeting}
                        </h2>
                    </div>
                    
                    <div class="main-message" style="margin-bottom: 35px; line-height: 1.7; color: #34495e; font-size: 16px; text-align: justify;">
                        {main_message}
                    </div>
                    
                    
                    <div class="dynamic-content" style="margin-bottom: 35px;">
                        {dynamic_content}
                    </div>
                    
                    
                    {action_button}
                    
                    <div class="footer-message" style="margin-top: 45px; padding-top: 35px; border-top: 2px solid #ecf0f1; text-align: center; color: #7f8c8d; font-size: 15px; font-style: italic;">
                        {footer_message}
                    </div>
                </div>
                
                
                <div class="email-footer" style="background-color: #273269; padding: 40px 30px; text-align: center; color: #ffffff;">
                    <div class="contact-info" style="margin-bottom: 30px;">
                        <h3 style="color: #ffffff; margin: 0 0 20px 0; font-size: 20px; font-weight: 600;">📞 Kontaktinformationen</h3>
                        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 20px; margin-bottom: 25px;">
                            <div style="text-align: left;">
                                <div style="margin-bottom: 12px; display: flex; align-items: center; gap: 10px;">
                                    <span style="font-size: 18px;">📞</span>
                                    <span style="font-weight: 500;">{company_phone}</span>
                                </div>
                                <div style="margin-bottom: 12px; display: flex; align-items: center; gap: 10px;">
                                    <span style="font-size: 18px;">📧</span>
                                    <span style="font-weight: 500;">{company_email}</span>
                                </div>
                            </div>
                            <div style="text-align: left;">
                                <div style="margin-bottom: 12px; display: flex; align-items: center; gap: 10px;">
                                    <span style="font-size: 18px;">🌐</span>
                                    <span style="font-weight: 500;">{company_website}</span>
                                </div>
                                <div style="margin-bottom: 12px; display: flex; align-items: center; gap: 10px;">
                                    <span style="font-size: 18px;">📍</span>
                                    <span style="font-weight: 500;">{company_address}</span>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="social-links" style="margin-bottom: 30px;">
                        <h4 style="color: #ffffff; margin: 0 0 20px 0; font-size: 18px; font-weight: 500;">📱 Folge uns auf Social Media</h4>
                        <div style="display: flex; justify-content: center; gap: 25px;">
                            <a href="{social_instagram}" style="color: #ffffff; text-decoration: none; padding: 12px 20px; background-color: rgba(255, 255, 255, 0.1); border-radius: 25px; font-weight: 500; transition: all 0.3s ease; display: flex; align-items: center; gap: 8px;">
                                📷 Instagram
                            </a>
                            <a href="{social_telegram}" style="color: #ffffff; text-decoration: none; padding: 12px 20px; background-color: rgba(255, 255, 255, 0.1); border-radius: 25px; font-weight: 500; transition: all 0.3s ease; display: flex; align-items: center; gap: 8px;">
                                📱 Telegram
                            </a>
                            <a href="{social_whatsapp}" style="color: #ffffff; text-decoration: none; padding: 12px 20px; background-color: rgba(255, 255, 255, 0.1); border-radius: 25px; font-weight: 500; transition: all 0.3s ease; display: flex; align-items: center; gap: 8px;">
                                💬 WhatsApp
                            </a>
                        </div>
                    </div>
                    
                    <div class="copyright" style="font-size: 14px; opacity: 0.9; padding-top: 25px; border-top: 1px solid rgba(255, 255, 255, 0.2);">
                        © {current_year} {company_name}. Alle Rechte vorbehalten.
                    </div>
                </div>
                
            </div>
        </body>
        </html>';
    }
    
    
    private function get_master_template_css() {
        return '
        .email-container {
            max-width: 650px;
            margin: 0 auto;
            background-color: #ffffff;
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.1);
            border-radius: 12px;
            overflow: hidden;
        }
        
        .email-header {
            background-color: #273269;
            padding: 40px 30px;
            text-align: center;
        }
        
        .email-content {
            padding: 50px 40px;
            line-height: 1.7;
        }
        
        .email-footer {
            background-color: #273269;
            padding: 40px 30px;
            text-align: center;
            color: #ffffff;
        }
        
        .action-button {
            display: inline-block;
            background: linear-gradient(135deg, #273269 0%, #34495e 100%);
            color: #ffffff;
            padding: 18px 35px;
            text-decoration: none;
            border-radius: 30px;
            font-weight: 600;
            font-size: 16px;
            margin: 25px 0;
            transition: all 0.3s ease;
            box-shadow: 0 4px 15px rgba(39, 50, 105, 0.3);
        }
        
        .action-button:hover {
            transform: translateY(-3px);
            box-shadow: 0 6px 20px rgba(39, 50, 105, 0.4);
            background: linear-gradient(135deg, #34495e 0%, #273269 100%);
        }
        
        .info-box {
            background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);
            border: 2px solid #dee2e6;
            border-radius: 12px;
            padding: 25px;
            margin: 25px 0;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.05);
        }
        
        .info-box h3 {
            color: #273269;
            margin-top: 0;
            margin-bottom: 20px;
            font-size: 20px;
            font-weight: 600;
            text-align: center;
        }
        
        .info-list {
            list-style: none;
            padding: 0;
            margin: 0;
        }
        
        .info-list li {
            padding: 12px 0;
            border-bottom: 1px solid #e9ecef;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        
        .info-list li:last-child {
            border-bottom: none;
        }
        
        .info-label {
            font-weight: 600;
            color: #273269;
            font-size: 15px;
        }
        
        .info-value {
            color: #495057;
            font-weight: 500;
            font-size: 15px;
        }
        
        
        .status-badge {
            display: inline-block;
            padding: 8px 16px;
            border-radius: 20px;
            font-size: 14px;
            font-weight: 600;
            text-transform: uppercase;
            color: #ffffff;
            text-align: center;
            min-width: 120px;
        }
        
        .status-badge.old {
            background-color: #e74c3c;
            border: 2px solid #c0392b;
        }
        
        .status-badge.new {
            background-color: #27ae60;
            border: 2px solid #229954;
        }
        
        .status-badge.pending {
            background-color: #f39c12;
            border: 2px solid #e67e22;
        }
        
        .status-badge.in-progress {
            background-color: #3498db;
            border: 2px solid #2980b9;
        }
        
        .status-badge.completed {
            background-color: #27ae60;
            border: 2px solid #229954;
        }
        
        
        .customer-account-info {
            background: linear-gradient(135deg, #e8f4fd 0%, #d1ecf1 100%);
            border: 2px solid #bee5eb;
            border-radius: 12px;
            padding: 25px;
            margin: 25px 0;
            text-align: center;
        }
        
        .customer-account-info h3 {
            color: #0c5460;
            margin-top: 0;
            margin-bottom: 20px;
            font-size: 20px;
            font-weight: 600;
        }
        
        .account-details {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 20px;
            margin-bottom: 25px;
        }
        
        .account-detail {
            text-align: center;
            padding: 15px;
            background-color: rgba(255, 255, 255, 0.7);
            border-radius: 8px;
        }
        
        .account-detail-label {
            font-weight: 600;
            color: #0c5460;
            margin-bottom: 8px;
            font-size: 14px;
        }
        
        .account-detail-value {
            color: #495057;
            font-weight: 500;
            font-size: 16px;
        }
        
        .dashboard-link {
            display: inline-block;
            background: linear-gradient(135deg, #273269 0%, #34495e 100%);
            color: #ffffff;
            padding: 15px 30px;
            text-decoration: none;
            border-radius: 25px;
            font-weight: 600;
            margin-top: 20px;
            transition: all 0.3s ease;
            box-shadow: 0 4px 15px rgba(39, 50, 105, 0.3);
        }
        
        .dashboard-link:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(39, 50, 105, 0.4);
        }
        
        
        @media (max-width: 600px) {
            .email-container {
                max-width: 100%;
                border-radius: 0;
            }
            
            .email-content {
                padding: 30px 20px;
            }
            
            .email-header,
            .email-footer {
                padding: 30px 20px;
            }
            
            .account-details {
                grid-template-columns: 1fr;
            }
            
            .social-links .social-links > div {
                flex-direction: column;
                gap: 15px;
            }
        }';
    }
    
    
    public function get_templates() {
        $saved_templates = get_option('nexora_email_templates', array());
        return array_merge($this->default_templates, $saved_templates);
    }
    
    
    public function get_messages() {
        $saved_messages = get_option('nexora_email_messages', array());
        return array_merge($this->default_messages, $saved_messages);
    }
    
    
    public function get_template($template_id) {
        $templates = $this->get_templates();
        return isset($templates[$template_id]) ? $templates[$template_id] : false;
    }
    
    
    public function get_message($message_id) {
        $messages = $this->get_messages();
        return isset($messages[$message_id]) ? $messages[$message_id] : false;
    }
    
    
    public function save_template($template_id, $template_data) {
        $templates = get_option('nexora_email_templates', array());
        $templates[$template_id] = $template_data;
        return update_option('nexora_email_templates', $templates);
    }
    
    
    public function save_message($message_id, $message_data) {
        $messages = get_option('nexora_email_messages', array());
        $messages[$message_id] = $message_data;
        return update_option('nexora_email_messages', $messages);
    }
    
    
    public function generate_email_html($message_id, $variables = array()) {
        $message = $this->get_message($message_id);
        $template = $this->get_template('master_template');
        
        if (!$message || !$template) {
            return false;
        }
        $subject = $this->replace_variables($message['subject'], $variables);
        $main_message = $this->replace_variables($message['message'], $variables);
        $company_info = $this->get_company_info();
        $template_variables = array_merge($company_info, array(
            'email_subject' => $subject,
            'greeting' => $this->get_greeting($variables),
            'main_message' => $main_message,
            'dynamic_content' => $this->generate_dynamic_content($message_id, $variables),
            'action_button' => $this->generate_action_button($message_id, $variables),
            'footer_message' => $this->get_footer_message($message_id),
            'current_year' => date('Y')
        ));
        $html = $template['html'];
        foreach ($template_variables as $key => $value) {
            $html = str_replace('{' . $key . '}', $value, $html);
        }
        $css = $template['css'] ?? '';
        if (!empty($css)) {
            $html = str_replace('</head>', '<style>' . $css . '</style></head>', $html);
        }
        
        return $html;
    }
    
    
    private function replace_variables($text, $variables) {
        foreach ($variables as $key => $value) {
            $text = str_replace('{' . $key . '}', $value, $text);
        }
        return $text;
    }
    
    
    public function get_company_info() {
        return array(
            'company_name' => get_option('nexora_email_sender_name', get_option('blogname', 'Our Company')),
            'company_slogan' => get_option('nexora_company_slogan', 'Quality services at fair prices'),
            'company_phone' => get_option('nexora_company_phone', '000-0000000'),
            'company_email' => get_option('nexora_email_sender_email', get_option('admin_email')),
            'company_website' => get_option('nexora_company_website', get_site_url()),
            'company_address' => get_option('nexora_company_address', 'City, Country'),
            'social_instagram' => get_option('nexora_social_instagram', '#'),
            'social_telegram' => get_option('nexora_social_telegram', '#'),
            'social_whatsapp' => get_option('nexora_social_whatsapp', '#'),
            'logo_url' => plugin_dir_url(dirname(__FILE__)) . 'assets/images/eccoripair.webp'
        );
    }
    
    
    private function get_greeting($variables) {
        $hour = date('H');
        if ($hour < 12) {
            return 'Good morning';
        } elseif ($hour < 17) {
            return 'Good afternoon';
        } else {
            return 'Good evening';
        }
    }
    
    
    private function generate_dynamic_content($message_id, $variables) {
        switch ($message_id) {
            case 'service_request_new':
                return $this->generate_service_request_content($variables);
            case 'service_status_change':
                return $this->generate_status_change_content($variables);
            case 'customer_welcome':
                return $this->generate_customer_welcome_content($variables);
            case 'invoice_generated':
                return $this->generate_invoice_content($variables);
            default:
                return '';
        }
    }
    
    
    private function generate_service_request_content($variables) {
        return '
        <div class="info-box">
            <h3>📋 Service request details</h3>
            <ul class="info-list">
                <li>
                    <span class="info-label">Request ID:</span>
                    <span class="info-value">' . ($variables['request_id'] ?? 'N/A') . '</span>
                </li>
                <li>
                    <span class="info-label">Service type:</span>
                    <span class="info-value">' . ($variables['service_type'] ?? 'N/A') . '</span>
                </li>
                <li>
                    <span class="info-label">Created at:</span>
                    <span class="info-value">' . ($variables['request_date'] ?? 'N/A') . '</span>
                </li>
                <li>
                    <span class="info-label">Current status:</span>
                    <span class="info-value">' . ($variables['current_status'] ?? 'N/A') . '</span>
                </li>
            </ul>
        </div>';
    }
    
    
    private function generate_status_change_content($variables) {
        return '
        <div class="info-box">
            <h3>🔄 Service-Statusänderung</h3>
            <ul class="info-list">
                <li>
                    <span class="info-label">Anfrage-Nummer:</span>
                    <span class="info-value">' . ($variables['request_id'] ?? 'N/A') . '</span>
                </li>
                <li>
                    <span class="info-label">Änderungsdatum:</span>
                    <span class="info-value">' . ($variables['change_date'] ?? 'N/A') . '</span>
                </li>
            </ul>
            
            <div style="text-align: center; margin: 30px 0;">
                <div style="margin-bottom: 20px;">
                    <div style="font-weight: 600; color: #273269; margin-bottom: 10px; font-size: 16px;">Vorheriger Status:</div>
                    <span class="status-badge old">' . ($variables['old_status'] ?? 'N/A') . '</span>
                </div>
                <div style="font-size: 24px; color: #273269; margin: 20px 0;">⬇️</div>
                <div>
                    <div style="font-weight: 600; color: #273269; margin-bottom: 10px; font-size: 16px;">Neuer Status:</div>
                    <span class="status-badge new">' . ($variables['new_status'] ?? 'N/A') . '</span>
                </div>
            </div>
            
            ' . (!empty($variables['status_description']) ? '
            <div style="background-color: #f8f9fa; border-left: 4px solid #273269; padding: 15px; margin-top: 20px; border-radius: 0 8px 8px 0;">
                <div style="font-weight: 600; color: #273269; margin-bottom: 8px;">📝 Beschreibung:</div>
                <div style="color: #495057; line-height: 1.6;">' . ($variables['status_description'] ?? '') . '</div>
            </div>
            ' : '') . '
        </div>';
    }

    
    private function generate_customer_welcome_content($variables) {
        return '
        <div class="customer-account-info">
            <h3>👋 Welcome to the {company_name} family!</h3>
            <p>Ihr Konto wurde erfolgreich erstellt und Sie können sich ab sofort in unserem System anmelden.</p>
            <div class="account-details">
                <div class="account-detail">
                    <div class="account-detail-label">Username:</div>
                    <div class="account-detail-value">' . ($variables['user_login'] ?? 'N/A') . '</div>
                </div>
                <div class="account-detail">
                    <div class="account-detail-label">E-Mail:</div>
                    <div class="account-detail-value">' . ($variables['user_email'] ?? 'N/A') . '</div>
                </div>
            </div>
            <a href="' . get_site_url() . '/my-account/" class="dashboard-link">
                🚀 Zu meinem Dashboard
            </a>
        </div>';
    }
    
    
    private function generate_invoice_content($variables) {
        return '
        <div class="info-box">
            <h3>🧾 Invoice details</h3>
            <ul class="info-list">
                <li>
                    <span class="info-label">Invoice number:</span>
                    <span class="info-value">' . ($variables['invoice_number'] ?? 'N/A') . '</span>
                </li>
                <li>
                    <span class="info-label">Gesamtbetrag:</span>
                    <span class="info-value">' . ($variables['total_amount'] ?? 'N/A') . '</span>
                </li>
                <li>
                    <span class="info-label">Ausstellungsdatum:</span>
                    <span class="info-value">' . ($variables['invoice_date'] ?? 'N/A') . '</span>
                </li>
                <li>
                    <span class="info-label">Fälligkeitsdatum:</span>
                    <span class="info-value">' . ($variables['invoice_due_date'] ?? 'N/A') . '</span>
                </li>
            </ul>
        </div>';
    }
    
    
    private function generate_action_button($message_id, $variables) {
        switch ($message_id) {
            case 'service_request_new':
                return '
                <div style="text-align: center; margin: 30px 0;">
                    <a href="' . get_site_url() . '/my-requests" class="action-button">
                        👀 Meine Anfragen anzeigen
                    </a>
                </div>';
            case 'service_status_change':
                return '
                <div style="text-align: center; margin: 30px 0;">
                    <a href="' . get_site_url() . '/my-account/" class="action-button">
                        🔍 Status überprüfen
                    </a>
                </div>';
            case 'customer_welcome':
                return '
                <div style="text-align: center; margin: 30px 0;">
                    <a href="' . get_site_url() . '/my-account/" class="action-button">
                        🚀 Zu meinem Dashboard
                    </a>
                </div>';
            case 'invoice_generated':
                return '
                <div style="text-align: center; margin: 30px 0;">
                    <a href="' . get_site_url() . '/my-invoices" class="action-button">
                        📥 Download invoice
                    </a>
                </div>';
            default:
                return '';
        }
    }
    
    
    private function get_footer_message($message_id) {
        switch ($message_id) {
            case 'service_request_new':
                return 'Bei Fragen kontaktieren Sie uns bitte. Wir sind bereit, Ihnen zu helfen.';
            case 'service_status_change':
                return 'For more information and the current status of your request, please visit your customer dashboard.';
            case 'customer_welcome':
                return 'Thank you for your trust. We are committed to providing high-quality services.';
            case 'invoice_generated':
                return 'Please pay the invoice amount before the due date.';
            default:
                return 'Vielen Dank für Ihre Wahl.';
        }
    }
    
    
    public function reset_to_defaults() {
        delete_option('nexora_email_templates');
        delete_option('nexora_email_messages');
        return true;
    }
    
    
    public function reset_template_to_default($template_id) {
        $templates = get_option('nexora_email_templates', array());
        if (isset($templates[$template_id])) {
            unset($templates[$template_id]);
            update_option('nexora_email_templates', $templates);
        }
        $messages = get_option('nexora_email_messages', array());
        if (isset($messages[$template_id])) {
            unset($messages[$template_id]);
            update_option('nexora_email_messages', $messages);
        }
        
        return true;
    }

    
    public function get_template_settings() {
        global $wpdb;
        $table_name = $wpdb->prefix . 'nexora_email_templates';
        $table_exists = $wpdb->get_var("SHOW TABLES LIKE '$table_name'") == $table_name;
        
        if (!$table_exists) {
            return $this->get_default_template_settings();
        }
        
        $settings = $wpdb->get_row("SELECT * FROM $table_name WHERE template_type = 'master' LIMIT 1");
        
        if (!$settings) {
            return $this->get_default_template_settings();
        }
        
        return array(
            'header_logo' => $settings->header_logo ?? '',
            'header_background_color' => $settings->header_background_color ?? '#273269',
            'header_text_color' => $settings->header_text_color ?? '#ffffff',
            'header_subtitle' => $settings->header_subtitle ?? 'Quality services at fair prices',
            'footer_background_color' => $settings->footer_background_color ?? '#273269',
            'footer_text_color' => $settings->footer_text_color ?? '#ffffff',
            'company_phone' => $settings->company_phone ?? '+43 1 234 5678',
            'company_email' => $settings->company_email ?? 'info@example.com',
            'company_website' => $settings->company_website ?? 'https://example.com',
            'company_address' => $settings->company_address ?? 'City, Country',
            'social_instagram' => $settings->social_instagram ?? '#',
            'social_telegram' => $settings->social_telegram ?? '#',
            'social_whatsapp' => $settings->social_whatsapp ?? '#',
            'status_change_text' => $settings->status_change_text ?? 'The status of your service request has changed.',
            'customer_welcome_text' => $settings->customer_welcome_text ?? 'Welcome to Nexora Service Suite! Your account has been created successfully.',
            'dashboard_link_text' => $settings->dashboard_link_text ?? '🚀 Open your dashboard'
        );
    }
    
    
    private function get_default_template_settings() {
        return array(
            'header_logo' => 'eccoripair.webp',
            'header_background_color' => '#273269',
            'header_text_color' => '#ffffff',
            'header_subtitle' => 'Quality services at fair prices',
            'footer_background_color' => '#273269',
            'footer_text_color' => '#ffffff',
            'company_phone' => '+43 1 234 5678',
            'company_email' => 'info@example.com',
            'company_website' => 'https://example.com',
            'company_address' => 'City, Country',
            'social_instagram' => '#',
            'social_telegram' => '#',
            'social_whatsapp' => '#',
            'status_change_text' => 'The status of your service request has changed.',
            'customer_welcome_text' => 'Welcome to Nexora Service Suite! Your account has been created successfully.',
            'dashboard_link_text' => '🚀 Open your dashboard'
        );
    }
    
    
    public function save_template_settings($settings) {
        global $wpdb;
        $table_name = $wpdb->prefix . 'nexora_email_templates';
        $table_exists = $wpdb->get_var("SHOW TABLES LIKE '$table_name'") == $table_name;
        
        if (!$table_exists) {
            $this->create_template_settings_table();
        }
        $existing = $wpdb->get_row("SELECT id FROM $table_name WHERE template_type = 'master' LIMIT 1");
        
        if ($existing) {
            $result = $wpdb->update(
                $table_name,
                array(
                    'header_logo' => $settings['header_logo'],
                    'header_background_color' => $settings['header_background_color'],
                    'header_text_color' => $settings['header_text_color'],
                    'header_subtitle' => $settings['header_subtitle'],
                    'footer_background_color' => $settings['footer_background_color'],
                    'footer_text_color' => $settings['footer_text_color'],
                    'company_phone' => $settings['company_phone'],
                    'company_email' => $settings['company_email'],
                    'company_website' => $settings['company_website'],
                    'company_address' => $settings['company_address'],
                    'social_instagram' => $settings['social_instagram'],
                    'social_telegram' => $settings['social_telegram'],
                    'social_whatsapp' => $settings['social_whatsapp'],
                    'status_change_text' => $settings['status_change_text'],
                    'customer_welcome_text' => $settings['customer_welcome_text'],
                    'dashboard_link_text' => $settings['dashboard_link_text'],
                    'updated_at' => current_time('mysql')
                ),
                array('template_type' => 'master'),
                array('%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s'),
                array('%s')
            );
        } else {
            $result = $wpdb->insert(
                $table_name,
                array(
                    'template_type' => 'master',
                    'header_logo' => $settings['header_logo'],
                    'header_background_color' => $settings['header_background_color'],
                    'header_text_color' => $settings['header_text_color'],
                    'header_subtitle' => $settings['header_subtitle'],
                    'footer_background_color' => $settings['footer_background_color'],
                    'footer_text_color' => $settings['footer_text_color'],
                    'company_phone' => $settings['company_phone'],
                    'company_email' => $settings['company_email'],
                    'company_website' => $settings['company_website'],
                    'company_address' => $settings['company_address'],
                    'social_instagram' => $settings['social_instagram'],
                    'social_telegram' => $settings['social_telegram'],
                    'social_whatsapp' => $settings['social_whatsapp'],
                    'status_change_text' => $settings['status_change_text'],
                    'customer_welcome_text' => $settings['customer_welcome_text'],
                    'dashboard_link_text' => $settings['dashboard_link_text'],
                    'created_at' => current_time('mysql'),
                    'updated_at' => current_time('mysql')
                ),
                array('%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s')
            );
        }
        
        return $result !== false;
    }
    
    
    private function create_template_settings_table() {
        global $wpdb;
        $table_name = $wpdb->prefix . 'nexora_email_templates';
        
        $charset_collate = $wpdb->get_charset_collate();
        
        $sql = "CREATE TABLE $table_name (
            id mediumint(9) NOT NULL AUTO_INCREMENT,
            template_type varchar(50) NOT NULL,
            header_logo varchar(255) DEFAULT '',
            header_background_color varchar(7) DEFAULT '#273269',
            header_text_color varchar(7) DEFAULT '#ffffff',
            header_subtitle text DEFAULT '',
            footer_background_color varchar(7) DEFAULT '#273269',
            footer_text_color varchar(7) DEFAULT '#ffffff',
            company_phone varchar(50) DEFAULT '',
            company_email varchar(100) DEFAULT '',
            company_website varchar(255) DEFAULT '',
            company_address text DEFAULT '',
            social_instagram varchar(255) DEFAULT '',
            social_telegram varchar(255) DEFAULT '',
            social_whatsapp varchar(255) DEFAULT '',
            status_change_text text DEFAULT '',
            customer_welcome_text text DEFAULT '',
            dashboard_link_text text DEFAULT '',
            created_at datetime DEFAULT CURRENT_TIMESTAMP,
            updated_at datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            PRIMARY KEY (id),
            UNIQUE KEY template_type (template_type)
        ) $charset_collate;";
        
        require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
        dbDelta($sql);
    }
    
    
    public function generate_custom_email_html($email_type, $data, $custom_settings = null) {
        if (!$custom_settings) {
            $custom_settings = $this->get_template_settings();
        }
        
        switch ($email_type) {
            case 'status_change':
                return $this->generate_custom_status_change_template($data, $custom_settings);
            case 'customer_registration':
                return $this->generate_custom_customer_registration_template($data, $custom_settings);
            default:
                return $this->generate_email_html($email_type, $data);
        }
    }
    
    
    private function generate_custom_status_change_template($data, $settings) {
        $logo_url = plugin_dir_url(dirname(__FILE__)) . 'assets/images/' . $settings['header_logo'];
        
        return '
        <!DOCTYPE html>
        <html lang="de" dir="ltr">
        <head>
            <meta charset="UTF-8">
            <meta name="viewport" content="width=device-width, initial-scale=1.0">
            <title>Status-Änderung für Ihre Serviceanfrage</title>
            <style>
                body { 
                    margin: 0; 
                    padding: 0; 
                    font-family: \'Segoe UI\', Tahoma, Geneva, Verdana, sans-serif; 
                    background-color: #f8f9fa; 
                    line-height: 1.6; 
                }
                
                .email-container {
                    max-width: 650px;
                    margin: 0 auto;
                    background-color: #ffffff;
                    box-shadow: 0 4px 20px rgba(0, 0, 0, 0.1);
                    border-radius: 12px;
                    overflow: hidden;
                }
                
                .email-header {
                    background-color: ' . $settings['header_background_color'] . ';
                    padding: 40px 30px;
                    text-align: center;
                }
                
                .logo {
                    margin-bottom: 25px;
                }
                
                .logo img {
                    max-width: 200px;
                    height: auto;
                    border-radius: 8px;
                }
                
                .header-subtitle {
                    color: ' . $settings['header_text_color'] . ';
                    font-size: 18px;
                    font-weight: 500;
                    opacity: 0.95;
                }
                
                .email-content {
                    padding: 50px 40px;
                    line-height: 1.7;
                }
                
                .greeting {
                    margin-bottom: 35px;
                    text-align: center;
                }
                
                .greeting h2 {
                    color: #2c3e50;
                    margin: 0 0 25px 0;
                    font-size: 26px;
                    font-weight: 600;
                }
                
                .main-message {
                    margin-bottom: 35px;
                    line-height: 1.7;
                    color: #34495e;
                    font-size: 16px;
                    text-align: justify;
                }
                
                .info-box {
                    background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);
                    border: 2px solid #dee2e6;
                    border-radius: 12px;
                    padding: 25px;
                    margin: 25px 0;
                    box-shadow: 0 2px 10px rgba(0, 0, 0, 0.05);
                }
                
                .info-box h3 {
                    color: ' . $settings['header_background_color'] . ';
                    margin-top: 0;
                    margin-bottom: 20px;
                    font-size: 20px;
                    font-weight: 600;
                    text-align: center;
                }
                
                .info-list {
                    list-style: none;
                    padding: 0;
                    margin: 0;
                }
                
                .info-list li {
                    padding: 12px 0;
                    border-bottom: 1px solid #e9ecef;
                    display: flex;
                    justify-content: space-between;
                    align-items: center;
                }
                
                .info-list li:last-child {
                    border-bottom: none;
                }
                
                .info-label {
                    font-weight: 600;
                    color: ' . $settings['header_background_color'] . ';
                    font-size: 15px;
                }
                
                .info-value {
                    color: #495057;
                    font-weight: 500;
                    font-size: 15px;
                }
                
                .status-badge {
                    display: inline-block;
                    padding: 8px 16px;
                    border-radius: 20px;
                    font-size: 14px;
                    font-weight: 600;
                    text-transform: uppercase;
                    color: #ffffff;
                    text-align: center;
                    min-width: 120px;
                }
                
                .status-badge.old {
                    background-color: #e74c3c;
                    border: 2px solid #c0392b;
                }
                
                .status-badge.new {
                    background-color: #27ae60;
                    border: 2px solid #229954;
                }
                
                .action-button {
                    display: inline-block;
                    background: linear-gradient(135deg, ' . $settings['header_background_color'] . ' 0%, #34495e 100%);
                    color: #ffffff;
                    padding: 18px 35px;
                    text-decoration: none;
                    border-radius: 30px;
                    font-weight: 600;
                    font-size: 16px;
                    margin: 25px 0;
                    transition: all 0.3s ease;
                    box-shadow: 0 4px 15px rgba(39, 50, 105, 0.3);
                }
                
                .action-button:hover {
                    transform: translateY(-3px);
                    box-shadow: 0 6px 20px rgba(39, 50, 105, 0.4);
                    background: linear-gradient(135deg, #34495e 0%, ' . $settings['header_background_color'] . ' 100%);
                }
                
                .email-footer {
                    background-color: ' . $settings['footer_background_color'] . ';
                    padding: 40px 30px;
                    text-align: center;
                    color: ' . $settings['footer_text_color'] . ';
                }
                
                .contact-info {
                    margin-bottom: 30px;
                }
                
                .contact-info h3 {
                    color: ' . $settings['footer_text_color'] . ';
                    margin: 0 0 20px 0;
                    font-size: 20px;
                    font-weight: 600;
                }
                
                .contact-grid {
                    display: grid;
                    grid-template-columns: 1fr 1fr;
                    gap: 20px;
                    margin-bottom: 25px;
                }
                
                .contact-item {
                    text-align: left;
                }
                
                .contact-item > div {
                    margin-bottom: 12px;
                    display: flex;
                    align-items: center;
                    gap: 10px;
                }
                
                .contact-item span:first-child {
                    font-size: 18px;
                }
                
                .contact-item span:last-child {
                    font-weight: 500;
                }
                
                .social-links {
                    margin-bottom: 30px;
                }
                
                .social-links h4 {
                    color: ' . $settings['footer_text_color'] . ';
                    margin: 0 0 20px 0;
                    font-size: 18px;
                    font-weight: 500;
                }
                
                .social-buttons {
                    display: flex;
                    justify-content: center;
                    gap: 25px;
                }
                
                .social-button {
                    color: ' . $settings['footer_text_color'] . ';
                    text-decoration: none;
                    padding: 12px 20px;
                    background-color: rgba(255, 255, 255, 0.1);
                    border-radius: 25px;
                    font-weight: 500;
                    transition: all 0.3s ease;
                    display: flex;
                    align-items: center;
                    gap: 8px;
                }
                
                .social-button:hover {
                    background-color: rgba(255, 255, 255, 0.2);
                    transform: translateY(-2px);
                }
                
                .copyright {
                    font-size: 14px;
                    opacity: 0.9;
                    padding-top: 25px;
                    border-top: 1px solid rgba(255, 255, 255, 0.2);
                }
                
                @media (max-width: 600px) {
                    .email-container {
                        max-width: 100%;
                        border-radius: 0;
                    }
                    
                    .email-content {
                        padding: 30px 20px;
                    }
                    
                    .email-header,
                    .email-footer {
                        padding: 30px 20px;
                    }
                    
                    .contact-grid {
                        grid-template-columns: 1fr;
                    }
                    
                    .social-buttons {
                        flex-direction: column;
                        gap: 15px;
                    }
                }
            </style>
        </head>
        <body>
            <div class="email-container">
                
                
                <div class="email-header">
                    <div class="logo">
                        <img src="' . $logo_url . '" alt="Nexora Service Suite">
                    </div>
                    <div class="header-subtitle">
                        ' . $settings['header_subtitle'] . '
                    </div>
                </div>
                
                
                <div class="email-content">
                    <div class="greeting">
                        <h2>Status-Änderung für Ihre Serviceanfrage</h2>
                    </div>
                    
                    <div class="main-message">
                        <p>Sehr geehrte(r) {customer_name},</p>
                        
                        <p>' . $settings['status_change_text'] . '</p>
                        
                        <p>Wir arbeiten kontinuierlich an Ihrer Anfrage und halten Sie über alle wichtigen Entwicklungen auf dem Laufenden.</p>
                    </div>
                    
                    <div class="info-box">
                        <h3>🔄 Status-Update</h3>
                        <ul class="info-list">
                            <li>
                                <span class="info-label">Anfrage-ID:</span>
                                <span class="info-value">#{request_id}</span>
                            </li>
                            <li>
                                <span class="info-label">Gerät:</span>
                                <span class="info-value">{serial} / {model}</span>
                            </li>
                        </ul>
                        
                        <div style="text-align: center; margin: 30px 0;">
                            <div style="margin-bottom: 20px;">
                                <div style="font-weight: 600; color: ' . $settings['header_background_color'] . '; margin-bottom: 10px; font-size: 16px;">Vorheriger Status:</div>
                                <span class="status-badge old">{old_status}</span>
                            </div>
                            <div style="font-size: 24px; color: ' . $settings['header_background_color'] . '; margin: 20px 0;">⬇️</div>
                            <div>
                                <div style="font-weight: 600; color: ' . $settings['header_background_color'] . '; margin-bottom: 10px; font-size: 16px;">Neuer Status:</div>
                                <span class="status-badge new">{new_status}</span>
                            </div>
                        </div>
                    </div>
                    
                    
                    <div style="text-align: center; margin: 30px 0;">
                        <a href="{customer_dashboard_url}" class="action-button">
                            ' . $settings['dashboard_link_text'] . '
                        </a>
                    </div>
                    
                    <div style="text-align: center; margin: 30px 0; color: #7f8c8d; font-style: italic;">
                        Bei Fragen stehen wir Ihnen gerne zur Verfügung.
                    </div>
                </div>
                
                
                <div class="email-footer">
                    <div class="contact-info">
                        <h3>📞 Kontaktinformationen</h3>
                        <div class="contact-grid">
                            <div class="contact-item">
                                <div>
                                    <span>📞</span>
                                    <span>' . $settings['company_phone'] . '</span>
                                </div>
                                <div>
                                    <span>📧</span>
                                    <span>' . $settings['company_email'] . '</span>
                                </div>
                            </div>
                            <div class="contact-item">
                                <div>
                                    <span>🌐</span>
                                    <span>' . $settings['company_website'] . '</span>
                                </div>
                                <div>
                                    <span>📍</span>
                                    <span>' . $settings['company_address'] . '</span>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="social-links">
                        <h4>📱 Folge uns auf Social Media</h4>
                        <div class="social-buttons">
                            <a href="' . $settings['social_instagram'] . '" class="social-button">
                                📷 Instagram
                            </a>
                            <a href="' . $settings['social_telegram'] . '" class="social-button">
                                📱 Telegram
                            </a>
                            <a href="' . $settings['social_whatsapp'] . '" class="social-button">
                                💬 WhatsApp
                            </a>
                        </div>
                    </div>
                    
                    <div class="copyright">
                        © ' . date('Y') . ' Nexora. Alle Rechte vorbehalten.
                    </div>
                </div>
                
            </div>
        </body>
        </html>';
    }
    
    
    private function generate_custom_customer_registration_template($data, $settings) {
        $logo_url = plugin_dir_url(dirname(__FILE__)) . 'assets/images/' . $settings['header_logo'];
        
        return '
        <!DOCTYPE html>
        <html lang="de" dir="ltr">
        <head>
            <meta charset="UTF-8">
            <meta name="viewport" content="width=device-width, initial-scale=1.0">
            <title>Willkommen bei Nexora Service Suite</title>
            <style>
                body { 
                    margin: 0; 
                    padding: 0; 
                    font-family: \'Segoe UI\', Tahoma, Geneva, Verdana, sans-serif; 
                    background-color: #f8f9fa; 
                    line-height: 1.6; 
                }
                
                .email-container {
                    max-width: 650px;
                    margin: 0 auto;
                    background-color: #ffffff;
                    box-shadow: 0 4px 20px rgba(0, 0, 0, 0.1);
                    border-radius: 12px;
                    overflow: hidden;
                }
                
                .email-header {
                    background-color: ' . $settings['header_background_color'] . ';
                    padding: 40px 30px;
                    text-align: center;
                }
                
                .logo {
                    margin-bottom: 25px;
                }
                
                .logo img {
                    max-width: 200px;
                    height: auto;
                    border-radius: 8px;
                }
                
                .header-subtitle {
                    color: ' . $settings['header_text_color'] . ';
                    font-size: 18px;
                    font-weight: 500;
                    opacity: 0.95;
                }
                
                .email-content {
                    padding: 50px 40px;
                    line-height: 1.7;
                }
                
                .greeting {
                    margin-bottom: 35px;
                    text-align: center;
                }
                
                .greeting h2 {
                    color: #2c3e50;
                    margin: 0 0 25px 0;
                    font-size: 26px;
                    font-weight: 600;
                }
                
                .main-message {
                    margin-bottom: 35px;
                    line-height: 1.7;
                    color: #34495e;
                    font-size: 16px;
                    text-align: justify;
                }
                
                .account-info-box {
                    background: linear-gradient(135deg, #e3f2fd 0%, #bbdefb 100%);
                    border: 2px solid #2196f3;
                    border-radius: 12px;
                    padding: 25px;
                    margin: 25px 0;
                    box-shadow: 0 2px 10px rgba(33, 150, 243, 0.1);
                }
                
                .account-info-box h3 {
                    color: #1976d2;
                    margin-top: 0;
                    margin-bottom: 20px;
                    font-size: 20px;
                    font-weight: 600;
                    text-align: center;
                }
                
                .account-details {
                    display: grid;
                    grid-template-columns: 1fr 1fr;
                    gap: 20px;
                    margin-bottom: 20px;
                }
                
                .account-detail {
                    background: rgba(255, 255, 255, 0.8);
                    padding: 15px;
                    border-radius: 8px;
                    border: 1px solid #e3f2fd;
                }
                
                .detail-label {
                    font-weight: 600;
                    color: #1976d2;
                    font-size: 14px;
                    margin-bottom: 5px;
                    display: block;
                }
                
                .detail-value {
                    color: #2c3e50;
                    font-weight: 500;
                    font-size: 16px;
                }
                
                .action-button {
                    display: inline-block;
                    background: linear-gradient(135deg, ' . $settings['header_background_color'] . ' 0%, #34495e 100%);
                    color: #ffffff;
                    padding: 18px 35px;
                    text-decoration: none;
                    border-radius: 30px;
                    font-weight: 600;
                    font-size: 16px;
                    margin: 25px 0;
                    transition: all 0.3s ease;
                    box-shadow: 0 4px 15px rgba(39, 50, 105, 0.3);
                }
                
                .action-button:hover {
                    transform: translateY(-3px);
                    box-shadow: 0 6px 20px rgba(39, 50, 105, 0.4);
                    background: linear-gradient(135deg, #34495e 0%, ' . $settings['header_background_color'] . ' 100%);
                }
                
                .email-footer {
                    background-color: ' . $settings['footer_background_color'] . ';
                    padding: 40px 30px;
                    text-align: center;
                    color: ' . $settings['footer_text_color'] . ';
                }
                
                .contact-info {
                    margin-bottom: 30px;
                }
                
                .contact-info h3 {
                    color: ' . $settings['footer_text_color'] . ';
                    margin: 0 0 20px 0;
                    font-size: 20px;
                    font-weight: 600;
                }
                
                .contact-grid {
                    display: grid;
                    grid-template-columns: 1fr 1fr;
                    gap: 20px;
                    margin-bottom: 25px;
                }
                
                .contact-item {
                    text-align: left;
                }
                
                .contact-item > div {
                    margin-bottom: 12px;
                    display: flex;
                    align-items: center;
                    gap: 10px;
                }
                
                .contact-item span:first-child {
                    font-size: 18px;
                }
                
                .contact-item span:last-child {
                    font-weight: 500;
                }
                
                .social-links {
                    margin-bottom: 30px;
                }
                
                .social-links h4 {
                    color: ' . $settings['footer_text_color'] . ';
                    margin: 0 0 20px 0;
                    font-size: 18px;
                    font-weight: 500;
                }
                
                .social-buttons {
                    display: flex;
                    justify-content: center;
                    gap: 25px;
                }
                
                .social-button {
                    color: ' . $settings['footer_text_color'] . ';
                    text-decoration: none;
                    padding: 12px 20px;
                    background-color: rgba(255, 255, 255, 0.1);
                    border-radius: 25px;
                    font-weight: 500;
                    transition: all 0.3s ease;
                    display: flex;
                    align-items: center;
                    gap: 8px;
                }
                
                .social-button:hover {
                    background-color: rgba(255, 255, 255, 0.2);
                    transform: translateY(-2px);
                }
                
                .copyright {
                    font-size: 14px;
                    opacity: 0.9;
                    padding-top: 25px;
                    border-top: 1px solid rgba(255, 255, 255, 0.2);
                }
                
                @media (max-width: 600px) {
                    .email-container {
                        max-width: 100%;
                        border-radius: 0;
                    }
                    
                    .email-content {
                        padding: 30px 20px;
                    }
                    
                    .email-header,
                    .email-footer {
                        padding: 30px 20px;
                    }
                    
                    .contact-grid,
                    .account-details {
                        grid-template-columns: 1fr;
                    }
                    
                    .social-buttons {
                        flex-direction: column;
                        gap: 15px;
                    }
                }
            </style>
        </head>
        <body>
            <div class="email-container">
                
                
                <div class="email-header">
                    <div class="logo">
                        <img src="' . $logo_url . '" alt="Nexora Service Suite">
                    </div>
                    <div class="header-subtitle">
                        ' . $settings['header_subtitle'] . '
                    </div>
                </div>
                
                
                <div class="email-content">
                    <div class="greeting">
                        <h2>Willkommen bei Nexora Service Suite!</h2>
                    </div>
                    
                    <div class="main-message">
                        <p>Sehr geehrte(r) {customer_name},</p>
                        
                        <p>' . $settings['customer_welcome_text'] . '</p>
                        
                        <p>Ihr Konto wurde erfolgreich erstellt und Sie können sich jetzt in Ihrem persönlichen Dashboard anmelden.</p>
                    </div>
                    
                    <div class="account-info-box">
                        <h3>📋 Ihre Kontoinformationen</h3>
                        <div class="account-details">
                            <div class="account-detail">
                                <span class="detail-label">Benutzername:</span>
                                <span class="detail-value">{username}</span>
                            </div>
                            <div class="account-detail">
                                <span class="detail-label">E-Mail-Adresse:</span>
                                <span class="detail-value">{email}</span>
                            </div>
                        </div>
                        
                        <div style="text-align: center; margin: 20px 0;">
                            <p style="color: #1976d2; font-weight: 500; margin: 0;">
                                Sie können sich jetzt in Ihrem Dashboard anmelden und alle Funktionen nutzen.
                            </p>
                        </div>
                    </div>
                    
                    
                    <div style="text-align: center; margin: 30px 0;">
                        <a href="https://example.com/my-account/" class="action-button">
                            ' . $settings['dashboard_link_text'] . '
                        </a>
                    </div>
                    
                    <div style="text-align: center; margin: 30px 0; color: #7f8c8d; font-style: italic;">
                        Bei Fragen stehen wir Ihnen gerne zur Verfügung.
                    </div>
                </div>
                
                
                <div class="email-footer">
                    <div class="contact-info">
                        <h3>📞 Kontaktinformationen</h3>
                        <div class="contact-grid">
                            <div class="contact-item">
                                <div>
                                    <span>📞</span>
                                    <span>' . $settings['company_phone'] . '</span>
                                </div>
                                <div>
                                    <span>📧</span>
                                    <span>' . $settings['company_email'] . '</span>
                                </div>
                            </div>
                            <div class="contact-item">
                                <div>
                                    <span>🌐</span>
                                    <span>' . $settings['company_website'] . '</span>
                                </div>
                                <div>
                                    <span>📍</span>
                                    <span>' . $settings['company_address'] . '</span>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="social-links">
                        <h4>📱 Folge uns auf Social Media</h4>
                        <div class="social-buttons">
                            <a href="' . $settings['social_instagram'] . '" class="social-button">
                                📷 Instagram
                            </a>
                            <a href="' . $settings['social_telegram'] . '" class="social-button">
                                📱 Telegram
                            </a>
                            <a href="' . $settings['social_whatsapp'] . '" class="social-button">
                                💬 WhatsApp
                            </a>
                        </div>
                    </div>
                    
                    <div class="copyright">
                        © ' . date('Y') . ' Nexora. Alle Rechte vorbehalten.
                    </div>
                </div>
                
            </div>
        </body>
        </html>';
    }
    
    
    public function install_default_templates() {
        try {
            global $wpdb;
            $table_name = $wpdb->prefix . 'nexora_email_templates';
            $table_exists = $wpdb->get_var("SHOW TABLES LIKE '$table_name'") == $table_name;
            if (!$table_exists) {
                error_log('Email templates table does not exist');
                return false;
            }
            $existing_templates = $wpdb->get_var("SELECT COUNT(*) FROM $table_name");
            if ($existing_templates > 0) {
                error_log('Email templates already exist in database');
                return true;
            }
            $default_templates = array(
                array(
                    'template_name' => 'new_service_request',
                    'template_type' => 'admin_notification',
                    'subject_template' => 'New service request #{request_id} received',
                    'body_template' => $this->get_default_new_service_request_template(),
                    'is_active' => 1,
                    'created_at' => current_time('mysql'),
                    'updated_at' => current_time('mysql')
                ),
                array(
                    'template_name' => 'service_status_change',
                    'template_type' => 'admin_notification',
                    'subject_template' => 'Status update for service request #{request_id}',
                    'body_template' => $this->get_default_status_change_template(),
                    'is_active' => 1,
                    'created_at' => current_time('mysql'),
                    'updated_at' => current_time('mysql')
                ),
                array(
                    'template_name' => 'customer_registration',
                    'template_type' => 'admin_notification',
                    'subject_template' => 'New customer registration: {customer_name}',
                    'body_template' => $this->get_default_customer_registration_template(),
                    'is_active' => 1,
                    'created_at' => current_time('mysql'),
                    'updated_at' => current_time('mysql')
                ),
                array(
                    'template_name' => 'invoice_generated',
                    'template_type' => 'admin_notification',
                    'subject_template' => 'New invoice generated for {customer_name}',
                    'body_template' => $this->get_default_invoice_generated_template(),
                    'is_active' => 1,
                    'created_at' => current_time('mysql'),
                    'updated_at' => current_time('mysql')
                ),
                array(
                    'template_name' => 'service_added',
                    'template_type' => 'admin_notification',
                    'subject_template' => 'Service added to service request #{request_id}',
                    'body_template' => $this->get_default_service_added_template(),
                    'is_active' => 1,
                    'created_at' => current_time('mysql'),
                    'updated_at' => current_time('mysql')
                ),
                array(
                    'template_name' => 'service_removed',
                    'template_type' => 'admin_notification',
                    'subject_template' => 'Service removed from service request #{request_id}',
                    'body_template' => $this->get_default_service_removed_template(),
                    'is_active' => 1,
                    'created_at' => current_time('mysql'),
                    'updated_at' => current_time('mysql')
                ),
                array(
                    'template_name' => 'service_quantity_changed',
                    'template_type' => 'admin_notification',
                    'subject_template' => 'Service quantity changed for service request #{request_id}',
                    'body_template' => $this->get_default_service_quantity_changed_template(),
                    'is_active' => 1,
                    'created_at' => current_time('mysql'),
                    'updated_at' => current_time('mysql')
                ),
                array(
                    'template_name' => 'customer_service_request_created',
                    'template_type' => 'customer_notification',
                    'subject_template' => 'Your service request #{request_id} has been created successfully',
                    'body_template' => $this->get_default_customer_service_request_created_template(),
                    'is_active' => 1,
                    'created_at' => current_time('mysql'),
                    'updated_at' => current_time('mysql')
                ),
                array(
                    'template_name' => 'customer_status_change',
                    'template_type' => 'customer_notification',
                    'subject_template' => 'Status update for your service request #{request_id}',
                    'body_template' => $this->get_default_customer_status_change_template(),
                    'is_active' => 1,
                    'created_at' => current_time('mysql'),
                    'updated_at' => current_time('mysql')
                ),
                array(
                    'template_name' => 'customer_service_added',
                    'template_type' => 'customer_notification',
                    'subject_template' => 'Service added to your service request #{request_id}',
                    'body_template' => $this->get_default_customer_service_added_template(),
                    'is_active' => 1,
                    'created_at' => current_time('mysql'),
                    'updated_at' => current_time('mysql')
                ),
                array(
                    'template_name' => 'customer_service_removed',
                    'template_type' => 'customer_notification',
                    'subject_template' => 'Service removed from your service request #{request_id}',
                    'body_template' => $this->get_default_customer_service_removed_template(),
                    'is_active' => 1,
                    'created_at' => current_time('mysql'),
                    'updated_at' => current_time('mysql')
                ),
                array(
                    'template_name' => 'customer_service_quantity_changed',
                    'template_type' => 'customer_notification',
                    'subject_template' => 'Service quantity changed for your service request #{request_id}',
                    'body_template' => $this->get_default_customer_service_quantity_changed_template(),
                    'is_active' => 1,
                    'created_at' => current_time('mysql'),
                    'updated_at' => current_time('mysql')
                )
            );
            $inserted_count = 0;
            foreach ($default_templates as $template) {
                $result = $wpdb->insert(
                    $table_name,
                    $template,
                    array('%s', '%s', '%s', '%s', '%d', '%s', '%s')
                );
                
                if ($result !== false) {
                    $inserted_count++;
                } else {
                    error_log('Failed to insert template: ' . $template['template_name'] . ' - ' . $wpdb->last_error);
                }
            }
            
            error_log("Installed $inserted_count default email templates");
            return $inserted_count > 0;
            
        } catch (Exception $e) {
            error_log('Exception installing default templates: ' . $e->getMessage());
            return false;
        }
    }
    
    
    private function get_default_new_service_request_template() {
        return '
        <h2>New service request received</h2>
        
        <p>A new service request has been submitted in the system.</p>
        
        <h3>Request details:</h3>
        <table style="border-collapse: collapse; width: 100%; margin: 20px 0;">
            <tr>
                <td style="padding: 8px; border: 1px solid #ddd; font-weight: bold; background: #f5f5f5;">Request ID:</td>
                <td style="padding: 8px; border: 1px solid #ddd;">#{request_id}</td>
            </tr>
            <tr>
                <td style="padding: 8px; border: 1px solid #ddd; font-weight: bold; background: #f5f5f5;">Customer:</td>
                <td style="padding: 8px; border: 1px solid #ddd;">{customer_name} ({customer_email})</td>
            </tr>
            <tr>
                <td style="padding: 8px; border: 1px solid #ddd; font-weight: bold; background: #f5f5f5;">Serial:</td>
                <td style="padding: 8px; border: 1px solid #ddd;">{serial}</td>
            </tr>
            <tr>
                <td style="padding: 8px; border: 1px solid #ddd; font-weight: bold; background: #f5f5f5;">Model:</td>
                <td style="padding: 8px; border: 1px solid #ddd;">{model}</td>
            </tr>
            <tr>
                <td style="padding: 8px; border: 1px solid #ddd; font-weight: bold; background: #f5f5f5;">Brand:</td>
                <td style="padding: 8px; border: 1px solid #ddd;">{brand_level_1} / {brand_level_2} / {brand_level_3}</td>
            </tr>
            <tr>
                <td style="padding: 8px; border: 1px solid #ddd; font-weight: bold; background: #f5f5f5;">Description:</td>
                <td style="padding: 8px; border: 1px solid #ddd;">{description}</td>
            </tr>
            <tr>
                <td style="padding: 8px; border: 1px solid #ddd; font-weight: bold; background: #f5f5f5;">Selected services:</td>
                <td style="padding: 8px; border: 1px solid #ddd;">{services_list}</td>
            </tr>
            <tr>
                <td style="padding: 8px; border: 1px solid #ddd; font-weight: bold; background: #f5f5f5;">Submitted at:</td>
                <td style="padding: 8px; border: 1px solid #ddd;">{created_date}</td>
            </tr>
        </table>
        
        <p><strong>Admin-Link:</strong> <a href="{admin_url}">Anfrage bearbeiten</a></p>
        
        <p>Diese E-Mail wurde automatisch generiert. Bitte antworten Sie nicht auf diese E-Mail.</p>
        ';
    }
    
    
    private function get_default_status_change_template() {
        return '
        <h2>Status-Änderung für Serviceanfrage</h2>
        
        <p>Der Status einer Serviceanfrage hat sich geändert.</p>
        
        <h3>Details:</h3>
        <table style="border-collapse: collapse; width: 100%; margin: 20px 0;">
            <tr>
                <td style="padding: 8px; border: 1px solid #ddd; font-weight: bold; background: #f5f5f5;">Anfrage-ID:</td>
                <td style="padding: 8px; border: 1px solid #ddd;">#{request_id}</td>
            </tr>
            <tr>
                <td style="padding: 8px; border: 1px solid #ddd; font-weight: bold; background: #f5f5f5;">Kunde:</td>
                <td style="padding: 8px; border: 1px solid #ddd;">{customer_name}</td>
            </tr>
            <tr>
                <td style="padding: 8px; border: 1px solid #ddd; font-weight: bold; background: #f5f5f5;">Gerät:</td>
                <td style="padding: 8px; border: 1px solid #ddd;">{serial} / {model}</td>
            </tr>
            <tr>
                <td style="padding: 8px; border: 1px solid #ddd; font-weight: bold; background: #f5f5f5;">Vorheriger Status:</td>
                <td style="padding: 8px; border: 1px solid #ddd;">{old_status}</td>
            </tr>
            <tr>
                <td style="padding: 8px; border: 1px solid #ddd; font-weight: bold; background: #f5f5f5;">Neuer Status:</td>
                <td style="padding: 8px; border: 1px solid #ddd;">{new_status}</td>
            </tr>
        </table>
        
        <p><strong>Admin-Link:</strong> <a href="{admin_url}">Anfrage bearbeiten</a></p>
        
        <p>Diese E-Mail wurde automatisch generiert. Bitte antworten Sie nicht auf diese E-Mail.</p>
        ';
    }
    
    
    private function get_default_customer_registration_template() {
        return '
        <h2>Neue Kundenregistrierung</h2>
        
        <p>Ein neuer Kunde hat sich im System registriert.</p>
        
        <h3>Kunden-Details:</h3>
        <table style="border-collapse: collapse; width: 100%; margin: 20px 0;">
            <tr>
                <td style="padding: 8px; border: 1px solid #ddd; font-weight: bold; background: #f5f5f5;">Name:</td>
                <td style="padding: 8px; border: 1px solid #ddd;">{customer_name}</td>
            </tr>
            <tr>
                <td style="padding: 8px; border: 1px solid #ddd; font-weight: bold; background: #f5f5f5;">E-Mail:</td>
                <td style="padding: 8px; border: 1px solid #ddd;">{customer_email}</td>
            </tr>
            <tr>
                <td style="padding: 8px; border: 1px solid #ddd; font-weight: bold; background: #f5f5f5;">Registriert am:</td>
                <td style="padding: 8px; border: 1px solid #ddd;">{registration_date}</td>
            </tr>
        </table>
        
        <p><strong>Admin-Link:</strong> <a href="{admin_url}">Kunde bearbeiten</a></p>
        
        <p>Diese E-Mail wurde automatisch generiert. Bitte antworten Sie nicht auf diese E-Mail.</p>
        ';
    }
    
    
    private function get_default_invoice_generated_template() {
        return '
        <h2>Neue Rechnung generiert</h2>
        
        <p>Eine neue Rechnung wurde im System generiert.</p>
        
        <h3>Rechnungs-Details:</h3>
        <table style="border-collapse: collapse; width: 100%; margin: 20px 0;">
            <tr>
                <td style="padding: 8px; border: 1px solid #ddd; font-weight: bold; background: #f5f5f5;">Rechnungs-ID:</td>
                <td style="padding: 8px; border: 1px solid #ddd;">#{invoice_id}</td>
            </tr>
            <tr>
                <td style="padding: 8px; border: 1px solid #ddd; font-weight: bold; background: #f5f5f5;">Kunde:</td>
                <td style="padding: 8px; border: 1px solid #ddd;">{customer_name} ({customer_email})</td>
            </tr>
            <tr>
                <td style="padding: 8px; border: 1px solid #ddd; font-weight: bold; background: #f5f5f5;">Generiert am:</td>
                <td style="padding: 8px; border: 1px solid #ddd;">{generation_date}</td>
            </tr>
        </table>
        
        <p><strong>Admin-Link:</strong> <a href="{admin_url}">Rechnung bearbeiten</a></p>
        
        <p>Diese E-Mail wurde automatisch generiert. Bitte antworten Sie nicht auf diese E-Mail.</p>
        ';
    }
    
    
    private function get_default_service_added_template() {
        return '
        <h2>Service zu Serviceanfrage hinzugefügt</h2>
        
        <p>Ein neuer Service wurde zu einer Serviceanfrage hinzugefügt.</p>
        
        <h3>Details:</h3>
        <table style="border-collapse: collapse; width: 100%; margin: 20px 0;">
            <tr>
                <td style="padding: 8px; border: 1px solid #ddd; font-weight: bold; background: #f5f5f5;">Anfrage-ID:</td>
                <td style="padding: 8px; border: 1px solid #ddd;">#{request_id}</td>
            </tr>
            <tr>
                <td style="padding: 8px; border: 1px solid #ddd; font-weight: bold; background: #f5f5f5;">Kunde:</td>
                <td style="padding: 8px; border: 1px solid #ddd;">{customer_name}</td>
            </tr>
            <tr>
                <td style="padding: 8px; border: 1px solid #ddd; font-weight: bold; background: #f5f5f5;">Gerät:</td>
                <td style="padding: 8px; border: 1px solid #ddd;">{serial} / {model}</td>
            </tr>
            <tr>
                <td style="padding: 8px; border: 1px solid #ddd; font-weight: bold; background: #f5f5f5;">Hinzugefügter Service:</td>
                <td style="padding: 8px; border: 1px solid #ddd;">{service_name}</td>
            </tr>
            <tr>
                <td style="padding: 8px; border: 1px solid #ddd; font-weight: bold; background: #f5f5f5;">Menge:</td>
                <td style="padding: 8px; border: 1px solid #ddd;">{quantity}</td>
            </tr>
        </table>
        
        <p><strong>Admin-Link:</strong> <a href="{admin_url}">Anfrage bearbeiten</a></p>
        
        <p>Diese E-Mail wurde automatisch generiert. Bitte antworten Sie nicht auf diese E-Mail.</p>
        ';
    }
    
    
    private function get_default_service_removed_template() {
        return '
        <h2>Service von Serviceanfrage entfernt</h2>
        
        <p>Ein Service wurde von einer Serviceanfrage entfernt.</p>
        
        <h3>Details:</h3>
        <table style="border-collapse: collapse; width: 100%; margin: 20px 0;">
            <tr>
                <td style="padding: 8px; border: 1px solid #ddd; font-weight: bold; background: #f5f5f5;">Anfrage-ID:</td>
                <td style="padding: 8px; border: 1px solid #ddd;">#{request_id}</td>
            </tr>
            <tr>
                <td style="padding: 8px; border: 1px solid #ddd; font-weight: bold; background: #f5f5f5;">Kunde:</td>
                <td style="padding: 8px; border: 1px solid #ddd;">{customer_name}</td>
            </tr>
            <tr>
                <td style="padding: 8px; border: 1px solid #ddd; font-weight: bold; background: #f5f5f5;">Gerät:</td>
                <td style="padding: 1px solid #ddd;">{serial} / {model}</td>
            </tr>
            <tr>
                <td style="padding: 8px; border: 1px solid #ddd; font-weight: bold; background: #f5f5f5;">Entfernter Service:</td>
                <td style="padding: 8px; border: 1px solid #ddd;">{service_name}</td>
            </tr>
            <tr>
                <td style="padding: 8px; border: 1px solid #ddd; font-weight: bold; background: #f5f5f5;">Menge:</td>
                <td style="padding: 8px; border: 1px solid #ddd;">{quantity}</td>
            </tr>
        </table>
        
        <p><strong>Admin-Link:</strong> <a href="{admin_url}">Anfrage bearbeiten</a></p>
        
        <p>Diese E-Mail wurde automatisch generiert. Bitte antworten Sie nicht auf diese E-Mail.</p>
        ';
    }
    
    
    private function get_default_service_quantity_changed_template() {
        return '
        <h2>Service-Menge geändert</h2>
        
        <p>Die Menge eines Services wurde in einer Serviceanfrage geändert.</p>
        
        <h3>Details:</h3>
        <table style="border-collapse: collapse; width: 100%; margin: 20px 0;">
            <tr>
                <td style="padding: 8px; border: 1px solid #ddd; font-weight: bold; background: #f5f5f5;">Anfrage-ID:</td>
                <td style="padding: 8px; border: 1px solid #ddd;">#{request_id}</td>
            </tr>
            <tr>
                <td style="padding: 8px; border: 1px solid #ddd; font-weight: bold; background: #f5f5f5;">Kunde:</td>
                <td style="padding: 8px; border: 1px solid #ddd;">{customer_name}</td>
            </tr>
            <tr>
                <td style="padding: 8px; border: 1px solid #ddd; font-weight: bold; background: #f5f5f5;">Gerät:</td>
                <td style="padding: 8px; border: 1px solid #ddd;">{serial} / {model}</td>
            </tr>
            <tr>
                <td style="padding: 8px; border: 1px solid #ddd; font-weight: bold; background: #f5f5f5;">Service:</td>
                <td style="padding: 8px; border: 1px solid #ddd;">{service_name}</td>
            </tr>
            <tr>
                <td style="padding: 8px; border: 1px solid #ddd; font-weight: bold; background: #f5f5f5;">Vorherige Menge:</td>
                <td style="padding: 8px; border: 1px solid #ddd;">{old_quantity}</td>
            </tr>
            <tr>
                <td style="padding: 8px; border: 1px solid #ddd; font-weight: bold; background: #f5f5f5;">Neue Menge:</td>
                <td style="td style="padding: 8px; border: 1px solid #ddd;">{new_quantity}</td>
        </tr>
    </table>
    
    <p>Diese Änderung wurde nach sorgfältiger Prüfung Ihrer Anfrage vorgenommen, um Ihnen den bestmöglichen Service zu bieten.</p>
    
    <p><strong>Dashboard-Link:</strong> <a href="{customer_dashboard_url}">Ihr Dashboard aufrufen</a></p>
    
    <p>Bei Fragen stehen wir Ihnen gerne zur Verfügung.</p>
    
    <p>Mit freundlichen Grüßen<br>
    Ihr Nexora Service Suite Team</p>
    
    <p>Diese E-Mail wurde automatisch generiert. Bitte antworten Sie nicht auf diese E-Mail.</p>
    ';
}

private function get_default_customer_service_request_created_template() {
    return '
    <h2>Ihre Serviceanfrage wurde erfolgreich erstellt</h2>
    
    <p>Sehr geehrte(r) {customer_name},</p>
    
    <p>Vielen Dank für Ihre Serviceanfrage. Diese wurde erfolgreich in unserem System registriert.</p>
    
    <h3>Details Ihrer Anfrage:</h3>
    <table style="border-collapse: collapse; width: 100%; margin: 20px 0;">
        <tr>
            <td style="padding: 8px; border: 1px solid #ddd; font-weight: bold; background: #f5f5f5;">Anfrage-ID:</td>
            <td style="padding: 8px; border: 1px solid #ddd;">#{request_id}</td>
        </tr>
        <tr>
            <td style="padding: 8px; border: 1px solid #ddd; font-weight: bold; background: #f5f5f5;">Gerät:</td>
            <td style="padding: 8px; border: 1px solid #ddd;">{serial} / {model}</td>
        </tr>
        <tr>
            <td style="padding: 8px; border: 1px solid #ddd; font-weight: bold; background: #f5f5f5;">Beschreibung:</td>
            <td style="padding: 8px; border: 1px solid #ddd;">{description}</td>
        </tr>
        <tr>
            <td style="padding: 8px; border: 1px solid #ddd; font-weight: bold; background: #f5f5f5;">Marke:</td>
            <td style="padding: 8px; border: 1px solid #ddd;">{brand_level_1} {brand_level_2} {brand_level_3}</td>
        </tr>
        <tr>
            <td style="padding: 8px; border: 1px solid #ddd; font-weight: bold; background: #f5f5f5;">Gewählte Services:</td>
            <td style="padding: 8px; border: 1px solid #ddd;">{services_list}</td>
        </tr>
        <tr>
            <td style="padding: 8px; border: 1px solid #ddd; font-weight: bold; background: #f5f5f5;">Erstellt am:</td>
            <td style="padding: 8px; border: 1px solid #ddd;">{created_date}</td>
        </tr>
    </table>
    
    <p>Wir werden uns in Kürze mit Ihnen in Verbindung setzen, um den nächsten Schritt zu besprechen.</p>
    
    <p><strong>Dashboard-Link:</strong> <a href="{customer_dashboard_url}">Ihr Dashboard aufrufen</a></p>
    
    <p>Bei Fragen stehen wir Ihnen gerne zur Verfügung.</p>
    
    <p>Mit freundlichen Grüßen<br>
    Ihr Nexora Service Suite Team</p>
    
    <p>Diese E-Mail wurde automatisch generiert. Bitte antworten Sie nicht auf diese E-Mail.</p>
    ';
}

private function get_default_customer_status_change_template() {
    return '
    <h2>Status-Änderung für Ihre Serviceanfrage</h2>
    
    <p>Sehr geehrte(r) {customer_name},</p>
    
    <p>Der Status Ihrer Serviceanfrage hat sich geändert.</p>
    
    <h3>Status-Update:</h3>
    <table style="border-collapse: collapse; width: 100%; margin: 20px 0;">
        <tr>
            <td style="padding: 8px; border: 1px solid #ddd; font-weight: bold; background: #f5f5f5;">Anfrage-ID:</td>
            <td style="padding: 8px; border: 1px solid #ddd;">#{request_id}</td>
        </tr>
        <tr>
            <td style="padding: 8px; border: 1px solid #ddd; font-weight: bold; background: #f5f5f5;">Gerät:</td>
            <td style="padding: 8px; border: 1px solid #ddd;">{serial} / {model}</td>
        </tr>
        <tr>
            <td style="padding: 8px; border: 1px solid #ddd; font-weight: bold; background: #f5f5f5;">Vorheriger Status:</td>
            <td style="padding: 8px; border: 1px solid #ddd;">{old_status}</td>
        </tr>
        <tr>
            <td style="padding: 8px; border: 1px solid #ddd; font-weight: bold; background: #f5f5f5;">Neuer Status:</td>
            <td style="padding: 8px; border: 1px solid #ddd;">{new_status}</td>
        </tr>
    </table>
    
    <p>Wir arbeiten kontinuierlich an Ihrer Anfrage und halten Sie über alle wichtigen Entwicklungen auf dem Laufenden.</p>
    
    <p><strong>Dashboard-Link:</strong> <a href="{customer_dashboard_url}">Ihr Dashboard aufrufen</a></p>
    
    <p>Bei Fragen stehen wir Ihnen gerne zur Verfügung.</p>
    
    <p>Mit freundlichen Grüßen<br>
    Ihr Nexora Service Suite Team</p>
    
    <p>Diese E-Mail wurde automatisch generiert. Bitte antworten Sie nicht auf diese E-Mail.</p>
    ';
}

private function get_default_customer_service_added_template() {
    return '
    <h2>Service zu Ihrer Anfrage hinzugefügt</h2>
    
    <p>Sehr geehrte(r) {customer_name},</p>
    
    <p>Ein neuer Service wurde zu Ihrer Serviceanfrage hinzugefügt.</p>
    
    <h3>Service-Details:</h3>
    <table style="border-collapse: collapse; width: 100%; margin: 20px 0;">
        <tr>
            <td style="padding: 8px; border: 1px solid #ddd; font-weight: bold; background: #f5f5f5;">Anfrage-ID:</td>
            <td style="padding: 8px; border: 1px solid #ddd;">#{request_id}</td>
        </tr>
        <tr>
            <td style="padding: 8px; border: 1px solid #ddd; font-weight: bold; background: #f5f5f5;">Gerät:</td>
            <td style="padding: 8px; border: 1px solid #ddd;">{serial} / {model}</td>
        </tr>
        <tr>
            <td style="padding: 8px; border: 1px solid #ddd; font-weight: bold; background: #f5f5f5;">Hinzugefügter Service:</td>
            <td style="padding: 8px; border: 1px solid #ddd;">{service_name}</td>
        </tr>
        <tr>
            <td style="padding: 8px; border: 1px solid #ddd; font-weight: bold; background: #f5f5f5;">Menge:</td>
            <td style="padding: 8px; border: 1px solid #ddd;">{quantity}</td>
        </tr>
    </table>
    
    <p>Dieser Service wurde nach sorgfältiger Prüfung Ihrer Anfrage hinzugefügt, um Ihnen den bestmöglichen Service zu bieten.</p>
    
    <p><strong>Dashboard-Link:</strong> <a href="{customer_dashboard_url}">Ihr Dashboard aufrufen</a></p>
    
    <p>Bei Fragen stehen wir Ihnen gerne zur Verfügung.</p>
    
    <p>Mit freundlichen Grüßen<br>
    Ihr Nexora Service Suite Team</p>
    
    <p>Diese E-Mail wurde automatisch generiert. Bitte antworten Sie nicht auf diese E-Mail.</p>
    ';
}

private function get_default_customer_service_removed_template() {
    return '
    <h2>Service von Ihrer Anfrage entfernt</h2>
    
    <p>Sehr geehrte(r) {customer_name},</p>
    
    <p>Ein Service wurde von Ihrer Serviceanfrage entfernt.</p>
    
    <h3>Service-Details:</h3>
    <table style="border-collapse: collapse; width: 100%; margin: 20px 0;">
        <tr>
            <td style="padding: 8px; border: 1px solid #ddd; font-weight: bold; background: #f5f5f5;">Anfrage-ID:</td>
            <td style="padding: 8px; border: 1px solid #ddd;">#{request_id}</td>
        </tr>
        <tr>
            <td style="padding: 8px; border: 1px solid #ddd; font-weight: bold; background: #f5f5f5;">Gerät:</td>
            <td style="padding: 8px; border: 1px solid #ddd;">{serial} / {model}</td>
        </tr>
        <tr>
            <td style="padding: 8px; border: 1px solid #ddd; font-weight: bold; background: #f5f5f5;">Entfernter Service:</td>
            <td style="padding: 8px; border: 1px solid #ddd;">{service_name}</td>
        </tr>
        <tr>
            <td style="padding: 8px; border: 1px solid #ddd; font-weight: bold; background: #f5f5f5;">Menge:</td>
            <td style="padding: 8px; border: 1px solid #ddd;">{quantity}</td>
        </tr>
    </table>
    
    <p>Dieser Service wurde nach sorgfältiger Prüfung von Ihrer Anfrage entfernt. Falls Sie Fragen dazu haben, kontaktieren Sie uns gerne.</p>
    
    <p><strong>Dashboard-Link:</strong> <a href="{customer_dashboard_url}">Ihr Dashboard aufrufen</a></p>
    
    <p>Bei Fragen stehen wir Ihnen gerne zur Verfügung.</p>
    
    <p>Mit freundlichen Grüßen<br>
    Ihr Nexora Service Suite Team</p>
    
    <p>Diese E-Mail wurde automatisch generiert. Bitte antworten Sie nicht auf diese E-Mail.</p>
    ';
}

private function get_default_customer_service_quantity_changed_template() {
    return '
    <h2>Service-Menge geändert</h2>
    
    <p>Sehr geehrte(r) {customer_name},</p>
    
    <p>Die Menge eines Services in Ihrer Anfrage wurde geändert.</p>
    
    <h3>Änderungs-Details:</h3>
    <table style="border-collapse: collapse; width: 100%; margin: 20px 0;">
        <tr>
            <td style="padding: 8px; border: 1px solid #ddd; font-weight: bold; background: #f5f5f5;">Anfrage-ID:</td>
            <td style="padding: 8px; border: 1px solid #ddd;">#{request_id}</td>
        </tr>
        <tr>
            <td style="padding: 8px; border: 1px solid #ddd; font-weight: bold; background: #f5f5f5;">Gerät:</td>
            <td style="padding: 8px; border: 1px solid #ddd;">{serial} / {model}</td>
        </tr>
        <tr>
            <td style="padding: 8px; border: 1px solid #ddd; font-weight: bold; background: #f5f5f5;">Service:</td>
            <td style="padding: 8px; border: 1px solid #ddd;">{service_name}</td>
        </tr>
        <tr>
            <td style="padding: 8px; border: 1px solid #ddd; font-weight: bold; background: #f5f5f5;">Vorherige Menge:</td>
            <td style="padding: 8px; border: 1px solid #ddd;">{old_quantity}</td>
        </tr>
        <tr>
            <td style="padding: 8px; border: 1px solid #ddd; font-weight: bold; background: #f5f5f5;">Neue Menge:</td>
            <td style="padding: 8px; border: 1px solid #ddd;">{new_quantity}</td>
        </tr>
    </table>
    
    <p>Diese Änderung wurde nach sorgfältiger Prüfung Ihrer Anfrage vorgenommen, um Ihnen den bestmöglichen Service zu bieten.</p>
    
    <p><strong>Dashboard-Link:</strong> <a href="{customer_dashboard_url}">Ihr Dashboard aufrufen</a></p>
    
    <p>Bei Fragen stehen wir Ihnen gerne zur Verfügung.</p>
    
    <p>Mit freundlichen Grüßen<br>
    Ihr Nexora Service Suite Team</p>
    
    <p>Diese E-Mail wurde automatisch generiert. Bitte antworten Sie nicht auf diese E-Mail.</p>
    ';
}

}
new Nexora_Email_Template_Manager();
