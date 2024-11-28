<?php

function enqueue_datepicker_assets()
{
    wp_enqueue_style('jquery-ui-css', 'https://code.jquery.com/ui/1.12.1/themes/base/jquery-ui.css');
    wp_enqueue_style('datepicker-css', plugin_dir_url(__FILE__) . '../assets/datepicker.css');
    wp_enqueue_script('jquery-ui-datepicker');
    wp_enqueue_script('datepicker-js', plugin_dir_url(__FILE__) . '../assets/datepicker.js', array('jquery', 'jquery-ui-datepicker'), null, true);
    wp_enqueue_script('ajax-script', plugin_dir_url(__FILE__) . '../assets/ajax.js', array('jquery'), null, true);
    wp_localize_script('ajax-script', 'ajax_object', array('ajax_url' => admin_url('admin-ajax.php')));
}
add_action('wp_enqueue_scripts', 'enqueue_datepicker_assets');

function get_year_for_candidate($kandidaat_id, $weeknummer)
{
    global $wpdb;
    $table_name = $wpdb->prefix . 'uren';

    $result = $wpdb->get_var($wpdb->prepare(
        "SELECT jaar FROM $table_name WHERE user_id = %d AND weeknummer = %d",
        $kandidaat_id,
        $weeknummer
    ));

    return $result ? intval($result) : date('Y');
}

