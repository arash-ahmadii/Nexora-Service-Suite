<?php

if (!defined('ABSPATH')) {
    exit;
}

require_once __DIR__ . '/user-registration/UserRegistration_AJAX.php';

class Nexora_User_Registration {
    use UserRegistration_AJAX;
    
    public function __construct() {
        add_action('init', array($this, 'init'));
        add_action('wp_ajax_nexora_register_user', array($this, 'handle_registration'));
        add_action('wp_ajax_nopriv_nexora_register_user', array($this, 'handle_registration'));
        add_action('wp_ajax_nexora_login_user', array($this, 'handle_login'));
        add_action('wp_ajax_nopriv_nexora_login_user', array($this, 'handle_login'));
        add_action('wp_ajax_nexora_get_customer_details', array($this, 'get_customer_details'));
        add_action('wp_ajax_nexora_get_user_details', array($this, 'get_user_details'));
        add_action('wp_ajax_nexora_update_user', array($this, 'update_user'));
        add_action('wp_ajax_nexora_get_users', array($this, 'ajax_get_users'));
        add_action('wp_ajax_update_payment_status', array($this, 'update_payment_status'));
        add_action('wp_ajax_get_user_logs', array($this, 'get_user_logs'));
        add_action('wp_enqueue_scripts', array($this, 'enqueue_scripts'));
        add_shortcode('nexora_auth_form', array($this, 'render_auth_form'));
        add_action('admin_menu', array($this, 'add_user_management_menu'));
        add_action('template_redirect', array($this, 'check_user_approval_status'));
        add_shortcode('nexora_approval_message', array($this, 'render_approval_message'));
        add_shortcode('nexora_approval_banner', array($this, 'render_approval_banner'));
        add_filter('authenticate', array($this, 'enforce_approval_on_auth'), 30, 3);
    }
    
    public function init() {
        add_action('show_user_profile', array($this, 'add_custom_user_fields'));
        add_action('edit_user_profile', array($this, 'add_custom_user_fields'));
        add_action('personal_options_update', array($this, 'save_custom_user_fields'));
        add_action('edit_user_profile_update', array($this, 'save_custom_user_fields'));
    }

    
    public function enforce_approval_on_auth($user, $username, $password) {
        if (is_wp_error($user) || empty($username) || empty($password)) {
            return $user;
        }

        if ($user instanceof WP_User) {
            if (user_can($user, 'manage_options')) {
                return $user;
            }

            $approved = get_user_meta($user->ID, 'user_approved', true);
            if ($approved === 'no') {
                return new WP_Error(
                    'nexora_user_rejected',
                    __('Ihr Konto wurde abgelehnt. Bitte kontaktieren Sie den Administrator.', 'Nexora Service Suite')
                );
            }
            if ($approved !== 'yes') {
                return new WP_Error(
                    'nexora_user_pending',
                    __('Ihr Konto wartet noch auf die Genehmigung durch den Administrator. Bitte haben Sie Geduld.', 'Nexora Service Suite')
                );
            }
        }

        return $user;
    }
    
    public function enqueue_scripts() {
        wp_enqueue_style(
            'Nexora Service Suite-auth-css',
            NEXORA_PLUGIN_URL . 'assets/css/auth-form.css',
            array(),
            filemtime(NEXORA_PLUGIN_DIR . 'assets/css/auth-form.css')
        );
        
        wp_enqueue_script(
            'Nexora Service Suite-auth-js',
            NEXORA_PLUGIN_URL . 'assets/js/auth-form.js',
            array('jquery'),
            filemtime(NEXORA_PLUGIN_DIR . 'assets/js/auth-form.js'),
            true
        );
        
        wp_localize_script('Nexora Service Suite-auth-js', 'nexora_auth', array(
            'ajax_url' => admin_url('admin-ajax.php'),
            'auth_nonce' => wp_create_nonce('nexora_auth_nonce'),
            'strings' => array(
                'required_field' => 'Dieses Feld ist erforderlich',
                'invalid_email' => 'Die E-Mail-Adresse ist ungültig',
                'password_mismatch' => 'Das Passwort und die Wiederholung stimmen nicht überein',
                'processing' => 'Wird verarbeitet...',
                'error' => 'Fehler beim Verarbeiten der Anfrage'
            )
        ));
    }
    
    public function render_auth_form($atts) {
        $atts = shortcode_atts(array(
            'mode' => 'both',
            'redirect' => ''
        ), $atts);
        
        if (is_user_logged_in()) {
            if (has_shortcode(get_post_field('post_content', get_the_ID()), 'woocommerce_my_account')) {
                return '';
            }
            ob_start();
            echo '<div class="Nexora Service Suite-auth-logged-in">';
            echo '<p>Sie sind eingeloggt. <a href="' . esc_url(wc_get_account_endpoint_url('dashboard')) . '">Dashboard anzeigen</a></p>';
            echo '</div>';
            return ob_get_clean();
        }
        ob_start();
        ?>
        <div class="Nexora Service Suite-auth-container">
            <div class="Nexora Service Suite-auth-tabs">
                <?php if ($atts['mode'] === 'both' || $atts['mode'] === 'login'): ?>
                <button class="Nexora Service Suite-tab-btn active" data-tab="login">Einloggen</button>
                <?php endif; ?>
                <?php if ($atts['mode'] === 'both' || $atts['mode'] === 'register'): ?>
                <button class="Nexora Service Suite-tab-btn" data-tab="register">Registrieren</button>
                <?php endif; ?>
            </div>
            
            <div class="Nexora Service Suite-auth-content">
                <?php if ($atts['mode'] === 'both' || $atts['mode'] === 'login'): ?>
                <div class="Nexora Service Suite-tab-content active" id="login-tab">
                    <?php $this->render_login_form($atts['redirect']); ?>
                </div>
                <?php endif; ?>
                
                <?php if ($atts['mode'] === 'both' || $atts['mode'] === 'register'): ?>
                <div class="Nexora Service Suite-tab-content" id="register-tab">
                    <?php $this->render_register_form($atts['redirect']); ?>
                </div>
                <?php endif; ?>
            </div>
        </div>
        <?php
        return ob_get_clean();
    }
    
    private function render_login_form($redirect = '') {
        ?>
        <form class="Nexora Service Suite-login-form" method="post">
            <div class="Nexora Service Suite-form-group">
                <label for="login_email">E-Mail oder Benutzername *</label>
                <input type="text" id="login_email" name="email" required>
            </div>
            
            <div class="Nexora Service Suite-form-group">
                <label for="login_password">Passwort *</label>
                <div class="Nexora Service Suite-password-wrapper">
                <input type="password" id="login_password" name="password" required>
                    <span class="Nexora Service Suite-password-toggle" onclick="togglePassword('login_password')" title="Passwort anzeigen">
                        <span class="Nexora Service Suite-eye-icon">👁</span>
                    </span>
                </div>
            </div>
            
            <div class="Nexora Service Suite-form-group">
                <label class="Nexora Service Suite-checkbox-label">
                    <input type="checkbox" name="remember" value="1">
                    Passwort merken
                </label>
            </div>
            
            <div class="Nexora Service Suite-form-actions">
                <button type="submit" class="Nexora Service Suite-btn Nexora Service Suite-btn-primary">Einloggen</button>
            </div>
            
            <input type="hidden" name="action" value="nexora_login_user">
            <input type="hidden" name="redirect" value="<?php echo esc_attr($redirect); ?>">
        </form>
        <?php
    }
    
