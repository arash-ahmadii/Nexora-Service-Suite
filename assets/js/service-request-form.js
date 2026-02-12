jQuery(document).ready(function($) {
    console.log('Service Request Form JS loaded');
    document.addEventListener('DOMContentLoaded', function () {
        const btn = document.getElementById('add-service-btn');
        const logBox = document.getElementById('service-log');

        if (!btn) {
            if (logBox) {
                logBox.innerHTML += '🔴 دکمه یافت نشد<br>';
            }
            console.error('Add service button not found');
            return;
        }

        if (!logBox) {
            console.error('Log box not found');
            return;
        }

        logBox.innerHTML += '✅ JavaScript لود شد<br>';

        btn.addEventListener('click', function () {
            logBox.innerHTML += '✅ دکمه کلیک شد<br>';
            logBox.scrollTop = logBox.scrollHeight;
        });
    });
    if (typeof window.availableServices === 'undefined') {
        window.availableServices = [];
    }
    function createAdditionalServiceRow(serviceIndex, serviceData = {}) {
        const serviceId = serviceData.id || '';
        const serviceQty = serviceData.qty || 1;
        const serviceNote = serviceData.note || '';
        const template = document.getElementById('additional-service-dropdown-template');
        if (!template) {
            console.error('Additional service dropdown template not found');
            return '';
        }
        const clonedSelect = template.querySelector('.additional-service-select').cloneNode(true);
        clonedSelect.name = `additional_services[${serviceIndex}][id]`;
        if (serviceId) {
            clonedSelect.value = serviceId;
        }
        
        return `
        <div class="additional-service-row" data-additional-service-index="${serviceIndex}" style="margin-bottom:10px; padding:10px; border:1px solid #ddd; border-radius:5px; background:#f9f9f9;">
            <div style="display:flex; align-items:center; gap:10px; flex-wrap:wrap;">
                <div style="flex:1; min-width:200px;">
                    <label style="display:block; margin-bottom:5px; font-weight:bold;">Service:</label>
                    ${clonedSelect.outerHTML}
                </div>
                <div style="flex:1; min-width:100px;">
                    <label style="display:block; margin-bottom:5px; font-weight:bold;">Anzahl:</label>
                    <input type="number" class="additional-service-qty" name="additional_services[${serviceIndex}][qty]" min="1" value="${serviceQty}" style="width:100%;">
                </div>
                <div style="flex:1; min-width:200px;">
                    <label style="display:block; margin-bottom:5px; font-weight:bold;">Notiz:</label>
                    <input type="text" class="additional-service-note" name="additional_services[${serviceIndex}][note]" value="${serviceNote}" placeholder="Optionale Notiz..." style="width:100%;">
                </div>
                <div style="margin-top:20px;">
                    <button type="button" class="remove-additional-service-row" style="color:#fff;background:#dc3545;border:none;padding:8px 12px;border-radius:4px;cursor:pointer;">× Entfernen</button>
                </div>
            </div>
        </div>
        `;
    }
    function addAdditionalServiceRow(serviceData = {}) {
        const $list = $('#Nexora Service Suite-additional-services-list');
        const currentRows = $list.find('.additional-service-row').length;
        const newIndex = currentRows;
        console.log('Adding Mehr Service row with index:', newIndex);
        $list.append(createAdditionalServiceRow(newIndex, serviceData));
        updateSaveButtonVisibility();
    }
    function reindexAdditionalServiceRows() {
        $('#Nexora Service Suite-additional-services-list .additional-service-row').each(function(idx) {
            $(this).attr('data-additional-service-index', idx);
            $(this).find('.additional-service-select').attr('name', `additional_services[${idx}][id]`);
            $(this).find('.additional-service-qty').attr('name', `additional_services[${idx}][qty]`);
            $(this).find('.additional-service-note').attr('name', `additional_services[${idx}][note]`);
        });
        console.log('Reindexed Mehr Service rows');
    }
    function loadExistingAdditionalServices(requestId) {
        if (!requestId || requestId == '0') {
            addAdditionalServiceRow();
            return;
        }
        
        $.ajax({
            url: ajaxurl,
            type: 'POST',
            data: {
                action: 'nexora_get_request_services',
                request_id: requestId,
                nonce: nexora_ajax.nonce
            },
            success: function(response) {
                if (response.success && response.data.services) {
                    const services = response.data.services;
                    if (services.length > 0) {
                        const mainServiceId = $('#Nexora Service Suite-request-service').val();
                        
                        if (mainServiceId) {
                            services.forEach(function(service) {
                                if (service.id != mainServiceId) {
                                    addAdditionalServiceRow(service);
                                }
                            });
                        } else {
                            services.forEach(function(service) {
                                addAdditionalServiceRow(service);
                            });
                        }
                    } else {
                        addAdditionalServiceRow();
                    }
                } else {
                    addAdditionalServiceRow();
                }
                updateSaveButtonVisibility();
            },
            error: function() {
                addAdditionalServiceRow();
                updateSaveButtonVisibility();
            }
        });
    }
    $('#add-additional-service-btn').off('click').on('click', function(e) {
        e.preventDefault();
        console.log('Add Mehr Service button clicked');
        addAdditionalServiceRow();
        calculateTotalCost();
    });
    $(document).on('click', '.remove-additional-service-row', function(e) {
        e.preventDefault();
        console.log('Remove Mehr Service button clicked');
        $(this).closest('.additional-service-row').remove();
        reindexAdditionalServiceRows();
        calculateTotalCost();
        updateSaveButtonVisibility();
    });
    $('#save-mehr-service-btn').on('click', function(e) {
        e.preventDefault();
        console.log('Save Mehr Service as main service clicked');
        const $firstRow = $('.additional-service-row').first();
        if ($firstRow.length === 0) {
            alert('Kein Mehr Service zum Speichern vorhanden.');
            return;
        }
        const serviceSelect = $firstRow.find('.additional-service-select');
        const serviceQty = $firstRow.find('.additional-service-qty');
        const serviceNote = $firstRow.find('.additional-service-note');
        
        const serviceId = serviceSelect.val();
        const serviceTitle = serviceSelect.find('option:selected').text();
        const quantity = serviceQty.val();
        const note = serviceNote.val();
        
        if (!serviceId) {
            alert('Bitte wählen Sie einen Service aus.');
            return;
        }
        if (!confirm(`Möchten Sie "${serviceTitle}" als Hauptservice speichern?\n\nDies wird den aktuellen Hauptservice ersetzen.`)) {
            return;
        }
        $('#Nexora Service Suite-request-service').val(serviceId);
        $('#Nexora Service Suite-service-quantity').val(quantity);
        $('#Nexora Service Suite-service-description').val(note);
        $firstRow.remove();
        reindexAdditionalServiceRows();
        calculateTotalCost();
        updateSaveButtonVisibility();
        $('#additional-service-log').html('<div style="color: #28a745; font-weight: bold;">✅ Service erfolgreich als Hauptservice gespeichert!</div>');
        
        console.log('Mehr Service saved as main service:', {
            id: serviceId,
            title: serviceTitle,
            quantity: quantity,
            note: note
        });
    });
    function updateSaveButtonVisibility() {
        const mehrServiceCount = $('.additional-service-row').length;
        const $saveBtn = $('#save-mehr-service-btn');
        
        if (mehrServiceCount > 0) {
            $saveBtn.show();
        } else {
            $saveBtn.hide();
        }
    }
    $('#Nexora Service Suite-device-type, #Nexora Service Suite-device-brand, #Nexora Service Suite-device-series, #Nexora Service Suite-device-model').hide();
    const requestId = $('#Nexora Service Suite-request-id').val();
    if (!requestId || requestId == '0') {
        $('#Nexora Service Suite-device-type-display, #Nexora Service Suite-device-brand-display, #Nexora Service Suite-device-series-display, #Nexora Service Suite-device-model-display').hide();
        $('#Nexora Service Suite-device-type, #Nexora Service Suite-device-brand, #Nexora Service Suite-device-series, #Nexora Service Suite-device-model').show();
    }
    function calculateTotalCost() {
        let totalCost = 0;
        const mainServiceSelect = $('#Nexora Service Suite-request-service');
        const mainServiceCost = mainServiceSelect.find('option:selected').data('cost');
        const mainServiceQty = $('#Nexora Service Suite-service-quantity').val() || 1;
        
        if (mainServiceCost) {
            totalCost += parseFloat(mainServiceCost) * parseInt(mainServiceQty);
        }
        $('.additional-service-row').each(function() {
            const $row = $(this);
            const serviceSelect = $row.find('.additional-service-select');
            const serviceCost = serviceSelect.find('option:selected').data('cost');
            const serviceQty = $row.find('.additional-service-qty').val() || 1;
            
            if (serviceCost) {
                totalCost += parseFloat(serviceCost) * parseInt(serviceQty);
            }
        });
        if (totalCost > 0) {
            $('#additional-service-log').html(`<strong>Gesamtpreis: ${totalCost.toFixed(2)} €</strong>`);
        } else {
            $('#additional-service-log').html('');
        }
        
        console.log('Total cost calculated:', totalCost);
    }
    $(document).on('change', '.additional-service-select, .additional-service-qty', function() {
        calculateTotalCost();
        updateServiceInfo();
    });
    
    $('#Nexora Service Suite-request-service, #Nexora Service Suite-service-quantity').on('change', function() {
        calculateTotalCost();
        updateServiceInfo();
    });
    function updateServiceInfo() {
        const $firstRow = $('.additional-service-row').first();
        if ($firstRow.length > 0) {
            const serviceSelect = $firstRow.find('.additional-service-select');
            const selectedOption = serviceSelect.find('option:selected');
            const serviceCost = selectedOption.data('cost');
            const serviceTitle = selectedOption.text();
            
            if (serviceCost && serviceTitle !== '-- Service auswählen --') {
                const quantity = $firstRow.find('.additional-service-qty').val() || 1;
                const totalCost = parseFloat(serviceCost) * parseInt(quantity);
                
                $('#additional-service-log').html(`
                    <div style="margin-bottom: 10px;">
                        <strong>Ausgewählter Service:</strong> ${serviceTitle}<br>
                        <strong>Kosten pro Stück:</strong> ${serviceCost} €<br>
                        <strong>Anzahl:</strong> ${quantity}<br>
                        <strong>Gesamtkosten:</strong> ${totalCost.toFixed(2)} €
                    </div>
                    <div style="color: #0073aa; font-size: 12px;">
                        💡 Klicken Sie auf "Als Service speichern" um diesen Service als Hauptservice zu übernehmen.
                    </div>
                `);
            } else {
                $('#additional-service-log').html(`
                    <div style="color: #6c757d; font-size: 12px;">
                        Wählen Sie einen Service aus, um Details zu sehen.
                    </div>
                `);
            }
        }
    }
    loadExistingAdditionalServices(requestId);
    setTimeout(function() {
        calculateTotalCost();
        updateServiceInfo();
    }, 500);
}); 