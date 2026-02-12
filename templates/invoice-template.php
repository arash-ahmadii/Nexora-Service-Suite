<?php

if (!defined('ABSPATH')) {
    exit;
}
$request_id = isset($_GET['request_id']) ? intval($_GET['request_id']) : 0;
$service_id = isset($_GET['service_id']) ? intval($_GET['service_id']) : 0;
error_log('Invoice Template Debug - GET parameters: ' . print_r($_GET, true));
error_log('Invoice Template Debug - Request ID: ' . $request_id);
error_log('Invoice Template Debug - Service ID: ' . $service_id);

if (!$request_id && !$service_id) {
    wp_die('Invalid request - Request ID: ' . $request_id . ', Service ID: ' . $service_id);
}

global $wpdb;
$invoice_data = null;
if ($request_id) {
    $query = "SELECT sr.*, 
                     u.user_login, u.user_email, u.first_name, u.last_name,
                     ci.customer_type, ci.customer_number, ci.company_name, ci.company_name_2,
                     ci.street, ci.address_addition, ci.postal_code, ci.city, ci.country,
                     ci.industry, ci.vat_id, ci.salutation, ci.phone, ci.newsletter,
                     b1.name as brand_1_name, b2.name as brand_2_name, b3.name as brand_3_name,
                     ss.title as status_title,
                     s.title as service_title,
                     s.cost as service_cost,
                     ci.phone as customer_phone,
                     ci.street as customer_street,
                     ci.postal_code as customer_postal_code,
                     ci.city as customer_city,
                     ci.country as customer_country,
                     ci.phone as phone_number,
                     ci.street as street_address,
                     ci.postal_code as postal_code,
                     ci.city as city_name,
                     ci.country as country_name,
                     sr.manual_customer_name,
                     sr.manual_customer_lastname,
                     sr.manual_customer_phone
              FROM {$wpdb->prefix}nexora_service_requests sr
              LEFT JOIN {$wpdb->users} u ON sr.user_id = u.ID
              LEFT JOIN {$wpdb->prefix}nexora_customer_info ci ON sr.user_id = ci.user_id
              LEFT JOIN {$wpdb->prefix}nexora_brands b1 ON sr.brand_level_1_id = b1.id
              LEFT JOIN {$wpdb->prefix}nexora_brands b2 ON sr.brand_level_2_id = b2.id
              LEFT JOIN {$wpdb->prefix}nexora_brands b3 ON sr.brand_level_3_id = b3.id
              LEFT JOIN {$wpdb->prefix}nexora_service_status ss ON sr.status_id = ss.id
              LEFT JOIN {$wpdb->prefix}nexora_services s ON sr.service_id = s.id
              WHERE sr.id = %d";
    
    $invoice_data = $wpdb->get_row($wpdb->prepare($query, $request_id));
    $invoice_type = 'request';
    $invoice_number = 'REQ-' . str_pad($request_id, 6, '0', STR_PAD_LEFT);
} else {
    $query = "SELECT s.*, 
                     u.user_login, u.user_email, u.first_name, u.last_name,
                     ci.customer_type, ci.customer_number, ci.company_name, ci.company_name_2,
                     ci.street, ci.address_addition, ci.postal_code, ci.city, ci.country,
                     ci.industry, ci.vat_id, ci.salutation, ci.phone, ci.newsletter,
                     ci.phone as customer_phone,
                     ci.street as customer_street,
                     ci.postal_code as customer_postal_code,
                     ci.city as customer_city,
                     ci.country as customer_country,
                     ci.phone as phone_number,
                     ci.street as street_address,
                     ci.postal_code as postal_code,
                     ci.city as city_name,
                     ci.country as country_name
              FROM {$wpdb->prefix}nexora_services s
              LEFT JOIN {$wpdb->users} u ON s.user_id = u.ID
              LEFT JOIN {$wpdb->prefix}nexora_customer_info ci ON s.user_id = ci.user_id
              WHERE s.id = %d";
    
    $invoice_data = $wpdb->get_row($wpdb->prepare($query, $service_id));
    $invoice_type = 'service';
    $invoice_number = 'SRV-' . str_pad($service_id, 6, '0', STR_PAD_LEFT);
}

