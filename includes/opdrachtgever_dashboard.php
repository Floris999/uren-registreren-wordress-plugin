<?php

if (!function_exists('get_start_and_end_date')) {
    function get_start_and_end_date($week, $year)
    {
        $dto = new DateTime();
        $dto->setISODate($year, $week);
        $start_date = $dto->format('d-m-Y');
        $dto->modify('+6 days');
        $end_date = $dto->format('d-m-Y');
        return array($start_date, $end_date);
    }
}

function hours_registration_client_dashboard()
{
    if (!is_user_logged_in()) {
        return '<p>Je moet ingelogd zijn om dit overzicht te bekijken.</p>';
    }

    $current_user = wp_get_current_user();

    if (!in_array('opdrachtgever', $current_user->roles) && !in_array('administrator', $current_user->roles)) {
        return '<p>Je hebt geen toestemming om deze pagina te bekijken.</p>';
    }

    if (!isset($_GET['token']) || !get_transient('opdrachtgever_token_' . $current_user->ID) || $_GET['token'] !== get_transient('opdrachtgever_token_' . $current_user->ID)) {
        return '<p>Oeps! Kies een gekoppelde kandidaat, om deze pagina te kunnen zien.</p>';
    }

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

        header('Location: /opdrachtgever/');
        exit;
    }

    $kandidaat_id = isset($_GET['kandidaat_id']) ? intval($_GET['kandidaat_id']) : 0;

    if ($kandidaat_id) {
        $kandidaat_users = get_users(array(
            'role' => 'kandidaat',
            'include' => array($kandidaat_id),
            'meta_query' => array(
                array(
                    'key' => 'opdrachtgever_id',
                    'value' => $current_user->ID,
                    'compare' => '='
                )
            )
        ));
    } else {
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
    }

    $kandidaat_user_ids = wp_list_pluck($kandidaat_users, 'ID');

    if (empty($kandidaat_user_ids)) {
        return '<p>Er zijn nog geen kandidaten toegevoegd.</p>';
    }

    $order_by = isset($_GET['order_by']) ? sanitize_text_field($_GET['order_by']) : 'weeknummer';
    $order = isset($_GET['order']) ? sanitize_text_field($_GET['order']) : 'ASC';

    $valid_order_by = array('weeknummer', 'naam');
    $valid_order = array('ASC', 'DESC');
    if (!in_array($order_by, $valid_order_by)) {
        $order_by = 'weeknummer';
    }
    if (!in_array($order, $valid_order)) {
        $order = 'ASC';
    }

    $next_order = ($order === 'ASC') ? 'DESC' : 'ASC';

    $results = $wpdb->get_results(
        "SELECT * FROM $table_name WHERE user_id IN (" . implode(',', array_map('intval', $kandidaat_user_ids)) . ") ORDER BY $order_by $order",
        ARRAY_A
    );

    ob_start();
