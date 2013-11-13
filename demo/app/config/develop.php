<?php

// Application config

$config = array(
// your config variables
);

// URL to callback mapping (in sequencial match order)
// All the path expressions are based in expressjs ones
pure::get('/', 'ctrl_welcome::index');
pure::get('*', 'ctrl_welcome::handle');

// ALWAYS return an array
return $config;