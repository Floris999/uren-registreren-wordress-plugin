<?php
/*
Plugin Name: Urenregistratie Plugin
Description: Een plugin voor het verzenden van uren naar opdrachtgever(s), inclusief een opdrachtgever en beheerder dashboard.
Version: 1.0
Author: Dintech
*/

if (!defined('ABSPATH')) exit;

require_once plugin_dir_path(__FILE__) . 'includes/email_verzending.php';
require_once plugin_dir_path(__FILE__) . 'includes/admin_dashboard.php';
require_once plugin_dir_path(__FILE__) . 'includes/kandidaat_dashboard.php';
require_once plugin_dir_path(__FILE__) . 'includes/notificaties.php';
require_once plugin_dir_path(__FILE__) . 'includes/opdrachtgever_dashboard.php';
require_once plugin_dir_path(__FILE__) . 'includes/wordpress_login_screen.php';
include_once plugin_dir_path(__FILE__) . 'redirects.php';

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

function urenregistratie_enqueue_tailwind()
{
    // Tailwind CSS
    wp_enqueue_style('tailwind-css', 'https://cdnjs.cloudflare.com/ajax/libs/tailwindcss/2.2.19/tailwind.min.css');
}
add_action('wp_enqueue_scripts', 'urenregistratie_enqueue_tailwind');