function hours_registration_user_form()
{
    if (!is_user_logged_in()) {
        return '<p>Je moet ingelogd zijn om je uren in te dienen.</p>';
    }

    $current_user = wp_get_current_user();
    $is_edit_mode = isset($_GET['edit']) && $_GET['edit'] === 'true';
    $weeknummer = isset($_GET['weeknummer']) ? intval($_GET['weeknummer']) : 0;
    $kandidaat_id = isset($_GET['kandidaat_id']) ? intval($_GET['kandidaat_id']) : $current_user->ID;

    if (!in_array('kandidaat', $current_user->roles) && !in_array('administrator', $current_user->roles) && !in_array('opdrachtgever', $current_user->roles)) {
        return '<p>Je hebt geen toestemming om deze pagina te bekijken.</p>';
    }

    if ($is_edit_mode && $kandidaat_id) {
        $kandidaat_user = get_userdata($kandidaat_id);
        $user_name = $kandidaat_user->display_name;
        $user_email = $kandidaat_user->user_email;
        $opdrachtgever_naam = get_client_name($kandidaat_id);
        $year = '';
        $ingediende_uren = get_submitted_hours($kandidaat_id, $weeknummer);
    } else {
        $user_name = $current_user->display_name;
        $user_email = $current_user->user_email;
        $opdrachtgever_naam = get_client_name($current_user->ID);
        $year = '';
        $ingediende_uren = array();
    }

    $ingediende_weken = get_submitted_weeks($kandidaat_id);

    ob_start();
?>
    <div>
        <div class="px-4 sm:px-0">
            <h3 class="text-base font-semibold leading-7 text-gray-900"><?php echo $is_edit_mode ? 'Uren aanpassen' : 'Urenregistratie'; ?></h3>
            <p class="mt-1 max-w-2xl text-sm leading-6 text-gray-500"><?php echo $is_edit_mode ? 'Pas de gewerkte uren aan voor de week.' : 'Vul je gewerkte uren in voor de week.'; ?></p>
        </div>
        <div class="mt-6 border-t border-gray-100">
            <dl class="divide-y divide-gray-100">
                <div class="px-4 py-2 sm:grid sm:grid-cols-3 sm:gap-4 sm:px-0">
                    <dt class="text-sm font-medium leading-6 text-gray-900">Naam</dt>
                    <dd class="mt-1 text-sm leading-6 text-gray-700 sm:col-span-2 sm:mt-0"><?php echo esc_html($user_name); ?></dd>
                </div>
                <div class="px-4 py-2 sm:grid sm:grid-cols-3 sm:gap-4 sm:px-0">
                    <dt class="text-sm font-medium leading-6 text-gray-900">E-mailadres</dt>
                    <dd class="mt-1 text-sm leading-6 text-gray-700 sm:col-span-2 sm:mt-0"><?php echo esc_html($user_email); ?></dd>
                </div>
                <div class="px-4 py-2 sm:grid sm:grid-cols-3 sm:gap-4 sm:px-0">
                    <dt class="text-sm font-medium leading-6 text-gray-900">Opdrachtgevernaam</dt>
                    <dd class="mt-1 text-sm leading-6 text-gray-700 sm:col-span-2 sm:mt-0"><?php echo esc_html($opdrachtgever_naam); ?></dd>
                </div>
                <?php if (!empty($ingediende_weken)): ?>
                    <div class="px-4 py-2 sm:grid sm:grid-cols-3 sm:gap-4 sm:px-0">
                        <dt class="text-sm font-medium leading-6 text-gray-900">Ingediende weken</dt>
                        <dd class="mt-1 text-sm leading-6 text-gray-700 sm:col-span-2 sm:mt-0">
                            <ul class="list-inside list-none">
                                <?php foreach ($ingediende_weken as $week): ?>
                                    <li>Week <?php echo esc_html($week['weeknummer']); ?>: <?php echo esc_html($week['status']); ?> (Totaal uren: <?php echo esc_html($week['totaal_uren']); ?>)</li>
                                <?php endforeach; ?>
                            </ul>
                        </dd>
                    </div>
                <?php endif; ?>
                <form id="urenregistratie-form" method="post" class="mt-4">
                    <input type="hidden" name="action" value="process_hours_submission">
                    <input type="hidden" name="role" value="<?php echo in_array('opdrachtgever', $current_user->roles) ? 'opdrachtgever' : 'kandidaat'; ?>">
                    <input type="hidden" name="old_weeknummer" value="<?php echo esc_attr($weeknummer); ?>">
                    <input type="hidden" name="kandidaat_id" value="<?php echo esc_attr($kandidaat_id); ?>">
                    <div class="px-4 py-2 sm:grid sm:grid-cols-3 sm:gap-4 sm:px-0">
                        <dt class="text-sm font-medium leading-6 text-gray-900">
                            <label for="weeknummer">Weeknummer:</label>
                        </dt>
                        <dd class="mt-1 text-sm leading-6 text-gray-700 sm:col-span-2 sm:mt-0">
                            <input type="text" id="weekpicker" name="weeknummer_display" class="border border-gray-300 rounded px-2 py-1 w-full" value="<?php echo esc_attr($is_edit_mode ? 'Week ' . $weeknummer : ''); ?>" readonly>
                            <input type="hidden" id="weekNumber" name="weekNumber" value="<?php echo esc_attr($weeknummer); ?>">
                            <input type="hidden" id="year" name="year" value="<?php echo esc_attr(get_year_for_candidate($kandidaat_id, $weeknummer)); ?>">
                            <div id="week-dates" class="mt-2 text-sm text-gray-700"></div>
                        </dd>
                    </div>

                    <?php
                    $dagen = ['maandag', 'dinsdag', 'woensdag', 'donderdag', 'vrijdag', 'zaterdag', 'zondag'];
                    foreach ($dagen as $dag): ?>
                        <div class="px-4 py-2 sm:grid sm:grid-cols-3 sm:gap-4 sm:px-0">
                            <dt class="text-sm font-medium leading-6 text-gray-900">
                                <label for="uren_<?php echo $dag; ?>">Uren <?php echo ucfirst($dag); ?>:</label>
                            </dt>
                            <dd class="mt-1 text-sm leading-6 text-gray-700 sm:col-span-2 sm:mt-0">
                                <input type="number" class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline" id="uren_<?php echo $dag; ?>" name="uren_<?php echo $dag; ?>" min="0" max="8" step="0.5" value="<?php echo esc_attr($is_edit_mode ? $ingediende_uren[$dag] : ''); ?>">
                            </dd>
                        </div>
                    <?php endforeach; ?>

                    <div class="px-4 py-2 sm:grid sm:grid-cols-3 sm:gap-4 sm:px-0">
                        <dt class="text-sm font-medium leading-6 text-gray-900"></dt>
                        <dd class="mt-1 text-sm leading-6 text-gray-700 sm:col-span-2 sm:mt-0">
                            <button type="submit" name="uren_submit" class="bg-black hover:bg-gray-800 text-white font-bold py-2 px-4 rounded focus:outline-none focus:shadow-outline"><?php echo $is_edit_mode ? 'Uren aanpassen' : 'Uren registreren'; ?></button>
                            <?php if ($is_edit_mode): ?>
                                <button type="button" onclick="history.back()" class="bg-black hover:bg-gray-800 text-white font-bold py-2 px-4 rounded focus:outline-none focus:shadow-outline">Terug</button>
                            <?php endif; ?>
                        </dd>
                    </div>
                </form>
            </dl>
        </div>
    </div>
<?php

    return ob_get_clean();
}

add_shortcode('urenregistratie_form', 'hours_registration_user_form');

function get_client_name($kandidaat_id)
{
    $opdrachtgever_id = get_user_meta($kandidaat_id, 'opdrachtgever_id', true);
    if ($opdrachtgever_id) {
        $opdrachtgever_info = get_userdata($opdrachtgever_id);
        if ($opdrachtgever_info) {
            return $opdrachtgever_info->display_name;
        }
    }
    return 'Geen opdrachtgever gevonden';
}

