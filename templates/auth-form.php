<?php

if (!defined('ABSPATH')) {
    exit;
}
$redirect_url = isset($_GET['redirect']) ? $_GET['redirect'] : home_url('/my-account/');
if (is_user_logged_in()) {
    wp_redirect($redirect_url);
    exit;
}
?>

<!DOCTYPE html>
<html <?php language_attributes(); ?>>
<head>
    <meta charset="<?php bloginfo('charset'); ?>">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?php wp_title('|', true, 'right'); ?></title>
    <?php wp_head(); ?>
</head>
<body <?php body_class(); ?>>
    <div class="Nexora Service Suite-auth-page">
        <div class="Nexora Service Suite-auth-wrapper">
            <div class="Nexora Service Suite-auth-header">
                <div style="display: flex; justify-content: space-between; align-items: center;">
                    <h1><?php echo get_bloginfo('name'); ?></h1>
                </div>
                <p id="Nexora Service Suite-auth-title"><?php _e('Registrierung und Anmeldung', 'Nexora Service Suite'); ?></p>
            </div>
            
            <div class="Nexora Service Suite-auth-tabs">
                <?php if ($atts['mode'] === 'both' || $atts['mode'] === 'login'): ?>
                <button class="Nexora Service Suite-tab-btn active" data-tab="login">Einloggen</button>
                <?php endif; ?>
                <?php if ($atts['mode'] === 'both' || $atts['mode'] === 'register'): ?>
                <button class="Nexora Service Suite-tab-btn" data-tab="register">Registrieren</button>
                <?php endif; ?>
                <button type="button" class="Nexora Service Suite-lang-switcher" id="Nexora Service Suite-lang-switcher"><span id="Nexora Service Suite-current-lang">DE</span> / EN</button>
                <button id="test-lang-btn">TEST LANG BTN</button>
            </div>
            <div class="Nexora Service Suite-register-progress" style="display:none;">
                <div class="Nexora Service Suite-register-progress-segment" data-step="1"></div>
                <div class="Nexora Service Suite-register-progress-segment" data-step="2"></div>
            </div>
            
            <?php echo do_shortcode('[nexora_approval_banner]'); ?>
            
            <?php echo do_shortcode('[nexora_auth_form mode="both" redirect="' . esc_attr($redirect_url) . '"]'); ?>
            
            <div class="Nexora Service Suite-auth-footer">
                <p>&copy; <?php echo date('Y'); ?> <?php echo get_bloginfo('name'); ?>. Alle Rechte vorbehalten.</p>
            </div>
        </div>
    </div>
    
    <?php wp_footer(); ?>
</body>
</html>
