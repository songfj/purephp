# PurePHP Framework

**A mini RESTFul framework for PHP 5.3.7+**

    Originally PurePHP was thought for PHP 5.2 support in mind __always__, but everyday
    the 5.3 adoption becomes faster, so I am discontinuing the PHP 5.2 support.
    If you are still interested there is a (unmaintained) php5.2 branch.

PurePHP is very suitable for small projects that doesn't require all the features
that other frameworks (like Laravel) have. A very small core and few dependencies
makes it very lightweight: ~9MB - ~1300 files.

## Requirements

* Apache 2 server (not tested in others)
* PHP 5.3.7 or later (needed for password_* compat. functions)

## Setup

Execute:

```composer create-project mjolnic/purephp your-project-name --prefer-dist --stability=dev```

... or download or clone the source code and run ```composer install```

Finally navigate to the folder url

## Features

* Suitable for small projects that doesn't require all the features that other frameworks like Laravel have.
* Expressjs compatible route expressions
* Ultra lightweight: the core is basically a HTTP routing engine and other essential libraries
* Ultra Fast (see benchmarks)
* Decoupled engines (you can replace them easily using a container)
* Event listener / dispatcher (Laravel Dispatcher)
* Flash messaging and Session manager (php native wrapper)
* Laravel helpers (for arrays and strings)
* Integration with Laravel Blade templates
* Integration with Whoops, Monolog, RedBean and SwiftMailer
* password_compat integration

## Benchmarks

(COMING SOON)

## Events

You can fire, listen or forget events using App::dispatcher()

Those events are triggered by default by the framework (usually in this order):

* request.before_populate, request.populate
* app.before_start, app.before_dispatch
* router.next
* tpl.before_load, tpl.load
* request.before_send, request.send
* app.dispatch, app.start