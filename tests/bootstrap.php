<?php

// Setting up error reporting and display errors for the startup process
error_reporting(-1);
ini_set('display_errors', 1);
date_default_timezone_set('Europe/Madrid');

$rootpath = realpath(dirname(__FILE__) . '/../') . DIRECTORY_SEPARATOR;

include $rootpath.'app/src/bootstrap.php';

$paths = array_merge($paths, array(
    'data' => $rootpath . 'app/data/test/',
    'cache' => $rootpath . 'app/data/test/cache/',
    'logs' => $rootpath . 'app/data/test/logs/'
        ));

foreach ($paths as $k => $p) {
    if (!is_dir($p)) {
        mkdir($p, 0755, true);
    }
}

// Set initial error log file
ini_set('error_log', $paths['logs'] . 'php_error.log');

$bootstrap = (object) array(
            'appClass' => $appClass,
            'loader' => $loader,
            'config' => array('paths' => $paths, 'start_time' => $startTime, 'name' => 'test', 'APP_ENV' => 'test')
);
