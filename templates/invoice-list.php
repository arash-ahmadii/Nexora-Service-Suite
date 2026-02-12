<div class="wrap Nexora Service Suite-admin">
    <?php
    $admin_menu = new Nexora_Admin_Menu();
    $admin_menu->render_admin_header();
    ?>
    <h1 class="wp-heading-inline">Rechnungsverwaltung</h1>

<div class="Nexora Service Suite-search-box">
    <input type="text" id="Nexora Service Suite-invoice-search" placeholder="Rechnung suchen..." />
    <button id="Nexora Service Suite-search-invoice-btn" class="button">Suchen</button>
</div>

    <div id="Nexora Service Suite-invoice-list-container">
        <table class="wp-list-table widefat fixed striped">
            <thead>
                <tr>
                            <th>Rechnungs-ID</th>
        <th>Anfrage-ID</th>
        <th>Seriennummer</th>
        <th>Modell</th>
        <th>Gesamtbetrag</th>
        <th>Rabatt</th>
        <th>Endbetrag</th>
        <th>Benutzer</th>
        <th>Anfragedatum</th>
        <th>Rechnungsdatum</th>
        <th>Details</th>
                </tr>
            </thead>
            <tbody id="Nexora Service Suite-invoice-list">
                
            </tbody>
        </table>
        <div class="Nexora Service Suite-pagination">
                <button id="Nexora Service Suite-invoice-prev-page" class="button" disabled>Vorherige</button>
    <span id="Nexora Service Suite-invoice-page-info">Seite 1 von 1</span>
    <button id="Nexora Service Suite-invoice-next-page" class="button" disabled>Nächste</button>
        </div>
    </div>

    
    <div id="Nexora Service Suite-invoice-modal" class="Nexora Service Suite-modal" style="display:none;">
        <div class="Nexora Service Suite-modal-content">
            <div class="Nexora Service Suite-modal-header">
                <h2>Rechnungsdetails</h2>
                <span class="Nexora Service Suite-close-modal">&times;</span>
            </div>
            <div class="Nexora Service Suite-modal-body" id="Nexora Service Suite-invoice-detail-body">
                
            </div>
            <div class="Nexora Service Suite-modal-footer">
                <button type="button" class="button Nexora Service Suite-cancel-btn">Schließen</button>
            </div>
        </div>
    </div>
</div>

<script>
jQuery(document).ready(function($){
    function loadInvoices(page = 1, search = '') {
        $.post(ajaxurl, {
            action: 'nexora_get_invoices',
            page: page,
            search: search,
            nonce: nexora_ajax.nonce
        }, function(resp){
            if(resp.success) {
                let rows = '';
                resp.data.invoices.forEach(inv => {
                    rows += `<tr>
                        <td>${inv.id}</td>
                        <td>${inv.request_id}</td>
                        <td>${inv.serial}</td>
                        <td>${inv.model}</td>
                        <td>${inv.total_price}</td>
                        <td>${inv.discount}</td>
                        <td>${inv.final_price}</td>
                        <td>${inv.user_name}</td>
                        <td>${inv.request_created_at}</td>
                        <td>${inv.invoice_created_at}</td>
                        <td><button class="button Nexora Service Suite-view-invoice" data-id="${inv.id}">Anzeigen</button></td>
                    </tr>`;
                });
                $('#Nexora Service Suite-invoice-list').html(rows);
            }
        });
    }
    $('#Nexora Service Suite-search-invoice-btn').on('click', function(){
        let search = $('#Nexora Service Suite-invoice-search').val();
        loadInvoices(1, search);
    });

    $(document).on('click', '.Nexora Service Suite-view-invoice', function(){
        let id = $(this).data('id');
        $.post(ajaxurl, {
            action: 'nexora_get_invoice_detail',
            id: id,
            nonce: nexora_ajax.nonce
        }, function(resp){
            if(resp.success) {
                $('#Nexora Service Suite-invoice-detail-body').html(resp.data.html);
                $('#Nexora Service Suite-invoice-modal').fadeIn(200);
            }
        });
    });
    $('.Nexora Service Suite-close-modal, .Nexora Service Suite-cancel-btn').on('click', function(){
        $('#Nexora Service Suite-invoice-modal').fadeOut(200);
    });
    loadInvoices();
});

</script>