    private function render_register_form($redirect = '') {
        ?>
        <form class="Nexora Service Suite-register-form" method="post">
            
            <div class="Nexora Service Suite-progress">
                <div class="Nexora Service Suite-progress-step active" data-step="1">1</div>
                <div class="Nexora Service Suite-progress-step" data-step="2">2</div>
            </div>
            
            
            <div class="Nexora Service Suite-form-step active" data-step="1">
                <h3>Schritt 1: Grundinformationen</h3>
                
                <div class="Nexora Service Suite-form-group">
                    <label for="reg_email">E-Mail-Adresse *</label>
                    <input type="email" id="reg_email" name="email" required>
                </div>
                
                <div class="Nexora Service Suite-form-group">
                    <label for="reg_password">Passwort *</label>
                    <div class="Nexora Service Suite-password-wrapper">
                    <input type="password" id="reg_password" name="password" required>
                        <span class="Nexora Service Suite-password-toggle" onclick="togglePassword('reg_password')" title="Passwort anzeigen">
                            <span class="Nexora Service Suite-eye-icon">👁</span>
                        </span>
                    </div>
                    <div class="Nexora Service Suite-password-strength"></div>
                    <div class="Nexora Service Suite-password-strength-bar">
                        <div class="Nexora Service Suite-password-strength-bar-fill"></div>
                    </div>
                </div>
                
                <div class="Nexora Service Suite-form-group">
                    <label for="reg_password_confirm">Passwort bestätigen *</label>
                    <div class="Nexora Service Suite-password-wrapper">
                    <input type="password" id="reg_password_confirm" name="password_confirm" required>
                        <span class="Nexora Service Suite-password-toggle" onclick="togglePassword('reg_password_confirm')" title="Passwort anzeigen">
                            <span class="Nexora Service Suite-eye-icon">👁</span>
                        </span>
                    </div>
                    <div class="Nexora Service Suite-password-match" style="color: #e53e3e; font-size: 14px; margin-top: 5px; display: none;"></div>
                </div>
                
                <div class="Nexora Service Suite-form-group">
                    <label for="customer_type">Kundentyp *</label>
                    <div class="Nexora Service Suite-custom-dropdown" id="customer_type_dropdown" style="position: relative;">
                        <div class="Nexora Service Suite-dropdown-trigger" data-value="" style="width: 100%; padding: 16px 12px; border: 2px solid #ddd; border-radius: 6px; font-size: 18px; background: #fff; color: #888; cursor: pointer; text-align: left; position: relative; margin-bottom: 0; font-family: inherit;">
                            Bitte auswählen
                            <span style="position: absolute; right: 12px; top: 50%; transform: translateY(-50%); font-size: 12px; color: #888;">▼</span>
                        </div>
                        <div class="Nexora Service Suite-dropdown-menu" style="position: absolute; top: 100%; left: 0; right: 0; background: white; border: 2px solid #ddd; border-top: none; border-radius: 0 0 6px 6px; max-height: 200px; overflow-y: auto; z-index: 1000; display: none; box-shadow: 0 4px 12px rgba(0,0,0,0.15);">
                            <div class="Nexora Service Suite-dropdown-option" data-value="" style="padding: 12px; cursor: pointer; border-bottom: 1px solid #eee; color: #888; font-style: italic;">Bitte auswählen</div>
                            <div class="Nexora Service Suite-dropdown-option" data-value="private" style="padding: 12px; cursor: pointer; border-bottom: 1px solid #eee;">Privatkunde</div>
                            <div class="Nexora Service Suite-dropdown-option" data-value="business" style="padding: 12px; cursor: pointer; border-bottom: 1px solid #eee;">Firmenkunde</div>
                        </div>
                    </div>
                    <input type="hidden" id="customer_type" name="customer_type" value="" required>
                </div>
                
                <div class="Nexora Service Suite-form-actions">
                    <button type="button" class="Nexora Service Suite-btn Nexora Service Suite-btn-primary next-step">weiter zu Schritt 2</button>
                </div>
            </div>
            
            
            <div class="Nexora Service Suite-form-step" data-step="2">
                <h3>Schritt 2: Kundendaten</h3>
                
                
                <div class="customer-form private-customer" style="display: none;">
                <div class="Nexora Service Suite-form-group">
                        <label for="salutation_private">Anrede *</label>
                        <div class="Nexora Service Suite-custom-dropdown" id="salutation_private_dropdown" style="position: relative;">
                            <div class="Nexora Service Suite-dropdown-trigger" data-value="" style="width: 100%; padding: 16px 12px; border: 2px solid #ddd; border-radius: 6px; font-size: 18px; background: #fff; color: #888; cursor: pointer; text-align: left; position: relative; margin-bottom: 0; font-family: inherit;">
                                Bitte auswählen
                                <span style="position: absolute; right: 12px; top: 50%; transform: translateY(-50%); font-size: 12px; color: #888;">▼</span>
                            </div>
                            <div class="Nexora Service Suite-dropdown-menu" style="position: absolute; top: 100%; left: 0; right: 0; background: white; border: 2px solid #ddd; border-top: none; border-radius: 0 0 6px 6px; max-height: 200px; overflow-y: auto; z-index: 1000; display: none; box-shadow: 0 4px 12px rgba(0,0,0,0.15);">
                                <div class="Nexora Service Suite-dropdown-option" data-value="" style="padding: 12px; cursor: pointer; border-bottom: 1px solid #eee; color: #888; font-style: italic;">Bitte auswählen</div>
                                <div class="Nexora Service Suite-dropdown-option" data-value="Herr" style="padding: 12px; cursor: pointer; border-bottom: 1px solid #eee;">Herr</div>
                                <div class="Nexora Service Suite-dropdown-option" data-value="Frau" style="padding: 12px; cursor: pointer; border-bottom: 1px solid #eee;">Frau</div>
                                <div class="Nexora Service Suite-dropdown-option" data-value="Divers" style="padding: 12px; cursor: pointer; border-bottom: 1px solid #eee;">Divers</div>
                            </div>
                        </div>
                        <input type="hidden" id="salutation_private" name="salutation_private" value="" required>
                </div>
                
                <div class="Nexora Service Suite-form-group">
                        <label for="first_name_private">Vorname *</label>
                        <input type="text" id="first_name_private" name="first_name_private" required>
                </div>
                
                <div class="Nexora Service Suite-form-group">
                        <label for="last_name_private">Nachname *</label>
                        <input type="text" id="last_name_private" name="last_name_private" required>
                </div>
                
                <div class="Nexora Service Suite-form-group">
                    <label for="street_private">Straße und Hausnummer *</label>
                    <input type="text" id="street_private" name="street_private" required>
                </div>
                
                <div class="Nexora Service Suite-form-group">
                        <label for="postfach_private">Postfach</label>
                        <input type="text" id="postfach_private" name="postfach_private">
                </div>
                
                <div class="Nexora Service Suite-form-row">
                    <div class="Nexora Service Suite-form-group">
                        <label for="postal_code_private">PLZ *</label>
                        <input type="text" id="postal_code_private" name="postal_code_private" required>
                    </div>
                    
                    <div class="Nexora Service Suite-form-group">
                        <label for="city_private">Ort *</label>
                        <input type="text" id="city_private" name="city_private" required>
                    </div>
                </div>
                
                <div class="Nexora Service Suite-form-group">
                    <label for="country_private">Land *</label>
                    <div class="Nexora Service Suite-custom-dropdown" id="country_private_dropdown" style="position: relative;">
                        <div class="Nexora Service Suite-dropdown-trigger" data-value="AT" style="width: 100%; padding: 16px 12px; border: 2px solid #ddd; border-radius: 6px; font-size: 18px; background: #fff; color: #222; cursor: pointer; text-align: left; position: relative; margin-bottom: 0; font-family: inherit;">
                            Österreich
                            <span style="position: absolute; right: 12px; top: 50%; transform: translateY(-50%); font-size: 12px; color: #888;">▼</span>
                        </div>
                        <div class="Nexora Service Suite-dropdown-menu" style="position: absolute; top: 100%; left: 0; right: 0; background: white; border: 2px solid #ddd; border-top: none; border-radius: 0 0 6px 6px; max-height: 200px; overflow-y: auto; z-index: 1000; display: none; box-shadow: 0 4px 12px rgba(0,0,0,0.15);">
                            <div class="Nexora Service Suite-dropdown-option" data-value="DE" style="padding: 12px; cursor: pointer; border-bottom: 1px solid #eee;">Deutschland</div>
                            <div class="Nexora Service Suite-dropdown-option" data-value="AT" style="padding: 12px; cursor: pointer; border-bottom: 1px solid #eee;">Österreich</div>
                            <div class="Nexora Service Suite-dropdown-option" data-value="CH" style="padding: 12px; cursor: pointer; border-bottom: 1px solid #eee;">Schweiz</div>
                        </div>
                    </div>
                    <input type="hidden" id="country_private" name="country_private" value="AT" required>
                </div>
                
                <div class="Nexora Service Suite-form-group">
                        <label for="reference_number_private">Ihre Referenznummer</label>
                        <input type="text" id="reference_number_private" name="reference_number_private">
                    </div>
                    
                    <div class="Nexora Service Suite-form-group">
                        <label for="phone_private">Telefon</label>
                        <input type="tel" id="phone_private" name="phone_private">
                </div>
                
                <div class="Nexora Service Suite-form-group">
                        <label class="Nexora Service Suite-checkbox-label">
                            <input type="checkbox" name="newsletter_private" value="1">
                            Newsletter abonnieren
                        </label>
                </div>
                
                    <div class="Nexora Service Suite-form-group">
                        <label class="Nexora Service Suite-checkbox-label">
                            <input type="checkbox" name="terms_accepted_private" value="1" required>
                            Datenschutzbestimmungen und Cookie-Richtlinien *
                        </label>
                    </div>
                </div>
                
                
                <div class="customer-form business-customer" style="display: none;">
                <div class="Nexora Service Suite-form-group">
                        <label for="salutation_business">Anrede *</label>
                        <div class="Nexora Service Suite-custom-dropdown" id="salutation_business_dropdown" style="position: relative;">
                            <div class="Nexora Service Suite-dropdown-trigger" data-value="" style="width: 100%; padding: 16px 12px; border: 2px solid #ddd; border-radius: 6px; font-size: 18px; background: #fff; color: #888; cursor: pointer; text-align: left; position: relative; margin-bottom: 0; font-family: inherit;">
                                Bitte auswählen
                                <span style="position: absolute; right: 12px; top: 50%; transform: translateY(-50%); font-size: 12px; color: #888;">▼</span>
                            </div>
                            <div class="Nexora Service Suite-dropdown-menu" style="position: absolute; top: 100%; left: 0; right: 0; background: white; border: 2px solid #ddd; border-top: none; border-radius: 0 0 6px 6px; max-height: 200px; overflow-y: auto; z-index: 1000; display: none; box-shadow: 0 4px 12px rgba(0,0,0,0.15);">
                                <div class="Nexora Service Suite-dropdown-option" data-value="" style="padding: 12px; cursor: pointer; border-bottom: 1px solid #eee; color: #888; font-style: italic;">Bitte auswählen</div>
                                <div class="Nexora Service Suite-dropdown-option" data-value="Herr" style="padding: 12px; cursor: pointer; border-bottom: 1px solid #eee;">Herr</div>
                                <div class="Nexora Service Suite-dropdown-option" data-value="Frau" style="padding: 12px; cursor: pointer; border-bottom: 1px solid #eee;">Frau</div>
                                <div class="Nexora Service Suite-dropdown-option" data-value="Divers" style="padding: 12px; cursor: pointer; border-bottom: 1px solid #eee;">Divers</div>
                            </div>
                        </div>
                        <input type="hidden" id="salutation_business" name="salutation_business" value="" required>
                </div>
                
                <div class="Nexora Service Suite-form-group">
                        <label for="first_name_business">Vorname *</label>
                        <input type="text" id="first_name_business" name="first_name_business" required>
                    </div>
                    
                    <div class="Nexora Service Suite-form-group">
                        <label for="last_name_business">Nachname *</label>
                        <input type="text" id="last_name_business" name="last_name_business" required>
                    </div>
                    
                    <div class="Nexora Service Suite-form-group">
                        <label for="company_name">Firma *</label>
                        <input type="text" id="company_name" name="company_name" required>
                    </div>
                    
                    <div class="Nexora Service Suite-form-group">
                        <label for="street_business">Straße *</label>
                        <input type="text" id="street_business" name="street_business" required>
                    </div>
                    
                    <div class="Nexora Service Suite-form-group">
                        <label for="house_number_business">Hausnummer *</label>
                        <input type="text" id="house_number_business" name="house_number_business" required>
                    </div>
                    
                    <div class="Nexora Service Suite-form-group">
                        <label for="postfach_business">Postfach</label>
                        <input type="text" id="postfach_business" name="postfach_business">
                    </div>
                    
                    <div class="Nexora Service Suite-form-row">
                        <div class="Nexora Service Suite-form-group">
                            <label for="postal_code_business">PLZ *</label>
                            <input type="text" id="postal_code_business" name="postal_code_business" required>
                        </div>
                        
                        <div class="Nexora Service Suite-form-group">
                            <label for="city_business">Ort *</label>
                            <input type="text" id="city_business" name="city_business" required>
                        </div>
                    </div>
                    
                    <div class="Nexora Service Suite-form-group">
                        <label for="country_business">Land *</label>
                        <div class="Nexora Service Suite-custom-dropdown" id="country_business_dropdown" style="position: relative;">
                            <div class="Nexora Service Suite-dropdown-trigger" data-value="AT" style="width: 100%; padding: 16px 12px; border: 2px solid #ddd; border-radius: 6px; font-size: 18px; background: #fff; color: #222; cursor: pointer; text-align: left; position: relative; margin-bottom: 0; font-family: inherit;">
                                Österreich
                                <span style="position: absolute; right: 12px; top: 50%; transform: translateY(-50%); font-size: 12px; color: #888;">▼</span>
                            </div>
                            <div class="Nexora Service Suite-dropdown-menu" style="position: absolute; top: 100%; left: 0; right: 0; background: white; border: 2px solid #ddd; border-top: none; border-radius: 0 0 6px 6px; max-height: 200px; overflow-y: auto; z-index: 1000; display: none; box-shadow: 0 4px 12px rgba(0,0,0,0.15);">
                                <div class="Nexora Service Suite-dropdown-option" data-value="DE" style="padding: 12px; cursor: pointer; border-bottom: 1px solid #eee;">Deutschland</div>
                                <div class="Nexora Service Suite-dropdown-option" data-value="AT" style="padding: 12px; cursor: pointer; border-bottom: 1px solid #eee;">Österreich</div>
                                <div class="Nexora Service Suite-dropdown-option" data-value="CH" style="padding: 12px; cursor: pointer; border-bottom: 1px solid #eee;">Schweiz</div>
                            </div>
                        </div>
                        <input type="hidden" id="country_business" name="country_business" value="AT" required>
                    </div>
                    
                    <div class="Nexora Service Suite-form-group">
                        <label for="vat_id">UST-ID</label>
                        <input type="text" id="vat_id" name="vat_id">
                        <small>Umsatzsteuer-Identifikationsnummer (optional)</small>
                </div>
                
                <div class="Nexora Service Suite-form-group">
                        <label for="reference_number_business">Ihre Referenznummer</label>
                        <input type="text" id="reference_number_business" name="reference_number">
                </div>
                
                <div class="Nexora Service Suite-form-group">
                        <label for="phone_business">Telefon</label>
                        <input type="tel" id="phone_business" name="phone_business">
                </div>
                
                <div class="Nexora Service Suite-form-group">
                    <label class="Nexora Service Suite-checkbox-label">
                        <input type="checkbox" name="newsletter_business" value="1">
                        Newsletter abonnieren
                    </label>
                </div>
                
                <div class="Nexora Service Suite-form-group">
                    <label class="Nexora Service Suite-checkbox-label">
                        <input type="checkbox" name="terms_accepted_business" value="1" required>
                        Datenschutzbestimmungen und Cookie-Richtlinien *
                    </label>
                    </div>
                </div>
                
                <div class="Nexora Service Suite-form-actions">
                    <button type="button" class="Nexora Service Suite-btn Nexora Service Suite-btn-secondary prev-step">vorherige Stufe</button>
                    <button type="submit" class="Nexora Service Suite-btn Nexora Service Suite-btn-primary">Konto erstellen</button>
                </div>
            </div>

            
            <input type="hidden" name="action" value="nexora_register_user">
            <input type="hidden" name="redirect" value="<?php echo esc_attr($redirect); ?>">
            <?php wp_nonce_field('nexora_auth_nonce', 'auth_nonce'); ?>
        </form>
        <?php
    }
    
