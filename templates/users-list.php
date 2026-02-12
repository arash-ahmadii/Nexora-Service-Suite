<?php
$args = array(
    'number' => -1,
    'orderby' => 'ID',
    'order' => 'DESC',
);
if (isset($_GET['customer_type']) && !empty($_GET['customer_type'])) {
    $args['meta_query'] = array(
        array(
            'key' => 'customer_type',
            'value' => sanitize_text_field($_GET['customer_type']),
            'compare' => '='
        )
    );
}

$users = get_users($args);
global $wp_roles;
$all_caps = [];
foreach ($wp_roles->roles as $role) {
    if (!empty($role['capabilities'])) {
        $all_caps = array_merge($all_caps, array_keys($role['capabilities']));
    }
}
$all_caps = array_unique($all_caps);
global $wpdb;
$customer_stats = $wpdb->get_results("
    SELECT 
        customer_type, 
        COUNT(*) as count 
    FROM {$wpdb->prefix}nexora_customer_info 
    GROUP BY customer_type
");
?>
<!DOCTYPE html>
<html lang="de">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Benutzerverwaltung - Nexora Service Suite</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
</head>
<body style="margin: 0; padding: 0; font-family: 'Inter', Arial, sans-serif; background: #0B0F19; color: #FFFFFF; overflow-x: hidden; min-height: 100vh;">

    
    
    <nav class="vertical-nav">
        <div class="nav-toggle">
            <i class="fas fa-bars"></i>
            <span class="nav-label">Menü</span>
        </div>
        
        <ul class="nav-menu">
            <li class="nav-item">
                <a href="<?php echo admin_url('admin.php?page=Nexora Service Suite-main'); ?>" class="nav-link" title="Dashboard">
                    <i class="fas fa-tachometer-alt"></i>
                    <span class="nav-text">Dashboard</span>
                </a>
            </li>
            
            <li class="nav-item">
                <a href="<?php echo admin_url('admin.php?page=Nexora Service Suite-services'); ?>" class="nav-link" title="Dienstleistungen">
                    <i class="fas fa-cogs"></i>
                    <span class="nav-text">Dienstleistungen</span>
                </a>
            </li>
            
            <li class="nav-item">
                <a href="<?php echo admin_url('admin.php?page=Nexora Service Suite-service-status'); ?>" class="nav-link" title="Status">
                    <i class="fas fa-tasks"></i>
                    <span class="nav-text">Status</span>
                </a>
            </li>
            
            <li class="nav-item">
                <a href="<?php echo admin_url('admin.php?page=Nexora Service Suite-service-request'); ?>" class="nav-link" title="Anfragen" id="nav-anfragen">
                    <i class="fas fa-clipboard-list"></i>
                    <span class="nav-text">Anfragen</span>
                    <span class="nav-badge awaiting-mod" id="nav-anfragen-badge" style="display: none;">0</span>
                </a>
            </li>
            
            <li class="nav-item">
                <a href="<?php echo admin_url('admin.php?page=Nexora Service Suite-users'); ?>" class="nav-link active" title="Benutzer" id="nav-benutzer">
                    <i class="fas fa-users"></i>
                    <span class="nav-text">Benutzer</span>
                    <span class="nav-badge awaiting-mod" id="nav-benutzer-badge" style="display: none;">0</span>
                </a>
            </li>
            
            <li class="nav-item">
                <a href="<?php echo admin_url('admin.php?page=Nexora Service Suite-eltern'); ?>" class="nav-link" title="Eltern">
                    <i class="fas fa-hand-holding-usd"></i>
                    <span class="nav-text">Eltern</span>
                </a>
            </li>
            
            <li class="nav-item">
                <a href="<?php echo admin_url('admin.php?page=Nexora Service Suite-device-manager'); ?>" class="nav-link" title="Geräteverwaltung">
                    <i class="fas fa-mobile-alt"></i>
                    <span class="nav-text">Geräteverwaltung</span>
                </a>
            </li>
            
            <li class="nav-item">
                <a href="<?php echo admin_url('admin.php?page=Nexora Service Suite-admin-notifications'); ?>" class="nav-link" title="Nachrichten" id="nav-nachrichten">
                    <i class="fas fa-bell"></i>
                    <span class="nav-text">Nachrichten</span>
                    <span class="nav-badge awaiting-mod" id="nav-nachrichten-badge" style="display: none;">0</span>
                </a>
            </li>
            
            <li class="nav-item">
                <a href="<?php echo admin_url('admin.php?page=Nexora Service Suite-email-management'); ?>" class="nav-link" title="Email-Verwaltung">
                    <i class="fas fa-envelope"></i>
                    <span class="nav-text">Email-Verwaltung</span>
                </a>
            </li>
        </ul>
    </nav>

    
    <div class="dashboard-container" style="margin-left: 80px;">
        
        <div class="dashboard-topbar">
            <div class="breadcrumb">
                <i class="fas fa-home"></i> Dashboard / Benutzerverwaltung
            </div>
            <div class="topbar-right">
                <div class="search-box">
                    <i class="fas fa-search"></i>
                    <input type="text" id="topbar-user-search" placeholder="Suchen...">
                </div>
                <div class="user-menu">
                    <i class="fas fa-user-circle"></i>
                    <span>Admin</span>
                </div>
            </div>
        </div>

        
        <div class="page-content glass-card">
            <div class="page-header">
                <h1 class="page-title">Benutzerverwaltung</h1>
                <div class="page-actions">
                    <a href="#" class="btn btn-primary" id="Nexora Service Suite-add-user">Neuen Benutzer hinzufügen</a>
                    <a href="#" class="btn btn-secondary" id="Nexora Service Suite-add-role">Neue Rolle hinzufügen</a>
                </div>
            </div>
    
            
            <div class="stats-container glass-card">
                <div class="stats-header">
                    <h3>Kundenstatistiken</h3>
                </div>
                <div class="stats-grid">
                    <?php foreach ($customer_stats as $stat): ?>
                        <div class="stat-card">
                            <div class="stat-number"><?php echo esc_html($stat->count); ?></div>
                            <div class="stat-label">
                                <?php 
                                echo $stat->customer_type == 'business' ? 'Geschäftskunden' : 'Privatkunden';
                                ?>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
    
            
            <div class="filters-container glass-card">
                <div class="filters-left">
                    <div class="search-box">
                        <input type="text" id="Nexora Service Suite-user-search" placeholder="Benutzer suchen..." autocomplete="off">
                        <button type="button" class="search-btn">
                            <span class="search-icon">🔍</span>
                        </button>
                    </div>
                    
                    <form method="GET" class="filter-form">
                        <input type="hidden" name="page" value="Nexora Service Suite-users">
                        
                        <label>Filter nach Kundentyp:</label>
                        <select id="customer-type-filter" class="filter-select">
                            <option value="">Alle Kunden</option>
                            <option value="business">Geschäftskunden</option>
                            <option value="private">Privatkunden</option>
                        </select>
                        
                        <?php if (isset($_GET['customer_type']) && !empty($_GET['customer_type'])): ?>
                            <a href="?page=Nexora Service Suite-users" class="btn btn-outline">Filter entfernen</a>
                        <?php endif; ?>
                    </form>
                </div>
                
                <div class="filters-right">
                    <button type="button" id="generate-customer-numbers-btn" class="btn btn-primary">
                        🔢 Kundennummern generieren
                    </button>
                </div>
            </div>
    
            
            <div class="table-container glass-card">
                <div class="table-header">
                    <div class="table-info">
                        <span id="user-table-info"><?php echo count($users); ?> Benutzer geladen</span>
                    </div>
                </div>
                <div class="table-wrapper">
                    <table class="users-table">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Benutzername</th>
                                <th>E-Mail</th>
                                <th>Kundennummer</th>
                                <th>Kundentyp</th>
                                <th>Rolle</th>
                                <th>Status</th>
                                <th>Aktionen</th>
                            </tr>
                        </thead>
                        <tbody id="Nexora Service Suite-user-list">
                            <?php foreach ($users as $user): ?>
                                <?php 
                                $approved = get_user_meta($user->ID, 'user_approved', true);
                                $customer_type = get_user_meta($user->ID, 'customer_type', true);
                                ?>
                                <tr>
                                    <td><strong>#<?php echo esc_html($user->ID); ?></strong></td>
                                    <td><?php echo esc_html($user->user_login); ?></td>
                                    <td><?php echo esc_html($user->user_email); ?></td>
                                    <td>
                                        <?php 
                                        global $wpdb;
                                        $customer_number = $wpdb->get_var($wpdb->prepare(
                                            "SELECT customer_number FROM {$wpdb->users} WHERE ID = %d",
                                            $user->ID
                                        ));
                                        ?>
                                        <input type="text" class="customer-number-input" 
                                               value="<?php echo esc_attr($customer_number ?: ''); ?>" 
                                               data-user-id="<?php echo esc_attr($user->ID); ?>"
                                               data-original-value="<?php echo esc_attr($customer_number ?: ''); ?>"
                                               placeholder="Wird automatisch generiert">
                                    </td>
                                    <td>
                                        <select class="user-customer-type-select" data-user-id="<?php echo esc_attr($user->ID); ?>">
                                            <option value="" <?php selected($customer_type, ''); ?>>-</option>
                                            <option value="business" <?php selected($customer_type, 'business'); ?>>Geschäftlich</option>
                                            <option value="private" <?php selected($customer_type, 'private'); ?>>Privat</option>
                                        </select>
                                    </td>
                                    <td>
                                        <select class="user-role-select" data-user-id="<?php echo esc_attr($user->ID); ?>">
                                            <option value="subscriber" <?php selected(in_array('subscriber', $user->roles), true); ?>>Abonnent</option>
                                            <option value="customer" <?php selected(in_array('customer', $user->roles), true); ?>>Kunde</option>
                                            <option value="editor" <?php selected(in_array('editor', $user->roles), true); ?>>Redakteur</option>
                                            <option value="author" <?php selected(in_array('author', $user->roles), true); ?>>Autor</option>
                                            <option value="contributor" <?php selected(in_array('contributor', $user->roles), true); ?>>Mitarbeiter</option>
                                            <option value="shop_manager" <?php selected(in_array('shop_manager', $user->roles), true); ?>>Shop-Manager</option>
                                            <option value="administrator" <?php selected(in_array('administrator', $user->roles), true); ?>>Administrator</option>
                                        </select>
                                    </td>
                                    <td>
                                        <select class="user-status-select" data-user-id="<?php echo esc_attr($user->ID); ?>">
                                            <option value="" <?php selected($approved, ''); ?>>Ausstehend</option>
                                            <option value="yes" <?php selected($approved, 'yes'); ?>>Genehmigt</option>
                                            <option value="no" <?php selected($approved, 'no'); ?>>Abgelehnt</option>
                                        </select>
                                    </td>
                                    <td class="actions-cell">
                                        <div class="actions-container">
                                            <button type="button" class="action-btn edit-btn Nexora Service Suite-edit-user" data-user-id="<?php echo esc_attr($user->ID); ?>" title="Bearbeiten">
                                                <i class="fas fa-edit"></i>
                                            </button>
                                            <button type="button" class="action-btn view-btn Nexora Service Suite-view-customer" data-user-id="<?php echo esc_attr($user->ID); ?>" title="Ansehen">
                                                <i class="fas fa-eye"></i>
                                            </button>
                                            <button type="button" class="action-btn delete-btn Nexora Service Suite-delete-user" data-user-id="<?php echo esc_attr($user->ID); ?>" title="Löschen">
                                                <i class="fas fa-trash"></i>
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
                <div class="pagination">
                    <button id="Nexora Service Suite-prev-page" class="btn btn-outline" disabled>← Vorherige</button>
                    <span class="pagination-info" id="Nexora Service Suite-page-info">Seite 1 von 1</span>
                    <button id="Nexora Service Suite-next-page" class="btn btn-outline" disabled>Nächste →</button>
                </div>
            </div>
    
    
    <div id="Nexora Service Suite-customer-details-modal" class="Nexora Service Suite-modal" style="display:none;">
        <div class="Nexora Service Suite-modal-content" style="max-width: 800px;">
            <span class="Nexora Service Suite-close-modal">&times;</span>
            <h2>Vollständige Kundeninformationen</h2>
            <div id="Nexora Service Suite-customer-details-content">
                
            </div>
        </div>
    </div>
    
    
    <div id="Nexora Service Suite-user-modal" class="Nexora Service Suite-modal" style="display:none;">
        <div class="Nexora Service Suite-modal-content">
            <span class="Nexora Service Suite-close-modal">&times;</span>
            <h2>Neuen Benutzer hinzufügen</h2>
            <form id="Nexora Service Suite-add-user-form">
                <p>
                    <label>Benutzername *</label>
                    <input type="text" name="user_login" required>
                </p>
                <p>
                    <label>E-Mail *</label>
                    <input type="email" name="user_email" required>
                </p>
                <p>
                    <label>Passwort *</label>
                    <input type="password" name="user_pass" required>
                </p>
                <p>
                    <label>Rolle</label>
                    <select name="role">
                        <option value="subscriber">Abonnent</option>
                        <option value="customer">Kunde</option>
                        <option value="editor">Redakteur</option>
                        <option value="author">Autor</option>
                        <option value="contributor">Mitarbeiter</option>
                        <option value="shop_manager">Shop-Manager</option>
                    </select>
                </p>
                <p>
                    <button type="submit" class="button button-primary">Hinzufügen</button>
                </p>
            </form>
            <div id="Nexora Service Suite-add-user-message"></div>
        </div>
    </div>
    
    <div id="Nexora Service Suite-edit-user-modal" class="Nexora Service Suite-modal" style="display:none;">
        <div class="Nexora Service Suite-modal-content compact-modal">
            <div class="Nexora Service Suite-modal-header compact-modal-header">
                <span class="Nexora Service Suite-close-modal">&times;</span>
                <h2>Benutzer bearbeiten</h2>
            </div>
            <div class="Nexora Service Suite-modal-body compact-modal-body">
                <form id="Nexora Service Suite-edit-user-form">
                    <input type="hidden" name="user_id" id="edit_user_id">
                    <div class="form-group">
                        <label for="edit_user_login">Benutzername *</label>
                        <input type="text" name="user_login" id="edit_user_login" required>
                    </div>
                    <div class="form-group">
                        <label for="edit_user_email">E-Mail *</label>
                        <input type="email" name="user_email" id="edit_user_email" required>
                    </div>
                    <div class="form-group">
                        <label for="edit_user_first_name">Vorname</label>
                        <input type="text" name="first_name" id="edit_user_first_name">
                    </div>
                    <div class="form-group">
                        <label for="edit_user_last_name">Nachname</label>
                        <input type="text" name="last_name" id="edit_user_last_name">
                    </div>
                    <div class="form-group">
                        <label for="edit_user_display_name">Anzeigename</label>
                        <input type="text" name="display_name" id="edit_user_display_name">
                    </div>
                    <div class="form-group">
                        <label for="edit_user_role">Rolle</label>
                        <select name="role" id="edit_user_role">
                            <option value="subscriber">Abonnent</option>
                            <option value="customer">Kunde</option>
                            <option value="editor">Redakteur</option>
                            <option value="author">Autor</option>
                            <option value="contributor">Mitarbeiter</option>
                            <option value="shop_manager">Shop-Manager</option>
                            <option value="administrator">Administrator</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label for="edit_user_customer_type">Kundentyp</label>
                        <select name="customer_type" id="edit_user_customer_type">
                            <option value="">Bitte wählen...</option>
                            <option value="business">Geschäftskunde</option>
                            <option value="private">Privatkunde</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label for="edit_user_company_name">Unternehmen</label>
                        <input type="text" name="company_name" id="edit_user_company_name">
                    </div>
                    <div class="form-group">
                        <label for="edit_user_phone">Telefon</label>
                        <input type="tel" name="phone" id="edit_user_phone">
                    </div>
                    <div class="form-group">
                        <label for="edit_user_city">Stadt</label>
                        <input type="text" name="city" id="edit_user_city">
                    </div>
                    <div class="form-group">
                        <label for="edit_user_street">Straße & Hausnummer</label>
                        <input type="text" name="street" id="edit_user_street">
                    </div>
                    <div class="form-group">
                        <label for="edit_user_postal_code">PLZ</label>
                        <input type="text" name="postal_code" id="edit_user_postal_code">
                    </div>
                    <div class="form-group">
                        <label for="edit_user_approved">Genehmigungsstatus</label>
                        <select name="user_approved" id="edit_user_approved">
                            <option value="">Ausstehend</option>
                            <option value="yes">Genehmigt</option>
                            <option value="no">Abgelehnt</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label for="edit_user_benefit_type">Vorteil-Typ</label>
                        <select name="benefit_type" id="edit_user_benefit_type">
                            <option value="">Kein Vorteil</option>
                            <option value="discount">Rabatt</option>
                            <option value="commission">Provision</option>
                        </select>
                        <small style="color: #888; font-size: 12px;">Wählen Sie den Vorteil-Typ für diesen Benutzer</small>
                    </div>
                    <div class="form-group" id="discount-percentage-group" style="display: none;">
                        <label for="edit_user_discount_percentage">Rabatt-Prozentsatz (%)</label>
                        <input type="number" name="discount_percentage" id="edit_user_discount_percentage" 
                               min="0" max="100" step="0.01" placeholder="0.00">
                        <small style="color: #888; font-size: 12px;">Rabatt wird auf alle Service-Anfragen dieses Benutzers angewendet</small>
                    </div>
                    <div class="form-group" id="commission-percentage-group" style="display: none;">
                        <label for="edit_user_commission_percentage">Provision-Prozentsatz (%)</label>
                        <input type="number" name="commission_percentage" id="edit_user_commission_percentage" 
                               min="0" max="100" step="0.01" placeholder="0.00">
                        <small style="color: #888; font-size: 12px;">Provision wird auf alle Service-Anfragen dieses Benutzers angewendet</small>
                    </div>
                    <div class="form-group">
                        <label for="edit_user_pass">Neues Passwort (leer lassen für keine Änderung)</label>
                        <input type="password" name="user_pass" id="edit_user_pass">
                    </div>
                    <div id="Nexora Service Suite-edit-user-message" style="margin-bottom: 8px;"></div>
                </form>
            </div>
            <div class="Nexora Service Suite-modal-footer compact-modal-footer">
                <button type="submit" form="Nexora Service Suite-edit-user-form" class="button button-primary">Änderungen speichern</button>
            </div>
        </div>
    </div>
    
    <div id="Nexora Service Suite-role-modal" class="Nexora Service Suite-modal" style="display:none;">
        <div class="Nexora Service Suite-modal-content">
            <span class="Nexora Service Suite-close-modal">&times;</span>
            <h2>Neue Rolle hinzufügen</h2>
            <form id="Nexora Service Suite-add-role-form">
                <p>
                    <label>Rollenname (Englisch)</label>
                    <input type="text" name="role_name" required>
                </p>
                <p>
                    <label>Anzeigename der Rolle</label>
                    <input type="text" name="role_label" required>
                </p>
                <p>
                    <label>Berechtigungen</label>
                    <div style="margin-bottom: 8px; color: #555; font-size: 0.95em;">Wählen Sie, auf welche Bereiche des Nexora Service Suite-Plugins diese Rolle Zugriff haben soll.</div>
                    <div style="max-height:180px;overflow-y:auto;border:1px solid #eee;padding:8px 6px;border-radius:8px;background:#fafafa;">
                        <label style="display:block;margin-bottom:4px;">
                            <input type="checkbox" name="capabilities[]" value="reparaturdienst_dashboard"> Dashboard
                        </label>
                        <label style="display:block;margin-bottom:4px;">
                            <input type="checkbox" name="capabilities[]" value="reparaturdienst_services"> Dienstleistungen
                        </label>
                        <label style="display:block;margin-bottom:4px;">
                            <input type="checkbox" name="capabilities[]" value="reparaturdienst_devices"> Geräteverwaltung
                        </label>
                        <label style="display:block;margin-bottom:4px;">
                            <input type="checkbox" name="capabilities[]" value="reparaturdienst_status"> Status
                        </label>
                        <label style="display:block;margin-bottom:4px;">
                            <input type="checkbox" name="capabilities[]" value="reparaturdienst_requests"> Anfragen
                        </label>
                        <label style="display:block;margin-bottom:4px;">
                            <input type="checkbox" name="capabilities[]" value="reparaturdienst_users"> Benutzer
                        </label>
                        <label style="display:block;margin-bottom:4px;">
                            <input type="checkbox" name="capabilities[]" value="reparaturdienst_messages"> Nachrichten
                        </label>
                        <label style="display:block;margin-bottom:4px;">
                            <input type="checkbox" name="capabilities[]" value="reparaturdienst_settings"> Einstellungen
                        </label>
                    </div>
                </p>
                <p>
                    <button type="submit" class="button button-primary">Rolle hinzufügen</button>
                </p>
            </form>
            <div id="Nexora Service Suite-add-role-message"></div>
        </div>
    </div>
</div>
</body>
</html>
<script>
jQuery(document).ready(function($) {
    if (typeof ajaxurl === 'undefined') {
        window.ajaxurl = window.ajaxurl || '/wp-admin/admin-ajax.php';
    }
    $(document).on('click', '.Nexora Service Suite-delete-user', function(e) {
        e.preventDefault();
        if (!confirm('Sind Sie sicher, dass Sie diesen Benutzer löschen möchten?')) return;
        var btn = $(this);
        var userId = btn.data('user-id');
        btn.prop('disabled', true);
        $.post(ajaxurl, {
            action: 'nexora_delete_user',
            user_id: userId
        }, function(response) {
            if (response.success) {
                btn.closest('tr').fadeOut(300, function() { $(this).remove(); });
            } else {
                alert(response.data || 'Fehler beim Löschen des Benutzers');
                btn.prop('disabled', false);
            }
        });
    });
    $('#Nexora Service Suite-add-user').on('click', function(e) {
        e.preventDefault();
        $('#Nexora Service Suite-user-modal').fadeIn(200);
    });
    $('#Nexora Service Suite-add-role').on('click', function(e) {
        e.preventDefault();
        $('#Nexora Service Suite-role-modal').fadeIn(200);
    });
    $('.Nexora Service Suite-close-modal').on('click', function() {
        $(this).closest('.Nexora Service Suite-modal').fadeOut(200);
    });
    $('#Nexora Service Suite-add-user-form').on('submit', function(e) {
        e.preventDefault();
        var form = $(this);
        var data = form.serialize();
        form.find('button[type=submit]').prop('disabled', true);
        $('#Nexora Service Suite-add-user-message').html('Wird gesendet...');
        $.post(ajaxurl, {
            action: 'nexora_add_user',
            data: data
        }, function(response) {
            if (response.success) {
                $('#Nexora Service Suite-add-user-message').html('<span style="color:green">Benutzer wurde erfolgreich hinzugefügt.</span>');
                setTimeout(function(){ location.reload(); }, 1000);
            } else {
                $('#Nexora Service Suite-add-user-message').html('<span style="color:red">' + (response.data || 'Fehler beim Hinzufügen des Benutzers') + '</span>');
                form.find('button[type=submit]').prop('disabled', false);
            }
        });
    });
    $(document).on('click', '.Nexora Service Suite-edit-user', function(e) {
        e.preventDefault();
        var userId = $(this).data('user-id');
        $('#Nexora Service Suite-edit-user-modal').fadeIn(200);
        $('#Nexora Service Suite-edit-user-message').html('<div style="text-align: center; padding: 20px; color: #6b7280;">Lade Benutzerdaten...</div>');
        $.post(ajaxurl, {
            action: 'nexora_get_user_details',
            user_id: userId,
            nonce: '<?php echo wp_create_nonce('nexora_nonce'); ?>'
        }, function(response) {
            if (response.success) {
                var user = response.data;
                $('#edit_user_id').val(user.ID);
                $('#edit_user_login').val(user.user_login);
                $('#edit_user_email').val(user.user_email);
                $('#edit_user_first_name').val(user.first_name || '');
                $('#edit_user_last_name').val(user.last_name || '');
                $('#edit_user_display_name').val(user.display_name || '');
                $('#edit_user_role').val(user.roles[0] || 'subscriber');
                $('#edit_user_customer_type').val(user.customer_type || '');
                $('#edit_user_company_name').val(user.company_name || '');
                $('#edit_user_phone').val(user.phone || '');
                $('#edit_user_city').val(user.city || '');
                $('#edit_user_street').val(user.street || '');
                $('#edit_user_postal_code').val(user.postal_code || '');
                $('#edit_user_approved').val(user.user_approved || '');
                $('#edit_user_benefit_type').val(user.benefit_type || '');
                $('#edit_user_discount_percentage').val(user.discount_percentage || '');
                $('#edit_user_commission_percentage').val(user.commission_percentage || '');
                $('#edit_user_pass').val('');
                updateBenefitTypeFields();
                
                $('#Nexora Service Suite-edit-user-message').html('');
            } else {
                $('#Nexora Service Suite-edit-user-message').html('<div style="color: red; text-align: center; padding: 20px;">Fehler beim Laden der Benutzerdaten</div>');
            }
        });
    });
    $('#Nexora Service Suite-edit-user-form').on('submit', function(e) {
        e.preventDefault();
        var form = $(this);
        var submitBtn = form.find('button[type=submit]');
        submitBtn.prop('disabled', true).text('Wird gespeichert...');
        $('#Nexora Service Suite-edit-user-message').html('<div style="text-align: center; padding: 10px; color: #6b7280;">Speichere Änderungen...</div>');
        
        var formData = form.serialize();
        
        $.post(ajaxurl, {
            action: 'nexora_update_user',
            data: formData,
            nonce: '<?php echo wp_create_nonce('nexora_nonce'); ?>'
        }, function(response) {
            if (response.success) {
                $('#Nexora Service Suite-edit-user-message').html('<div style="color: green; text-align: center; padding: 10px;">Benutzer wurde erfolgreich aktualisiert!</div>');
                setTimeout(function() {
                    $('#Nexora Service Suite-edit-user-modal').fadeOut(200);
                    location.reload();
                }, 1500);
            } else {
                $('#Nexora Service Suite-edit-user-message').html('<div style="color: red; text-align: center; padding: 10px;">' + (response.data || 'Fehler beim Speichern der Änderungen') + '</div>');
                submitBtn.prop('disabled', false).text('Änderungen speichern');
            }
        });
    });
    $('#Nexora Service Suite-add-role-form').on('submit', function(e) {
        e.preventDefault();
        var form = $(this);
        var data = form.serialize();
        form.find('button[type=submit]').prop('disabled', true);
        $('#Nexora Service Suite-add-role-message').html('Wird gesendet...');
        $.post(ajaxurl, {
            action: 'nexora_add_role',
            data: data
        }, function(response) {
            if (response.success) {
                $('#Nexora Service Suite-add-role-message').html('<span style="color:green">Rolle wurde erfolgreich hinzugefügt.</span>');
                setTimeout(function(){ location.reload(); }, 1000);
            } else {
                $('#Nexora Service Suite-add-role-message').html('<span style="color:red">' + (response.data || 'Fehler beim Hinzufügen der Rolle') + '</span>');
                form.find('button[type=submit]').prop('disabled', false);
            }
        });
    });
    $(document).on('change', '.user-status-select', function() {
        var select = $(this);
        var userId = select.data('user-id');
        var newStatus = select.val();

        if (newStatus === '') {
            alert('Bitte wählen Sie einen Status.');
            return;
        }

        var action = newStatus === 'yes' ? 'approve' : 'reject';
        var message = newStatus === 'yes' ? 'Genehmigen' : 'Ablehnen';

        if (!confirm('Sind Sie sicher, dass Sie diesen Benutzer ' + message + ' möchten?')) {
            select.val('');
            return;
        }

        select.prop('disabled', true);
        select.html('<span style="color: #6b7280;">Speichere Status...</span>');

        $.post(ajaxurl, {
            action: 'nexora_' + action + '_user',
            user_id: userId,
            nonce: '<?php echo wp_create_nonce('nexora_nonce'); ?>'
        }, function(response) {
            if (response.success) {
                select.html('<span style="color: #059669;">Genehmigt</span>');
                select.prop('disabled', false);
                select.closest('td').find('.Nexora Service Suite-approve-user, .Nexora Service Suite-reject-user').hide();
            } else {
                alert(response.data || 'Fehler bei der Benutzergenehmigung/ablehnung');
                select.val('');
                select.prop('disabled', false);
            }
        });
    });
    $(document).on('change', '.user-customer-type-select', function() {
        var select = $(this);
        var userId = select.data('user-id');
        var newCustomerType = select.val();

        if (newCustomerType === '') {
            alert('Bitte wählen Sie einen Kundentyp.');
            return;
        }

        var action = newCustomerType === 'business' ? 'set_customer_type_business' : 'set_customer_type_private';
        var message = newCustomerType === 'business' ? 'Geschäftlich' : 'Privat';

        if (!confirm('Sind Sie sicher, dass Sie diesen Benutzer als ' + message + ' markieren möchten?')) {
            select.val('');
            return;
        }

        select.prop('disabled', true);
        select.html('<span style="color: #6b7280;">Speichere Kundentyp...</span>');

        $.post(ajaxurl, {
            action: 'nexora_' + action + '_user',
            user_id: userId,
            nonce: '<?php echo wp_create_nonce('nexora_nonce'); ?>'
        }, function(response) {
            if (response.success) {
                select.html('<span style="color: #2563eb;">' + message + '</span>');
                select.prop('disabled', false);
            } else {
                alert(response.data || 'Fehler beim Speichern des Kundentyps');
                select.val('');
                select.prop('disabled', false);
            }
        });
    });
    $(document).on('change', '.user-role-select', function() {
        var select = $(this);
        var userId = select.data('user-id');
        var newRole = select.val();

        if (newRole === '') {
            alert('Bitte wählen Sie eine Rolle.');
            return;
        }

        var action = newRole === 'administrator' ? 'set_user_role_administrator' : 'set_user_role';
        var message = newRole === 'administrator' ? 'Administrator' : newRole;

        if (!confirm('Sind Sie sicher, dass Sie diesen Benutzer als ' + message + ' markieren möchten?')) {
            select.val('');
            return;
        }

        select.prop('disabled', true);
        select.html('<span style="color: #6b7280;">Speichere Rolle...</span>');

        $.post(ajaxurl, {
            action: 'nexora_' + action + '_user',
            user_id: userId,
            role: newRole,
            nonce: '<?php echo wp_create_nonce('nexora_nonce'); ?>'
        }, function(response) {
            if (response.success) {
                select.html('<span style="color: #2563eb;">' + message + '</span>');
                select.prop('disabled', false);
            } else {
                alert(response.data || 'Fehler beim Speichern der Rolle');
                select.val('');
                select.prop('disabled', false);
            }
        });
    });
    $(document).on('click', '.Nexora Service Suite-view-customer', function(e) {
        e.preventDefault();
        var userId = $(this).data('user-id');
        $('#Nexora Service Suite-customer-details-content').html('<div style="text-align: center; padding: 40px;">Wird geladen...</div>');
        $('#Nexora Service Suite-customer-details-modal').fadeIn(200);
        $.post(ajaxurl, {
            action: 'nexora_get_customer_details',
            user_id: userId,
            nonce: '<?php echo wp_create_nonce('nexora_nonce'); ?>'
        }, function(response) {
            if (response.success) {
                $('#Nexora Service Suite-customer-details-content').html(response.data.html);
            } else {
                $('#Nexora Service Suite-customer-details-content').html('<div style="color: red; text-align: center; padding: 20px;">Fehler beim Laden der Informationen</div>');
            }
        });
    });
    function updateUserStatusSelectColor($select) {
        var val = $select.val();
        $select.removeClass('status-pending status-approved status-rejected');
        if (val === 'yes') $select.addClass('status-approved');
        else if (val === 'no') $select.addClass('status-rejected');
        else $select.addClass('status-pending');
    }
    $('.user-status-select').each(function(){ updateUserStatusSelectColor($(this)); });
    $(document).on('change', '.user-status-select', function(){ updateUserStatusSelectColor($(this)); });
    function updateUserCustomerTypeSelectColor($select) {
        var val = $select.val();
        $select.removeClass('type-business type-private type-unset');
        if (val === 'business') $select.addClass('type-business');
        else if (val === 'private') $select.addClass('type-private');
        else $select.addClass('type-unset');
    }
    $('.user-customer-type-select').each(function(){ updateUserCustomerTypeSelectColor($(this)); });
    $(document).on('change', '.user-customer-type-select', function(){ updateUserCustomerTypeSelectColor($(this)); });
    $('#generate-customer-numbers-btn').on('click', function() {
        if (!confirm('Möchten Sie für alle Benutzer ohne Kundennummer automatisch eine Kundennummer generieren?\n\nDies kann nicht rückgängig gemacht werden.')) {
            return;
        }

        var button = $(this);
        var originalText = button.text();
        button.prop('disabled', true);
        button.text('Generiere...');

        $.post(ajaxurl, {
            action: 'nexora_initialize_customer_numbers',
            nonce: '<?php echo wp_create_nonce('nexora_customer_number_nonce'); ?>'
        }, function(response) {
            if (response.success) {
                alert('Erfolgreich ' + response.data.count + ' Kundennummern generiert!');
                location.reload();
            } else {
                alert('Fehler beim Generieren der Kundennummern: ' + (response.data || 'Unbekannter Fehler'));
            }
            button.prop('disabled', false);
            button.text(originalText);
        }).fail(function() {
            alert('Fehler bei der AJAX-Anfrage');
            button.prop('disabled', false);
            button.text(originalText);
        });
    });
    $(document).on('change', '.customer-number-input', function() {
        var input = $(this);
        var userId = input.data('user-id');
        var newCustomerNumber = input.val().trim();
        var originalValue = input.data('original-value');

        if (newCustomerNumber === originalValue) {
            return;
        }

        if (newCustomerNumber === '') {
            alert('Kundennummer darf nicht leer sein.');
            input.val(originalValue);
            return;
        }

        if (!confirm('Sind Sie sicher, dass Sie die Kundennummer von "' + (originalValue || 'leer') + '" zu "' + newCustomerNumber + '" ändern möchten?')) {
            input.val(originalValue);
            return;
        }

        input.prop('disabled', true);
        input.css('background-color', '#f3f4f6');

        $.post(ajaxurl, {
            action: 'nexora_update_customer_number',
            user_id: userId,
            customer_number: newCustomerNumber,
            nonce: '<?php echo wp_create_nonce('nexora_customer_number_nonce'); ?>'
        }, function(response) {
            if (response.success) {
                input.data('original-value', newCustomerNumber);
                input.css('background-color', '#d1fae5');
                setTimeout(function() {
                    input.css('background-color', '');
                }, 2000);
                input.attr('title', 'Kundennummer erfolgreich aktualisiert');
                setTimeout(function() {
                    input.removeAttr('title');
                }, 3000);
            } else {
                alert('Fehler beim Aktualisieren der Kundennummer: ' + (response.data || 'Unbekannter Fehler'));
                input.val(originalValue);
            }
            input.prop('disabled', false);
        }).fail(function() {
            alert('Fehler bei der AJAX-Anfrage');
            input.val(originalValue);
            input.prop('disabled', false);
        });
    });
    function updateUserRoleSelectColor($select) {
        var val = $select.val();
        $select.removeClass('role-subscriber role-customer role-editor role-author role-contributor role-shop_manager role-administrator');
        if (val === 'subscriber') $select.addClass('role-subscriber');
        else if (val === 'customer') $select.addClass('role-customer');
        else if (val === 'editor') $select.addClass('role-editor');
        else if (val === 'author') $select.addClass('role-author');
        else if (val === 'contributor') $select.addClass('role-contributor');
        else if (val === 'shop_manager') $select.addClass('role-shop_manager');
        else if (val === 'administrator') $select.addClass('role-administrator');
    }
    $('.user-role-select').each(function(){ updateUserRoleSelectColor($(this)); });
    $(document).on('change', '.user-role-select', function(){ updateUserRoleSelectColor($(this)); });
    function loadNotificationCounts() {
        $.post(ajaxurl, {action: 'get_new_requests_count', nonce: '<?php echo wp_create_nonce("nexora_notifications_nonce"); ?>'}, function(resp){
            if(resp.success && resp.data.count > 0) {
                $('#nav-anfragen-badge').text(resp.data.count).show();
            } else {
                $('#nav-anfragen-badge').hide();
            }
        });
        $.post(ajaxurl, {action: 'nexora_get_new_users_count', nonce: '<?php echo wp_create_nonce("nexora_nonce"); ?>'}, function(resp){
            if(resp.success && resp.data.count > 0) {
                $('#nav-benutzer-badge').text(resp.data.count).show();
            } else {
                $('#nav-benutzer-badge').hide();
            }
        });
        $.post(ajaxurl, {action: 'nexora_get_admin_notifications', nonce: '<?php echo wp_create_nonce("nexora_nonce"); ?>'}, function(resp){
            if(resp.success && resp.data.length > 0) {
                $('#nav-nachrichten-badge').text(resp.data.length).show();
            } else {
                $('#nav-nachrichten-badge').hide();
            }
        });
    }
    $('#topbar-user-search, #Nexora Service Suite-user-search').on('input', function() {
        const searchTerm = $(this).val().toLowerCase().trim();
        $('#topbar-user-search, #Nexora Service Suite-user-search').val($(this).val());
        
        filterUsers(searchTerm);
    });
    function filterUsers(searchTerm) {
        if (searchTerm === '') {
            $('#Nexora Service Suite-user-list tr').show();
            $('#user-table-info').text('<?php echo count($users); ?> Benutzer geladen');
            getAllUsers();
            showUsersForPage(1);
            return;
        }
        
        let visibleCount = 0;
        let filteredUsers = [];
        $('#Nexora Service Suite-user-list tr').each(function() {
            const id = $(this).find('td:nth-child(1)').text().toLowerCase();
            const username = $(this).find('td:nth-child(2)').text().toLowerCase();
            const email = $(this).find('td:nth-child(3)').text().toLowerCase();
            const customerNumber = $(this).find('.customer-number-input').val().toLowerCase();
            const customerType = $(this).find('.user-customer-type-select option:selected').text().toLowerCase();
            const role = $(this).find('.user-role-select option:selected').text().toLowerCase();
            const status = $(this).find('.user-status-select option:selected').text().toLowerCase();
            
            if (id.includes(searchTerm) || 
                username.includes(searchTerm) || 
                email.includes(searchTerm) || 
                customerNumber.includes(searchTerm) ||
                customerType.includes(searchTerm) ||
                role.includes(searchTerm) ||
                status.includes(searchTerm)) {
                $(this).show();
                visibleCount++;
                filteredUsers.push($(this));
            } else {
                $(this).hide();
            }
        });
        allUsers = filteredUsers;
        $('#user-table-info').text(`${visibleCount} Benutzer gefunden für "${searchTerm}"`);
        showUsersForPage(1);
    }
    loadNotificationCounts();
    setInterval(loadNotificationCounts, 60000);
    let currentPage = 1;
    const usersPerPage = 10;
    let allUsers = [];
    function getAllUsers() {
        allUsers = [];
        $('#Nexora Service Suite-user-list tr').each(function() {
            const row = $(this);
            allUsers.push(row);
        });
    }
    function showUsersForPage(page) {
        const startIndex = (page - 1) * usersPerPage;
        const endIndex = startIndex + usersPerPage;
        $('#Nexora Service Suite-user-list tr').hide();
        for (let i = startIndex; i < endIndex && i < allUsers.length; i++) {
            allUsers[i].show();
        }
        const totalPages = Math.ceil(allUsers.length / usersPerPage);
        $('#Nexora Service Suite-page-info').text(`Seite ${page} von ${totalPages} (${allUsers.length} Benutzer gesamt)`);
        $('#Nexora Service Suite-prev-page').prop('disabled', page <= 1);
        $('#Nexora Service Suite-next-page').prop('disabled', page >= totalPages);
        
        currentPage = page;
    }
    $('#Nexora Service Suite-prev-page').on('click', function() {
        if (currentPage > 1) {
            showUsersForPage(currentPage - 1);
        }
    });
    
    $('#Nexora Service Suite-next-page').on('click', function() {
        const totalPages = Math.ceil(allUsers.length / usersPerPage);
        if (currentPage < totalPages) {
            showUsersForPage(currentPage + 1);
        }
    });
    getAllUsers();
    showUsersForPage(1);

});
</script>

<style>

.vertical-nav {
    position: fixed;
    width: 80px;
    height: 100vh;
    z-index: 1000;
    background: rgba(26, 31, 43, 0.22);
    backdrop-filter: blur(20px);
    border-right: 1px solid rgba(255, 255, 255, 0.12);
    transition: all 0.4s cubic-bezier(0.4, 0, 0.2, 1);
    box-shadow: 0 10px 30px rgba(0, 0, 0, 0.35);
    padding: 16px 8px;
    box-sizing: border-box;
}

.vertical-nav:hover {
    width: 200px;
    box-shadow: 0 16px 40px rgba(0, 0, 0, 0.45);
}

.nav-toggle {
    display: flex;
    flex-direction: column;
    align-items: center;
    gap: 8px;
    padding: 16px 8px;
    border-radius: 12px;
    background: rgba(108, 93, 211, 0.2);
    margin-bottom: 20px;
    transition: all 0.3s ease;
    border-bottom: 1px solid rgba(255, 255, 255, 0.12);
}

.nav-toggle:hover {
    background: rgba(108, 93, 211, 0.4);
    transform: scale(1.05);
}

.nav-toggle i {
    font-size: 20px;
    color: #6c5dd3;
}

.nav-label {
    font-size: 12px;
    font-weight: 600;
    color: #ffffff;
    opacity: 0.9;
}

.nav-menu {
    list-style: none;
    margin: 0;
    padding: 0;
    display: flex;
    flex-direction: column;
    gap: 8px;
}

.nav-item {
    opacity: 0;
    transform: translateX(-20px);
    animation: slideInLeft 0.6s ease forwards;
}

.nav-item:nth-child(1) { animation-delay: 0.1s; }
.nav-item:nth-child(2) { animation-delay: 0.2s; }
.nav-item:nth-child(3) { animation-delay: 0.3s; }
.nav-item:nth-child(4) { animation-delay: 0.4s; }
.nav-item:nth-child(5) { animation-delay: 0.5s; }
.nav-item:nth-child(6) { animation-delay: 0.6s; }
.nav-item:nth-child(7) { animation-delay: 0.7s; }

.nav-link {
    display: flex;
    align-items: center;
    gap: 12px;
    padding: 12px 16px;
    border-radius: 16px;
    color: #a0aec0;
    text-decoration: none;
    transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
    position: relative;
    overflow: hidden;
}

.nav-link::before {
    content: '';
    position: absolute;
    top: 0;
    left: -100%;
    width: 100%;
    height: 100%;
    background: linear-gradient(90deg, transparent, rgba(108, 93, 211, 0.1), transparent);
    transition: left 0.5s ease;
}

.nav-link:hover::before {
    left: 100%;
}

.nav-link:hover {
    background: rgba(108, 93, 211, 0.1);
    color: #ffffff;
    transform: translateX(4px);
}

.nav-link.active {
    background: rgba(108, 93, 211, 0.2);
    color: #6c5dd3;
}

.nav-link i {
    font-size: 18px;
    transition: all 0.3s ease;
    min-width: 20px;
}

.nav-link:hover i {
    color: #6c5dd3;
    transform: scale(1.1) rotate(5deg);
}

.nav-text {
    font-size: 14px;
    font-weight: 500;
    white-space: nowrap;
    opacity: 0;
    transition: all 0.3s ease;
    position: absolute;
            left: 45px !important;
    top: 50%;
    transform: translateY(-50%);
}

.vertical-nav:hover .nav-text {
    opacity: 1;
    transform: translateY(-50%);
}

.nav-badge {
    position: absolute;
    top: 8px;
    right: 8px;
    background: #e74c3c;
    color: white;
    border-radius: 50%;
    min-width: 18px;
    height: 18px;
    font-size: 11px;
    font-weight: 600;
    display: flex;
    align-items: center;
    justify-content: center;
    padding: 2px;
    box-shadow: 0 2px 4px rgba(0, 0, 0, 0.3);
    animation: badgePulse 2s infinite;
}

.nav-badge.awaiting-mod {
    background: #e74c3c;
}

@keyframes badgePulse {
    0% { transform: scale(1); }
    50% { transform: scale(1.1); }
    100% { transform: scale(1); }
}

@keyframes slideInLeft {
    to {
        opacity: 1;
        transform: translateX(0);
    }
}

.dashboard-container {
    padding: 20px;
    width: calc(100% - 80px);
    max-width: calc(100% - 80px);
    min-width: 0;
    margin: 0;
    overflow-x: hidden;
    box-sizing: border-box;
}

#wpbody-content {
    padding: 0;
    margin: 0;
}

