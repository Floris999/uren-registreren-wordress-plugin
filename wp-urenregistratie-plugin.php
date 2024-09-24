<?php
/*
Plugin Name: Urenregistratie Plugin
Description: Een plugin voor het verzenden van uren naar opdrachtgever(s), inclusief notificaties en een opdrachtgever dashboard.
Version: 1.0
Author: Dintech & FVD
*/

if (!defined('ABSPATH')) exit;

require_once plugin_dir_path(__FILE__) . 'includes/email-verzending.php';
require_once plugin_dir_path(__FILE__) . 'includes/opdrachtgever-dashboard.php';
require_once plugin_dir_path(__FILE__) . 'includes/user-dashboard.php';
require_once plugin_dir_path(__FILE__) . 'includes/notificaties.php';

function urenregistratie_plugin_activatie()
{
    flush_rewrite_rules();
}
register_activation_hook(__FILE__, 'urenregistratie_plugin_activatie');

function urenregistratie_plugin_deactivatie()
{
    flush_rewrite_rules();
}
register_deactivation_hook(__FILE__, 'urenregistratie_plugin_deactivatie');

function urenregistratie_enqueue_bootstrap()
{
    // Bootstrap CSS
    wp_enqueue_style('bootstrap-css', 'https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css');

    // Bootstrap JS
    wp_enqueue_script('bootstrap-js', 'https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js', array('jquery'), null, true);
}
add_action('wp_enqueue_scripts', 'urenregistratie_enqueue_bootstrap');