    public function handle_registration() {
        check_ajax_referer('nexora_auth_nonce', 'auth_nonce');
        add_filter('gettext', array($this, 'fix_inherited_orderly_errors'), 10, 3);
        add_filter('ngettext', array($this, 'fix_inherited_orderly_errors'), 10, 3);
        
        $response = array('success' => false, 'message' => '');
        if (!function_exists('nexora_clean_error_message')) {
            function nexora_clean_error_message($message) {
                error_log('Nexora Service Suite CLEAN ERROR - Original: ' . $message);
                if (strpos($message, 'inherited orderly') !== false || strpos($message, 'inherited') !== false) {
                    error_log('Nexora Service Suite CLEAN ERROR - Detected inherited orderly corruption');
                    
                    if (strpos($message, 'Vorname') !== false || strpos($message, 'first_name') !== false) {
                        $cleaned = 'Das Feld "Vorname" ist erforderlich.';
                        error_log('Nexora Service Suite CLEAN ERROR - Fixed Vorname: ' . $cleaned);
                        return $cleaned;
                    }
                    if (strpos($message, 'Nachname') !== false || strpos($message, 'last_name') !== false) {
                        $cleaned = 'Das Feld "Nachname" ist erforderlich.';
                        error_log('Nexora Service Suite CLEAN ERROR - Fixed Nachname: ' . $cleaned);
                        return $cleaned;
                    }
                    if (strpos($message, 'email') !== false || strpos($message, 'E-Mail') !== false) {
                        $cleaned = 'Das Feld "E-Mail-Adresse" ist erforderlich.';
                        error_log('Nexora Service Suite CLEAN ERROR - Fixed Email: ' . $cleaned);
                        return $cleaned;
                    }
                    if (strpos($message, 'password') !== false || strpos($message, 'Passwort') !== false) {
                        $cleaned = 'Das Feld "Passwort" ist erforderlich.';
                        error_log('Nexora Service Suite CLEAN ERROR - Fixed Password: ' . $cleaned);
                        return $cleaned;
                    }
                    if (strpos($message, 'salutation') !== false || strpos($message, 'Anrede') !== false) {
                        $cleaned = 'Bitte wählen Sie eine Anrede aus.';
                        error_log('Nexora Service Suite CLEAN ERROR - Fixed Salutation: ' . $cleaned);
                        return $cleaned;
                    }
                    $cleaned = 'Ein erforderliches Feld ist nicht ausgefüllt.';
                    error_log('Nexora Service Suite CLEAN ERROR - Generic fallback: ' . $cleaned);
                    return $cleaned;
                }
                if (preg_match('/The field.*is (required|inherited|orderly)/i', $message)) {
                    error_log('Nexora Service Suite CLEAN ERROR - Detected English corruption');
                    if (strpos($message, 'Vorname') !== false) {
                        $cleaned = 'Das Feld "Vorname" ist erforderlich.';
                        error_log('Nexora Service Suite CLEAN ERROR - Fixed English Vorname: ' . $cleaned);
                        return $cleaned;
                    }
                    $cleaned = 'Ein erforderliches Feld ist nicht ausgefüllt.';
                    error_log('Nexora Service Suite CLEAN ERROR - Fixed English generic: ' . $cleaned);
                    return $cleaned;
                }
                
                error_log('Nexora Service Suite CLEAN ERROR - No corruption detected, returning original');
                return $message;
            }
        }
        error_log('Registration POST data: ' . print_r($_POST, true));
        if ($_POST['password'] !== $_POST['password_confirm']) {
            $response['message'] = 'Das Passwort und die Wiederholung stimmen nicht überein';
            $response['message'] = nexora_clean_error_message($response['message']);
            wp_send_json($response);
        }
        if (email_exists($_POST['email'])) {
            $response['message'] = 'Diese E-Mail-Adresse ist bereits registriert';
            $response['message'] = nexora_clean_error_message($response['message']);
            wp_send_json($response);
        }
        if (email_exists($_POST['email'])) {
            $response['message'] = 'Diese E-Mail-Adresse ist bereits registriert';
            $response['message'] = nexora_clean_error_message($response['message']);
            wp_send_json($response);
        }
        $user_data = array(
            'user_login' => $_POST['email'],
            'user_email' => $_POST['email'],
            'user_pass' => $_POST['password'],
            'first_name' => !empty($_POST['first_name']) ? sanitize_text_field($_POST['first_name']) : '',
            'last_name' => !empty($_POST['last_name']) ? sanitize_text_field($_POST['last_name']) : '',
            'role' => 'customer'
        );
        
        $user_id = wp_insert_user($user_data);
        
        if (is_wp_error($user_id)) {
            $response['message'] = $user_id->get_error_message();
            $response['message'] = nexora_clean_error_message($response['message']);
            wp_send_json($response);
        }
        $this->save_user_meta($user_id, $_POST);
        update_user_meta($user_id, 'user_approved', 'pending');
        if (class_exists('Nexora_Admin_Notifications')) {
            $customer_type = sanitize_text_field($_POST['customer_type']);
            Nexora_Admin_Notifications::notify_new_user_registration($user_id, $customer_type);
        }
        do_action('nexora_customer_registered', $user_id, $_POST);
        $response['success'] = true;
        $response['message'] = 'Ihre Registrierung wurde erfolgreich eingereicht. Die Freigabe kann bis zu 24 Stunden dauern.';
        $response['redirect'] = '';
        $response['message'] = nexora_clean_error_message($response['message']);
        
        wp_send_json($response);
    }
    public function fix_inherited_orderly_errors($translation, $text, $domain) {
        error_log('Nexora Service Suite TRANSLATION DEBUG - Original: ' . $text . ' | Translated: ' . $translation . ' | Domain: ' . $domain);
        if (strpos($translation, 'inherited orderly') !== false) {
            error_log('Nexora Service Suite TRANSLATION FIX - Detected inherited orderly in: ' . $translation);
            if (strpos($text, 'first_name') !== false || strpos($text, 'Vorname') !== false) {
                $fixed = 'Das Feld "Vorname" ist erforderlich.';
                error_log('Nexora Service Suite TRANSLATION FIX - Fixed first_name: ' . $fixed);
                return $fixed;
            }
            if (strpos($text, 'last_name') !== false || strpos($text, 'Nachname') !== false) {
                $fixed = 'Das Feld "Nachname" ist erforderlich.';
                error_log('Nexora Service Suite TRANSLATION FIX - Fixed last_name: ' . $fixed);
                return $fixed;
            }
            if (strpos($text, 'email') !== false || strpos($text, 'E-Mail') !== false) {
                $fixed = 'Das Feld "E-Mail-Adresse" ist erforderlich.';
                error_log('Nexora Service Suite TRANSLATION FIX - Fixed email: ' . $fixed);
                return $fixed;
            }
            if (strpos($text, 'password') !== false || strpos($text, 'Passwort') !== false) {
                $fixed = 'Das Feld "Passwort" ist erforderlich.';
                error_log('Nexora Service Suite TRANSLATION FIX - Fixed password: ' . $fixed);
                return $fixed;
            }
            if (strpos($text, 'salutation') !== false || strpos($text, 'Anrede') !== false) {
                $fixed = 'Bitte wählen Sie eine Anrede aus.';
                error_log('Nexora Service Suite TRANSLATION FIX - Fixed salutation: ' . $fixed);
                return $fixed;
            }
            if (strpos($text, 'street') !== false || strpos($text, 'Straße') !== false) {
                $fixed = 'Das Feld "Straße und Hausnummer" ist erforderlich.';
                error_log('Nexora Service Suite TRANSLATION FIX - Fixed street: ' . $fixed);
                return $fixed;
            }
            if (strpos($text, 'postal_code') !== false || strpos($text, 'PLZ') !== false) {
                $fixed = 'Das Feld "PLZ" ist erforderlich.';
                error_log('Nexora Service Suite TRANSLATION FIX - Fixed postal_code: ' . $fixed);
                return $fixed;
            }
            if (strpos($text, 'city') !== false || strpos($text, 'Ort') !== false) {
                $fixed = 'Das Feld "Ort" ist erforderlich.';
                error_log('Nexora Service Suite TRANSLATION FIX - Fixed city: ' . $fixed);
                return $fixed;
            }
            if (strpos($text, 'country') !== false || strpos($text, 'Land') !== false) {
                $fixed = 'Das Feld "Land" ist erforderlich.';
                error_log('Nexora Service Suite TRANSLATION FIX - Fixed country: ' . $fixed);
                return $fixed;
            }
            $fixed = 'Ein erforderliches Feld ist nicht ausgefüllt.';
            error_log('Nexora Service Suite TRANSLATION FIX - Generic fallback: ' . $fixed);
            return $fixed;
        }
        if (preg_match('/The field.*is (required|inherited|orderly)/i', $translation)) {
            error_log('Nexora Service Suite TRANSLATION FIX - Detected English validation message: ' . $translation);
            
            if (strpos($text, 'first_name') !== false || strpos($text, 'Vorname') !== false) {
                $fixed = 'Das Feld "Vorname" ist erforderlich.';
                error_log('Nexora Service Suite TRANSLATION FIX - Fixed English first_name: ' . $fixed);
                return $fixed;
            }
            
            $fixed = 'Ein erforderliches Feld ist nicht ausgefüllt.';
            error_log('Nexora Service Suite TRANSLATION FIX - Fixed English generic: ' . $fixed);
            return $fixed;
        }
        
        return $translation;
    }
    
