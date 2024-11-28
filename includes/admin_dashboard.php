<?php

function hours_registration_admin_menu()
{
    add_menu_page(
        'Uren Overzicht',
        'Uren Overzicht',
        'manage_options',
        'uren-overzicht',
        'hours_registration_admin_page',
        'dashicons-clock'
    );
}
add_action('admin_menu', 'hours_registration_admin_menu');

function hours_registration_admin_page()
{
    global $wpdb;
    $table_name = $wpdb->prefix . 'uren';

    if (isset($_POST['update_status'])) {
        $entry_id = intval($_POST['entry_id']);
        $status = sanitize_text_field($_POST['status']);
        $wpdb->update(
            $table_name,
            array('status' => $status),
            array('id' => $entry_id)
        );
    }

    if (isset($_POST['delete_entry'])) {
        $entry_id = intval($_POST['entry_id']);
        $wpdb->delete($table_name, array('id' => $entry_id));
    }

    if (isset($_POST['save_email'])) {
        $email = sanitize_email($_POST['notification_email']);
        update_option('urenregistratie_notification_email', $email);
    }

    if (isset($_POST['koppel_kandidaat'])) {
        $opdrachtgever_id = intval($_POST['opdrachtgever_id']);
        $kandidaat_id = intval($_POST['kandidaat_id']);
        update_user_meta($kandidaat_id, 'opdrachtgever_id', $opdrachtgever_id);
    }

    if (isset($_POST['ontkoppel_kandidaat'])) {
        $kandidaat_id = intval($_POST['kandidaat_id']);
        delete_user_meta($kandidaat_id, 'opdrachtgever_id');
    }

    $order_by = isset($_GET['order_by']) ? sanitize_text_field($_GET['order_by']) : 'weeknummer';
    $order = isset($_GET['order']) ? sanitize_text_field($_GET['order']) : 'ASC';

    $valid_order_by = array('weeknummer');
    $valid_order = array('ASC', 'DESC');
    if (!in_array($order_by, $valid_order_by)) {
        $order_by = 'weeknummer';
    }
    if (!in_array($order, $valid_order)) {
        $order = 'ASC';
    }

    $next_order = ($order === 'ASC') ? 'DESC' : 'ASC';

    // Paginering
    $items_per_page = isset($_GET['items_per_page']) ? intval($_GET['items_per_page']) : 10;
    $current_page = isset($_GET['paged']) ? intval($_GET['paged']) : 1;
    $offset = ($current_page - 1) * $items_per_page;

    $total_items = $wpdb->get_var("SELECT COUNT(*) FROM $table_name");
    $total_pages = ceil($total_items / $items_per_page);

    $results = $wpdb->get_results($wpdb->prepare("SELECT * FROM $table_name ORDER BY $order_by $order LIMIT %d OFFSET %d", $items_per_page, $offset), ARRAY_A);

    echo '<div class="wrap">';
    echo '<h1>Urenoverzicht</h1>';

    // Selectie voor items per pagina
    echo '<form method="get">';
    echo '<input type="hidden" name="page" value="uren-overzicht">';
    echo '<label for="items_per_page">Items per pagina:</label>';
    echo '<select name="items_per_page" id="items_per_page" onchange="this.form.submit()">';
    $options = array(10, 20, 50, 100);
    foreach ($options as $option) {
        $selected = ($option == $items_per_page) ? 'selected' : '';
        echo "<option value=\"$option\" $selected>$option</option>";
    }
    echo '</select>';
    echo '</form>';

    if (!empty($results)) {
        echo '<table class="wp-list-table widefat fixed striped">';
        echo '<thead>
        <tr>
            <th>Naam</th>
            <th><a href="' . esc_url(add_query_arg(array('order_by' => 'weeknummer', 'order' => $next_order))) . '" style="text-decoration: none; color: inherit;">Weeknummer ' . ($order_by === 'weeknummer' ? ($order === 'ASC' ? '▲' : '▼') : '') . '</a></th>
            <th>Jaar</th>
            <th>Ingediende uren</th>
            <th>Totaal uren</th>
            <th>Status</th>
            <th>Gekoppelde Opdrachtgever</th>
            <th>Datum Aangevraagd</th>
            <th>Acties</th>
        </tr>
      </thead>';
        echo '<tbody>';

        $unique_weken = array();

        foreach ($results as $row) {
            $entry_id = $row['id'];
            $user_id = $row['user_id'];
            $weeknummer = $row['weeknummer'];
            $jaar = $row['jaar'];
            $uren = json_decode($row['uren'], true);
            $status = $row['status'] ?: 'in afwachting';
            $datum_aangevraagd = date('d-m-Y', strtotime($row['date']));

            if (in_array($weeknummer, $unique_weken)) {
                continue;
            }

            $unique_weken[] = $weeknummer;
            $user_info = get_userdata($user_id);

            if ($user_info) {
                $naam = $user_info->display_name;

                $opdrachtgever_id = get_user_meta($user_id, 'opdrachtgever_id', true);
                $opdrachtgever_name = 'Nog geen opdrachtgever gekoppeld';
                if ($opdrachtgever_id) {
                    $opdrachtgever_info = get_userdata($opdrachtgever_id);
                    if ($opdrachtgever_info) {
                        $opdrachtgever_name = $opdrachtgever_info->display_name;
                    }
                }

                if (is_string($naam) && is_string($weeknummer) && is_array($uren)) {
                    $ingediende_uren = '';
                    $totaal_uren = 0;
                    foreach ($uren as $dag => $uren_per_dag) {
                        $ingediende_uren .= ucfirst($dag) . ': ' . esc_html($uren_per_dag) . ' uur<br>';
                        $totaal_uren += (float)$uren_per_dag;
                    }

                    echo '<tr>';
                    echo '<td>' . esc_html($naam) . '</td>';
                    echo '<td>' . esc_html($weeknummer) . '</td>';
                    echo '<td>' . esc_html($jaar) . '</td>';
                    echo '<td>' . $ingediende_uren . '</td>';
                    echo '<td>' . esc_html($totaal_uren) . '</td>';
                    echo '<td>' . esc_html($status) . '</td>';
                    echo '<td>' . esc_html($opdrachtgever_name) . '</td>';
                    echo '<td>' . esc_html($datum_aangevraagd) . '</td>';
                    echo '<td>
                        <form method="post" style="display:inline;">
                            <input type="hidden" name="entry_id" value="' . esc_attr($entry_id) . '">
                            <input type="hidden" name="status" value="goedgekeurd">
                            <button type="submit" name="update_status" class="button button-primary">Goedkeuren</button>
                        </form>
                        <form method="post" style="display:inline;">
                            <input type="hidden" name="entry_id" value="' . esc_attr($entry_id) . '">
                            <input type="hidden" name="status" value="afgekeurd">
                            <button type="submit" name="update_status" class="button button-secondary">Afkeuren</button>
                        </form>
                        <form method="post" style="display:inline;">
                            <input type="hidden" name="entry_id" value="' . esc_attr($entry_id) . '">
                            <button type="submit" name="delete_entry" class="button button-danger" onclick="return confirm(\'Weet je zeker dat je deze uren wilt verwijderen?\')">Verwijderen</button>
                        </form>
                    </td>';
                    echo '</tr>';
                } else {
                    echo '<tr><td colspan="9">Ongeldige gegevens gevonden.</td></tr>';
                }
            }
        }
        echo '</tbody>';
        echo '</table>';

        // Paginering
        $pagination_args = array(
            'base' => add_query_arg('paged', '%#%'),
            'format' => '',
            'total' => $total_pages,
            'current' => $current_page,
            'prev_text' => __('&laquo; Vorige'),
            'next_text' => __('Volgende &raquo;'),
        );

        echo '<div class="tablenav"><div class="tablenav-pages">' . paginate_links($pagination_args) . '</div></div>';
    } else {
        echo '<p>Geen uren gevonden.</p>';
    }

    echo '<h2>Koppel opdrachtgevers</h2>';
    echo '<table class="wp-list-table widefat fixed striped">';
    echo '<thead>
    <tr>
        <th>Opdrachtgever</th>
        <th>Kandidaten</th>
        <th>Acties</th>
    </tr>
    </thead>';
    echo '<tbody>';

    $opdrachtgever_users = get_users(array('role' => 'opdrachtgever'));

    $kandidaat_users = get_users(array('role' => 'kandidaat'));

    foreach ($opdrachtgever_users as $opdrachtgever) {
        $opdrachtgever_id = $opdrachtgever->ID;
        $opdrachtgever_name = $opdrachtgever->display_name;
        $gekoppelde_kandidaten = get_users(array(
            'role' => 'kandidaat',
            'meta_query' => array(
                array(
                    'key' => 'opdrachtgever_id',
                    'value' => $opdrachtgever_id,
                    'compare' => '='
                )
            )
        ));

        echo '<tr>';
        echo '<td>' . esc_html($opdrachtgever_name) . '</td>';
        echo '<td>';
        if (!empty($gekoppelde_kandidaten)) {
            foreach ($gekoppelde_kandidaten as $kandidaat) {
                echo esc_html($kandidaat->display_name) . ' ';
                echo '<form method="post" style="display:inline;">
                    <input type="hidden" name="opdrachtgever_id" value="' . esc_attr($opdrachtgever_id) . '">
                    <input type="hidden" name="kandidaat_id" value="' . esc_attr($kandidaat->ID) . '">
                    <button type="submit" name="ontkoppel_kandidaat" class="button button-secondary">Ontkoppelen</button>
                </form><br>';
            }
        } else {
            echo 'Geen kandidaten gekoppeld.';
        }
        echo '</td>';
        echo '<td>
            <form method="post">
                <input type="hidden" name="opdrachtgever_id" value="' . esc_attr($opdrachtgever_id) . '">
                <select name="kandidaat_id">';
        foreach ($kandidaat_users as $kandidaat) {
            echo '<option value="' . esc_attr($kandidaat->ID) . '">' . esc_html($kandidaat->display_name) . '</option>';
        }
        echo '</select>
                <button type="submit" name="koppel_kandidaat" class="button button-primary">Koppelen</button>
            </form>
        </td>';
        echo '</tr>';
    }

    echo '</tbody>';
    echo '</table>';

    echo '</div>';

    echo '<h2>Instellingen</h2>';
    echo '<form method="post">';
    $saved_email = get_option('urenregistratie_notification_email', '');
    echo '<table class="form-table">';
    echo '<tr>';
    echo '<th scope="row"><label for="notification_email">Notificatie E-mailadres</label></th>';
    echo '<td><input type="email" name="notification_email" value="' . esc_attr($saved_email) . '" class="regular-text"></td>';
    echo '</tr>';
    echo '</table>';
    echo '<p class="submit"><button type="submit" name="save_email" class="button button-primary">Opslaan</button></p>';
    echo '</form>';
}
