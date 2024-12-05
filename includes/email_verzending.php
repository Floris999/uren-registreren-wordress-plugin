<?php

function get_start_and_end_date($week, $year)
{
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

    $admin_email = get_option('urenregistratie_notification_email', '');
    if (!$admin_email) {
        return;
    }

    $extra_notification_email = get_user_meta($opdrachtgever_id, 'notificatie_mail_2', true);

    $uren = json_decode($uren_data->uren, true);

    $uren_leesbaar = '';
    $totaal_uren = 0;
    foreach ($uren as $dag => $uren_per_dag) {
        if (!empty($uren_per_dag)) {
            $uren_leesbaar .= ucfirst($dag) . ': ' . $uren_per_dag . ' uur<br>';
            $totaal_uren += (float)$uren_per_dag;
        }
    }

    $jaar = $uren_data->jaar;

    $subject = 'Nieuwe uren ingediend door ' . $user_name;

    $message = 'Beste ' . $opdrachtgever_info->display_name . ",\n\n";
    $message .= 'Er zijn nieuwe uren ingediend door ' . $user_name . " voor week " . $uren_data->weeknummer . " jaartal " . $jaar . ":\n\n";
    $message .= $uren_leesbaar;
    $message .= "\n\nTotaal aantal uren: " . $totaal_uren . " uur";
    $message .= "\n\nIn ons portaal kun je de ingediende uren goedkeuren of evt. eenmalig aanpassen: " . site_url('/wp-admin') . "\n\n";
    $message .= "Wil je de uren uiterlijk op maandag controleren en goedkeuren?\n";
    $message .= "Bedankt!\n\n";
    $message .= "Met vriendelijke groet,\n";
    $message .= get_bloginfo('name');

    wp_mail($opdrachtgever_info->user_email, $subject, nl2br($message), array('Content-Type: text/html; charset=UTF-8'));
    wp_mail($admin_email, $subject, nl2br($message), array('Content-Type: text/html; charset=UTF-8'));

    if (!empty($extra_notification_email)) {
        wp_mail($extra_notification_email, $subject, nl2br($message), array('Content-Type: text/html; charset=UTF-8'));
    }
}

function send_opdrachtgever_submission_email($kandidaat_id, $weeknummer, $jaar, $uren)
{
    $saved_email = get_option('urenregistratie_notification_email', '');
    if (!$saved_email) {
        return;
    }

    $user_info = get_userdata($kandidaat_id);
    $user_name = $user_info ? $user_info->display_name : 'Onbekende gebruiker';

    $opdrachtgever_id = get_user_meta($kandidaat_id, 'opdrachtgever_id', true);
    $opdrachtgever_info = get_userdata($opdrachtgever_id);
    $opdrachtgever_name = $opdrachtgever_info ? $opdrachtgever_info->display_name : 'Onbekende opdrachtgever';

    $uren_leesbaar = '';
    $totaal_uren = 0;
    foreach ($uren as $dag => $uren_per_dag) {
        $uren_leesbaar .= ucfirst($dag) . ': ' . esc_html($uren_per_dag) . ' uur<br>';
        $totaal_uren += (float)$uren_per_dag;
    }

    $subject = 'Uren aangepast ';
    $message = 'Beste beheerder' .  ",<br><br>";
    $message .= 'De uren van ' . $user_name . ' voor week ' . $weeknummer . ' jaartal ' . $jaar . ' zijn aangepast door opdrachtgever ' . $opdrachtgever_name . '.<br><br>';
    $message .= 'Hier is een overzicht van de aangepaste uren:<br><br>';
    $message .= $uren_leesbaar;
    $message .= '<br><br>Totaal aantal uren: ' . $totaal_uren . ' uur<br><br>';
    $message .= "Met vriendelijke groet,<br>";
    $message .= get_bloginfo('name');

    wp_mail($saved_email, $subject, $message, array('Content-Type: text/html; charset=UTF-8'));
}
