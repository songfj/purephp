<?php
// Application initialization file

// Start a PHP session
App::session()->start();

// Set default content type
App::resp()->contentType('text/html', 'utf-8');

// URL to callback mapping (in sequencial match order)
// All the path expressions are based in expressjs ones
App::get('/', 'MainController@index');
App::get('*', 'MainController@handle');