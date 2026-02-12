<?php

if (!defined('ABSPATH')) {
    exit;
}

class NEXORA_PDF_Invoice_Generator {
    
    public function __construct() {
        add_action('wp_ajax_generate_pdf_invoice', array($this, 'generate_pdf_invoice'));
        add_action('wp_ajax_nopriv_generate_pdf_invoice', array($this, 'generate_pdf_invoice'));
    }
    
    
    public function generate_pdf_invoice() {
        if (!wp_verify_nonce($_POST['nonce'], 'nexora_pdf_invoice_nonce')) {
            wp_die('Security check failed');
        }
        
        $request_id = intval($_POST['request_id']);
        
        if (!$request_id) {
            wp_die('Invalid request ID');
        }
        $request_data = $this->get_request_data($request_id);
        
        if (!$request_data) {
            wp_die('Request not found');
        }
        $this->create_pdf_invoice($request_data);
    }
    
    
    private function get_request_data($request_id) {
        global $wpdb;
        
        $table_name = $wpdb->prefix . 'nexora_service_requests';
        $services_table = $wpdb->prefix . 'nexora_services';
        
        $request = $wpdb->get_row($wpdb->prepare(
            "SELECT r.*, s.title as service_title, s.cost as service_cost, s.description as service_description,
                     r.manual_customer_name, r.manual_customer_lastname, r.manual_customer_phone
             FROM {$table_name} r 
             LEFT JOIN {$services_table} s ON r.service_id = s.id 
             WHERE r.id = %d",
            $request_id
        ));
        
        if (!$request) {
            return false;
        }
        $customer = get_userdata($request->user_id);
        $admin_user = null;
        if ($request->user_id != get_current_user_id()) {
            $admin_user = get_userdata(get_current_user_id());
        }
        $current_user = wp_get_current_user();
        $services_data = $wpdb->get_var($wpdb->prepare(
            "SELECT services_data FROM {$wpdb->prefix}nexora_complete_service_requests WHERE request_id = %d",
            $request_id
        ));
        
        $faktor_services = [];
        $total_amount = 0;
        $discount_percentage = 0;
        $discount_amount = 0;
        $user_benefit_type = '';
        $user_benefit_percentage = 0;
        $benefit_amount = 0;
        
        if ($request->user_id) {
            $user_benefit_type = get_user_meta($request->user_id, 'benefit_type', true);
            if ($user_benefit_type === 'discount') {
                $user_benefit_percentage = floatval(get_user_meta($request->user_id, 'discount_percentage', true));
            } elseif ($user_benefit_type === 'commission') {
                $user_benefit_percentage = floatval(get_user_meta($request->user_id, 'commission_percentage', true));
            }
        }
        
        if ($services_data) {
            $services_array = json_decode($services_data, true);
            if ($services_array && is_array($services_array)) {
                foreach ($services_array as $service) {
                    $faktor_services[] = (object) [
                        'service_title' => $service['service_title'] ?? 'Service',
                        'service_cost' => floatval($service['service_cost'] ?? 0),
                        'quantity' => intval($service['quantity'] ?? 1),
                        'description' => $service['description'] ?? ''
                    ];
                    $total_amount += floatval($service['service_cost'] ?? 0) * intval($service['quantity'] ?? 1);
                }
            }
        }
        if (empty($faktor_services)) {
            $faktor_services = $wpdb->get_results($wpdb->prepare(
                "SELECT service_title, service_cost, quantity, description 
                 FROM {$wpdb->prefix}nexora_faktor_services 
                 WHERE request_id = %d 
                 ORDER BY id ASC",
                $request_id
            ));
            
            if (!empty($faktor_services)) {
                $total_amount = 0;
                foreach ($faktor_services as $fs) {
                    $total_amount += $fs->quantity * $fs->service_cost;
                }
            } else {
                $total_amount = ($request->service_cost ?: 0) * ($request->service_quantity ?: 1);
            }
        }
        if ($user_benefit_percentage > 0) {
            $benefit_amount = ($total_amount * $user_benefit_percentage) / 100;
            
            if ($user_benefit_type === 'discount') {
                $total_amount = $total_amount - $benefit_amount;
                error_log("Applied discount: {$user_benefit_percentage}% = {$benefit_amount}€, Final cost: {$total_amount}€");
            } elseif ($user_benefit_type === 'commission') {
                error_log("Applied commission: {$user_benefit_percentage}% = {$benefit_amount}€, Total cost: {$total_amount}€");
            }
        }
        
        return array(
            'request' => $request,
            'customer' => $customer,
            'admin_user' => $admin_user,
            'current_user' => $current_user,
            'faktor_services' => $faktor_services,
            'invoice_number' => 'INV-' . str_pad($request_id, 6, '0', STR_PAD_LEFT),
            'created_date' => date('d.m.Y', strtotime($request->created_at)),
            'due_date' => date('d.m.Y', strtotime($request->created_at . ' +30 days')),
            'service_created_date' => date('d.m.Y H:i', strtotime($request->created_at)),
            'invoice_created_date' => date('d.m.Y H:i'),
            'total_amount' => $total_amount,
            'user_benefit_type' => $user_benefit_type,
            'user_benefit_percentage' => $user_benefit_percentage,
            'benefit_amount' => $benefit_amount,
            'discount_percentage' => $user_benefit_type === 'discount' ? $user_benefit_percentage : 0,
            'discount_amount' => $user_benefit_type === 'discount' ? $benefit_amount : 0
        );
    }
    
    
    private function get_status_text($status_id) {
        $statuses = array(
            1 => 'Neu',
            2 => 'In Bearbeitung', 
            3 => 'Abgeschlossen',
            4 => 'Abgelehnt'
        );
        
        return isset($statuses[$status_id]) ? $statuses[$status_id] : 'Unbekannt';
    }
    
    
    private function create_pdf_invoice($data) {
        $html = $this->create_html_template($data);
        header('Content-Type: text/html; charset=UTF-8');
        echo $html;
        exit;
    }
    
    
    private function create_html_template($data) {
        $request = $data['request'];
        $customer = $data['customer'];
        $user_id = $request->user_id;
        $user = get_userdata($user_id);
        global $wpdb;
        $customer_info = $wpdb->get_row($wpdb->prepare(
            "SELECT * FROM {$wpdb->prefix}nexora_customer_info WHERE user_id = %d",
            $user_id
        ));
        $customer_type = get_user_meta($user_id, 'customer_type', true);
        $company_name = get_user_meta($user_id, 'company_name', true);
        $street = get_user_meta($user_id, 'street', true);
        $house_number = get_user_meta($user_id, 'house_number', true);
        $postfach = get_user_meta($user_id, 'postfach', true);
        $postal_code = get_user_meta($user_id, 'postal_code', true);
        $city = get_user_meta($user_id, 'city', true);
        $country = get_user_meta($user_id, 'country', true);
        $vat_id = get_user_meta($user_id, 'vat_id', true);
        $reference_number = get_user_meta($user_id, 'reference_number', true);
        $salutation = get_user_meta($user_id, 'salutation', true);
        $phone = get_user_meta($user_id, 'phone', true);
        $newsletter = get_user_meta($user_id, 'newsletter', true);
        $customer_name = '';
        $first_name = '';
        $last_name = '';
        if (isset($request->manual_customer_name) && trim($request->manual_customer_name) !== '') {
            $first_name = trim($request->manual_customer_name);
        } else {
            $first_name = $user->first_name ?? '';
        }
        if (isset($request->manual_customer_lastname) && trim($request->manual_customer_lastname) !== '') {
            $last_name = trim($request->manual_customer_lastname);
        } else {
            $last_name = $user->last_name ?? '';
        }
        if ($first_name || $last_name) {
            $customer_name = trim($first_name . ' ' . $last_name);
        } else {
            $customer_name = $user->user_login ?? '';
        }
        if (isset($request->manual_customer_phone) && trim($request->manual_customer_phone) !== '') {
            $phone = trim($request->manual_customer_phone);
        }
        
        $html = '<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Rechnung - ' . $data['invoice_number'] . '</title>
    <style>
        @page {
            margin: 0;
            size: A4;
        }
        body {
            margin: 0;
            padding: 0;
            font-family: Arial, sans-serif;
            background: #f5f5f5;
        }
        .invoice-page {
            width: 210mm;
            height: 297mm;
            position: relative;
            background-image: url("' . NEXORA_PLUGIN_URL . 'assets/images/invoice.jpg");
            background-size: cover;
            background-position: center;
            background-repeat: no-repeat;
            margin: 0 auto;
            box-shadow: 0 0 20px rgba(0,0,0,0.3);
        }
        .invoice-content {
            position: absolute;
            top: 29%;
            left: 50%;
            transform: translate(-50%, -50%);
            text-align: left;
            color: #000;
            background: transparent;
            padding: 0;
            border-radius: 0;
            max-width: 90%;
            width: 90%;
            box-shadow: none;
        }
        .invoice-title {
            font-size: 22px;
            font-weight: bold;
            margin-bottom: 15px;
            color: #2c3e50;
            text-align: center;
        }
        .invoice-number {
            font-size: 16px;
            font-weight: bold;
            margin-bottom: 15px;
            color: #34495e;
            text-align: center;
        }
        .invoice-dates {
            display: flex;
            justify-content: space-between;
            margin-bottom: 20px;
            font-size: 12px;
            color: #000;
            font-weight: bold;
        }
        .invoice-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 15px;
        }
        .invoice-table th {
            background: transparent;
            padding: 8px 10px;
            text-align: left;
            font-weight: bold;
            color: #000;
            border-bottom: 2px solid #000;
            font-size: 12px;
            text-transform: uppercase;
        }
        .invoice-table td {
            padding: 8px 10px;
            border-bottom: 1px solid #000;
            font-size: 12px;
            color: #000;
            font-weight: 500;
        }
        .invoice-table tr:hover {
            background: transparent;
        }
        .amount {
            font-size: 18px;
            font-weight: bold;
            margin-top: 15px;
            color: #000;
            text-align: center;
            padding: 10px;
            background: transparent;
            border-radius: 0;
            border: none;
            text-transform: uppercase;
        }
        .print-button {
            position: fixed;
            top: 20px;
            right: 20px;
            padding: 12px 24px;
            background: #3498db;
            color: white;
            border: none;
            border-radius: 8px;
            cursor: pointer;
            font-size: 16px;
            font-weight: bold;
            box-shadow: 0 2px 10px rgba(0,0,0,0.2);
            transition: all 0.3s ease;
        }
        .print-button:hover {
            background: #2980b9;
            transform: translateY(-2px);
        }
        .back-button {
            position: fixed;
            top: 20px;
            left: 20px;
            padding: 12px 24px;
            background: #95a5a6;
            color: white;
            text-decoration: none;
            border-radius: 8px;
            font-size: 16px;
            font-weight: bold;
            box-shadow: 0 2px 10px rgba(0,0,0,0.2);
            transition: all 0.3s ease;
        }
        .back-button:hover {
            background: #7f8c8d;
            transform: translateY(-2px);
        }
        @media print {
            .print-button, .back-button {
                display: none;
            }
            body {
                background: white;
            }
            .invoice-page {
                box-shadow: none;
            }
        }
    </style>
</head>
<body>
    <button class="print-button" onclick="window.print()">🖨️ Drucken</button>
    <a href="javascript:history.back()" class="back-button">← Zurück</a>
    