#wpbody {
    overflow-x: hidden;
}

.dashboard-topbar {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 32px;
    padding: 16px 0;
}

.breadcrumb {
    color: #CBD5E0;
    font-size: 14px;
}

.topbar-right {
    display: flex;
    align-items: center;
    gap: 20px;
}

.search-box {
    display: flex;
    align-items: center;
    gap: 12px;
    background: rgba(255, 255, 255, 0.1);
    padding: 8px 16px;
    border-radius: 8px;
    border: 1px solid rgba(255, 255, 255, 0.2);
}

.search-box input {
    background: transparent;
    border: none;
    color: #FFFFFF;
    outline: none;
    width: 200px;
}

.search-box input::placeholder {
    color: #CBD5E0;
}

.user-menu {
    display: flex;
    align-items: center;
    gap: 8px;
    color: #CBD5E0;
    cursor: pointer;
}

.glass-card {
    background: rgba(26, 31, 43, 0.2);
    backdrop-filter: blur(20px);
    border: 1px solid rgba(255, 255, 255, 0.2);
    border-radius: 16px;
    box-shadow: 0 8px 32px rgba(0, 0, 0, 0.3);
    padding: 24px;
    margin-bottom: 24px;
}

.table-container {
    margin-bottom: 24px;
    overflow-x: auto;
    max-width: 100%;
}

