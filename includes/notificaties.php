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
