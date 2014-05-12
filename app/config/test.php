<?php

// Application config

$config = array(
    //Whoops
    'debug' => false,
    // DB
    'db.enabled' => false,
    'db.dsn' => 'sqlite:' . App::path('data') . 'app.sqlite',
    'db.username' => null,
    'db.password' => null,
    // SMTP
    'smtp.enabled' => false,
    'smtp.host' => 'mail.example.com',
    'smtp.port' => 25, //or 587, or 465, ...
    'smtp.from' => 'noreply@example.com',
    'smtp.user' => 'noreply@example.com',
    'smtp.password' => 'xxxxxxxxx'
);

// ALWAYS return an array
return $config;
