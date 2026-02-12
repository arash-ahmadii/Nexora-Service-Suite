<?php
if (!defined('ABSPATH')) {
    exit;
}
?>
<div class="wrap Nexora Service Suite-admin">
    <?php
    $admin_menu = new Nexora_Admin_Menu();
    $admin_menu->render_admin_header();
    ?>
    <div class="Nexora Service Suite-admin-content">
        <div class="Nexora Service Suite-page-header">
            <h1 class="wp-heading-inline">🔧 System Repair</h1>
            <p class="description">Comprehensive testing and repair system for all plugin components</p>
        </div>
        <div class="notice notice-info" style="margin: 15px 0;">
            <p><strong>💡 Tip:</strong> Use the <strong>"QUICK REGISTRATION DIAGNOSTIC"</strong> for fast diagnosis of registration issues, or the <strong>"COMPREHENSIVE SYSTEM TEST"</strong> for complete system analysis.</p>
        </div> 