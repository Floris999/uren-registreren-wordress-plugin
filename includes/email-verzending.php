<?php
// Functie voor het verzenden van de e-mail met urenoverzicht
function urenregistratie_verzend_email($post_id) {
    if (get_post_type($post_id) !== 'uren') {
        return;
    }

    // Haal het e-mailadres op uit de instellingen
    $notification_email = get_option('urenregistratie_notification_email');
    if (!$notification_email) {
        // Gebruik het admin e-mailadres als fallback
        $notification_email = get_option('admin_email');
    }

    $uren_tekst = get_post_field('post_content', $post_id);

    // Haal de gebruiker op die de uren heeft ingediend
    $user_id = get_post_field('post_author', $post_id);
    $user_info = get_userdata($user_id);
    $user_name = $user_info ? $user_info->display_name : 'Onbekende gebruiker';

    // Haal het weeknummer op uit de postmeta
    $weeknummer = get_post_meta($post_id, 'weeknummer', true);

    // Uren omzetten naar een leesbaar formaat en totaal berekenen
    $uren = json_decode($uren_tekst, true);
    $uren_leesbaar = '';
    $totaal_uren = 0;
    foreach ($uren as $dag => $uren_per_dag) {
        if (!empty($uren_per_dag)) {
            $uren_leesbaar .= ucfirst($dag) . ': ' . $uren_per_dag . ' uur<br>';
            $totaal_uren += (int)$uren_per_dag;
        }
    }

    // E-mail content
    $onderwerp = "Nieuwe uren ingediend door " . $user_name;
    $boodschap = "De volgende uren zijn ingediend door " . $user_name . " voor week " . $weeknummer . ":\n\n" . $uren_leesbaar;
    $boodschap .= "\n\nTotaal aantal uren: " . $totaal_uren . " uur";
    $boodschap .= "\n\nLog nu in om de uren te beoordelen: " . wp_login_url();
    $headers = array('Content-Type: text/html; charset=UTF-8');

    // Verzend de e-mail
    wp_mail($notification_email, $onderwerp, nl2br($boodschap), $headers);
}

// Trigger wanneer uren worden ingediend (auto verzenden)
add_action('publish_uren', 'urenregistratie_verzend_email');