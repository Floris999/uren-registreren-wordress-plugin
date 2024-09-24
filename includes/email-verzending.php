<?php
// Functie voor het verzenden van de e-mail met urenoverzicht
function urenregistratie_verzend_email($post_id) {
    if (get_post_type($post_id) !== 'uren') {
        return;
    }

    // Haal de opdrachtgever e-mail op
    $opdrachtgever_email = get_post_meta($post_id, 'opdrachtgever_email', true);
    $uren_tekst = get_post_field('post_content', $post_id);

    // E-mail content
    $onderwerp = "Nieuwe uren ingediend";
    $boodschap = "De volgende uren zijn ingediend:\n\n" . $uren_tekst;
    $headers = array('Content-Type: text/html; charset=UTF-8');

    // Verzend de e-mail
    wp_mail($opdrachtgever_email, $onderwerp, nl2br($boodschap), $headers);
}

// Trigger wanneer uren worden ingediend (auto verzenden)
add_action('publish_uren', 'urenregistratie_verzend_email');
