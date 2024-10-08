<?php

function urenregistratie_gebruikersformulier()
{
    if (!is_user_logged_in()) {
        return '<p>Je moet ingelogd zijn om je uren in te dienen.</p>';
    }

    $current_user = wp_get_current_user();
    $user_name = $current_user->display_name;
    $user_email = $current_user->user_email;


    if (!in_array('kandidaat', $current_user->roles) && !in_array('administrator', $current_user->roles)) {
        return '<p>Je hebt geen toestemming om deze pagina te bekijken.</p>';
    }

    if (isset($_POST['uren_submit'])) {
        urenregistratie_verwerk_inzending($current_user->ID, $user_email);
    }

    $ingediende_weken = urenregistratie_get_ingediende_weken($current_user->ID);

    ob_start();
?>
    <div>
        <div class="px-4 sm:px-0">
            <h3 class="text-base font-semibold leading-7 text-gray-900">Urenregistratie</h3>
            <p class="mt-1 max-w-2xl text-sm leading-6 text-gray-500">Vul je gewerkte uren in voor de week.</p>
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
                    <div class="px-4 py-6 sm:grid sm:grid-cols-3 sm:gap-4 sm:px-0">
                        <dt class="text-sm font-medium leading-6 text-gray-900">
                            <label for="weeknummer">Weeknummer:</label>
                        </dt>
                        <dd class="mt-1 text-sm leading-6 text-gray-700 sm:col-span-2 sm:mt-0">
                            <input type="number" class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline" id="weeknummer" name="weeknummer" required>
                        </dd>
                    </div>

                    <?php
                    $dagen = ['maandag', 'dinsdag', 'woensdag', 'donderdag', 'vrijdag', 'zaterdag', 'zondag'];
                    foreach ($dagen as $dag): ?>
                        <div class="px-4 py-6 sm:grid sm:grid-cols-3 sm:gap-4 sm:px-0">
                            <dt class="text-sm font-medium leading-6 text-gray-900">
                                <label for="uren_<?php echo $dag; ?>">Uren <?php echo $dag; ?>:</label>
                            </dt>
                            <dd class="mt-1 text-sm leading-6 text-gray-700 sm:col-span-2 sm:mt-0">
                                <input type="number" class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline" id="uren_<?php echo $dag; ?>" name="uren_<?php echo $dag; ?>" min="0" max="8">
                            </dd>
                        </div>
                    <?php endforeach; ?>

                    <div class="px-4 py-6 sm:grid sm:grid-cols-3 sm:gap-4 sm:px-0">
                        <dt class="text-sm font-medium leading-6 text-gray-900"></dt>
                        <dd class="mt-1 text-sm leading-6 text-gray-700 sm:col-span-2 sm:mt-0">
                            <button type="submit" name="uren_submit" class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded focus:outline-none focus:shadow-outline">Uren registreren</button>
                        </dd>
                    </div>
                </form>
            </dl>
        </div>
    </div>
<?php

    return ob_get_clean();
}

add_shortcode('urenregistratie_form', 'urenregistratie_gebruikersformulier');

function urenregistratie_verwerk_inzending($user_id, $user_email)
{
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

    $existing_posts = get_posts(array(
        'post_type' => 'uren',
        'meta_query' => array(
            array(
                'key' => 'user_id',
                'value' => $user_id,
                'compare' => '='
            ),
            array(
                'key' => 'weeknummer',
                'value' => $weeknummer,
                'compare' => '='
            )
        )
    ));

    if (!empty($existing_posts)) {
        return 'Je hebt al uren ingediend voor week ' . esc_html($weeknummer) . '.';
    }

    $uren_post = array(
        'post_title' => 'Uren Week ' . $weeknummer . ' - Gebruiker ' . $user_id,
        'post_content' => json_encode($uren),
        'post_status' => 'publish',
        'post_type' => 'uren',
        'meta_input' => array(
            'user_id' => $user_id,
            'weeknummer' => $weeknummer,
            'uren' => $uren,
            'status' => 'in afwachting',
            'kandidaat_email' => $user_email,
        ),
    );

    wp_insert_post($uren_post);
    header('Location: /');
}

function urenregistratie_get_ingediende_weken($user_id)
{
    $posts = get_posts(array(
        'post_type' => 'uren',
        'meta_query' => array(
            array(
                'key' => 'user_id',
                'value' => $user_id,
                'compare' => '='
            )
        ),
        'posts_per_page' => -1
    ));

    $weken = array();
    foreach ($posts as $post) {
        $weeknummer = get_post_meta($post->ID, 'weeknummer', true);
        $status = get_post_meta($post->ID, 'status', true) ?: 'in afwachting';
        if ($weeknummer && !in_array($weeknummer, array_column($weken, 'weeknummer'))) {
            $weken[] = array('weeknummer' => $weeknummer, 'status' => $status);
        }
    }

    return $weken;
}