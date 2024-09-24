# Urenregistratie Plugin

The Urenregistratie Plugin is a WordPress plugin that allows users to submit their working hours for different days of the week. Administrators can review the submitted hours and approve or reject them. The status of the submitted hours is then displayed to the users.

## Features

- Users can submit their working hours for each day of the week.
- Administrators can view all submitted hours in the admin dashboard.
- Administrators can approve or reject the submitted hours.
- Users can see the status of their submitted hours (approved, rejected, or pending).

## Installation

1. Download the plugin files and upload them to the `/wp-content/plugins/urenregistratie` directory, or install the plugin through the WordPress plugins screen directly.
2. Activate the plugin through the 'Plugins' screen in WordPress.

## Shortcode

The `[urenregistratie_form]` shortcode displays a form where users can submit their working hours. The form includes fields for each day of the week and a submit button.

## Functions

### `urenregistratie_gebruikersformulier()`

This function generates the form for users to submit their working hours. It checks if the user is logged in, processes the form submission, and displays the submitted weeks with their statuses.

### `urenregistratie_verwerk_inzending($user_id)`

This function processes the form submission and saves the submitted hours as a custom post type 'uren'. It also sets the initial status of the submission to 'pending'.

### `urenregistratie_get_ingediende_weken($user_id)`

This function retrieves the submitted weeks for the logged-in user and returns an array of weeks with their statuses.

## Admin Dashboard

In the admin dashboard, administrators can view all submitted hours in a table. Each row in the table includes the user's name, email, week number, submitted hours, total hours, status, and action buttons to approve or reject the submission.

## Changelog

### 1.0.0

- Initial release.

## Support

For support, please contact the plugin author.
