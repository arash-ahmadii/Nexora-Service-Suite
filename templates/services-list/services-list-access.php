<?php
if (class_exists('Nexora_User_Registration')) {
    if (!Nexora_User_Registration::user_has_access()) {
        $approval_message = Nexora_User_Registration::get_approval_status_message();
        if ($approval_message) {
            echo '<div class="Nexora Service Suite-approval-message error" style="background: #ffebee; color: #c62828; padding: 15px; border-radius: 4px; margin: 20px 0; border: 1px solid #ef9a9a;">';
            echo '<strong>Zugriff verweigert:</strong> ' . esc_html($approval_message);
            echo '</div>';
            return;
        }
    }
} 