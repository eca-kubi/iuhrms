<?php
ini_set('error_log', '/var/www/html/logs/PHP_errors.log');
error_reporting(E_ALL);
// Bootstrap file for the application
// Load the Composer autoloader
use Composer\Autoload\ClassLoader;
require_once dirname(__DIR__) . "/vendor/autoload.php";

// Autoload Core Libraries
spl_autoload_register(static function ($class_name) {
    $dirs = array(
        __DIR__ . "/controllers",
        __DIR__ . "/libraries",
        __DIR__ . "/viewmodels",
        __DIR__ . '/constants',
        __DIR__ . "/models"
    );
    foreach ($dirs as $dir) {
        $path = "$dir/$class_name.php";
        if (!file_exists($path)) {
            continue;
        }
        require_once $path;
    }
});
// Autoload Composer Libraries
$loader = new ClassLoader();
spl_autoload_register([$loader, 'loadClass']);

// Load the secrets
require_once __DIR__ . '/config/secrets.php';  // VERY IMPORTANT! SEE app/config/secrets-example.php file

// Load the config
require_once __DIR__ . '/config/config.php';

// Start session
session_start();

//Instantiate core class
$init = new Core();
