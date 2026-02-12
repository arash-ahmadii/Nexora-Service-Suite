<?php

trait RepairSystem_AJAX {
    public function ajax_check_tables() {
        check_ajax_referer('nexora_repair_nonce', 'nonce');
        wp_send_json($this->check_tables_status());
    }
    public function ajax_create_tables() {
        check_ajax_referer('nexora_repair_nonce', 'nonce');
        wp_send_json($this->create_missing_tables());
    }
    public function ajax_repair_tables() {
        check_ajax_referer('nexora_repair_nonce', 'nonce');
        wp_send_json($this->repair_tables());
    }
    public function ajax_test_classes() {
        check_ajax_referer('nexora_repair_nonce', 'nonce');
        wp_send_json($this->test_plugin_classes());
    }
    public function ajax_test_ajax() {
        check_ajax_referer('nexora_repair_nonce', 'nonce');
        wp_send_json($this->test_ajax_endpoints());
    }
    public function ajax_test_wordpress() {
        check_ajax_referer('nexora_repair_nonce', 'nonce');
        wp_send_json($this->test_wordpress_integration());
    }
    public function ajax_test_functionality() {
        check_ajax_referer('nexora_repair_nonce', 'nonce');
        wp_send_json($this->test_functionality());
    }
    public function ajax_create_sample_data() {
        check_ajax_referer('nexora_repair_nonce', 'nonce');
        wp_send_json($this->create_sample_data());
    }
    public function ajax_run_full_test() {
        check_ajax_referer('nexora_repair_nonce', 'nonce');
        $full_test = array(
            'tables' => $this->check_tables_status(),
            'classes' => $this->test_plugin_classes(),
            'ajax' => $this->test_ajax_endpoints(),
            'wordpress' => $this->test_wordpress_integration(),
            'functionality' => $this->test_functionality()
        );
        wp_send_json($full_test);
    }
    public function ajax_test_registration_errors() {
        check_ajax_referer('nexora_repair_nonce', 'nonce');
        wp_send_json($this->test_registration_errors());
    }
    public function ajax_test_ajax_registration() {
        check_ajax_referer('nexora_repair_nonce', 'nonce');
        wp_send_json($this->test_ajax_registration());
    }
    public function ajax_test_missing_fields() {
        check_ajax_referer('nexora_repair_nonce', 'nonce');
        wp_send_json($this->test_registration_with_missing_fields());
    }
    public function ajax_test_invalid_email() {
        check_ajax_referer('nexora_repair_nonce', 'nonce');
        $result = $this->test_registration_errors();
        wp_send_json($result['invalid_email']);
    }
    public function ajax_test_complete_registration() {
        check_ajax_referer('nexora_repair_nonce', 'nonce');
        wp_send_json($this->test_complete_registration());
    }
    public function ajax_test_duplicate_email() {
        check_ajax_referer('nexora_repair_nonce', 'nonce');
        $result = $this->test_registration_errors();
        wp_send_json($result['duplicate_email']);
    }
    public function ajax_test_database_operations() {
        check_ajax_referer('nexora_repair_nonce', 'nonce');
        wp_send_json($this->test_database_operations());
    }
    public function ajax_test_ajax_registration_comprehensive() {
        check_ajax_referer('nexora_repair_nonce', 'nonce');
        wp_send_json($this->test_ajax_registration_comprehensive());
    }
    public function ajax_test_form_rendering() {
        check_ajax_referer('nexora_repair_nonce', 'nonce');
        wp_send_json($this->test_form_rendering());
    }
    public function ajax_comprehensive_system_test() {
        check_ajax_referer('nexora_repair_nonce', 'nonce');
        ini_set('max_execution_time', 60);
        try {
            $result = $this->comprehensive_system_test();
            wp_send_json($result);
        } catch (Exception $e) {
            wp_send_json(array(
                'success' => false,
                'error' => 'Exception: ' . $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ));
        }
    }
    public function ajax_quick_registration_diagnostic() {
        check_ajax_referer('nexora_repair_nonce', 'nonce');
        try {
            $result = $this->quick_registration_diagnostic();
            wp_send_json($result);
        } catch (Exception $e) {
            wp_send_json(array(
                'success' => false,
                'error' => 'Exception: ' . $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ));
        }
    }
    public function ajax_fix_registration_issues() {
        check_ajax_referer('nexora_repair_nonce', 'nonce');
        try {
            $result = $this->fix_registration_issues();
            wp_send_json($result);
        } catch (Exception $e) {
            wp_send_json(array(
                'success' => false,
                'error' => 'Exception: ' . $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ));
        }
    }
    public function ajax_test_inherited_orderly() {
        check_ajax_referer('nexora_repair_nonce', 'nonce');
        try {
            $result = $this->test_inherited_orderly_error();
            wp_send_json($result);
        } catch (Exception $e) {
            wp_send_json(array(
                'success' => false,
                'error' => 'Exception: ' . $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ));
        }
    }
    public function ajax_test_privatkunde() {
        check_ajax_referer('nexora_repair_nonce', 'nonce');
        try {
            $result = $this->test_privatkunde_registration();
            wp_send_json($result);
        } catch (Exception $e) {
            wp_send_json(array(
                'success' => false,
                'error' => 'Exception: ' . $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ));
        }
    }
    public function ajax_test_field_validation() {
        check_ajax_referer('nexora_repair_nonce', 'nonce');
        wp_send_json($this->test_field_validation());
    }
    public function ajax_test_user_approval() {
        check_ajax_referer('nexora_repair_nonce', 'nonce');
        wp_send_json($this->test_user_approval());
    }
    public function ajax_test_badge_system() {
        check_ajax_referer('nexora_repair_nonce', 'nonce');
        wp_send_json($this->test_badge_system());
    }
    public function ajax_test_status_filter() {
        check_ajax_referer('nexora_repair_nonce', 'nonce');
        wp_send_json($this->test_status_filter());
    }
    public function ajax_test_user_info() {
        check_ajax_referer('nexora_repair_nonce', 'nonce');
        wp_send_json($this->test_user_info());
    }
    public function ajax_repair_request_invoices_table() {
        check_ajax_referer('nexora_repair_nonce', 'nonce');
        wp_send_json($this->repair_request_invoices_table());
    }
    public function ajax_debug_services_list() {
        check_ajax_referer('nexora_repair_nonce', 'nonce');
        wp_send_json($this->debug_services_list());
    }
    public function ajax_comprehensive_services_test() {
        check_ajax_referer('nexora_repair_nonce', 'nonce');
        try {
            $result = $this->comprehensive_services_test();
            wp_send_json($result);
        } catch (Exception $e) {
            wp_send_json(array(
                'success' => false,
                'error' => 'Exception: ' . $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ));
        }
    }
} 