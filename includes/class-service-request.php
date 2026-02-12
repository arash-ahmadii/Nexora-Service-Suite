<?php
require_once __DIR__ . '/service-request/ServiceRequest_AJAX.php';

class Nexora_Service_Request {
    use ServiceRequest_AJAX;

        private $table_name;
        private $kindCurrentUser;
        private $idCurrentUser;
        private $userCanEditRequestService;
        private $userCanCreateRequestService;
        private $userCanCreateOrder;

    
        public function __construct() {
            global $wpdb;
            $this->table_name = $wpdb->prefix . 'nexora_service_requests';
            add_action('init', [$this, 'setKindCurrentUser'],10);
            add_action('init', [$this, 'setup'],11);
        }

        public function setKindCurrentUser()
        {
            $this->kindCurrentUser=get_user_meta(get_current_user_id(), 'nexora_kind_user', true);
            $this->idCurrentUser=get_current_user_id();
            $this->userCanCreateRequestService = get_user_meta(get_current_user_id(), 'nexora_user_can_create_request_service', true);
            $this->userCanEditRequestService = get_user_meta(get_current_user_id(), 'nexora_user_can_edit_request_service', true);
            $this->userCanCreateOrder = get_user_meta(get_current_user_id(), 'nexora_user_can_create_factor', true);

        }

    
        public function setup() {
            add_action('wp_ajax_nexora_upload_attachment', array($this, 'ajax_upload_attachment'));
            add_action('wp_ajax_nexora_get_attachments', array($this, 'ajax_get_attachments'));
            add_action('wp_ajax_nexora_delete_attachment', array($this, 'ajax_delete_attachment'));
            add_action('wp_ajax_nexora_download_attachment', array($this, 'ajax_download_attachment'));
            add_action('wp_ajax_nexora_upload_invoice', array($this, 'ajax_upload_invoice'));
            add_action('wp_ajax_nexora_get_invoices', array($this, 'ajax_get_invoices'));
            add_action('wp_ajax_nexora_delete_invoice', array($this, 'ajax_delete_invoice'));
            add_action('wp_ajax_nexora_download_invoice', array($this, 'ajax_download_invoice'));
            add_action('wp_ajax_nexora_get_service_requests', array($this, 'ajax_get_requests'));
            add_action('wp_ajax_nexora_get_form_options', array($this, 'ajax_get_form_options'));
            add_action('wp_ajax_nexora_add_service_request', array($this, 'ajax_add_request'));
            add_action('wp_ajax_nexora_update_service_request', array($this, 'ajax_update_request'));
            add_action('wp_ajax_nexora_delete_request', array($this, 'ajax_delete_request'));
            add_action('wp_ajax_nexora_delete_service_request', array($this, 'ajax_delete_service_request'));
            add_action('wp_ajax_nexora_update_request_status_only', array($this, 'ajax_update_status_only'));
            add_action('wp_ajax_nexora_get_request_services', array($this, 'ajax_get_request_services'));
            add_action('wp_ajax_nexora_get_current_status', array($this, 'ajax_get_current_status'));
            add_action('wp_ajax_nexora_bulk_delete_service_requests', array($this, 'ajax_bulk_delete_requests'));
            add_action('wp_ajax_nexora_test_db_requests', array($this, 'ajax_test_db_requests'));
            add_action('wp_ajax_nexora_create_service_request_from_admin', array($this, 'ajax_create_service_request_from_admin'));
            add_action('wp_ajax_nexora_get_users', array($this, 'ajax_get_users'));
        }
        
        public function ajax_test_db_requests() {
            $this->verify_nonce();
            global $wpdb;
            $count = $wpdb->get_var("SELECT COUNT(*) FROM {$this->table_name}");
            $sample = $wpdb->get_row("SELECT * FROM {$this->table_name} LIMIT 1");
            
            wp_send_json_success([
                'count' => $count,
                'sample' => $sample,
                'table_name' => $this->table_name
            ]);
        }
        
        
        public function ajax_create_service_request_from_admin() {
            error_log('=== AJAX CREATE SERVICE REQUEST FROM ADMIN ===');
            error_log('POST data: ' . print_r($_POST, true));
            $nonce_valid = false;
            if (isset($_POST['nonce'])) {
                if (wp_verify_nonce($_POST['nonce'], 'nexora_nonce')) {
                    $nonce_valid = true;
                } elseif (wp_verify_nonce($_POST['nonce'], 'nexora_user_nonce')) {
                    $nonce_valid = true;
                }
            }
            
            if (!$nonce_valid) {
                error_log('Nonce verification failed');
                error_log('POST nonce: ' . ($_POST['nonce'] ?? 'not set'));
                wp_send_json_error('Nonce verification failed');
                return;
            }
            
            error_log('Nonce verification passed');
            
            global $wpdb;
            $customer_name = sanitize_text_field($_POST['customer_name'] ?? '');
            $customer_email = sanitize_email($_POST['customer_email'] ?? '');
            $customer_phone = sanitize_text_field($_POST['customer_phone'] ?? '');
            $customer_number = sanitize_text_field($_POST['customer_number'] ?? '');
            $salutation = sanitize_text_field($_POST['salutation'] ?? 'Herr');
            $customer_type = sanitize_text_field($_POST['customer_type'] ?? 'private');
            $company_name = sanitize_text_field($_POST['company_name'] ?? '');
            $street = sanitize_text_field($_POST['street'] ?? '');
            $postal_code = sanitize_text_field($_POST['postal_code'] ?? '');
            $city = sanitize_text_field($_POST['city'] ?? '');
            $country = sanitize_text_field($_POST['country'] ?? 'DE');
            $vat_id = sanitize_text_field($_POST['vat_id'] ?? '');
            $device_type_id = intval($_POST['device_type_id'] ?? 0);
            $device_brand_id = intval($_POST['device_brand_id'] ?? 0);
            $device_series_id = intval($_POST['device_series_id'] ?? 0);
            $device_model_id = intval($_POST['device_model_id'] ?? 0);
            $device_serial = sanitize_text_field($_POST['device_serial'] ?? '');
            $device_description = sanitize_textarea_field($_POST['device_description'] ?? '');
            $service_id = 0;
            $priority = 'medium';
            $assigned_to = 0;
            $estimated_completion = '';
            if (empty($device_type_id) || empty($device_brand_id)) {
                wp_send_json_error('Required fields are missing');
                return;
            }
            $user = get_user_by('email', $customer_email);
            if (!$user) {
                $username = sanitize_user($customer_email);
                $user_id = wp_create_user($username, wp_generate_password(), $customer_email);
                
                if (is_wp_error($user_id)) {
                    wp_send_json_error('Failed to create user: ' . $user_id->get_error_message());
                    return;
                }
                wp_update_user([
                    'ID' => $user_id,
                    'display_name' => $customer_name,
                    'first_name' => $customer_name
                ]);
            } else {
                $user_id = $user->ID;
            }
            $device_type = $wpdb->get_var($wpdb->prepare("SELECT name FROM {$wpdb->prefix}nexora_devices WHERE id = %d", $device_type_id));
            $device_brand = $wpdb->get_var($wpdb->prepare("SELECT name FROM {$wpdb->prefix}nexora_devices WHERE id = %d", $device_brand_id));
            $device_series = $device_series_id ? $wpdb->get_var($wpdb->prepare("SELECT name FROM {$wpdb->prefix}nexora_devices WHERE id = %d", $device_series_id)) : '';
            $device_model = $device_model_id ? $wpdb->get_var($wpdb->prepare("SELECT name FROM {$wpdb->prefix}nexora_devices WHERE id = %d", $device_model_id)) : '';
            $service = (object) [
                'id' => 0,
                'title' => 'Standard Service',
                'cost' => 0
            ];
            $default_status = $wpdb->get_var("SELECT id FROM {$wpdb->prefix}nexora_service_status WHERE is_default = 1 LIMIT 1");
            if (!$default_status) {
                $default_status = 1;
            }
            $request_data = [
                'user_id' => $user_id,
                'service_id' => $service_id,
                'brand_level_1_id' => $device_type_id,
                'brand_level_2_id' => $device_brand_id,
                'brand_level_3_id' => $device_series_id,
                'serial' => $device_serial,
                'model' => $device_model ? $device_model : '',
                'description' => $device_description,
                'status_id' => $default_status,
                'created_at' => current_time('mysql'),
                'updated_at' => current_time('mysql')
            ];
            
            error_log('Request data: ' . print_r($request_data, true));
            error_log('Table name: ' . $this->table_name);
            
            $result = $wpdb->insert($this->table_name, $request_data);
            
            if ($result === false) {
                error_log('Insert failed: ' . $wpdb->last_error);
                wp_send_json_error('Failed to create service request: ' . $wpdb->last_error);
                return;
            } else {
                error_log('Insert successful, ID: ' . $wpdb->insert_id);
            }
            
            $request_id = $wpdb->insert_id;
            $customer_table = $wpdb->prefix . 'nexora_customer_info';
            $customer_data = [
                'user_id' => $user_id,
                'phone' => $customer_phone,
                'customer_type' => $customer_type,
                'customer_number' => $customer_number,
                'company_name' => $company_name,
                'street' => $street,
                'postal_code' => $postal_code,
                'city' => $city,
                'country' => $country,
                'vat_id' => $vat_id,
                'salutation' => $salutation,
                'created_at' => current_time('mysql'),
                'updated_at' => current_time('mysql')
            ];
            
            error_log('Customer data: ' . print_r($customer_data, true));
            $existing_customer = $wpdb->get_row($wpdb->prepare("SELECT id FROM $customer_table WHERE user_id = %d", $user_id));
            
            if ($existing_customer) {
                $customer_result = $wpdb->update($customer_table, $customer_data, ['user_id' => $user_id]);
                error_log('Customer update result: ' . $customer_result);
            } else {
                $customer_result = $wpdb->insert($customer_table, $customer_data);
                error_log('Customer insert result: ' . $customer_result);
            }
            
            if ($customer_result === false) {
                error_log('Customer table operation failed: ' . $wpdb->last_error);
            }
            $complete_table = $wpdb->prefix . 'nexora_complete_service_requests';
            $complete_data = [
                'request_id' => $request_id,
                'user_id' => $user_id,
                'customer_name' => $customer_name,
                'customer_email' => $customer_email,
                'customer_phone' => $customer_phone,
                'customer_type' => $customer_type,
                'company_name' => $company_name,
                'street' => $street,
                'postal_code' => $postal_code,
                'city' => $city,
                'country' => $country,
                'vat_id' => $vat_id,
                'device_id' => $device_model_id,
                'device_type' => $device_type,
                'device_brand' => $device_brand,
                'device_series' => $device_series,
                'device_model' => $device_model,
                'device_serial' => $device_serial,
                'device_description' => $device_description,
                'status_id' => $default_status,
                'created_at' => current_time('mysql'),
                'updated_at' => current_time('mysql')
            ];
            
            error_log('Complete data: ' . print_r($complete_data, true));
            $complete_result = $wpdb->insert($complete_table, $complete_data);
            
            if ($complete_result === false) {
                error_log('Complete table insert failed: ' . $wpdb->last_error);
            } else {
                error_log('Complete table insert successful');
            }
            
            wp_send_json_success([
                'request_id' => $request_id,
                'message' => 'Service request created successfully'
            ]);
        }
        
        
        public function ajax_get_users() {
            $this->verify_nonce();
            
            $users = get_users([
                'role__in' => ['administrator', 'editor'],
                'fields' => ['ID', 'display_name', 'user_email']
            ]);
            
            wp_send_json_success($users);
        }