function process_hours_submission()
{
    global $wpdb;
    $table_name = $wpdb->prefix . 'uren';

    $user_id = get_current_user_id();
    $weeknummer = sanitize_text_field($_POST['weekNumber']);
    $year = sanitize_text_field($_POST['year']);
    $uren = array(
        'maandag' => sanitize_text_field($_POST['uren_maandag']),
        'dinsdag' => sanitize_text_field($_POST['uren_dinsdag']),
        'woensdag' => sanitize_text_field($_POST['uren_woensdag']),
        'donderdag' => sanitize_text_field($_POST['uren_donderdag']),
        'vrijdag' => sanitize_text_field($_POST['uren_vrijdag']),
        'zaterdag' => sanitize_text_field($_POST['uren_zaterdag']),
        'zondag' => sanitize_text_field($_POST['uren_zondag']),
    );

    $existing_entry = $wpdb->get_var($wpdb->prepare(
        "SELECT COUNT(*) FROM $table_name WHERE user_id = %d AND weeknummer = %d AND jaar = %d",
        $user_id,
        $weeknummer,
        $year
    ));

    if ($existing_entry > 0) {
        wp_send_json_error('Je hebt al uren ingediend voor week ' . esc_html($weeknummer) . '.');
    }

    $wpdb->insert(
        $table_name,
        array(
            'user_id' => $user_id,
            'weeknummer' => $weeknummer,
            'jaar' => $year,
            'uren' => json_encode($uren),
            'status' => 'in afwachting'
        )
    );

    $record_id = $wpdb->insert_id;

    send_hours_submission_email_custom_table($record_id);
    send_candidate_notification_email($record_id);

    wp_send_json_success('Bedankt voor het doorgeven!');
}

function process_opdrachtgever_submission()
{
    global $wpdb;
    $table_name = $wpdb->prefix . 'uren';

    $kandidaat_id = sanitize_text_field($_POST['kandidaat_id']);
    $weeknummer = sanitize_text_field($_POST['weekNumber']);
    $old_weeknummer = sanitize_text_field($_POST['old_weeknummer']);
    $year = sanitize_text_field($_POST['year']);
    $uren = array(
        'maandag' => sanitize_text_field($_POST['uren_maandag']),
        'dinsdag' => sanitize_text_field($_POST['uren_dinsdag']),
        'woensdag' => sanitize_text_field($_POST['uren_woensdag']),
        'donderdag' => sanitize_text_field($_POST['uren_donderdag']),
        'vrijdag' => sanitize_text_field($_POST['uren_vrijdag']),
        'zaterdag' => sanitize_text_field($_POST['uren_zaterdag']),
        'zondag' => sanitize_text_field($_POST['uren_zondag']),
    );

    $wpdb->update(
        $table_name,
        array(
            'weeknummer' => $weeknummer,
            'jaar' => $year,
            'uren' => json_encode($uren),
            'status' => 'goedgekeurd'
        ),
        array(
            'user_id' => $kandidaat_id,
            'weeknummer' => $old_weeknummer
        )
    );
    send_opdrachtgever_submission_email($kandidaat_id, $weeknummer, $year, $uren);
    wp_send_json_success(array('message' => 'Bedankt voor het accorderen!', 'redirect' => site_url('/opdrachtgever')));
}

function get_submitted_weeks($user_id)
{
    global $wpdb;
    $table_name = $wpdb->prefix . 'uren';

    $results = $wpdb->get_results($wpdb->prepare(
        "SELECT weeknummer, status, uren FROM $table_name WHERE user_id = %d",
        $user_id
    ), ARRAY_A);

    foreach ($results as &$result) {
        $uren = json_decode($result['uren'], true);
        $totaal_uren = array_sum($uren);
        $result['totaal_uren'] = $totaal_uren;
    }

    return $results;
}

function get_submitted_hours($kandidaat_id, $weeknummer)
{
    global $wpdb;
    $table_name = $wpdb->prefix . 'uren';

    $result = $wpdb->get_row($wpdb->prepare(
        "SELECT uren FROM $table_name WHERE user_id = %d AND weeknummer = %d",
        $kandidaat_id,
        $weeknummer
    ), ARRAY_A);

    return json_decode($result['uren'], true);
}

function handle_ajax_request()
{
    if ($_POST['role'] === 'opdrachtgever') {
        process_opdrachtgever_submission();
    } else {
        process_hours_submission();
    }
}

add_action('wp_ajax_handle_ajax_request', 'handle_ajax_request');
add_action('wp_ajax_nopriv_handle_ajax_request', 'handle_ajax_request');
