<?php

if (!defined('ABSPATH')) {
    exit;
}

class Nexora_Error_Handler {
    
    private static $instance = null;
    private $original_error_handler = null;
    
    public static function get_instance() {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }
    
    private function __construct() {
        add_action('plugins_loaded', array($this, 'early_error_handling'), 1);
        add_action('admin_init', array($this, 'setup_error_handling'), 1);
        add_action('admin_init', array($this, 'remove_admin_notices'), 2);
        add_action('admin_head', array($this, 'hide_notices_css'));
    }
    
    public function early_error_handling() {
        if (is_admin() && isset($_GET['page']) && strpos($_GET['page'], 'Nexora Service Suite') !== false) {
            if ($this->original_error_handler === null) {
                $this->original_error_handler = set_error_handler(array($this, 'handle_error'));
            }
            ini_set('display_errors', 0);
            ini_set('display_startup_errors', 0);
            error_reporting(E_ERROR | E_PARSE | E_COMPILE_ERROR);
        }
    }
    
    public function setup_error_handling() {
        if (is_admin() && $this->is_nexora_page()) {
            if ($this->original_error_handler === null) {
                $this->original_error_handler = set_error_handler(array($this, 'handle_error'));
            }
            ini_set('display_errors', 0);
            ini_set('display_startup_errors', 0);
            error_reporting(E_ERROR | E_PARSE | E_COMPILE_ERROR);
        }
    }
    
    public function handle_error($errno, $errstr, $errfile, $errline) {
        if ($errno === E_ERROR || $errno === E_PARSE || $errno === E_COMPILE_ERROR) {
            return false;
        }
        if (defined('WP_DEBUG') && WP_DEBUG && defined('WP_DEBUG_LOG') && WP_DEBUG_LOG) {
            $error_types = array(
                E_ERROR => 'Fatal Error',
                E_WARNING => 'Warning',
                E_PARSE => 'Parse Error',
                E_NOTICE => 'Notice',
                E_CORE_ERROR => 'Core Error',
                E_CORE_WARNING => 'Core Warning',
                E_COMPILE_ERROR => 'Compile Error',
                E_COMPILE_WARNING => 'Compile Warning',
                E_USER_ERROR => 'User Error',
                E_USER_WARNING => 'User Warning',
                E_USER_NOTICE => 'User Notice',
                E_STRICT => 'Strict Notice',
                E_RECOVERABLE_ERROR => 'Recoverable Error',
                E_DEPRECATED => 'Deprecated',
                E_USER_DEPRECATED => 'User Deprecated'
            );
            
            $error_type = isset($error_types[$errno]) ? $error_types[$errno] : 'Unknown Error';
            $log_message = sprintf(
                'Nexora Service Suite Plugin %s: %s in %s on line %d',
                $error_type,
                $errstr,
                $errfile,
                $errline
            );
            
            error_log($log_message);
        }
        return true;
    }
    
    public function remove_admin_notices() {
        if ($this->is_nexora_page()) {
            remove_action('admin_notices', 'update_nag', 3);
            remove_action('admin_notices', 'maintenance_nag');
            remove_action('admin_notices', 'wp_recovery_mode_nag');
            remove_action('network_admin_notices', 'update_nag', 3);
            remove_action('user_admin_notices', 'update_nag', 3);
            remove_action('admin_notices', array('WP_Privacy_Policy_Content', 'notice'));
            if (isset($GLOBALS['wp_filter']['admin_notices'])) {
                $GLOBALS['wp_filter']['admin_notices']->callbacks = array();
            }
            
            if (isset($GLOBALS['wp_filter']['all_admin_notices'])) {
                $GLOBALS['wp_filter']['all_admin_notices']->callbacks = array();
            }
        }
    }
    
    public function hide_notices_css() {
        if ($this->is_nexora_page()) {
            echo '<style type="text/css">
                .notice, .error, .updated, .update-nag,
                div.notice, div.error, div.updated, div.update-nag,
                .notice-warning, .notice-error, .notice-success, .notice-info,
                #update-nag, .update-nag,
                .plugin-update-tr, .theme-update-tr,
                .wp-pointer, .wp-pointer-content,
                .update-message, .updating-message,
                .notice-alt, .inline-notice,
                .wp-core-ui .notice, .wp-core-ui .error, .wp-core-ui .updated {
                    display: none !important;
                    visibility: hidden !important;
                    height: 0 !important;
                    margin: 0 !important;
                    padding: 0 !important;
                }
                
                
                .wrap > .notice:first-child,
                .wrap > .error:first-child,
                .wrap > .updated:first-child,
                .wrap > .update-nag:first-child {
                    display: none !important;
                }
                
                
                .php-error, .wp-die-message {
                    display: none !important;
                }
            </style>';
        }
    }
    
    private function is_nexora_page() {
        if (isset($_GET['page']) && strpos($_GET['page'], 'Nexora Service Suite') !== false) {
            return true;
        }
        if (function_exists('get_current_screen')) {
            $screen = get_current_screen();
            if ($screen && isset($screen->id) && strpos($screen->id, 'Nexora Service Suite') !== false) {
                return true;
            }
        }
        if (defined('DOING_AJAX') && DOING_AJAX && isset($_POST['action']) && strpos($_POST['action'], 'Nexora Service Suite') !== false) {
            return true;
        }
        
        return false;
    }
    
    public function restore_error_handler() {
        if ($this->original_error_handler !== null) {
            set_error_handler($this->original_error_handler);
        } else {
            restore_error_handler();
        }
    }
    public function __destruct() {
        $this->restore_error_handler();
    }
}
Nexora_Error_Handler::get_instance(); 