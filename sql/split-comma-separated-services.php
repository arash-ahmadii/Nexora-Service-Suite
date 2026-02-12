<?php
if (!defined('ABSPATH')) {
    $wp_load_paths = [
        '../../../wp-load.php',
        '../../wp-load.php',
        '../wp-load.php',
        'wp-load.php'
    ];
    
    $wp_loaded = false;
    foreach ($wp_load_paths as $path) {
        if (file_exists($path)) {
            require_once($path);
            $wp_loaded = true;
            break;
        }
    }
    
    if (!$wp_loaded) {
        die('WordPress not found. Please run this script from the WordPress environment.');
    }
}
if (!current_user_can('manage_options')) {
    die('Insufficient permissions. Admin access required.');
}

echo "<h1>Migration: Split Comma-Separated Services</h1>";
echo "<p><strong>This script will split comma-separated service titles into individual database records.</strong></p>";

global $wpdb;
echo "<h2>Step 1: Analyzing Current Data</h2>";

$comma_separated_records = $wpdb->get_results("
    SELECT id, request_id, service_id, service_title, quantity, note, created_at, updated_at
    FROM {$wpdb->prefix}nexora_service_details 
    WHERE service_title LIKE '%,%'
    ORDER BY id
");

$total_comma_records = count($comma_separated_records);

echo "<div style='background: #fff3cd; border: 1px solid #ffeaa7; border-radius: 5px; padding: 15px; margin: 10px 0;'>";
echo "<h3>📊 Analysis Results:</h3>";
echo "<p><strong>Total records with comma-separated services:</strong> {$total_comma_records}</p>";

if ($total_comma_records > 0) {
    echo "<h4>Records to be processed:</h4>";
    echo "<table style='width: 100%; border-collapse: collapse; margin-top: 10px;'>";
    echo "<tr style='background: #e9ecef;'>";
    echo "<th style='border: 1px solid #dee2e6; padding: 8px; text-align: left;'>ID</th>";
    echo "<th style='border: 1px solid #dee2e6; padding: 8px; text-align: left;'>Request ID</th>";
    echo "<th style='border: 1px solid #dee2e6; padding: 8px; text-align: left;'>Service Title</th>";
    echo "<th style='border: 1px solid #dee2e6; padding: 8px; text-align: left;'>Quantity</th>";
    echo "</tr>";
    
    foreach ($comma_separated_records as $record) {
        echo "<tr>";
        echo "<td style='border: 1px solid #dee2e6; padding: 8px;'>{$record->id}</td>";
        echo "<td style='border: 1px solid #dee2e6; padding: 8px;'>{$record->request_id}</td>";
        echo "<td style='border: 1px solid #dee2e6; padding: 8px;'><span style='color: #dc3545;'>{$record->service_title}</span></td>";
        echo "<td style='border: 1px solid #dee2e6; padding: 8px;'>{$record->quantity}</td>";
        echo "</tr>";
    }
    echo "</table>";
} else {
    echo "<p style='color: #28a745;'>✅ No comma-separated services found. Database is already clean!</p>";
}
echo "</div>";
if ($total_comma_records == 0) {
    echo "<div style='background: #d4edda; border: 1px solid #c3e6cb; border-radius: 5px; padding: 15px; margin: 10px 0;'>";
    echo "<h3>🎉 Migration Complete!</h3>";
    echo "<p>No comma-separated services found. Your database is already in the correct format.</p>";
    echo "</div>";
    return;
}
echo "<h2>Step 2: Migration Confirmation</h2>";
echo "<div style='background: #f8d7da; border: 1px solid #f5c6cb; border-radius: 5px; padding: 15px; margin: 10px 0;'>";
echo "<h3>⚠️ Important:</h3>";
echo "<ul>";
echo "<li>This operation will create new database records</li>";
echo "<li>Original records with comma-separated services will be deleted</li>";
echo "<li>Make sure you have a backup of your database before proceeding</li>";
echo "<li>This operation cannot be easily undone</li>";
echo "</ul>";
echo "</div>";
if (!isset($_POST['confirm_migration'])) {
    echo "<form method='post' style='margin: 20px 0;'>";
    echo "<input type='hidden' name='confirm_migration' value='1'>";
    echo "<button type='submit' style='background: #dc3545; color: white; border: none; padding: 12px 24px; border-radius: 5px; font-size: 16px; cursor: pointer;'>";
    echo "🚀 Start Migration";
    echo "</button>";
    echo "</form>";
    return;
}
echo "<h2>Step 3: Performing Migration</h2>";

$migration_results = [
    'processed' => 0,
    'created' => 0,
    'deleted' => 0,
    'errors' => []
];
$wpdb->query('START TRANSACTION');

try {
    foreach ($comma_separated_records as $record) {
        $migration_results['processed']++;
        $service_titles = array_map('trim', explode(',', $record->service_title));
        
        echo "<div style='background: #e9ecef; padding: 10px; border-radius: 5px; margin: 5px 0;'>";
        echo "<strong>Processing Record ID {$record->id}:</strong> {$record->service_title}<br>";
        echo "Splitting into " . count($service_titles) . " individual services...<br>";
        foreach ($service_titles as $index => $title) {
            if (!empty($title)) {
                $insert_result = $wpdb->insert(
                    $wpdb->prefix . 'nexora_service_details',
                    [
                        'request_id' => $record->request_id,
                        'service_id' => $record->service_id,
                        'service_title' => $title,
                        'quantity' => $record->quantity,
                        'note' => $record->note,
                        'created_at' => $record->created_at,
                        'updated_at' => current_time('mysql')
                    ],
                    ['%d', '%d', '%s', '%d', '%s', '%s', '%s']
                );
                
                if ($insert_result !== false) {
                    $migration_results['created']++;
                    echo "  ✅ Created: {$title}<br>";
                } else {
                    $error_msg = "Failed to create record for: {$title}";
                    $migration_results['errors'][] = $error_msg;
                    echo "  ❌ Error: {$error_msg}<br>";
                }
            }
        }
        $delete_result = $wpdb->delete(
            $wpdb->prefix . 'nexora_service_details',
            ['id' => $record->id],
            ['%d']
        );
        
        if ($delete_result !== false) {
            $migration_results['deleted']++;
            echo "  🗑️ Deleted original record<br>";
        } else {
            $error_msg = "Failed to delete original record ID: {$record->id}";
            $migration_results['errors'][] = $error_msg;
            echo "  ❌ Error: {$error_msg}<br>";
        }
        
        echo "</div>";
    }
    if (empty($migration_results['errors'])) {
        $wpdb->query('COMMIT');
        echo "<div style='background: #d4edda; border: 1px solid #c3e6cb; border-radius: 5px; padding: 15px; margin: 10px 0;'>";
        echo "<h3>✅ Migration Completed Successfully!</h3>";
    } else {
        $wpdb->query('ROLLBACK');
        echo "<div style='background: #f8d7da; border: 1px solid #f5c6cb; border-radius: 5px; padding: 15px; margin: 10px 0;'>";
        echo "<h3>❌ Migration Failed!</h3>";
    }
    
} catch (Exception $e) {
    $wpdb->query('ROLLBACK');
    $migration_results['errors'][] = "Database error: " . $e->getMessage();
    echo "<div style='background: #f8d7da; border: 1px solid #f5c6cb; border-radius: 5px; padding: 15px; margin: 10px 0;'>";
    echo "<h3>❌ Migration Failed!</h3>";
}
echo "<h2>Step 4: Migration Results</h2>";

echo "<div style='background: #f8f9fa; border: 1px solid #dee2e6; border-radius: 5px; padding: 15px; margin: 10px 0;'>";
echo "<h3>📊 Summary:</h3>";
echo "<ul>";
echo "<li><strong>Records Processed:</strong> {$migration_results['processed']}</li>";
echo "<li><strong>New Records Created:</strong> {$migration_results['created']}</li>";
echo "<li><strong>Original Records Deleted:</strong> {$migration_results['deleted']}</li>";
echo "<li><strong>Errors:</strong> " . count($migration_results['errors']) . "</li>";
echo "</ul>";

if (!empty($migration_results['errors'])) {
    echo "<h4>❌ Errors Encountered:</h4>";
    echo "<ul>";
    foreach ($migration_results['errors'] as $error) {
        echo "<li style='color: #dc3545;'>{$error}</li>";
    }
    echo "</ul>";
}
echo "</div>";
echo "<h2>Step 5: Verification</h2>";

$remaining_comma_records = $wpdb->get_var("
    SELECT COUNT(*) 
    FROM {$wpdb->prefix}nexora_service_details 
    WHERE service_title LIKE '%,%'
");

$total_records_after = $wpdb->get_var("
    SELECT COUNT(*) 
    FROM {$wpdb->prefix}nexora_service_details
");

echo "<div style='background: #d1ecf1; border: 1px solid #bee5eb; border-radius: 5px; padding: 15px; margin: 10px 0;'>";
echo "<h3>🔍 Verification Results:</h3>";
echo "<ul>";
echo "<li><strong>Remaining comma-separated records:</strong> {$remaining_comma_records}</li>";
echo "<li><strong>Total service details records:</strong> {$total_records_after}</li>";
echo "</ul>";

if ($remaining_comma_records == 0) {
    echo "<p style='color: #28a745; font-weight: bold;'>✅ Verification successful! All comma-separated services have been split.</p>";
} else {
    echo "<p style='color: #dc3545; font-weight: bold;'>⚠️ Some comma-separated records still remain. Please check the errors above.</p>";
}
echo "</div>";
echo "<h2>Step 6: Sample Results</h2>";

$sample_records = $wpdb->get_results("
    SELECT id, request_id, service_title, quantity
    FROM {$wpdb->prefix}nexora_service_details 
    ORDER BY request_id, id
    LIMIT 10
");

if ($sample_records) {
    echo "<div style='background: #d4edda; border: 1px solid #c3e6cb; border-radius: 5px; padding: 15px; margin: 10px 0;'>";
    echo "<h3>📋 Sample Records After Migration:</h3>";
    echo "<table style='width: 100%; border-collapse: collapse; margin-top: 10px;'>";
    echo "<tr style='background: #c3e6cb;'>";
    echo "<th style='border: 1px solid #28a745; padding: 8px; text-align: left;'>ID</th>";
    echo "<th style='border: 1px solid #28a745; padding: 8px; text-align: left;'>Request ID</th>";
    echo "<th style='border: 1px solid #28a745; padding: 8px; text-align: left;'>Service Title</th>";
    echo "<th style='border: 1px solid #28a745; padding: 8px; text-align: left;'>Quantity</th>";
    echo "</tr>";
    
    foreach ($sample_records as $record) {
        echo "<tr>";
        echo "<td style='border: 1px solid #28a745; padding: 8px;'>{$record->id}</td>";
        echo "<td style='border: 1px solid #28a745; padding: 8px;'>{$record->request_id}</td>";
        echo "<td style='border: 1px solid #28a745; padding: 8px;'><span style='color: #28a745;'>{$record->service_title}</span></td>";
        echo "<td style='border: 1px solid #28a745; padding: 8px;'>{$record->quantity}</td>";
        echo "</tr>";
    }
    echo "</table>";
    echo "</div>";
}

echo "<div style='background: #d4edda; border: 2px solid #28a745; border-radius: 5px; padding: 15px; margin: 20px 0;'>";
echo "<h3>🎉 Migration Complete!</h3>";
echo "<p><strong>Your services are now properly separated in the database.</strong></p>";
echo "<p>Benefits achieved:</p>";
echo "<ul>";
echo "<li>✅ Each service is now a separate database record</li>";
echo "<li>✅ Edit and delete buttons will work properly</li>";
echo "<li>✅ Better data integrity and consistency</li>";
echo "<li>✅ Improved service management capabilities</li>";
echo "<li>✅ Cleaner database structure</li>";
echo "</ul>";
echo "</div>";

echo "<hr>";
echo "<p><strong>Next Steps:</strong></p>";
echo "<ol>";
echo "<li>Test the service management functionality</li>";
echo "<li>Verify that edit and delete buttons work correctly</li>";
echo "<li>Add validation to prevent future comma-separated input</li>";
echo "<li>Update any forms that might create comma-separated services</li>";
echo "</ol>";
?> 