.table-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 20px;
    padding-bottom: 16px;
    border-bottom: 1px solid rgba(255, 255, 255, 0.1);
}

.table-info {
    color: rgba(255, 255, 255, 0.8);
    font-size: 14px;
}

.table-wrapper {
    overflow-x: auto;
    border-radius: 12px;
    border: 1px solid rgba(255, 255, 255, 0.1);
    max-width: 100%;
}

.filters-container {
    margin-bottom: 24px;
}

.filters-left {
    display: flex;
    align-items: center;
    gap: 20px;
    margin-bottom: 20px;
}

.filters-left .search-box {
    display: flex;
    align-items: center;
    gap: 12px;
    background: rgba(255, 255, 255, 0.1);
    padding: 8px 16px;
    border-radius: 8px;
    border: 1px solid rgba(255, 255, 255, 0.2);
}

.filters-left .search-box input {
    background: transparent;
    border: none;
    color: #FFFFFF;
    outline: none;
    width: 200px;
}

.filters-left .search-box input::placeholder {
    color: rgba(255, 255, 255, 0.5);
}

.filters-left .search-btn {
    background: none;
    border: none;
    color: rgba(255, 255, 255, 0.7);
    cursor: pointer;
    padding: 4px;
    border-radius: 4px;
    transition: all 0.3s ease;
}