if (!$invoice_data) {
    wp_die('Invoice data not found');
}
$customer_name = '';
$first_name = '';
$last_name = '';
error_log('Invoice Template Debug - Manual customer name: ' . ($invoice_data->manual_customer_name ?? 'NULL'));
error_log('Invoice Template Debug - Manual customer lastname: ' . ($invoice_data->manual_customer_lastname ?? 'NULL'));
error_log('Invoice Template Debug - Manual customer phone: ' . ($invoice_data->manual_customer_phone ?? 'NULL'));
if (isset($invoice_data->manual_customer_name) && trim($invoice_data->manual_customer_name) !== '') {
    $first_name = trim($invoice_data->manual_customer_name);
    error_log('Invoice Template Debug - Using manual first name: ' . $first_name);
} else {
    $first_name = $invoice_data->first_name ?? '';
    error_log('Invoice Template Debug - Using original first name: ' . $first_name);
}
if (isset($invoice_data->manual_customer_lastname) && trim($invoice_data->manual_customer_lastname) !== '') {
    $last_name = trim($invoice_data->manual_customer_lastname);
    error_log('Invoice Template Debug - Using manual last name: ' . $last_name);
} else {
    $last_name = $invoice_data->last_name ?? '';
    error_log('Invoice Template Debug - Using original last name: ' . $last_name);
}
if ($first_name || $last_name) {
    $customer_name = trim($first_name . ' ' . $last_name);
} else {
    $customer_name = $invoice_data->user_login ?? '';
}

error_log('Invoice Template Debug - Final customer name: ' . $customer_name);
$customer_phone = '';
if (isset($invoice_data->manual_customer_phone) && trim($invoice_data->manual_customer_phone) !== '') {
    $customer_phone = trim($invoice_data->manual_customer_phone);
} else {
    $customer_phone = $invoice_data->customer_phone ?? $invoice_data->phone_number ?? '';
}
$net_amount = $invoice_data->service_cost ?: 0;
$vat_rate = 20;
$vat_amount = $net_amount * ($vat_rate / 100);
$gross_amount = $net_amount + $vat_amount;
$discount_percentage = 0;
$discount_amount = 0;
if ($invoice_data->user_id) {
    $discount_percentage = floatval(get_user_meta($invoice_data->user_id, 'discount_percentage', true));
}
$services_data = null;
$services = [];
$total_net_amount = 0;
$services_source = 'none';

if ($invoice_type === 'request') {
    $faktor_services = $wpdb->get_results($wpdb->prepare(
        "SELECT service_title, service_cost, quantity, description 
         FROM {$wpdb->prefix}nexora_faktor_services 
         WHERE request_id = %d 
         ORDER BY id ASC",
        $request_id
    ));
    
    if (!empty($faktor_services)) {
        $services = [];
        $total_net_amount = 0;
        foreach ($faktor_services as $fs) {
            $services[] = [
                'service_title' => $fs->service_title,
                'service_cost' => $fs->service_cost,
                'quantity' => $fs->quantity,
                'description' => $fs->description
            ];
            $total_net_amount += $fs->quantity * $fs->service_cost;
        }
        $services_source = 'faktor';
    } else {
        $services_data = $wpdb->get_var($wpdb->prepare(
            "SELECT services_data FROM {$wpdb->prefix}nexora_complete_service_requests WHERE request_id = %d",
            $request_id
        ));
        if ($services_data) {
            $services_raw = json_decode($services_data, true);
            $services = [];
            foreach ($services_raw as $srv) {
                $qty = isset($srv['quantity']) ? (int)$srv['quantity'] : 1;
                $price = isset($srv['service_cost']) ? floatval($srv['service_cost']) : 0;
                $service_title = isset($srv['service_title']) ? $srv['service_title'] : 'Service';
                
                $services[] = [
                    'service_title' => $service_title,
                    'service_cost' => $price,
                    'quantity' => $qty,
                    'description' => $srv['description'] ?? ''
                ];
                
                $total_net_amount += $qty * $price;
            }
            $services_source = 'complete';
        }
    }
    if (!empty($services)) {
        $net_amount = $total_net_amount;
        if ($discount_percentage > 0) {
            $discount_amount = ($net_amount * $discount_percentage) / 100;
            $net_amount = $net_amount - $discount_amount;
        }
        
        $vat_amount = $net_amount * ($vat_rate / 100);
        $gross_amount = $net_amount + $vat_amount;
    } else {
        if ($discount_percentage > 0) {
            $discount_amount = ($net_amount * $discount_percentage) / 100;
            $net_amount = $net_amount - $discount_amount;
            $vat_amount = $net_amount * ($vat_rate / 100);
            $gross_amount = $net_amount + $vat_amount;
        }
    }
}

