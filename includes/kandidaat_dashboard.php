<?php

function hours_registration_user_form()
{
    if (!is_user_logged_in()) {
        return '<p>Je moet ingelogd zijn om je uren in te dienen.</p>';
    }

    $current_user = wp_get_current_user();
    $user_name = $current_user->display_name;
    $user_email = $current_user->user_email;

    $is_edit_mode = isset($_GET['edit']) && $_GET['edit'] === 'true';
    $weeknummer = isset($_GET['weeknummer']) ? intval($_GET['weeknummer']) : 0;
    $kandidaat_id = isset($_GET['kandidaat_id']) ? intval($_GET['kandidaat_id']) : $current_user->ID;

    if (!in_array('kandidaat', $current_user->roles) && !in_array('administrator', $current_user->roles) && !in_array('opdrachtgever', $current_user->roles)) {
        return '<p>Je hebt geen toestemming om deze pagina te bekijken.</p>';
    }

    if (isset($_POST['uren_submit'])) {
        if ($is_edit_mode) {
            process_opdrachtgever_submission($kandidaat_id, $user_email);
        } else {
            process_hours_submission($current_user->ID, $user_email);
        }
    }

    if ($is_edit_mode && $weeknummer && $kandidaat_id) {
        $ingediende_uren = get_ingediende_uren($kandidaat_id, $weeknummer);
    }

    $ingediende_weken = get_submitted_weeks($current_user->ID);

    ob_start();
?>
    <div>
        <div class="px-4 sm:px-0">
            <h3 class="text-base font-semibold leading-7 text-gray-900"><?php echo $is_edit_mode ? 'Uren aanpassen' : 'Urenregistratie'; ?></h3>
            <p class="mt-1 max-w-2xl text-sm leading-6 text-gray-500"><?php echo $is_edit_mode ? 'Pas de gewerkte uren aan voor de week.' : 'Vul je gewerkte uren in voor de week.'; ?></p>
        </div>
        <div class="mt-6 border-t border-gray-100">
            <dl class="divide-y divide-gray-100">
                <div class="px-4 py-6 sm:grid sm:grid-cols-3 sm:gap-4 sm:px-0">
                    <dt class="text-sm font-medium leading-6 text-gray-900">Naam</dt>
                    <dd class="mt-1 text-sm leading-6 text-gray-700 sm:col-span-2 sm:mt-0"><?php echo esc_html($user_name); ?></dd>
                </div>
                <div class="px-4 py-6 sm:grid sm:grid-cols-3 sm:gap-4 sm:px-0">
                    <dt class="text-sm font-medium leading-6 text-gray-900">E-mailadres</dt>
                    <dd class="mt-1 text-sm leading-6 text-gray-700 sm:col-span-2 sm:mt-0"><?php echo esc_html($user_email); ?></dd>
                </div>
                <?php if (!empty($ingediende_weken)): ?>
                    <div class="px-4 py-6 sm:grid sm:grid-cols-3 sm:gap-4 sm:px-0">
                        <dt class="text-sm font-medium leading-6 text-gray-900">Ingediende weken</dt>
                        <dd class="mt-1 text-sm leading-6 text-gray-700 sm:col-span-2 sm:mt-0">
                            <ul class="list-disc list-inside">
                                <?php foreach ($ingediende_weken as $week): ?>
                                    <li>Week <?php echo esc_html($week['weeknummer']); ?>: <?php echo esc_html($week['status']); ?></li>
                                <?php endforeach; ?>
                            </ul>
                        </dd>
                    </div>
                <?php endif; ?>
                <form method="post" action="" class="mt-4">
                    <input type="hidden" name="old_weeknummer" value="<?php echo esc_attr($weeknummer); ?>">
                    <input type="hidden" name="kandidaat_id" value="<?php echo esc_attr($kandidaat_id); ?>">
                    <div class="px-4 py-6 sm:grid sm:grid-cols-3 sm:gap-4 sm:px-0">
                        <dt class="text-sm font-medium leading-6 text-gray-900">
                            <label for="weeknummer">Weeknummer:</label>
                        </dt>
                        <dd class="mt-1 text-sm leading-6 text-gray-700 sm:col-span-2 sm:mt-0">
                            <input type="number" class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline" id="weeknummer" name="weeknummer" required min="1" max="53" oninput="updateWeekDates()" value="<?php echo esc_attr($is_edit_mode ? $weeknummer : ''); ?>">
                            <div id="week-dates" class="mt-2 text-sm text-gray-700"></div>
                        </dd>
                    </div>

                    <?php
                    $dagen = ['maandag', 'dinsdag', 'woensdag', 'donderdag', 'vrijdag', 'zaterdag', 'zondag'];
                    foreach ($dagen as $dag): ?>
                        <div class="px-4 py-6 sm:grid sm:grid-cols-3 sm:gap-4 sm:px-0">
                            <dt class="text-sm font-medium leading-6 text-gray-900">
                                <label for="uren_<?php echo $dag; ?>">Uren <?php echo ucfirst($dag); ?>:</label>
                            </dt>
                            <dd class="mt-1 text-sm leading-6 text-gray-700 sm:col-span-2 sm:mt-0">
                                <input type="number" class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline" id="uren_<?php echo $dag; ?>" name="uren_<?php echo $dag; ?>" min="0" max="8" value="<?php echo esc_attr($is_edit_mode ? $ingediende_uren[$dag] : ''); ?>">
                            </dd>
                        </div>
                    <?php endforeach; ?>

                    <div class="px-4 py-6 sm:grid sm:grid-cols-3 sm:gap-4 sm:px-0">
                        <dt class="text-sm font-medium leading-6 text-gray-900"></dt>
                        <dd class="mt-1 text-sm leading-6 text-gray-700 sm:col-span-2 sm:mt-0">
                            <button type="submit" name="uren_submit" class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded focus:outline-none focus:shadow-outline"><?php echo $is_edit_mode ? 'Uren aanpassen' : 'Uren registreren'; ?></button>
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

function process_hours_submission($user_id, $user_email)
{
    global $wpdb;
    $table_name = $wpdb->prefix . 'uren';

    $weeknummer = sanitize_text_field($_POST['weeknummer']);
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
        "SELECT COUNT(*) FROM $table_name WHERE user_id = %d AND weeknummer = %d",
        $user_id,
        $weeknummer
    ));

    if ($existing_entry > 0) {
        return 'Je hebt al uren ingediend voor week ' . esc_html($weeknummer) . '.';
    }

    $wpdb->insert(
        $table_name,
        array(
            'user_id' => $user_id,
            'weeknummer' => $weeknummer,
            'uren' => json_encode($uren),
            'status' => 'in afwachting'
        )
    );

    $record_id = $wpdb->insert_id;

    send_hours_submission_email_custom_table($record_id);

    send_candidate_notification_email($record_id);

    wp_redirect(home_url());
    exit;
}

function process_opdrachtgever_submission($kandidaat_id, $user_email)
{
    global $wpdb;
    $table_name = $wpdb->prefix . 'uren';

    $weeknummer = sanitize_text_field($_POST['weeknummer']);
    $old_weeknummer = sanitize_text_field($_POST['old_weeknummer']);
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
            'uren' => json_encode($uren),
            'status' => 'goedgekeurd'
        ),
        array(
            'user_id' => $kandidaat_id,
            'weeknummer' => $old_weeknummer
        )
    );

    wp_redirect(home_url('/opdrachtgever'));
    exit;
}

function get_submitted_weeks($user_id)
{
    global $wpdb;
    $table_name = $wpdb->prefix . 'uren';

    $results = $wpdb->get_results($wpdb->prepare(
        "SELECT weeknummer, status FROM $table_name WHERE user_id = %d",
        $user_id
    ), ARRAY_A);

    return $results;
}

function get_ingediende_uren($kandidaat_id, $weeknummer)
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