.filters-left .search-btn:hover {
    color: #fcdc24;
    background: rgba(255, 255, 255, 0.1);
}

.filter-form {
    display: flex;
    align-items: center;
    gap: 12px;
}

.filter-form label {
    color: rgba(255, 255, 255, 0.8);
    font-size: 14px;
    white-space: nowrap;
}

.filter-select {
    background: rgba(255, 255, 255, 0.1);
    border: 1px solid rgba(255, 255, 255, 0.2);
    border-radius: 8px;
    padding: 8px 12px;
    color: white;
    outline: none;
    transition: all 0.3s ease;
}

.filter-select:focus {
    border-color: #667eea;
    box-shadow: 0 0 0 3px rgba(102, 126, 234, 0.2);
}

.filters-right {
    display: flex;
    justify-content: flex-end;
}

.page-content {
    background: rgba(26, 31, 43, 0.95);
    backdrop-filter: blur(20px);
    border: 1px solid rgba(255, 255, 255, 0.2);
    border-radius: 16px;
    padding: 24px;
    box-shadow: 0 8px 32px rgba(0, 0, 0, 0.3);
}

.page-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 24px;
    padding-bottom: 20px;
    border-bottom: 1px solid rgba(255, 255, 255, 0.1);
}

.page-title {
    color: #fcdc24;
    font-size: 28px;
    font-weight: 600;
    margin: 0;
}

