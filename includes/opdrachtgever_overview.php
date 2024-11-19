<?php

function hours_registration_client_overview()
{
    if (!is_user_logged_in()) {
        return '<p>Je moet ingelogd zijn om dit overzicht te bekijken.</p>';
    }

    $current_user = wp_get_current_user();

    if (!in_array('opdrachtgever', $current_user->roles) && !in_array('administrator', $current_user->roles)) {
        return '<p>Je hebt geen toestemming om deze pagina te bekijken.</p>';
    }

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

    if (empty($kandidaat_users)) {
        return '<p>Er zijn nog geen kandidaten toegevoegd.</p>';
    }

    // Genereer een token en sla deze op als transient
    $opdrachtgever_token = bin2hex(random_bytes(32));
    set_transient('opdrachtgever_token_' . $current_user->ID, $opdrachtgever_token, 12 * HOUR_IN_SECONDS);

    ob_start();
?>
    <div class="flex flex-col mb-1 sm:mb-0">
        <h1 class="text-2xl leading-tight">Hallo <?php echo esc_html($current_user->display_name); ?></h1>
        <p>Hier is een overzicht van jouw gekoppelde talenten. Klik op een talent om de geregistreerde uren te bekijken.</p>
    </div>
    <div class="overflow-x-auto">
        <table class="min-w-full table-auto">
            <thead>
                <tr>
                    <th class="px-6 py-4 border-b-2 border-gray-200 bg-gray-100 text-left text-xs font-semibold text-gray-600 uppercase">Naam</th>
                    <th class="px-6 py-4 border-b-2 border-gray-200 bg-gray-100 text-left text-xs font-semibold text-gray-600 uppercase">E-mailadres</th>
                    <th class="px-6 py-4 border-b-2 border-gray-200 bg-gray-100 text-left text-xs font-semibold text-gray-600 uppercase">Acties</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($kandidaat_users as $user): ?>
                    <tr>
                        <td class="px-2 py-4 text-left text-sm whitespace-nowrap"><?php echo esc_html($user->display_name); ?></td>
                        <td class="px-2 py-4 text-left text-sm whitespace-nowrap"><?php echo esc_html($user->user_email); ?></td>
                        <td class="px-2 py-4 text-left text-sm whitespace-nowrap">
                            <a href="<?php echo esc_url(add_query_arg(array('kandidaat_id' => $user->ID, 'token' => $opdrachtgever_token), home_url('/opdrachtgever-dashboard'))); ?>" class="bg-black hover:bg-gray-800 text-white font-bold py-2 px-4 rounded focus:outline-none focus:shadow-outline">Bekijk uren</a>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>

<?php
    return ob_get_clean();
}

add_shortcode('opdrachtgever_overview', 'hours_registration_client_overview');