        public function nexora_get_brand_children() {
        if (isset($_POST['nonce'])) {
            if (!wp_verify_nonce($_POST['nonce'], 'nexora_nonce') && !wp_verify_nonce($_POST['nonce'], 'nexora_user_nonce')) {
                wp_send_json_error('Nonce verification failed', 403);
            }
        }

        $parent_id = intval($_POST['parent_id'] ?? 0);

        global $wpdb;
        $table = $wpdb->prefix . 'nexora_brands';

        $children = $wpdb->get_results(
            $wpdb->prepare("SELECT id, name FROM $table WHERE parent_id = %d", $parent_id),
            ARRAY_A
        );

        wp_send_json_success($children);
    }
    
        private function verify_nonce() {
            if (!isset($_POST['nonce']) || !wp_verify_nonce($_POST['nonce'], 'nexora_nonce')) {
                wp_send_json_error('Nonce-Verifikation fehlgeschlagen', 403);
            }
            if (!is_user_logged_in()) {
                wp_send_json_error('Unbefugter Zugriff', 403);
            }
        }

        public function ajax_save_invoice()
        {

             if(!($this->kindCurrentUser == 'admin' || $this->userCanCreateOrder == 'yes'))
            {
                    wp_send_json_error('Sie haben keinen Zugriff auf diesen Bereich');
            }

                global $wpdb;
                $request_id = intval($_POST['request_id']);
                $items = isset($_POST['items']) ? $_POST['items'] : [];

                if(empty($items) || !$request_id) {
                    wp_send_json_error('Unvollständige Informationen übermittelt.');
                }

                 $user_id=$this->getUserId($request_id);
                 $discountPercent=get_user_meta($user_id, 'nexora_discount_percent', true);

                $total_price = 0;
                $total_discount = 0;
                $final_price = 0;
                $order = wc_create_order();
                foreach ($items as $item) {

                    $price = isset($item['price']) ? floatval($item['price']) : 0;
                    $discount = isset($item['price']) ? floatval($item['price'] * $discountPercent/100) : 0;
                    $final = $price - $discount;

                    $total_price += $price;
                    $total_discount += $discount;
                    $final_price += $final;
                $order->add_product($this->get_dummy_product($this->getServiceTitle($item['service_id']),$price, $discount), 1, [
                    'subtotal' =>   $price,
                    'total' => $final,
                ]);
                     }

        $user_id=$this->getUserId($request_id);
        $user_data = get_userdata($user_id);

        $order->set_total($final_price);
        $order->set_customer_id($user_id);
        $order->set_address([
            'first_name' => $user_data->display_name,
            'email' =>  $user_data->user_email,
            'address_1' => '—',
            'city' => '—',
            'country' => 'IR',
        ], 'billing');

        $order->update_status('pending');
        $order->save();
        foreach ($order->get_items() as $item) {
        $product_id = $item->get_product_id();
        if ($product_id) {
            wp_delete_post($product_id, true);
        }
    }
        $this->updateRequest($order->id,$request_id);

   wp_send_json_success([
            'message' => 'Rechnung erfolgreich gespeichert!',
            'order_id' => $order->get_id(),
            'order_edit_url' => admin_url('admin.php?page=wc-orders'),
            'orders_list_url' => admin_url('admin.php?page=wc-orders')
        ]);
    
 }

    
        public function ajax_add_request() {
            $this->verify_nonce();
            global $wpdb;
            error_log('AJAX Add Request - POST Data: ' . print_r($_POST, true));
            if (isset($_POST['services'])) {
                error_log('Services array found: ' . print_r($_POST['services'], true));
            } else {
                error_log('No services array found in POST data');
            }
            
            if(!($this->kindCurrentUser == 'admin' || $this->userCanEditRequestService == 'yes'))
        {
        wp_send_json_error('Sie haben keinen Zugriff auf diesen Bereich');
        }
          else{
            $data = array(
                'serial' => sanitize_text_field($_POST['serial']),
                'model' => sanitize_text_field($_POST['model']),
                'description' => sanitize_textarea_field($_POST['description']),
                'service_id' => null,
                'service_quantity' => 1,
                'service_description' => '',
                'user_id' => intval($_POST['user_id']),
                'status_id' => intval($_POST['status_id']),
                'brand_level_1_id' => intval($_POST['brand_level_1_id']),
                'brand_level_2_id' => intval($_POST['brand_level_2_id']),
                'brand_level_3_id' => intval($_POST['brand_level_3_id'])
            );

    
            $format = ['%s','%s','%s','%d','%d','%s','%d','%d','%d','%d'];
            $result = $wpdb->insert($this->table_name, $data, $format);

            if ($result) {
                $request_id = $wpdb->insert_id;
                if (isset($_POST['additional_services']) && is_array($_POST['additional_services'])) {
                    error_log('=== MEHR SERVICE ADD DEBUG ===');
                    error_log('Request ID: ' . $request_id);
                    error_log('Additional services array received: ' . print_r($_POST['additional_services'], true));
                    
                    $inserted_count = 0;
                    foreach ($_POST['additional_services'] as $service) {
                        error_log('Processing Mehr Service: ' . print_r($service, true));
                        
                        if (!empty($service['id']) && !empty($service['title'])) {
                            $insert_data = [
                                'request_id' => $request_id,
                                'service_id' => intval($service['id']),
                                'service_title' => sanitize_text_field($service['title']),
                                'quantity' => intval($service['qty'] ?: 1),
                                'note' => sanitize_text_field($service['note'] ?: '')
                            ];
                            
                            error_log('Inserting Mehr Service data: ' . print_r($insert_data, true));
                            
                            $insert_result = $wpdb->insert(
                                $wpdb->prefix . 'nexora_service_details',
                                $insert_data,
                                ['%d', '%d', '%s', '%d', '%s']
                            );
                            
                            if ($insert_result !== false) {
                                $inserted_count++;
                                error_log('Mehr Service inserted successfully. Insert ID: ' . $wpdb->insert_id);
                                error_log('=== EXECUTING SERVICE ADDED HOOK IN CLASS-SERVICE-REQUEST.PHP ===');
                                error_log('Request ID: ' . $request_id);
                                error_log('Service ID: ' . intval($service['id']));
                                error_log('Quantity: ' . intval($service['qty'] ?: 1));
                                error_log('About to call do_action...');
                                
                                do_action('nexora_service_added', $request_id, intval($service['id']), intval($service['qty'] ?: 1));
                                
                                error_log('✅ do_action nexora_service_added completed in class-service-request.php');
                                error_log('=== SERVICE ADDED HOOK EXECUTION COMPLETED IN CLASS-SERVICE-REQUEST.PHP ===');
                            } else {
                                error_log('Failed to insert Mehr Service. Error: ' . $wpdb->last_error);
                            }
                        } else {
                            error_log('Skipping Mehr Service with empty ID or title');
                        }
                    }
                    
                    error_log('Total Mehr Services inserted: ' . $inserted_count);
                    error_log('=== END MEHR SERVICE ADD DEBUG ===');
                } else {
                    error_log('No additional_services array found in POST data for new request');
                }
                if (class_exists('Nexora_Activity_Logger')) {
                    $logger = new Nexora_Activity_Logger();
                    $logger->log_request_created($request_id, $data);
                }
                if (class_exists('Nexora_Admin_Notifications')) {
                    Nexora_Admin_Notifications::notify_new_service_request($request_id, $data['user_id'], $data['serial'], $data['model']);
                }
                error_log('=== HOOK EXECUTION DEBUG ===');
                error_log('About to fire nexora_service_request_created hook');
                error_log('Request ID: ' . $request_id);
                error_log('User ID: ' . $data['user_id']);
                error_log('Hook exists: ' . (has_action('nexora_service_request_created') ? 'YES' : 'NO'));
                
                do_action('nexora_service_request_created', $request_id, $data['user_id']);
                
                error_log('✅ nexora_service_request_created hook fired successfully');
                error_log('=== HOOK EXECUTION DEBUG COMPLETED ===');
                
                wp_send_json_success(['message' => 'Anfrage erfolgreich eingereicht.', 'id' => $request_id]);
            } else {
                wp_send_json_error('Fehler beim Speichern der Anfrage.');
            }
        }
        }
    
