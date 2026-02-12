<?php

trait UserRegistration_AJAX {
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
        error_log('🔐 LOGIN HANDLER CALLED - POST data: ' . print_r($_POST, true));
        check_ajax_referer('nexora_auth_nonce', 'auth_nonce');
        $response = array('success' => false, 'message' => '');
        $login_identifier = sanitize_text_field($_POST['email']);
        $password = $_POST['password'];
        $remember = !empty($_POST['remember']);
        if (empty($login_identifier) || empty($password)) {
            $response['message'] = 'E-Mail/Benutzername und Passwort sind erforderlich';
            error_log('🔐 LOGIN ERROR - Empty email/username or password');
            wp_send_json($response);
        }
        $user = get_user_by('email', $login_identifier);
        if (!$user) {
            $user = get_user_by('login', $login_identifier);
        }
        if (!$user) {
            $response['message'] = 'E-Mail/Benutzername oder Passwort falsch';
            error_log('🔐 LOGIN ERROR - User not found for identifier: ' . $login_identifier);
            wp_send_json($response);
        }
        if (!wp_check_password($password, $user->user_pass, $user->ID)) {
            $response['message'] = 'E-Mail oder Passwort falsch';
            error_log('🔐 LOGIN ERROR - Invalid password for user: ' . $user->ID);
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
        error_log('🔐 LOGIN SUCCESS - Response: ' . print_r($response, true));
        wp_send_json($response);
    }
    public function get_customer_details() {
        check_ajax_referer('nexora_auth_nonce', 'auth_nonce');
        $user_id = get_current_user_id();
        if (!$user_id) {
            wp_send_json(array('success' => false, 'message' => 'Nicht eingeloggt'));
        }
        global $wpdb;
        $customer_data = $wpdb->get_row($wpdb->prepare(
            "SELECT * FROM {$wpdb->users} WHERE ID = %d",
            $user_id
        ));
        
        if (!$customer_data) {
            wp_send_json(array('success' => false, 'message' => 'Benutzer nicht gefunden'));
        }
        
        $customer_info = array(
            'first_name' => $customer_data->first_name ?? '',
            'last_name' => $customer_data->last_name ?? '',
            'email' => $customer_data->user_email ?? '',
            'customer_type' => $customer_data->customer_type ?? '',
            'company_name' => $customer_data->company_name ?? '',
            'street' => $customer_data->street ?? '',
            'house_number' => $customer_data->house_number ?? '',
            'postfach' => $customer_data->postfach ?? '',
            'postal_code' => $customer_data->postal_code ?? '',
            'city' => $customer_data->city ?? '',
            'country' => $customer_data->country ?? '',
            'vat_id' => $customer_data->vat_id ?? '',
            'reference_number' => $customer_data->reference_number ?? '',
            'salutation' => $customer_data->salutation ?? '',
            'phone' => $customer_data->phone ?? '',
            'newsletter' => $customer_data->newsletter ?? '',
            'nexora_kind_user' => $customer_data->nexora_kind_user ?? '',
            'registration_date' => $customer_data->registration_date ?? '',
            'customer_status' => $customer_data->customer_status ?? ''
        );
        if ($customer_data->customer_type === 'private') {
            $customer_info['salutation_private'] = $customer_data->salutation_private ?? '';
            $customer_info['first_name_private'] = $customer_data->first_name_private ?? '';
            $customer_info['last_name_private'] = $customer_data->last_name_private ?? '';
            $customer_info['street_private'] = $customer_data->street_private ?? '';
            $customer_info['house_number_private'] = $customer_data->house_number_private ?? '';
            $customer_info['postfach_private'] = $customer_data->postfach_private ?? '';
            $customer_info['postal_code_private'] = $customer_data->postal_code_private ?? '';
            $customer_info['city_private'] = $customer_data->city_private ?? '';
            $customer_info['country_private'] = $customer_data->country_private ?? '';
            $customer_info['reference_number_private'] = $customer_data->reference_number_private ?? '';
            $customer_info['phone_private'] = $customer_data->phone_private ?? '';
            $customer_info['newsletter_private'] = $customer_data->newsletter_private ?? '';
            $customer_info['terms_accepted_private'] = $customer_data->terms_accepted_private ?? '';
        }
        
        if ($customer_data->customer_type === 'business') {
            $customer_info['salutation_business'] = $customer_data->salutation_business ?? '';
            $customer_info['first_name_business'] = $customer_data->first_name_business ?? '';
            $customer_info['last_name_business'] = $customer_data->last_name_business ?? '';
            $customer_info['street_business'] = $customer_data->street_business ?? '';
            $customer_info['house_number_business'] = $customer_data->house_number_business ?? '';
            $customer_info['postfach_business'] = $customer_data->postfach_business ?? '';
            $customer_info['postal_code_business'] = $customer_data->postal_code_business ?? '';
            $customer_info['city_business'] = $customer_data->city_business ?? '';
            $customer_info['country_business'] = $customer_data->country_business ?? '';
            $customer_info['phone_business'] = $customer_data->phone_business ?? '';
            $customer_info['newsletter_business'] = $customer_data->newsletter_business ?? '';
            $customer_info['terms_accepted_business'] = $customer_data->terms_accepted_business ?? '';
        }
        
        wp_send_json(array('success' => true, 'customer_info' => $customer_info));
    }
} 