    <div class="invoice-page">
        <div class="invoice-content">
            <div class="invoice-title">RECHNUNG</div>
            <div class="invoice-number">Nr: ' . esc_html($data['invoice_number']) . '</div>
            <div class="invoice-dates">
                <div>Datum: ' . esc_html($data['created_date']) . '</div>
                <div>Fälligkeitsdatum: ' . esc_html($data['due_date']) . '</div>
            </div>
            
            <table class="invoice-table">
                <thead>
                    <tr>
                        <th style="width: 50%;">KUNDE & SERVICE</th>
                        <th style="width: 50%;">DETAILS & ZEITPLAN</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td>
                            <strong>Kunde:</strong> ' . esc_html($customer_name) . '<br>';
        
        if ($customer->user_email) {
            $html .= '<strong>Email:</strong> ' . esc_html($customer->user_email) . '<br>';
        }
        
        if ($phone) {
            $html .= '<strong>Telefon:</strong> ' . esc_html($phone) . '<br>';
        }
        if ($customer_type) {
            $customer_type_text = $customer_type === 'business' ? 'Geschäftskunden' : 'Privatkunden';
            $html .= '<strong>Kundenart:</strong> ' . esc_html($customer_type_text) . '<br>';
        }
        
        if ($salutation) {
            $html .= '<strong>Anrede:</strong> ' . esc_html($salutation) . '<br>';
        }
        
