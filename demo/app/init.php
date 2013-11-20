<?php

// Application initialization file

// URL to callback mapping (in sequencial match order)
// All the path expressions are based in expressjs ones
pure::get('/', 'Page_Welcome::index');
pure::get('*', 'Page_Welcome::handle');