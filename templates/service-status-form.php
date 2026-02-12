<div id="Nexora Service Suite-service-status-form-modal" class="Nexora Service Suite-modal" style="display:none;">
    <div class="Nexora Service Suite-modal-content">
        <div class="Nexora Service Suite-modal-header">
            <h2 id="Nexora Service Suite-service-status-form-modal-title">Neuen Status hinzufügen</h2>
            <span class="Nexora Service Suite-close-service-status-form-modal">&times;</span>
        </div>
        <div class="Nexora Service Suite-modal-body">
            <form id="Nexora Service Suite-service-status-form">
                <input type="hidden" id="Nexora Service Suite-service-status-id" value="0">
                
                <div class="Nexora Service Suite-form-group">
                    <label for="Nexora Service Suite-service-status-title">Status-Titel *</label>
                    <input type="text" id="Nexora Service Suite-service-status-title" required>
                </div>
                
                <div class="Nexora Service Suite-form-group">
                    <label for="Nexora Service Suite-service-status-color">Farbe</label>
                    <input type="color" id="Nexora Service Suite-service-status-color" value="#0073aa">
                </div>
                
                <div class="Nexora Service Suite-form-group">
                    <label class="Nexora Service Suite-checkbox-label">
                        <input type="checkbox" id="Nexora Service Suite-service-status-is-default">
                        <span class="Nexora Service Suite-checkbox-text">Als Standard-Status festlegen</span>
                    </label>
                    <small class="Nexora Service Suite-form-help">Dieser Status wird automatisch für neue Serviceanfragen ausgewählt</small>
                </div>
                
                <div class="Nexora Service Suite-form-actions">
                    <button type="submit" class="button button-primary">Speichern</button>
                    <button type="button" class="button Nexora Service Suite-cancel-service-status-form-btn">Abbrechen</button>
                </div>
            </form>
        </div>
    </div>
</div>