<?php

function custom_login_screen()
{
?>
    <!DOCTYPE html>
    <html class="h-full bg-white">

    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Login</title>
        <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
    </head>

    <body class="h-full">
        <div class="flex min-h-full flex-col justify-center px-6 py-12 lg:px-8">
            <div class="sm:mx-auto sm:w-full sm:max-w-sm">
                <img class="mx-auto h-10 w-auto" src="https://tailwindui.com/plus/img/logos/mark.svg?color=indigo&shade=600" alt="Your Company">
                <h2 class="mt-10 text-center text-2xl font-bold leading-9 tracking-tight text-gray-900">Inloggen</h2>
            </div>

            <div class="mt-10 sm:mx-auto sm:w-full sm:max-w-sm">
                <form class="space-y-6" action="<?php echo esc_url(site_url('wp-login.php', 'login_post')); ?>" method="POST">
                    <div>
                        <label for="user_login" class="block text-sm font-medium leading-6 text-gray-900">E-mailadres</label>
                        <div class="mt-2">
                            <input id="user_login" name="log" type="text" autocomplete="username" required class="block w-full rounded-md border-0 py-1.5 text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 placeholder:text-gray-400 focus:ring-2 focus:ring-inset focus:ring-indigo-600 sm:text-sm sm:leading-6">
                        </div>
                    </div>

                    <div>
                        <div class="flex items-center justify-between">
                            <label for="user_pass" class="block text-sm font-medium leading-6 text-gray-900">Wachtwoord</label>
                            <!--<div class="text-sm">
                                <a href="" class="font-semibold text-indigo-600 hover:text-indigo-500">Wachtwoord vergeten</a>
                            </div>-->
                        </div>
                        <div class="mt-2">
                            <input id="user_pass" name="pwd" type="password" autocomplete="current-password" required class="block w-full rounded-md border-0 py-1.5 text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 placeholder:text-gray-400 focus:ring-2 focus:ring-inset focus:ring-indigo-600 sm:text-sm sm:leading-6">
                        </div>
                    </div>

                    <div>
                        <button type="submit" class="flex w-full justify-center rounded-md bg-indigo-600 px-3 py-1.5 text-sm font-semibold leading-6 text-white shadow-sm hover:bg-indigo-500 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-indigo-600">Inloggen</button>
                    </div>
                </form>
            </div>
        </div>
    </body>

    </html>
<?php
}

function hide_default_login_form()
{
?>
    <style>
        /* Verberg het standaard WordPress-inlogformulier en de taalkeuze */
        #login,
        #language-switcher {
            display: none;
        }
    </style>
<?php
}

add_action('login_enqueue_scripts', 'custom_login_screen');
add_action('login_head', 'hide_default_login_form');
