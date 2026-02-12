<div class="wrap Nexora Service Suite-admin">
    <h1 class="wp-heading-inline">Service-Verwaltung</h1>
    <a href="#" class="page-title-action" id="Nexora Service Suite-add-service">Neuen Service hinzufügen</a>
    
    <div class="Nexora Service Suite-search-box">
        <input type="text" id="Nexora Service Suite-service-search" placeholder="Service suchen..." />
        <button id="Nexora Service Suite-search-service-btn" class="button">Suchen</button>
    </div>
    
    <div id="Nexora Service Suite-service-list-container">
        <table class="wp-list-table widefat fixed striped">
            <thead>
                <tr>
                                <th>ID</th>
            <th>Titel</th>
            <th>Beschreibung</th>
            <th>Kosten</th>
            <th>Status</th>
            <th>Erstellungsdatum</th>
            <th>Aktionen</th>
                </tr>
            </thead>
            <tbody id="Nexora Service Suite-service-list">
            </tbody>
        </table>
        
        <div class="Nexora Service Suite-pagination">
                    <button id="Nexora Service Suite-prev-page" class="button" disabled>Vorherige</button>
        <span id="Nexora Service Suite-page-info">Seite 1 von 1</span>
        <button id="Nexora Service Suite-next-page" class="button" disabled>Nächste</button>
        </div>
    </div>
    
    <div id="Nexora Service Suite-service-modal" class="Nexora Service Suite-modal" style="display:none;">
        <div class="Nexora Service Suite-modal-content">
            <div class="Nexora Service Suite-modal-header">
                <h2 id="Nexora Service Suite-service-modal-title">Neuen Service hinzufügen</h2>
                <span class="Nexora Service Suite-close-modal">&times;</span>
            </div>
            <div class="Nexora Service Suite-modal-body">
                <form id="Nexora Service Suite-service-form">
                    <input type="hidden" id="Nexora Service Suite-service-id" value="0">
                    
                    <div class="Nexora Service Suite-form-group">
                        <label for="Nexora Service Suite-service-title">Service-Titel</label>
                        <input type="text" id="Nexora Service Suite-service-title" required>
                    </div>
                    
                    <div class="Nexora Service Suite-form-group">
                        <label for="Nexora Service Suite-service-description">Beschreibung</label>
                        <textarea id="Nexora Service Suite-service-description" rows="4"></textarea>
                    </div>
                    
                    <div class="Nexora Service Suite-form-group">
                        <label for="Nexora Service Suite-service-cost">Kosten</label>
                        <input type="number" id="Nexora Service Suite-service-cost" step="1" min="0" required>
                    </div>
                    
                    <div class="Nexora Service Suite-form-group">
                        <label for="Nexora Service Suite-service-status-title">Service-Status</label>
                        <select id="Nexora Service Suite-service-status-title" required>
                        <option value="" disabled selected>-- Status wählen --</option>
                        <option value="active">Aktiv</option>
                        <option value="inactive">Inaktiv</option>
                        </select>
                    </div>

                    
                    <div class="Nexora Service Suite-form-actions">
                        <button type="submit" class="button button-primary">Speichern</button>
                        <button type="button" class="button Nexora Service Suite-cancel-btn">Abbrechen</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<script>
jQuery(document).ready(function($) {
});
</script>