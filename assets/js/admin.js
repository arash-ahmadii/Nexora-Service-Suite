

jQuery(document).ready(function($) {
    $('#save-notifications').on('click', function() {
        const button = $(this);
        const originalText = button.text();
        button.prop('disabled', true).text('در حال ذخیره...');
        const notifications = {};
        $('input[name^="notifications"]:checked').each(function() {
            const name = $(this).attr('name');
            const value = $(this).val();
            const matches = name.match(/notifications\[([^\]]+)\]\[([^\]]+)\]/);
            if (matches) {
                const category = matches[1];
                const event = matches[2];
                
                if (!notifications[category]) {
                    notifications[category] = {};
                }
                notifications[category][event] = value;
            }
        });
        $.ajax({
            url: ajaxurl,
            type: 'POST',
            data: {
                action: 'save_notification_settings',
                notifications: notifications,
                nonce: nexora_ajax.nonce
            },
            success: function(response) {
                if (response.success) {
                    showNotification('تنظیمات با موفقیت ذخیره شد!', 'success');
                } else {
                    showNotification('خطا در ذخیره تنظیمات: ' + (response.data || 'خطای نامشخص'), 'error');
                }
            },
            error: function(xhr, status, error) {
                console.error('AJAX Error:', error);
                showNotification('خطای شبکه در ذخیره تنظیمات.', 'error');
            },
            complete: function() {
                button.prop('disabled', false).text(originalText);
            }
        });
    });
    $('.tab-button').on('click', function() {
        const targetTab = $(this).data('tab');
        $('.tab-button').removeClass('active');
        $(this).addClass('active');
        $('.tab-content').removeClass('active');
        $('#' + targetTab).addClass('active');
    });
    $('.notification-option').on('click', function(e) {
        if (!$(e.target).is('input[type="checkbox"]')) {
            const checkbox = $(this).find('input[type="checkbox"]');
            checkbox.prop('checked', !checkbox.prop('checked'));
        }
    });
    function updateActiveNotificationsCount() {
        const activeCount = $('input[name^="notifications"]:checked').length;
        const totalCount = $('input[name^="notifications"]').length;
        if (activeCount > 0) {
            $('#save-notifications').text(`ذخیره تنظیمات (${activeCount}/${totalCount})`);
        } else {
            $('#save-notifications').text('ذخیره تنظیمات');
        }
    }
    $('input[name^="notifications"]').on('change', function() {
        updateActiveNotificationsCount();
    });
    updateActiveNotificationsCount();
    $(document).on('keydown', function(e) {
        if (e.ctrlKey && e.key === 's') {
            e.preventDefault();
            $('#save-notifications').click();
        }
        if (e.key === 'Escape') {
            $('.Nexora Service Suite-notification').fadeOut();
        }
    });
    if ('ontouchstart' in window) {
        $('.notification-option').addClass('touch-device');
        $('.tab-button').addClass('touch-device');
    }
});

function showNotification(message, type = 'info') {
    $('.Nexora Service Suite-notification').remove();
    
    const notification = $(`
        <div class="Nexora Service Suite-notification ${type}">
            <button class="close-notification" title="بستن">&times;</button>
            <span>${message}</span>
        </div>
    `);
    
    $('body').append(notification);
    const autoHide = setTimeout(() => {
        notification.fadeOut(() => notification.remove());
    }, 5000);
    notification.find('.close-notification').on('click', function() {
        clearTimeout(autoHide);
        notification.fadeOut(() => notification.remove());
    });
    notification.on('click', function(e) {
        if (!$(e.target).is('.close-notification')) {
            clearTimeout(autoHide);
            notification.fadeOut(() => notification.remove());
        }
    });
    notification.hide().fadeIn(300);
}

function showAdvancedNotification(title, message, type = 'info', duration = 5000) {
    const notification = $(`
        <div class="Nexora Service Suite-notification ${type} advanced">
            <button class="close-notification" title="بستن">&times;</button>
            <div class="notification-content">
                <div class="notification-title">${title}</div>
                <div class="notification-message">${message}</div>
            </div>
        </div>
    `);
    
    $('body').append(notification);
    setTimeout(() => {
        notification.fadeOut(() => notification.remove());
    }, duration);
    notification.find('.close-notification').on('click', function() {
        notification.fadeOut(() => notification.remove());
    });
}

function validateNotificationForm() {
    const checkedBoxes = $('input[name^="notifications"]:checked');
    
    if (checkedBoxes.length === 0) {
        showNotification('لطفاً حداقل یک گزینه اعلان را انتخاب کنید.', 'warning');
        return false;
    }
    
    return true;
}

function resetNotificationForm() {
    if (confirm('آیا مطمئن هستید که می‌خواهید تمام تنظیمات را بازنشانی کنید؟')) {
        $('input[name^="notifications"]').prop('checked', true);
        updateActiveNotificationsCount();
        showNotification('فرم بازنشانی شد.', 'info');
    }
}

function toggleAllNotifications(selectAll) {
    $('input[name^="notifications"]').prop('checked', selectAll);
    updateActiveNotificationsCount();
    
    const message = selectAll ? 'همه گزینه‌ها انتخاب شدند.' : 'همه گزینه‌ها لغو انتخاب شدند.';
    showNotification(message, 'info');
}
jQuery(document).ready(function($) {
    const controlButtons = $(`
        <div class="notification-controls" style="margin-bottom: 20px; text-align: center;">
            <button type="button" class="button button-secondary" onclick="toggleAllNotifications(true)" style="margin-right: 10px;">
                انتخاب همه
            </button>
            <button type="button" class="button button-secondary" onclick="toggleAllNotifications(false)" style="margin-left: 10px;">
                لغو انتخاب همه
            </button>
            <button type="button" class="button button-secondary" onclick="resetNotificationForm()" style="margin-left: 10px;">
                بازنشانی
            </button>
        </div>
    `);
    $('#notification-settings-form').before(controlButtons);
    

});