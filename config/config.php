<?php

/**
 * Fernico - Ridiculously lite PHP framework
 *
 * @author Areeb Majeed, Volcrado Holdings
 * @package Fernico
 * @copyright 2017 - Volcrado Holdings Limited
 * @license https://opensource.org/licenses/MIT MIT License
 * @link https://volcrado.com/
 *
 */

// Nothing strengthens authority so much as silence.

if (!defined('FERNICO')) {
    fernico_destroy();
}

/*
 * Database details for your installation. It's recommended that you use Fernico with a database.
 * Fernico uses PHP MySQLi to communicate with the database.
 */

$fernico_db_settings = array(
    "CONNECT_TO_DATABASE" => true,
    "DATABASE_HOST" => "",
    "DATABASE_NAME" => "",
    "DATABASE_USER" => "",
    "DATABASE_PASSWORD" => ""
);

/*
 * These are some miscellaneous application details here.
 */

$fernico_app_settings = array(
    "DEFAULT_CONTROLLER" => "homeIndex",
    "DEFAULT_ACTION" => "home",
    "ERROR_REPORTING" => true,
    "ERROR_LOG_DATABASE" => false
);

/*
 * Fernico uses Smart template engine to render the templates.
 * You can configure your default template directory here and storage place for generated templates.
 */

$fernico_template_settings = array(
    "TEMPLATE_DIR" => "default",
    "TEMPLATE_COMPILED_DIR" => FERNICO_PATH . '/storage/cache/templates_c'
);

/*
 * This includes settings related with the default session created by Fernico.
 */

$fernico_session_settings = array(
    "SESSION_NAME" => 'fernico_session',
    "SECURE" => false,
    "HTTP_ONLY" => true
);

/*
 * Fernico comes with an inbuilt Authentication system which makes it easier to authentication users in your app.
 * You can choose between multiple settings.
 */

$fernico_authentication_settings = array(
    "GOOGLE_RECAPTCHA" => true,
    "RECAPTCHA_SITE_KEY" => "",
    "RECAPTCHA_SECRET_KEY" => "",
    "CONTACT_EMAIL" => "",
    "SESSION_DAYS" => 30,
    "COOKIE_DOMAIN" => '.domain.tld',
    "NO_REPLY_EMAIL" => 'no.reply@domain.tld',
    "COOKIE_SECRET" => '52e052e01be840d3d1fc618fc4e2381a39da1be840d352e01be840d3d1fc618fc4e2381a39dad152e01be840d3d1fc618fc4e2381a39dafc618fc4e2381a39da',
    "EMAIL_CONFIRMATION" => true,
    "WEBSITE_NAME" => "Fernico",
    "CONFIRMATION_CONTROLLER" => "account",
    "CONFIRMATION_ACTION" => "confirm_account",
    "USE_SMTP" => true,
    "SMTP_AUTH" => true,
    "EMAIL_SMTP_ENCRYPTION" => 'ssl',
    "EMAIL_SMTP_HOST" => "",
    "EMAIL_SMTP_USERNAME" => "",
    "EMAIL_SMTP_PASSWORD" => "",
    "EMAIL_SMTP_PORT" => "587",
    "RESET_PASSWORD_CONTROLLER" => "account",
    "RESET_PASSWORD_ACTION" => "confirm_password_change",
    "CHANGE_EMAIL_CONTROLLER" => "account",
    "CHANGE_EMAIL_ACTION" => "confirm_email_change"
);

/*
 * NOTE: To add your own custom settings (maybe, for a plugin or some controller), you need to create an array before this line of comment.
 * The array you created should have a prefix of 'fernico_' and a suffix of '_settings' (for example, $fernico_site_settings), otherwise it won't be callable from the Config class.
 *
 * Do not edit beyond this line unless you know what you're doing.
 * Combining all arrays together to form one global array.
 * To access these settings, use the Config static class. For instance, $template_dir = Config::fetch("TEMPLATE_DIR");
 */

$ignore = array(
    'GLOBALS',
    '_FILES',
    '_COOKIE',
    '_POST',
    '_GET',
    '_SERVER',
    '_ENV',
    'ignore',
    'php_errormsg',
    'HTTP_RAW_POST_DATA',
    'http_response_header',
    'argc',
    'argv',
    'ignore'
);

$all_settings_found = array_diff_key(get_defined_vars() + array_flip($ignore), array_flip($ignore));
$global_fernico_settings = array();

foreach ($all_settings_found as $key => $value) {

    if (substr($key, 0, 8) === 'fernico_' && substr($key, -9) === '_settings') {

        $global_fernico_settings = array_merge($global_fernico_settings, $value);

    }

}