        public function ajax_update_request() {
            $this->verify_nonce();
            global $wpdb;

            $id = intval($_POST['id']);
            error_log('AJAX Update Request - ID: ' . $id);
            error_log('POST Data: ' . print_r($_POST, true));
            if (isset($_POST['additional_services'])) {
                error_log('Additional services array found: ' . print_r($_POST['additional_services'], true));
            } else {
                error_log('No additional_services array found in POST data');
            }
            $old_data = $wpdb->get_row($wpdb->prepare("SELECT * FROM {$this->table_name} WHERE id = %d", $id), ARRAY_A);

            $this->userCanEditStatus( $id,$_POST['status_id']);

              if($this->kindCurrentUser == 'admin')
              {
                $new_status_id = (isset($_POST['status_id']) && $_POST['status_id'] !== '')
                  ? intval($_POST['status_id'])
                  : (isset($old_data['status_id']) ? intval($old_data['status_id']) : null);

                $data = array(
                    'serial' => sanitize_text_field($_POST['serial']),
                    'model' => sanitize_text_field($_POST['model']),
                    'description' => sanitize_textarea_field($_POST['description']),
                    'service_id' => null,
                    'service_quantity' => 1,
                    'service_description' => '',
                    'user_id' => intval($_POST['user_id']),
                    'status_id' => $new_status_id,
                    'brand_level_1_id' => !empty($_POST['brand_level_1_id']) ? intval($_POST['brand_level_1_id']) : null,
                    'brand_level_2_id' => !empty($_POST['brand_level_2_id']) ? intval($_POST['brand_level_2_id']) : null,
                    'brand_level_3_id' => !empty($_POST['brand_level_3_id']) ? intval($_POST['brand_level_3_id']) : null,
                    'manual_customer_name' => sanitize_text_field($_POST['manual_customer_name'] ?? ''),
                    'manual_customer_lastname' => sanitize_text_field($_POST['manual_customer_lastname'] ?? ''),
                    'manual_customer_phone' => sanitize_text_field($_POST['manual_customer_phone'] ?? ''),
                    'updated_at' => current_time('mysql')
                );
                $format = ['%s','%s','%s','%d','%d','%s','%d','%d','%d','%d','%s','%s','%s','%s','%s'];

              }
              else
              {
            $data = array(
                'status_id' => intval($_POST['status_id']),
                     'updated_at' => current_time('mysql')
            );
    
                 $format = ['%d', '%s'];
              }
           
            $where = ['id' => $id];
    
            $result = $wpdb->update($this->table_name, $data, $where, $format, ['%d']);
            error_log('Database Update Result: ' . ($result !== false ? 'success' : 'failed'));
            if ($result === false) {
                error_log('Database Error: ' . $wpdb->last_error);
            }
    
            if ($result !== false) {
                if (isset($_POST['additional_services']) && is_array($_POST['additional_services'])) {
                    error_log('=== MEHR SERVICE UPDATE DEBUG ===');
                    error_log('Request ID: ' . $id);
                    error_log('Additional services array received: ' . print_r($_POST['additional_services'], true));
                    $existing_services = $wpdb->get_results($wpdb->prepare(
                        "SELECT service_id, quantity FROM {$wpdb->prefix}nexora_service_details WHERE request_id = %d",
                        $id
                    ));
                    $existing_services_map = [];
                    foreach ($existing_services as $existing_service) {
                        $existing_services_map[$existing_service->service_id] = $existing_service->quantity;
                    }
                    $delete_result = $wpdb->delete(
                        $wpdb->prefix . 'nexora_service_details',
                        ['request_id' => $id],
                        ['%d']
                    );
                    error_log('Delete existing Mehr Services result: ' . ($delete_result !== false ? 'success' : 'failed'));
                    if ($delete_result !== false && $existing_services) {
                        foreach ($existing_services as $existing_service) {
                            do_action('nexora_service_removed', $id, $existing_service->service_id, $existing_service->quantity);
                        }
                    }
                    $inserted_count = 0;
                    foreach ($_POST['additional_services'] as $service) {
                        error_log('Processing Mehr Service: ' . print_r($service, true));
                        
                        if (!empty($service['id'])) {
                            $service_title = '';
                            if (!empty($service['title'])) {
                                $service_title = sanitize_text_field($service['title']);
                            } else {
                                $service_title = $wpdb->get_var($wpdb->prepare(
                                    "SELECT title FROM {$wpdb->prefix}nexora_services WHERE id = %d",
                                    intval($service['id'])
                                ));
                            }
                            
                            $insert_data = [
                                'request_id' => $id,
                                'service_id' => intval($service['id']),
                                'service_title' => $service_title,
                                'quantity' => intval($service['qty'] ?: 1),
                                'note' => sanitize_text_field($service['note'] ?: '')
                            ];
                            
                            error_log('Inserting Mehr Service data: ' . print_r($insert_data, true));
                            
                            $insert_result = $wpdb->insert(
                                $wpdb->prefix . 'nexora_service_details',
                                $insert_data,
                                ['%d', '%d', '%s', '%d', '%s']
                            );
                            
                            if ($insert_result !== false) {
                                $inserted_count++;
                                error_log('Mehr Service inserted successfully. Insert ID: ' . $wpdb->insert_id);
                                error_log('=== EXECUTING SERVICE ADDED HOOK IN AJAX_UPDATE_REQUEST ===');
                                error_log('Request ID: ' . $id);
                                error_log('Service ID: ' . intval($service['id']));
                                error_log('Quantity: ' . intval($service['qty'] ?: 1));
                                error_log('About to call do_action...');
                                
                                do_action('nexora_service_added', $id, intval($service['id']), intval($service['qty'] ?: 1));
                                
                                error_log('✅ do_action nexora_service_added completed in ajax_update_request');
                                error_log('=== SERVICE ADDED HOOK EXECUTION COMPLETED IN AJAX_UPDATE_REQUEST ===');
                                $new_quantity = intval($service['qty'] ?: 1);
                                if (isset($existing_services_map[intval($service['id'])]) && $existing_services_map[intval($service['id'])] != $new_quantity) {
                                    $old_quantity = $existing_services_map[intval($service['id'])];
                                    do_action('nexora_service_quantity_changed', $id, intval($service['id']), $old_quantity, $new_quantity);
                                }
                            } else {
                                error_log('Failed to insert Mehr Service. Error: ' . $wpdb->last_error);
                            }
                        } else {
                            error_log('Skipping Mehr Service with empty ID');
                        }
                    }
                    
                    error_log('Total Mehr Services inserted: ' . $inserted_count);
                    error_log('=== END MEHR SERVICE UPDATE DEBUG ===');
                } else {
                    error_log('No additional_services array found in POST data');
                }
                error_log('=== STATUS CHANGE DEBUG ===');
                error_log('Request ID: ' . $id);
                error_log('Old status_id: ' . (isset($old_data['status_id']) ? $old_data['status_id'] : 'NOT SET'));
                error_log('New status_id: ' . (isset($data['status_id']) ? $data['status_id'] : 'NOT SET'));
                error_log('Status changed: ' . (isset($old_data['status_id']) && isset($data['status_id']) && $old_data['status_id'] != $data['status_id'] ? 'YES' : 'NO'));
                if (class_exists('Nexora_Activity_Logger')) {
                    $logger = new Nexora_Activity_Logger();
                    if (isset($old_data['status_id']) && isset($data['status_id']) && $old_data['status_id'] != $data['status_id']) {
                        error_log('Status change detected - triggering hook and logging');
                        $logger->log_status_change($id, $old_data['status_id'], $data['status_id']);
                        do_action('nexora_service_status_changed', $id, $old_data['status_id'], $data['status_id']);
                        error_log('Status change hook triggered: nexora_service_status_changed(' . $id . ', ' . $old_data['status_id'] . ', ' . $data['status_id'] . ')');
                    } else {
                        error_log('No status change detected - old: ' . (isset($old_data['status_id']) ? $old_data['status_id'] : 'NOT SET') . ', new: ' . (isset($data['status_id']) ? $data['status_id'] : 'NOT SET'));
                    }
                    if (count($data) > 1 || !isset($data['status_id']) || $old_data['status_id'] == $data['status_id']) {
                        $logger->log_request_updated($id, $old_data, $data);
                    }
                }
                
                error_log('=== END STATUS CHANGE DEBUG ===');
                
                            wp_send_json_success('Anfrage erfolgreich aktualisiert.');
        } else {
                wp_send_json_error('Fehler beim Aktualisieren der Anfrage. Database Error: ' . $wpdb->last_error);
        }
        }
    
