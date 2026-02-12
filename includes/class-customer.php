<?php
class Nexora_Customer {

    public function __construct() {
        add_action('init', [$this, 'add_endpoints']);
        add_action('init', [$this, 'setup'], 11);
    }

    public function add_endpoints() {
    }

    public function setup()
    {    
        add_action('wp_ajax_nexora_add_service_request_user', [$this, 'nexora_add_service_request_user']);
        add_action('wp_ajax_nexora_user_requests_list', [$this, 'nexora_user_requests_list']);
        add_action('wp_ajax_nexora_get_form_options_customer', [$this, 'nexora_get_form_options_customer']);
        add_shortcode('nexora_request_form', [$this, 'render_request_form']);
        add_shortcode('nexora_requests_list', [$this, 'requests_content']);
    }

    public function requests_content() {
        $user_id = get_current_user_id();
        global $wpdb;
        $requests = $wpdb->get_results(
            $wpdb->prepare(
                "SELECT 
                    sr.id,
                    sr.serial, 
                    sr.model, 
                    sr.description, 
                    sr.created_at, 
                    sr.status_id,
                    ss.title as status_title,
                    ss.color as status_color,
                    b1.name as brand_1_name,
                    b2.name as brand_2_name,
                    b3.name as brand_3_name
                FROM {$wpdb->prefix}nexora_service_requests sr
                LEFT JOIN {$wpdb->prefix}nexora_service_status ss ON ss.id = sr.status_id
                LEFT JOIN {$wpdb->prefix}nexora_brands b1 ON sr.brand_level_1_id = b1.id
                LEFT JOIN {$wpdb->prefix}nexora_brands b2 ON sr.brand_level_2_id = b2.id
                LEFT JOIN {$wpdb->prefix}nexora_brands b3 ON sr.brand_level_3_id = b3.id
                WHERE sr.user_id = %d 
                ORDER BY sr.id DESC",
                $user_id
            )
        );
        foreach ($requests as &$request) {
            $complete_table = $wpdb->prefix . 'nexora_complete_service_requests';
            $complete_data = $wpdb->get_row($wpdb->prepare(
                "SELECT services_data FROM $complete_table WHERE request_id = %d",
                $request->id
            ), ARRAY_A);
            
            if ($complete_data && $complete_data['services_data']) {
                error_log("Complete data found for request {$request->id}: " . print_r($complete_data, true));
                
                $services = json_decode($complete_data['services_data'], true);
                error_log("Decoded services: " . print_r($services, true));
                
                if ($services && is_array($services)) {
                    $request->services = [];
                    $request->total_cost = 0;
                    $request->total_quantity = 0;
                    
                    foreach ($services as $service) {
                        error_log("Processing service: " . print_r($service, true));
                        
                        $cost = floatval($service['service_cost'] ?? 0);
                        $quantity = floatval($service['quantity'] ?? 1);
                        $service_title = isset($service['service_title']) ? $service['service_title'] : 'Service';
                        
                        error_log("Extracted: cost={$cost}, quantity={$quantity}, service_title={$service_title}");
                        
                        $request->services[] = [
                            'service_title' => $service_title,
                            'service_cost' => $cost,
                            'quantity' => $quantity,
                            'description' => $service['description'] ?? ''
                        ];
                        error_log("Added service: " . json_encode($request->services[count($request->services) - 1]));
                        
                        $request->total_cost += $cost * $quantity;
                        $request->total_quantity += $quantity;
                    }
                    $discount_percentage = floatval(get_user_meta($request->user_id, 'discount_percentage', true));
                    if ($discount_percentage > 0) {
                        $discount_amount = ($request->total_cost * $discount_percentage) / 100;
                        $request->total_cost = $request->total_cost - $discount_amount;
                        $request->discount_applied = $discount_percentage;
                        $request->discount_amount = $discount_amount;
                    }
                } else {
                    $request->services = [];
                    $request->total_cost = 0;
                    $request->total_quantity = 0;
                }
            } else {
                $service_info = $wpdb->get_row($wpdb->prepare(
                    "SELECT s.title as service_title, s.cost as service_cost, sr.service_quantity
                     FROM {$wpdb->prefix}nexora_service_requests sr
                     LEFT JOIN {$wpdb->prefix}nexora_services s ON sr.service_id = s.id
                     WHERE sr.id = %d",
                    $request->id
                ));
                
                if ($service_info) {
                    $request->services = [[
                        'service_title' => $service_info->service_title,
                        'service_cost' => $service_info->service_cost,
                        'quantity' => $service_info->service_quantity ?: 1
                    ]];
                    $request->total_cost = floatval($service_info->service_cost) * intval($service_info->service_quantity ?: 1);
                    $request->total_quantity = intval($service_info->service_quantity ?: 1);
                    $discount_percentage = floatval(get_user_meta($request->user_id, 'discount_percentage', true));
                    if ($discount_percentage > 0) {
                        $discount_amount = ($request->total_cost * $discount_percentage) / 100;
                        $request->total_cost = $request->total_cost - $discount_amount;
                        $request->discount_applied = $discount_percentage;
                        $request->discount_amount = $discount_amount;
                    }
                } else {
                    $request->services = [];
                    $request->total_cost = 0;
                    $request->total_quantity = 0;
                }
            }
        }

        ob_start();
        ?>
        <div class="Nexora Service Suite-requests-wrapper">
            <h2>Meine Serviceanfragen</h2>
            
            <style>
                .Nexora Service Suite-requests-wrapper {
                    max-width: 1200px;
                    margin: 0 auto;
                    font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
                }
                
                .Nexora Service Suite-requests-wrapper h2 {
                    color: #333;
                    margin-bottom: 30px;
                    font-size: 24px;
                    font-weight: 600;
                }
                
                .request-item {
                    background: #fff;
                    border: 1px solid #e1e5e9;
                    border-radius: 8px;
                    margin-bottom: 16px;
                    box-shadow: 0 2px 4px rgba(0,0,0,0.05);
                    transition: all 0.3s ease;
                    overflow: hidden;
                }
                
                .request-item:hover {
                    box-shadow: 0 4px 12px rgba(0,0,0,0.1);
                    transform: translateY(-2px);
                }
                
                .request-header {
                    padding: 20px;
                    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
                    color: white;
                    cursor: pointer;
                    display: flex;
                    justify-content: space-between;
                    align-items: center;
                    transition: background 0.3s ease;
                }
                
                .request-header:hover {
                    background: linear-gradient(135deg, #5a6fd8 0%, #6a4190 100%);
                }
                
                .request-header-left {
                    display: flex;
                    align-items: center;
                    gap: 16px;
                }
                
                .request-id {
                    background: rgba(255,255,255,0.2);
                    padding: 6px 12px;
                    border-radius: 20px;
                    font-weight: 600;
                    font-size: 14px;
                }
                
                .request-model {
                    font-size: 18px;
                    font-weight: 600;
                }
                
                .request-date {
                    font-size: 14px;
                    opacity: 0.9;
                }
                
                .request-status {
                    padding: 6px 16px;
                    border-radius: 20px;
                    font-size: 12px;
                    font-weight: 600;
                    text-transform: uppercase;
                    letter-spacing: 0.5px;
                    box-shadow: 0 2px 4px rgba(0,0,0,0.15);
                    text-shadow: 0 1px 2px rgba(0,0,0,0.2);
                }
                
                .request-details {
                    padding: 0;
                    max-height: 0;
                    overflow: hidden;
                    transition: all 0.4s ease;
                    background: #fafbfc;
                }
                
                .request-details.active {
                    padding: 24px;
                    max-height: 500px;
                }
                
                .details-grid {
                    display: grid;
                    grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
                    gap: 24px;
                }
                
                .detail-section {
                    background: white;
                    padding: 20px;
                    border-radius: 8px;
                    border: 1px solid #e1e5e9;
                }
                
                .detail-section h4 {
                    margin: 0 0 16px 0;
                    color: #333;
                    font-size: 16px;
                    font-weight: 600;
                    border-bottom: 2px solid #667eea;
                    padding-bottom: 8px;
                }
                
                .detail-row {
                    display: flex;
                    justify-content: space-between;
                    margin-bottom: 12px;
                    padding: 8px 0;
                    border-bottom: 1px solid #f0f0f0;
                }
                
                .detail-row:last-child {
                    border-bottom: none;
                    margin-bottom: 0;
                }
                
                .detail-label {
                    font-weight: 600;
                    color: #666;
                    min-width: 0;
                }
                
                .detail-value {
                    color: #333;
                    text-align: right;
                    flex: 1;
                }
                
                .invoice-section {
                    margin-top: 20px;
                    padding-top: 20px;
                    border-top: 1px solid #e1e5e9;
                }
                
                .invoice-item {
                    display: flex;
                    align-items: center;
                    gap: 12px;
                    padding: 12px;
                    background: #f8f9fa;
                    border-radius: 6px;
                    margin-bottom: 8px;
                }
                
                .invoice-download-link {
                    color: #667eea;
                    text-decoration: none;
                    font-weight: 500;
                    display: flex;
                    align-items: center;
                    gap: 8px;
                    padding: 8px 16px;
                    border: 1px solid #667eea;
                    border-radius: 6px;
                    transition: all 0.3s ease;
                }
                
                .invoice-download-link:hover {
                    background: #667eea;
                    color: white;
                    text-decoration: none;
                }
                
                .no-requests {
                    text-align: center;
                    padding: 60px 20px;
                    color: #666;
                }
                
                .no-requests h3 {
                    margin-bottom: 16px;
                    color: #333;
                }
                
                .no-requests .button {
                    background: #667eea;
                    color: white;
                    padding: 12px 24px;
                    border-radius: 6px;
                    text-decoration: none;
                    display: inline-block;
                    margin-top: 16px;
                    transition: background 0.3s ease;
                }
                
                .no-requests .button:hover {
                    background: #5a6fd8;
                    text-decoration: none;
                }
                
                .expand-icon {
                    transition: transform 0.3s ease;
                    font-size: 20px;
                }
                
                .request-header.active .expand-icon {
                    transform: rotate(180deg);
                }
                
                @media (max-width: 768px) {
                    .request-header {
                        flex-direction: column;
                        gap: 12px;
                        align-items: flex-start;
                    }
                    
                    .request-header-left {
                        width: 100%;
                        justify-content: space-between;
                    }
                    
                    .details-grid {
                        grid-template-columns: 1fr;
                    }
                    
                    .detail-row {
                        flex-direction: column;
                        gap: 4px;
                    }
                    
                    .detail-value {
                        text-align: left;
                    }
                }
            </style>
            
            <?php if (!empty($requests)): ?>
                <?php foreach ($requests as $request): ?>
                    <?php
                    $invoices = $wpdb->get_results($wpdb->prepare(
                        "SELECT * FROM {$wpdb->prefix}nexora_request_invoices WHERE request_id = %d ORDER BY created_at DESC",
                        $request->id
                    ));
                    ?>
                    <div class="request-item">
                        <div class="request-header" onclick="toggleRequestDetails(this)">
                            <div class="request-header-left">
                                <span class="request-id">#<?php echo esc_html($request->id); ?></span>
                                <div>
                                    <div class="request-model"><?php echo esc_html($request->model); ?></div>
                                    <div class="request-date"><?php echo date('d.m.Y H:i', strtotime($request->created_at)); ?></div>
                                </div>
                            </div>
                            <div style="display: flex; align-items: center; gap: 16px;">
                                <span class="request-status" style="background-color: <?php echo esc_attr($request->status_color ?: '#0073aa'); ?>; color: white;">
                                    <?php echo esc_html($this->getStatusLabel($request->status_id)); ?>
                                </span>
                                <span class="expand-icon">▼</span>
                            </div>
                        </div>
                        
                        <div class="request-details">
                            <div class="details-grid">
                                <div class="detail-section">
                                    <h4>📋 Anfrage-Details</h4>
                                    <div class="detail-row">
                                        <span class="detail-label">Anfrage-ID:</span>
                                        <span class="detail-value">#<?php echo esc_html($request->id); ?></span>
                                    </div>
                                    <div class="detail-row">
                                        <span class="detail-label">Modell:</span>
                                        <span class="detail-value"><?php echo esc_html($request->model); ?></span>
                                    </div>
                                    <div class="detail-row">
                                        <span class="detail-label">Seriennummer:</span>
                                        <span class="detail-value"><?php echo esc_html($request->serial); ?></span>
                                    </div>
                                    <div class="detail-row">
                                        <span class="detail-label">Erstellt am:</span>
                                        <span class="detail-value"><?php echo date('d.m.Y H:i', strtotime($request->created_at)); ?></span>
                                    </div>
                                    <div class="detail-row">
                                        <span class="detail-label">Status:</span>
                                        <span class="detail-value">
                                            <span class="request-status" style="background-color: <?php echo esc_attr($request->status_color ?: '#0073aa'); ?>; color: white;">
                                                <?php echo esc_html($this->getStatusLabel($request->status_id)); ?>
                                            </span>
                                        </span>
                                    </div>
                                </div>
                                
                                <div class="detail-section">
                                    <h4>🔧 Geräte-Informationen</h4>
                                    <div class="detail-row">
                                        <span class="detail-label">Gerätetyp:</span>
                                        <span class="detail-value"><?php echo esc_html($request->brand_1_name ?: '-'); ?></span>
                                    </div>
                                    <div class="detail-row">
                                        <span class="detail-label">Marke:</span>
                                        <span class="detail-value"><?php echo esc_html($request->brand_2_name ?: '-'); ?></span>
                                    </div>
                                    <div class="detail-row">
                                        <span class="detail-label">Serie:</span>
                                        <span class="detail-value"><?php echo esc_html($request->brand_3_name ?: '-'); ?></span>
                                    </div>
                                    <div class="detail-row">
                                        <span class="detail-label">Services & Preis:</span>
                                        <span class="detail-value">
                                            <?php if (!empty($request->services)): ?>
                                                <?php foreach ($request->services as $service): ?>
                                                    <div style="margin-bottom: 8px; padding: 8px; background: #f8f9fa; border-radius: 4px;">
                                                        <strong><?php echo esc_html($service['service_title'] ?? 'Service'); ?></strong>
                                                        <?php if (!empty($service['quantity']) && $service['quantity'] > 1): ?>
                                                            <span style="color: #666; font-size: 12px;"> (<?php echo esc_html($service['quantity']); ?>x)</span>
                                                        <?php endif; ?>
                                                        <br>
                                                        <?php if (!empty($service['description'])): ?>
                                                            <span style="color:#666;font-size:12px;"><?php echo esc_html($service['description']); ?></span><br>
                                                        <?php endif; ?>
                                                        <span style="color: #28a745; font-weight: 600;">
                                                            <?php 
                                                            $service_cost = floatval($service['service_cost'] ?? 0);
                                                            $service_quantity = intval($service['quantity'] ?? 1);
                                                            $service_total = $service_cost * $service_quantity;
                                                            echo ($service_total > 0 ? number_format($service_total, 2) . ' €' : '-'); 
                                                            ?>
                                                        </span>
                                                    </div>
                                                <?php endforeach; ?>
                                                <div style="margin-top: 12px; padding: 8px; background: #e3f2fd; border-radius: 4px; border-left: 4px solid #2196f3;">
                                                    <?php if (isset($request->discount_applied) && $request->discount_applied > 0): ?>
                                                        <div style="margin-bottom: 8px;">
                                                            <span style="color: #28a745; font-weight: 600;">
                                                                Rabatt (<?php echo $request->discount_applied; ?>%): -<?php echo number_format($request->discount_amount, 2); ?> €
                                                            </span>
                                                        </div>
                                                    <?php endif; ?>
                                                    <strong>Gesamtpreis: <?php echo number_format($request->total_cost, 2); ?> €</strong>
                                                    <br>
                                                    <small style="color: #666;">(<?php echo $request->total_quantity; ?> Services)</small>
                                                </div>
                                            <?php else: ?>
                                                <span style="color: #666;">Keine Services definiert</span>
                                            <?php endif; ?>
                                        </span>
                                    </div>
                                </div>
                                
                                <div class="detail-section" style="grid-column: 1 / -1;">
                                    <h4>📝 Beschreibung</h4>
                                    <div style="background: #f8f9fa; padding: 16px; border-radius: 6px; margin-top: 12px;">
                                        <?php echo nl2br(esc_html($request->description)); ?>
                                    </div>
                                </div>
                                
                                <?php if (!empty($invoices)): ?>
                                    <div class="detail-section" style="grid-column: 1 / -1;">
                                        <h4>📄 Rechnungen</h4>
                                        <div class="invoice-section">
                                            <?php foreach ($invoices as $invoice): ?>
                                                <div class="invoice-item">
                                                    <span>📄</span>
                                                    <span><?php echo esc_html($invoice->file_name); ?></span>
                                                    <a href="<?php echo wp_upload_dir()['baseurl'] . '/Nexora Service Suite-invoices/' . basename($invoice->file_path); ?>" 
                                                       target="_blank" 
                                                       class="invoice-download-link"
                                                       title="<?php echo esc_attr($invoice->file_name); ?>">
                                                        Herunterladen
                                                    </a>
                                                </div>
                                            <?php endforeach; ?>
                                        </div>
                                    </div>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php else: ?>
                <div class="no-requests">
                    <h3>Keine Serviceanfragen gefunden</h3>
                    <p>Sie haben noch keine Serviceanfragen erstellt.</p>
                    <a href="<?php echo wc_get_account_endpoint_url('service-request'); ?>" class="button">Neue Serviceanfrage erstellen</a>
                </div>
            <?php endif; ?>
        </div>
        
        <script>
        function toggleRequestDetails(header) {
            const details = header.nextElementSibling;
            const expandIcon = header.querySelector('.expand-icon');
            header.classList.toggle('active');
            details.classList.toggle('active');
            if (details.classList.contains('active')) {
                setTimeout(() => {
                    details.scrollIntoView({ 
                        behavior: 'smooth', 
                        block: 'nearest' 
                    });
                }, 300);
            }
        }
        document.addEventListener('DOMContentLoaded', function() {
            const headers = document.querySelectorAll('.request-header');
            headers.forEach(header => {
                header.addEventListener('touchstart', function(e) {
                    e.preventDefault();
                    toggleRequestDetails(this);
                });
            });
        });
        </script>
        <?php
        $content = ob_get_clean();
        echo $content;
    }
    
    public function render_request_form()
    {
        global $wpdb;
        $services = $wpdb->get_results("SELECT id, title, cost FROM {$wpdb->prefix}nexora_services WHERE status = 'active'");
        $brands_level1 = $wpdb->get_results("SELECT id, name FROM {$wpdb->prefix}nexora_brands WHERE parent_id IS NULL");
        
        ob_start();
        ?>
        <div class="woocommerce">
        <form id="Nexora Service Suite-service-request-form" class="woocommerce-EditAccountForm edit-account" method="post">
            <?php wp_nonce_field('nexora_user_nonce', 'nonce'); ?>
            <h2><?php _e('Neue Serviceanfrage erstellen', 'Nexora Service Suite'); ?></h2>
            <p class="form-row form-row-wide">
                <label for="service_id">Serviceart <span class="required">*</span></label>
                <select name="service_id" id="service_id" required>
                    <option value="">Bitte wählen Sie aus</option>
                    <?php foreach (
                        $services as $service): ?>
                        <option value="<?php echo esc_attr($service->id); ?>" data-cost="<?php echo esc_attr($service->cost); ?>">
                            <?php echo esc_html($service->title); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
                <div id="Nexora Service Suite-service-cost-display" style="margin-top:8px; color:#0073aa; font-weight:600; display:none;"></div>
            </p>
            <p class="form-row form-row-wide">
                <label for="device_type_id">Gerätetyp <span class="required">*</span></label>
                <select name="device_type_id" id="device_type_id" required>
                    <option value="">Bitte wählen Sie aus</option>
                </select>
            </p>
            <p class="form-row form-row-wide">
                <label for="device_brand_id">Marke <span class="required">*</span></label>
                <select name="device_brand_id" id="device_brand_id" required disabled>
                    <option value="">Bitte wählen Sie zuerst einen Gerätetyp</option>
                </select>
            </p>
            <p class="form-row form-row-wide">
                <label for="device_series_id">Serie <span class="required">*</span></label>
                <select name="device_series_id" id="device_series_id" required disabled>
                    <option value="">Bitte wählen Sie zuerst eine Marke</option>
                </select>
            </p>
            <p class="form-row form-row-wide">
                <label for="device_model_id">Gerätemodell <span class="required">*</span></label>
                <select name="device_model_id" id="device_model_id" required disabled>
                    <option value="">Bitte wählen Sie zuerst eine Serie</option>
                </select>
            </p>
            <p class="form-row form-row-wide">
                <label for="description">Beschreibung <span class="required">*</span></label>
                <textarea name="description" id="description" rows="5" placeholder="Detaillierte Beschreibung oder Problembeschreibung" required></textarea>
            </p>
            <p class="form-row">
                <button type="submit" class="woocommerce-Button button button-primary">Serviceanfrage senden</button>
            </p>
        </form>
        <div id="Nexora Service Suite-message"></div>
        </div>
        <script>
        jQuery(document).ready(function($) {
            $('#service_id').on('change', function() {
                var cost = $(this).find('option:selected').data('cost');
                if (cost !== undefined && cost !== null && cost !== '') {
                    $('#Nexora Service Suite-service-cost-display').html('Preis: ' + cost + ' €').show();
                } else {
                    $('#Nexora Service Suite-service-cost-display').hide();
                }
            });
            function loadDeviceTypes(selected) {
                $.post(nexora_user_ajax.ajax_url, {
                    action: 'nexora_get_device_types',
                    nonce: nexora_user_ajax.nonce
                }, function(response) {
                    if (response.success) {
                        let html = '<option value="">Bitte wählen Sie aus</option>';
                        response.data.forEach(function(type) {
                            html += `<option value="${type.id}"${selected && selected.device_type_id == type.id ? ' selected' : ''}>${type.name}</option>`;
                        });
                        $('#device_type_id').html(html);
                        if (selected && selected.device_type_id) {
                            $('#device_type_id').val(selected.device_type_id);
                            loadDeviceBrands(selected.device_type_id, selected);
                        }
                    }
                });
            }
            function loadDeviceBrands(typeId, selected) {
                if (!typeId) {
                    $('#device_brand_id').html('<option value="">Bitte wählen Sie zuerst einen Gerätetyp</option>').prop('disabled', true);
                    $('#device_series_id').html('<option value="">Bitte wählen Sie zuerst eine Marke</option>').prop('disabled', true);
                    $('#device_model_id').html('<option value="">Bitte wählen Sie zuerst eine Serie</option>').prop('disabled', true);
                    return;
                }
                $.post(nexora_user_ajax.ajax_url, {
                    action: 'nexora_get_device_brands',
                    nonce: nexora_user_ajax.nonce,
                    type_id: typeId
                }, function(response) {
                    if (response.success) {
                        let html = '<option value="">Bitte wählen Sie aus</option>';
                        response.data.forEach(function(brand) {
                            html += `<option value="${brand.id}"${selected && selected.device_brand_id == brand.id ? ' selected' : ''}>${brand.name}</option>`;
                        });
                        $('#device_brand_id').html(html).prop('disabled', false);
                        if (selected && selected.device_brand_id) {
                            $('#device_brand_id').val(selected.device_brand_id);
                            loadDeviceSeries(selected.device_brand_id, selected);
                        }
                    }
                });
            }
            function loadDeviceSeries(brandId, selected) {
                if (!brandId) {
                    $('#device_series_id').html('<option value="">Bitte wählen Sie zuerst eine Marke</option>').prop('disabled', true);
                    $('#device_model_id').html('<option value="">Bitte wählen Sie zuerst eine Serie</option>').prop('disabled', true);
                    return;
                }
                $.post(nexora_user_ajax.ajax_url, {
                    action: 'nexora_get_device_series',
                    nonce: nexora_user_ajax.nonce,
                    brand_id: brandId
                }, function(response) {
                    if (response.success) {
                        let html = '<option value="">Bitte wählen Sie aus</option>';
                        response.data.forEach(function(series) {
                            html += `<option value="${series.id}"${selected && selected.device_series_id == series.id ? ' selected' : ''}>${series.name}</option>`;
                        });
                        $('#device_series_id').html(html).prop('disabled', false);
                        if (selected && selected.device_series_id) {
                            $('#device_series_id').val(selected.device_series_id);
                            loadDeviceModels(selected.device_series_id, selected);
                        }
                    }
                });
            }
            function loadDeviceModels(seriesId, selected) {
                if (!seriesId) {
                    $('#device_model_id').html('<option value="">Bitte wählen Sie zuerst eine Serie</option>').prop('disabled', true);
                    return;
                }
                $.post(nexora_user_ajax.ajax_url, {
                    action: 'nexora_get_device_models',
                    nonce: nexora_user_ajax.nonce,
                    series_id: seriesId
                }, function(response) {
                    if (response.success) {
                        let html = '<option value="">Bitte wählen Sie aus</option>';
                        response.data.forEach(function(model) {
                            html += `<option value="${model.id}"${selected && selected.device_model_id == model.id ? ' selected' : ''}>${model.name}</option>`;
                        });
                        $('#device_model_id').html(html).prop('disabled', false);
                        if (selected && selected.device_model_id) {
                            $('#device_model_id').val(selected.device_model_id);
                        }
                    }
                });
            }
            loadDeviceTypes();
            $('#device_type_id').on('change', function() {
                loadDeviceBrands($(this).val());
            });
            $('#device_brand_id').on('change', function() {
                loadDeviceSeries($(this).val());
            });
            $('#device_series_id').on('change', function() {
                loadDeviceModels($(this).val());
            });
        });
        </script>
        <?php
        return ob_get_clean();
    }

    public function nexora_add_service_request_user()
    {
        check_ajax_referer('nexora_nonce', 'nonce');
        if(!is_user_logged_in()) wp_send_json_error('Bitte melden Sie sich zuerst an');
        global $wpdb;
        $user_id = get_current_user_id();
        $data = [
            'model' => sanitize_text_field($_POST['model']),
            'serial' => sanitize_text_field($_POST['serial']),
            'description' => sanitize_textarea_field($_POST['description']),
            'service_description' => sanitize_textarea_field($_POST['service_description']),
            'brand_level_1_id' => intval($_POST['brand_level_1_id']),
            'brand_level_2_id' => intval($_POST['brand_level_2_id']),
            'brand_level_3_id' => intval($_POST['brand_level_3_id']),
            'user_id' => $user_id,
            'status_id' => $this->getDefaultStatus()
        ];
        $res = $wpdb->insert($wpdb->prefix.'nexora_service_requests', $data);
        if($res) {
            $request_id = $wpdb->insert_id;
            do_action('nexora_service_request_created', $request_id, $user_id);
            error_log('New service request hook triggered: nexora_service_request_created(' . $request_id . ', ' . $user_id . ')');
            
            wp_send_json_success();
        } else {
            wp_send_json_error('Fehler bei der Registrierung');
        }
    }

    public function nexora_user_requests_list()
    {
        check_ajax_referer('nexora_nonce', 'nonce');
        if(!is_user_logged_in()) wp_send_json_error();
        global $wpdb;
        $user_id = get_current_user_id();

         $sql="SELECT 
                esr.id as invoice_id,
                esr.id as request_id,
                esr.serial, 
                esr.model, 
                esr.description, 
                esr.service_description,
                ss.title AS title_status,
                s.cost as service_cost
            FROM wp_nexora_service_requests esr
            LEFT JOIN wp_nexora_service_status ss ON (ss.id = esr.status_id)
            LEFT JOIN wp_nexora_services s ON (esr.service_id = s.id)
            WHERE esr.user_id = {$user_id} and esr.order_id IS NULL
            ORDER BY esr.id DESC
            LIMIT 5; ";

             $rows = $wpdb->get_results($wpdb->prepare(  $sql));
            wp_send_json_success(value: $rows);
    }

    public function nexora_get_form_options_customer()
    {
        global $wpdb;
        $brands = $wpdb->get_results("SELECT id, name FROM {$wpdb->prefix}nexora_brands ORDER BY name ASC");

        wp_send_json_success([
            'brands' => $brands
        ]);
    }

    private function getDefaultStatus()
    {
        global $wpdb;
        $tbl=$wpdb->prefix."nexora_service_status";
        $sql = $wpdb->prepare(
        "SELECT id FROM {$tbl} WHERE is_default = 1");
        $statusId = $wpdb->get_var($sql);
        return $statusId;
    }

    public static function render_user_dashboard($content) {
        if (is_account_page() && function_exists('wc_get_account_endpoint_url')) {
            echo $content;
            return;
        }
        ?>
        <div class="Nexora Service Suite-user-dashboard">
            <div class="dashboard-sidebar">
                <div class="dashboard-nav-item"><a href="/my-account/">Dashboard</a></div>
                <div class="dashboard-nav-item"><a href="/my-account/requests/">Serviceanfragen</a></div>
                <div class="dashboard-nav-item"><a href="/my-account/service-request/">Neue Anfrage</a></div>
            </div>
            <div class="dashboard-main-content">
                <?php echo $content; ?>
            </div>
        </div>
        <?php
    }

    private function getStatusLabel($status_id) {
        global $wpdb;
        $status = $wpdb->get_var($wpdb->prepare(
            "SELECT title FROM {$wpdb->prefix}nexora_service_status WHERE id = %d",
            $status_id
        ));
        $status_map = [
            'Neu' => 'ausstehend',
            'In Bearbeitung' => 'in Bearbeitung',
            'Abgeschlossen' => 'abgeschlossen',
            'Abgelehnt' => 'storniert',
            'pending' => 'ausstehend',
            'processing' => 'in Bearbeitung',
            'completed' => 'abgeschlossen',
            'cancelled' => 'storniert'
        ];
        
        return isset($status_map[$status]) ? $status_map[$status] : $status;
    }

}

?>