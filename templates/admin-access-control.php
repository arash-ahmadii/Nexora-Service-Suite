<?php
if (!defined('ABSPATH')) exit;
if (!current_user_can('manage_options')) {
    wp_die('Zugriff verweigert. Sie benötigen Administrator-Rechte.');
}
?> 