        public function ajax_delete_request() {
            $this->verify_nonce();
            global $wpdb;
            $request_id = intval($_POST['id']);
            $request_data = $wpdb->get_row($wpdb->prepare("SELECT * FROM {$this->table_name} WHERE id = %d", $request_id), ARRAY_A);

            $result = $wpdb->delete($this->table_name, ['id' => $request_id, 'order_id' => null], ['%d']);
            if ($result) {
                if (class_exists('Nexora_Activity_Logger') && $request_data) {
                    $logger = new Nexora_Activity_Logger();
                    $logger->log_request_deleted($request_id, $request_data);
                }
                
                            wp_send_json_success('Service-Anfrage erfolgreich gelöscht.');
        } else {
            wp_send_json_error('Fehler beim Löschen der Service-Anfrage.');
        }
            
        }

        public function ajax_delete_service_request() {
            $this->verify_nonce();
            global $wpdb;
            $request_id = intval($_POST['request_id']);
            $request_data = $wpdb->get_row($wpdb->prepare("SELECT * FROM {$this->table_name} WHERE id = %d", $request_id), ARRAY_A);

            $result = $wpdb->delete($this->table_name, ['id' => $request_id, 'order_id' => null], ['%d']);
            if ($result) {
                if (class_exists('Nexora_Activity_Logger') && $request_data) {
                    $logger = new Nexora_Activity_Logger();
                    $logger->log_request_deleted($request_id, $request_data);
                }
                
                wp_send_json_success('Service-Anfrage erfolgreich gelöscht.');
            } else {
                wp_send_json_error('Fehler beim Löschen der Service-Anfrage.');
            }
        }
        
        public function ajax_bulk_delete_requests() {
            $this->verify_nonce();
            
            global $wpdb;
            
            if (!isset($_POST['ids']) || !is_array($_POST['ids'])) {
                wp_send_json_error('Keine gültigen Anfrage-IDs übermittelt.');
            }
            
            $request_ids = array_map('intval', $_POST['ids']);
            $deleted_count = 0;
            
            foreach ($request_ids as $request_id) {
                $request_data = $wpdb->get_row($wpdb->prepare("SELECT * FROM {$this->table_name} WHERE id = %d", $request_id), ARRAY_A);
                
                $result = $wpdb->delete(
                    $this->table_name,
                    array('id' => $request_id, 'order_id' => null),
                    array('%d')
                );
                
                if ($result) {
                    $deleted_count++;
                    if (class_exists('Nexora_Activity_Logger') && $request_data) {
                        $logger = new Nexora_Activity_Logger();
                        $logger->log_request_deleted($request_id, $request_data);
                    }
                }
            }
            
            if ($deleted_count > 0) {
                wp_send_json_success("{$deleted_count} Anfrage(n) wurden erfolgreich gelöscht.");
            } else {
                wp_send_json_error('Fehler beim Löschen der Anfragen.');
            }
        }
    
