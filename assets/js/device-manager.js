jQuery(document).ready(function($) {
    const app = $('#Nexora Service Suite-device-manager-app');
    if (!app.length) return;
    const tab = app.data('tab');
    const list = $('#Nexora Service Suite-device-list');
    const form = $('#Nexora Service Suite-device-form');
    const parentSelect = $('#device-parent');
    const saveBtn = $('#device-save-btn');
    const cancelBtn = $('#device-cancel-btn');
    let editingId = null;
    function loadList() {
        list.html('<div class="Nexora Service Suite-loading">Lade Daten...</div>');
        $.post(ajaxurl, {
            action: 'nexora_device_crud',
            nonce: nexora_admin.nonce,
            action_type: 'list',
            data: { type: tab }
        }, function(res) {
            if (!res.success) return list.html('<div class="Nexora Service Suite-error">' + res.data + '</div>');
            renderList(res.data);
        });
    }
    function loadParentDropdown() {
        if (tab === 'type') return parentSelect.html('');
        parentSelect.html('<option value="">Bitte wählen...</option>');
        $.post(ajaxurl, {
            action: 'nexora_device_crud',
            nonce: nexora_admin.nonce,
            action_type: 'list',
            data: { type: getParentType(tab) }
        }, function(res) {
            if (!res.success) return;
            res.data.forEach(function(item) {
                const optionText = item.name + ' (' + (item.slug || item.name) + ')';
                parentSelect.append('<option value="' + item.id + '">' + optionText + '</option>');
            });
        });
    }
    function getParentType(type) {
        if (type === 'brand') return 'type';
        if (type === 'series') return 'brand';
        if (type === 'model') return 'series';
        return null;
    }
    function renderList(items) {
        if (!items.length) return list.html('<div class="Nexora Service Suite-empty">Keine Einträge gefunden.</div>');
        let html = '<table class="Nexora Service Suite-modern-table"><thead><tr>' +
            '<th>Name</th>' + (tab !== 'type' ? '<th>Eltern</th>' : '') + '<th>Slug</th><th>Aktionen</th></tr></thead><tbody>';
        items.forEach(function(item) {
            html += '<tr>' +
                '<td>' + escapeHtml(item.name) + '</td>' +
                (tab !== 'type' ? '<td>' + escapeHtml(item.parent_name || '-') + '</td>' : '') +
                '<td>' + escapeHtml(item.slug) + '</td>' +
                '<td>' +
                '<button class="button button-small edit-device" data-id="' + item.id + '">Bearbeiten</button> ' +
                '<button class="button button-small button-danger delete-device" data-id="' + item.id + '">Löschen</button>' +
                '</td></tr>';
        });
        html += '</tbody></table>';
        list.html(html);
    }
    form.on('submit', function(e) {
        e.preventDefault();
        saveBtn.prop('disabled', true);
        const data = form.serializeArray().reduce((obj, f) => (obj[f.name] = f.value, obj), {});
        const action_type = editingId ? 'update' : 'create';
        if (editingId) data.id = editingId;
        $.post(ajaxurl, {
            action: 'nexora_device_crud',
            nonce: nexora_admin.nonce,
            action_type: action_type,
            data: data,
            id: editingId
        }, function(res) {
            saveBtn.prop('disabled', false);
            if (!res.success) return alert(res.data);
            form[0].reset();
            editingId = null;
            cancelBtn.hide();
            loadList();
            loadParentDropdown();
        });
    });
    list.on('click', '.edit-device', function() {
        const id = $(this).data('id');
        $.post(ajaxurl, {
            action: 'nexora_device_crud',
            nonce: nexora_admin.nonce,
            action_type: 'get',
            id: id
        }, function(res) {
            if (!res.success) return alert(res.data);
            const d = res.data;
            editingId = d.id;
            form.find('#device-name').val(d.name);
            form.find('#device-slug').val(d.slug);
            if (tab !== 'type') form.find('#device-parent').val(d.parent_id);
            cancelBtn.show();
        });
    });
    cancelBtn.on('click', function() {
        editingId = null;
        form[0].reset();
        cancelBtn.hide();
    });
    list.on('click', '.delete-device', function() {
        const id = $(this).data('id');
        if (!confirm('Soll dieser Eintrag gelöscht werden? Wenn untergeordnete Einträge existieren, werden sie ebenfalls gelöscht.')) return;
        $.post(ajaxurl, {
            action: 'nexora_device_crud',
            nonce: nexora_admin.nonce,
            action_type: 'delete',
            id: id,
            cascade: 1
        }, function(res) {
            if (!res.success) return alert(res.data);
            loadList();
            loadParentDropdown();
        });
    });
    function escapeHtml(str) {
        return String(str).replace(/[&<>"]/g, function(s) {
            return ({'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;'}[s]);
        });
    }
    loadList();
    loadParentDropdown();
});
