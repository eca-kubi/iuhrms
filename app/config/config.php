<?php

use Twig\Environment;
use Twig\Extension\DebugExtension;

define("URL_ROOT", 'http://localhost:' . ($_SERVER['SERVER_PORT'] ?? 8000));

define('APP_ROOT', dirname(__FILE__, 2));

// DB Params
const DB_HOST = 'mysql';
const DB_USER = 'root';
const DB_NAME = 'iuhrms';
const DB_PORT = 3306;

// Site Name
const SITE_NAME = 'IU Hostel Reservation & Management System';
const APP_NAME = 'IU H R M S';

// App Version
const APP_VERSION = '1.0.0';

// Configure PHPMailer
const EMAIL_SENDER_ADDRESS = 'ecakubi@gmail.com';
const EMAIL_SENDER_NAME = SITE_NAME;
const EMAIL_SMTP_HOST = 'smtp.gmail.com';
const EMAIL_SMTP_PORT = '587';

function getURLRoot(): string
{
    return URL_ROOT;
}

// Twig setup

$loader = new CustomFilesystemLoader(APP_ROOT . '/views');
$twig = new Environment($loader, [
    'cache' => APP_ROOT . '/cache',
    'debug' => true,
]);

$twig->addExtension(new DebugExtension());

$twig->registerUndefinedFunctionCallback(function ($name) {
    Helpers::log_error("Undefined function called in Twig template: $name");
    return false; // This tells Twig to ignore the error
});

$twig->registerUndefinedFilterCallback(function ($name) {
    Helpers::log_error("Undefined filter called in Twig template: $name");
    return false; // This tells Twig to ignore the error
});

$twig->registerUndefinedTokenParserCallback(function ($name) {
    Helpers::log_error("Undefined token parser called in Twig template: $name");
    return false; // This tells Twig to ignore the error
});

// Add the custom extension to Twig
$twig->addExtension(new HelpersExtension());
$twig->addExtension(new ModelsExtension());

// Add functions to the Twig environment
$twig->addFunction(new Twig\TwigFunction('getURLRoot', 'getURLRoot'));

// Add filters to the Twig environment
$twig->addFilter(new Twig\TwigFilter('date_format', function ($date, $format) {
    return date_format(date_create($date), $format);
}));


// Add Global constants to the Twig environment
$twig->addGlobal('URL_ROOT', URL_ROOT);

$twig->addGlobal('APP_ROOT', APP_ROOT);

$twig->addGlobal('SITE_NAME', SITE_NAME);

$twig->addGlobal('APP_NAME', APP_NAME);

$twig->addGlobal('APP_VERSION', APP_VERSION);

