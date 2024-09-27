<?php

function urenregistratie_admin_menu()
{
    add_menu_page(
        'Uren Overzicht',
        'Uren Overzicht',
        'manage_options',
        'uren-overzicht',
        'urenregistratie_admin_page'
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


    $args = array(
        'post_type' => 'uren',
        'posts_per_page' => -1,
        'post_status' => 'publish'
    );
    $uren_query = new WP_Query($args);


    echo '<div class="wrap">';
    echo '<h1>Uren Overzicht</h1>';

    if ($uren_query->have_posts()) {
        echo '<table class="wp-list-table widefat fixed striped">';
        echo '<thead>
        <tr>
            <th>Naam</th>
            <th>E-mailadres</th>
            <th>Weeknummer</th>
            <th>Ingediende uren</th>
            <th>Totaal uren</th>
            <th>Status</th>
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


            if (in_array($weeknummer, $unique_weken)) {
                continue;
            }

            $unique_weken[] = $weeknummer;

            $user_info = get_userdata($user_id);

            if ($user_info) {
                $naam = $user_info->display_name;
                $email = $user_info->user_email;

                if (is_string($naam) && is_string($email) && is_string($weeknummer) && is_array($uren)) {
                    $ingediende_uren = '';
                    $totaal_uren = 0;
                    foreach ($uren as $dag => $uren_per_dag) {
                        $ingediende_uren .= ucfirst($dag) . ': ' . esc_html($uren_per_dag) . ' uur<br>';
                        $totaal_uren += (int)$uren_per_dag;
                    }

                    echo '<tr>';
                    echo '<td>' . esc_html($naam) . '</td>';
                    echo '<td>' . esc_html($email) . '</td>';
                    echo '<td>' . esc_html($weeknummer) . '</td>';
                    echo '<td>' . $ingediende_uren . '</td>';
                    echo '<td>' . esc_html($totaal_uren) . '</td>';
                    echo '<td>' . esc_html($status) . '</td>';
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
                            <button type="submit" name="delete_post" class="button button-danger" onclick="return confirm(\'Weet je zeker dat je deze uren reeks wilt verwijderen?\')">Verwijderen</button>
                        </form>
                    </td>';
                    echo '</tr>';
                } else {
                    echo '<tr><td colspan="7">Ongeldige gegevens gevonden.</td></tr>';
                }
            }
        }
        echo '</tbody>';
        echo '</table>';
    } else {
        echo '<p>Geen uren gevonden.</p>';
    }
    echo '</div>';
    wp_reset_postdata();
}
