<?php

function urenregistratie_opdrachtgever_dashboard()
{
    if (!is_user_logged_in()) {
        return '<p>Je moet ingelogd zijn om dit overzicht te bekijken.</p>';
    }

    $current_user = wp_get_current_user();

    if (!in_array('opdrachtgever', $current_user->roles) && !in_array('administrator', $current_user->roles)) {
        return '<p>Je hebt geen toestemming om deze pagina te bekijken.</p>';
    }

    if (isset($_POST['update_status'])) {
        $post_id = intval($_POST['post_id']);
        $status = sanitize_text_field($_POST['status']);
        update_post_meta($post_id, 'status', $status);

        // Redirect to prevent form resubmission
        header('Location: ' . $_SERVER['REQUEST_URI']);
        exit;
    }

    // Haal alle 'Kandidaat' gebruikers op die aan de ingelogde 'Opdrachtgever' zijn gekoppeld
    $kandidaat_users = get_users(array(
        'role' => 'kandidaat',
        'meta_query' => array(
            array(
                'key' => 'opdrachtgever_id',
                'value' => $current_user->ID,
                'compare' => '='
            )
        )
    ));
    $kandidaat_user_ids = wp_list_pluck($kandidaat_users, 'ID');

    if (empty($kandidaat_user_ids)) {
        return '<p>Er zijn nog geen kandidaten toegevoegd.</p>';
    }

    // Query om alle 'uren' posts op te halen die behoren tot de gekoppelde 'Kandidaat' gebruikers
    $args = array(
        'post_type' => 'uren',
        'posts_per_page' => -1,
        'post_status' => 'publish',
        'meta_query' => array(
            array(
                'key' => 'user_id',
                'value' => $kandidaat_user_ids,
                'compare' => 'IN'
            )
        )
    );
    $uren_query = new WP_Query($args);

    ob_start();
?>
    <div class="flex flex-col mb-1 sm:mb-0">
        <h1 class="text-2xl leading-tight">Hallo <?php echo esc_html($current_user->display_name); ?></h1>
        <p>De volgende uren zijn door jouw kandidaten geregistreerd.</p>
    </div>
    <table>
        <thead>
            <tr>
                <th class="px-6 py-4 border-b-2 border-gray-200 bg-gray-100 text-left text-xs font-semibold text-gray-600 uppercase">Naam</th>
                <th class="px-6 py-4 border-b-2 border-gray-200 bg-gray-100 text-left text-xs font-semibold text-gray-600 uppercase">Weeknummer</th>
                <th class="px-6 py-4 border-b-2 border-gray-200 bg-gray-100 text-left text-xs font-semibold text-gray-600 uppercase">Ingediende uren</th>
                <th class="px-6 py-4 border-b-2 border-gray-200 bg-gray-100 text-left text-xs font-semibold text-gray-600 uppercase">Totaal uren</th>
                <th class="px-6 py-4 border-b-2 border-gray-200 bg-gray-100 text-left text-xs font-semibold text-gray-600 uppercase">Status</th>
                <th class="px-6 py-4 border-b-2 border-gray-200 bg-gray-100 text-left text-xs font-semibold text-gray-600 uppercase">Datum Aangevraagd</th>
                <th class="px-6 py-4 border-b-2 border-gray-200 bg-gray-100 text-left text-xs font-semibold text-gray-600 uppercase">Acties</th>
            </tr>
        </thead>
        <tbody>
            <?php
            if ($uren_query->have_posts()) {
                while ($uren_query->have_posts()) {
                    $uren_query->the_post();
                    $post_id = get_the_ID();
                    $user_id = get_post_meta($post_id, 'user_id', true);
                    $weeknummer = get_post_meta($post_id, 'weeknummer', true);
                    $uren = get_post_meta($post_id, 'uren', true);
                    $status = get_post_meta($post_id, 'status', true) ?: 'in afwachting';
                    $datum_aangevraagd = get_the_date('d-m-Y', $post_id);

                    $user_info = get_userdata($user_id);
                    if ($user_info && in_array('kandidaat', $user_info->roles) && in_array($user_id, $kandidaat_user_ids)) {
                        $naam = $user_info->display_name;

                        if (is_string($naam) && is_string($weeknummer) && is_array($uren)) {
                            $ingediende_uren = '';
                            $totaal_uren = 0;
                            foreach ($uren as $dag => $uren_per_dag) {
                                $ingediende_uren .= ucfirst($dag) . ': ' . esc_html($uren_per_dag) . ' uur<br>';
                                $totaal_uren += (int)$uren_per_dag;
                            }
            ?>
                            <tr>
                                <td class="px-6 py-4 border-b border-gray-200 bg-white text-sm"><?php echo esc_html($naam); ?></td>
                                <td class="px-6 py-4 border-b border-gray-200 bg-white text-sm"><?php echo esc_html($weeknummer); ?></td>
                                <td class="px-6 py-4 border-b border-gray-200 bg-white text-sm"><?php echo $ingediende_uren; ?></td>
                                <td class="px-6 py-4 border-b border-gray-200 bg-white text-sm"><?php echo esc_html($totaal_uren); ?></td>
                                <td class="px-6 py-4 border-b border-gray-200 bg-white text-sm"><?php echo esc_html($status); ?></td>
                                <td class="px-6 py-4 border-b border-gray-200 bg-white text-sm"><?php echo esc_html($datum_aangevraagd); ?></td>
                                <td class="px-6 py-4 border-b border-gray-200 bg-white text-sm">
                                    <div class="flex space-x-2">
                                        <form method="post" style="display:inline;">
                                            <input type="hidden" name="post_id" value="<?php echo esc_attr($post_id); ?>">
                                            <input type="hidden" name="status" value="goedgekeurd">
                                            <button type="submit" name="update_status" class="bg-green-500 hover:bg-green-700 text-white font-bold py-2 px-4 rounded">Goedkeuren</button>
                                        </form>
                                        <form method="post" style="display:inline;">
                                            <input type="hidden" name="post_id" value="<?php echo esc_attr($post_id); ?>">
                                            <input type="hidden" name="status" value="afgekeurd">
                                            <button type="submit" name="update_status" class="bg-red-500 hover:bg-red-700 text-white font-bold py-2 px-4 rounded">Afkeuren</button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                <?php
                        }
                    }
                }
            } else {
                ?>
                <tr>
                    <td colspan="7" class="px-6 py-4 border-b border-gray-200 bg-white text-sm">Geen uren gevonden.</td>
                </tr>
            <?php
            }
            ?>
        </tbody>
    </table>
<?php
    wp_reset_postdata();

    return ob_get_clean();
}

add_shortcode('opdrachtgever_dashboard', 'urenregistratie_opdrachtgever_dashboard');
