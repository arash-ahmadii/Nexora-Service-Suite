jQuery(document).ready(function($) {
    'use strict';
    const nexoraLangStrings = {
        de: {
            auth_title: 'Registration and Login',
            login_tab: 'Login',
            register_tab: 'Register',
            email: 'Email Address *',
            login_identifier: 'Email or Username *',
            password: 'Password *',
            password_confirm: 'Repeat Password *',
            remember: 'Remember password',
            login_btn: 'Login',
            register_btn: 'Register',
            next_step: 'Next to Step',
            step1: 'Step 1: Basic Information',
            step2: 'Step 2: Customer Data',
            customer_type: 'Customer Type *',
            business: 'Business',
            private: 'Private',
            customer_number: 'Customer Number',
            required_field: 'This field is required',
            invalid_email: 'Invalid email address',
            password_mismatch: 'Passwords do not match',
            processing: 'Processing...',
            error: 'Error processing request',
            password_strength: {
                weak: 'Password is weak',
                medium: 'Password is medium',
                strong: 'Password is strong'
            }
        },
        en: {
            auth_title: 'Registration and Login',
            login_tab: 'Login',
            register_tab: 'Register',
            email: 'Email Address *',
            login_identifier: 'Email or Username *',
            password: 'Password *',
            password_confirm: 'Repeat Password *',
            remember: 'Remember password',
            login_btn: 'Login',
            register_btn: 'Register',
            next_step: 'Next to Step',
            step1: 'Step 1: Basic Information',
            step2: 'Step 2: Customer Data',
            customer_type: 'Customer Type *',
            business: 'Business',
            private: 'Private',
            customer_number: 'Customer Number',
            required_field: 'This field is required',
            invalid_email: 'Invalid email address',
            password_mismatch: 'Passwords do not match',
            processing: 'Processing...',
            error: 'Error processing request',
            password_strength: {
                weak: 'Password is weak',
                medium: 'Password is medium',
                strong: 'Password is strong'
            }
        }
    };
    let nexoraLang = localStorage.getItem('nexora_lang') || 'en';
    let selectedCustomerType = '';
    function isValidEmail(email) {
        const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
        return emailRegex.test(email);
    }

    function updateNexoraFormLang(lang) {
        $('#Nexora Service Suite-auth-title').text(nexoraLangStrings[lang].auth_title);
        $('.Nexora Service Suite-tab-btn[data-tab="login"]').text(nexoraLangStrings[lang].login_tab);
        $('.Nexora Service Suite-tab-btn[data-tab="register"]').text(nexoraLangStrings[lang].register_tab);
        $('#login_email').closest('.Nexora Service Suite-form-group').find('label').text(nexoraLangStrings[lang].login_identifier);
        $('#login_password').closest('.Nexora Service Suite-form-group').find('label').text(nexoraLangStrings[lang].password);
        $('.Nexora Service Suite-checkbox-label').contents().filter(function(){ return this.nodeType === 3; }).last().replaceWith(' ' + nexoraLangStrings[lang].remember);
        $('.Nexora Service Suite-login-form button[type="submit"]').text(nexoraLangStrings[lang].login_btn);
        $('#reg_email').closest('.Nexora Service Suite-form-group').find('label').text(nexoraLangStrings[lang].email);
        $('#reg_password').closest('.Nexora Service Suite-form-group').find('label').text(nexoraLangStrings[lang].password);
        $('#reg_password_confirm').closest('.Nexora Service Suite-form-group').find('label').text(nexoraLangStrings[lang].password_confirm);
        $('.Nexora Service Suite-register-form .next-step').text(nexoraLangStrings[lang].next_step + ' 2');
        $('.Nexora Service Suite-form-step[data-step="1"] h3').text(nexoraLangStrings[lang].step1);
        $('.Nexora Service Suite-form-step[data-step="2"] h3').text(nexoraLangStrings[lang].step2);
        $('.Nexora Service Suite-form-group label:contains("Kundenart"), .Nexora Service Suite-form-group label:contains("Customer Type")').text(nexoraLangStrings[lang].customer_type);
        $('.Nexora Service Suite-radio-label input[value="business"]').parent().contents().filter(function(){ return this.nodeType === 3; }).last().replaceWith(' ' + nexoraLangStrings[lang].business);
        $('.Nexora Service Suite-radio-label input[value="private"]').parent().contents().filter(function(){ return this.nodeType === 3; }).last().replaceWith(' ' + nexoraLangStrings[lang].private);
        $('#customer_number').closest('.Nexora Service Suite-form-group').find('label').text(nexoraLangStrings[lang].customer_number);
        $('#Nexora Service Suite-current-lang').text(lang.toUpperCase());
    }
    updateNexoraFormLang(nexoraLang);
    $('#Nexora Service Suite-lang-switcher').on('click', function() {
        nexoraLang = (nexoraLang === 'de') ? 'en' : 'de';
        localStorage.setItem('nexora_lang', nexoraLang);
        updateNexoraFormLang(nexoraLang);
    });
    $('.Nexora Service Suite-tab-btn').on('click', function() {
        const tabId = $(this).data('tab');
        $('.Nexora Service Suite-tab-btn').removeClass('active');
        $(this).addClass('active');
        $('.Nexora Service Suite-tab-content').removeClass('active');
        $('#' + tabId + '-tab').addClass('active');
        if (tabId === 'register') {
            $('.Nexora Service Suite-form-step').removeClass('active').hide();
            $('.Nexora Service Suite-form-step[data-step="1"]').addClass('active').show();
            updateProgressIndicator(1);
            updateRegisterProgressBar(1);
        } else {
            updateRegisterProgressBar(0);
        }
    });
    $('.Nexora Service Suite-register-form .Nexora Service Suite-form-step').hide();
    $('.Nexora Service Suite-register-form .Nexora Service Suite-form-step.active').show();

    $('.Nexora Service Suite-register-form .next-step').on('click', function() {
        const currentStep = $(this).closest('.Nexora Service Suite-form-step');
        const nextStep = currentStep.next('.Nexora Service Suite-form-step');
        
        console.log('Next step clicked, current step:', currentStep.data('step'));
        console.log('Next step will be:', nextStep.data('step'));
        
        if (validateCurrentStep(currentStep)) {
            console.log('Validation passed, moving to next step');
            currentStep.removeClass('active').hide();
            nextStep.addClass('active').fadeIn(200);
            updateProgressIndicator(nextStep.data('step'));
            updateRegisterProgressBar(nextStep.data('step'));
            if (nextStep.data('step') === 2) {
                const customerType = $('#customer_type').val() || selectedCustomerType;
                console.log('Moving to step 2, customer type is:', customerType);
                
                if (customerType) {
                    showCustomerForm(customerType);
                } else {
                    console.log('No customer type found, hiding forms');
                    $('.private-customer, .business-customer').hide();
                }
            }
        } else {
            console.log('Validation failed, staying on current step');
        }
    });
    $('.Nexora Service Suite-register-form .prev-step').on('click', function() {
        const currentStep = $(this).closest('.Nexora Service Suite-form-step');
        const prevStep = currentStep.prev('.Nexora Service Suite-form-step');
        currentStep.removeClass('active').hide();
        prevStep.addClass('active').fadeIn(200);
        updateProgressIndicator(prevStep.data('step'));
        updateRegisterProgressBar(prevStep.data('step'));
    });
    function validateCurrentStep(step) {
        console.log('🔍 Validation disabled - allowing any data');
        return true;
    }
    
    function showFieldError(field, message) {
        const formGroup = field.closest('.Nexora Service Suite-form-group');
        formGroup.addClass('error');
        formGroup.find('.error-message').remove();
        const label = formGroup.find('label');
        if (label.length) {
            label.css('color', '#dc3545');
            label.css('font-weight', 'bold');
        }
        if (field.hasClass('Nexora Service Suite-dropdown-trigger')) {
            const dropdown = field.closest('.Nexora Service Suite-custom-dropdown');
            dropdown.after('<div class="error-message">' + message + '</div>');
        } else if (field.is('select')) {
            field.after('<div class="error-message">' + message + '</div>');
        } else {
        field.after('<div class="error-message">' + message + '</div>');
        }
        if (formGroup.length) {
            const rect = formGroup[0].getBoundingClientRect();
            if (rect.top < 0 || rect.bottom > window.innerHeight) {
                formGroup[0].scrollIntoView({ behavior: 'smooth', block: 'center' });
            }
        }
        
        console.log('🔍 Error shown for field:', field.attr('name') || field.attr('id') || 'unknown', 'Message:', message);
    }
    
    function clearFieldError(field) {
        const formGroup = field.closest('.Nexora Service Suite-form-group');
        formGroup.removeClass('error');
        formGroup.find('.error-message').remove();
        const label = formGroup.find('label');
        if (label.length) {
            label.css('color', '');
            label.css('font-weight', '');
        }
        if (field.hasClass('Nexora Service Suite-dropdown-trigger')) {
            const dropdown = field.closest('.Nexora Service Suite-custom-dropdown');
            dropdown.next('.error-message').remove();
        }
        
        console.log('🧹 Error cleared for field:', field.attr('name') || field.attr('id') || 'unknown');
    }
    
    function updateProgressIndicator(step) {
        $('.Nexora Service Suite-progress-step').removeClass('active completed');
        
        for (let i = 1; i <= 2; i++) {
            const stepElement = $('.Nexora Service Suite-progress-step[data-step="' + i + '"]');
            if (i < step) {
                stepElement.addClass('completed');
            } else if (i === step) {
                stepElement.addClass('active');
            }
        }
    }
    $('input[name="password"]').on('input', function() {
        const password = $(this).val();
        const strength = calculatePasswordStrength(password);
        updatePasswordStrengthIndicator(strength);
    });
    
    function calculatePasswordStrength(password) {
        let score = 0;
        
        if (password.length >= 8) score++;
        if (/[a-z]/.test(password)) score++;
        if (/[A-Z]/.test(password)) score++;
        if (/[0-9]/.test(password)) score++;
        if (/[^A-Za-z0-9]/.test(password)) score++;
        
        if (score < 3) return 'weak';
        if (score < 5) return 'medium';
        return 'strong';
    }
    
    function updatePasswordStrengthIndicator(strength) {
        const indicator = $('.Nexora Service Suite-password-strength');
        const barFill = $('.Nexora Service Suite-password-strength-bar-fill');
        
        indicator.removeClass('weak medium strong');
        barFill.removeClass('weak medium strong');
        
        indicator.addClass(strength);
        barFill.addClass(strength);
        
        indicator.text(nexoraLangStrings[nexoraLang].password_strength[strength]);
    }
    $('.Nexora Service Suite-login-form').on('submit', function(e) {
        e.preventDefault();
        console.log('🔐 Login form submitted');
        console.log('📋 Form data:', $(this).serialize());
        console.log('🔑 AJAX URL:', nexora_auth.ajax_url);
        console.log('🔐 Auth nonce:', nexora_auth.auth_nonce);
        handleFormSubmission($(this), 'login');
    });
    
    $('.Nexora Service Suite-register-form').on('submit', function(e) {
        e.preventDefault();
        handleFormSubmission($(this), 'register');
    });
    function handleFormSubmission(form, type) {
        const submitBtn = form.find('button[type="submit"]');
        const originalText = submitBtn.text();
        
        console.log('🚀 Form submission started for type:', type);
        if (type === 'register') {
            const password = form.find('#reg_password').val();
            const passwordConfirm = form.find('#reg_password_confirm').val();
            
            if (password !== passwordConfirm) {
                showFieldError(form.find('#reg_password_confirm'), nexoraLangStrings[nexoraLang].password_mismatch);
                submitBtn.text(originalText).prop('disabled', false);
                form.removeClass('Nexora Service Suite-loading');
                return;
            }
        }
        
        console.log('🔍 Form validation passed - proceeding with submission');
        
        submitBtn.text(nexoraLangStrings[nexoraLang].processing).prop('disabled', true);
        form.addClass('Nexora Service Suite-loading');
        if (submitBtn.next('.Nexora Service Suite-btn-loading-spinner').length === 0) {
            submitBtn.after('<span class="Nexora Service Suite-btn-loading-spinner"></span>');
        }
        
        form.find('.Nexora Service Suite-message').remove();
        
        let formData;
        if (type === 'register') {
            formData = collectFormData(form);
        } else {
            formData = new FormData(form[0]);
        }
        
        formData.append('action', 'nexora_' + type + '_user');
        formData.append('auth_nonce', nexora_auth.auth_nonce);
        
        console.log('📤 Sending AJAX request with data:', Object.fromEntries(formData));
        
        $.ajax({
            url: nexora_auth.ajax_url,
            type: 'POST',
            data: formData,
            processData: false,
            contentType: false,
            success: function(response) {
                console.log('✅ AJAX Success Response:', response);
                
                if (response.success) {
                    showMessage(form, response.message, 'success');
                    $(document).trigger('nexora_form_success');
                    if (type === 'login') {
                        setTimeout(function() {
                            if (response.redirect) {
                                window.location.href = response.redirect;
                            } else {
                                window.location.reload();
                            }
                        }, 1200);
                    }
                } else {
                    showMessage(form, response.message, 'error');
                }
            },
            error: function(xhr, status, error) {
                console.error('❌ AJAX Error:', status, error);
                console.error('📄 Response Text:', xhr.responseText);
                showMessage(form, nexoraLangStrings[nexoraLang].error, 'error');
            },
            complete: function() {
                submitBtn.text(originalText).prop('disabled', false);
                form.removeClass('Nexora Service Suite-loading');
                form.find('.Nexora Service Suite-btn-loading-spinner').remove();
            }
        });
    }
    
    function showMessage(form, message, type) {
        const messageHtml = '<div class="Nexora Service Suite-message ' + type + '">' + message + '</div>';
        form.prepend(messageHtml);
        $('html, body').animate({
            scrollTop: form.find('.Nexora Service Suite-message').offset().top - 100
        }, 500);
    }
    $('.Nexora Service Suite-form-group input, .Nexora Service Suite-form-group select').on('blur', function() {
        const field = $(this);
        const value = field.val().trim();
        
        if (field.prop('required') && !value) {
            showFieldError(field, nexoraLangStrings[nexoraLang].required_field);
        } else if (field.attr('type') === 'email' && value && !isValidEmail(value) && field.attr('id') !== 'login_email') {
            showFieldError(field, nexoraLangStrings[nexoraLang].invalid_email);
        } else {
            clearFieldError(field);
        }
    });
    $(document).on('click', '.Nexora Service Suite-dropdown-trigger', function() {
        const menu = $(this).next('.Nexora Service Suite-dropdown-menu');
        $('.Nexora Service Suite-dropdown-menu').not(menu).hide();
        menu.toggle();
    });
    
    $(document).on('click', '.Nexora Service Suite-dropdown-option', function() {
        const value = $(this).data('value');
        const text = $(this).text();
        const dropdown = $(this).closest('.Nexora Service Suite-custom-dropdown');
        const trigger = dropdown.find('.Nexora Service Suite-dropdown-trigger');
        const hiddenInput = dropdown.next('input[type="hidden"]');
        const arrow = trigger.find('span').detach();
        trigger.text(text).append(arrow);
        trigger.attr('data-value', value);
        if (hiddenInput.length) {
            hiddenInput.val(value);
        }
        if (value) {
            trigger.css('color', '#222');
        } else {
            trigger.css('color', '#888');
        }
        $(this).closest('.Nexora Service Suite-dropdown-menu').hide();
        if (dropdown.attr('id') === 'customer_type_dropdown') {
            selectedCustomerType = value;
            showCustomerForm(value);
            console.log('Customer type selected:', value);
        }
        if (dropdown.attr('id') === 'salutation_private_dropdown') {
            console.log('Private salutation selected:', value);
        }
        if (dropdown.attr('id') === 'salutation_business_dropdown') {
            console.log('Business salutation selected:', value);
        }
        
        console.log('Custom dropdown changed:', value, '=', text);
    });
    $(document).on('click', function(e) {
        if (!$(e.target).closest('.Nexora Service Suite-custom-dropdown').length) {
            $('.Nexora Service Suite-dropdown-menu').hide();
        }
    });
    $(document).on('mouseenter', '.Nexora Service Suite-dropdown-option', function() {
        $(this).css('background-color', '#f0f0f0');
    });
    
    $(document).on('mouseleave', '.Nexora Service Suite-dropdown-option', function() {
        $(this).css('background-color', 'transparent');
    });
    function showCustomerForm(customerType) {
        const privateForm = $('.private-customer');
        const businessForm = $('.business-customer');
        
        console.log('Showing customer form for type:', customerType);
        privateForm.hide();
        businessForm.hide();
        if (customerType === 'private') {
            console.log('Showing private customer form');
            privateForm.show();
            privateForm.find('input[required], select[required]').prop('required', true);
            businessForm.find('input, select').prop('required', false);
        } else if (customerType === 'business') {
            console.log('Showing business customer form');
            businessForm.show();
            businessForm.find('input[required], select[required]').prop('required', true);
            privateForm.find('input, select').prop('required', false);
        } else {
            console.log('No customer type selected');
            privateForm.find('input, select').prop('required', false);
            businessForm.find('input, select').prop('required', false);
        }
    }
    function collectFormData(form) {
        const formData = new FormData(form[0]);
        const customerType = formData.get('customer_type');
        const fieldsToRemove = [
            'salutation_private', 'first_name_private', 'last_name_private', 'street_private', 
            'house_number_private', 'postfach_private', 'postal_code_private', 'city_private', 
            'country_private', 'reference_number_private', 'phone_private', 'newsletter_private', 
            'terms_accepted_private',
            'salutation_business', 'first_name_business', 'last_name_business', 'street_business', 
            'house_number_business', 'postfach_business', 'postal_code_business', 'city_business', 
            'country_business', 'vat_id', 'phone_business', 'newsletter_business', 'terms_accepted_business'
        ];
        
        fieldsToRemove.forEach(field => {
            formData.delete(field);
        });
        if (customerType === 'private') {
            const privateFields = [
                'salutation_private', 'first_name_private', 'last_name_private', 'street_private', 
                'house_number_private', 'postfach_private', 'postal_code_private', 'city_private', 
                'country_private', 'reference_number_private', 'phone_private', 'newsletter_private', 
                'terms_accepted_private'
            ];
            
            privateFields.forEach(field => {
                const value = form.find(`[name="${field}"]`).val();
                if (value !== undefined && value !== '') {
                    formData.append(field, value);
                }
            });
            const fieldMapping = {
                'salutation_private': 'salutation',
                'first_name_private': 'first_name',
                'last_name_private': 'last_name',
                'street_private': 'street',
                'house_number_private': 'house_number',
                'postfach_private': 'postfach',
                'postal_code_private': 'postal_code',
                'city_private': 'city',
                'country_private': 'country',
                'reference_number_private': 'reference_number',
                'phone_private': 'phone',
                'newsletter_private': 'newsletter',
                'terms_accepted_private': 'terms_accepted'
            };
            
            Object.keys(fieldMapping).forEach(oldName => {
                const value = formData.get(oldName);
                if (value) {
                    formData.delete(oldName);
                    formData.append(fieldMapping[oldName], value);
                }
            });
            
        } else if (customerType === 'business') {
            const businessFields = [
                'salutation_business', 'first_name_business', 'last_name_business', 'company_name', 
                'street_business', 'house_number_business', 'postfach_business', 'postal_code_business', 
                'city_business', 'country_business', 'vat_id', 'phone_business', 'newsletter_business', 
                'terms_accepted_business'
            ];
            
            businessFields.forEach(field => {
                const value = form.find(`[name="${field}"]`).val();
                if (value !== undefined && value !== '') {
                    formData.append(field, value);
                }
            });
            const fieldMapping = {
                'salutation_business': 'salutation',
                'first_name_business': 'first_name',
                'last_name_business': 'last_name',
                'street_business': 'street',
                'house_number_business': 'house_number',
                'postfach_business': 'postfach',
                'postal_code_business': 'postal_code',
                'city_business': 'city',
                'country_business': 'country',
                'phone_business': 'phone',
                'newsletter_business': 'newsletter',
                'terms_accepted_business': 'terms_accepted'
            };
            
            Object.keys(fieldMapping).forEach(oldName => {
                const value = formData.get(oldName);
                if (value) {
                    formData.delete(oldName);
                    formData.append(fieldMapping[oldName], value);
                }
            });
        }
        
        return formData;
    }
    $(document).ready(function() {
        const initialCustomerType = $('#customer_type').val();
        if (initialCustomerType) {
            selectedCustomerType = initialCustomerType;
            showCustomerForm(initialCustomerType);
        }
        $('select').on('change', function() {
            console.log('Legacy select changed:', $(this).attr('id'), 'Value:', $(this).val());
            if ($(this).val() !== '' && $(this).val() !== null) {
                $(this).css('color', '#222 !important');
                $(this).removeClass('placeholder-text');
                $(this).attr('data-selected', 'true');
            } else {
                $(this).css('color', '#888 !important');
                $(this).addClass('placeholder-text');
                $(this).removeAttr('data-selected');
            }
        });
    });
    if ($('.Nexora Service Suite-progress').length) {
        updateProgressIndicator(1);
    }
    $('.Nexora Service Suite-tab-content.active input:first').focus();
    $(document).on('keydown', function(e) {
        if (e.key === 'Enter' && e.ctrlKey) {
            const activeStep = $('.Nexora Service Suite-form-step.active');
            const nextBtn = activeStep.find('.next-step');
            
            if (nextBtn.length && validateCurrentStep(activeStep)) {
                nextBtn.click();
            }
        }
    });
    let autoSaveTimer;
    $('.Nexora Service Suite-register-form input, .Nexora Service Suite-register-form select').on('input', function() {
        clearTimeout(autoSaveTimer);
        autoSaveTimer = setTimeout(function() {
            const formData = {};
            $('.Nexora Service Suite-register-form').serializeArray().forEach(function(item) {
                formData[item.name] = item.value;
            });
            localStorage.setItem('nexora_form_data', JSON.stringify(formData));
        }, 1000);
    });
    const savedData = localStorage.getItem('nexora_form_data');
    if (savedData) {
        const formData = JSON.parse(savedData);
        Object.keys(formData).forEach(function(key) {
            const field = $('.Nexora Service Suite-register-form [name="' + key + '"]');
            if (field.length) {
                field.val(formData[key]);
                if (key === 'customer_type' && formData[key]) {
                    selectedCustomerType = formData[key];
                    showCustomerForm(formData[key]);
                }
            }
        });
    }
    $(document).on('nexora_form_success', function() {
        localStorage.removeItem('nexora_form_data');
    });

    function updateRegisterProgressBar(step) {
        if ($('.Nexora Service Suite-tab-btn[data-tab="register"]').hasClass('active')) {
            $('.Nexora Service Suite-register-progress').show();
            $('.Nexora Service Suite-register-progress-segment').each(function() {
                var segStep = parseInt($(this).data('step'));
                if (segStep <= 2 && segStep <= step) {
                    $(this).addClass('active');
                } else {
                    $(this).removeClass('active');
                }
            });
        } else {
            $('.Nexora Service Suite-register-progress').hide();
        }
    }
    $(function() {
        if ($('.Nexora Service Suite-tab-btn[data-tab="register"]').hasClass('active')) {
            let step = $('.Nexora Service Suite-form-step.active').data('step') || 1;
            updateRegisterProgressBar(step);
        } else {
            updateRegisterProgressBar(0);
        }
    });
    window.debugNexoraForm = function() {
        console.log('=== Nexora Service Suite Form Debug ===');
        console.log('Selected customer type:', selectedCustomerType);
        console.log('Customer type field value:', $('#customer_type').val());
        console.log('Private form visible:', $('.private-customer').is(':visible'));
        console.log('Business form visible:', $('.business-customer').is(':visible'));
        console.log('Current step:', $('.Nexora Service Suite-form-step.active').data('step'));
        console.log('===============================');
    };
    $(document).on('input', '#reg_password, #reg_password_confirm', function() {
        const password = $('#reg_password').val();
        const passwordConfirm = $('#reg_password_confirm').val();
        const matchDiv = $('.Nexora Service Suite-password-match');
        
        if (passwordConfirm.length > 0) {
            if (password === passwordConfirm) {
                matchDiv.text('✓ Passwörter stimmen überein').css('color', '#22c55e').show();
                clearFieldError($('#reg_password_confirm'));
            } else {
                matchDiv.text('✗ Passwörter stimmen nicht überein').css('color', '#e53e3e').show();
                showFieldError($('#reg_password_confirm'), nexoraLangStrings[nexoraLang].password_mismatch);
            }
        } else {
            matchDiv.hide();
            clearFieldError($('#reg_password_confirm'));
        }
    });
    window.togglePassword = function(inputId) {
        const passwordInput = document.getElementById(inputId);
        const toggleIcon = passwordInput.parentElement.querySelector('.Nexora Service Suite-eye-icon');
        
        if (passwordInput.type === 'password') {
            passwordInput.type = 'text';
            toggleIcon.textContent = '🙈';
            toggleIcon.title = 'Passwort verbergen';
        } else {
            passwordInput.type = 'password';
            toggleIcon.textContent = '👁';
            toggleIcon.title = 'Passwort anzeigen';
        }
    };
});
