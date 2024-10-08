<?php

function send_hours_submission_email($post_id)
{
    if (get_post_type($post_id) != 'uren') {
        return;
    }

    $user_id = get_post_meta($post_id, 'user_id', true);
    $user_info = get_userdata($user_id);
    $user_name = $user_info ? $user_info->display_name : 'Onbekende gebruiker';
    $user_email = $user_info ? $user_info->user_email : '';

    $opdrachtgever_id = get_user_meta($user_id, 'opdrachtgever_id', true);
    $opdrachtgever_info = get_userdata($opdrachtgever_id);

    $admin_email = get_option('urenregistratie_notification_email', '');

    if (!$opdrachtgever_info || !$admin_email) {
        return;
    }

    $weeknummer = get_post_meta($post_id, 'weeknummer', true);
    $uren_tekst = get_post_meta($post_id, 'uren', true);

    $uren_leesbaar = '';
    $totaal_uren = 0;
    foreach ($uren_tekst as $dag => $uren_per_dag) {
        if (!empty($uren_per_dag)) {
            $uren_leesbaar .= ucfirst($dag) . ': ' . $uren_per_dag . ' uur<br>';
            $totaal_uren += (int)$uren_per_dag;
        }
    }

    $subject = 'Nieuwe uren ingediend door ' . $user_name;

    $message = 'Beste ' . $opdrachtgever_info->display_name . ",\n\n";
    $message .= 'Er zijn nieuwe uren ingediend door ' . $user_name . " voor week " . $weeknummer . ":\n\n";
    $message .= $uren_leesbaar;
    $message .= "\n\nTotaal aantal uren: " . $totaal_uren . " uur";
    $message .= "\n\nBekijk de ingediende uren in het dashboard: " . site_url('/wp-admin') . "\n\n";
    $message .= 'Met vriendelijke groet,\n';
    $message .= 'Het Uren Registratie Team';

    wp_mail($opdrachtgever_info->user_email, $subject, nl2br($message), array('Content-Type: text/html; charset=UTF-8'));

    wp_mail($admin_email, $subject, nl2br($message), array('Content-Type: text/html; charset=UTF-8'));
}

add_action('save_post_uren', 'send_hours_submission_email');
