<?php

function get_start_and_end_date($week, $year) {
    $dto = new DateTime();
    $dto->setISODate($year, $week);
    $start_date = $dto->format('d-m-Y');
    $dto->modify('+6 days');
    $end_date = $dto->format('d-m-Y');
    return array($start_date, $end_date);
}

function send_hours_submission_email_custom_table($record_id)
{
    global $wpdb;
    $table_name = $wpdb->prefix . 'uren';

    $uren_data = $wpdb->get_row($wpdb->prepare(
        "SELECT * FROM $table_name WHERE id = %d",
        $record_id
    ));

    if (!$uren_data) {
        return;
    }

    $user_info = get_userdata($uren_data->user_id);
    $user_name = $user_info ? $user_info->display_name : 'Onbekende gebruiker';
    $user_email = $user_info ? $user_info->user_email : '';

    $opdrachtgever_id = get_user_meta($uren_data->user_id, 'opdrachtgever_id', true);
    $opdrachtgever_info = get_userdata($opdrachtgever_id);
    if (!$opdrachtgever_info) {
        return;
    }

    // Haal het admin emailadres op
    $admin_email = get_option('urenregistratie_notification_email', '');
    if (!$admin_email) {
        return;
    }

    $uren = json_decode($uren_data->uren, true);

    $uren_leesbaar = '';
    $totaal_uren = 0;
    foreach ($uren as $dag => $uren_per_dag) {
        if (!empty($uren_per_dag)) {
            $uren_leesbaar .= ucfirst($dag) . ': ' . $uren_per_dag . ' uur<br>';
            $totaal_uren += (int)$uren_per_dag;
        }
    }

    // Bereken de start- en einddatum van de week
    list($start_date, $end_date) = get_start_and_end_date($uren_data->weeknummer, date('Y'));

    $subject = 'Nieuwe uren ingediend door ' . $user_name;

    $message = 'Beste ' . $opdrachtgever_info->display_name . ",\n\n";
    $message .= 'Er zijn nieuwe uren ingediend door ' . $user_name . " voor week " . $uren_data->weeknummer . " (van " . $start_date . " tot " . $end_date . "):\n\n";
    $message .= $uren_leesbaar;
    $message .= "\n\nTotaal aantal uren: " . $totaal_uren . " uur";
    $message .= "\n\nBekijk de ingediende uren in het dashboard: " . site_url('/wp-admin') . "\n\n";
    $message .= "Met vriendelijke groet,\n";
    $message .= 'Het Uren Registratie Team';

    wp_mail($opdrachtgever_info->user_email, $subject, nl2br($message), array('Content-Type: text/html; charset=UTF-8'));
    wp_mail($admin_email, $subject, nl2br($message), array('Content-Type: text/html; charset=UTF-8'));
}