        public function ajax_get_requests() {
            $this->verify_nonce();
            global $wpdb;
    
            $page = isset($_POST['page']) ? intval($_POST['page']) : 1;
            $per_page = isset($_POST['per_page']) ? intval($_POST['per_page']) : 10;
            $offset = ($page - 1) * $per_page;
            $search = isset($_POST['search']) ? sanitize_text_field($_POST['search']) : '';
            $status_filter = isset($_POST['status_filter']) ? sanitize_text_field($_POST['status_filter']) : '';
            $order_by = isset($_POST['order_by']) ? sanitize_text_field($_POST['order_by']) : 'id';
            $order_dir = (isset($_POST['order_dir']) && strtolower($_POST['order_dir']) === 'asc') ? 'ASC' : 'DESC';
            $allowed_order_by = [
                'id' => 'r.id',
                'model' => 'r.model',
                'display_name' => 'u.display_name',
                'status_id' => 'r.status_id',
                'device_type_display' => 'device_type_display',
                'cost' => 'cost',
                'created_at' => 'r.created_at'
            ];
            $order_by_sql = isset($allowed_order_by[$order_by]) ? $allowed_order_by[$order_by] : 'r.id';
            $query = "SELECT r.*, 
                        u.display_name,
                        ss.title as status_title,
                        ss.color as status_color,
                        b1.name as brand_1_name,
                        b2.name as brand_2_name,
                        b3.name as brand_3_name,
                        -- Calculate total cost - will be calculated in PHP for better compatibility
                        0 as cost,
                        -- Get actual device information from devices table or custom values
                        CASE 
                            WHEN r.brand_level_1_id IS NOT NULL THEN b1.name
                            ELSE 'Custom Type'
                        END as device_type_display,
                        CASE 
                            WHEN r.brand_level_2_id IS NOT NULL THEN b2.name
                            ELSE 'Custom Brand'
                        END as device_brand_display,
                        CASE 
                            WHEN r.brand_level_3_id IS NOT NULL THEN b3.name
                            ELSE 'Custom Series'
                        END as device_series_display,
                        r.model as device_model_display,
                        r.description
                    FROM {$this->table_name} r
                    LEFT JOIN {$wpdb->prefix}users u ON r.user_id = u.ID
                    LEFT JOIN {$wpdb->prefix}nexora_service_status ss ON r.status_id = ss.id
                    LEFT JOIN {$wpdb->prefix}nexora_brands b1 ON r.brand_level_1_id = b1.id
                    LEFT JOIN {$wpdb->prefix}nexora_brands b2 ON r.brand_level_2_id = b2.id
                    LEFT JOIN {$wpdb->prefix}nexora_brands b3 ON r.brand_level_3_id = b3.id";
    
            $where = [];
            $params = [];
    
            if (!empty($search)) {
                $where[] = "(r.serial LIKE %s OR r.model LIKE %s OR r.description LIKE %s OR u.display_name LIKE %s)";
                $params[] = "%{$search}%";
                $params[] = "%{$search}%";
                $params[] = "%{$search}%";
                $params[] = "%{$search}%";
            }
            if (!empty($status_filter)) {
                $where[] = "r.status_id = %d";
                $params[] = intval($status_filter);
            }
            $where[] = "r.order_id IS NULL";
           
            if (!empty($where)) {
                $query .= " WHERE " . implode(" AND ", $where);
            }
            $count_query = "SELECT COUNT(1) FROM {$this->table_name} r
                           LEFT JOIN {$wpdb->prefix}users u ON r.user_id = u.ID";
            
            $count_where = [];
            $count_params = [];
            
            if (!empty($search)) {
                $count_where[] = "(r.serial LIKE %s OR r.model LIKE %s OR r.description LIKE %s OR u.display_name LIKE %s)";
                $count_params[] = "%{$search}%";
                $count_params[] = "%{$search}%";
                $count_params[] = "%{$search}%";
                $count_params[] = "%{$search}%";
            }
            if (!empty($status_filter)) {
                $count_where[] = "r.status_id = %d";
                $count_params[] = intval($status_filter);
            }
            $count_where[] = "r.order_id IS NULL";
            
            if (!empty($count_where)) {
                $count_query .= " WHERE " . implode(" AND ", $count_where);
            }
            
            $total = $wpdb->get_var($wpdb->prepare($count_query, $count_params));
            $total = intval($total) ?: 0;
            $query .= " ORDER BY $order_by_sql $order_dir LIMIT %d, %d";
            $params[] = $offset;
            $params[] = $per_page;
    
            $results = $wpdb->get_results($wpdb->prepare($query, $params));
            foreach ($results as &$result) {
                $cost = $this->calculate_request_total_cost($result->id);
                $result->cost = $cost;
                $result->cost_formatted = number_format($cost, 2);
                $result->device_info_formatted = $this->format_device_info($result);
            }
            error_log('Nexora Service Suite Service Requests Query: ' . $query);
            error_log('Nexora Service Suite Service Requests Params: ' . print_r($params, true));
            error_log('Nexora Service Suite Service Requests Found: ' . count($results));
            error_log('Nexora Service Suite Service Requests Total: ' . $total);
            $simple_count = $wpdb->get_var("SELECT COUNT(*) FROM {$this->table_name} WHERE order_id IS NULL");
            error_log('Nexora Service Suite Simple Count (without filters): ' . $simple_count);
            error_log('Nexora Service Suite Count Query: ' . $count_query);
            error_log('Nexora Service Suite Count Query Params: ' . print_r($count_params, true));
    
            $total_pages = ceil($total / $per_page);
            error_log('Nexora Service Suite Pagination Debug - Total: ' . $total . ', Per Page: ' . $per_page . ', Total Pages: ' . $total_pages);
            
            wp_send_json_success([
                'requests' => $results,
                'total' => $total,
                'page' => $page,
                'per_page' => $per_page,
                'total_pages' => $total_pages
            ]);
        }
        
        
        private function format_device_info($request) {
            global $wpdb;
            $device_parts = array();
            $custom_info = $this->parse_custom_device_info($request->description ?? '');
            $complete_table = $wpdb->prefix . 'nexora_complete_service_requests';
            $complete_data = $wpdb->get_row($wpdb->prepare(
                "SELECT device_type, device_brand, device_model, device_serial, device_description FROM $complete_table WHERE request_id = %d",
                $request->id
            ), ARRAY_A);
            if (!empty($request->device_type_display) && $request->device_type_display !== 'Custom Type') {
                $device_parts[] = $request->device_type_display;
            } elseif (!empty($complete_data['device_type'])) {
                $device_type_name = $wpdb->get_var($wpdb->prepare(
                    "SELECT name FROM {$wpdb->prefix}nexora_brands WHERE id = %d", 
                    $complete_data['device_type']
                ));
                if ($device_type_name) {
                    $device_parts[] = $device_type_name;
                }
            } elseif (!empty($custom_info['device_type_custom'])) {
                $device_parts[] = $custom_info['device_type_custom'];
            }
            if (!empty($request->device_brand_display) && $request->device_brand_display !== 'Custom Brand') {
                $device_parts[] = $request->device_brand_display;
            } elseif (!empty($complete_data['device_brand'])) {
                $brand_name = $wpdb->get_var($wpdb->prepare(
                    "SELECT name FROM {$wpdb->prefix}nexora_brands WHERE id = %d", 
                    $complete_data['device_brand']
                ));
                if ($brand_name) {
                    $device_parts[] = $brand_name;
                }
            } elseif (!empty($custom_info['device_brand_custom'])) {
                $device_parts[] = $custom_info['device_brand_custom'];
            }
            if (!empty($request->device_series_display) && $request->device_series_display !== 'Custom Series') {
                $device_parts[] = $request->device_series_display;
            } elseif (!empty($custom_info['device_series_custom'])) {
                $device_parts[] = $custom_info['device_series_custom'];
            }
            if (!empty($complete_data['device_model'])) {
                $device_parts[] = $complete_data['device_model'];
            } elseif (!empty($request->device_model_display)) {
                $device_parts[] = $request->device_model_display;
            } elseif (!empty($custom_info['device_model_custom'])) {
                $device_parts[] = $custom_info['device_model_custom'];
            }
            
            return !empty($device_parts) ? implode(' | ', $device_parts) : '-';
        }
        
        
        private function parse_custom_device_info($description) {
            $custom_info = [
                'device_type_custom' => '',
                'device_brand_custom' => '',
                'device_series_custom' => '',
                'device_model_custom' => '',
                'description_clean' => $description
            ];
            
            if (empty($description)) {
                return $custom_info;
            }
            $lines = explode("\n", $description);
            
            foreach ($lines as $line) {
                $line = trim($line);
                if (empty($line)) continue;
                if (strpos($line, '|') !== false) {
                    $parts = explode('|', $line);
                    foreach ($parts as $part) {
                        $part = trim($part);
                        if (preg_match('/^(Gerätetyp|Marke|Serie|Modell):\s*(.+)$/', $part, $matches)) {
                            $field = strtolower($matches[1]);
                            $value = trim($matches[2]);
                            
                            switch ($field) {
                                case 'gerätetyp':
                                    $custom_info['device_type_custom'] = $value;
                                    break;
                                case 'marke':
                                    $custom_info['device_brand_custom'] = $value;
                                    break;
                                case 'serie':
                                    $custom_info['device_series_custom'] = $value;
                                    break;
                                case 'modell':
                                    $custom_info['device_model_custom'] = $value;
                                    break;
                            }
                        }
                    }
                }
                elseif (preg_match('/^(Gerätetyp|Marke|Serie|Modell):\s*(.+)$/', $line, $matches)) {
                    $field = strtolower($matches[1]);
                    $value = trim($matches[2]);
                    
                    switch ($field) {
                        case 'gerätetyp':
                            $custom_info['device_type_custom'] = $value;
                            break;
                        case 'marke':
                            $custom_info['device_brand_custom'] = $value;
                            break;
                        case 'serie':
                            $custom_info['device_series_custom'] = $value;
                            break;
                        case 'modell':
                            $custom_info['device_model_custom'] = $value;
                            break;
                    }
                }
            }
            
            return $custom_info;
        }
        
        
        private function calculate_request_total_cost($request_id) {
            global $wpdb;
            error_log("=== COST CALCULATION DEBUG ===");
            error_log("Request ID: {$request_id}");
            $user_id = $wpdb->get_var($wpdb->prepare(
                "SELECT user_id FROM {$wpdb->prefix}nexora_service_requests WHERE id = %d",
                $request_id
            ));
            $discount_percentage = 0;
            if ($user_id) {
                $discount_percentage = floatval(get_user_meta($user_id, 'discount_percentage', true));
                error_log("User ID: {$user_id}, Discount: {$discount_percentage}%");
            }
            $complete_table = $wpdb->prefix . 'nexora_complete_service_requests';
            $complete_data = $wpdb->get_row($wpdb->prepare(
                "SELECT services_data FROM $complete_table WHERE request_id = %d", 
                $request_id
            ), ARRAY_A);
            
            error_log("Complete table data: " . print_r($complete_data, true));
            
            if ($complete_data && $complete_data['services_data']) {
                $services = json_decode($complete_data['services_data'], true);
                error_log("Decoded services: " . print_r($services, true));
                
                if ($services && is_array($services)) {
                    $total_cost = 0;
                    foreach ($services as $service) {
                        $cost = floatval($service['service_cost'] ?? 0);
                        $quantity = floatval($service['quantity'] ?? 1);
                        $total_cost += $cost * $quantity;
                        error_log("Service: cost={$cost}, quantity={$quantity}, subtotal=" . ($cost * $quantity));
                    }
                    if ($discount_percentage > 0) {
                        $discount_amount = ($total_cost * $discount_percentage) / 100;
                        $total_cost = $total_cost - $discount_amount;
                        error_log("Applied discount: {$discount_percentage}% = {$discount_amount}€, Final cost: {$total_cost}€");
                    }
                    
                    error_log("Total cost from complete table: {$total_cost}");
                    return $total_cost;
                }
            }
            $invoice_table = $wpdb->prefix . 'nexora_invoice_services';
            $invoice_services = $wpdb->get_results($wpdb->prepare(
                "SELECT service_cost, quantity FROM $invoice_table WHERE request_id = %d",
                $request_id
            ), ARRAY_A);
            
            error_log("Invoice services data: " . print_r($invoice_services, true));
            
            if ($invoice_services) {
                $total_cost = 0;
                foreach ($invoice_services as $service) {
                    $cost = floatval($service['service_cost'] ?? 0);
                    $quantity = floatval($service['quantity'] ?? 1);
                    $total_cost += $cost * $quantity;
                }
                if ($discount_percentage > 0) {
                    $discount_amount = ($total_cost * $discount_percentage) / 100;
                    $total_cost = $total_cost - $discount_amount;
                    error_log("Applied discount to invoice services: {$discount_percentage}% = {$discount_amount}€, Final cost: {$total_cost}€");
                }
                
                error_log("Total cost from invoice table: {$total_cost}");
                return $total_cost;
            }
            
            error_log("No services found, returning 0");
            return 0;
        }
    
