# PurePHP Framework

**A lightweight RESTFul framework for PHP 5.3.7+**

IMPORTANT: I am discontinuing the PHP 5.2 support, but if you are still interested there is a (unmaintained) php5.2 branch

## Requirements

* Apache 2 server (not tested in others)
* PHP 5.3.7 or later

## Setup

Execute:

```composer create-project mjolnic/purephp your-project-name --prefer-dist --stability=dev```

... or download or clone the source code

Finally navigate to the folder url

## Features

* Ultra lightweight: the core is basically a HTTP routing engine and other basic libraries
* Ultra Fast (see benchmarks)
* Decoupled engines (you can replace them easily)
* Event listener / dispatcher (Laravel Dispatcher)
* Flash messaging and Session manager (php native wrapper)
* Laravel 4 helpers (for arrays and strings)
* Integration with Laravel Blade templates
* Integration with Whoops, Monolog, RedBean and SwiftMailer
* password_compat integration

## Benchmarks

(NEEDS UPDATE)

Apache Bench is used in all tests (ab -n 2000 -c 10 {url}) in
a 2011 iMac with 4GB of DDR3 RAM using MAMP

* PHP 5.4.4 :  ~300 req/sec (~3.2ms*)
* PHP 5.4.4 + APC :  ~900 req/sec (~1.1ms*)
* PHP 5.2.13 :  ~275 req/sec (~3.6ms*)
* PHP 5.2.13 + APC :  ~850 req/sec (~1.1ms*)

(*) Time per request, mean across all concurrent requests

## Events

You can trigger, bind or unbind events using Pure::trigger, Pure::on and
Pure::off respectively.

Those events are triggered by the framework (usually in this order):

* request.before_populate, request.populate
* app.before_start, app.before_dispatch
* router.next
* tpl.before_load, tpl.load
* request.before_send, request.send
* app.dispatch, app.start