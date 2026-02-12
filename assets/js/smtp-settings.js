

jQuery(document).ready(function($) {
    initTabSwitching();
    initSMTPForm();
    initAdminEmailsForm();
    
    
    function initTabSwitching() {
        $('.tab-button').on('click', function() {
            const targetTab = $(this).data('tab');
            $('.tab-button').removeClass('active');
            $('.tab-content').removeClass('active');
            $(this).addClass('active');
            $('#' + targetTab).addClass('active');
        });
    }
    
    
    function initSMTPForm() {
        const form = $('#smtp-settings-form');
        if (!form.length) return;
        $('#save-smtp-settings').on('click', function(e) {
            e.preventDefault();
            saveSMTPSettings();
        });
        form.on('submit', function(e) {
            e.preventDefault();
            saveSMTPSettings();
        });
        $('#smtp-enabled').on('change', function() {
            toggleSMTPFields($(this).is(':checked'));
        });
        toggleSMTPFields($('#smtp-enabled').is(':checked'));
        $(document).on('click', '#test-smtp-connection', function() {
            testSMTPConnection();
        });
        $('#reset-smtp-settings').on('click', function() {
            if (confirm('آیا مطمئن هستید که می‌خواهید تنظیمات SMTP را به حالت پیش‌فرض بازگردانید؟')) {
                resetSMTPSettings();
            }
        });
        $('#send-admin-test-email').on('click', function() {
            sendAdminTestEmail();
        });
    }
    
    
    function initAdminEmailsForm() {
        const form = $('#admin-emails-form');
        if (!form.length) return;
        form.on('submit', function(e) {
            e.preventDefault();
            saveAdminEmails();
        });
        $('#add-admin-email').on('click', function() {
            addAdminEmail();
        });
        $(document).on('click', '.remove-admin-email', function() {
            $(this).closest('.admin-email-item').remove();
        });
    }
    
    
    function toggleSMTPFields(enabled) {
        const fields = $('.smtp-field');
        const requiredFields = $('.smtp-required');
        
        if (enabled) {
            fields.show();
            requiredFields.addClass('required');
        } else {
            fields.hide();
            requiredFields.removeClass('required');
        }
    }
    
    
    function saveSMTPSettings() {
        const form = $('#smtp-settings-form');
        const submitBtn = form.find('button[type="submit"]');
        const originalText = submitBtn.text();
        submitBtn.prop('disabled', true).text('در حال ذخیره...');
        const formData = new FormData(form[0]);
        const adminUsers = $('#admin-users-select').val();
        if (Array.isArray(adminUsers)) {
            formData.delete('admin_users[]');
            adminUsers.forEach(function(id) {
                formData.append('admin_users[]', id);
            });
        }
        formData.append('action', 'save_smtp_settings');
        const formNonce = $('#nexora_smtp_nonce').val();
        formData.append('nonce', formNonce);
        $.ajax({
            url: ajaxurl,
            type: 'POST',
            data: formData,
            processData: false,
            contentType: false,
            success: function(response) {
                console.log('save_smtp_settings response:', response);
                const msg = response && response.data ? response.data : (response.success ? 'ذخیره شد' : 'خطا در ذخیره');
                showNotification(response.success ? 'success' : 'error', msg);
            },
            error: function() {
                showNotification('error', 'خطا در ارتباط با سرور');
            },
            complete: function() {
                submitBtn.prop('disabled', false).text(originalText);
            }
        });
    }
    
    
    function testSMTPConnection() {
        const testBtn = $('#test-smtp-connection');
        const originalText = testBtn.text();
        testBtn.prop('disabled', true).text('در حال تست...');
        const form = $('#smtp-settings-form');
        const formData = new FormData(form[0]);
        const adminUsers2 = $('#admin-users-select').val();
        if (Array.isArray(adminUsers2)) {
            formData.delete('admin_users[]');
            adminUsers2.forEach(function(id) {
                formData.append('admin_users[]', id);
            });
        }
        formData.append('action', 'test_smtp_connection');
        const formNonce = $('#nexora_smtp_nonce').val();
        formData.append('nonce', formNonce);
        $.ajax({
            url: ajaxurl,
            type: 'POST',
            data: formData,
            processData: false,
            contentType: false,
            success: function(response) {
                if (response.success) {
                    showNotification('success', response.data);
                } else {
                    showNotification('error', response.data);
                }
            },
            error: function() {
                showNotification('error', 'خطا در ارتباط با سرور');
            },
            complete: function() {
                testBtn.prop('disabled', false).text(originalText);
            }
        });
    }

    
    function sendAdminTestEmail() {
        const form = $('#smtp-settings-form');
        const button = $('#send-admin-test-email');
        const originalText = button.text();
        button.prop('disabled', true).text('در حال ارسال تست...');
        const formData = new FormData(form[0]);
        formData.append('action', 'send_admin_test_email');
        const formNonce = $('#nexora_smtp_nonce').val();
        formData.append('nonce', formNonce);

        $.ajax({
            url: ajaxurl,
            type: 'POST',
            data: formData,
            processData: false,
            contentType: false,
            success: function(response) {
                if (response.success) {
                    showNotification('success', response.data || 'ایمیل تست برای ادمین‌ها ارسال شد');
                } else {
                    showNotification('error', response.data || 'ارسال ایمیل تست ناموفق بود');
                }
            },
            error: function() {
                showNotification('error', 'خطا در ارتباط با سرور');
            },
            complete: function() {
                button.prop('disabled', false).text(originalText);
            }
        });
    }
    
    
    function resetSMTPSettings() {
        const resetBtn = $('#reset-smtp-settings');
        const originalText = resetBtn.text();
        resetBtn.prop('disabled', true).text('در حال بازگردانی...');
        $.ajax({
            url: ajaxurl,
            type: 'POST',
            data: {
                action: 'reset_smtp_settings',
                nonce: $('#nexora_smtp_nonce').val()
            },
            success: function(response) {
                if (response.success) {
                    showNotification('success', response.data);
                    setTimeout(function() {
                        location.reload();
                    }, 1500);
                } else {
                    showNotification('error', response.data);
                }
            },
            error: function() {
                showNotification('error', 'خطا در ارتباط با سرور');
            },
            complete: function() {
                resetBtn.prop('disabled', false).text(originalText);
            }
        });
    }
    
    
    function saveAdminEmails() {
        const form = $('#admin-emails-form');
        const submitBtn = form.find('button[type="submit"]');
        const originalText = submitBtn.text();
        submitBtn.prop('disabled', true).text('در حال ذخیره...');
        const emails = $('#admin-emails-list').val();
        $.ajax({
            url: ajaxurl,
            type: 'POST',
            data: {
                action: 'save_admin_emails',
                admin_emails: emails,
                nonce: nexora_ajax.nonce
            },
            success: function(response) {
                if (response.success) {
                    showNotification('success', response.data);
                } else {
                    showNotification('error', response.data);
                }
            },
            error: function() {
                showNotification('error', 'خطا در ارتباط با سرور');
            },
            complete: function() {
                submitBtn.prop('disabled', false).text(originalText);
            }
        });
    }
    
    
    function addAdminEmail() {
        const container = $('#admin-emails-container');
        const emailItem = $('<div class="admin-email-item">' +
            '<input type="email" class="admin-email-input" placeholder="ایمیل ادمین">' +
            '<button type="button" class="remove-admin-email button button-small">حذف</button>' +
            '</div>');
        
        container.append(emailItem);
    }
    
    
    function showNotification(type, message) {
        const notification = $('<div class="notice notice-' + type + ' is-dismissible">' +
            '<p>' + message + '</p>' +
            '<button type="button" class="notice-dismiss">' +
            '<span class="screen-reader-text">بستن این اعلان.</span>' +
            '</button>' +
            '</div>');
        $('.wrap h1').after(notification);
        setTimeout(function() {
            notification.fadeOut();
        }, 5000);
        notification.find('.notice-dismiss').on('click', function() {
            notification.fadeOut();
        });
    }
    
    
    function isValidEmail(email) {
        const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
        return emailRegex.test(email);
    }
    
    
    function formatAdminEmails(emails) {
        if (typeof emails === 'string') {
            return emails.split('\n').filter(email => email.trim() !== '').join('\n');
        }
        return '';
    }
    $('#admin-emails-list').on('blur', function() {
        const emails = $(this).val();
        const formatted = formatAdminEmails(emails);
        if (formatted !== emails) {
            $(this).val(formatted);
        }
    });
    
});