.page-actions {
    display: flex;
    gap: 12px;
}

.btn {
    display: inline-flex;
    align-items: center;
    gap: 8px;
    padding: 12px 20px;
    border: none;
    border-radius: 12px;
    font-weight: 600;
    text-decoration: none;
    transition: all 0.3s ease;
    cursor: pointer;
    font-size: 14px;
}

.btn-primary {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    color: white;
}

.btn-primary:hover {
    transform: translateY(-2px);
    box-shadow: 0 8px 25px rgba(102, 126, 234, 0.4);
}

.btn-secondary {
    background: rgba(255, 255, 255, 0.1);
    color: #fcdc24;
    border: 1px solid rgba(252, 220, 36, 0.3);
}

.btn-secondary:hover {
    background: rgba(252, 220, 36, 0.1);
    transform: translateY(-2px);
}

.btn-outline {
    background: transparent;
    color: #fcdc24;
    border: 1px solid rgba(252, 220, 36, 0.3);
}

.btn-outline:hover {
    background: rgba(252, 220, 36, 0.1);
}

.stats-container {
    margin-bottom: 24px;
}

.stats-header h3 {
    color: #fcdc24;
    font-size: 20px;
    font-weight: 600;
    margin: 0 0 16px 0;
}

.stats-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
    gap: 16px;
}

