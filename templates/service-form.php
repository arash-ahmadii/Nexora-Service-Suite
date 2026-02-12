<div id="Nexora Service Suite-service-form-modal" class="Nexora Service Suite-modal" style="display:none;">
    <div class="Nexora Service Suite-modal-content">
        <div class="Nexora Service Suite-modal-header">
            <h2 id="Nexora Service Suite-service-form-modal-title">Neuen Service hinzufügen</h2>
            <span class="Nexora Service Suite-close-service-form-modal">&times;</span>
        </div>
        <div class="Nexora Service Suite-modal-body">
            <form id="Nexora Service Suite-service-form">
                <input type="hidden" id="Nexora Service Suite-service-id" value="0">
                
                <div class="Nexora Service Suite-form-group">
                    <label for="Nexora Service Suite-service-title">Service-Titel *</label>
                    <input type="text" id="Nexora Service Suite-service-title" required>
                </div>
                
                <div class="Nexora Service Suite-form-group">
                    <label for="Nexora Service Suite-service-description">Beschreibung</label>
                    <textarea id="Nexora Service Suite-service-description" rows="4"></textarea>
                </div>
                
                <div class="Nexora Service Suite-form-group">
                    <label for="Nexora Service Suite-service-cost">Kosten (EUR) *</label>
                    <input type="number" id="Nexora Service Suite-service-cost" step="0.01" min="0" required>
                </div>
                
                <div class="Nexora Service Suite-form-group">
                    <label for="Nexora Service Suite-service-statuss">Service-Status *</label>
                    <select id="Nexora Service Suite-service-statuss" required>
                        <option value="">-- Bitte wählen --</option>
                        <?php
                        global $wpdb;
                        $statuses = $wpdb->get_results("SELECT * FROM {$wpdb->prefix}nexora_service_status ORDER BY id");
                        foreach ($statuses as $status) {
                            echo '<option value="' . esc_attr($status->id) . '">' . esc_html($status->title) . '</option>';
                        }
                        ?>
                    </select>
                </div>
                
                <div class="Nexora Service Suite-form-group">
                    <label for="Nexora Service Suite-service-brand">Marke (optional)</label>
                    <select id="Nexora Service Suite-service-brand">
                        <option value="">-- Bitte wählen --</option>
                        <?php
                        $brands = $wpdb->get_results("SELECT id, name, parent_id FROM {$wpdb->prefix}nexora_brands ORDER BY parent_id, name");
                        $parent_brands = array_filter($brands, function($b) { return $b->parent_id === NULL; });
                        
                        foreach ($parent_brands as $brand) {
                            echo '<option value="' . esc_attr($brand->id) . '">' . esc_html($brand->name) . '</option>';
                            
                            $children = array_filter($brands, function($b) use ($brand) { return $b->parent_id == $brand->id; });
                            foreach ($children as $child) {
                                echo '<option value="' . esc_attr($child->id) . '">&nbsp;&nbsp;' . esc_html($child->name) . '</option>';
                            }
                        }
                        ?>
                    </select>
                </div>
                
                <div class="Nexora Service Suite-form-actions">
                    <button type="submit" class="button button-primary">Speichern</button>
                    <button type="button" class="button Nexora Service Suite-cancel-service-form-btn">Abbrechen</button>
                </div>
            </form>
        </div>
    </div>
</div>