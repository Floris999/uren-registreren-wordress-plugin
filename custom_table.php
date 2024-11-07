<?php
function urenregistratie_create_custom_table()
{
    global $wpdb;
    $table_name = $wpdb->prefix . 'uren';
    $charset_collate = $wpdb->get_charset_collate();

    $sql = "CREATE TABLE $table_name (
        id mediumint(9) NOT NULL AUTO_INCREMENT,
        user_id bigint(20) NOT NULL,
        weeknummer int(11) NOT NULL,
        jaar varchar(50) NOT NULL,
        uren text NOT NULL,
        status varchar(20) DEFAULT 'in afwachting' NOT NULL,
        date datetime DEFAULT CURRENT_TIMESTAMP NOT NULL,
        PRIMARY KEY  (id)
    ) $charset_collate;";

    require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
    dbDelta($sql);
}