.stat-card {
    background: rgba(255, 255, 255, 0.05);
    border: 1px solid rgba(255, 255, 255, 0.1);
    border-radius: 12px;
    padding: 20px;
    text-align: center;
    transition: all 0.3s ease;
}

.stat-card:hover {
    transform: translateY(-4px);
    box-shadow: 0 8px 25px rgba(0, 0, 0, 0.2);
}

.stat-number {
    font-size: 32px;
    font-weight: 700;
    color: #fcdc24;
    margin-bottom: 8px;
}

.stat-label {
    color: rgba(255, 255, 255, 0.8);
    font-size: 14px;
    font-weight: 500;
}

.users-table {
    width: 100%;
    border-collapse: collapse;
    color: white;
}

.users-table {
    width: 100%;
    border-collapse: collapse;
    background: rgba(255, 255, 255, 0.02);
    border-radius: 12px;
    overflow: hidden;
    min-width: 800px;
}

.users-table th {
    background: rgba(255, 255, 255, 0.05);
    padding: 16px 12px;
    text-align: left;
    font-weight: 600;
    color: #fcdc24 !important;
    border-bottom: 1px solid rgba(255, 255, 255, 0.1);
}

.users-table td {
    padding: 16px 12px;
    border-bottom: 1px solid rgba(255, 255, 255, 0.05);
}

