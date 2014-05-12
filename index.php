<?php

include_once 'app/src/bootstrap.php';

/* @var $app Pure_App */
$app = new $appClass($loader, array('paths' => $paths, 'start_time' => $startTime, 'name' => 'default'));

try {
    $app->start();
} catch (Exception $exc) {
    error_log($exc->getTraceAsString());
    $app->halt();
}
?>