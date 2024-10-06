# Uren registratie Plugin

De Uren registratie Plugin is een WordPress-plugin waarmee gebruikers hun gewerkte uren voor verschillende dagen van de week kunnen indienen. Administrators en opdrachtgevers kunnen de ingediende uren bekijken en goedkeuren of afkeuren. De status van de ingediende uren wordt vervolgens aan de gebruikers getoond.

## Features

- Gebruikers kunnen hun gewerkte uren voor elke dag van de week indienen.
- Administrators kunnen alle ingediende uren bekijken in het admin dashboard.
- Administrators kunnen de ingediende uren goedkeuren of afkeuren.
- Gebruikers kunnen de status van hun ingediende uren zien (goedgekeurd, afgekeurd of in afwachting).
- Aangepaste inlogpagina met Tailwind CSS-styling.
- Redirects voor gebruikers met de rol `opdrachtgever` of `kandidaat` naar de homepage na het inloggen en blokkeren van toegang tot het WordPress-dashboard en profielpagina.

## Installatie

1. Download de pluginbestanden en upload ze naar de `/wp-content/plugins/urenregistratie` directory, of installeer de plugin direct via het WordPress plugins scherm.
2. Activeer de plugin via het 'Plugins' scherm in WordPress.

## Shortcodes

### `[urenregistratie_form]`

De `[urenregistratie_form]` shortcode toont een formulier waarmee gebruikers hun gewerkte uren kunnen indienen. Het formulier bevat velden voor elke dag van de week en een verzendknop.

### `[opdrachtgever_dashboard]`

De `[opdrachtgever_dashboard]` shortcode toont een dashboard voor opdrachtgevers waarin zij de ingediende uren van hun kandidaten kunnen bekijken en goedkeuren of afkeuren.

## Functies

### `urenregistratie_gebruikersformulier()`

Deze functie genereert het formulier waarmee gebruikers hun gewerkte uren kunnen indienen. Het controleert of de gebruiker is ingelogd, verwerkt de formulierinzending en toont de ingediende weken met hun statussen.

### `urenregistratie_verwerk_inzending($user_id)`

Deze functie verwerkt de formulierinzending en slaat de ingediende uren op als een custom post type 'uren'. Het stelt ook de initiële status van de inzending in op 'in afwachting'.

### `urenregistratie_get_ingediende_weken($user_id)`

Deze functie haalt de ingediende weken op voor de ingelogde gebruiker en retourneert een array van weken met hun statussen.

### `urenregistratie_opdrachtgever_dashboard()`

Deze functie genereert het dashboard voor opdrachtgevers waarin zij de ingediende uren van hun kandidaten kunnen bekijken en goedkeuren of afkeuren. Het controleert of de gebruiker is ingelogd en de juiste rol heeft, en toont vervolgens een tabel met de ingediende uren.

## Admin Dashboard

In het admin dashboard kunnen administrators alle ingediende uren bekijken in een tabel. Elke rij in de tabel bevat de naam van de gebruiker, e-mailadres, weeknummer, ingediende uren, totale uren, status en actieknoppen om de inzending goed te keuren of af te keuren.

## Aangepaste Inlogpagina

De plugin bevat een aangepaste inlogpagina met Tailwind CSS-styling. De standaard WordPress-inlogpagina en taalkeuze worden verborgen.

## Redirects

Gebruikers met de rol `opdrachtgever` of `kandidaat` worden na het inloggen altijd doorgestuurd naar de homepage (`/`). Deze gebruikers hebben geen toegang tot het WordPress-dashboard (`/wp-admin/index.php`) of hun profielpagina (`/wp-admin/profile.php`).

## Changelog

### 1.0.0

- Eerste release.

## Support

Voor ondersteuning, neem contact op met de auteur van de plugin.