.users-table tr:hover {
    background: rgba(255, 255, 255, 0.05);
}

.customer-number-input,
.user-customer-type-select,
.user-role-select,
.user-status-select {
    background: rgba(255, 255, 255, 0.1);
    border: 1px solid rgba(255, 255, 255, 0.2);
    border-radius: 6px;
    padding: 8px 12px;
    color: white;
    width: 100%;
    outline: none;
    transition: all 0.3s ease;
}

.customer-number-input:focus,
.user-customer-type-select:focus,
.user-role-select:focus,
.user-status-select:focus {
    border-color: #667eea;
    box-shadow: 0 0 0 3px rgba(102, 126, 234, 0.2);
}

.customer-number-input::placeholder {
    color: rgba(255, 255, 255, 0.5);
}

.actions-cell {
    width: 117.6px;
    text-align: center;
}

.actions-container {
    display: flex;
    gap: 7.84px;
    justify-content: center;
    align-items: center;
}

.action-btn {
    display: flex;
    align-items: center;
    justify-content: center;
    width: 31.36px;
    height: 31.36px;
    min-width: 9.8px;
    padding: 2px 2px;
    border: none;
    border-radius: 5.88px;
    cursor: pointer;
    transition: all 0.3s ease;
    font-size: 13.72px;
    color: white;
    background: rgba(255, 255, 255, 0.1);
    border: 1px solid rgba(255, 255, 255, 0.2);
}

