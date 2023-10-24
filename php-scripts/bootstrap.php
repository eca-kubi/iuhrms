<?php

require '../vendor/autoload.php';

// Define Constants
define('APP_ROOT', dirname(__FILE__, 2). '/app');
define('PROJECT_ROOT', dirname(__FILE__, 2));

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

const ERROR_LOG_FILE = APP_ROOT . '/logs/PHP_errors.log';

const INFO_LOG_FILE = APP_ROOT . '/logs/PHP_info.log';


// Autoload Core Libraries
spl_autoload_register(static function ($class_name) {
    $baseDir = PROJECT_ROOT . '/';  // Pointing to the iuhrms folder
    $dirs = array(
        $baseDir . "app/controllers",
        $baseDir . "app/libraries",
        $baseDir . "app/viewmodels",
        $baseDir . "app/constants",
        $baseDir . "app/models",
        $baseDir . "app/unittests"
    );
    foreach ($dirs as $dir) {
        $path = "$dir/$class_name.php";
        if (!file_exists($path)) {
            continue;
        }
        if (require_once $path)
            return;
    }
});