    public function handle_login() {
        check_ajax_referer('nexora_auth_nonce', 'auth_nonce');
        
        $response = array('success' => false, 'message' => '');
        
        $login_identifier = sanitize_text_field($_POST['email']);
        $password = $_POST['password'];
        $remember = !empty($_POST['remember']);
        
        if (empty($login_identifier) || empty($password)) {
            $response['message'] = 'E-Mail/Benutzername und Passwort sind erforderlich';
            wp_send_json($response);
        }
        $user = get_user_by('email', $login_identifier);
        if (!$user) {
            $user = get_user_by('login', $login_identifier);
        }
        
        if (!$user) {
            $response['message'] = 'E-Mail/Benutzername oder Passwort falsch';
            wp_send_json($response);
        }
        
        if (!wp_check_password($password, $user->user_pass, $user->ID)) {
            $response['message'] = 'E-Mail/Benutzername oder Passwort falsch';
            wp_send_json($response);
        }
        $user_approved = get_user_meta($user->ID, 'user_approved', true);
        if ($user_approved === 'no') {
            $response['message'] = 'Ihr Konto wurde abgelehnt. Bitte kontaktieren Sie den Administrator.';
            wp_send_json($response);
        }
        if ($user_approved !== 'yes') {
            $response['message'] = 'Ihr Konto wartet noch auf die Genehmigung durch den Administrator. Bitte haben Sie Geduld.';
            wp_send_json($response);
        }
        wp_set_current_user($user->ID);
        wp_set_auth_cookie($user->ID, $remember);
        
        $response['success'] = true;
        $response['message'] = 'Einloggen erfolgreich';
        $response['redirect'] = !empty($_POST['redirect']) ? $_POST['redirect'] : home_url('/my-account/');
        
        wp_send_json($response);
    }
    
