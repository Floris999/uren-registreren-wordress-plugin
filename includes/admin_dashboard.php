<?php

function urenregistratie_admin_menu()
{
    add_menu_page(
        'Uren Overzicht',
        'Uren Overzicht',
        'manage_options',
        'uren-overzicht',
        'urenregistratie_admin_page',
        'dashicons-clock',
    );
}
add_action('admin_menu', 'urenregistratie_admin_menu');

function urenregistratie_admin_page()
{
    if (isset($_POST['update_status'])) {
        $post_id = intval($_POST['post_id']);
        $status = sanitize_text_field($_POST['status']);
        update_post_meta($post_id, 'status', $status);
    }

    if (isset($_POST['delete_post'])) {
        $post_id = intval($_POST['post_id']);
        wp_delete_post($post_id, true);
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

    $args = array(
        'post_type' => 'uren',
        'posts_per_page' => -1,
        'post_status' => 'publish'
    );
    $uren_query = new WP_Query($args);

    echo '<div class="wrap">';
    echo '<h1>Urenoverzicht</h1>';

    if ($uren_query->have_posts()) {
        echo '<table class="wp-list-table widefat fixed striped">';
        echo '<thead>
        <tr>
            <th>Naam</th>
            <th>Weeknummer</th>
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

        while ($uren_query->have_posts()) {
            $uren_query->the_post();
            $post_id = get_the_ID();
            $user_id = get_post_meta($post_id, 'user_id', true);
            $weeknummer = get_post_meta($post_id, 'weeknummer', true);
            $uren = get_post_meta($post_id, 'uren', true);
            $status = get_post_meta($post_id, 'status', true) ?: 'in afwachting';
            $datum_aangevraagd = get_the_date('d-m-Y', $post_id);

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
                        $totaal_uren += (int)$uren_per_dag;
                    }

                    echo '<tr>';
                    echo '<td>' . esc_html($naam) . '</td>';
                    echo '<td>' . esc_html($weeknummer) . '</td>';
                    echo '<td>' . $ingediende_uren . '</td>';
                    echo '<td>' . esc_html($totaal_uren) . '</td>';
                    echo '<td>' . esc_html($status) . '</td>';
                    echo '<td>' . esc_html($opdrachtgever_name) . '</td>';
                    echo '<td>' . esc_html($datum_aangevraagd) . '</td>';
                    echo '<td>
                        <form method="post" style="display:inline;">
                            <input type="hidden" name="post_id" value="' . esc_attr($post_id) . '">
                            <input type="hidden" name="status" value="goedgekeurd">
                            <button type="submit" name="update_status" class="button button-primary">Goedkeuren</button>
                        </form>
                        <form method="post" style="display:inline;">
                            <input type="hidden" name="post_id" value="' . esc_attr($post_id) . '">
                            <input type="hidden" name="status" value="afgekeurd">
                            <button type="submit" name="update_status" class="button button-secondary">Afkeuren</button>
                        </form>
                        <form method="post" style="display:inline;">
                            <input type="hidden" name="post_id" value="' . esc_attr($post_id) . '">
                            <button type="submit" name="delete_post" class="button button-danger" onclick="return confirm(\'Weet je zeker dat je deze uren wilt verwijderen?\')">Verwijderen</button>
                        </form>
                    </td>';
                    echo '</tr>';
                } else {
                    echo '<tr><td colspan="8">Ongeldige gegevens gevonden.</td></tr>';
                }
            }
        }
        echo '</tbody>';
        echo '</table>';
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
    wp_reset_postdata();

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
