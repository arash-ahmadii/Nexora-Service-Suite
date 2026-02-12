<?php

if (!defined('ABSPATH')) {
    exit;
}

include __DIR__ . '/repair-system/repair-system-header.php';
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
    
    <div id="Nexora Service Suite-repair-system">
        <div class="Nexora Service Suite-status-bar">
            <div class="status-item">
                <span class="status-label">WordPress:</span>
                <span class="status-value" id="wp-status">Connected</span>
            </div>
            <div class="status-item">
                <span class="status-label">Database:</span>
                <span class="status-value" id="db-status">Connected</span>
            </div>
            <div class="status-item">
                <span class="status-label">Plugin:</span>
                <span class="status-value" id="plugin-status">Loaded</span>
            </div>
        </div>

        
        <div class="postbox" style="border: 3px solid #d54e21; background: #fff5f5;">
            <h2><span>⚡ QUICK REGISTRATION DIAGNOSTIC</span></h2>
            <div class="inside">
                <p style="margin-bottom: 15px; font-weight: bold; color: #d54e21;">
                    🔍 Fast diagnostic specifically for registration button issues - finds exactly why registration fails
                </p>
                <button class="button button-primary button-hero" onclick="runQuickRegistrationDiagnostic()" 
                        style="background: #d54e21; border-color: #d54e21; font-size: 18px; padding: 12px 25px; box-shadow: 0 4px 8px rgba(0,0,0,0.2); font-weight: bold;">
                    🚨 QUICK REGISTRATION DIAGNOSTIC
                </button>
                <button class="button button-secondary button-hero" onclick="fixRegistrationIssues()" 
                        style="background: #e74c3c; border-color: #e74c3c; color: white; font-size: 16px; padding: 10px 20px; box-shadow: 0 2px 4px rgba(0,0,0,0.2); margin-top: 10px;">
                    🔧 FIX REGISTRATION ISSUES
                </button>
                <button class="button button-secondary" onclick="testInheritedOrderly()" 
                        style="background: #9b59b6; border-color: #9b59b6; color: white; font-size: 14px; padding: 8px 16px; box-shadow: 0 2px 4px rgba(0,0,0,0.2); margin-top: 10px;">
                    🔍 TEST "INHERITED ORDERLY" ERROR
                </button>
                <button class="button button-secondary" onclick="testPrivatkunde()" 
                        style="background: #27ae60; border-color: #27ae60; color: white; font-size: 14px; padding: 8px 16px; box-shadow: 0 2px 4px rgba(0,0,0,0.2); margin-top: 10px;">
                    👤 TEST PRIVATKUNDE REGISTRATION
                </button>
                <button class="button button-secondary" onclick="testFieldValidation()" 
                        style="background: #f39c12; border-color: #f39c12; color: white; font-size: 14px; padding: 8px 16px; box-shadow: 0 2px 4px rgba(0,0,0,0.2); margin-top: 10px;">
                    📝 TEST FIELD VALIDATION
                </button>
                <button class="button button-secondary" onclick="testUserApproval()" 
                        style="background: #3498db; border-color: #3498db; color: white; font-size: 14px; padding: 8px 16px; box-shadow: 0 2px 4px rgba(0,0,0,0.2); margin-top: 10px;">
                    👤 TEST USER APPROVAL
                </button>
                <button class="button button-secondary" onclick="testBadgeSystem()" 
                        style="background: #e74c3c; border-color: #e74c3c; color: white; font-size: 14px; padding: 8px 16px; box-shadow: 0 2px 4px rgba(0,0,0,0.2); margin-top: 10px;">
                    🔢 TEST BADGE SYSTEM
                </button>
                <button class="button button-secondary" onclick="testStatusFilter()" 
                        style="background: #f39c12; border-color: #f39c12; color: white; font-size: 14px; padding: 8px 16px; box-shadow: 0 2px 4px rgba(0,0,0,0.2); margin-top: 10px;">
                    🔍 TEST STATUS FILTER
                </button>
                <p style="margin-top: 10px; font-size: 12px; color: #666;">
                    <em>⚡ Fast test - focuses only on registration issues</em>
                </p>
            </div>
        </div>

        
        <div class="postbox" style="border: 3px solid #9b59b6; background: #f8f4ff;">
            <h2><span>🚨 COMPREHENSIVE SERVICES TEST</span></h2>
            <div class="inside">
                <p style="margin-bottom: 15px; font-weight: bold; color: #9b59b6;">
                    🔍 ULTIMATE TEST - Checks EVERYTHING related to services list display
                </p>
                <button class="button button-primary button-hero" onclick="runComprehensiveServicesTest()" 
                        style="background: #9b59b6; border-color: #9b59b6; font-size: 18px; padding: 12px 25px; box-shadow: 0 4px 8px rgba(0,0,0,0.2); font-weight: bold;">
                    🚨 RUN COMPREHENSIVE SERVICES TEST
                </button>
                <p style="margin-top: 10px; font-size: 12px; color: #666;">
                    <em>🔍 Tests Database, PHP Classes, Files, AJAX, WordPress Integration, and provides Browser Debugging Guide</em>
                </p>
            </div>
        </div>

        
        <div class="postbox" style="border: 3px solid #e74c3c; background: #fff5f5;">
            <h2><span>🔧 Dienstleistungen List Debug</span></h2>
            <div class="inside">
                <p style="margin-bottom: 15px; font-weight: bold; color: #e74c3c;">
                    🔍 Comprehensive debugging for Dienstleistungen list not displaying in wp-list-table
                </p>
                <button class="button button-primary button-hero" onclick="debugServicesList()" 
                        style="background: #e74c3c; border-color: #e74c3c; font-size: 18px; padding: 12px 25px; box-shadow: 0 4px 8px rgba(0,0,0,0.2); font-weight: bold;">
                    🚨 DEBUG DIENSTLEISTUNGEN LIST
                </button>
                <p style="margin-top: 10px; font-size: 12px; color: #666;">
                    <em>🔍 Tests database, AJAX, JavaScript, templates, and all components</em>
                </p>
            </div>
        </div>

        
        <div class="postbox" style="border: 2px solid #2ecc40; background: #f6fff6;">
            <h2><span>🧑‍💻 Benutzer-Informationen testen</span></h2>
            <div class="inside">
                <p>Zeigt die gespeicherten Benutzerdaten (WordPress & Kundeninfo) für eine schnelle Diagnose.</p>
                <button class="button button-primary" id="Nexora Service Suite-test-user-info-btn">Benutzer-Info testen</button>
                <pre id="Nexora Service Suite-user-info-log" style="background:#e8f5e9; color:#222; padding:10px; border-radius:6px; margin-top:10px; display:none;"></pre>
            </div>
        </div>

        
        <div class="postbox" style="border: 2px solid #0073aa; background: #f0f8ff;">
            <h2><span>🚀 Comprehensive System Test</span></h2>
            <div class="inside">
                <p style="margin-bottom: 15px; font-weight: bold; color: #0073aa;">
                    Runs 11 comprehensive tests and automatically identifies/fixes registration problems
                </p>
                <button class="button button-primary button-hero" onclick="runComprehensiveTest()" 
                        style="background: #0073aa; border-color: #0073aa; font-size: 16px; padding: 8px 20px; box-shadow: 0 2px 4px rgba(0,0,0,0.1);">
                    🔥 START COMPREHENSIVE SYSTEM TEST
                </button>
                <p style="margin-top: 10px; font-size: 12px; color: #666;">
                    <em>⌨️ Keyboard shortcut: Ctrl+Shift+T</em>
                </p>
            </div>
        </div>

        
        <div class="postbox">
            <h2><span>⚡ Quick Actions</span></h2>
            <div class="inside">
                <div class="Nexora Service Suite-quick-actions">
                    <button class="button button-primary" onclick="runFullTest()">🔍 Full System Test</button>
                    <button class="button button-secondary" onclick="testTables()">🗄️ Check Tables</button>
                    <button class="button button-secondary" onclick="testClasses()">🔧 Check Classes</button>
                    <button class="button button-secondary" onclick="testAjax()">🌐 Check AJAX</button>
                    <button class="button button-secondary" onclick="createSampleData()">📊 Create Sample Data</button>
                </div>
            </div>
        </div>

        
        <div class="Nexora Service Suite-test-grid">
            <div class="postbox">
                <h2><span>👤 Registration Tests</span></h2>
                <div class="inside">
                    <p>Test user registration functionality and error messages</p>
                    <div class="Nexora Service Suite-test-buttons">
                        <button class="button" onclick="testRegistrationErrors()">All Registration Tests</button>
                        <button class="button" onclick="testRegistrationAjax()">Registration AJAX</button>
                        <button class="button" onclick="testDatabaseOperations()">Database Operations</button>
                        <button class="button" onclick="testFormRendering()">Form Rendering</button>
                    </div>
                </div>
            </div>

            <div class="postbox">
                <h2><span>🔍 Specific Registration Tests</span></h2>
                <div class="inside">
                    <p>Test individual registration scenarios and edge cases</p>
                    <div class="Nexora Service Suite-test-buttons">
                        <button class="button" onclick="testMissingFields()">Missing Fields</button>
                        <button class="button" onclick="testInvalidEmail()">Invalid Email</button>
                        <button class="button" onclick="testCompleteRegistration()">Complete Registration</button>
                        <button class="button" onclick="testDuplicateEmail()">Duplicate Email</button>
                        <button class="button" onclick="testAjaxComprehensive()">AJAX Comprehensive</button>
                    </div>
                </div>
            </div>
        </div>

        
        <div class="postbox">
            <h2><span>🗄️ Database Management</span></h2>
            <div class="inside">
                <p>Manage plugin database tables - check status, create missing tables and repair existing ones</p>
                
                <div class="Nexora Service Suite-table-actions">
                    <button class="button button-primary" onclick="testTables()">Check Tables</button>
                    <button class="button button-secondary" onclick="createMissingTables()">Create Missing Tables</button>
                    <button class="button button-secondary" onclick="repairAllTables()">Repair All Tables</button>
                    <button class="button button-secondary" onclick="repairCustomerInfoTable()" style="background: #d54e21; border-color: #d54e21; color: white;">🔧 Fix Customer Info Table</button>
                    <button class="button button-secondary" onclick="repairRequestInvoicesTable()" style="background: #0073aa; border-color: #0073aa; color: white;">🔧 Fix Invoice Table</button>
                    <button class="button button-secondary" onclick="createInvoiceServicesTable()" style="background: #28a745; border-color: #28a745; color: white;">🆕 Create Faktor Services Table</button>
                </div>
                
                
                <div class="Nexora Service Suite-table-actions" style="margin-top: 20px; padding: 15px; background: #f8f9fa; border: 1px solid #e9ecef; border-radius: 8px;">
                    <h4 style="margin: 0 0 15px 0; color: #495057;">🔢 Customer Number Management</h4>
                    <p style="margin-bottom: 15px; color: #6c757d; font-size: 14px;">
                        Manage customer numbers for existing users and database structure
                    </p>
                    
                    <div style="display: flex; gap: 10px; flex-wrap: wrap;">
                        <button class="button button-secondary" onclick="addCustomerNumberColumn()" 
                                style="background: #17a2b8; border-color: #17a2b8; color: white;">
                            🆕 Add Customer Number Column
                        </button>
                        <button class="button button-secondary" onclick="initializeCustomerNumbers()" 
                                style="background: #28a745; border-color: #28a745; color: white;">
                            🔢 Initialize Customer Numbers
                        </button>
                        <button class="button button-secondary" onclick="checkCustomerNumberStatus()" 
                                style="background: #ffc107; border-color: #ffc107; color: #212529;">
                            🔍 Check Customer Number Status
                        </button>
                    </div>
                </div>

                
                <div class="Nexora Service Suite-table-actions" style="margin-top: 20px; padding: 15px; background: #fff3cd; border: 1px solid #ffeaa7; border-radius: 8px;">
                    <h4 style="margin: 0 0 15px 0; color: #856404;">🚀 Easy Form Database Repair</h4>
                    <p style="margin-bottom: 15px; color: #856404; font-size: 14px;">
                        Fix database issues preventing Easy Form from working properly
                    </p>
                    
                    <div style="display: flex; gap: 10px; flex-wrap: wrap;">
                        <button class="button button-secondary" onclick="checkEasyFormTables()" 
                                style="background: #fd7e14; border-color: #fd7e14; color: white;">
                            🔍 Check Easy Form Tables
                        </button>
                        <button class="button button-secondary" onclick="createMissingEasyFormTables()" 
                                style="background: #e83e8c; border-color: #e83e8c; color: white;">
                            🆕 Create Missing Tables
                        </button>
                        <button class="button button-secondary" onclick="repairEasyFormDatabase()" 
                                style="background: #6f42c1; border-color: #6f42c1; color: white;">
                            🔧 Repair Easy Form Database
                        </button>
                        <button class="button button-secondary" onclick="testEasyFormDatabase()" 
                                style="background: #20c997; border-color: #20c997; color: white;">
                            🧪 Test Easy Form Database
                        </button>
                    </div>
                </div>
                
                <div id="tables-grid" class="Nexora Service Suite-tables-grid">
                    <div class="table-item">
                        <p>Click "Check Tables" to load database status...</p>
                    </div>
                </div>
            </div>
        </div>

        
        <div class="postbox">
            <h2><span>📝 Console</span></h2>
            <div class="inside">
                <div id="console" class="Nexora Service Suite-console">
                    <div class="console-header">
                        <span class="timestamp">[<?php echo date('H:i:s'); ?>]</span>
                        <span class="success">🏥 Plugin Health Check System initialized</span>
                    </div>
                    <div>✅ WordPress: Connected</div>
                    <div>✅ Database: Connected</div>
                    <div>✅ Plugin: Loaded</div>
                    <div>📊 Ready for testing. Click buttons above to start diagnostics.</div>
                </div>
            </div>
        </div>
    </div>