?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Rechnung - <?php echo esc_html($invoice_number); ?></title>
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
            background-image: url("<?php echo NEXORA_PLUGIN_URL; ?>assets/images/invoice.jpg");
            background-size: cover;
            background-position: center;
            background-repeat: no-repeat;
            margin: 0 auto;
            box-shadow: 0 0 20px rgba(0,0,0,0.3);
        }
        .invoice-content {
            position: absolute;
            top: 44%;
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
        .invoice-header {
            text-align: center;
            margin-bottom: 20px;
            border-bottom: 2px solid #000;
            padding-bottom: 15px;
        }
        .invoice-title {
            font-size: 22px;
            font-weight: bold;
            margin-bottom: 10px;
            color: #2c3e50;
        }
        .invoice-number {
            font-size: 16px;
            font-weight: bold;
            margin-bottom: 10px;
            color: #34495e;
        }
        .customer-info-section {
            margin-bottom: 20px;
            border-bottom: 1px solid #000;
            padding-bottom: 15px;
        }
        .customer-info-title {
            font-size: 14px;
            font-weight: bold;
            margin-bottom: 15px;
            color: #2c3e50;
            text-transform: uppercase;
        }
        .customer-info-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 15px;
        }
        .customer-info-item {
            margin-bottom: 8px;
        }
        .customer-info-label {
            font-weight: bold;
            font-size: 11px;
            color: #666;
        }
        .customer-info-value {
            font-size: 12px;
            color: #000;
        }
        .customer-type-badge {
            padding: 3px 8px;
            border-radius: 3px;
            font-size: 12px;
            font-weight: 600;
            background: #e3f2fd;
            color: #1976d2;
        }
        .invoice-dates {
            display: flex;
            justify-content: space-between;
            margin-bottom: 20px;
            font-size: 12px;
            color: #000;
            font-weight: bold;
            border-bottom: 1px solid #000;
            padding-bottom: 15px;
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
        .summary-section {
            margin-top: 20px;
            border-top: 1px solid #000;
            padding-top: 15px;
        }
        .amount-section {
            margin-top: 20px;
            text-align: center;
            border-top: 2px solid #000;
            padding-top: 15px;
        }
        .amount {
            font-size: 18px;
            font-weight: bold;
            color: #000;
            text-transform: uppercase;
            border: none;
            background: transparent;
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
            <div class="invoice-header">
                <div class="invoice-title">RECHNUNG</div>
                <div class="invoice-number"><?php echo esc_html($invoice_number); ?></div>
            </div>
            
            <div class="customer-info-section">
                <div class="customer-info-title">Kundeninformationen</div>
                <div class="customer-info-grid">
                    <div><strong>Kundenart:</strong> 
                        <span class="customer-type-badge">
                            <?php echo esc_html($invoice_data->customer_type === 'business' ? 'Geschäftskunden' : ($invoice_data->customer_type === 'private' ? 'Privatkunden' : 'N/A')); ?>
                        </span>
                    </div>
                    <div><strong>Anrede:</strong> <?php echo esc_html($invoice_data->salutation ?: 'N/A'); ?></div>
                    <div><strong>Vollständiger Name:</strong> <?php echo esc_html($customer_name ?: 'N/A'); ?></div>
                    <?php if ((isset($invoice_data->manual_customer_name) && trim($invoice_data->manual_customer_name) !== '') || 
                              (isset($invoice_data->manual_customer_lastname) && trim($invoice_data->manual_customer_lastname) !== '') || 
                              (isset($invoice_data->manual_customer_phone) && trim($invoice_data->manual_customer_phone) !== '')): ?>
                        <div style="color: #007cba; font-style: italic; font-size: 12px;">📝 Manuelle Rechnungsdaten verwendet</div>
                    <?php endif; ?>
                    <div><strong>Firmenname:</strong> <?php echo esc_html($invoice_data->company_name ?: 'N/A'); ?></div>
                    <div><strong>Telefon:</strong> <?php echo esc_html($customer_phone ?: 'N/A'); ?></div>
                    <div><strong>Referenznummer:</strong> <?php echo esc_html($invoice_data->customer_number ?: 'N/A'); ?></div>
                    <div><strong>Steuernummer:</strong> <?php echo esc_html($invoice_data->vat_id ?: 'N/A'); ?></div>
                    <div><strong>Straße:</strong> <?php echo esc_html($invoice_data->street_address ?: $invoice_data->customer_street ?: $invoice_data->street ?: 'N/A'); ?></div>
                    <div><strong>PLZ:</strong> <?php echo esc_html($invoice_data->postal_code ?: $invoice_data->customer_postal_code ?: $invoice_data->postal_code ?: 'N/A'); ?></div>
                    <div><strong>Stadt:</strong> <?php echo esc_html($invoice_data->city_name ?: $invoice_data->customer_city ?: $invoice_data->city ?: 'N/A'); ?></div>
                    <div><strong>Land:</strong> <?php echo esc_html($invoice_data->country_name ?: $invoice_data->customer_country ?: $invoice_data->country ?: 'N/A'); ?></div>
                    <div><strong>E-Mail:</strong> <?php echo esc_html($invoice_data->user_email ?: 'N/A'); ?></div>
                    <div><strong>Branche:</strong> <?php echo esc_html($invoice_data->industry ?: 'N/A'); ?></div>
                    <div><strong>Adresszusatz:</strong> <?php echo esc_html($invoice_data->address_addition ?: 'N/A'); ?></div>
                    <div><strong>Newsletter:</strong> <?php echo esc_html($invoice_data->newsletter ? 'Ja' : 'Nein'); ?></div>
                </div>
                
                
                <div style="margin-top: 20px; padding: 15px; background: #f8f9fa; border: 2px solid #007cba; border-radius: 5px;">
                    <h4 style="margin: 0 0 10px 0; color: #007cba;">🔍 Debug: Customer Information</h4>
                    <div style="font-family: monospace; font-size: 12px; color: #333;">
                        <p><strong>User ID:</strong> <?php echo esc_html($invoice_data->user_id ?? 'NULL'); ?></p>
                        <p><strong>Request ID:</strong> <?php echo esc_html($request_id); ?></p>
                        <p><strong>Service ID:</strong> <?php echo esc_html($service_id); ?></p>
                        <p style="color: #007cba; font-weight: bold;">🔍 Manual Customer Data Debug:</p>
                        <p><strong>Manual Name:</strong> <?php echo esc_html($invoice_data->manual_customer_name ?? 'NULL'); ?></p>
                        <p><strong>Manual Lastname:</strong> <?php echo esc_html($invoice_data->manual_customer_lastname ?? 'NULL'); ?></p>
                        <p><strong>Manual Phone:</strong> <?php echo esc_html($invoice_data->manual_customer_phone ?? 'NULL'); ?></p>
                        <p><strong>Final Customer Name:</strong> <?php echo esc_html($customer_name ?? 'NULL'); ?></p>
                        <p><strong>Final Customer Phone:</strong> <?php echo esc_html($customer_phone ?? 'NULL'); ?></p>
                        <p><strong>Customer Type:</strong> <?php echo esc_html($invoice_data->customer_type ?? 'NULL'); ?></p>
                        <p><strong>Phone (direct):</strong> <?php echo esc_html($invoice_data->phone ?? 'NULL'); ?></p>
                        <p><strong>Phone (customer_phone):</strong> <?php echo esc_html($invoice_data->customer_phone ?? 'NULL'); ?></p>
                        <p><strong>Phone (phone_number):</strong> <?php echo esc_html($invoice_data->phone_number ?? 'NULL'); ?></p>
                        <p><strong>Street (direct):</strong> <?php echo esc_html($invoice_data->street ?? 'NULL'); ?></p>
                        <p><strong>Street (customer_street):</strong> <?php echo esc_html($invoice_data->customer_street ?? 'NULL'); ?></p>
                        <p><strong>Street (street_address):</strong> <?php echo esc_html($invoice_data->street_address ?? 'NULL'); ?></p>
                        <p><strong>Postal Code (direct):</strong> <?php echo esc_html($invoice_data->postal_code ?? 'NULL'); ?></p>
                        <p><strong>Postal Code (customer_postal_code):</strong> <?php echo esc_html($invoice_data->customer_postal_code ?? 'NULL'); ?></p>
                        <p><strong>City (direct):</strong> <?php echo esc_html($invoice_data->city ?? 'NULL'); ?></p>
                        <p><strong>City (customer_city):</strong> <?php echo esc_html($invoice_data->customer_city ?? 'NULL'); ?></p>
                        <p><strong>City (city_name):</strong> <?php echo esc_html($invoice_data->city_name ?? 'NULL'); ?></p>
                        <p><strong>Country (direct):</strong> <?php echo esc_html($invoice_data->country ?? 'NULL'); ?></p>
                        <p><strong>Country (customer_country):</strong> <?php echo esc_html($invoice_data->customer_country ?? 'NULL'); ?></p>
                        <p><strong>Country (country_name):</strong> <?php echo esc_html($invoice_data->country_name ?? 'NULL'); ?></p>
                        
                        <?php
                        $customer_check = $wpdb->get_row($wpdb->prepare(
                            "SELECT * FROM {$wpdb->prefix}nexora_customer_info WHERE user_id = %d",
                            $invoice_data->user_id
                        ));
                        ?>
                        <p><strong>Customer Info in DB:</strong> <?php echo $customer_check ? 'EXISTS' : 'NOT FOUND'; ?></p>
                        <?php if ($customer_check): ?>
                        <p><strong>DB Phone:</strong> <?php echo esc_html($customer_check->phone ?? 'NULL'); ?></p>
                        <p><strong>DB Street:</strong> <?php echo esc_html($customer_check->street ?? 'NULL'); ?></p>
                        <p><strong>DB Postal Code:</strong> <?php echo esc_html($customer_check->postal_code ?? 'NULL'); ?></p>
                        <p><strong>DB City:</strong> <?php echo esc_html($customer_check->city ?? 'NULL'); ?></p>
                        <p><strong>DB Country:</strong> <?php echo esc_html($customer_check->country ?? 'NULL'); ?></p>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
            
            <div class="invoice-dates">
                <div>Erstellungsdatum: <?php echo date('d.m.Y'); ?></div>
                <div>Fälligkeitsdatum: <?php echo date('d.m.Y', strtotime('+30 days')); ?></div>
                <div>Lieferdatum: <?php echo date('d.m.Y'); ?></div>
            </div>
            
            <div class="customer-info-section">
                <div class="customer-info-title">Serviceinformationen</div>
                <div class="customer-info-grid">
                    <div class="customer-info-item">
                        <span class="customer-info-label">Service-ID:</span>
                        <span class="customer-info-value"><?php echo esc_html($invoice_data->id); ?></span>
                    </div>
                    <div class="customer-info-item">
                        <span class="customer-info-label">Service-Typ:</span>
                        <span class="customer-info-value"><?php echo $invoice_type === 'service' ? 'Service' : 'Reparatur-Anfrage'; ?></span>
                    </div>
                    <?php if ($invoice_type === 'request'): ?>
                    <div class="customer-info-item">
                        <span class="customer-info-label">Gerät:</span>
                        <span class="customer-info-value"><?php echo esc_html($invoice_data->model ?: 'N/A'); ?></span>
                    </div>
                    <div class="customer-info-item">
                        <span class="customer-info-label">Seriennummer:</span>
                        <span class="customer-info-value"><?php echo esc_html($invoice_data->serial ?: 'N/A'); ?></span>
                    </div>
                    <?php endif; ?>
                    <div class="customer-info-item">
                        <span class="customer-info-label">Status:</span>
                        <span class="customer-info-value"><?php echo esc_html($invoice_data->status_title ?: 'N/A'); ?></span>
                    </div>
                    <div class="customer-info-item">
                        <span class="customer-info-label">Erstellt am:</span>
                        <span class="customer-info-value"><?php echo date('d.m.Y H:i', strtotime($invoice_data->created_at)); ?></span>
                    </div>
                </div>
            </div>
            
            
            <?php if (!empty($services)): ?>
            <div class="services-section" style="margin: 20px 0;">
                <div class="services-title" style="font-weight: bold; font-size: 14px; margin-bottom: 10px; color: #333;">
                    Rechnungsservices
                </div>
                <table class="invoice-table" style="width: 100%; border-collapse: collapse; margin-bottom: 15px;">
                <thead>
                        <tr style="background-color: #f5f5f5;">
                            <th style="border: 1px solid #ddd; padding: 8px; text-align: left; font-size: 11px; font-weight: bold;">Beschreibung</th>
                            <th style="border: 1px solid #ddd; padding: 8px; text-align: center; font-size: 11px; font-weight: bold;">Menge</th>
                            <th style="border: 1px solid #ddd; padding: 8px; text-align: right; font-size: 11px; font-weight: bold;">Einzelpreis</th>
                            <th style="border: 1px solid #ddd; padding: 8px; text-align: right; font-size: 11px; font-weight: bold;">Gesamtpreis</th>
                    </tr>
                </thead>
                <tbody>
                        <?php foreach ($services as $srv): 
                            $qty = isset($srv['quantity']) ? (int)$srv['quantity'] : 1;
                            $price = isset($srv['service_cost']) ? floatval($srv['service_cost']) : 0;
                            $total = $qty * $price;
                        ?>
                        <tr>
                            <td style="border: 1px solid #ddd; padding: 8px; font-size: 11px;">
                                <strong><?php echo esc_html($srv['service_title'] ?? 'Service'); ?></strong>
                                <?php if (!empty($srv['description'])): ?>
                                    <br><span style="color: #666; font-size: 10px;"><?php echo esc_html($srv['description']); ?></span>
                            <?php endif; ?>
                        </td>
                            <td style="border: 1px solid #ddd; padding: 8px; text-align: center; font-size: 11px;"><?php echo $qty; ?> Stück</td>
                            <td style="border: 1px solid #ddd; padding: 8px; text-align: right; font-size: 11px;"><?php echo number_format($price, 2, ',', '.'); ?> EUR</td>
                            <td style="border: 1px solid #ddd; padding: 8px; text-align: right; font-size: 11px; font-weight: bold;"><?php echo number_format($total, 2, ',', '.'); ?> EUR</td>
                    </tr>
                        <?php endforeach; ?>
                </tbody>
            </table>
                <?php if ($discount_percentage > 0): ?>
                    <div style="text-align: right; font-size: 11px; margin-top: 10px; padding: 8px; background: #f8f9fa; border-radius: 4px;">
                        <div style="color: #666;">
                            Zwischensumme: <?php echo number_format($total_net_amount + $discount_amount, 2, ',', '.'); ?> EUR
                        </div>
                        <div style="color: #28a745; font-weight: bold;">
                            Rabatt (<?php echo $discount_percentage; ?>%): -<?php echo number_format($discount_amount, 2, ',', '.'); ?> EUR
                        </div>
                    </div>
                <?php endif; ?>
                <div style="text-align: right; font-weight: bold; font-size: 12px; margin-top: 10px;">
                    Gesamtbetrag: <?php echo number_format($net_amount, 2, ',', '.'); ?> EUR
                </div>
            </div>
            <?php endif; ?>
            
            
            <div style="background: #f0f8ff; border: 2px solid #0066cc; padding: 15px; margin: 20px 0; border-radius: 5px; font-family: monospace; font-size: 11px;">
                <h3 style="color: #0066cc; margin: 0 0 10px 0; font-size: 14px;">🔍 DEBUG: Rechnungsservices Section</h3>
                
                <div style="margin-bottom: 10px;">
                    <strong>Request ID:</strong> <?php echo esc_html($request_id); ?><br>
                    <strong>Invoice Type:</strong> <?php echo esc_html($invoice_type); ?><br>
                    <strong>Services Source:</strong> <?php echo esc_html($services_source); ?><br>
                    <strong>Total Services Count:</strong> <?php echo count($services); ?><br>
                    <strong>Total Net Amount:</strong> €<?php echo number_format($total_net_amount, 2, ',', '.'); ?><br>
                    <strong>Net Amount:</strong> €<?php echo number_format($net_amount, 2, ',', '.'); ?><br>
                    <strong>VAT Amount:</strong> €<?php echo number_format($vat_amount, 2, ',', '.'); ?><br>
                    <strong>Gross Amount:</strong> €<?php echo number_format($gross_amount, 2, ',', '.'); ?>
                </div>
                
                <?php if (!empty($services)): ?>
                <div style="margin-bottom: 10px;">
                    <strong>Services Details:</strong><br>
                    <?php foreach ($services as $index => $srv): ?>
                        <div style="margin-left: 20px; margin-bottom: 5px;">
                            <strong>Service <?php echo $index + 1; ?>:</strong><br>
                            &nbsp;&nbsp;Title: <?php echo esc_html($srv['service_title'] ?? 'N/A'); ?><br>
                            &nbsp;&nbsp;Cost: €<?php echo number_format($srv['service_cost'] ?? 0, 2, ',', '.'); ?><br>
                            &nbsp;&nbsp;Quantity: <?php echo esc_html($srv['quantity'] ?? 0); ?><br>
                            &nbsp;&nbsp;Total: €<?php echo number_format(($srv['service_cost'] ?? 0) * ($srv['quantity'] ?? 0), 2, ',', '.'); ?><br>
                            &nbsp;&nbsp;Description: <?php echo esc_html($srv['description'] ?? 'N/A'); ?>
                        </div>
                    <?php endforeach; ?>
                </div>
                <?php else: ?>
                <div style="margin-bottom: 10px; color: #cc0000;">
                    <strong>❌ NO SERVICES FOUND!</strong><br>
                    Services array is empty or null.
                </div>
                <?php endif; ?>
                
                <div style="margin-bottom: 10px;">
                    <strong>Database Queries Debug:</strong><br>
                    <?php
                    $faktor_debug = $wpdb->get_results($wpdb->prepare(
                        "SELECT COUNT(*) as count FROM {$wpdb->prefix}nexora_faktor_services WHERE request_id = %d",
                        $request_id
                    ));
                    echo "&nbsp;&nbsp;Faktor Services Count: " . ($faktor_debug[0]->count ?? 0) . "<br>";
                    $complete_debug = $wpdb->get_var($wpdb->prepare(
                        "SELECT COUNT(*) FROM {$wpdb->prefix}nexora_complete_service_requests WHERE request_id = %d",
                        $request_id
                    ));
                    echo "&nbsp;&nbsp;Complete Services Count: " . ($complete_debug ?? 0) . "<br>";
                    $original_debug = $wpdb->get_var($wpdb->prepare(
                        "SELECT service_id FROM {$wpdb->prefix}nexora_service_requests WHERE id = %d",
                        $request_id
                    ));
                    echo "&nbsp;&nbsp;Original Service ID: " . ($original_debug ?? 'N/A') . "<br>";
                    ?>
                </div>
                
                <div style="margin-bottom: 10px;">
                    <strong>Raw Data Debug:</strong><br>
                    <textarea style="width: 100%; height: 100px; font-size: 10px; background: #f5f5f5; border: 1px solid #ccc; padding: 5px;" readonly><?php echo esc_html(print_r($services, true)); ?></textarea>
                </div>
                
                <div style="color: #666; font-size: 10px;">
                    <strong>Debug Info:</strong> This debug section shows all the data being used to generate the Rechnungsservices table. 
                    If the table is not displaying correctly, check the values above.
                </div>
            </div>
            
            <div class="summary-section">
                <table class="invoice-table">
                    <tr>
                        <td style="text-align: right; font-weight: bold;">Gesamt</td>
                        <td></td>
                        <td></td>
                        <td></td>
                        <td style="font-weight: bold;"><?php echo number_format($net_amount, 2, ',', '.'); ?> EUR</td>
                    </tr>
                </table>
            </div>
            
            <table class="invoice-table" style="margin-top: 10px;">
                <thead>
                    <tr>
                        <th>USt.</th>
                        <th>Netto</th>
                        <th>Steuerbetrag</th>
                        <th>Brutto</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td><?php echo $vat_rate; ?>% USt.</td>
                        <td><?php echo number_format($net_amount, 2, ',', '.'); ?> EUR</td>
                        <td><?php echo number_format($vat_amount, 2, ',', '.'); ?> EUR</td>
                        <td><?php echo number_format($gross_amount, 2, ',', '.'); ?> EUR</td>
                    </tr>
                </tbody>
            </table>
            
            <div class="amount-section">
                <div class="amount">BETRAG: €<?php echo number_format($gross_amount, 2, ',', '.'); ?></div>
            </div>
            
            <?php if ($discount_percentage > 0): ?>
                <div style="text-align: center; margin-top: 10px; padding: 8px; background: #e8f5e8; border-radius: 4px; font-size: 11px; color: #28a745;">
                    <strong>ℹ️ Dieser Betrag enthält bereits einen Rabatt von <?php echo $discount_percentage; ?>%</strong>
                </div>
            <?php endif; ?>
        </div>
    </div>
</body>
</html> 