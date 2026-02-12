<?php

if (!defined('ABSPATH')) {
    exit;
}

class Nexora_Easy_Registration {
    
    public function __construct() {
        add_action('wp_ajax_nexora_easy_register', array($this, 'ajax_easy_register'));
        add_action('wp_ajax_nopriv_nexora_easy_register', array($this, 'ajax_easy_register'));
        add_action('wp_ajax_nexora_easy_service_request', array($this, 'ajax_easy_service_request'));
        add_action('wp_ajax_nopriv_nexora_easy_service_request', array($this, 'ajax_easy_service_request'));
        add_shortcode('nexora_easy_form', array($this, 'render_easy_form'));
    }
    
    public function render_easy_form() {
        ob_start();
        ?>
        <div class="Nexora Service Suite-easy-form">
            <h3>🚀 Ihre Kontakt Daten</h3>
            
            
            <form method="post" action="" id="simpleForm">
                <p><label>Name: <input type="text" name="full_name" required></label></p>
                <p><label>Telefon: <input type="tel" name="phone" required></label></p>
                <p><label>Email: <input type="email" name="email" required></label></p>
                <p><label>PLZ: <input type="text" name="postal_code" required></label></p>
                <p><button type="submit">Registrieren</button></p>
            </form>
            
            
            
            <script>
            document.getElementById('simpleForm').addEventListener('submit', function(e) {
                e.preventDefault();
                var formData = new FormData(this);
                formData.append('action', 'nexora_easy_register');
                formData.append('nonce', '<?php echo wp_create_nonce("nexora_easy_nonce"); ?>');
                fetch('<?php echo admin_url("admin-ajax.php"); ?>', {
                    method: 'POST',
                    body: formData
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        showServiceRequestForm(data.data.user_id);
                    } else {
                        console.error('Registration error:', data.data);
                    }
                })
                .catch(error => {
                    console.error('AJAX Error:', error);
                });
            });
            function showServiceRequestForm(userId) {
                var container = document.querySelector('.Nexora Service Suite-easy-form');
                document.getElementById('simpleForm').style.display = 'none';
                var serviceForm = document.createElement('div');
                serviceForm.innerHTML = `
                    <div class="Nexora Service Suite-service-request-container">
                        <h2>Neue Serviceanfrage erstellen</h2>
                        <form id="Nexora Service Suite-service-request-form" class="Nexora Service Suite-form" enctype="multipart/form-data">
                            <div class="form-row">
                                <div class="form-group">
                                    <label for="device_type_id">Gerätetyp <span class="required">*</span></label>
                                    <select name="device_type_id" id="device_type_id" required>
                                        <option value="">Bitte wählen</option>
                                    </select>
                                    <input type="text" name="device_type_custom" id="device_type_custom" placeholder="Eigener Gerätetyp" style="display:none;margin-top:8px;width:100%;padding:8px;border:1px solid #ddd;border-radius:4px;">
                                </div>
                            </div>
                            <div class="form-row">
                                <div class="form-group">
                                    <label for="device_brand_id">Marke <span class="required">*</span></label>
                                    <select name="device_brand_id" id="device_brand_id" required disabled>
                                        <option value="">Bitte wählen Sie zuerst einen Gerätetyp</option>
                                    </select>
                                    <input type="text" name="device_brand_custom" id="device_brand_custom" placeholder="Eigene Marke" style="display:none;margin-top:8px;width:100%;padding:8px;border:1px solid #ddd;border-radius:4px;">
                                </div>
                            </div>
                            <div class="form-row">
                                <div class="form-group">
                                    <label for="device_series_id">Serie <span class="required">*</span></label>
                                    <select name="device_series_id" id="device_series_id" required disabled>
                                        <option value="">Bitte wählen Sie zuerst eine Marke</option>
                                    </select>
                                    <input type="text" name="device_series_custom" id="device_series_custom" placeholder="Eigene Serie" style="display:none;margin-top:8px;width:100%;padding:8px;border:1px solid #ddd;border-radius:4px;">
                                </div>
                            </div>
                            <div class="form-row">
                                <div class="form-group">
                                    <label for="device_model_id">Gerätemodell <span class="required">*</span></label>
                                    <select name="device_model_id" id="device_model_id" required disabled>
                                        <option value="">Bitte wählen Sie zuerst eine Serie</option>
                                    </select>
                                    <input type="text" name="device_model_custom" id="device_model_custom" placeholder="Eigenes Modell" style="display:none;margin-top:8px;width:100%;padding:8px;border:1px solid #ddd;border-radius:4px;">
                                </div>
                            </div>
                            <div class="form-row">
                                <div class="form-group">
                                    <label for="attachments">Bilder anhängen (max. 5)</label>
                                    <input type="file" name="attachments[]" id="attachments" accept="image/*" multiple style="display:block;margin-bottom:8px;" />
                                    <div id="attachments-preview" style="display:flex;gap:8px;flex-wrap:wrap;"></div>
                                </div>
                            </div>
                            <div class="form-row">
                                <div class="form-group">
                                    <label for="serial">Seriennummer</label>
                                    <input type="text" name="serial" id="serial" placeholder="Seriennummer des Geräts (optional)">
                                </div>
                            </div>
                            <div class="form-row">
                                <div class="form-group">
                                    <label for="description">Beschreibung <span class="required">*</span></label>
                                    <textarea name="description" id="description" rows="5" placeholder="Detaillierte Beschreibung oder Problembeschreibung" required></textarea>
                                </div>
                            </div>
                            <div class="form-actions">
                                <button type="submit" class="button-primary">Serviceanfrage senden</button>
                            </div>
                            <input type="hidden" name="user_id" value="${userId}">
                        </form>
                    </div>
                `;
                
                container.appendChild(serviceForm);
                loadServiceFormData();
                setupDependentDropdowns();
                document.getElementById('Nexora Service Suite-service-request-form').addEventListener('submit', function(e) {
                    e.preventDefault();
                    
                    var serviceFormData = new FormData(this);
                    serviceFormData.append('action', 'nexora_easy_service_request');
                    serviceFormData.append('nonce', '<?php echo wp_create_nonce("nexora_easy_nonce"); ?>');
                    
                    fetch('<?php echo admin_url("admin-ajax.php"); ?>', {
                        method: 'POST',
                        body: serviceFormData
                    })
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            showSuccessMessage();
                        } else {
                            console.error('Service Error:', data.data);
                        }
                    })
                    .catch(error => {
                        console.error('Service AJAX Error:', error);
                    });
                });
            }
            function loadServiceFormData() {
                var deviceTypeSelect = document.getElementById('device_type_id');
                deviceTypeSelect.innerHTML = '<option value="">Lade Gerätetypen...</option>';
                fetch('<?php echo admin_url("admin-ajax.php"); ?>', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/x-www-form-urlencoded',
                    },
                    body: 'action=nexora_get_device_types&nonce=<?php echo wp_create_nonce("nexora_nonce"); ?>'
                })
                .then(response => response.json())
                .then(data => {
                    console.log('Device types response:', data);
                    if (data.success) {
                        var html = '<option value="">Bitte wählen</option>';
                        if (data.data && data.data.length > 0) {
                            data.data.forEach(function(deviceType) {
                                html += '<option value="' + deviceType.id + '">' + deviceType.name + '</option>';
                            });
                        }
                        html += '<option value="custom">Nicht gelistet? Typen</option>';
                        console.log('Final HTML:', html);
                        deviceTypeSelect.innerHTML = html;
                        document.getElementById('device_type_custom').style.display = 'none';
                    } else {
                        console.error('Failed to load device types:', data);
                    }
                })
                .catch(error => {
                    deviceTypeSelect.innerHTML = '<option value="">Fehler beim Laden</option>';
                    console.error('Error:', error);
                });
            }
            function setupDependentDropdowns() {
                document.getElementById('device_type_id').addEventListener('change', function() {
                    var customInput = document.getElementById('device_type_custom');
                    if (this.value === 'custom') {
                        customInput.style.display = 'block';
                        customInput.required = true;
                        document.getElementById('device_brand_id').disabled = true;
                        document.getElementById('device_series_id').disabled = true;
                        document.getElementById('device_model_id').disabled = true;
                    } else {
                        customInput.style.display = 'none';
                        customInput.required = false;
                        customInput.value = '';
                        resetSubsequentStages('type');
                        loadDeviceBrands(this.value);
                    }
                });
                document.getElementById('device_brand_id').addEventListener('change', function() {
                    var customInput = document.getElementById('device_brand_custom');
                    if (this.value === 'custom') {
                        customInput.style.display = 'block';
                        customInput.required = true;
                        document.getElementById('device_series_id').disabled = true;
                        document.getElementById('device_series_custom').style.display = 'block';
                        document.getElementById('device_model_id').disabled = true;
                        document.getElementById('device_model_custom').style.display = 'block';
                    } else {
                        customInput.style.display = 'none';
                        customInput.required = false;
                        customInput.value = '';
                        resetSubsequentStages('brand');
                        loadDeviceSeries(this.value);
                    }
                });
                document.getElementById('device_series_id').addEventListener('change', function() {
                    var customInput = document.getElementById('device_series_custom');
                    if (this.value === 'custom') {
                        customInput.style.display = 'block';
                        customInput.required = true;
                        document.getElementById('device_model_id').disabled = true;
                        document.getElementById('device_model_custom').style.display = 'block';
                    } else {
                        customInput.style.display = 'none';
                        customInput.required = false;
                        customInput.value = '';
                        resetSubsequentStages('series');
                        loadDeviceModels(this.value);
                    }
                });
                document.getElementById('device_model_id').addEventListener('change', function() {
                    var customInput = document.getElementById('device_model_custom');
                    if (this.value === 'custom') {
                        customInput.style.display = 'block';
                        customInput.required = true;
                    } else {
                        customInput.style.display = 'none';
                        customInput.required = false;
                        customInput.value = '';
                    }
                });
                document.getElementById('attachments').addEventListener('change', function() {
                    var preview = document.getElementById('attachments-preview');
                    preview.innerHTML = '';
                    
                    if (this.files.length > 5) {
                        alert('Maximal 5 Bilder erlaubt!');
                        this.value = '';
                        return;
                    }
                    
                    for (var i = 0; i < this.files.length; i++) {
                        var file = this.files[i];
                        if (file.type.startsWith('image/')) {
                            var reader = new FileReader();
                            reader.onload = function(e) {
                                var img = document.createElement('img');
                                img.src = e.target.result;
                                img.style.width = '100px';
                                img.style.height = '100px';
                                img.style.objectFit = 'cover';
                                img.style.border = '1px solid #ccc';
                                preview.appendChild(img);
                            };
                            reader.readAsDataURL(file);
                        }
                    }
                });
            }
            function loadDeviceBrands(typeId) {
                if (!typeId) {
                    document.getElementById('device_brand_id').innerHTML = '<option value="">Bitte wählen Sie zuerst einen Gerätetyp</option>';
                    document.getElementById('device_brand_id').disabled = true;
                    document.getElementById('device_series_id').innerHTML = '<option value="">Bitte wählen Sie zuerst eine Marke</option>';
                    document.getElementById('device_series_id').disabled = true;
                    document.getElementById('device_model_id').innerHTML = '<option value="">Bitte wählen Sie zuerst eine Serie</option>';
                    document.getElementById('device_model_id').disabled = true;
                    return;
                }
                var brandSelect = document.getElementById('device_brand_id');
                brandSelect.innerHTML = '<option value="">Lade Marken...</option>';
                brandSelect.disabled = false;
                
                fetch('<?php echo admin_url("admin-ajax.php"); ?>', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/x-www-form-urlencoded',
                    },
                    body: 'action=nexora_get_device_brands&nonce=<?php echo wp_create_nonce("nexora_nonce"); ?>&type_id=' + typeId
                })
                .then(response => response.json())
                .then(data => {
                    console.log('Device brands response:', data);
                    if (data.success) {
                        var html = '<option value="">Bitte wählen Sie aus</option>';
                        if (data.data && data.data.length > 0) {
                            data.data.forEach(function(brand) {
                                html += '<option value="' + brand.id + '">' + brand.name + '</option>';
                            });
                        }
                        html += '<option value="custom">Nicht gelistet? Typen</option>';
                        console.log('Brands HTML:', html);
                        brandSelect.innerHTML = html;
                        document.getElementById('device_series_id').innerHTML = '<option value="">Bitte wählen Sie zuerst eine Marke</option>';
                        document.getElementById('device_series_id').disabled = true;
                        document.getElementById('device_model_id').innerHTML = '<option value="">Bitte wählen Sie zuerst eine Serie</option>';
                        document.getElementById('device_model_id').disabled = true;
                        document.getElementById('device_brand_custom').style.display = 'none';
                        document.getElementById('device_series_custom').style.display = 'none';
                        document.getElementById('device_model_custom').style.display = 'none';
                    }
                })
                .catch(error => {
                    brandSelect.innerHTML = '<option value="">Fehler beim Laden</option>';
                    console.error('Error:', error);
                });
            }
            function loadDeviceSeries(brandId) {
                if (!brandId) {
                    document.getElementById('device_series_id').innerHTML = '<option value="">Bitte wählen Sie zuerst eine Marke</option>';
                    document.getElementById('device_series_id').disabled = true;
                    document.getElementById('device_model_id').innerHTML = '<option value="">Bitte wählen Sie zuerst eine Serie</option>';
                    document.getElementById('device_model_id').disabled = true;
                    return;
                }
                var seriesSelect = document.getElementById('device_series_id');
                seriesSelect.innerHTML = '<option value="">Lade Serien...</option>';
                seriesSelect.disabled = false;
                
                fetch('<?php echo admin_url("admin-ajax.php"); ?>', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/x-www-form-urlencoded',
                    },
                    body: 'action=nexora_get_device_series&nonce=<?php echo wp_create_nonce("nexora_nonce"); ?>&brand_id=' + brandId
                })
                .then(response => response.json())
                .then(data => {
                    console.log('Device series response:', data);
                    if (data.success) {
                        var html = '<option value="">Bitte wählen Sie aus</option>';
                        if (data.data && data.data.length > 0) {
                            data.data.forEach(function(series) {
                                html += '<option value="' + series.id + '">' + series.name + '</option>';
                            });
                        }
                        html += '<option value="custom">Nicht gelistet? Typen</option>';
                        console.log('Series HTML:', html);
                        seriesSelect.innerHTML = html;
                        document.getElementById('device_model_id').innerHTML = '<option value="">Bitte wählen Sie zuerst eine Serie</option>';
                        document.getElementById('device_model_id').disabled = true;
                        document.getElementById('device_series_custom').style.display = 'none';
                        document.getElementById('device_model_custom').style.display = 'none';
                    }
                })
                .catch(error => {
                    seriesSelect.innerHTML = '<option value="">Fehler beim Laden</option>';
                    console.error('Error:', error);
                });
            }
            function loadDeviceModels(seriesId) {
                if (!seriesId) {
                    document.getElementById('device_model_id').innerHTML = '<option value="">Bitte wählen Sie zuerst eine Serie</option>';
                    document.getElementById('device_model_id').disabled = true;
                    return;
                }
                var modelSelect = document.getElementById('device_model_id');
                modelSelect.innerHTML = '<option value="">Lade Modelle...</option>';
                modelSelect.disabled = false;
                
                fetch('<?php echo admin_url("admin-ajax.php"); ?>', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/x-www-form-urlencoded',
                    },
                    body: 'action=nexora_get_device_models&nonce=<?php echo wp_create_nonce("nexora_nonce"); ?>&series_id=' + seriesId
                })
                .then(response => response.json())
                .then(data => {
                    console.log('Device models response:', data);
                    if (data.success) {
                        var html = '<option value="">Bitte wählen Sie aus</option>';
                        if (data.data && data.data.length > 0) {
                            data.data.forEach(function(model) {
                                html += '<option value="' + model.id + '">' + model.name + '</option>';
                            });
                        }
                        html += '<option value="custom">Nicht gelistet? Typen</option>';
                        console.log('Models HTML:', html);
                        modelSelect.innerHTML = html;
                        document.getElementById('device_model_custom').style.display = 'none';
                    }
                })
                .catch(error => {
                    modelSelect.innerHTML = '<option value="">Fehler beim Laden</option>';
                    console.error('Error:', error);
                });
            }
            function resetSubsequentStages(startFrom) {
                switch(startFrom) {
                    case 'type':
                        document.getElementById('device_brand_id').disabled = true;
                        document.getElementById('device_brand_custom').style.display = 'none';
                        document.getElementById('device_series_id').disabled = true;
                        document.getElementById('device_series_custom').style.display = 'none';
                        document.getElementById('device_model_id').disabled = true;
                        document.getElementById('device_model_custom').style.display = 'none';
                        break;
                    case 'brand':
                        document.getElementById('device_series_id').disabled = true;
                        document.getElementById('device_series_custom').style.display = 'none';
                        document.getElementById('device_model_id').disabled = true;
                        document.getElementById('device_model_custom').style.display = 'none';
                        break;
                    case 'series':
                        document.getElementById('device_model_id').disabled = true;
                        document.getElementById('device_model_custom').style.display = 'none';
                        break;
                }
            }
            function showSuccessMessage() {
                var container = document.querySelector('.Nexora Service Suite-easy-form');
                container.innerHTML = `
                    <div style="text-align: center; padding: 20px;">
                        <h3>✅ Erfolgreich abgeschlossen!</h3>
                        <p>Ihre Registrierung und Serviceanfrage wurden erfolgreich eingereicht.</p>
                        <p><strong>Nächste Schritte:</strong></p>
                        <p>Ihre Anfrage wird von unserem Team geprüft. Sie erhalten innerhalb von 24 Stunden eine Rückmeldung per E-Mail oder telefonisch.</p>
                        <button onclick="location.reload()">Neue Anfrage</button>
                    </div>
                `;
            }
            </script>
        </div>
        <?php
        return ob_get_clean();
    }
    
    public function ajax_easy_register() {
        error_log('AJAX called');
        
        if (!wp_verify_nonce($_POST['nonce'], 'nexora_easy_nonce')) {
            wp_send_json_error('Nonce failed');
        }
        
        $full_name = sanitize_text_field($_POST['full_name']);
        $phone = sanitize_text_field($_POST['phone']);
        $email = sanitize_email($_POST['email']);
        $postal_code = sanitize_text_field($_POST['postal_code']);
        
        error_log("Data: $full_name, $phone, $email, $postal_code");
        $user_data = array(
            'user_login' => $email,
            'user_email' => $email,
            'user_pass' => wp_generate_password(12, false),
            'first_name' => $full_name,
            'role' => 'customer'
        );
        
        $user_id = wp_insert_user($user_data);
        
        if (is_wp_error($user_id)) {
            wp_send_json_error($user_id->get_error_message());
        }
        update_user_meta($user_id, 'phone', $phone);
        update_user_meta($user_id, 'postal_code', $postal_code);
        
        error_log("User created: $user_id");
        
        wp_send_json_success(array('user_id' => $user_id));
    }
    
    public function ajax_easy_service_request() {
        if (!wp_verify_nonce($_POST['nonce'], 'nexora_easy_nonce')) {
            wp_send_json_error('Nonce failed');
        }
        
        global $wpdb;
        
        $user_id = intval($_POST['user_id']);
        $device_type_id = intval($_POST['device_type_id']);
        $device_brand_id = intval($_POST['device_brand_id']);
        $device_series_id = intval($_POST['device_series_id']);
        $device_model_id = intval($_POST['device_model_id']);
        $serial = sanitize_text_field($_POST['serial']);
        $description = sanitize_textarea_field($_POST['description']);
        $device_type_custom = sanitize_text_field($_POST['device_type_custom'] ?? '');
        $device_brand_custom = sanitize_text_field($_POST['device_brand_custom'] ?? '');
        $device_series_custom = sanitize_text_field($_POST['device_series_custom'] ?? '');
        $device_model_custom = sanitize_text_field($_POST['device_model_custom'] ?? '');
        $device_model_name = '';
        if ($device_model_id && $device_model_id !== 'custom') {
            $device_model_name = $wpdb->get_var($wpdb->prepare(
                "SELECT name FROM {$wpdb->prefix}nexora_brands WHERE id = %d",
                $device_model_id
            ));
        } elseif ($device_model_custom) {
            $device_model_name = $device_model_custom;
        }
        $default_status = $wpdb->get_var("SELECT id FROM {$wpdb->prefix}nexora_service_status WHERE is_default = 1");
        if (!$default_status) {
            $default_status = 1;
        }
        $data = array(
            'user_id' => $user_id,
            'model' => $device_model_name ?: 'Unbekanntes Modell',
            'serial' => $serial ?: '',
            'description' => $description,
            'service_description' => '',
            'brand_level_1_id' => ($device_type_id === 'custom' || $device_type_custom) ? 0 : $device_type_id,
            'brand_level_2_id' => ($device_brand_id === 'custom' || $device_brand_custom) ? 0 : $device_brand_id,
            'brand_level_3_id' => ($device_series_id === 'custom' || $device_series_custom) ? 0 : $device_series_id,
            'status_id' => $default_status,
            'priority' => 'medium',
            'created_at' => current_time('mysql'),
            'updated_at' => current_time('mysql')
        );
        $custom_info = array();
        if ($device_type_custom) $custom_info['device_type_custom'] = $device_type_custom;
        if ($device_brand_custom) $custom_info['device_brand_custom'] = $device_brand_custom;
        if ($device_series_custom) $custom_info['device_series_custom'] = $device_series_custom;
        if ($device_model_custom) $custom_info['device_model_custom'] = $device_model_custom;
        
        if (!empty($custom_info)) {
            $data['description'] = $description . "\n\nCustom Information:\n" . json_encode($custom_info, JSON_PRETTY_PRINT);
        }
        $result = $wpdb->insert($wpdb->prefix . 'nexora_service_requests', $data);
        
        if ($result === false) {
            error_log('Easy Form Service Request Insert Error: ' . $wpdb->last_error);
            wp_send_json_error('Database error: ' . $wpdb->last_error);
        }
        
        $request_id = $wpdb->insert_id;
        do_action('nexora_service_request_created', $request_id, $user_id);
        error_log('Easy Form New service request hook triggered: nexora_service_request_created(' . $request_id . ', ' . $user_id . ')');
        if (!empty($_FILES['attachments'])) {
            $this->handle_file_uploads($request_id, $_FILES['attachments']);
        }
        
        error_log("Easy Form Service Request created successfully. ID: $request_id, User: $user_id");
        
        wp_send_json_success(array(
            'message' => 'Service request created successfully',
            'request_id' => $request_id
        ));
    }
    
    private function handle_file_uploads($request_id, $files) {
        global $wpdb;
        $attachments_table = $wpdb->prefix . 'nexora_attachments';
        $table_exists = $wpdb->get_var("SHOW TABLES LIKE '$attachments_table'") === $attachments_table;
        
        if (!$table_exists) {
            error_log('Attachments table does not exist');
            return;
        }
        
        $upload_dir = wp_upload_dir();
        $nexora_dir = $upload_dir['basedir'] . '/Nexora Service Suite-attachments/';
        if (!file_exists($nexora_dir)) {
            wp_mkdir_p($nexora_dir);
        }
        
        foreach ($files['name'] as $key => $name) {
            if ($files['error'][$key] === UPLOAD_ERR_OK) {
                $tmp_name = $files['tmp_name'][$key];
                $file_extension = pathinfo($name, PATHINFO_EXTENSION);
                $file_name = uniqid() . '_' . $request_id . '.' . $file_extension;
                $file_path = $nexora_dir . $file_name;
                
                if (move_uploaded_file($tmp_name, $file_path)) {
                    $wpdb->insert($attachments_table, array(
                        'request_id' => $request_id,
                        'file_name' => $file_name,
                        'original_name' => $name,
                        'file_path' => $file_path,
                        'file_size' => $files['size'][$key],
                        'file_type' => $files['type'][$key],
                        'created_at' => current_time('mysql')
                    ));
                }
            }
        }
    }
    

    
    public function ajax_get_device_types() {
        global $wpdb;
        
        $device_types = $wpdb->get_results("SELECT id, name FROM {$wpdb->prefix}nexora_devices WHERE type = 'type' ORDER BY name");
        
        if ($device_types) {
            wp_send_json_success($device_types);
        } else {
            wp_send_json_error('No device types found');
        }
    }
    
    public function ajax_get_device_brands() {
        global $wpdb;
        
        $type_id = intval($_POST['type_id']);
        $brands = $wpdb->get_results($wpdb->prepare(
            "SELECT id, name FROM {$wpdb->prefix}nexora_devices WHERE type = 'brand' AND parent_id = %d ORDER BY name",
            $type_id
        ));
        
        if ($brands) {
            wp_send_json_success($brands);
        } else {
            wp_send_json_error('No brands found');
        }
    }
    
    public function ajax_get_device_series() {
        global $wpdb;
        
        $brand_id = intval($_POST['brand_id']);
        $series = $wpdb->get_results($wpdb->prepare(
            "SELECT id, name FROM {$wpdb->prefix}nexora_devices WHERE type = 'series' AND parent_id = %d ORDER BY name",
            $brand_id
        ));
        
        if ($series) {
            wp_send_json_success($series);
        } else {
            wp_send_json_error('No series found');
        }
    }
    
    public function ajax_get_device_models() {
        global $wpdb;
        
        $series_id = intval($_POST['series_id']);
        $models = $wpdb->get_results($wpdb->prepare(
            "SELECT id, name FROM {$wpdb->prefix}nexora_devices WHERE type = 'model' AND parent_id = %d ORDER BY name",
            $series_id
        ));
        
        if ($models) {
            wp_send_json_success($models);
        } else {
            wp_send_json_error('No models found');
        }
    }
}

new Nexora_Easy_Registration();