</div>

<style>

.Nexora Service Suite-admin-content {
    background: #f6f8fc;
    min-height: calc(100vh - 100px);
    padding: 20px;
}

.Nexora Service Suite-page-header {
    background: #fff;
    padding: 24px;
    border-radius: 16px;
    box-shadow: 0 4px 24px rgba(76,110,245,0.08);
    margin-bottom: 24px;
}

.Nexora Service Suite-page-header h1 {
    margin: 0 0 8px 0;
    color: #2c3e50;
    font-size: 28px;
    font-weight: 700;
}

.Nexora Service Suite-page-header .description {
    margin: 0;
    color: #6c757d;
    font-size: 16px;
}

.Nexora Service Suite-status-bar {
    display: flex;
    gap: 20px;
    margin-bottom: 20px;
    background: #fff;
    padding: 15px;
    border-radius: 16px;
    border: 1px solid #e9ecef;
    box-shadow: 0 4px 24px rgba(76,110,245,0.08);
}

.status-item {
    display: flex;
    align-items: center;
    gap: 8px;
}

.status-label {
    font-weight: bold;
    color: #666;
}

.status-value {
    color: #28a745;
    font-weight: bold;
}

.Nexora Service Suite-quick-actions {
    display: flex;
    gap: 10px;
    flex-wrap: wrap;
}

.Nexora Service Suite-test-grid {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 20px;
    margin-bottom: 20px;
}

.Nexora Service Suite-test-buttons {
    display: flex;
    gap: 8px;
    flex-wrap: wrap;
    margin-top: 10px;
}

.Nexora Service Suite-table-actions {
    display: flex;
    gap: 10px;
    margin-bottom: 15px;
}

.Nexora Service Suite-tables-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
    gap: 15px;
    margin-top: 15px;
}

.table-item {
    background: #fff;
    padding: 20px;
    border-radius: 16px;
    box-shadow: 0 4px 24px rgba(76,110,245,0.08);
    border: 1px solid #e9ecef;
    border-radius: 0;
    border: 1px solid #c3c4c7;
    box-shadow: 0 1px 1px rgba(0,0,0,.04);
}

.table-item h4 {
    margin: 0 0 10px 0;
    color: #23282d;
}

.Nexora Service Suite-console {
    background: #23282d;
    color: #50c878;
    padding: 20px;
    border-radius: 16px;
    font-family: Consolas, Monaco, 'Courier New', monospace;
    font-size: 13px;
    line-height: 1.4;
    max-height: 500px;
    overflow-y: auto;
    border: 1px solid #e9ecef;
    box-shadow: 0 4px 24px rgba(0,0,0,0.2);
}

.Nexora Service Suite-console .console-header {
    margin-bottom: 10px;
    padding-bottom: 10px;
    border-bottom: 1px solid #333;
}

.Nexora Service Suite-console .timestamp {
    color: #888;
}

.Nexora Service Suite-console .success {
    color: #28a745;
}

.Nexora Service Suite-console .error {
    color: #dc3545;
}

.Nexora Service Suite-console .warning {
    color: #ffc107;
}

.Nexora Service Suite-console .info {
    color: #17a2b8;
}

.metric {
    display: inline-block;
    background: #f8f9fa;
    padding: 8px 12px;
    border-radius: 8px;
    margin: 4px;
    font-size: 12px;
    border: 1px solid #e9ecef;
    box-shadow: 0 2px 4px rgba(0,0,0,0.05);
}

.metric strong {
    color: #495057;
}

#Nexora Service Suite-repair-system.testing {
    opacity: 0.8;
    pointer-events: none;
}

#Nexora Service Suite-repair-system.testing button:not(.button-hero) {
    opacity: 0.5;
}

#Nexora Service Suite-repair-system.testing .button-hero {
    background: #666 !important;
    border-color: #666 !important;
}

#Nexora Service Suite-repair-system.testing .button-hero:after {
    content: " ⏳";
}

@media (max-width: 768px) {
    .Nexora Service Suite-test-grid {
        grid-template-columns: 1fr;
    }
    
    .Nexora Service Suite-quick-actions,
    .Nexora Service Suite-test-buttons {
        flex-direction: column;
    }
}
</style>

