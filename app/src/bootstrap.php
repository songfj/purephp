<?php

define('PUREPHP_VERSION', '2.0.0-dev');

$startTime = microtime(true);

// Specify your custom app class or leave it as Pure_App
$appClass = 'Pure_App';

// Setting up error reporting and display errors for the startup process
error_reporting(-1);
ini_set('display_errors', 1);

// Set default timezone to avoid timezone warnings
// date_default_timezone_set('UTC');
date_default_timezone_set('Europe/Madrid');

// Server resources
ini_set('memory_limit', '128M');
ini_set('max_execution_time', 60);
ini_set('post_max_size', '16M');
ini_set('upload_max_filesize', '16M');

// Locale and charset
ini_set('default_charset', 'UTF-8');
mb_internal_encoding('UTF-8');
ini_set('default_mimetype', 'text/html');
//setlocale(LC_ALL, 'en_US.UTF8');
setlocale(LC_ALL, 'es_ES@euro', 'es_ES', 'es');

// Session security
ini_set('session.auto_start', 0);
ini_set('session.use_cookies', true);
ini_set('session.use_only_cookies', true); # do not use PHPSESSID in urls
ini_set('session.use_trans_sid', false); # do not use PHPSESSID in urls
ini_set('session.hash_function', 1); # use sha1 algorithm (160 bits)
ini_set('session.cookie_httponly', true);

$ds = DIRECTORY_SEPARATOR;
$rootpath = realpath(dirname(__FILE__) . '/../../') . $ds;

$paths = array(
    'root' => $rootpath,
    'public' => $rootpath,
    'app' => $rootpath . 'app/',
    'config' => $rootpath . 'app/config/',
    'vendor' => $rootpath . 'vendor/',
    'data' => $rootpath . 'app/data/',
    'cache' => $rootpath . 'app/data/cache/',
    'logs' => $rootpath . 'app/data/logs/',
    'content' => $rootpath . 'content/',
    'uploads' => $rootpath . 'content/uploads/',
    'views' => $rootpath . 'app/views/'
);


// Set initial error log file
ini_set('error_log', $paths['logs'] . 'php_error.log');

// Helpers must be included before the Laravel ones
include_once $paths['app'] . 'src/helpers.php';

$loader = require $paths['vendor'] . 'autoload.php';
