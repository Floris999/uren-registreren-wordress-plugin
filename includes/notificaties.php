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
    $message = 'Beste ' . $user_name . ',<br><br>';
    $message .= 'Je hebt nieuwe uren ingediend voor week ' . $uren_data->weeknummer . '.<br><br>';
    $message .= 'Log in om de status van je uren te bekijken: <a href="' . $login_url . '">' . $login_url . '</a>';

    $headers = array('Content-Type: text/html; charset=UTF-8');

    wp_mail($user_email, 'Nieuwe uren ingediend', $message, $headers);
}
