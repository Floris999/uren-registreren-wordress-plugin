<?php

function urenregistratie_gebruikersformulier()
{
    if (!is_user_logged_in()) {
        return '<p>Je moet ingelogd zijn om je uren in te dienen.</p>';
    }

    $current_user = wp_get_current_user();
    $user_name = $current_user->display_name;
    $user_email = $current_user->user_email;

    $melding = '';
    if (isset($_POST['uren_submit'])) {
        $melding = urenregistratie_verwerk_inzending($current_user->ID);
    }

    $ingediende_weken = urenregistratie_get_ingediende_weken($current_user->ID);

    ob_start();
?>
    <div class="container">
        <?php if ($melding): ?>
            <div class="alert alert-info"><?php echo $melding; ?></div>
        <?php endif; ?>

        <h2 class="mt-4">Urenregistratie voor: <?php echo esc_html($user_name); ?></h2>
        <p>E-mailadres: <?php echo esc_html($user_email); ?></p>

        <?php if (!empty($ingediende_weken)): ?>
            <h4>Ingediende weken:</h4>
            <ul>
                <?php foreach ($ingediende_weken as $week): ?>
                    <li>Week <?php echo esc_html($week['weeknummer']); ?>: <?php echo esc_html($week['status']); ?></li>
                <?php endforeach; ?>
            </ul>
        <?php endif; ?>

        <form method="post" class="mt-4">
            <div class="form-group">
                <label for="weeknummer">Weeknummer:</label>
                <input type="number" class="form-control" id="weeknummer" name="weeknummer" required>
            </div>

            <div class="form-group">
                <label for="uren_maandag">Uren maandag:</label>
                <input type="number" class="form-control" id="uren_maandag" name="uren_maandag" min="0" max="24" required>
            </div>

            <div class="form-group">
                <label for="uren_dinsdag">Uren dinsdag:</label>
                <input type="number" class="form-control" id="uren_dinsdag" name="uren_dinsdag" min="0" max="24" required>
            </div>

            <div class="form-group">
                <label for="uren_woensdag">Uren woensdag:</label>
                <input type="number" class="form-control" id="uren_woensdag" name="uren_woensdag" min="0" max="24" required>
            </div>

            <div class="form-group">
                <label for="uren_donderdag">Uren donderdag:</label>
                <input type="number" class="form-control" id="uren_donderdag" name="uren_donderdag" min="0" max="24" required>
            </div>

            <div class="form-group">
                <label for="uren_vrijdag">Uren vrijdag:</label>
                <input type="number" class="form-control" id="uren_vrijdag" name="uren_vrijdag" min="0" max="24" required>
            </div>

            <div class="form-group">
                <label for="uren_zaterdag">Uren zaterdag:</label>
                <input type="number" class="form-control" id="uren_zaterdag" name="uren_zaterdag" min="0" max="24" required>
            </div>

            <div class="form-group">
                <label for="uren_zondag">Uren zondag:</label>
                <input type="number" class="form-control" id="uren_zondag" name="uren_zondag" min="0" max="24" required>
            </div>

            <button type="submit" name="uren_submit" class="btn btn-primary">Uren registreren</button>
        </form>
    </div>
<?php

    return ob_get_clean();
}

add_shortcode('urenregistratie_form', 'urenregistratie_gebruikersformulier');

function urenregistratie_verwerk_inzending($user_id)
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
        ),
    );

    // Sla de post op
    // $post_id = wp_insert_post($uren_post);
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