.action-btn:hover {
    transform: translateY(-2px);
    box-shadow: 0 4px 12px rgba(0, 0, 0, 0.3);
}

.action-btn:active {
    transform: translateY(0);
}

.edit-btn {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    border-color: #667eea;
}

.edit-btn:hover {
    background: linear-gradient(135deg, #5a6fd8 0%, #6a4190 100%);
}

.view-btn {
    background: linear-gradient(135deg, #8B5CF6 0%, #A855F7 100%);
    border-color: #8B5CF6;
}

.view-btn:hover {
    background: linear-gradient(135deg, #7c4dd8 0%, #9645e0 100%);
}

.delete-btn {
    background: linear-gradient(135deg, #FF6B6B 0%, #FF8E8E 100%);
    border-color: #FF6B6B;
}

.delete-btn:hover {
    background: linear-gradient(135deg, #e55a5a 0%, #e57d7d 100%);
}

.action-btn i {
    font-size: 14px;
    line-height: 1;
}

.pagination {
    display: flex;
    justify-content: center;
    align-items: center;
    gap: 16px;
    padding: 20px;
    border-top: 1px solid rgba(255, 255, 255, 0.1);
}

.pagination-info {
    color: rgba(255, 255, 255, 0.8);
    font-size: 14px;
}

.Nexora Service Suite-modal {
    display: none;
    position: fixed;
    z-index: 10000;
    left: 0;
    top: 0;
    width: 100%;
    height: 100%;
    background-color: rgba(0, 0, 0, 0.8);
    backdrop-filter: blur(10px);
}

.Nexora Service Suite-modal-content {
    background: rgba(26, 31, 43, 0.95);
    margin: 5% auto;
    padding: 20px;
    border: 1px solid rgba(255, 255, 255, 0.2);
    border-radius: 16px;
    width: 90%;
    max-width: 600px;
    box-shadow: 0 20px 60px rgba(0, 0, 0, 0.5);
    backdrop-filter: blur(20px);
    color: white;
}

.Nexora Service Suite-close-modal {
    color: #aaa;
    float: right;
    font-size: 28px;
    font-weight: bold;
    cursor: pointer;
    line-height: 1;
}

.Nexora Service Suite-close-modal:hover {
    color: #fcdc24;
}

.Nexora Service Suite-modal h2 {
    color: #fcdc24;
    margin-top: 0;
}

.Nexora Service Suite-modal label {
    display: block;
    margin-bottom: 5px;
    color: rgba(255, 255, 255, 0.8);
}

.Nexora Service Suite-modal input,
.Nexora Service Suite-modal select {
    width: 100%;
    padding: 10px;
    margin-bottom: 15px;
    border: 1px solid rgba(255, 255, 255, 0.2);
    border-radius: 8px;
    background: rgba(255, 255, 255, 0.1);
    color: white;
    box-sizing: border-box;
}

.Nexora Service Suite-modal input:focus,
.Nexora Service Suite-modal select:focus {
    outline: none;
    border-color: #667eea;
    box-shadow: 0 0 0 3px rgba(102, 126, 234, 0.2);
}

.Nexora Service Suite-modal button {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    color: white;
    padding: 12px 24px;
    border: none;
    border-radius: 8px;
    cursor: pointer;
    font-weight: 600;
    transition: all 0.3s ease;
}

.Nexora Service Suite-modal button:hover {
    transform: translateY(-2px);
    box-shadow: 0 8px 25px rgba(102, 126, 234, 0.4);
}

@media (max-width: 1200px) {
    .dashboard-container {
        width: calc(100% - 80px);
        padding: 16px;
    }
    
    .page-header {
        flex-direction: column;
        gap: 16px;
        align-items: flex-start;
    }
    
    .filters-container {
        flex-direction: column;
        gap: 16px;
        align-items: stretch;
    }
    
    .filters-left {
        flex-direction: column;
        gap: 16px;
    }
    
    .search-box input {
        width: 100%;
    }
    
    .stats-grid {
        grid-template-columns: repeat(auto-fit, minmax(180px, 1fr));
    }
}

@media (max-width: 768px) {
    .dashboard-container {
        width: calc(100% - 80px);
        padding: 12px;
    }
    
            .actions-container {
            gap: 3.92px;
        }
        
        .action-btn {
            width: 27.44px;
            height: 27.44px;
            font-size: 11.76px;
        }
        
        .actions-cell {
            width: 98px;
        }
    
    .stats-grid {
        grid-template-columns: 1fr;
    }
}

#discount-percentage-group,
#commission-percentage-group {
    transition: all 0.3s ease;
}

#discount-percentage-group.show,
#commission-percentage-group.show {
    display: block !important;
    opacity: 1;
    transform: translateY(0);
}

#discount-percentage-group.hide,
#commission-percentage-group.hide {
    display: none !important;
    opacity: 0;
    transform: translateY(-10px);
}
</style>

<script>
function updateBenefitTypeFields() {
    const benefitType = $('#edit_user_benefit_type').val();
    const discountGroup = $('#discount-percentage-group');
    const commissionGroup = $('#commission-percentage-group');
    discountGroup.hide();
    commissionGroup.hide();
    if (benefitType === 'discount') {
        discountGroup.show();
    } else if (benefitType === 'commission') {
        commissionGroup.show();
    }
}
$(document).on('change', '#edit_user_benefit_type', function() {
    updateBenefitTypeFields();
});
</script> 