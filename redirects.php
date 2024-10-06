<?php

function redirect_non_admin_users()
{
    if (!defined('DOING_AJAX') || !DOING_AJAX) {
        $current_user = wp_get_current_user();
        if (in_array('opdrachtgever', $current_user->roles) || in_array('kandidaat', $current_user->roles)) {
            wp_redirect(home_url());
            exit;
        }
    }
}
add_action('admin_init', 'redirect_non_admin_users');

function prevent_admin_access()
{
    $current_user = wp_get_current_user();
    if (in_array('opdrachtgever', $current_user->roles) || in_array('kandidaat', $current_user->roles)) {
        if (is_admin() && !defined('DOING_AJAX') && !DOING_AJAX) {
            wp_redirect(home_url());
            exit;
        }
    }
}
add_action('admin_init', 'prevent_admin_access', 100);

function redirect_login_page($redirect_to, $request, $user)
{
    if (isset($user->roles) && (in_array('opdrachtgever', $user->roles) || in_array('kandidaat', $user->roles))) {
        return home_url();
    }
    return $redirect_to;
}
add_filter('login_redirect', 'redirect_login_page', 10, 3);
