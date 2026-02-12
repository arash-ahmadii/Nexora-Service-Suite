<?php
class Nexora_User_Profile {

    private $wpdb;
    private $table_name;
    private $kindCurrentUser;

    public function __construct() {
   
        add_action('init', [$this, 'setKindCurrentUser'],10);
        add_action('init', [$this, 'setup'],11);

    }

    public function setKindCurrentUser()
    {
        $this->kindCurrentUser=get_user_meta(get_current_user_id(), 'nexora_kind_user', true);
    }

    public function setup()
    {
        global $wpdb;

        if($this->kindCurrentUser == 'admin' && current_user_can('manage_options'))
        {
            $this->wpdb = $wpdb;
            $this->table_name = $wpdb->prefix . 'nexora_user_status';
            add_action('edit_user_profile', [$this, 'render_status_map_fields']);
            add_action('show_user_profile', [$this, 'render_status_map_fields']);
            add_action('edit_user_profile_update', [$this, 'save_status_map_fields']);
            add_action('personal_options_update', [$this, 'save_status_map_fields']);
        }
    }

    public function render_status_map_fields($user) {
        $statuses = $this->wpdb->get_results("SELECT id, title FROM {$this->wpdb->prefix}nexora_service_status");
         $selected_statuses = $this->wpdb->get_col($this->wpdb->prepare(
        "SELECT status_id FROM wp_nexora_user_show_status WHERE user_id = %d", 
        $user->ID
    ));

  
        
        
        $rows = $this->wpdb->get_results($this->wpdb->prepare(
            "SELECT * FROM {$this->table_name} WHERE user_id = %d", $user->ID
        ));

        if (empty($rows)) {
            $rows = [ (object)[ 'source_status_id' => '', 'destination_status_id' => '' ] ];
        }

        $discount = get_user_meta($user->ID, 'nexora_discount_percent', true);
        $custom_checkbox = get_user_meta($user->ID, 'nexora_custom_checkbox', true);
        $custom_text = get_user_meta($user->ID, 'nexora_custom_text', true);
        $kindUser = get_user_meta($user->ID, 'nexora_kind_user', true);
        $userCanCreateFactor = get_user_meta($user->ID, 'nexora_user_can_create_factor', true);
        $userCanCreateRequestService = get_user_meta($user->ID, 'nexora_user_can_create_request_service', true);
        $userCanEditRequestService = get_user_meta($user->ID, 'nexora_user_can_edit_request_service', true);

      ?>

 <h3>Sichtbare Status</h3>
    <table class="form-table">
        <tr>
            <th><label for="user_visible_statuses">Für Benutzer sichtbare Status</label></th>
            <td>
                <select name="user_visible_statuses[]" id="user_visible_statuses" multiple="multiple" style="width: 50%; height: 150px;">
                    <?php foreach ($statuses as $status): 
                        $selected = in_array($status->id, $selected_statuses) ? 'selected' : '';
                    ?>
                        <option value="<?= esc_attr($status->id) ?>" <?= $selected ?>>
                            <?= esc_html($status->title) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
                <p class="description">Halten Sie die Strg-Taste gedrückt, um mehrere Optionen auszuwählen</p>
            </td>
        </tr>
    </table>

      <table class="form-table">

      <tr>
        <th><label for="nexora_kind_user">Benutzertyp</label></th>
        <td>
           <select name="nexora_kind_user" id="nexora_kind_user">
            <?php if($kindUser == 'admin' ) { ?>
            <option selected value="admin">Administrator</option>
            <option value="employee">Mitarbeiter</option>
            <?php }  else {?>
             <option  value="admin">Administrator</option>
            <option selected value="employee">Mitarbeiter</option>
              <?php } ?>
           </select>
        </td>
    </tr>

       <tr>
        <th><label for="nexora_user_can_create_request_service">Berechtigung zur Erstellung von Service-Anfragen</label></th>
        <td>
           <select name="nexora_user_can_create_request_service" id="nexora_user_can_create_request_service">
            <?php if($userCanCreateRequestService == 'yes' ) { ?>
            <option selected value="yes">Ja</option>
            <option value="no">Nein</option>
            <?php }  else {?>
             <option  value="yes">Ja</option>
            <option selected value="no">Nein</option>
              <?php } ?>
           </select>
        </td>
    </tr>

       <tr>
        <th><label for="nexora_user_can_edit_request_service">Berechtigung zur Bearbeitung von Service-Anfragen</label></th>
        <td>
           <select name="nexora_user_can_edit_request_service" id="nexora_user_can_edit_request_service">
            <?php if($userCanEditRequestService == 'yes' ) { ?>
            <option selected value="yes">Ja</option>
            <option value="no">Nein</option>
            <?php }  else {?>
             <option  value="yes">Ja</option>
            <option selected value="no">Nein</option>
              <?php } ?>
           </select>
        </td>
    </tr>

     <tr>
        <th><label for="nexora_user_can_create_factor">Berechtigung zur Rechnungserstellung</label></th>
        <td>
           <select name="nexora_user_can_create_factor" id="nexora_user_can_create_factor">
            <?php if($userCanCreateFactor == 'yes' ) { ?>
            <option selected value="yes">Ja</option>
            <option value="no">Nein</option>
            <?php }  else {?>
             <option  value="yes">Ja</option>
            <option selected value="no">Nein</option>
              <?php } ?>
           </select>
        </td>
    </tr>

    <tr>
        <th><label for="nexora_discount_percent">Individueller Rabattprozentsatz</label></th>
        <td>
            <input type="number" name="nexora_discount_percent" id="nexora_discount_percent"
                value="<?= esc_attr($discount) ?>" min="0" max="100" step="0.1" style="width: 100px;">
        </td>
    </tr>

    <tr>
        <th><label for="custom_checkbox">Benutzerdefinierten Titel aktivieren</label></th>
        <td>
            <label>
                <input type="checkbox" id="custom_checkbox" name="custom_checkbox" value="1"
                    <?= checked($custom_checkbox, '1', false) ?>>
                Aktivieren
            </label>
            <br><br>
            <input type="text" id="custom_text" name="custom_text"
                placeholder="Gewünschter Titel..."
                value="<?= esc_attr($custom_text) ?>"
                class="regular-text"
                style="width: 300px;"
                <?= $custom_checkbox ? '' : 'disabled' ?>>
        </td>
    </tr>
</table>

        <h3>Berechtigung zur Änderung des Auftragsstatus</h3>
        <table id="status-map-table" class="form-table">
            <tbody>
            <?php foreach ($rows as $index => $row): ?>
                <tr>
                    <td>
                        <select name="status_map[<?= $index ?>][source]" required>
                            <option value="">Von Status</option>
                            <?php foreach ($statuses as $status): ?>
                                <option value="<?= $status->id ?>" <?= $status->id == $row->source_status_id ? 'selected' : '' ?>>
                                    <?= esc_html($status->title) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </td>
                    <td>
                        <select name="status_map[<?= $index ?>][destination]" required>
                            <option value="">Zu Status</option>
                            <?php foreach ($statuses as $status): ?>
                                <option value="<?= $status->id ?>" <?= $status->id == $row->destination_status_id ? 'selected' : '' ?>>
                                    <?= esc_html($status->title) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </td>
                    <td><button type="button" class="remove-status-map">❌</button></td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
        <button type="button" id="add-status-map">➕ Berechtigung hinzufügen</button>

        <script>
        document.addEventListener('DOMContentLoaded', function () {

            const checkbox = document.getElementById('custom_checkbox');
            const textInput = document.getElementById('custom_text');

            if (checkbox && textInput) {
                const toggleField = () => {
                    textInput.disabled = !checkbox.checked;
                };

                checkbox.addEventListener('change', toggleField);
                toggleField();
                    }

            let rowIndex = <?= count($rows) ?>;
            const table = document.querySelector('#status-map-table tbody');
            const statuses = <?= json_encode(array_map(function ($s) {
                return ['id' => $s->id, 'title' => $s->title];
            }, $statuses)) ?>;

            document.getElementById('add-status-map').addEventListener('click', function () {
                const row = document.createElement('tr');

                const sourceSelect = document.createElement('select');
                sourceSelect.name = `status_map[${rowIndex}][source]`;
                sourceSelect.required = true;

                const destSelect = document.createElement('select');
                destSelect.name = `status_map[${rowIndex}][destination]`;
                destSelect.required = true;

                const defaultSource = document.createElement('option');
                defaultSource.value = '';
                defaultSource.textContent = 'Von Status';
                sourceSelect.appendChild(defaultSource);

                const defaultDest = document.createElement('option');
                defaultDest.value = '';
                defaultDest.textContent = 'Zu Status';
                destSelect.appendChild(defaultDest);

                statuses.forEach(s => {
                    const opt1 = document.createElement('option');
                    opt1.value = s.id;
                    opt1.textContent = s.title;
                    sourceSelect.appendChild(opt1);

                    const opt2 = document.createElement('option');
                    opt2.value = s.id;
                    opt2.textContent = s.title;
                    destSelect.appendChild(opt2);
                });

                const td1 = document.createElement('td');
                td1.appendChild(sourceSelect);

                const td2 = document.createElement('td');
                td2.appendChild(destSelect);

                const td3 = document.createElement('td');
                const removeBtn = document.createElement('button');
                removeBtn.type = 'button';
                removeBtn.textContent = '❌';
                removeBtn.className = 'remove-status-map';
                td3.appendChild(removeBtn);

                row.appendChild(td1);
                row.appendChild(td2);
                row.appendChild(td3);

                table.appendChild(row);
                rowIndex++;
            });

            table.addEventListener('click', function (e) {
                if (e.target.classList.contains('remove-status-map')) {
                    e.target.closest('tr').remove();
                }
            });
        });
        </script>
        <?php
    }

