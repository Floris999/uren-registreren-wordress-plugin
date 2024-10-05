<?php
// E-mail notificatiesysteem
function notificatie_verzend_email($action, $to, $message) {
    $onderwerp = "Notificatie: " . $action;
    $headers = array('Content-Type: text/html; charset=UTF-8');

    // Verzend notificatie
    wp_mail($to, $onderwerp, nl2br($message), $headers);
}

// Trigger notificatie bij nieuwe uren
add_action('publish_uren', function($post_id) {
    $kandidaat_email = get_post_meta($post_id, 'kandidaat_email', true);
    notificatie_verzend_email('Nieuwe uren ingediend', $kandidaat_email, 'Je hebt nieuwe uren ingediend.');
});

// Verwerk goedkeur-actie
function urenregistratie_approve_uren() {
    if (!isset($_GET['post_id']) || !isset($_GET['token'])) {
        wp_die('Ongeldige aanvraag.');
    }

    $post_id = intval($_GET['post_id']);
    $token = sanitize_text_field($_GET['token']);
    $saved_token = get_post_meta($post_id, 'uren_token', true);

    if ($token !== $saved_token) {
        wp_die('Ongeldige token.');
    }

    update_post_meta($post_id, 'status', 'goedgekeurd');

    // Haal de e-mail van de aanvrager op
    $kandidaat_email = get_post_meta($post_id, 'kandidaat_email', true);

    // Haal het weeknummer op uit de postmeta
    $weeknummer = get_post_meta($post_id, 'weeknummer', true);

    // Uren omzetten naar een leesbaar formaat en totaal berekenen
    $uren_tekst = get_post_field('post_content', $post_id);
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
    $boodschap = "Je aangevraagde uren voor week " . $weeknummer . " zijn goedgekeurd.\n\n" . $uren_leesbaar;
    $boodschap .= "\n\nTotaal aantal uren: " . $totaal_uren . " uur";

    // Verzend de notificatie
    notificatie_verzend_email('Uren goedgekeurd', $kandidaat_email, $boodschap);

    wp_redirect(admin_url('admin.php?page=uren-overzicht'));
    exit;
}
add_action('admin_post_approve_uren', 'urenregistratie_approve_uren');

// Verwerk afkeur-actie
function urenregistratie_reject_uren() {
    if (!isset($_GET['post_id']) || !isset($_GET['token'])) {
        wp_die('Ongeldige aanvraag.');
    }

    $post_id = intval($_GET['post_id']);
    $token = sanitize_text_field($_GET['token']);
    $saved_token = get_post_meta($post_id, 'uren_token', true);

    if ($token !== $saved_token) {
        wp_die('Ongeldige token.');
    }

    update_post_meta($post_id, 'status', 'afgekeurd');

    // Haal de e-mail van de aanvrager op
    $kandidaat_email = get_post_meta($post_id, 'kandidaat_email', true);

    // Haal het weeknummer op uit de postmeta
    $weeknummer = get_post_meta($post_id, 'weeknummer', true);

    // Uren omzetten naar een leesbaar formaat en totaal berekenen
    $uren_tekst = get_post_field('post_content', $post_id);
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
    $boodschap = "Je aangevraagde uren voor week " . $weeknummer . " zijn afgekeurd.\n\n" . $uren_leesbaar;
    $boodschap .= "\n\nTotaal aantal uren: " . $totaal_uren . " uur";

    // Verzend de notificatie
    notificatie_verzend_email('Uren afgekeurd', $kandidaat_email, $boodschap);

    wp_redirect(admin_url('admin.php?page=uren-overzicht'));
    exit;
}
add_action('admin_post_reject_uren', 'urenregistratie_reject_uren');