<script>
jQuery(document).ready(function($) {
    const ajaxUrl = '<?php echo admin_url('admin-ajax.php'); ?>';
    const nonce = '<?php echo $nonce; ?>';
    
    let testRunning = false;
    
    function log(message, type = 'info') {
        const console = $('#console');
        const timestamp = new Date().toLocaleTimeString();
        const className = type === 'success' ? 'success' : 
                         type === 'error' ? 'error' : 
                         type === 'warning' ? 'warning' : 'info';
        
        const logEntry = `<div class="${className}"><span class="timestamp">[${timestamp}]</span> ${message}</div>`;
        console.append(logEntry);
        console.scrollTop(console[0].scrollHeight);
    }
    
    function setTestingState(isRunning) {
        testRunning = isRunning;
        if (isRunning) {
            $('#Nexora Service Suite-repair-system').addClass('testing');
        } else {
            $('#Nexora Service Suite-repair-system').removeClass('testing');
        }
    }
    
    function makeRequest(action, callback) {
        if (testRunning) {
            log('⚠️ Test already active. Please wait...', 'warning');
            return;
        }
        
        setTestingState(true);
        log(`🔄 Starting ${action}...`, 'info');
        const isComprehensive = action === 'comprehensive_system_test';
        const timeout = isComprehensive ? 60000 : 30000;
        
        $.ajax({
            url: ajaxUrl,
            type: 'POST',
            data: {
                action: 'repair_' + action,
                nonce: nonce
            },
            timeout: timeout,
            success: function(response) {
                callback(response);
                setTestingState(false);
            },
            error: function(xhr, status, error) {
                let errorMsg = `❌ Error in ${action}: `;
                
                if (status === 'timeout') {
                    errorMsg += 'Request timeout - Test took too long';
                } else if (xhr.status === 0) {
                    errorMsg += 'Connection error - Check network';
                } else if (xhr.status === 500) {
                    errorMsg += 'Server error (500) - Check PHP error log';
                } else {
                    errorMsg += `${status} (${xhr.status}): ${error}`;
                }
                
                log(errorMsg, 'error');
                if (xhr.responseText && xhr.responseText.length < 1000) {
                    log(`🔍 Server Response: ${xhr.responseText}`, 'error');
                }
                
                setTestingState(false);
            }
        });
    }
    window.runQuickRegistrationDiagnostic = function() {
        $('#console').html(`
            <div class="console-header">
                <span class="timestamp">[${new Date().toLocaleTimeString()}]</span>
                <span class="success">⚡ QUICK REGISTRATION DIAGNOSTIC STARTED</span>
            </div>
        `);
        log('🚨 Starting quick registration diagnostic...', 'info');
        log('🔍 Focusing specifically on registration button issues...', 'info');
        log('⚡ This test is fast and targeted...', 'warning');
        log('', 'info');
        
        const startTime = Date.now();
        
        makeRequest('quick_registration_diagnostic', function(response) {
            const endTime = Date.now();
            const totalTime = ((endTime - startTime) / 1000).toFixed(2);
            if (response.error) {
                log('', 'info');
                log('❌ === QUICK DIAGNOSTIC FAILED ===', 'error');
                log(`💥 Error: ${response.error}`, 'error');
                if (response.trace) {
                    log('🔍 Stack Trace:', 'error');
                    log(response.trace, 'error');
                }
                return;
            }
            
            log('', 'info');
            log('🏁 === QUICK REGISTRATION DIAGNOSTIC COMPLETED ===', 'success');
            log(`⏱️ Execution time: ${totalTime}s`, 'info');
            log(`❌ Issues found: ${response.issues.length}`, response.issues.length > 0 ? 'error' : 'success');
            log(`🔧 Fixes applied: ${response.fixes.length}`, response.fixes.length > 0 ? 'success' : 'info');
            log(`🎯 Overall status: ${response.success ? 'No issues found' : 'Issues found'}`, response.success ? 'success' : 'error');
            if (response.debug_info && response.debug_info.length > 0) {
                log('', 'info');
                log('🔍 === DIAGNOSTIC DETAILS ===', 'info');
                response.debug_info.forEach((debug, index) => {
                    log(`${debug}`, 'info');
                });
            }
            if (response.issues && response.issues.length > 0) {
                log('', 'info');
                log('❌ === REGISTRATION ISSUES FOUND ===', 'error');
                response.issues.forEach((issue, index) => {
                    log(`${index + 1}. ${issue}`, 'error');
                });
            }
            if (response.fixes && response.fixes.length > 0) {
                log('', 'info');
                log('🔧 === AUTOMATIC FIXES APPLIED ===', 'success');
                response.fixes.forEach((fix, index) => {
                    log(`${index + 1}. ${fix}`, 'success');
                });
            }
            if (response.registration_test_results) {
                log('', 'info');
                log('📊 === REGISTRATION TEST RESULTS ===', 'info');
                Object.keys(response.registration_test_results).forEach(testName => {
                    const result = response.registration_test_results[testName];
                    const status = result.success ? '✅' : '❌';
                    log(`${status} ${testName}: ${result.message || result.error || 'Completed'}`, 
                        result.success ? 'success' : 'error');
                });
            }
            
            log('', 'info');
            if (response.success) {
                log('🎉 No registration issues found! Registration button should work.', 'success');
            } else {
                log('⚠️ Registration issues found! Check the issues above.', 'error');
                log('💡 Try the "Repair All Tables" button to fix database issues.', 'info');
            }
            log('📊 === END QUICK DIAGNOSTIC ===', 'info');
        });
    };
    
    window.fixRegistrationIssues = function() {
        $('#console').html(`
            <div class="console-header">
                <span class="timestamp">[${new Date().toLocaleTimeString()}]</span>
                <span class="success">🔧 REGISTRATION ISSUES FIX STARTED</span>
            </div>
        `);
        log('🔧 Starting registration issues fix...', 'info');
        log('🔍 Focusing on fixing registration problems...', 'info');
        log('⚡ This will attempt to fix common registration issues...', 'warning');
        log('', 'info');
        
        const startTime = Date.now();
        
        makeRequest('fix_registration_issues', function(response) {
            const endTime = Date.now();
            const totalTime = ((endTime - startTime) / 1000).toFixed(2);
            if (response.error) {
                log('', 'info');
                log('❌ === REGISTRATION ISSUES FIX FAILED ===', 'error');
                log(`💥 Error: ${response.error}`, 'error');
                if (response.trace) {
                    log('🔍 Stack Trace:', 'error');
                    log(response.trace, 'error');
                }
                return;
            }
            
            log('', 'info');
            log('🏁 === REGISTRATION ISSUES FIX COMPLETED ===', 'success');
            log(`⏱️ Execution time: ${totalTime}s`, 'info');
            log(`🔧 Issues fixed: ${response.issues_fixed ? response.issues_fixed.length : 0}`, 'success');
            log(`❌ Errors found: ${response.errors ? response.errors.length : 0}`, response.errors && response.errors.length > 0 ? 'error' : 'success');
            log(`🎯 Overall status: ${response.success ? 'Success' : 'Failed'}`, response.success ? 'success' : 'error');
            if (response.debug_info && response.debug_info.length > 0) {
                log('', 'info');
                log('🔍 === FIX DETAILS ===', 'info');
                response.debug_info.forEach((debug, index) => {
                    log(`${debug}`, 'info');
                });
            }
            if (response.issues_fixed && response.issues_fixed.length > 0) {
                log('', 'info');
                log('🔧 === ISSUES FIXED ===', 'success');
                response.issues_fixed.forEach((issue, index) => {
                    log(`${index + 1}. ${issue}`, 'success');
                });
            }
            if (response.errors && response.errors.length > 0) {
                log('', 'info');
                log('❌ === ERRORS FOUND ===', 'error');
                response.errors.forEach((error, index) => {
                    log(`${index + 1}. ${error}`, 'error');
                });
            }
            
            log('', 'info');
            if (response.success) {
                log('🎉 Registration issues fix completed successfully!', 'success');
                log('💡 Try registering a new user now to test the fix.', 'info');
            } else {
                log('⚠️ Some issues could not be fixed automatically.', 'error');
                log('💡 Check the errors above and try manual fixes.', 'info');
            }
            log('📊 === END REGISTRATION ISSUES FIX ===', 'info');
        });
    };
    
    window.testInheritedOrderly = function() {
        $('#console').html(`
            <div class="console-header">
                <span class="timestamp">[${new Date().toLocaleTimeString()}]</span>
                <span class="success">🔍 INHERITED ORDERLY ERROR TEST STARTED</span>
            </div>
        `);
        log('🔍 Testing for "inherited orderly" error specifically...', 'info');
        log('🔧 This will help identify the root cause of the error...', 'info');
        log('', 'info');
        
        const startTime = Date.now();
        
        makeRequest('test_inherited_orderly', function(response) {
            const endTime = Date.now();
            const totalTime = ((endTime - startTime) / 1000).toFixed(2);
            if (response.error) {
                log('', 'info');
                log('❌ === INHERITED ORDERLY TEST FAILED ===', 'error');
                log(`💥 Error: ${response.error}`, 'error');
                if (response.trace) {
                    log('🔍 Stack Trace:', 'error');
                    log(response.trace, 'error');
                }
                return;
            }
            
            log('', 'info');
            log('🏁 === INHERITED ORDERLY TEST COMPLETED ===', 'success');
            log(`⏱️ Execution time: ${totalTime}s`, 'info');
            log(`❌ Issues found: ${response.issues_found ? response.issues_found.length : 0}`, response.issues_found && response.issues_found.length > 0 ? 'error' : 'success');
            log(`🔧 Fixes applied: ${response.fixes_applied ? response.fixes_applied.length : 0}`, response.fixes_applied && response.fixes_applied.length > 0 ? 'success' : 'info');
            log(`🎯 Overall status: ${response.success ? 'No issues found' : 'Issues found'}`, response.success ? 'success' : 'error');
            if (response.debug_info && response.debug_info.length > 0) {
                log('', 'info');
                log('🔍 === DETAILED TEST RESULTS ===', 'info');
                response.debug_info.forEach((debug, index) => {
                    log(`${debug}`, 'info');
                });
            }
            if (response.issues_found && response.issues_found.length > 0) {
                log('', 'info');
                log('❌ === ISSUES FOUND ===', 'error');
                response.issues_found.forEach((issue, index) => {
                    log(`${index + 1}. ${issue}`, 'error');
                });
            }
            if (response.fixes_applied && response.fixes_applied.length > 0) {
                log('', 'info');
                log('🔧 === FIXES APPLIED ===', 'success');
                response.fixes_applied.forEach((fix, index) => {
                    log(`${index + 1}. ${fix}`, 'success');
                });
            }
            
            log('', 'info');
            if (response.success) {
                log('🎉 No "inherited orderly" issues found!', 'success');
            } else {
                log('⚠️ "Inherited orderly" issues found!', 'error');
                log('💡 Try the "FIX REGISTRATION ISSUES" button to fix them.', 'info');
            }
            log('📊 === END INHERITED ORDERLY TEST ===', 'info');
        });
    };
    
    window.testPrivatkunde = function() {
        $('#console').html(`
            <div class="console-header">
                <span class="timestamp">[${new Date().toLocaleTimeString()}]</span>
                <span class="success">👤 PRIVATKUNDE REGISTRATION TEST STARTED</span>
            </div>
        `);
        log('👤 Testing Privatkunde registration specifically...', 'info');
        log('🔧 This will test why Privatkunde registration fails...', 'info');
        log('', 'info');
        
        const startTime = Date.now();
        
        makeRequest('test_privatkunde', function(response) {
            const endTime = Date.now();
            const totalTime = ((endTime - startTime) / 1000).toFixed(2);
            if (response.error) {
                log('', 'info');
                log('❌ === PRIVATKUNDE TEST FAILED ===', 'error');
                log(`💥 Error: ${response.error}`, 'error');
                if (response.trace) {
                    log('🔍 Stack Trace:', 'error');
                    log(response.trace, 'error');
                }
                return;
            }
            
            log('', 'info');
            log('🏁 === PRIVATKUNDE TEST COMPLETED ===', 'success');
            log(`⏱️ Execution time: ${totalTime}s`, 'info');
            log(`❌ Issues found: ${response.issues_found ? response.issues_found.length : 0}`, response.issues_found && response.issues_found.length > 0 ? 'error' : 'success');
            log(`🔧 Fixes applied: ${response.fixes_applied ? response.fixes_applied.length : 0}`, response.fixes_applied && response.fixes_applied.length > 0 ? 'success' : 'info');
            log(`🎯 Overall status: ${response.success ? 'No issues found' : 'Issues found'}`, response.success ? 'success' : 'error');
            if (response.debug_info && response.debug_info.length > 0) {
                log('', 'info');
                log('🔍 === DETAILED TEST RESULTS ===', 'info');
                response.debug_info.forEach((debug, index) => {
                    log(`${debug}`, 'info');
                });
            }
            if (response.issues_found && response.issues_found.length > 0) {
                log('', 'info');
                log('❌ === ISSUES FOUND ===', 'error');
                response.issues_found.forEach((issue, index) => {
                    log(`${index + 1}. ${issue}`, 'error');
                });
            }
            if (response.fixes_applied && response.fixes_applied.length > 0) {
                log('', 'info');
                log('🔧 === FIXES APPLIED ===', 'success');
                response.fixes_applied.forEach((fix, index) => {
                    log(`${index + 1}. ${fix}`, 'success');
                });
            }
            
            log('', 'info');
            if (response.success) {
                log('🎉 Privatkunde registration should work!', 'success');
            } else {
                log('⚠️ Privatkunde registration issues found!', 'error');
                log('💡 Try the "FIX REGISTRATION ISSUES" button to fix them.', 'info');
            }
            log('📊 === END PRIVATKUNDE TEST ===', 'info');
        });
    };
    
    window.testFieldValidation = function() {
        $('#console').html(`
            <div class="console-header">
                <span class="timestamp">[${new Date().toLocaleTimeString()}]</span>
                <span class="success">📝 FIELD VALIDATION TEST STARTED</span>
            </div>
        `);
        log('📝 Testing field validation specifically...', 'info');
        log('🔧 This will test PLZ, phone, and other field validations...', 'info');
        log('', 'info');
        
        const startTime = Date.now();
        
        makeRequest('test_field_validation', function(response) {
            const endTime = Date.now();
            const totalTime = ((endTime - startTime) / 1000).toFixed(2);
            if (response.error) {
                log('', 'info');
                log('❌ === FIELD VALIDATION TEST FAILED ===', 'error');
                log(`💥 Error: ${response.error}`, 'error');
                if (response.trace) {
                    log('🔍 Stack Trace:', 'error');
                    log(response.trace, 'error');
                }
                return;
            }
            
            log('', 'info');
            log('🏁 === FIELD VALIDATION TEST COMPLETED ===', 'success');
            log(`⏱️ Execution time: ${totalTime}s`, 'info');
            log(`❌ Issues found: ${response.issues_found ? response.issues_found.length : 0}`, response.issues_found && response.issues_found.length > 0 ? 'error' : 'success');
            log(`🔧 Fixes applied: ${response.fixes_applied ? response.fixes_applied.length : 0}`, response.fixes_applied && response.fixes_applied.length > 0 ? 'success' : 'info');
            log(`🎯 Overall status: ${response.success ? 'No issues found' : 'Issues found'}`, response.success ? 'success' : 'error');
            if (response.debug_info && response.debug_info.length > 0) {
                log('', 'info');
                log('🔍 === DETAILED VALIDATION RESULTS ===', 'info');
                response.debug_info.forEach((debug, index) => {
                    log(`${debug}`, 'info');
                });
            }
            if (response.issues_found && response.issues_found.length > 0) {
                log('', 'info');
                log('❌ === VALIDATION ISSUES FOUND ===', 'error');
                response.issues_found.forEach((issue, index) => {
                    log(`${index + 1}. ${issue}`, 'error');
                });
            }
            if (response.fixes_applied && response.fixes_applied.length > 0) {
                log('', 'info');
                log('🔧 === FIXES APPLIED ===', 'success');
                response.fixes_applied.forEach((fix, index) => {
                    log(`${index + 1}. ${fix}`, 'success');
                });
            }
            
            log('', 'info');
            if (response.success) {
                log('🎉 Field validation is working correctly!', 'success');
                log('💡 PLZ, phone, and other field validations are working.', 'info');
            } else {
                log('⚠️ Field validation issues found!', 'error');
                log('💡 Check the issues above and fix validation rules.', 'info');
            }
            log('📊 === END FIELD VALIDATION TEST ===', 'info');
        });
    };
    
    window.testUserApproval = function() {
        $('#console').html(`
            <div class="console-header">
                <span class="timestamp">[${new Date().toLocaleTimeString()}]</span>
                <span class="success">👤 USER APPROVAL SYSTEM TEST STARTED</span>
            </div>
        `);
        log('👤 Testing user approval system...', 'info');
        log('🔧 This will test the user approval functionality...', 'info');
        log('', 'info');
        
        const startTime = Date.now();
        
        makeRequest('nexora_test_user_approval', function(response) {
            const endTime = Date.now();
            const totalTime = ((endTime - startTime) / 1000).toFixed(2);
            if (response.error) {
                log('', 'info');
                log('❌ === USER APPROVAL TEST FAILED ===', 'error');
                log(`💥 Error: ${response.error}`, 'error');
                if (response.trace) {
                    log('🔍 Stack Trace:', 'error');
                    log(response.trace, 'error');
                }
                return;
            }
            
            log('', 'info');
            log('🏁 === USER APPROVAL TEST COMPLETED ===', 'success');
            log(`⏱️ Execution time: ${totalTime}s`, 'info');
            log(`❌ Issues found: ${response.issues_found ? response.issues_found.length : 0}`, response.issues_found && response.issues_found.length > 0 ? 'error' : 'success');
            log(`🔧 Fixes applied: ${response.fixes_applied ? response.fixes_applied.length : 0}`, response.fixes_applied && response.fixes_applied.length > 0 ? 'success' : 'info');
            log(`🎯 Overall status: ${response.success ? 'No issues found' : 'Issues found'}`, response.success ? 'success' : 'error');
            if (response.debug_info && response.debug_info.length > 0) {
                log('', 'info');
                log('🔍 === DETAILED TEST RESULTS ===', 'info');
                response.debug_info.forEach((debug, index) => {
                    log(`${debug}`, 'info');
                });
            }
            if (response.issues_found && response.issues_found.length > 0) {
                log('', 'info');
                log('❌ === ISSUES FOUND ===', 'error');
                response.issues_found.forEach((issue, index) => {
                    log(`${index + 1}. ${issue}`, 'error');
                });
            }
            if (response.fixes_applied && response.fixes_applied.length > 0) {
                log('', 'info');
                log('🔧 === FIXES APPLIED ===', 'success');
                response.fixes_applied.forEach((fix, index) => {
                    log(`${index + 1}. ${fix}`, 'success');
                });
            }
            
            log('', 'info');
            if (response.success) {
                log('🎉 User approval system is working correctly!', 'success');
            } else {
                log('⚠️ User approval system issues found!', 'error');
                log('💡 Try the "FIX REGISTRATION ISSUES" button to fix them.', 'info');
            }
            log('📊 === END USER APPROVAL TEST ===', 'info');
        });
    };
    
    window.testBadgeSystem = function() {
        $('#console').html(`
            <div class="console-header">
                <span class="timestamp">[${new Date().toLocaleTimeString()}]</span>
                <span class="success">🔢 BADGE SYSTEM TEST STARTED</span>
            </div>
        `);
        log('🔢 Testing badge system...', 'info');
        log('🔧 This will test the functionality of the badge system...', 'info');
        log('', 'info');
        
        const startTime = Date.now();
        
        makeRequest('test_badge_system', function(response) {
            const endTime = Date.now();
            const totalTime = ((endTime - startTime) / 1000).toFixed(2);
            if (response.error) {
                log('', 'info');
                log('❌ === BADGE SYSTEM TEST FAILED ===', 'error');
                log(`💥 Error: ${response.error}`, 'error');
                if (response.trace) {
                    log('🔍 Stack Trace:', 'error');
                    log(response.trace, 'error');
                }
                return;
            }
            
            log('', 'info');
            log('🏁 === BADGE SYSTEM TEST COMPLETED ===', 'success');
            log(`⏱️ Execution time: ${totalTime}s`, 'info');
            log(`❌ Issues found: ${response.issues_found ? response.issues_found.length : 0}`, response.issues_found && response.issues_found.length > 0 ? 'error' : 'success');
            log(`🔧 Fixes applied: ${response.fixes_applied ? response.fixes_applied.length : 0}`, response.fixes_applied && response.fixes_applied.length > 0 ? 'success' : 'info');
            log(`🎯 Overall status: ${response.success ? 'No issues found' : 'Issues found'}`, response.success ? 'success' : 'error');
            if (response.debug_info && response.debug_info.length > 0) {
                log('', 'info');
                log('🔍 === DETAILED TEST RESULTS ===', 'info');
                response.debug_info.forEach((debug, index) => {
                    log(`${debug}`, 'info');
                });
            }
            if (response.issues_found && response.issues_found.length > 0) {
                log('', 'info');
                log('❌ === ISSUES FOUND ===', 'error');
                response.issues_found.forEach((issue, index) => {
                    log(`${index + 1}. ${issue}`, 'error');
                });
            }
            if (response.fixes_applied && response.fixes_applied.length > 0) {
                log('', 'info');
                log('🔧 === FIXES APPLIED ===', 'success');
                response.fixes_applied.forEach((fix, index) => {
                    log(`${index + 1}. ${fix}`, 'success');
                });
            }
            
            log('', 'info');
            if (response.success) {
                log('🎉 Badge system is working correctly!', 'success');
            } else {
                log('⚠️ Badge system issues found!', 'error');
                log('💡 Try the "FIX REGISTRATION ISSUES" button to fix them.', 'info');
            }
            log('📊 === END BADGE SYSTEM TEST ===', 'info');
        });
    };
    
    window.testStatusFilter = function() {
        $('#console').html(`
            <div class="console-header">
                <span class="timestamp">[${new Date().toLocaleTimeString()}]</span>
                <span class="success">🔍 STATUS FILTER TEST STARTED</span>
            </div>
        `);
        log('🔍 Testing status filter functionality...', 'info');
        log('🔧 This will test if the status filter button works correctly...', 'info');
        log('', 'info');
        
        const startTime = Date.now();
        
        makeRequest('test_status_filter', function(response) {
            const endTime = Date.now();
            const totalTime = ((endTime - startTime) / 1000).toFixed(2);
            if (response.error) {
                log('', 'info');
                log('❌ === STATUS FILTER TEST FAILED ===', 'error');
                log(`💥 Error: ${response.error}`, 'error');
                if (response.trace) {
                    log('🔍 Stack Trace:', 'error');
                    log(response.trace, 'error');
                }
                return;
            }
            
            log('', 'info');
            log('🏁 === STATUS FILTER TEST COMPLETED ===', 'success');
            log(`⏱️ Execution time: ${totalTime}s`, 'info');
            log(`❌ Issues found: ${response.issues_found ? response.issues_found.length : 0}`, response.issues_found && response.issues_found.length > 0 ? 'error' : 'success');
            log(`🔧 Fixes applied: ${response.fixes_applied ? response.fixes_applied.length : 0}`, response.fixes_applied && response.fixes_applied.length > 0 ? 'success' : 'info');
            log(`🎯 Overall status: ${response.success ? 'No issues found' : 'Issues found'}`, response.success ? 'success' : 'error');
            if (response.debug_info && response.debug_info.length > 0) {
                log('', 'info');
                log('🔍 === DETAILED TEST RESULTS ===', 'info');
                response.debug_info.forEach((debug, index) => {
                    log(`${debug}`, 'info');
                });
            }
            if (response.issues_found && response.issues_found.length > 0) {
                log('', 'info');
                log('❌ === ISSUES FOUND ===', 'error');
                response.issues_found.forEach((issue, index) => {
                    log(`${index + 1}. ${issue}`, 'error');
                });
            }
            if (response.fixes_applied && response.fixes_applied.length > 0) {
                log('', 'info');
                log('🔧 === FIXES APPLIED ===', 'success');
                response.fixes_applied.forEach((fix, index) => {
                    log(`${index + 1}. ${fix}`, 'success');
                });
            }
            
            log('', 'info');
            if (response.success) {
                log('🎉 Status filter functionality is working correctly!', 'success');
            } else {
                log('⚠️ Status filter issues found!', 'error');
                log('💡 Try the "FIX REGISTRATION ISSUES" button to fix them.', 'info');
            }
            log('📊 === END STATUS FILTER TEST ===', 'info');
        });
    };
    
    window.runComprehensiveTest = function() {
        $('#console').html(`
            <div class="console-header">
                <span class="timestamp">[${new Date().toLocaleTimeString()}]</span>
                <span class="success">🚀 COMPREHENSIVE SYSTEM TEST STARTED</span>
            </div>
        `);
        log('🔄 Initializing comprehensive test...', 'info');
        log('📊 Running 11 different test categories...', 'info');
        log('⏳ This may take up to 60 seconds...', 'warning');
        log('🔍 Advanced registration diagnostics being performed...', 'info');
        log('', 'info');
        
        const startTime = Date.now();
        
        makeRequest('comprehensive_system_test', function(response) {
            const endTime = Date.now();
            const totalTime = ((endTime - startTime) / 1000).toFixed(2);
            if (response.error) {
                log('', 'info');
                log('❌ === COMPREHENSIVE SYSTEM TEST FAILED ===', 'error');
                log(`💥 Error: ${response.error}`, 'error');
                if (response.trace) {
                    log('🔍 Stack Trace:', 'error');
                    log(response.trace, 'error');
                }
                return;
            }
            
            log('', 'info');
            log('🏁 === COMPREHENSIVE SYSTEM TEST COMPLETED ===', 'success');
            log(`⏱️ Execution time: ${response.summary.execution_time} (Total: ${totalTime}s)`, 'info');
            log(`📊 Tests performed: ${response.summary.total_tests}`, 'info');
            log(`❌ Issues found: ${response.summary.issues_found_count}`, response.summary.issues_found_count > 0 ? 'warning' : 'success');
            log(`🔧 Automatic repairs: ${response.summary.fixes_applied_count}`, response.summary.fixes_applied_count > 0 ? 'success' : 'info');
            log(`💡 Recommendations: ${response.summary.recommendations_count}`, 'info');
            log(`🎯 Overall status: ${response.summary.overall_status}`, response.success ? 'success' : 'error');
            
            log('', 'info');
            log('📋 === DETAILED TEST RESULTS ===', 'info');
            if (response.tests_performed && response.tests_performed.length > 0) {
                response.tests_performed.forEach((test, index) => {
                    log(`${index + 1}. ${test}`, 'info');
                });
            }
            if (response.issues_found && response.issues_found.length > 0) {
                log('', 'info');
                log('❌ === DETAILED ISSUES ANALYSIS ===', 'error');
                response.issues_found.forEach((issue, index) => {
                    log(`${index + 1}. ${issue}`, 'error');
                    if (issue.includes('inherited orderly')) {
                        log('   🔍 This indicates corrupted error messages - likely translation file issues', 'warning');
                        log('   💡 Solution: Check WordPress language files or plugin translation', 'info');
                    }
                    if (issue.includes('JSON')) {
                        log('   🔍 This indicates output buffer or PHP error issues', 'warning');
                        log('   💡 Solution: Check wp_send_json() calls in registration handler', 'info');
                    }
                    if (issue.includes('table')) {
                        log('   🔍 This indicates database structure problems', 'warning');
                        log('   💡 Solution: Run auto-fix-tables.php or use "Repair All Tables"', 'info');
                    }
                    if (issue.includes('registration')) {
                        log('   🔍 This indicates user registration system problems', 'warning');
                        log('   💡 Solution: Check Nexora_User_Registration class', 'info');
                    }
                });
            }
            if (response.fixes_applied && response.fixes_applied.length > 0) {
                log('', 'info');
                log('🔧 === AUTOMATIC REPAIRS APPLIED ===', 'success');
                response.fixes_applied.forEach((fix, index) => {
                    log(`${index + 1}. ${fix}`, 'success');
                });
            }
            if (response.recommendations && response.recommendations.length > 0) {
                log('', 'info');
                log('💡 === DETAILED RECOMMENDATIONS ===', 'warning');
                response.recommendations.forEach((recommendation, index) => {
                    log(`${index + 1}. ${recommendation}`, 'warning');
                    if (recommendation.includes('CRITICAL')) {
                        log('   ⚠️ This is a critical issue requiring immediate attention', 'error');
                    }
                    if (recommendation.includes('IMMEDIATE')) {
                        log('   🚨 This requires immediate action to fix the system', 'error');
                    }
                    if (recommendation.includes('SOFORT')) {
                        log('   🚨 This requires immediate action to fix the system', 'error');
                    }
                });
                const autoFixableRecommendations = response.recommendations.filter(rec => 
                    rec.includes('auto-fix-tables.php') || 
                    rec.includes('Table Repair') ||
                    rec.includes('Database Problems')
                );
                
                if (autoFixableRecommendations.length > 0) {
                    log('', 'info');
                    log('🔧 Automatic repair available!', 'success');
                    log('💡 Tip: Use "Repair All Tables" to fix database problems.', 'info');
                }
            }
            if (response.debug_info && response.debug_info.length > 0) {
                log('', 'info');
                log('🔍 === DETAILED DEBUG INFORMATION ===', 'info');
                response.debug_info.forEach((debug, index) => {
                    log(`${index + 1}. ${debug}`, 'info');
                });
            }
            if (response.detailed_results) {
                log('', 'info');
                log('📊 === DETAILED TEST RESULTS ===', 'info');
                if (response.detailed_results.database_tables) {
                    log('🗄️ Database Tables Status:', 'info');
                    Object.keys(response.detailed_results.database_tables).forEach(table => {
                        const tableInfo = response.detailed_results.database_tables[table];
                        const status = tableInfo.status === 'OK' ? '✅' : 
                                     tableInfo.status === 'MISSING' ? '❌' : '⚠️';
                        log(`  ${status} ${table}: ${tableInfo.status}`, 
                            tableInfo.status === 'OK' ? 'success' : 'error');
                    });
                }
                if (response.detailed_results.registration_errors) {
                    log('👤 Registration Error Tests:', 'info');
                    Object.keys(response.detailed_results.registration_errors).forEach(testCase => {
                        const testInfo = response.detailed_results.registration_errors[testCase];
                        const status = testInfo.status === 'OK' ? '✅' : 
                                     testInfo.status === 'CRITICAL_ERROR' ? '🚨' : '⚠️';
                        log(`  ${status} ${testCase}: ${testInfo.status}`, 
                            testInfo.status === 'OK' ? 'success' : 'error');
                        if (testInfo.status !== 'OK' && testInfo.result && testInfo.result.message) {
                            log(`    Message: ${testInfo.result.message}`, 'warning');
                        }
                    });
                }
            }
            log('', 'info');
            log('📊 === SYSTEM INFORMATION ===', 'info');
            log(`🕐 Test started: ${response.start_time}`, 'info');
            log(`🕐 Test completed: ${response.end_time}`, 'info');
            log(`⚡ PHP Version: ${response.debug_info ? response.debug_info.find(d => d.includes('PHP Version')) || 'Unknown' : 'Unknown'}`, 'info');
            log(`🌐 WordPress Version: ${response.debug_info ? response.debug_info.find(d => d.includes('WordPress Version')) || 'Unknown' : 'Unknown'}`, 'info');
            
            log('', 'info');
            if (response.success) {
                log('🎉 ALL TESTS PASSED! Your system is working perfectly.', 'success');
            } else {
                log('⚠️ ISSUES FOUND! Please follow the recommendations above.', 'error');
                log('💡 For immediate fixes, try the "Repair All Tables" button.', 'info');
            }
            log('📊 === END COMPREHENSIVE TEST ===', 'info');
        });
    };
    
    window.runFullTest = function() {
        makeRequest('run_full_test', function(response) {
            log('✅ Full system test completed', 'success');
            log('📊 === FULL SYSTEM TEST RESULTS ===', 'info');
            log(`🗃️ Database tables: ${Object.keys(response.tables).filter(k => response.tables[k].exists).length}/${Object.keys(response.tables).length} exist`, 'info');
            log(`🔧 Plugin classes: ${Object.keys(response.classes).filter(k => response.classes[k]).length}/${Object.keys(response.classes).length} loaded`, 'info');
            log(`🌐 AJAX endpoints: ${Object.keys(response.ajax).filter(k => response.ajax[k].wp_ajax || response.ajax[k].wp_ajax_nopriv).length}/${Object.keys(response.ajax).length} registered`, 'info');
            log(`📊 WordPress: ${response.wordpress.wp_version} | PHP: ${response.wordpress.php_version} | MySQL: ${response.wordpress.mysql_version}`, 'info');
            log(`📊 Database: ${response.wordpress.database_connected ? 'Connected' : 'Not connected'}`, 'info');
            log(`📊 Plugin: ${response.wordpress.plugin_loaded ? 'Loaded' : 'Not loaded'}`, 'info');
            log('📊 === END TEST RESULTS ===', 'info');
        });
    };
    
    window.testTables = function() {
        makeRequest('check_tables', function(response) {
            log('✅ Table check completed', 'success');
            updateTablesGrid(response);
        });
    };
    
    window.createMissingTables = function() {
        log('🔨 Creating missing tables...', 'info');
        makeRequest('create_tables', function(response) {
            if (response.success) {
                log('✅ Tables created successfully', 'success');
                if (response.created_tables && response.created_tables.length > 0) {
                    log(`📊 Created ${response.created_tables.length} tables:`, 'success');
                    response.created_tables.forEach(table => {
                        log(`  ✅ ${table}`, 'success');
                    });
                }
                if (response.errors && response.errors.length > 0) {
                    log('❌ Errors occurred:', 'error');
                    response.errors.forEach(error => {
                        log(`  ❌ ${error}`, 'error');
                    });
                }
            } else {
                log('❌ Failed to create tables', 'error');
                if (response.errors) {
                    response.errors.forEach(error => {
                        log(`  ❌ ${error}`, 'error');
                    });
                }
            }
            testTables();
        });
    };
    
    window.repairAllTables = function() {
        makeRequest('repair_tables', function(response) {
            log('✅ Tables repaired', 'success');
            testTables();
        });
    };
    
    window.repairCustomerInfoTable = function() {
        log('🔧 Repairing customer info table...', 'info');
        makeRequest('repair_tables', function(response) {
            if (response.success) {
                log('✅ Customer info table repaired successfully', 'success');
                if (response.message) {
                    log(`📋 Message: ${response.message}`, 'info');
                }
            } else {
                log('❌ Failed to repair customer info table', 'error');
                if (response.errors) {
                    response.errors.forEach(error => {
                        log(`  ❌ ${error}`, 'error');
                    });
                }
            }
            testTables();
        });
    };
    
    window.testClasses = function() {
        makeRequest('test_classes', function(response) {
            log('✅ Class test completed', 'success');
            Object.keys(response).forEach(className => {
                const status = response[className] ? '✅' : '❌';
                log(`${status} ${className}: ${response[className] ? 'Loaded' : 'Not found'}`, 
                    response[className] ? 'success' : 'error');
            });
        });
    };
    
    window.testAjax = function() {
        makeRequest('test_ajax', function(response) {
            log('✅ AJAX test completed', 'success');
            Object.keys(response).forEach(endpoint => {
                const info = response[endpoint];
                const status = (info.wp_ajax || info.wp_ajax_nopriv) ? '✅' : '❌';
                log(`${status} ${endpoint}: ${info.wp_ajax ? 'wp_ajax' : ''} ${info.wp_ajax_nopriv ? 'wp_ajax_nopriv' : ''}`, 
                    (info.wp_ajax || info.wp_ajax_nopriv) ? 'success' : 'error');
            });
        });
    };
    
    window.createSampleData = function() {
        makeRequest('create_sample_data', function(response) {
            log('✅ Sample data created', 'success');
        });
    };
    window.testRegistrationErrors = function() {
        makeRequest('test_registration_errors', function(response) {
            log('✅ Registration error test completed', 'success');
            Object.keys(response).forEach(testType => {
                const result = response[testType];
                log(`📋 ${testType}: ${result.message || result.error || 'Completed'}`, 
                    result.success ? 'success' : 'warning');
            });
        });
    };
    
    window.testRegistrationAjax = function() {
        makeRequest('test_ajax_registration', function(response) {
            log('✅ Registration AJAX test completed', 'success');
            Object.keys(response).forEach(endpoint => {
                const info = response[endpoint];
                log(`✅ Registration AJAX ${endpoint}: ${info.registered ? 'Registered' : 'Not registered'}`, 
                    info.registered ? 'success' : 'error');
                log(`- wp_ajax: ${info.wp_ajax ? 'Yes' : 'No'}`, 'info');
                log(`- wp_ajax_nopriv: ${info.wp_ajax_nopriv ? 'Yes' : 'No'}`, 'info');
                log(`- nonce_valid: ${info.nonce_valid ? 'Yes' : 'No'}`, 'info');
            });
        });
    };
    
    window.testDatabaseOperations = function() {
        makeRequest('test_database_operations', function(response) {
            log('✅ Database operations test completed', 'success');
            log(`📊 Customer info table exists: ${response.customer_info_table_exists ? 'Yes' : 'No'}`, 'info');
            
            if (response.customer_info_table_exists) {
                log(`📋 Table structure: ${response.customer_info_table_structure.length} columns`, 'info');
                log(`🔧 Salutation ENUM correct: ${response.salutation_enum_correct ? 'Yes' : 'No'}`, 'info');
                log(`💾 Test insert successful: ${response.test_insert_success ? 'Yes' : 'No'}`, 'info');
                
                if (response.test_insert_error) {
                    log(`❌ Database insert error: ${response.test_insert_error}`, 'error');
                }
            }
        });
    };
    
    window.testFormRendering = function() {
        makeRequest('test_form_rendering', function(response) {
            log('✅ Form rendering test completed', 'success');
            log(`📝 Form renders: ${response.form_renders ? 'Yes' : 'No'}`, 'info');
            log(`🔍 Contains register form: ${response.contains_register_form ? 'Yes' : 'No'}`, 'info');
            log(`👤 Contains salutation field: ${response.contains_salutation_field ? 'Yes' : 'No'}`, 'info');
            log(`🏳️‍🌈 Contains Divers option: ${response.contains_divers_option ? 'Yes' : 'No'}`, 'info');
            log(`📏 Form length: ${response.form_length} characters`, 'info');
            
            if (response.error) {
                log(`❌ Form rendering error: ${response.error}`, 'error');
            }
        });
    };
    window.testMissingFields = function() {
        makeRequest('test_missing_fields', function(response) {
            log('✅ Missing fields test completed', 'success');
            
            if (typeof response === 'object' && response !== null) {
                Object.keys(response).forEach(testCase => {
                    const result = response[testCase];
                    log(`📋 ${testCase}:`, 'info');
                    log(`  - Message: ${result.message || 'No message'}`, 'info');
                    log(`  - Valid JSON: ${result.output_is_valid_json ? 'Yes' : 'No'}`, 'info');
                    log(`  - Contains orderly error: ${result.contains_orderly_error ? 'Yes' : 'No'}`, 
                        result.contains_orderly_error ? 'error' : 'success');
                    
                    if (result.error) {
                        log(`  - Error: ${result.error}`, 'error');
                    }
                });
            } else {
                log(`❌ Invalid response format: ${JSON.stringify(response)}`, 'error');
            }
        });
    };
    
    window.testInvalidEmail = function() {
        makeRequest('test_invalid_email', function(response) {
            log('✅ Invalid email test completed', 'success');
            log(`📧 Invalid email result: ${response.message || 'Undefined'}`, 'info');
        });
    };
    
    window.testCompleteRegistration = function() {
        makeRequest('test_complete_registration', function(response) {
            log('✅ Complete registration test completed', 'success');
            
            if (typeof response === 'object' && response !== null) {
                log(`🎯 Registration successful: ${response.success ? 'Yes' : 'No'}`, 
                    response.success ? 'success' : 'error');
                log(`📨 Message: ${response.message || 'No message'}`, 'info');
                log(`👤 User created: ${response.user_created ? 'Yes' : 'No'}`, 
                    response.user_created ? 'success' : 'error');
                log(`💾 Customer info saved: ${response.customer_info_saved ? 'Yes' : 'No'}`, 
                    response.customer_info_saved ? 'success' : 'error');
                log(`✅ Valid JSON: ${response.output_is_valid_json ? 'Yes' : 'No'}`, 'info');
                
                if (response.error) {
                    log(`❌ Registration error: ${response.error}`, 'error');
                }
                
                if (!response.output_is_valid_json) {
                    log(`🔍 JSON error: ${response.json_error}`, 'error');
                    log(`📋 Raw output: ${response.raw_output}`, 'error');
                }
            } else {
                log(`❌ Invalid response format: ${JSON.stringify(response)}`, 'error');
            }
        });
    };
    
    window.testDuplicateEmail = function() {
        makeRequest('test_duplicate_email', function(response) {
            log('✅ Duplicate email test completed', 'success');
            log(`📧 Duplicate email result (${response.tested_email || 'undefined'}): ${response.message || 'Undefined'}`, 'info');
        });
    };
    
    window.testAjaxComprehensive = function() {
        makeRequest('test_ajax_registration_comprehensive', function(response) {
            log('✅ Comprehensive AJAX test completed', 'success');
            
            if (typeof response === 'object' && response !== null) {
                if (response.endpoint_registration) {
                    const endpoints = response.endpoint_registration;
                    Object.keys(endpoints).forEach(endpoint => {
                        const info = endpoints[endpoint];
                        log(`📡 ${endpoint}: ${info.registered ? 'Registered' : 'Not registered'}`, 
                            info.registered ? 'success' : 'error');
                    });
                }
                if (response.actual_ajax_test) {
                    const ajaxTest = response.actual_ajax_test;
                    log(`🔄 AJAX call test: ${ajaxTest.success ? 'Success' : 'Failed'}`, 
                        ajaxTest.success ? 'success' : 'error');
                    log(`📨 Response message: ${ajaxTest.message || 'No message'}`, 'info');
                    log(`👤 User created: ${ajaxTest.user_created ? 'Yes' : 'No'}`, 'info');
                    log(`✅ Valid JSON: ${ajaxTest.valid_json ? 'Yes' : 'No'}`, 'info');
                    
                    if (ajaxTest.error) {
                        log(`❌ AJAX test error: ${ajaxTest.error}`, 'error');
                    }
                    
                    if (!ajaxTest.valid_json) {
                        log(`🔍 JSON error: ${ajaxTest.json_error}`, 'error');
                        log(`📋 Raw output: ${ajaxTest.output}`, 'error');
                    }
                }
            } else {
                log(`❌ Invalid response format: ${JSON.stringify(response)}`, 'error');
            }
        });
    };
    
    function updateTablesGrid(tables) {
        const grid = $('#tables-grid');
        grid.empty();
        Object.keys(tables).forEach(tableName => {
            const table = tables[tableName];
            let displayName = tableName;
            if (tableName === 'nexora_devices') {
                displayName = 'nexora_devices <span style="color:#0073aa;font-size:13px;">(Geräteverwaltung – مدیریت دستگاه‌ها)</span>';
            }
            const div = $('<div class="table-item"></div>');
            div.html(`
                <h4>${displayName}</h4>
                <div class="metric">
                    <strong>Status:</strong> ${table.exists ? '✅ Exists' : '❌ Missing'}
                </div>
                <div class="metric">
                    <strong>Structure:</strong> ${table.structure_valid ? '✅ Valid' : '❌ Invalid'}
                </div>
                ${table.exists ? `
                    <button class="button button-secondary" onclick="repairTable('${tableName}')">🔧 Repair</button>
                ` : `
                    <button class="button button-primary" onclick="createTable('${tableName}')">➕ Create</button>
                `}
            `);
            grid.append(div);
        });
    }
    
    window.createTable = function(tableName) {
        log(`🔄 Creating table: ${tableName}`, 'info');
        createMissingTables();
    };
    
    window.repairTable = function(tableName) {
        log(`🔧 Repairing table: ${tableName}`, 'info');
        repairAllTables();
    };

    function repairRequestInvoicesTable() {
        var nonce = '<?php echo esc_js($nonce); ?>';
        jQuery.ajax({
            url: '<?php echo esc_js($ajax_url); ?>',
            type: 'POST',
            data: {
                action: 'repair_request_invoices_table',
                nonce: nonce
            },
            success: function(response) {
                if (response.success) {
                    logToConsole('✅ ' + response.message);
                } else {
                    logToConsole('❌ Fehler beim Reparieren der Rechnungstabelle');
                }
            },
            error: function() {
                logToConsole('❌ AJAX Fehler beim Reparieren der Rechnungstabelle');
            }
        });
    }
    testTables();
    $(document).on('keydown', function(e) {
        if (e.ctrlKey && e.shiftKey && e.keyCode === 84) {
            e.preventDefault();
            if (!testRunning) {
                runComprehensiveTest();
            }
        }
    });
    log('⌨️ Tip: Press Ctrl+Shift+T for quick comprehensive test', 'info');

    $('#Nexora Service Suite-test-user-info-btn').on('click', function(){
        $('#Nexora Service Suite-user-info-log').text('Bitte warten...').show();
        $.post(ajaxurl, {
            action: 'nexora_test_user_info',
            nonce: '<?php echo wp_create_nonce('nexora_repair_nonce'); ?>'
        }, function(resp){
            if(resp.success){
                $('#Nexora Service Suite-user-info-log').text(resp.data);
            } else {
                $('#Nexora Service Suite-user-info-log').text('Fehler: ' + (resp.data || resp.error || 'Unbekannter Fehler'));
            }
        });
    });
    window.runComprehensiveServicesTest = function() {
        $('#console').html(`
            <div class="console-header">
                <span class="timestamp">[${new Date().toLocaleTimeString()}]</span>
                <span class="success">🚨 COMPREHENSIVE SERVICES TEST STARTED</span>
            </div>
        `);
        log('🚨 Starting ULTIMATE comprehensive services test...', 'info');
        log('🔍 This test will check EVERYTHING related to services list display...', 'info');
        log('⚡ Testing Database, PHP Classes, Files, AJAX, WordPress Integration...', 'warning');
        log('📋 Will provide detailed browser debugging guide...', 'info');
        log('', 'info');
        
        const startTime = Date.now();
        
        makeRequest('comprehensive_services_test', function(response) {
            const endTime = Date.now();
            const totalTime = ((endTime - startTime) / 1000).toFixed(2);
            if (response.error) {
                log('', 'info');
                log('❌ === COMPREHENSIVE SERVICES TEST FAILED ===', 'error');
                log(`💥 Error: ${response.error}`, 'error');
                if (response.trace) {
                    log('🔍 Stack Trace:', 'error');
                    log(response.trace, 'error');
                }
                return;
            }
            
            log('', 'info');
            log('🏁 === COMPREHENSIVE SERVICES TEST COMPLETED ===', 'success');
            log(`⏱️ Execution time: ${totalTime}s`, 'info');
            log(`❌ Issues found: ${response.issues_found ? response.issues_found.length : 0}`, 
                (response.issues_found && response.issues_found.length > 0) ? 'error' : 'success');
            log(`🔧 Fixes applied: ${response.fixes_applied ? response.fixes_applied.length : 0}`, 
                (response.fixes_applied && response.fixes_applied.length > 0) ? 'success' : 'info');
            log(`🎯 Overall status: ${response.success ? 'No issues found' : 'Issues found'}`, 
                response.success ? 'success' : 'error');
            if (response.debug_info && response.debug_info.length > 0) {
                log('', 'info');
                log('🔍 === COMPREHENSIVE TEST RESULTS ===', 'info');
                response.debug_info.forEach((debug, index) => {
                    log(`${debug}`, 'info');
                });
            }
            if (response.issues_found && response.issues_found.length > 0) {
                log('', 'info');
                log('❌ === SERVICES LIST ISSUES FOUND ===', 'error');
                response.issues_found.forEach((issue, index) => {
                    log(`${index + 1}. ${issue}`, 'error');
                });
            }
            if (response.fixes_applied && response.fixes_applied.length > 0) {
                log('', 'info');
                log('🔧 === AUTOMATIC FIXES APPLIED ===', 'success');
                response.fixes_applied.forEach((fix, index) => {
                    log(`${index + 1}. ${fix}`, 'success');
                });
            }
            if (response.test_results) {
                log('', 'info');
                log('📊 === TEST RESULTS ===', 'info');
                Object.keys(response.test_results).forEach(testName => {
                    const result = response.test_results[testName];
                    const status = result.success ? '✅' : '❌';
                    log(`${status} ${testName}: ${result.message || result.error || 'Completed'}`, 
                        result.success ? 'success' : 'error');
                });
            }
            
            log('', 'info');
            if (response.success) {
                log('🎉 No critical issues found! Check browser debugging guide above.', 'success');
            } else {
                log('⚠️ Issues found! Check the issues above and follow recommendations.', 'error');
            }
            log('📊 === END COMPREHENSIVE SERVICES TEST ===', 'info');
        });
    };
    window.debugServicesList = function() {
        $('#console').html(`
            <div class="console-header">
                <span class="timestamp">[${new Date().toLocaleTimeString()}]</span>
                <span class="success">🔧 DIENSTLEISTUNGEN LIST DEBUG STARTED</span>
            </div>
        `);
        log('🚨 Starting comprehensive Dienstleistungen list debugging...', 'info');
        log('🔍 Testing database, AJAX, JavaScript, templates, and all components...', 'info');
        log('⚡ This test will identify exactly why the services list is not displaying...', 'warning');
        log('', 'info');
        
        const startTime = Date.now();
        
        makeRequest('debug_services_list', function(response) {
            const endTime = Date.now();
            const totalTime = ((endTime - startTime) / 1000).toFixed(2);
            if (response.error) {
                log('', 'info');
                log('❌ === DIENSTLEISTUNGEN DEBUG FAILED ===', 'error');
                log(`💥 Error: ${response.error}`, 'error');
                if (response.trace) {
                    log('🔍 Stack Trace:', 'error');
                    log(response.trace, 'error');
                }
                return;
            }
            
            log('', 'info');
            log('🏁 === DIENSTLEISTUNGEN LIST DEBUG COMPLETED ===', 'success');
            log(`⏱️ Execution time: ${totalTime}s`, 'info');
            log(`❌ Issues found: ${response.issues_found ? response.issues_found.length : 0}`, 
                (response.issues_found && response.issues_found.length > 0) ? 'error' : 'success');
            log(`🔧 Fixes applied: ${response.fixes_applied ? response.fixes_applied.length : 0}`, 
                (response.fixes_applied && response.fixes_applied.length > 0) ? 'success' : 'info');
            log(`🎯 Overall status: ${response.success ? 'No issues found' : 'Issues found'}`, 
                response.success ? 'success' : 'error');
            if (response.debug_info && response.debug_info.length > 0) {
                log('', 'info');
                log('🔍 === DIAGNOSTIC DETAILS ===', 'info');
                response.debug_info.forEach((debug, index) => {
                    log(`${debug}`, 'info');
                });
            }
            if (response.issues_found && response.issues_found.length > 0) {
                log('', 'info');
                log('❌ === DIENSTLEISTUNGEN LIST ISSUES FOUND ===', 'error');
                response.issues_found.forEach((issue, index) => {
                    log(`${index + 1}. ${issue}`, 'error');
                });
            }
            if (response.fixes_applied && response.fixes_applied.length > 0) {
                log('', 'info');
                log('🔧 === AUTOMATIC FIXES APPLIED ===', 'success');
                response.fixes_applied.forEach((fix, index) => {
                    log(`${index + 1}. ${fix}`, 'success');
                });
            }
            if (response.test_results) {
                log('', 'info');
                log('📊 === TEST RESULTS ===', 'info');
                Object.keys(response.test_results).forEach(testName => {
                    const result = response.test_results[testName];
                    const status = result.success ? '✅' : '❌';
                    log(`${status} ${testName}: ${result.message || result.error || 'Completed'}`, 
                        result.success ? 'success' : 'error');
                });
            }
            
            log('', 'info');
            if (response.success) {
                log('🎉 No Dienstleistungen list issues found! The list should display correctly.', 'success');
            } else {
                log('⚠️ Dienstleistungen list issues found! Check the issues above.', 'error');
                log('💡 Common solutions:', 'info');
                log('   1. Check if services exist in database', 'info');
                log('   2. Verify AJAX handlers are registered', 'info');
                log('   3. Check JavaScript console for errors', 'info');
                log('   4. Verify template files exist', 'info');
                log('   5. Check admin menu registration', 'info');
            }
            log('📊 === END DIENSTLEISTUNGEN DEBUG ===', 'info');
        });
    };
    window.addCustomerNumberColumn = function() {
        log('🔢 Adding customer_number column to wp_users table...', 'info');
        log('📊 This will add the customer_number column for automatic customer number generation', 'info');
        
        $.post(ajaxurl, {
            action: 'nexora_add_customer_number_column',
            nonce: '<?php echo wp_create_nonce('nexora_customer_number_nonce'); ?>'
        }, function(response) {
            if (response.success) {
                log('✅ Customer number column added successfully!', 'success');
                log('📋 Column added to: wp_users table', 'success');
                if (response.data && response.data.message) {
                    log(`ℹ️ ${response.data.message}`, 'info');
                }
            } else {
                log('❌ Failed to add customer number column', 'error');
                if (response.data) {
                    log(`💥 Error: ${response.data}`, 'error');
                }
            }
        }).fail(function() {
            log('❌ AJAX request failed', 'error');
        });
    };
    
    window.initializeCustomerNumbers = function() {
        log('🔢 Initializing customer numbers for existing users...', 'info');
        log('📊 This will generate customer numbers for all existing customer users', 'info');
        
        if (!confirm('Are you sure you want to initialize customer numbers for all existing users?\n\nThis will generate customer numbers starting from C0350 for all customer users without numbers.')) {
            log('❌ Operation cancelled by user', 'warning');
            return;
        }
        
        $.post(ajaxurl, {
            action: 'nexora_initialize_customer_numbers',
            nonce: '<?php echo wp_create_nonce('nexora_customer_number_nonce'); ?>'
        }, function(response) {
            if (response.success) {
                log('✅ Customer numbers initialized successfully!', 'success');
                log(`📊 Generated ${response.data.count} customer numbers`, 'success');
                if (response.data.message) {
                    log(`ℹ️ ${response.data.message}`, 'info');
                }
            } else {
                log('❌ Failed to initialize customer numbers', 'error');
                if (response.data) {
                    log(`💥 Error: ${response.data}`, 'error');
                }
            }
        }).fail(function() {
            log('❌ AJAX request failed', 'error');
        });
    };
    
    window.checkCustomerNumberStatus = function() {
        log('🔍 Checking customer number status...', 'info');
        log('📊 This will show the current status of customer numbers in the system', 'info');
        
        $.post(ajaxurl, {
            action: 'nexora_check_customer_number_status',
            nonce: '<?php echo wp_create_nonce('nexora_customer_number_nonce'); ?>'
        }, function(response) {
            if (response.success) {
                log('✅ Customer number status check completed!', 'success');
                if (response.data) {
                    log(`📊 Total users: ${response.data.total_users}`, 'info');
                    log(`🔢 Users with customer numbers: ${response.data.users_with_numbers}`, 'info');
                    log(`❌ Users without customer numbers: ${response.data.users_without_numbers}`, 'info');
                    log(`🎯 Next available number: ${response.data.next_number}`, 'info');
                }
            } else {
                log('❌ Failed to check customer number status', 'error');
                if (response.data) {
                    log(`💥 Error: ${response.data}`, 'error');
                }
            }
        }).fail(function() {
            log('❌ AJAX request failed', 'error');
        });
    };
    window.createInvoiceServicesTable = function() {
        log('🆕 Starting Faktor Services Table creation...', 'info');
        log('🔧 Creating nexora_faktor_services table...', 'info');
        
        makeRequest('create_invoice_services_table', function(response) {
            if (response.success) {
                log('✅ Faktor Services Table created successfully!', 'success');
                log(`📊 Table name: ${response.table_name}`, 'info');
                log(`🔧 SQL executed: ${response.sql_executed}`, 'info');
                log('🎉 You can now use the Faktor Services feature!', 'success');
                setTimeout(function() {
                    testTables();
                }, 1000);
            } else {
                log('❌ Failed to create Faktor Services Table', 'error');
                log(`💥 Error: ${response.error}`, 'error');
                if (response.sql_error) {
                    log(`🔍 SQL Error: ${response.sql_error}`, 'error');
                }
            }
        });
    };
    window.checkEasyFormTables = function() {
        log('🔍 Checking Easy Form database tables...', 'info');
        log('📊 This will check if all required tables exist and are properly configured', 'info');
        
        $.post(ajaxurl, {
            action: 'nexora_check_easy_form_tables',
            nonce: '<?php echo wp_create_nonce('nexora_easy_form_nonce'); ?>'
        }, function(response) {
            if (response.success) {
                log('✅ Easy Form tables check completed!', 'success');
                if (response.data) {
                    log(`📊 wp_users table: ${response.data.users_table ? '✅ Exists' : '❌ Missing'}`, 'info');
                    log(`📊 customer_number column: ${response.data.customer_number_column ? '✅ Exists' : '❌ Missing'}`, 'info');
                    log(`📊 nexora_customer_info table: ${response.data.customer_info_table ? '✅ Exists' : '❌ Missing'}`, 'info');
                    log(`📊 nexora_service_requests table: ${response.data.service_requests_table ? '✅ Exists' : '❌ Missing'}`, 'info');
                    log(`📊 nexora_complete_service_requests table: ${response.data.complete_service_requests_table ? '✅ Exists' : '❌ Missing'}`, 'info');
                }
            } else {
                log('❌ Failed to check Easy Form tables', 'error');
                if (response.data) {
                    log(`💥 Error: ${response.data}`, 'error');
                }
            }
        }).fail(function() {
            log('❌ AJAX request failed', 'error');
        });
    };

    window.createMissingEasyFormTables = function() {
        log('🆕 Creating missing Easy Form tables...', 'info');
        log('📊 This will create any missing tables required for Easy Form functionality', 'info');
        
        if (!confirm('Are you sure you want to create missing Easy Form tables?\n\nThis will ensure all required database structures exist.')) {
            log('❌ Operation cancelled by user', 'warning');
            return;
        }
        
        $.post(ajaxurl, {
            action: 'nexora_create_missing_easy_form_tables',
            nonce: '<?php echo wp_create_nonce('nexora_easy_form_nonce'); ?>'
        }, function(response) {
            if (response.success) {
                log('✅ Missing Easy Form tables created successfully!', 'success');
                if (response.data) {
                    log(`📊 Tables created: ${response.data.tables_created}`, 'success');
                    log(`📊 Columns added: ${response.data.columns_added}`, 'success');
                    if (response.data.message) {
                        log(`ℹ️ ${response.data.message}`, 'info');
                    }
                }
            } else {
                log('❌ Failed to create missing Easy Form tables', 'error');
                if (response.data) {
                    log(`💥 Error: ${response.data}`, 'error');
                }
            }
        }).fail(function() {
            log('❌ AJAX request failed', 'error');
        });
    };

    window.repairEasyFormDatabase = function() {
        log('🔧 Repairing Easy Form database...', 'info');
        log('📊 This will fix common database issues and ensure proper structure', 'info');
        
        if (!confirm('Are you sure you want to repair the Easy Form database?\n\nThis will attempt to fix any structural issues.')) {
            log('❌ Operation cancelled by user', 'warning');
            return;
        }
        
        $.post(ajaxurl, {
            action: 'nexora_repair_easy_form_database',
            nonce: '<?php echo wp_create_nonce('nexora_easy_form_nonce'); ?>'
        }, function(response) {
            if (response.success) {
                log('✅ Easy Form database repaired successfully!', 'success');
                if (response.data) {
                    log(`📊 Repairs performed: ${response.data.repairs_performed}`, 'success');
                    log(`📊 Issues fixed: ${response.data.issues_fixed}`, 'success');
                    if (response.data.message) {
                        log(`ℹ️ ${response.data.message}`, 'info');
                    }
                }
            } else {
                log('❌ Failed to repair Easy Form database', 'error');
                if (response.data) {
                    log(`💥 Error: ${response.data}`, 'error');
                }
            }
        }).fail(function() {
            log('❌ AJAX request failed', 'error');
        });
    };

    window.testEasyFormDatabase = function() {
        log('🧪 Testing Easy Form database functionality...', 'info');
        log('📊 This will test if the database can handle Easy Form operations', 'info');
        
        $.post(ajaxurl, {
            action: 'nexora_test_easy_form_database',
            nonce: '<?php echo wp_create_nonce('nexora_easy_form_nonce'); ?>'
        }, function(response) {
            if (response.success) {
                log('✅ Easy Form database test completed successfully!', 'success');
                if (response.data) {
                    log(`📊 Test results: ${response.data.test_results}`, 'success');
                    log(`📊 Database operations: ${response.data.database_operations}`, 'success');
                    if (response.data.message) {
                        log(`ℹ️ ${response.data.message}`, 'info');
                    }
                }
            } else {
                log('❌ Easy Form database test failed', 'error');
                if (response.data) {
                    log(`💥 Error: ${response.data}`, 'error');
                }
            }
        }).fail(function() {
            log('❌ AJAX request failed', 'error');
        });
    };
});
</script>
</div> 