    private function save_user_meta($user_id, $data) {
        global $wpdb;
        error_log('Nexora Service Suite save_user_meta - Received data: ' . print_r($data, true));
        $users_table_fields = array(
            'customer_type' => $data['customer_type'] ?? '',
            'company_name' => $data['company_name'] ?? '',
            'street' => $data['street'] ?? '',
            'house_number' => $data['house_number'] ?? '',
            'postfach' => $data['postfach'] ?? '',
            'postal_code' => $data['postal_code'] ?? '',
            'city' => $data['city'] ?? '',
            'country' => $data['country'] ?? 'AT',
            'vat_id' => $data['vat_id'] ?? '',
            'reference_number' => $data['reference_number'] ?? '',
            'salutation' => $data['salutation'] ?? '',
            'phone' => $data['phone'] ?? '',
            'newsletter' => !empty($data['newsletter']) ? 'yes' : 'no',
            'nexora_kind_user' => 'customer',
            'registration_date' => current_time('mysql'),
            'customer_status' => 'active'
        );
        foreach ($users_table_fields as $field => $value) {
            if (!empty($value)) {
                $wpdb->update(
                    $wpdb->users,
                    array($field => sanitize_text_field($value)),
                    array('ID' => $user_id),
                    array('%s'),
                    array('%d')
                );
                error_log("Nexora Service Suite save_user_meta - Updated wp_users.$field: $value");
            }
        }
        $meta_fields = array(
            'customer_type' => 'customer_type',
            'company_name' => 'company_name',
            'street' => 'street',
            'house_number' => 'house_number',
            'postfach' => 'postfach',
            'postal_code' => 'postal_code',
            'city' => 'city',
            'country' => 'country',
            'vat_id' => 'vat_id',
            'reference_number' => 'reference_number',
            'salutation' => 'salutation',
            'phone' => 'phone',
            'newsletter' => 'newsletter',
            'nexora_kind_user' => 'customer'
        );
        
        foreach ($meta_fields as $meta_key => $post_key) {
            if (isset($data[$post_key])) {
                $value = $data[$post_key];
                if ($post_key === 'newsletter') {
                    $value = !empty($value) ? 'yes' : 'no';
                }
                update_user_meta($user_id, $meta_key, sanitize_text_field($value));
                error_log("Nexora Service Suite save_user_meta - Saved meta $meta_key: $value");
            }
        }
        if (isset($data['customer_type']) && $data['customer_type'] === 'private') {
            $private_fields = array(
                'salutation_private' => $data['salutation_private'] ?? '',
                'first_name_private' => $data['first_name_private'] ?? '',
                'last_name_private' => $data['last_name_private'] ?? '',
                'street_private' => $data['street_private'] ?? '',
                'house_number_private' => $data['house_number_private'] ?? '',
                'postfach_private' => $data['postfach_private'] ?? '',
                'postal_code_private' => $data['postal_code_private'] ?? '',
                'city_private' => $data['city_private'] ?? '',
                'country_private' => $data['country_private'] ?? 'AT',
                'reference_number_private' => $data['reference_number_private'] ?? '',
                'phone_private' => $data['phone_private'] ?? '',
                'newsletter_private' => !empty($data['newsletter_private']) ? 'yes' : 'no',
                'terms_accepted_private' => !empty($data['terms_accepted_private']) ? 'yes' : 'no'
            );
            
            foreach ($private_fields as $field => $value) {
                if (!empty($value)) {
                    $wpdb->update(
                        $wpdb->users,
                        array($field => sanitize_text_field($value)),
                        array('ID' => $user_id),
                        array('%s'),
                        array('%d')
                    );
                    error_log("Nexora Service Suite save_user_meta - Updated wp_users.$field: $value");
                }
            }
        }
        
        if (isset($data['customer_type']) && $data['customer_type'] === 'business') {
            $business_fields = array(
                'salutation_business' => $data['salutation_business'] ?? '',
                'first_name_business' => $data['first_name_business'] ?? '',
                'last_name_business' => $data['last_name_business'] ?? '',
                'street_business' => $data['street_business'] ?? '',
                'house_number_business' => $data['house_number_business'] ?? '',
                'postfach_business' => $data['postfach_business'] ?? '',
                'postal_code_business' => $data['postal_code_business'] ?? '',
                'city_business' => $data['city_business'] ?? '',
                'country_business' => $data['country_business'] ?? 'AT',
                'phone_business' => $data['phone_business'] ?? '',
                'newsletter_business' => !empty($data['newsletter_business']) ? 'yes' : 'no',
                'terms_accepted_business' => !empty($data['terms_accepted_business']) ? 'yes' : 'no'
            );
            
            foreach ($business_fields as $field => $value) {
                if (!empty($value)) {
                    $wpdb->update(
                        $wpdb->users,
                        array($field => sanitize_text_field($value)),
                        array('ID' => $user_id),
                        array('%s'),
                        array('%d')
                    );
                    error_log("Nexora Service Suite save_user_meta - Updated wp_users.$field: $value");
                }
            }
        }
        
        error_log('Nexora Service Suite save_user_meta - All data saved to wp_users table successfully');
    }
    
    public function add_custom_user_fields($user) {
        ?>
        <h3>Kundeninformationen</h3>
        <table class="form-table">
            <tr>
                <th><label for="customer_type">Kundenart</label></th>
                <td>
                    <select name="customer_type" id="customer_type">
                        <option value="">Bitte auswählen</option>
                        <option value="business" <?php selected(get_user_meta($user->ID, 'customer_type', true), 'business'); ?>>Geschäftskunden</option>
                        <option value="private" <?php selected(get_user_meta($user->ID, 'customer_type', true), 'private'); ?>>Privatkunden</option>
                    </select>
                </td>
            </tr>
            <tr>
                <th><label for="company_name">Firmenname</label></th>
                <td>
                    <input type="text" name="company_name" id="company_name" value="<?php echo esc_attr(get_user_meta($user->ID, 'company_name', true)); ?>" class="regular-text" />
                </td>
            </tr>
            <tr>
                <th><label for="phone">Telefon</label></th>
                <td>
                    <input type="tel" name="phone" id="phone" value="<?php echo esc_attr(get_user_meta($user->ID, 'phone', true)); ?>" class="regular-text" />
                </td>
            </tr>
        </table>
        <?php
    }
    
    public function save_custom_user_fields($user_id) {
        if (!current_user_can('edit_user', $user_id)) {
            return false;
        }
        
        $meta_fields = array('customer_type', 'company_name', 'phone');
        
        foreach ($meta_fields as $field) {
            if (isset($_POST[$field])) {
                update_user_meta($user_id, $field, sanitize_text_field($_POST[$field]));
            }
        }
    }

    public function add_user_management_menu() {
        add_submenu_page(
            'Nexora Service Suite-service-manager',
            __('Benutzerverwaltung', 'Nexora Service Suite'),
            __('Benutzerverwaltung', 'Nexora Service Suite'),
            'manage_options',
            'Nexora Service Suite-user-management',
            array($this, 'render_user_management_page')
        );
    }

    public function render_user_management_page() {
        if (!current_user_can('manage_options')) {
            wp_die(__('You do not have sufficient permissions to access this page.'));
        }
        echo '<div class="wrap"><h1>' . __('Benutzerverwaltung', 'Nexora Service Suite') . '</h1>';
        echo '<div id="Nexora Service Suite-user-management-app"></div>';
        echo '</div>';
    }
    
    public function ajax_get_users() {
        if (!wp_verify_nonce($_POST['nonce'], 'nexora_nonce')) {
            wp_send_json_error('Nonce verification failed');
        }
        if (!current_user_can('manage_options')) {
            wp_send_json_error('Insufficient permissions');
        }
        
        $page = isset($_POST['page']) ? intval($_POST['page']) : 1;
        $per_page = isset($_POST['per_page']) ? intval($_POST['per_page']) : 10;
        $search = isset($_POST['search']) ? sanitize_text_field($_POST['search']) : '';
        $customer_type = isset($_POST['customer_type']) ? sanitize_text_field($_POST['customer_type']) : '';
        $order_by = isset($_POST['order_by']) ? sanitize_text_field($_POST['order_by']) : 'ID';
        $order_dir = isset($_POST['order_dir']) ? sanitize_text_field($_POST['order_dir']) : 'DESC';
        $allowed_order_by = ['ID', 'user_login', 'user_email', 'display_name', 'user_registered'];
        if (!in_array($order_by, $allowed_order_by)) {
            $order_by = 'ID';
        }
        $order_dir = strtoupper($order_dir) === 'ASC' ? 'ASC' : 'DESC';
        $offset = ($page - 1) * $per_page;
        global $wpdb;
        $query = "SELECT u.*, 
                         um_customer_type.meta_value as customer_type,
                         um_company.meta_value as company_name,
                         um_phone.meta_value as phone
                  FROM {$wpdb->users} u
                  LEFT JOIN {$wpdb->usermeta} um_customer_type ON u.ID = um_customer_type.user_id AND um_customer_type.meta_key = 'customer_type'
                  LEFT JOIN {$wpdb->usermeta} um_company ON u.ID = um_company.user_id AND um_company.meta_key = 'company_name'
                  LEFT JOIN {$wpdb->usermeta} um_phone ON u.ID = um_phone.user_id AND um_phone.meta_key = 'phone'";
        
        $where = [];
        $params = [];
        if (!empty($search)) {
            $where[] = "(u.user_login LIKE %s OR u.user_email LIKE %s OR u.display_name LIKE %s OR um_company.meta_value LIKE %s)";
            $params[] = "%{$search}%";
            $params[] = "%{$search}%";
            $params[] = "%{$search}%";
            $params[] = "%{$search}%";
        }
        if (!empty($customer_type)) {
            $where[] = "um_customer_type.meta_value = %s";
            $params[] = $customer_type;
        }
        
        if (!empty($where)) {
            $query .= " WHERE " . implode(" AND ", $where);
        }
        $count_query = "SELECT COUNT(1) FROM ({$query}) AS total";
        $total = $wpdb->get_var($wpdb->prepare($count_query, $params));
        $total = intval($total) ?: 0;
        $query .= " ORDER BY {$order_by} {$order_dir} LIMIT %d, %d";
        $params[] = $offset;
        $params[] = $per_page;
        
        $results = $wpdb->get_results($wpdb->prepare($query, $params));
        foreach ($results as &$user) {
            $user->user_registered_formatted = date('d.m.Y H:i', strtotime($user->user_registered));
            $user->customer_type_display = $user->customer_type ? ucfirst($user->customer_type) : 'Nicht gesetzt';
            $user->company_name_display = $user->company_name ?: 'Nicht gesetzt';
            $user->phone_display = $user->phone ?: 'Nicht gesetzt';
            $user_data = get_userdata($user->ID);
            $user->roles = $user_data ? $user_data->roles : ['subscriber'];
            global $wpdb;
            $user->customer_number = $wpdb->get_var($wpdb->prepare(
                "SELECT customer_number FROM {$wpdb->users} WHERE ID = %d",
                $user->ID
            ));
            $user->user_approved = get_user_meta($user->ID, 'user_approved', true);
        }
        error_log('Nexora Service Suite Users Query: ' . $query);
        error_log('Nexora Service Suite Users Params: ' . print_r($params, true));
        error_log('Nexora Service Suite Users Found: ' . count($results));
        error_log('Nexora Service Suite Users Total: ' . $total);
        
        $total_pages = ceil($total / $per_page);
        error_log('Nexora Service Suite Users Pagination Debug - Total: ' . $total . ', Per Page: ' . $per_page . ', Total Pages: ' . $total_pages);
        
        wp_send_json_success([
            'users' => $results,
            'total' => $total,
            'page' => $page,
            'per_page' => $per_page,
            'total_pages' => $total_pages
        ]);
    }