?>
    <div class="flex flex-col mb-1 sm:mb-0">
        <h1 class="text-2xl leading-tight">Hallo <?php echo esc_html($current_user->display_name); ?></h1>
        <p>De volgende uren zijn door jouw talenten geregistreerd.</p>
    </div>
    <div class="overflow-x-auto">
        <table class="min-w-full table-auto">
            <thead>
                <tr>
                    <th class="px-6 py-4 border-b-2 border-gray-200 bg-gray-100 text-left text-xs font-semibold text-gray-600 uppercase">
                        <a href="<?php echo esc_url(add_query_arg(array('order_by' => 'naam', 'order' => $next_order))); ?>">
                            Naam <?php if ($order_by === 'naam') echo $order === 'ASC' ? '▲' : '▼'; ?>
                        </a>
                    </th>
                    <th class="px-6 py-4 border-b-2 border-gray-200 bg-gray-100 text-left text-xs font-semibold text-gray-600 uppercase">
                        <a href="<?php echo esc_url(add_query_arg(array('order_by' => 'weeknummer', 'order' => $next_order))); ?>" class="inline-flex items-center">
                            <span>Weeknummer</span>
                            <span class="ml-1">
                                <?php if ($order_by === 'weeknummer') echo $order === 'ASC' ? '▲' : '▼'; ?>
                            </span>
                        </a>
                    </th>
                    <th class="px-6 py-4 border-b-2 border-gray-200 bg-gray-100 text-left text-xs font-semibold text-gray-600 uppercase">Weekdatum</th>
                    <th class="px-6 py-4 border-b-2 border-gray-200 bg-gray-100 text-left text-xs font-semibold text-gray-600 uppercase">Ingediende uren</th>
                    <th class="px-6 py-4 border-b-2 border-gray-200 bg-gray-100 text-left text-xs font-semibold text-gray-600 uppercase">Totaal uren</th>
                    <th class="px-6 py-4 border-b-2 border-gray-200 bg-gray-100 text-left text-xs font-semibold text-gray-600 uppercase">Status</th>
                    <th class="px-6 py-4 border-b-2 border-gray-200 bg-gray-100 text-left text-xs font-semibold text-gray-600 uppercase">Datum Aangevraagd</th>
                    <th class="px-6 py-4 border-b-2 border-gray-200 bg-gray-100 text-left text-xs font-semibold text-gray-600 uppercase">Acties</th>
                </tr>
            </thead>
            <tbody>
                <?php
                if (!empty($results)) {
                    foreach ($results as $row) {
                        $entry_id = $row['id'];
                        $user_id = $row['user_id'];
                        $weeknummer = $row['weeknummer'];
                        $uren = json_decode($row['uren'], true);
                        $status = $row['status'] ?: 'in afwachting';
                        $datum_aangevraagd = isset($row['date']) ? date('d-m-Y', strtotime($row['date'])) : 'Onbekend';

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
                                list($start_date, $end_date) = get_start_and_end_date($weeknummer, date('Y'));
                ?>
                                <tr>
                                    <td class="px-2 py-4 text-center text-sm whitespace-nowrap"><?php echo esc_html($naam); ?></td>
                                    <td class="px-2 py-4 text-center text-sm whitespace-nowrap"><?php echo esc_html($weeknummer); ?></td>
                                    <td class="px-2 py-4 text-center text-sm whitespace-nowrap">
                                        <?php echo esc_html($start_date); ?><br>
                                        <?php echo esc_html($end_date); ?>
                                    </td>
                                    <td class="px-2 py-4 text-center text-sm whitespace-nowrap">
                                        <?php echo $ingediende_uren; ?>
                                    </td>
                                    <td class="px-2 py-4 text-center text-sm whitespace-nowrap"><?php echo esc_html($totaal_uren); ?></td>
                                    <td class="px-2 py-4 text-center text-sm whitespace-nowrap"><?php echo esc_html($status); ?></td>
                                    <td class="px-2 py-4 text-center text-sm whitespace-nowrap"><?php echo esc_html($datum_aangevraagd); ?></td>
                                    <td class="px-2 py-4 text-center text-sm whitespace-nowrap">
                                        <div class="flex space-x-2 justify-center">
                                            <?php if ($status !== 'goedgekeurd'): ?>
                                                <form method="post" style="display:inline;">
                                                    <input type="hidden" name="entry_id" value="<?php echo esc_attr($entry_id); ?>">
                                                    <input type="hidden" name="status" value="goedgekeurd">
                                                    <button type="submit" name="update_status" class="bg-green-500 hover:bg-green-700 text-white font-bold py-2 px-4 rounded" onclick="alert('Bedankt voor het accorderen!');">Goedkeuren</button>
                                                </form>
                                                <a href="<?php echo esc_url(add_query_arg(array('weeknummer' => $weeknummer, 'kandidaat_id' => $user_id, 'edit' => 'true'), home_url('/kandidaat'))); ?>" class="bg-yellow-500 hover:bg-yellow-700 text-white font-bold py-2 px-4 rounded focus:outline-none focus:shadow-outline">Aanpassen</a>
                                            <?php endif; ?>
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
                        <td colspan="8" class="px-6 py-4 border-b border-gray-200 bg-white text-sm">Geen uren gevonden.</td>
                    </tr>
                <?php
                }
                ?>
            </tbody>
        </table>
    </div>

<?php
    wp_reset_postdata();

    return ob_get_clean();
}

add_shortcode('opdrachtgever_dashboard', 'hours_registration_client_dashboard');