        public function ajax_update_status_only() {
            $this->verify_nonce();
            global $wpdb;

            $id = intval($_POST['id']);
            $status_id = intval($_POST['status_id']);
            error_log('AJAX Update Status Only - ID: ' . $id . ', Status: ' . $status_id);
            $old_data = $wpdb->get_row($wpdb->prepare("SELECT * FROM {$this->table_name} WHERE id = %d", $id), ARRAY_A);
            
            if (!$old_data) {
                wp_send_json_error('Request not found');
                return;
            }
            
            $data = array(
                'status_id' => $status_id,
                'updated_at' => current_time('mysql')
            );
            
            $where = ['id' => $id];
            $format = ['%d', '%s'];
            
            $result = $wpdb->update($this->table_name, $data, $where, $format, ['%d']);
            error_log('Database Update Result: ' . ($result !== false ? 'success' : 'failed'));
            if ($result === false) {
                error_log('Database Error: ' . $wpdb->last_error);
            }
    
            if ($result !== false) {
                if (class_exists('Nexora_Activity_Logger')) {
                    $logger = new Nexora_Activity_Logger();
                    if (isset($old_data['status_id']) && $old_data['status_id'] != $data['status_id']) {
                        $logger->log_status_change($id, $old_data['status_id'], $data['status_id']);
                        $old_status_name = $wpdb->get_var($wpdb->prepare(
                            "SELECT title FROM {$wpdb->prefix}nexora_service_status WHERE id = %d",
                            $old_data['status_id']
                        ));
                        $new_status_name = $wpdb->get_var($wpdb->prepare(
                            "SELECT title FROM {$wpdb->prefix}nexora_service_status WHERE id = %d",
                            $data['status_id']
                        ));
                        do_action('nexora_service_status_changed', $id, $old_data['status_id'], $data['status_id']);
                    }
                }
                
                wp_send_json_success('Status erfolgreich aktualisiert.');
            } else {
                wp_send_json_error('Fehler beim Aktualisieren des Status. Database Error: ' . $wpdb->last_error);
            }
        }
        
        public function ajax_get_current_status() {
            $this->verify_nonce();
            global $wpdb;

            $request_id = intval($_POST['request_id']);
            
            if (!$request_id) {
                wp_send_json_error('Invalid request ID');
                return;
            }
            $current_status = $wpdb->get_var($wpdb->prepare(
                "SELECT status_id FROM {$this->table_name} WHERE id = %d",
                $request_id
            ));
            
            if ($current_status !== null) {
                wp_send_json_success([
                    'status_id' => $current_status,
                    'request_id' => $request_id
                ]);
            } else {
                wp_send_json_error('Request not found');
            }
        }
        
        public function ajax_get_request_services() {
            $this->verify_nonce();
            global $wpdb;

            $request_id = intval($_POST['request_id']);
            
            if (!$request_id) {
                wp_send_json_error('Invalid request ID');
                return;
            }
            $services = $wpdb->get_results($wpdb->prepare(
                "SELECT sd.service_id, sd.service_title as title, sd.quantity as qty, sd.note,
                        s.cost as service_cost
                 FROM {$wpdb->prefix}nexora_service_details sd
                 LEFT JOIN {$wpdb->prefix}nexora_services s ON sd.service_id = s.id
                 WHERE sd.request_id = %d 
                 ORDER BY sd.id",
                $request_id
            ), ARRAY_A);
            if ($services) {
                foreach ($services as &$service) {
                    $service['id'] = $service['service_id'];
                    unset($service['service_id']);
                    $service['is_additional'] = true;
                    $service['cost'] = $service['service_cost'] ?: 0;
                    unset($service['service_cost']);
                }
            }
            
            if ($services) {
                wp_send_json_success(['services' => $services]);
                return;
            }
            $request = $wpdb->get_row($wpdb->prepare(
                "SELECT sr.service_id, sr.service_description, sr.service_quantity,
                        s.title as service_title, s.cost as service_cost
                 FROM {$this->table_name} sr
                 LEFT JOIN {$wpdb->prefix}nexora_services s ON sr.service_id = s.id
                 WHERE sr.id = %d",
                $request_id
            ));
            
            if ($request && $request->service_id) {
                $services = [[
                    'id' => $request->service_id,
                    'title' => $request->service_title ?: 'Service',
                    'qty' => $request->service_quantity ?: 1,
                    'note' => $request->service_description ?: '',
                    'cost' => $request->service_cost ?: 0,
                    'is_additional' => false
                ]];
                
                wp_send_json_success(['services' => $services]);
            } else {
                wp_send_json_success(['services' => []]);
            }
        }
    
