<?php
// Bootstrap file for the application
// Load the Composer autoloader
use Composer\Autoload\ClassLoader;
require_once dirname(__DIR__) . "/vendor/autoload.php";

// Autoload Core Libraries
spl_autoload_register(static function ($class_name) {
    $dirs = array(
        __DIR__ . "/apicontrollers",
        __DIR__ . "/controllers",
        __DIR__ . "/libraries",
        __DIR__ . '/constants',
        __DIR__ . "/viewmodels",
        __DIR__ . "/models",
        __DIR__ . "/models/validators",
        __DIR__ . "/models/schemas",

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

// Secure the session cookie options
$options =  [
    'lifetime' => 3600 * 24 * 30, // 30 days
    'path' => '/',
    'domain' =>  DOMAIN,
    //'secure' => true, // Transmit session cookies over HTTPS only
    'httponly' => true, // Prevent JavaScript access to session cookies
    'samesite' => 'Lax' // SameSite attribute (can be 'Strict', 'Lax', or 'None')
];

session_set_cookie_params($options);

// Start session
session_start();

//Instantiate core class
$init = new Core();
