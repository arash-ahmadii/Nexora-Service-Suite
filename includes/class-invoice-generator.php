<?php

if (!defined('ABSPATH')) {
    exit;
}

class Nexora_Invoice_Generator {
    
    public function __construct() {
        $this->init_hooks();
    }
    
    private function init_hooks() {
        add_action('wp_ajax_nexora_generate_service_invoice', array($this, 'ajax_generate_service_invoice'));
        add_action('wp_ajax_nexora_generate_service_request_invoice', array($this, 'ajax_generate_service_request_invoice'));
    }
    
    
    public function ajax_generate_service_invoice() {
        $this->verify_nonce();
        
        global $wpdb;
        
        $service_id = intval($_POST['service_id']);
        $query = "SELECT s.*, 
                         COALESCE(u.user_login, '') as user_login, 
                         COALESCE(u.user_email, '') as user_email, 
                         COALESCE(u.first_name, '') as first_name, 
                         COALESCE(u.last_name, '') as last_name,
                         COALESCE(ci.customer_type, '') as customer_type, 
                         COALESCE(ci.customer_number, '') as customer_number, 
                         COALESCE(ci.company_name, '') as company_name, 
                         COALESCE(ci.company_name_2, '') as company_name_2,
                         COALESCE(ci.street, '') as street, 
                         COALESCE(ci.address_addition, '') as address_addition, 
                         COALESCE(ci.postal_code, '') as postal_code, 
                         COALESCE(ci.city, '') as city, 
                         COALESCE(ci.country, '') as country,
                         COALESCE(ci.industry, '') as industry, 
                         COALESCE(ci.vat_id, '') as vat_id, 
                         COALESCE(ci.salutation, '') as salutation, 
                         COALESCE(ci.phone, '') as phone, 
                         COALESCE(ci.newsletter, 0) as newsletter
                  FROM {$wpdb->prefix}nexora_services s
                  LEFT JOIN {$wpdb->users} u ON s.user_id = u.ID
                  LEFT JOIN {$wpdb->prefix}nexora_customer_info ci ON s.user_id = ci.user_id
                  WHERE s.id = %d";
        $service_exists = $wpdb->get_var($wpdb->prepare(
            "SELECT COUNT(*) FROM {$wpdb->prefix}nexora_services WHERE id = %d",
            $service_id
        ));
        
        if (!$service_exists) {
            wp_send_json_error('Service mit ID ' . $service_id . ' wurde nicht in der Datenbank gefunden. Gesendete Werte: ' . print_r($_POST, true));
        }
        $columns = $wpdb->get_results("SHOW COLUMNS FROM {$wpdb->prefix}nexora_services LIKE 'user_id'");
        $has_user_id = !empty($columns);
        
        if ($has_user_id) {
            $service = $wpdb->get_row($wpdb->prepare($query, $service_id));
        } else {
            $simple_query = "SELECT * FROM {$wpdb->prefix}nexora_services WHERE id = %d";
            $service = $wpdb->get_row($wpdb->prepare($simple_query, $service_id));
            
            if ($service) {
                $service->user_login = '';
                $service->user_email = '';
                $service->first_name = '';
                $service->last_name = '';
                $service->customer_type = '';
                $service->customer_number = '';
                $service->company_name = '';
                $service->company_name_2 = '';
                $service->street = '';
                $service->address_addition = '';
                $service->postal_code = '';
                $service->city = '';
                $service->country = '';
                $service->industry = '';
                $service->vat_id = '';
                $service->salutation = '';
                $service->phone = '';
                $service->newsletter = '';
            }
        }
        
        if (!$service) {
            $service = (object) array(
                'id' => $service_id,
                'title' => '---',
                'description' => '',
                'cost' => 0,
                'status' => '',
                'created_at' => '',
                'user_login' => '',
                'user_email' => '',
                'first_name' => '',
                'last_name' => '',
                'customer_type' => '',
                'customer_number' => '',
                'company_name' => '',
                'company_name_2' => '',
                'street' => '',
                'address_addition' => '',
                'postal_code' => '',
                'city' => '',
                'country' => '',
                'industry' => '',
                'vat_id' => '',
                'salutation' => '',
                'phone' => '',
                'newsletter' => ''
            );
        }
        $this->generate_service_html_invoice($service);
    }
    
    
    public function ajax_generate_service_request_invoice() {
        $this->verify_nonce();
        
        global $wpdb;
        
        $request_id = intval($_POST['request_id']);
        $query = "SELECT sr.*, 
                         u.user_login, u.user_email, u.first_name, u.last_name,
                         ci.customer_type, ci.customer_number, ci.company_name, ci.company_name_2,
                         ci.street, ci.address_addition, ci.postal_code, ci.city, ci.country,
                         ci.industry, ci.vat_id, ci.salutation, ci.phone, ci.newsletter,
                         b1.name as brand_1_name, b2.name as brand_2_name, b3.name as brand_3_name,
                         ss.title as status_title,
                         s.title as service_title,
                         s.cost as service_cost
                  FROM {$wpdb->prefix}nexora_service_requests sr
                  LEFT JOIN {$wpdb->users} u ON sr.user_id = u.ID
                  LEFT JOIN {$wpdb->prefix}nexora_customer_info ci ON sr.user_id = ci.user_id
                  LEFT JOIN {$wpdb->prefix}nexora_brands b1 ON sr.brand_level_1_id = b1.id
                  LEFT JOIN {$wpdb->prefix}nexora_brands b2 ON sr.brand_level_2_id = b2.id
                  LEFT JOIN {$wpdb->prefix}nexora_brands b3 ON sr.brand_level_3_id = b3.id
                  LEFT JOIN {$wpdb->prefix}nexora_service_status ss ON sr.status_id = ss.id
                  LEFT JOIN {$wpdb->prefix}nexora_services s ON sr.service_id = s.id
                  WHERE sr.id = %d";
        
        $request = $wpdb->get_row($wpdb->prepare($query, $request_id));
        
        if (!$request) {
            $request = (object) array(
                'id' => $request_id,
                'serial' => '',
                'model' => '',
                'description' => '',
                'user_login' => '',
                'user_email' => '',
                'first_name' => '',
                'last_name' => '',
                'customer_type' => '',
                'customer_number' => '',
                'company_name' => '',
                'company_name_2' => '',
                'street' => '',
                'address_addition' => '',
                'postal_code' => '',
                'city' => '',
                'country' => '',
                'industry' => '',
                'vat_id' => '',
                'salutation' => '',
                'phone' => '',
                'newsletter' => '',
                'brand_1_name' => '',
                'brand_2_name' => '',
                'brand_3_name' => '',
                'status_title' => '',
                'created_at' => '',
            );
        }
        $this->generate_service_request_html_invoice($request);
    }
    
    
    private function generate_service_html_invoice($service) {
        $invoice_data = array(
            'type' => 'service',
            'invoice_number' => 'SRV-' . str_pad($service->id, 6, '0', STR_PAD_LEFT),
            'invoice_date' => date('Y/m/d', strtotime($service->created_at)),
            'customer_name' => ($service->first_name || $service->last_name) ? 
                ($service->first_name . ' ' . $service->last_name) : $service->user_login,
            'customer_email' => $service->user_email,
            'customer_phone' => $service->phone,
            'customer_company' => $service->company_name,
            'customer_type' => $service->customer_type,
            'customer_salutation' => $service->salutation,
            'customer_vat_id' => $service->vat_id,
            'customer_street' => $service->street,
            'customer_address_addition' => $service->address_addition,
            'customer_postal_code' => $service->postal_code,
            'customer_city' => $service->city,
            'customer_country' => $service->country,
            'service_title' => $service->title,
            'service_description' => $service->description,
            'service_cost' => number_format($service->cost),
            'service_status' => $service->status,
            'service_status_id' => $service->status_id
        );
        if (class_exists('Nexora_Admin_Notifications')) {
            $amount = isset($service->cost) ? floatval($service->cost) : 0.00;
            Nexora_Admin_Notifications::notify_new_invoice($service->id, $service->id, $amount);
        }
        $invoice_url = NEXORA_PLUGIN_URL . 'templates/invoice-template.php?request_id=' . $service->id;
        wp_redirect($invoice_url);
        exit;
    }
    
    
    private function generate_service_request_html_invoice($request) {
        $invoice_data = array(
            'type' => 'request',
            'invoice_number' => 'REQ-' . str_pad($request->id, 6, '0', STR_PAD_LEFT),
            'invoice_date' => date('Y/m/d', strtotime($request->created_at)),
            'customer_name' => ($request->first_name || $request->last_name) ? 
                ($request->first_name . ' ' . $request->last_name) : $request->user_login,
            'customer_email' => $request->user_email,
            'customer_phone' => $request->phone,
            'customer_company' => $request->company_name,
            'customer_type' => $request->customer_type,
            'customer_salutation' => $request->salutation,
            'customer_vat_id' => $request->vat_id,
            'customer_street' => $request->street,
            'customer_address_addition' => $request->address_addition,
            'customer_postal_code' => $request->postal_code,
            'customer_city' => $request->city,
            'customer_country' => $request->country,
            'serial' => $request->serial,
            'model' => $request->model,
            'description' => $request->description,
            'brand_1' => $request->brand_1_name,
            'brand_2' => $request->brand_2_name,
            'brand_3' => $request->brand_3_name,
            'status' => $request->status_title,
            'status_id' => $request->status_id,
            'service_cost' => $request->service_cost,
        );
        if (class_exists('Nexora_Admin_Notifications')) {
            Nexora_Admin_Notifications::notify_new_invoice($request->id, $request->id, 0.00);
        }
        $invoice_url = NEXORA_PLUGIN_URL . 'templates/invoice-template.php?request_id=' . $request->id;
        wp_redirect($invoice_url);
        exit;
    }
    
    
    private function generate_service_pdf($service) {
        $tcpdf_path = NEXORA_PLUGIN_DIR . 'lib/tcpdf/tcpdf.php';
        if (!file_exists($tcpdf_path)) {
            wp_send_json_error('TCPDF-Bibliothek ist nicht installiert. Bitte lesen Sie die Datei README-PDF-INSTALLATION.md.');
            return;
        }
        require_once($tcpdf_path);
        $pdf = new TCPDF(PDF_PAGE_ORIENTATION, PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);
        $pdf->SetCreator('Nexora Service Suite Service Manager');
        $pdf->SetAuthor('Nexora Service Suite');
        $pdf->SetTitle('Service-Rechnung - ' . $service->title);
        $pdf->SetSubject('Service-Rechnung');
        $pdf->SetHeaderData('', 0, 'Nexora Service Suite Service Manager', 'Service-Rechnung', array(0,0,0), array(0,0,0));
        $pdf->setFooterData(array(0,0,0), array(0,0,0));
        $pdf->setHeaderFont(Array('dejavusans', '', 10));
        $pdf->setFooterFont(Array('dejavusans', '', 8));
        $pdf->SetDefaultMonospacedFont('courier');
        $pdf->SetMargins(15, 27, 15);
        $pdf->SetHeaderMargin(5);
        $pdf->SetFooterMargin(10);
        $pdf->SetAutoPageBreak(TRUE, 25);
        $pdf->setImageScale(PDF_IMAGE_SCALE_RATIO);
        $pdf->SetFont('dejavusans', '', 10);
        $pdf->AddPage();
        $pdf->setRTL(true);
        $this->generate_invoice_content($pdf, $service, 'service');
        $filename = 'invoice_service_' . $service->id . '_' . date('Y-m-d_H-i-s') . '.pdf';
        $pdf->Output($filename, 'D');
        exit;
    }
    
    
    private function generate_service_request_pdf($request) {
        $tcpdf_path = NEXORA_PLUGIN_DIR . 'lib/tcpdf/tcpdf.php';
        if (!file_exists($tcpdf_path)) {
            wp_send_json_error('TCPDF-Bibliothek ist nicht installiert. Bitte lesen Sie die Datei README-PDF-INSTALLATION.md.');
            return;
        }
        require_once($tcpdf_path);
        $pdf = new TCPDF(PDF_PAGE_ORIENTATION, PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);
        $pdf->SetCreator('Nexora Service Suite Service Manager');
        $pdf->SetAuthor('Nexora Service Suite');
        $pdf->SetTitle('Service-Anfrage-Rechnung - ' . $request->serial);
        $pdf->SetSubject('Service-Anfrage-Rechnung');
        $pdf->SetHeaderData('', 0, 'Nexora Service Suite Service Manager', 'Service-Anfrage-Rechnung', array(0,0,0), array(0,0,0));
        $pdf->setFooterData(array(0,0,0), array(0,0,0));
        $pdf->setHeaderFont(Array('dejavusans', '', 10));
        $pdf->setFooterFont(Array('dejavusans', '', 8));
        $pdf->SetDefaultMonospacedFont('courier');
        $pdf->SetMargins(15, 27, 15);
        $pdf->SetHeaderMargin(5);
        $pdf->SetFooterMargin(10);
        $pdf->SetAutoPageBreak(TRUE, 25);
        $pdf->setImageScale(PDF_IMAGE_SCALE_RATIO);
        $pdf->SetFont('dejavusans', '', 10);
        $pdf->AddPage();
        $pdf->setRTL(true);
        $this->generate_invoice_content($pdf, $request, 'request');
        $filename = 'invoice_request_' . $request->id . '_' . date('Y-m-d_H-i-s') . '.pdf';
        $pdf->Output($filename, 'D');
        exit;
    }
    
    
    private function generate_invoice_content($pdf, $data, $type) {
        $pdf->SetFont('dejavusans', 'B', 16);
        $pdf->Cell(0, 10, 'Rechnung ' . ($type === 'service' ? 'Service' : 'Service-Anfrage'), 0, 1, 'C');
        $pdf->Ln(5);
        $pdf->SetFont('dejavusans', 'B', 12);
        $pdf->Cell(0, 8, 'Rechnungsinformationen:', 0, 1, 'R');
        $pdf->SetFont('dejavusans', '', 10);
        
        $invoice_number = ($type === 'service' ? 'SRV' : 'REQ') . '-' . str_pad($data->id, 6, '0', STR_PAD_LEFT);
        $invoice_date = date('d.m.Y', strtotime($data->created_at));
        
        $pdf->Cell(40, 6, 'Rechnungsnummer:', 0, 0, 'R');
        $pdf->Cell(60, 6, $invoice_number, 0, 1, 'R');
        
        $pdf->Cell(40, 6, 'Rechnungsdatum:', 0, 0, 'R');
        $pdf->Cell(60, 6, $invoice_date, 0, 1, 'R');
        
        $pdf->Ln(5);
        $pdf->SetFont('dejavusans', 'B', 12);
        $pdf->Cell(0, 8, 'Kundeninformationen:', 0, 1, 'R');
        $pdf->SetFont('dejavusans', '', 10);
        
        $customer_name = ($data->first_name || $data->last_name) ? 
            ($data->first_name . ' ' . $data->last_name) : $data->user_login;
        if ($data->customer_type) {
            $pdf->Cell(40, 6, 'Kundentyp:', 0, 0, 'R');
            $customer_type_text = $data->customer_type === 'business' ? 'Geschäftlich' : 'Privat';
            $pdf->Cell(60, 6, $customer_type_text, 0, 1, 'R');
        }
        if ($data->salutation) {
            $pdf->Cell(40, 6, 'Anrede:', 0, 0, 'R');
            $pdf->Cell(60, 6, $data->salutation, 0, 1, 'R');
        }
        
        $pdf->Cell(40, 6, 'Kundenname:', 0, 0, 'R');
        $pdf->Cell(60, 6, $customer_name, 0, 1, 'R');
        
        if ($data->user_email) {
            $pdf->Cell(40, 6, 'E-Mail:', 0, 0, 'R');
            $pdf->Cell(60, 6, $data->user_email, 0, 1, 'R');
        }
        
        if ($data->phone) {
            $pdf->Cell(40, 6, 'Telefon:', 0, 0, 'R');
            $pdf->Cell(60, 6, $data->phone, 0, 1, 'R');
        }
        
        if ($data->company_name) {
            $pdf->Cell(40, 6, 'Firmenname:', 0, 0, 'R');
            $pdf->Cell(60, 6, $data->company_name, 0, 1, 'R');
        }
        
        if ($data->customer_number) {
            $pdf->Cell(40, 6, 'Kundennummer:', 0, 0, 'R');
            $pdf->Cell(60, 6, $data->customer_number, 0, 1, 'R');
        }
        
        if ($data->vat_id) {
            $pdf->Cell(40, 6, 'USt-IdNr.:', 0, 0, 'R');
            $pdf->Cell(60, 6, $data->vat_id, 0, 1, 'R');
        }
        if ($data->street || $data->city) {
            $pdf->Cell(40, 6, 'Adresse:', 0, 0, 'R');
            $address = $data->street;
            if ($data->address_addition) $address .= ' - ' . $data->address_addition;
            if ($data->postal_code) $address .= ' - ' . $data->postal_code;
            if ($data->city) $address .= ' - ' . $data->city;
            if ($data->country) $address .= ' - ' . $data->country;
            $pdf->Cell(60, 6, $address, 0, 1, 'R');
        }
        
        $pdf->Ln(5);
        $pdf->SetFont('dejavusans', 'B', 12);
        $pdf->Cell(0, 8, 'Informationen ' . ($type === 'service' ? 'Service' : 'Anfrage') . ':', 0, 1, 'R');
        $pdf->SetFont('dejavusans', '', 10);
        
        if ($type === 'service') {
            $pdf->Cell(40, 6, 'Service-Titel:', 0, 0, 'R');
            $pdf->Cell(60, 6, $data->title, 0, 1, 'R');
            
            if ($data->description) {
                $pdf->Cell(40, 6, 'Beschreibung:', 0, 0, 'R');
                $pdf->MultiCell(60, 6, $data->description, 0, 'R');
            }
            
            $pdf->Cell(40, 6, 'Kosten:', 0, 0, 'R');
            $pdf->Cell(60, 6, number_format($data->cost, 2) . ' €', 0, 1, 'R');
            
            $pdf->Cell(40, 6, 'Status:', 0, 0, 'R');
            $status_text = $data->status === 'active' ? 'Aktiv' : 'Inaktiv';
            $pdf->Cell(60, 6, $status_text, 0, 1, 'R');
        } else {
            $pdf->Cell(40, 6, 'Seriennummer:', 0, 0, 'R');
            $pdf->Cell(60, 6, $data->serial, 0, 1, 'R');
            
            $pdf->Cell(40, 6, 'Modell:', 0, 0, 'R');
            $pdf->Cell(60, 6, $data->model, 0, 1, 'R');
            
            if ($data->description) {
                $pdf->Cell(40, 6, 'Beschreibung:', 0, 0, 'R');
                $pdf->MultiCell(60, 6, $data->description, 0, 'R');
            }
            
            $pdf->Cell(40, 6, 'Status:', 0, 0, 'R');
            $pdf->Cell(60, 6, $data->status_title, 0, 1, 'R');
            if ($data->brand_1_name || $data->brand_2_name || $data->brand_3_name) {
                $pdf->Ln(3);
                $pdf->Cell(40, 6, 'Marke:', 0, 0, 'R');
                $brands = array_filter([$data->brand_1_name, $data->brand_2_name, $data->brand_3_name]);
                $pdf->Cell(60, 6, implode(' / ', $brands), 0, 1, 'R');
            }
        }
        
        $pdf->Ln(10);
        $pdf->SetFont('dejavusans', 'B', 10);
        $pdf->Cell(0, 6, 'Geschäftsbedingungen:', 0, 1, 'R');
        $pdf->SetFont('dejavusans', '', 8);
        $pdf->MultiCell(0, 5, '1. Diese Rechnung ist gültig und muss innerhalb der angegebenen Frist bezahlt werden.
2. Bei verspäteter Zahlung werden Verzugszinsen berechnet.
3. Alle Preise sind in Euro und inkl. Mehrwertsteuer angegeben.
4. Bei Fragen wenden Sie sich bitte an uns.', 0, 'R');
        
        $pdf->Ln(10);
        $pdf->SetFont('dejavusans', '', 8);
        $pdf->Cell(0, 6, 'Nexora Service Suite Service Manager - ' . date('d.m.Y H:i'), 0, 1, 'C');
    }
    
    private function verify_nonce() {
        if (!isset($_POST['nonce']) || !wp_verify_nonce($_POST['nonce'], 'nexora_nonce')) {
            wp_send_json_error('Nonce verification failed', 403);
        }
        
        if (!current_user_can('manage_options')) {
            wp_send_json_error('Unbefugter Zugriff', 403);
        }
    }
} 