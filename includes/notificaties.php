<?php

function send_candidate_notification_email($record_id)
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
    $user_name = $user_info ? $user_info->display_name : 'Kandidaat';
    $user_email = $user_info ? $user_info->user_email : '';

    if (empty($user_email)) {
        return;
    }


    $login_url = wp_login_url();
    $message = 'Hi ' . $user_name . ',<br><br>';
    $message .= 'Bedankt voor je ingevulde uren voor week ' . $uren_data->weeknummer . '.<br><br>';
    $message .= 'Je opdrachtgever ontvangt ook een email met daarin een overzicht van je ingevulde uren.<br><br>';
    $message .= 'De status van je uren kun je bekijken door in te loggen in ons portaal. Ga dan naar: <a href="' . $login_url . '">' . $login_url . '</a><br><br>';
    $message .= 'Zijn je uren op maandag aan het einde van de middag nog niet goedgekeurd?<br>';
    $message .= 'Geef dit dan gerust aan ons (en je opdrachtgever) door.<br><br>';
    $message .= "Met vriendelijke groet,<br>";
    $message .= get_bloginfo('name');

    $headers = array('Content-Type: text/html; charset=UTF-8');

    wp_mail($user_email, 'Nieuwe uren ingediend', $message, $headers);
}