        if ($company_name) {
            $html .= '<strong>Firmenname:</strong> ' . esc_html($company_name) . '<br>';
        }
        
        if ($reference_number) {
            $html .= '<strong>Referenznummer:</strong> ' . esc_html($reference_number) . '<br>';
        }
        
        if ($vat_id) {
            $html .= '<strong>Steuernummer:</strong> ' . esc_html($vat_id) . '<br>';
        }
        
        if ($street) {
            $html .= '<strong>Straße:</strong> ' . esc_html($street) . '<br>';
        }
        
        if ($house_number) {
            $html .= '<strong>Hausnummer:</strong> ' . esc_html($house_number) . '<br>';
        }
        
        if ($postfach) {
            $html .= '<strong>Postfach:</strong> ' . esc_html($postfach) . '<br>';
        }
        
        if ($postal_code) {
            $html .= '<strong>Postleitzahl:</strong> ' . esc_html($postal_code) . '<br>';
        }
        
        if ($city) {
            $html .= '<strong>Stadt:</strong> ' . esc_html($city) . '<br>';
        }
        
        if ($country) {
            $html .= '<strong>Land:</strong> ' . esc_html($country) . '<br>';
        }
        
        if ($newsletter !== '') {
            $html .= '<strong>Newsletter:</strong> ' . ($newsletter ? 'Ja' : 'Nein') . '<br>';
        }
        $service_title = 'N/A';
        if ($request->service_id) {
            $service = $wpdb->get_row($wpdb->prepare(
                "SELECT title FROM {$wpdb->prefix}nexora_services WHERE id = %d",
                $request->service_id
            ));
            if ($service) {
                $service_title = $service->title;
            }
        }
        
