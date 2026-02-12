<?php
class Nexora_Invoice_Request{

    
        public function __construct() {
            global $wpdb;
            add_action('init', [$this, 'setKindCurrentUser'],10);
            add_action('init', [$this, 'setup'],11);
        }

        public function setKindCurrentUser()
        {
            
        }

    
        public function setup() {
            add_action('wp_ajax_nexora_get_invoices', [$this, 'ajax_get_invoices']);
            add_action('wp_ajax_nexora_get_invoice_detail', [$this, 'ajax_get_invoice_detail']);

        }
    
        private function verify_nonce() {
            if (!isset($_POST['nonce']) || !wp_verify_nonce($_POST['nonce'], 'nexora_nonce')) {
                wp_send_json_error('Nonce-Verifizierung fehlgeschlagen', 403);
            }
            if (!current_user_can('manage_options')) {
                wp_send_json_error('Unbefugter Zugriff', 403);
            }
        }

    
}
    