    public function save_status_map_fields($user_id) {

        $this->wpdb->delete($this->table_name, ['user_id' => $user_id], ['%d']);

        if (!empty($_POST['status_map']) && is_array($_POST['status_map'])) {
            foreach ($_POST['status_map'] as $map) {
                if (!empty($map['source']) && !empty($map['destination'])) {
                   
                    $resu=$this->wpdb->insert($this->table_name, [
                        'user_id' => $user_id,
                        'source_status_id' => intval($map['source']),
                        'destination_status_id' => intval($map['destination']),
                    ]);
                }
            }
        }
        if (isset($_POST['nexora_discount_percent'])) {
            update_user_meta(
                $user_id,
                'nexora_discount_percent',
                floatval($_POST['nexora_discount_percent'])
            );
        }
        if (!empty($_POST['custom_checkbox'])) {
            update_user_meta($user_id, 'nexora_custom_checkbox', '1');

            if (!empty($_POST['custom_text'])) {
                update_user_meta($user_id, 'nexora_custom_text', sanitize_text_field($_POST['custom_text']));
            }
        } else {
            delete_user_meta($user_id, 'nexora_custom_checkbox');
            delete_user_meta($user_id, 'nexora_custom_text');
        }

        update_user_meta($user_id, 'nexora_kind_user', sanitize_text_field($_POST['nexora_kind_user']));

        update_user_meta($user_id, 'nexora_user_can_create_factor', sanitize_text_field($_POST['nexora_user_can_create_factor']));
        update_user_meta($user_id, 'nexora_user_can_create_request_service', sanitize_text_field($_POST['nexora_user_can_create_request_service']));
        update_user_meta($user_id, 'nexora_user_can_edit_request_service', sanitize_text_field($_POST['nexora_user_can_edit_request_service']));
    $this->wpdb->delete($this->wpdb->prefix .'nexora_user_show_status', ['user_id' => $user_id]);
    if (isset($_POST['user_visible_statuses'])) {
        $statuses = array_map('sanitize_text_field', $_POST['user_visible_statuses']);
        
        foreach ($statuses as $status_id) {
            $this->wpdb->insert($this->wpdb->prefix.'nexora_user_show_status', [
                'user_id' => $user_id,
                'status_id' => $status_id
            ]);
        }
    }

    }
}
?>
