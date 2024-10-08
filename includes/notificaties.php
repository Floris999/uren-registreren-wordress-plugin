<?php

function notificatie_verzend_email($action, $to, $message)
{
    $onderwerp = "Notificatie: " . $action;
    $headers = array('Content-Type: text/html; charset=UTF-8');

    wp_mail($to, $onderwerp, nl2br($message), $headers);
}

add_action('publish_uren', function ($post_id) {
    $kandidaat_email = get_post_meta($post_id, 'kandidaat_email', true);
    $user_id = get_post_field('post_author', $post_id);
    $user_info = get_userdata($user_id);
    $user_name = $user_info ? $user_info->display_name : 'Kandidaat';
    $login_url = wp_login_url();

    $message = 'Beste ' . $user_name . ',<br><br>';
    $message .= 'Je hebt nieuwe uren ingediend.<br><br>';
    $message .= 'Log in om de status van je uren te bekijken: <a href="' . $login_url . '">' . $login_url . '</a>';

    notificatie_verzend_email('Nieuwe uren ingediend', $kandidaat_email, $message);
});

add_action('admin_post_reject_uren', 'urenregistratie_reject_uren');