        $html .= '<strong>Service:</strong> ' . esc_html($service_title) . '<br>';
        $html .= '<strong>Gerät:</strong> ' . esc_html($request->model ?: 'N/A') . '<br>';
        $html .= '<strong>Seriennummer:</strong> ' . esc_html($request->serial ?: 'N/A') . '<br>';
        $html .= '<strong>Problem:</strong> ' . esc_html($request->description ?: 'N/A');
        
        if ($request->notes) {
            $html .= '<br><strong>Notizen:</strong> ' . esc_html($request->notes);
        }
        
        $html .= '
                        </td>
                        <td>
                            <strong>Service erstellt:</strong> ' . esc_html($data['service_created_date']) . '<br>
                            <strong>Rechnung erstellt:</strong> ' . esc_html($data['invoice_created_date']) . '<br>';
        
        if ($data['admin_user']) {
            $html .= '<strong>Verantwortlich:</strong> ' . esc_html($data['admin_user']->display_name) . '<br>';
        }
        
        $html .= '<strong>Rechnung gedruckt von:</strong> ' . esc_html($data['current_user']->display_name) . '<br>
                            <strong>Service ID:</strong> ' . esc_html($request->id) . '<br>
                            <strong>Status:</strong> ' . esc_html($this->get_status_text($request->status_id));
        if ($request->brand_level_1_id || $request->brand_level_2_id || $request->brand_level_3_id) {
            $brand_names = array();
            
            if ($request->brand_level_1_id) {
                $brand1 = $wpdb->get_var($wpdb->prepare(
                    "SELECT name FROM {$wpdb->prefix}nexora_brands WHERE id = %d",
                    $request->brand_level_1_id
                ));
                if ($brand1) $brand_names[] = $brand1;
            }
            
            if ($request->brand_level_2_id) {
                $brand2 = $wpdb->get_var($wpdb->prepare(
                    "SELECT name FROM {$wpdb->prefix}nexora_brands WHERE id = %d",
                    $request->brand_level_2_id
                ));
                if ($brand2) $brand_names[] = $brand2;
            }
            
            if ($request->brand_level_3_id) {
                $brand3 = $wpdb->get_var($wpdb->prepare(
                    "SELECT name FROM {$wpdb->prefix}nexora_brands WHERE id = %d",
                    $request->brand_level_3_id
                ));
                if ($brand3) $brand_names[] = $brand3;
            }
            
            if (!empty($brand_names)) {
                $html .= '<br><strong>Marke/Serie:</strong> ' . esc_html(implode(' / ', $brand_names));
            }
        }
        