        public function ajax_get_form_options() {
            $this->verify_nonce();
            global $wpdb;
            $users = $wpdb->get_results("
                SELECT 
                    u.ID as id, 
                    u.display_name,
                    u.user_email,
                    ci.phone,
                    ci.street,
                    ci.postal_code,
                    ci.city,
                    ci.country,
                    ci.company_name,
                    ci.customer_type
                FROM {$wpdb->prefix}users u
                LEFT JOIN {$wpdb->prefix}nexora_customer_info ci ON u.ID = ci.user_id
                ORDER BY u.display_name ASC
            ");
            $formatted_users = array();
            foreach ($users as $user) {
                $contact_info = array();
                
                if (!empty($user->phone)) {
                    $contact_info[] = 'Tel: ' . $user->phone;
                }
                if (!empty($user->street) || !empty($user->city)) {
                    $address_parts = array();
                    if (!empty($user->street)) $address_parts[] = $user->street;
                    if (!empty($user->postal_code)) $address_parts[] = $user->postal_code;
                    if (!empty($user->city)) $address_parts[] = $user->city;
                    if (!empty($user->country)) $address_parts[] = $user->country;
                    $contact_info[] = 'Adresse: ' . implode(', ', $address_parts);
                }
                if (!empty($user->company_name)) {
                    $contact_info[] = 'Firma: ' . $user->company_name;
                }
                
                $display_name = $user->display_name;
                if (!empty($contact_info)) {
                    $display_name .= ' (' . implode(' | ', $contact_info) . ')';
                }
                
                $formatted_users[] = array(
                    'id' => $user->id,
                    'display_name' => $display_name
                );
            }
            
            $statuses = $wpdb->get_results("SELECT id, title, color, is_default FROM {$wpdb->prefix}nexora_service_status ORDER BY title ASC");
            $brands = $wpdb->get_results("SELECT id, name FROM {$wpdb->prefix}nexora_brands  ORDER BY name ASC");
    
            wp_send_json_success([
                'users' => $formatted_users,
                'statuses' => $statuses,
                'brands' => $brands
            ]);
        }

        public function ajax_get_request_comments() {
            $this->verify_nonce();
            global $wpdb;
            $request_id = intval($_POST['request_id'] ?? 0);
            $current_user = get_current_user_id();
        
            $table = $wpdb->prefix . 'nexora_request_comments';
        
            $results = $wpdb->get_results($wpdb->prepare(
                "SELECT c.*, u.display_name, 
                        CASE WHEN c.user_id = %d THEN 1 ELSE 0 END AS is_current_user
                 FROM $table c
                 LEFT JOIN {$wpdb->prefix}users u ON c.user_id = u.ID
                 WHERE c.request_id = %d
                 ORDER BY c.created_at DESC",
                $current_user, $request_id
            ));
        
            wp_send_json_success(['comments' => $results]);
        }

        public function ajax_add_request_comment() {
            $this->verify_nonce();
            global $wpdb;

            $user_id = get_current_user_id();
            $request_id = intval($_POST['request_id'] ?? 0);
            $comment_text = sanitize_text_field($_POST['comment_text'] ?? '');
        
            if ( !$request_id || empty($comment_text)) {
                wp_send_json_error('Daten sind unvollständig.');
            }
        
            $table = $wpdb->prefix . 'nexora_request_comments';
        
            $existing = $wpdb->get_var($wpdb->prepare(
                "SELECT id FROM $table WHERE user_id = %d AND request_id = %d",
                $user_id, $request_id
            ));

         
            if ($existing) {
                $resu=$wpdb->update($table, [
                    'comment_text' => $comment_text
                ], [ 'id' => $existing ]);      
               
            } else {
                $wpdb->insert($table, [
                    'request_id' => $request_id,
                    'user_id' => $user_id,
                    'comment_text' => $comment_text
                ], [ '%d', '%d', '%s' ]);
                if (class_exists('Nexora_Activity_Logger')) {
                    $logger = new Nexora_Activity_Logger();
                    $logger->log_comment_added($request_id, $comment_text);
                }
                
                wp_send_json_success('Ihr Kommentar wurde gespeichert.');
            }

        }

        public function ajax_get_user_meta_checkbox() {

            $this->verify_nonce();

            $user_id = get_current_user_id();
            $request_id = intval($_POST['request_id'] ?? 0);

            if (!$user_id || !$request_id) {
                wp_send_json_error('Ungültige ID.');
            }

            $checkbox = get_user_meta($user_id, 'nexora_custom_checkbox', true);
      
            $custom_label = get_user_meta($user_id, 'nexora_custom_text', true);

            if ($checkbox == '1') {
                wp_send_json_success([
                    'allowed' => true,
                    'label' => $custom_label ?: 'Ihr Kommentar'
                ]);
            } else {
                wp_send_json_success(['allowed' => false]);
            }
        
        }

    private function userCanEditStatus($requestId,$destinationStatusId){
        global $wpdb;
        if($this->kindCurrentUser == 'admin') {
            return true;
        }
        $table_exists = $wpdb->get_var("SHOW TABLES LIKE '{$wpdb->prefix}nexora_user_status'");
        if(!$table_exists) {
            return false;
        }

        $sql="select * from ".$this->table_name;
        $sql.=" where id = ".$requestId;
        $results=$wpdb->get_results( $sql);
        
        if(empty($results)) {
            return false;
        }
        
        $sourceStatusId=$results[0]->status_id;

        $statusSql="select * from ".$wpdb->prefix . "nexora_user_status ";
        $statusSql.="where 
        source_status_id = $sourceStatusId
        and destination_status_id =  $destinationStatusId
        and user_id =".$this->idCurrentUser;
         $status=$wpdb->get_results( $statusSql);

        if(count($status))
            return true;
        else
            return false;
    }

    public function get_dummy_product($productName,$price,$discount) {
        $product = new WC_Product_Simple();
        $product->set_name( $productName);
        $product->set_price($price);
        $product->set_regular_price(($price - $discount));
        $product->set_catalog_visibility('hidden');
        $product->save();
    return $product;
}

private function updateRequest($order_id,$request_id)
{
    global $wpdb;
    
    $data = array(
    'order_id' => $order_id);

    $where = array('id' => $request_id);

    $updated = $wpdb->update(
    $this->table_name,
    $data,
    $where
);

}

private function getUserId($request_id)
{
    global $wpdb;
    
    $sql = $wpdb->prepare(
    "SELECT user_id FROM {$this->table_name} WHERE id = %d", 
    $request_id
        );

    $user_id = $wpdb->get_var($sql);
    return $user_id;
}

private function getServiceTitle($serviceId)
{
    global $wpdb;
    $tblService=$wpdb->prefix."nexora_services";
    
    $sql = $wpdb->prepare(
    "SELECT title FROM {$tblService} WHERE id = %d", 
    $serviceId
        );
       
    $title = $wpdb->get_var($sql);
    return $title;

}

    public function ajax_upload_attachment() {
        $this->verify_nonce();
        
        if (!isset($_FILES['attachment']) || !isset($_POST['request_id'])) {
            wp_send_json_error('Missing file or request ID');
        }
        
        $request_id = intval($_POST['request_id']);
        $file = $_FILES['attachment'];
        if (!($this->kindCurrentUser == 'admin' || $this->userCanEditRequestService == 'yes')) {
            wp_send_json_error('Sie haben keinen Zugriff auf diesen Bereich');
        }
        $allowed_types = array('pdf', 'doc', 'docx', 'jpg', 'jpeg', 'png', 'gif', 'zip', 'rar');
        $file_extension = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
        
        if (!in_array($file_extension, $allowed_types)) {
            wp_send_json_error('Invalid file type');
        }
        
        if ($file['size'] > 10 * 1024 * 1024) {
            wp_send_json_error('File too large. Maximum size is 10MB');
        }
        $upload_dir = wp_upload_dir();
        $nexora_dir = $upload_dir['basedir'] . '/Nexora Service Suite-attachments';
        
        if (!file_exists($nexora_dir)) {
            wp_mkdir_p($nexora_dir);
        }
        $filename = time() . '_' . sanitize_file_name($file['name']);
        $filepath = $nexora_dir . '/' . $filename;
        if (move_uploaded_file($file['tmp_name'], $filepath)) {
            global $wpdb;
            $result = $wpdb->insert(
                $wpdb->prefix . 'nexora_request_attachments',
                array(
                    'request_id' => $request_id,
                    'file_name' => $file['name'],
                    'file_path' => $filepath,
                    'file_size' => $file['size'],
                    'file_type' => $file['type'],
                    'uploaded_by' => get_current_user_id()
                ),
                array('%d', '%s', '%s', '%d', '%s', '%d')
            );
            
            if ($result) {
                wp_send_json_success(array(
                    'message' => 'File uploaded successfully',
                    'attachment_id' => $wpdb->insert_id
                ));
            } else {
                wp_send_json_error('Database error');
            }
        } else {
            wp_send_json_error('Failed to upload file');
        }
    }
    
    public function ajax_get_attachments() {
        $this->verify_nonce();
        
        if (!isset($_POST['request_id'])) {
            wp_send_json_error('Missing request ID');
        }
        
        $request_id = intval($_POST['request_id']);
        
        global $wpdb;
        $attachments = $wpdb->get_results(
            $wpdb->prepare(
                "SELECT a.*, u.display_name as uploaded_by_name 
                 FROM {$wpdb->prefix}nexora_request_attachments a 
                 LEFT JOIN {$wpdb->users} u ON a.uploaded_by = u.ID 
                 WHERE a.request_id = %d 
                 ORDER BY a.created_at DESC",
                $request_id
            )
        );
        
        wp_send_json_success(array('attachments' => $attachments));
    }
    
    public function ajax_delete_attachment() {
        $this->verify_nonce();
        
        if (!isset($_POST['attachment_id'])) {
            wp_send_json_error('Missing attachment ID');
        }
        
        $attachment_id = intval($_POST['attachment_id']);
        
        if (!($this->kindCurrentUser == 'admin' || $this->userCanEditRequestService == 'yes')) {
            wp_send_json_error('Sie haben keinen Zugriff auf diesen Bereich');
        }
        
        global $wpdb;
        $attachment = $wpdb->get_row(
            $wpdb->prepare("SELECT file_path FROM {$wpdb->prefix}nexora_request_attachments WHERE id = %d", $attachment_id)
        );
        
        if ($attachment) {
            if (file_exists($attachment->file_path)) {
                unlink($attachment->file_path);
            }
            $result = $wpdb->delete(
                $wpdb->prefix . 'nexora_request_attachments',
                array('id' => $attachment_id),
                array('%d')
            );
            
            if ($result) {
                wp_send_json_success('Attachment deleted successfully');
            } else {
                wp_send_json_error('Database error');
            }
        } else {
            wp_send_json_error('Attachment not found');
        }
    }
    
    public function ajax_download_attachment() {
        $this->verify_nonce();
        
        if (!isset($_POST['attachment_id'])) {
            wp_send_json_error('Missing attachment ID');
        }
        
        $attachment_id = intval($_POST['attachment_id']);
        
        global $wpdb;
        $attachment = $wpdb->get_row(
            $wpdb->prepare("SELECT * FROM {$wpdb->prefix}nexora_request_attachments WHERE id = %d", $attachment_id)
        );
        
        if ($attachment && file_exists($attachment->file_path)) {
            wp_send_json_success(array(
                'download_url' => wp_upload_dir()['baseurl'] . '/Nexora Service Suite-attachments/' . basename($attachment->file_path),
                'file_name' => $attachment->file_name
            ));
        } else {
            wp_send_json_error('File not found');
        }
    }

    
    public function ajax_upload_invoice() {
        $this->verify_nonce();
        
        if (!isset($_FILES['invoice_file']) || !isset($_POST['request_id'])) {
            wp_send_json_error('Missing file or request ID');
        }
        
        $request_id = intval($_POST['request_id']);
        $file = $_FILES['invoice_file'];
        if (!($this->kindCurrentUser == 'admin' || $this->userCanEditRequestService == 'yes')) {
            wp_send_json_error('Sie haben keinen Zugriff auf diesen Bereich');
        }
        $allowed_types = array('pdf', 'doc', 'docx', 'jpg', 'jpeg', 'png', 'gif');
        $file_extension = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
        
        if (!in_array($file_extension, $allowed_types)) {
            wp_send_json_error('Ungültiger Dateityp. Nur PDF, DOC, DOCX, JPG, JPEG, PNG, GIF sind erlaubt');
        }
        
        if ($file['size'] > 10 * 1024 * 1024) {
            wp_send_json_error('File too large. Maximum size is 10MB');
        }
        $upload_dir = wp_upload_dir();
        $nexora_dir = $upload_dir['basedir'] . '/Nexora Service Suite-invoices';
        
        if (!file_exists($nexora_dir)) {
            wp_mkdir_p($nexora_dir);
        }
        $filename = time() . '_' . sanitize_file_name($file['name']);
        $filepath = $nexora_dir . '/' . $filename;
        if (move_uploaded_file($file['tmp_name'], $filepath)) {
            global $wpdb;
            $result = $wpdb->insert(
                $wpdb->prefix . 'nexora_request_invoices',
                array(
                    'request_id' => $request_id,
                    'file_name' => $file['name'],
                    'file_path' => $filepath,
                    'file_size' => $file['size'],
                    'file_type' => $file['type'],
                    'uploaded_by' => get_current_user_id()
                ),
                array('%d', '%s', '%s', '%d', '%s', '%d')
            );
            
            if ($result) {
                wp_send_json_success(array(
                    'message' => 'Invoice uploaded successfully',
                    'invoice_id' => $wpdb->insert_id
                ));
            } else {
                wp_send_json_error('Database error');
            }
        } else {
            wp_send_json_error('Failed to upload file');
        }
    }
    
    
    public function ajax_get_invoices() {
        $this->verify_nonce();
        
        if (!isset($_POST['request_id'])) {
            wp_send_json_error('Missing request ID');
        }
        
        $request_id = intval($_POST['request_id']);
        
        global $wpdb;
        $invoices = $wpdb->get_results(
            $wpdb->prepare(
                "SELECT i.*, u.display_name as uploaded_by_name 
                 FROM {$wpdb->prefix}nexora_request_invoices i 
                 LEFT JOIN {$wpdb->users} u ON i.uploaded_by = u.ID 
                 WHERE i.request_id = %d 
                 ORDER BY i.created_at DESC",
                $request_id
            )
        );
        
        wp_send_json_success(array('invoices' => $invoices));
    }
    
    
    public function ajax_delete_invoice() {
        $this->verify_nonce();
        
        if (!isset($_POST['invoice_id'])) {
            wp_send_json_error('Missing invoice ID');
        }
        
        $invoice_id = intval($_POST['invoice_id']);
        
        if (!($this->kindCurrentUser == 'admin' || $this->userCanEditRequestService == 'yes')) {
            wp_send_json_error('Sie haben keinen Zugriff auf diesen Bereich');
        }
        
        global $wpdb;
        $invoice = $wpdb->get_row(
            $wpdb->prepare("SELECT file_path FROM {$wpdb->prefix}nexora_request_invoices WHERE id = %d", $invoice_id)
        );
        
        if ($invoice) {
            if (file_exists($invoice->file_path)) {
                unlink($invoice->file_path);
            }
            $result = $wpdb->delete(
                $wpdb->prefix . 'nexora_request_invoices',
                array('id' => $invoice_id),
                array('%d')
            );
            
            if ($result) {
                wp_send_json_success('Invoice deleted successfully');
            } else {
                wp_send_json_error('Database error');
            }
        } else {
            wp_send_json_error('Invoice not found');
        }
    }
    
    
    public function ajax_download_invoice() {
        $this->verify_nonce();
        
        if (!isset($_POST['invoice_id'])) {
            wp_send_json_error('Missing invoice ID');
        }
        
        $invoice_id = intval($_POST['invoice_id']);
        
        global $wpdb;
        $invoice = $wpdb->get_row(
            $wpdb->prepare("SELECT * FROM {$wpdb->prefix}nexora_request_invoices WHERE id = %d", $invoice_id)
        );
        
        if ($invoice && file_exists($invoice->file_path)) {
            wp_send_json_success(array(
                'download_url' => wp_upload_dir()['baseurl'] . '/Nexora Service Suite-invoices/' . basename($invoice->file_path),
                'file_name' => $invoice->file_name
            ));
        } else {
            wp_send_json_error('File not found');
        }
    }

}
    