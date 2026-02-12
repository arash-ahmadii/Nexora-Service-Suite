<?php
require_once('wp-config.php');
if (!defined('ABSPATH')) {
    die('Direct access not allowed');
}

global $wpdb;

echo "<h2>Nexora Service Suite Database Update - User Discount Feature</h2>";
echo "<p>Updating database structure...</p>";

try {
    $benefit_type_exists = $wpdb->get_results("SHOW COLUMNS FROM {$wpdb->users} LIKE 'benefit_type'");
    
    if (empty($benefit_type_exists)) {
        $sql = "ALTER TABLE {$wpdb->users} ADD COLUMN benefit_type ENUM('', 'discount', 'commission') DEFAULT '' COMMENT 'User benefit type: discount or commission'";
        
        $result = $wpdb->query($sql);
        
        if ($result !== false) {
            echo "<p style='color: green;'>✅ Successfully added 'benefit_type' column to wp_users table</p>";
        } else {
            echo "<p style='color: red;'>❌ Error adding benefit_type column: " . $wpdb->last_error . "</p>";
        }
    } else {
        echo "<p style='color: blue;'>ℹ️ Column 'benefit_type' already exists in wp_users table</p>";
    }
    $discount_exists = $wpdb->get_results("SHOW COLUMNS FROM {$wpdb->users} LIKE 'discount_percentage'");
    
    if (empty($discount_exists)) {
        $sql = "ALTER TABLE {$wpdb->users} ADD COLUMN discount_percentage DECIMAL(5,2) DEFAULT 0.00 COMMENT 'User discount percentage for all services'";
        
        $result = $wpdb->query($sql);
        
        if ($result !== false) {
            echo "<p style='color: green;'>✅ Successfully added 'discount_percentage' column to wp_users table</p>";
        } else {
            echo "<p style='color: red;'>❌ Error adding discount_percentage column: " . $wpdb->last_error . "</p>";
        }
    } else {
        echo "<p style='color: blue;'>ℹ️ Column 'discount_percentage' already exists in wp_users table</p>";
    }
    $commission_exists = $wpdb->get_results("SHOW COLUMNS FROM {$wpdb->users} LIKE 'commission_percentage'");
    
    if (empty($commission_exists)) {
        $sql = "ALTER TABLE {$wpdb->users} ADD COLUMN commission_percentage DECIMAL(5,2) DEFAULT 0.00 COMMENT 'User commission percentage for all services'";
        
        $result = $wpdb->query($sql);
        
        if ($result !== false) {
            echo "<p style='color: green;'>✅ Successfully added 'commission_percentage' column to wp_users table</p>";
        } else {
            echo "<p style='color: red;'>❌ Error adding commission_percentage column: " . $wpdb->last_error . "</p>";
        }
    } else {
        echo "<p style='color: blue;'>ℹ️ Column 'commission_percentage' already exists in wp_users table</p>";
    }
    $payment_logs_table = $wpdb->prefix . 'nexora_payment_logs';
    $table_exists = $wpdb->get_var("SHOW TABLES LIKE '$payment_logs_table'");
    
    if (!$table_exists) {
        $create_payment_logs_sql = "CREATE TABLE $payment_logs_table (
            id INT(11) NOT NULL AUTO_INCREMENT,
            request_id INT(11) NOT NULL,
            user_id INT(11) NOT NULL,
            benefit_type ENUM('discount', 'commission') NOT NULL,
            percentage DECIMAL(5,2) NOT NULL,
            amount DECIMAL(10,2) NOT NULL,
            payment_status ENUM('not_paid', 'paid') DEFAULT 'not_paid',
            paid_by INT(11) NULL,
            paid_at DATETIME NULL,
            created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
            updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            PRIMARY KEY (id),
            INDEX idx_request_id (request_id),
            INDEX idx_user_id (user_id),
            INDEX idx_payment_status (payment_status),
            INDEX idx_benefit_type (benefit_type)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci";
        
        $result = $wpdb->query($create_payment_logs_sql);
        
        if ($result !== false) {
            echo "<p style='color: green;'>✅ Successfully created 'nexora_payment_logs' table</p>";
        } else {
            echo "<p style='color: red;'>❌ Error creating payment_logs table: " . $wpdb->last_error . "</p>";
        }
    } else {
        echo "<p style='color: blue;'>ℹ️ Table 'nexora_payment_logs' already exists</p>";
    }
    $indexes = [
        'idx_benefit_type' => 'benefit_type',
        'idx_discount_percentage' => 'discount_percentage',
        'idx_commission_percentage' => 'commission_percentage'
    ];
    
    foreach ($indexes as $index_name => $column) {
        $index_exists = $wpdb->get_results("SHOW INDEX FROM {$wpdb->users} WHERE Key_name = '$index_name'");
        
        if (empty($index_exists)) {
            $index_sql = "ALTER TABLE {$wpdb->users} ADD INDEX $index_name ($column)";
            $index_result = $wpdb->query($index_sql);
            
            if ($index_result !== false) {
                echo "<p style='color: green;'>✅ Successfully added index '$index_name' for $column column</p>";
            } else {
                echo "<p style='color: orange;'>⚠️ Warning: Could not add index '$index_name': " . $wpdb->last_error . "</p>";
            }
        } else {
            echo "<p style='color: blue;'>ℹ️ Index '$index_name' for $column already exists</p>";
        }
    }
    $verify = $wpdb->get_results("DESCRIBE {$wpdb->users} discount_percentage");
    
    if (!empty($verify)) {
        echo "<p style='color: green;'>✅ Verification successful: discount_percentage column is properly configured</p>";
        echo "<p><strong>Column Details:</strong></p>";
        echo "<ul>";
        foreach ($verify as $column) {
            echo "<li>Field: {$column->Field}</li>";
            echo "<li>Type: {$column->Type}</li>";
            echo "<li>Null: {$column->Null}</li>";
            echo "<li>Key: {$column->Key}</li>";
            echo "<li>Default: {$column->Default}</li>";
            echo "<li>Extra: {$column->Extra}</li>";
        }
        echo "</ul>";
    }
    echo "<hr><h3>Users with Benefits:</h3>";
    $users_with_discount = $wpdb->get_results("
        SELECT ID, user_login, user_email, benefit_type, discount_percentage, commission_percentage 
        FROM {$wpdb->users} 
        WHERE benefit_type = 'discount' AND discount_percentage > 0
        ORDER BY discount_percentage DESC
    ");
    
    if (!empty($users_with_discount)) {
        echo "<h4>Users with Discount:</h4>";
        echo "<table border='1' style='border-collapse: collapse; width: 100%; margin-bottom: 20px;'>";
        echo "<tr><th>User ID</th><th>Username</th><th>Email</th><th>Benefit Type</th><th>Discount %</th></tr>";
        
        foreach ($users_with_discount as $user) {
            echo "<tr>";
            echo "<td>{$user->ID}</td>";
            echo "<td>{$user->user_login}</td>";
            echo "<td>{$user->user_email}</td>";
            echo "<td>{$user->benefit_type}</td>";
            echo "<td>{$user->discount_percentage}%</td>";
            echo "</tr>";
        }
        echo "</table>";
    }
    $users_with_commission = $wpdb->get_results("
        SELECT ID, user_login, user_email, benefit_type, discount_percentage, commission_percentage 
        FROM {$wpdb->users} 
        WHERE benefit_type = 'commission' AND commission_percentage > 0
        ORDER BY commission_percentage DESC
    ");
    
    if (!empty($users_with_commission)) {
        echo "<h4>Users with Commission:</h4>";
        echo "<table border='1' style='border-collapse: collapse; width: 100%; margin-bottom: 20px;'>";
        echo "<tr><th>User ID</th><th>Username</th><th>Email</th><th>Benefit Type</th><th>Commission %</th></tr>";
        
        foreach ($users_with_commission as $user) {
            echo "<tr>";
            echo "<td>{$user->ID}</td>";
            echo "<td>{$user->user_login}</td>";
            echo "<td>{$user->user_email}</td>";
            echo "<td>{$user->benefit_type}</td>";
            echo "<td>{$user->commission_percentage}%</td>";
            echo "</tr>";
        }
        echo "</table>";
    }
    
    if (empty($users_with_discount) && empty($users_with_commission)) {
        echo "<p style='color: blue;'>ℹ️ No users currently have benefits set</p>";
    }
    echo "<hr><h3>Payment Logs Table Structure:</h3>";
    $payment_logs_structure = $wpdb->get_results("DESCRIBE {$wpdb->prefix}nexora_payment_logs");
    if ($payment_logs_structure) {
        echo "<table border='1' cellpadding='5' cellspacing='0'>";
        echo "<thead><tr><th>Field</th><th>Type</th><th>Null</th><th>Key</th><th>Default</th><th>Extra</th></tr></thead>";
        echo "<tbody>";
        foreach ($payment_logs_structure as $field) {
            echo "<tr>";
            echo "<td>{$field->Field}</td>";
            echo "<td>{$field->Type}</td>";
            echo "<td>{$field->Null}</td>";
            echo "<td>{$field->Key}</td>";
            echo "<td>{$field->Default}</td>";
            echo "<td>{$field->Extra}</td>";
            echo "</tr>";
        }
        echo "</tbody>";
        echo "</table>";
    }
    
    echo "<hr>";
    echo "<h3>Update Summary:</h3>";
    echo "<ul>";
    echo "<li>✅ Database structure updated for user benefits feature</li>";
    echo "<li>✅ Added 'benefit_type' column to wp_users table</li>";
    echo "<li>✅ Added 'discount_percentage' column to wp_users table</li>";
    echo "<li>✅ Added 'commission_percentage' column to wp_users table</li>";
    echo "<li>✅ Created 'nexora_payment_logs' table for payment tracking</li>";
    echo "<li>✅ Created indexes for better query performance</li>";
    echo "<li>✅ Feature ready to use in admin panel</li>";
    echo "</ul>";
    
    echo "<p style='color: green; font-weight: bold;'>🎉 Database update completed successfully!</p>";
    echo "<p><strong>Next steps:</strong></p>";
    echo "<ol>";
    echo "<li>Go to WordPress Admin → Benutzerverwaltung</li>";
    echo "<li>Edit any user to set their benefit type (discount or commission)</li>";
    echo "<li>Set the percentage for the selected benefit type</li>";
    echo "<li>Access the new 'Eltern' page to manage payments</li>";
    echo "</ol>";
    
} catch (Exception $e) {
    echo "<p style='color: red;'>❌ Error during database update: " . $e->getMessage() . "</p>";
}

echo "<hr>";
echo "<p><em>Script completed at: " . date('Y-m-d H:i:s') . "</em></p>";
?>