        $html .= '
                        </td>
                    </tr>
                </tbody>
            </table>
            
            
            <div style="margin: 20px 0; padding: 3px; border-top: 2px solid #000; border-bottom: 2px solid #000;">
                <div style="display: flex; justify-content: space-between; align-items: center; font-size: 11px;">
                    <div style="flex: 1;">
                        <strong style="color: #333; font-size: 13px;">';
        
        if (!empty($data['faktor_services'])) {
            $html .= count($data['faktor_services']) . ' Rechnungsservice(s)';
        } else {
            $html .= esc_html($service_title);
        if (!empty($request->service_quantity) && $request->service_quantity > 1) {
            $html .= ' <span style="color: #666; font-size: 11px;">(' . esc_html($request->service_quantity) . 'x)</span>';
            }
        }
        
        $html .= '</strong>';
        if (empty($data['faktor_services']) && !empty($request->service_description)) {
            $html .= '<br><span style="color: #666; font-size: 9px; margin-top: 3px; display: inline-block;">' . esc_html($request->service_description) . '</span>';
        }
        
        $html .= '
                    </div>
                    <div style="text-align: right; font-weight: bold; color: #333; font-size: 14px;">
                        €' . number_format($data['total_amount'], 2, ',', '.') . '
                    </div>
                </div>
            </div>';
        if (!empty($data['faktor_services'])) {
            $html .= '
            <table class="invoice-table">
                <thead>
                    <tr>
                        <th>Beschreibung</th>
                        <th>Menge</th>
                        <th>Einzelpreis</th>
                        <th>Gesamtpreis</th>
                    </tr>
                </thead>
                <tbody>';
            
            foreach ($data['faktor_services'] as $fs) {
                $total = $fs->quantity * $fs->service_cost;
                $html .= '
                    <tr>
                        <td>
                            ' . esc_html($fs->service_title) . '
                            ' . (!empty($fs->description) ? '<br><span style="color: #666; font-size: 9px;">' . esc_html($fs->description) . '</span>' : '') . '
                        </td>
                        <td>' . $fs->quantity . ' Stück</td>
                        <td>' . number_format($fs->service_cost, 2, ',', '.') . ' EUR</td>
                        <td>' . number_format($total, 2, ',', '.') . ' EUR</td>
                    </tr>';
            }
            
            $html .= '
                </tbody>
            </table>';
            if ($data['user_benefit_percentage'] > 0) {
                $benefitTypeText = $data['user_benefit_type'] === 'discount' ? 'Rabatt' : 'Provision';
                $benefitColor = $data['user_benefit_type'] === 'discount' ? '#28a745' : '#f39c12';
                
                if ($data['user_benefit_type'] === 'discount') {
                    $html .= '
                    <div style="text-align: right; margin-top: 10px; padding: 8px; background: #f8f9fa; border-radius: 4px;">
                        <div style="font-size: 11px; color: #666;">
                            Zwischensumme: ' . number_format($data['total_amount'] + $data['benefit_amount'], 2, ',', '.') . ' EUR
                        </div>
                        <div style="font-size: 12px; color: ' . $benefitColor . '; font-weight: bold;">
                            ' . $benefitTypeText . ' (' . $data['user_benefit_percentage'] . '%): -' . number_format($data['benefit_amount'], 2, ',', '.') . ' EUR
                        </div>
                    </div>';
                } else {
                    $html .= '
                    <div style="text-align: right; margin-top: 10px; padding: 8px; background: #f8f9fa; border-radius: 4px;">
                        <div style="font-size: 11px; color: #666;">
                            Gesamtbetrag: ' . number_format($data['total_amount'], 2, ',', '.') . ' EUR
                        </div>
                        <div style="font-size: 12px; color: ' . $benefitColor . '; font-weight: bold;">
                            ' . $benefitTypeText . ' (' . $data['user_benefit_percentage'] . '%): ' . number_format($data['benefit_amount'], 2, ',', '.') . ' EUR
                        </div>
                    </div>';
                }
            }
        }
        if (empty($data['faktor_services']) && $data['user_benefit_percentage'] > 0) {
            $benefitTypeText = $data['user_benefit_type'] === 'discount' ? 'Rabatt' : 'Provision';
            $benefitColor = $data['user_benefit_type'] === 'discount' ? '#28a745' : '#f39c12';
            
            if ($data['user_benefit_type'] === 'discount') {
                $html .= '
                <div style="text-align: right; margin-top: 10px; padding: 8px; background: #f8f9fa; border-radius: 4px;">
                    <div style="font-size: 11px; color: #666;">
                        Zwischensumme: ' . number_format($data['total_amount'] + $data['benefit_amount'], 2, ',', '.') . ' EUR
                    </div>
                    <div style="font-size: 12px; color: ' . $benefitColor . '; font-weight: bold;">
                        ' . $benefitTypeText . ' (' . $data['user_benefit_percentage'] . '%): -' . number_format($data['benefit_amount'], 2, ',', '.') . ' EUR
                    </div>
                </div>';
            } else {
                $html .= '
                <div style="text-align: right; margin-top: 10px; padding: 8px; background: #f8f9fa; border-radius: 4px;">
                    <div style="font-size: 11px; color: #666;">
                        Gesamtbetrag: ' . number_format($data['total_amount'], 2, ',', '.') . ' EUR
                    </div>
                    <div style="font-size: 12px; color: ' . $benefitColor . '; font-weight: bold;">
                        ' . $benefitTypeText . ' (' . $data['user_benefit_percentage'] . '%): ' . number_format($data['benefit_amount'], 2, ',', '.') . ' EUR
                    </div>
                </div>';
            }
        }
        
        $html .= '<div class="amount">BETRAG: €' . number_format($data['total_amount'], 2, ',', '.') . '</div>';
        if ($data['user_benefit_percentage'] > 0) {
            $benefitTypeText = $data['user_benefit_type'] === 'discount' ? 'Rabatt' : 'Provision';
            $html .= '<div style="text-align: center; margin-top: 10px; padding: 8px; background: #e8f5e8; border-radius: 4px; font-size: 11px; color: #28a745;">
                <strong>ℹ️ Dieser Betrag enthält bereits einen ' . $benefitTypeText . ' von ' . $data['user_benefit_percentage'] . '%</strong>
            </div>';
        }
        
        $html .= '
        </div>
    </div>
</body>
</html>';
        
        return $html;
    }
}
new NEXORA_PDF_Invoice_Generator();