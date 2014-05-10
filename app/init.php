<?php
// Application initialization file

// including helpers is optional
include_once Pure::path('purephp') . 'helpers.php';

// Start a PHP session
Pure::session()->start();

// Set default content type
Pure::resp()->contentType('text/html', 'utf-8');

// URL to callback mapping (in sequencial match order)
// All the path expressions are based in expressjs ones
Pure::get('/', 'MainController::index');
Pure::get('*', 'MainController::handle');