    public function get_customer_details() {
        if (!wp_verify_nonce($_POST['nonce'], 'nexora_nonce')) {
            wp_send_json_error('Nonce verification failed');
        }
        if (!current_user_can('manage_options')) {
            wp_send_json_error('Insufficient permissions');
        }
        
        $user_id = intval($_POST['user_id']);
        
        if (!$user_id) {
            wp_send_json_error('Invalid user ID');
        }
        $user = get_userdata($user_id);
        if (!$user) {
            wp_send_json_error('User not found');
        }
        global $wpdb;
        $customer_data = $wpdb->get_row($wpdb->prepare(
            "SELECT * FROM {$wpdb->users} WHERE ID = %d",
            $user_id
        ));
        $customer_type = $customer_data->customer_type ?? '';
        $company_name = $customer_data->company_name ?? '';
        $street = $customer_data->street ?? '';
        $house_number = $customer_data->house_number ?? '';
        $postfach = $customer_data->postfach ?? '';
        $postal_code = $customer_data->postal_code ?? '';
        $city = $customer_data->city ?? '';
        $country = $customer_data->country ?? '';
        $vat_id = $customer_data->vat_id ?? '';
        $reference_number = $customer_data->reference_number ?? '';
        $salutation = $customer_data->salutation ?? '';
        $phone = $customer_data->phone ?? '';
        $newsletter = $customer_data->newsletter ?? '';
        if ($customer_type === 'private') {
            $salutation_private = $customer_data->salutation_private ?? '';
            $first_name_private = $customer_data->first_name_private ?? '';
            $last_name_private = $customer_data->last_name_private ?? '';
            $street_private = $customer_data->street_private ?? '';
            $house_number_private = $customer_data->house_number_private ?? '';
            $postfach_private = $customer_data->postfach_private ?? '';
            $postal_code_private = $customer_data->postal_code_private ?? '';
            $city_private = $customer_data->city_private ?? '';
            $country_private = $customer_data->country_private ?? '';
            $reference_number_private = $customer_data->reference_number_private ?? '';
            $phone_private = $customer_data->phone_private ?? '';
            $newsletter_private = $customer_data->newsletter_private ?? '';
            $terms_accepted_private = $customer_data->terms_accepted_private ?? '';
        }
        
        if ($customer_type === 'business') {
            $salutation_business = $customer_data->salutation_business ?? '';
            $first_name_business = $customer_data->first_name_business ?? '';
            $last_name_business = $customer_data->last_name_business ?? '';
            $street_business = $customer_data->street_business ?? '';
            $house_number_business = $customer_data->house_number_business ?? '';
            $postfach_business = $customer_data->postfach_business ?? '';
            $postal_code_business = $customer_data->postal_code_business ?? '';
            $city_business = $customer_data->city_business ?? '';
            $country_business = $customer_data->country_business ?? '';
            $phone_business = $customer_data->phone_business ?? '';
            $newsletter_business = $customer_data->newsletter_business ?? '';
            $terms_accepted_business = $customer_data->terms_accepted_business ?? '';
        }
        ob_start();
        ?>
        <div class="customer-details-container">
            <div class="customer-section">
                <h3 style="border-bottom: 2px solid #0073aa; padding-bottom: 10px; margin-bottom: 20px;">Benutzerinformationen</h3>
                <div class="customer-info-grid" style="display: grid; grid-template-columns: 1fr 1fr; gap: 15px;">
                    <div><strong>Benutzername:</strong> <?php echo esc_html($user->user_login); ?></div>
                    <div><strong>E-Mail:</strong> <?php echo esc_html($user->user_email); ?></div>
                    <div><strong>Vorname:</strong> <?php echo esc_html($user->first_name); ?></div>
                    <div><strong>Nachname:</strong> <?php echo esc_html($user->last_name); ?></div>
                    <div><strong>Registrierungsdatum:</strong> <?php echo esc_html(date('Y-m-d H:i', strtotime($user->user_registered))); ?></div>
                    <div><strong>Rolle:</strong> <?php echo esc_html(implode(', ', $user->roles)); ?></div>
                </div>
            </div>
            
            <div class="customer-section" style="margin-top: 30px;">
                <h3 style="border-bottom: 2px solid #0073aa; padding-bottom: 10px; margin-bottom: 20px;">Kundeninformationen</h3>
                <div class="customer-info-grid" style="display: grid; grid-template-columns: 1fr 1fr; gap: 15px;">
                    <div><strong>Kundenart:</strong> 
                        <?php if ($customer_type): ?>
                            <span style="padding: 3px 8px; border-radius: 3px; font-size: 12px; font-weight: 600; 
                                <?php echo $customer_type == 'business' ? 'background: #e3f2fd; color: #1976d2;' : 'background: #f3e5f5; color: #7b1fa2;'; ?>">
                                <?php echo $customer_type == 'business' ? 'Geschäftskunden' : 'Privatkunden'; ?>
                            </span>
                        <?php else: ?>
                            <span style="color: #999;">-</span>
                        <?php endif; ?>
                    </div>
                    <div><strong>Anrede:</strong> <?php echo esc_html($salutation ?: '-'); ?></div>
                    <?php if ($company_name): ?>
                        <div><strong>Firmenname:</strong> <?php echo esc_html($company_name); ?></div>
                    <?php endif; ?>
                    <div><strong>Telefon:</strong> <?php echo esc_html($phone ?: '-'); ?></div>
                    <?php if ($reference_number): ?>
                        <div><strong>Referenznummer:</strong> <?php echo esc_html($reference_number); ?></div>
                    <?php endif; ?>
                    <?php if ($vat_id): ?>
                        <div><strong>Steuernummer:</strong> <?php echo esc_html($vat_id); ?></div>
                    <?php endif; ?>
                </div>
            </div>
            
            <div class="customer-section" style="margin-top: 30px;">
                <h3 style="border-bottom: 2px solid #0073aa; padding-bottom: 10px; margin-bottom: 20px;">Adresse</h3>
                <div class="customer-info-grid" style="display: grid; grid-template-columns: 1fr 1fr; gap: 15px;">
                    <div><strong>Straße:</strong> <?php echo esc_html($street ?: '-'); ?></div>
                    <?php if ($house_number): ?>
                        <div><strong>Hausnummer:</strong> <?php echo esc_html($house_number); ?></div>
                    <?php endif; ?>
                    <?php if ($postfach): ?>
                        <div><strong>Postfach:</strong> <?php echo esc_html($postfach); ?></div>
                    <?php endif; ?>
                    <div><strong>Postleitzahl:</strong> <?php echo esc_html($postal_code ?: '-'); ?></div>
                    <div><strong>Stadt:</strong> <?php echo esc_html($city ?: '-'); ?></div>
                    <div><strong>Land:</strong> <?php echo esc_html($country ?: '-'); ?></div>
                </div>
            </div>
            
            <?php if ($customer_type === 'private'): ?>
            <div class="customer-section" style="margin-top: 30px;">
                <h3 style="border-bottom: 2px solid #0073aa; padding-bottom: 10px; margin-bottom: 20px;">Privatkunde Details</h3>
                <div class="customer-info-grid" style="display: grid; grid-template-columns: 1fr 1fr; gap: 15px;">
                    <div><strong>Anrede:</strong> <?php echo esc_html($salutation_private ?: '-'); ?></div>
                    <div><strong>Vorname:</strong> <?php echo esc_html($first_name_private ?: '-'); ?></div>
                    <div><strong>Nachname:</strong> <?php echo esc_html($last_name_private ?: '-'); ?></div>
                    <div><strong>Straße:</strong> <?php echo esc_html($street_private ?: '-'); ?></div>
                    <div><strong>Hausnummer:</strong> <?php echo esc_html($house_number_private ?: '-'); ?></div>
                    <div><strong>Postfach:</strong> <?php echo esc_html($postfach_private ?: '-'); ?></div>
                    <div><strong>Postleitzahl:</strong> <?php echo esc_html($postal_code_private ?: '-'); ?></div>
                    <div><strong>Stadt:</strong> <?php echo esc_html($city_private ?: '-'); ?></div>
                    <div><strong>Land:</strong> <?php echo esc_html($country_private ?: '-'); ?></div>
                    <div><strong>Referenznummer:</strong> <?php echo esc_html($reference_number_private ?: '-'); ?></div>
                    <div><strong>Telefon:</strong> <?php echo esc_html($phone_private ?: '-'); ?></div>
                    <div><strong>Newsletter:</strong> 
                        <span style="color: <?php echo $newsletter_private === 'yes' ? 'green' : 'red'; ?>;">
                            <?php echo $newsletter_private === 'yes' ? 'Ja' : 'Nein'; ?>
                        </span>
                    </div>
                    <div><strong>Bedingungen akzeptiert:</strong> 
                        <span style="color: <?php echo $terms_accepted_private === 'yes' ? 'green' : 'red'; ?>;">
                            <?php echo $terms_accepted_private === 'yes' ? 'Ja' : 'Nein'; ?>
                        </span>
                    </div>
                </div>
            </div>
            <?php endif; ?>
            
            <?php if ($customer_type === 'business'): ?>
            <div class="customer-section" style="margin-top: 30px;">
                <h3 style="border-bottom: 2px solid #0073aa; padding-bottom: 10px; margin-bottom: 20px;">Geschäftskunde Details</h3>
                <div class="customer-info-grid" style="display: grid; grid-template-columns: 1fr 1fr; gap: 15px;">
                    <div><strong>Anrede:</strong> <?php echo esc_html($salutation_business ?: '-'); ?></div>
                    <div><strong>Vorname:</strong> <?php echo esc_html($first_name_business ?: '-'); ?></div>
                    <div><strong>Nachname:</strong> <?php echo esc_html($last_name_business ?: '-'); ?></div>
                    <div><strong>Straße:</strong> <?php echo esc_html($street_business ?: '-'); ?></div>
                    <div><strong>Hausnummer:</strong> <?php echo esc_html($house_number_business ?: '-'); ?></div>
                    <div><strong>Postfach:</strong> <?php echo esc_html($postfach_business ?: '-'); ?></div>
                    <div><strong>Postleitzahl:</strong> <?php echo esc_html($postal_code_business ?: '-'); ?></div>
                    <div><strong>Stadt:</strong> <?php echo esc_html($city_business ?: '-'); ?></div>
                    <div><strong>Land:</strong> <?php echo esc_html($country_business ?: '-'); ?></div>
                    <div><strong>Telefon:</strong> <?php echo esc_html($phone_business ?: '-'); ?></div>
                    <div><strong>Newsletter:</strong> 
                        <span style="color: <?php echo $newsletter_business === 'yes' ? 'green' : 'red'; ?>;">
                            <?php echo $newsletter_business === 'yes' ? 'Ja' : 'Nein'; ?>
                        </span>
                    </div>
                    <div><strong>Bedingungen akzeptiert:</strong> 
                        <span style="color: <?php echo $terms_accepted_business === 'yes' ? 'green' : 'red'; ?>;">
                            <?php echo $terms_accepted_business === 'yes' ? 'Ja' : 'Nein'; ?>
                        </span>
                    </div>
                </div>
            </div>
            <?php endif; ?>
            
            <div class="customer-section" style="margin-top: 30px;">
                <h3 style="border-bottom: 2px solid #0073aa; padding-bottom: 10px; margin-bottom: 20px;">System Information</h3>
                <div class="customer-info-grid" style="display: grid; grid-template-columns: 1fr 1fr; gap: 15px;">
                    <div><strong>Newsletter-Abonnement:</strong> 
                        <span style="color: <?php echo $newsletter === 'yes' ? 'green' : 'red'; ?>;">
                            <?php echo $newsletter === 'yes' ? 'Ja' : 'Nein'; ?>
                        </span>
                    </div>
                    <div><strong>Kundenstatus:</strong> <?php echo esc_html($customer_data->customer_status ?? 'Aktiv'); ?></div>
                    <div><strong>Registrierungsdatum:</strong> <?php echo esc_html($customer_data->registration_date ? date('Y-m-d H:i', strtotime($customer_data->registration_date)) : '-'); ?></div>
                    <div><strong>Letzte Aktualisierung:</strong> <?php echo esc_html($customer_data->last_updated ? date('Y-m-d H:i', strtotime($customer_data->last_updated)) : '-'); ?></div>
                </div>
            </div>
        </div>
        
        <style>
        .customer-details-container {
            padding: 20px;
        }
        .customer-section {
            background: #f9f9f9;
            padding: 20px;
            border-radius: 8px;
            margin-bottom: 20px;
        }
        .customer-info-grid div {
            padding: 8px 0;
            border-bottom: 1px solid #eee;
        }
        .customer-info-grid div:last-child {
            border-bottom: none;
        }
        </style>
        <?php
        $html = ob_get_clean();
        
        wp_send_json_success(array('html' => $html));
    }
    
    
    public function check_user_approval_status() {
        if (is_user_logged_in()) {
            $user_id = get_current_user_id();
            $user_approved = get_user_meta($user_id, 'user_approved', true);
            if (current_user_can('manage_options')) {
                return;
            }
            
            if ($user_approved === 'no') {
                wp_logout();
                wp_redirect(home_url('/?user_rejected=1'));
                exit;
            }
            
            if ($user_approved !== 'yes') {
                add_action('wp_footer', array($this, 'show_approval_waiting_message'));
            }
        }
    }
    
    
    public function show_approval_waiting_message() {
        if (is_user_logged_in()) {
            $user_id = get_current_user_id();
            $user_approved = get_user_meta($user_id, 'user_approved', true);
            
            if ($user_approved !== 'yes' && !current_user_can('manage_options')) {
                echo '<div id="Nexora Service Suite-approval-message" style="position: fixed; top: 0; left: 0; right: 0; background: #fff3cd; color: #856404; padding: 15px; text-align: center; border-bottom: 1px solid #ffeaa7; z-index: 9999; font-weight: bold;">';
                echo '⚠️ Ihr Konto wartet noch auf die Genehmigung durch den Administrator. Bitte haben Sie Geduld.';
                echo '</div>';
            }
        }
    }
    
    
    public function render_approval_message($atts) {
        $atts = shortcode_atts(array(
            'type' => 'info',
            'message' => 'Ihr Konto wartet noch auf die Genehmigung durch den Administrator. Bitte haben Sie Geduld.'
        ), $atts);
        
        $allowed_types = array('info', 'success', 'warning', 'error');
        if (!in_array($atts['type'], $allowed_types)) {
            $atts['type'] = 'info';
        }
        
        ob_start();
        ?>
        <div class="Nexora Service Suite-approval-message <?php echo esc_attr($atts['type']); ?>">
            <?php echo esc_html($atts['message']); ?>
        </div>
        <?php
        return ob_get_clean();
    }
    
    
    public function render_approval_banner($atts) {
        if (!is_user_logged_in()) {
            return '';
        }
        
        $user_id = get_current_user_id();
        $user_approved = get_user_meta($user_id, 'user_approved', true);
        if (current_user_can('manage_options')) {
            return '';
        }
        if ($user_approved === 'no') {
            return '<div class="Nexora Service Suite-approval-banner rejected" style="background: #f8d7da; color: #721c24; padding: 15px; margin: 20px 0; border: 1px solid #f5c6cb; border-radius: 4px; text-align: center; font-weight: bold; animation: fadeIn 0.3s ease-in;">❌ Ihr Konto wurde abgelehnt. Bitte kontaktieren Sie den Administrator.</div>';
        } elseif ($user_approved !== 'yes') {
            return '<div class="Nexora Service Suite-approval-banner pending" style="background: #fff3cd; color: #856404; padding: 15px; margin: 20px 0; border: 1px solid #ffeaa7; border-radius: 4px; text-align: center; font-weight: bold; animation: fadeIn 0.3s ease-in;">⚠️ Ihr Konto wartet noch auf die Genehmigung durch den Administrator. Bitte haben Sie Geduld.</div>';
        }
        
        return '';
    }
    
    
    public static function user_has_access() {
        if (!is_user_logged_in()) {
            return false;
        }
        
        $user_id = get_current_user_id();
        $user_approved = get_user_meta($user_id, 'user_approved', true);
        if (current_user_can('manage_options')) {
            return true;
        }
        return $user_approved === 'yes';
    }
    
    
    public static function get_approval_status_message($user_id = null) {
        if (!$user_id) {
            $user_id = get_current_user_id();
        }
        
        if (!$user_id) {
            return '';
        }
        
        $user_approved = get_user_meta($user_id, 'user_approved', true);
        
        switch ($user_approved) {
            case 'yes':
                return '';
            case 'no':
                return 'Ihr Konto wurde abgelehnt. Bitte kontaktieren Sie den Administrator.';
            case 'pending':
            default:
                return 'Ihr Konto wartet noch auf die Genehmigung durch den Administrator. Bitte haben Sie Geduld.';
        }
    }
    
    
    public function get_user_details() {
        if (!wp_verify_nonce($_POST['nonce'], 'nexora_nonce')) {
            wp_die('Sicherheitscheck fehlgeschlagen');
        }
        if (!current_user_can('manage_options')) {
            wp_die('Unzureichende Berechtigungen');
        }
        
        $user_id = intval($_POST['user_id']);
        $user = get_user_by('ID', $user_id);
        
        if (!$user) {
            wp_send_json_error('Benutzer nicht gefunden');
        }
        $customer_type = get_user_meta($user_id, 'customer_type', true);
        $company_name = get_user_meta($user_id, 'company_name', true);
        $phone = get_user_meta($user_id, 'phone', true);
        $city = get_user_meta($user_id, 'city', true);
        $street = get_user_meta($user_id, 'street', true);
        $postal_code = get_user_meta($user_id, 'postal_code', true);
        $user_approved = get_user_meta($user_id, 'user_approved', true);
        $benefit_type = get_user_meta($user_id, 'benefit_type', true);
        $discount_percentage = get_user_meta($user_id, 'discount_percentage', true);
        $commission_percentage = get_user_meta($user_id, 'commission_percentage', true);
        
        $user_data = array(
            'ID' => $user->ID,
            'user_login' => $user->user_login,
            'user_email' => $user->user_email,
            'first_name' => $user->first_name,
            'last_name' => $user->last_name,
            'display_name' => $user->display_name,
            'roles' => $user->roles,
            'customer_type' => $customer_type,
            'company_name' => $company_name,
            'phone' => $phone,
            'city' => $city,
            'street' => $street,
            'postal_code' => $postal_code,
            'user_approved' => $user_approved,
            'benefit_type' => $benefit_type,
            'discount_percentage' => $discount_percentage,
            'commission_percentage' => $commission_percentage
        );
        
        wp_send_json_success($user_data);
    }
    
    
    public function update_user() {
        if (!wp_verify_nonce($_POST['nonce'], 'nexora_nonce')) {
            wp_die('Sicherheitscheck fehlgeschlagen');
        }
        if (!current_user_can('manage_options')) {
            wp_die('Unzureichende Berechtigungen');
        }
        
        parse_str($_POST['data'], $form_data);
        
        $user_id = intval($form_data['user_id']);
        $user = get_user_by('ID', $user_id);
        
        if (!$user) {
            wp_send_json_error('Benutzer nicht gefunden');
        }
        $user_data = array(
            'ID' => $user_id,
            'user_login' => sanitize_text_field($form_data['user_login']),
            'user_email' => sanitize_email($form_data['user_email']),
            'first_name' => sanitize_text_field($form_data['first_name']),
            'last_name' => sanitize_text_field($form_data['last_name']),
            'display_name' => sanitize_text_field($form_data['display_name'])
        );
        if (!empty($form_data['user_pass'])) {
            $user_data['user_pass'] = $form_data['user_pass'];
        }
        
        $user_update_result = wp_update_user($user_data);
        
        if (is_wp_error($user_update_result)) {
            wp_send_json_error('Fehler beim Aktualisieren der Benutzerdaten: ' . $user_update_result->get_error_message());
        }
        if (!empty($form_data['role'])) {
            $user_obj = new WP_User($user_id);
            $user_obj->set_role($form_data['role']);
        }
        $meta_fields = array(
            'customer_type' => sanitize_text_field($form_data['customer_type']),
            'company_name' => sanitize_text_field($form_data['company_name']),
            'phone' => sanitize_text_field($form_data['phone']),
            'city' => sanitize_text_field($form_data['city']),
            'street' => sanitize_text_field($form_data['street']),
            'postal_code' => sanitize_text_field($form_data['postal_code']),
            'user_approved' => sanitize_text_field($form_data['user_approved']),
            'benefit_type' => sanitize_text_field($form_data['benefit_type']),
            'discount_percentage' => floatval($form_data['discount_percentage']),
            'commission_percentage' => floatval($form_data['commission_percentage'])
        );
        
        foreach ($meta_fields as $meta_key => $meta_value) {
            update_user_meta($user_id, $meta_key, $meta_value);
        }
        
        wp_send_json_success('Benutzer wurde erfolgreich aktualisiert');
    }

    
    public function update_payment_status() {
        if (!wp_verify_nonce($_POST['nonce'], 'update_payment_status')) {
            wp_send_json_error('Invalid nonce');
            return;
        }
        if (!current_user_can('manage_options')) {
            wp_send_json_error('Insufficient permissions');
            return;
        }

        $user_id = intval($_POST['user_id']);
        $status = sanitize_text_field($_POST['status']);

        if (!in_array($status, ['paid', 'not_paid'])) {
            wp_send_json_error('Invalid status');
            return;
        }
        update_user_meta($user_id, 'payment_status', $status);
        $current_user = wp_get_current_user();
        $log_data = array(
            'user_id' => $user_id,
            'status' => $status,
            'changed_by' => $current_user->ID,
            'changed_at' => current_time('mysql'),
            'changed_by_name' => $current_user->display_name,
            'changed_by_email' => $current_user->user_email
        );
        
        update_user_meta($user_id, 'payment_status_log', $log_data);

        wp_send_json_success('Payment status updated successfully');
    }

    
    public function get_user_logs() {
        if (!wp_verify_nonce($_POST['nonce'], 'get_user_logs')) {
            wp_send_json_error('Invalid nonce');
            return;
        }
        if (!current_user_can('manage_options')) {
            wp_send_json_error('Insufficient permissions');
            return;
        }

        $user_id = intval($_POST['user_id']);
        $logs = array();
        $user = get_user_by('ID', $user_id);
        if (!$user) {
            wp_send_json_error('User not found');
            return;
        }
        $payment_log = get_user_meta($user_id, 'payment_status_log', true);
        if ($payment_log && is_array($payment_log)) {
            $status_text = $payment_log['status'] === 'paid' ? 'Bezahlt' : 'Nicht bezahlt';
            $logs[] = array(
                'type' => 'payment',
                'message' => "Zahlungsstatus geändert zu: {$status_text}",
                'timestamp' => $payment_log['changed_at'],
                'admin' => $payment_log['changed_by_name'],
                'details' => "Admin: {$payment_log['changed_by_name']} ({$payment_log['changed_by_email']}) - ID: {$payment_log['changed_by']}"
            );
        }
        $benefit_type = get_user_meta($user_id, 'benefit_type', true);
        if ($benefit_type) {
            $benefit_text = $benefit_type === 'discount' ? 'Rabatt' : 'Provision';
            $logs[] = array(
                'type' => 'benefit',
                'message' => "Vorteil-Typ gesetzt: {$benefit_text}",
                'timestamp' => $user->user_registered,
                'admin' => 'System',
                'details' => 'Benutzer registriert'
            );
        }
        $discount_percentage = get_user_meta($user_id, 'discount_percentage', true);
        if ($discount_percentage && $benefit_type === 'discount') {
            $logs[] = array(
                'type' => 'benefit',
                'message' => "Rabatt-Prozentsatz gesetzt: {$discount_percentage}%",
                'timestamp' => $user->user_registered,
                'admin' => 'System',
                'details' => 'Benutzer registriert'
            );
        }
        $commission_percentage = get_user_meta($user_id, 'commission_percentage', true);
        if ($commission_percentage && $benefit_type === 'commission') {
            $logs[] = array(
                'type' => 'benefit',
                'message' => "Provision-Prozentsatz gesetzt: {$commission_percentage}%",
                'timestamp' => $user->user_registered,
                'admin' => 'System',
                'details' => 'Benutzer registriert'
            );
        }
        $logs[] = array(
            'type' => 'user',
            'message' => "Benutzer erstellt: {$user->user_login}",
            'timestamp' => $user->user_registered,
            'admin' => 'System',
            'details' => "Email: {$user->user_email}"
        );
        global $wpdb;
        $requests_table = $wpdb->prefix . 'nexora_service_requests';
        
        $requests = $wpdb->get_results($wpdb->prepare("
            SELECT id, description, created_at, status, total_cost
            FROM {$requests_table}
            WHERE user_id = %d
            ORDER BY created_at DESC
        ", $user_id));

        if ($requests) {
            foreach ($requests as $request) {
                $status_text = '';
                switch ($request->status) {
                    case 'pending':
                        $status_text = 'Ausstehend';
                        break;
                    case 'in_progress':
                        $status_text = 'In Bearbeitung';
                        break;
                    case 'completed':
                        $status_text = 'Abgeschlossen';
                        break;
                    case 'cancelled':
                        $status_text = 'Storniert';
                        break;
                    default:
                        $status_text = ucfirst($request->status);
                }

                $logs[] = array(
                    'type' => 'request',
                    'message' => "Service-Anfrage erstellt: #{$request->id}",
                    'timestamp' => $request->created_at,
                    'admin' => 'Benutzer',
                    'details' => "Status: {$status_text} | Kosten: €{$request->total_cost} | Beschreibung: " . substr($request->description, 0, 50) . (strlen($request->description) > 50 ? '...' : '')
                );
            }
        }
        usort($logs, function($a, $b) {
            return strtotime($b['timestamp']) - strtotime($a['timestamp']);
        });

        wp_send_json_success($logs);
    }
} 