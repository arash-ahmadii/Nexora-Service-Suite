<?php

trait ServiceRequest_AJAX {
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
    public function ajax_save_invoice() {  }
    public function ajax_add_request() {  }
    public function ajax_update_request() {  }
    public function ajax_delete_request() {  }
    public function ajax_get_requests() {  }
    public function ajax_get_form_options() {  }
    public function ajax_get_request_comments() {  }
    public function ajax_add_request_comment() {  }
    public function ajax_get_user_meta_checkbox() {  }
    public function ajax_upload_attachment() {  }
    public function ajax_get_attachments() {  }
    public function ajax_delete_attachment() {  }
    public function ajax_download_attachment() {  }
    public function ajax_upload_invoice() {  }
    public function ajax_get_invoices() {  }
    public function ajax_delete_invoice() {  }
    public function ajax_download_invoice() {  }
} 