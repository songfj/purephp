<?php
// Application initialization file

// Start a PHP session
pure::session()->start();

// Set default content type
pure::resp()->contentType('text/html', 'utf-8');

// URL to callback mapping (in sequencial match order)
// All the path expressions are based in expressjs ones
pure::get('/', 'MainController::index');
pure::get('*', 'MainController::handle');