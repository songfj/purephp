<?php

// Specify your custom app class or leave it as pure_app
$appClass = 'pure_app';

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
$rootpath = realpath(dirname(dirname(__FILE__))) . $ds;
$demopath = $rootpath . 'demo' . $ds;

$paths = array(
    'root' => $demopath,
    'app' => $demopath . 'app/',
    'config' => $demopath . 'app/config/',
    'vendor' => $demopath . 'app/vendor/',
    'data' => $demopath . 'app/data/',
    'logs' => $demopath . 'app/data/logs/',
    'content' => $demopath . 'content/',
    'assets' => $demopath . 'content/assets/',
    'uploads' => $demopath . 'content/uploads/',
    'views' => $demopath . 'content/views/'
);

// Create paths if don't exist (optional)
//foreach ($paths as $p) {
//    if (!is_dir($p)) {
//        mkdir($p, 0755, true);
//    }
//}


// Set initial error log file
ini_set('error_log', $paths['logs'] . 'php_error.log');

require_once $rootpath . 'src/pure/loader.php';

$loader = pure_loader::getDefault();
$loader->register();
$loader->add(null, array($paths['vendor'], $paths['app'] . 'classes'));

/* @var $app pure_app */
$app1 = new $appClass($loader, $paths, 'default', array('useIndexFile' => false));

//try {
//    $app->start();
//} catch (Exception $exc) {
//    error_log($exc->getTraceAsString());
//    pure::dieMessage();
//}
?>