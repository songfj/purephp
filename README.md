# PURE Framework

**A lightweight PHP 5.2+ RESTFul framework based on expressjs**

[![Build Status](https://travis-ci.org/mjaalnir/purephp.png)](https://travis-ci.org/mjaalnir/purephp)
[![Bitdeli Badge](https://d2weczhvl823v0.cloudfront.net/mjaalnir/purephp/trend.png)](https://bitdeli.com/free "Bitdeli Badge")

## Requirements

* Apache server
* PHP 5.2 or later (PHP 5.3.3 or later for unit tests)

## Setup

* Download or clone the source code
* Navigate to install.php and follow the instructions
* For getting started read the demo app source code

## Features

* Ultra lightweight: the core is basically a HTTP routing engine and utility classes
* Ultra Fast (see benchmarks)
* Decoupled engines (you can replace them easily)
* Event listener / dispatcher
* Flash messaging
* Basic templating engine (pure php)
* Composer class loader port compatible with PHP 5.2

## Benchmarks

Apache Bench is used in all tests (ab -n 2000 -c 10 {url}) in
a 2011 iMac with 4GB of DDR3 RAM using MAMP

* PHP 5.4.4 :  ~300 req/sec (~3.2ms*)
* PHP 5.4.4 + APC :  ~900 req/sec (~1.1ms*)
* PHP 5.2.13 :  ~275 req/sec (~3.6ms*)
* PHP 5.2.13 + APC :  ~850 req/sec (~1.1ms*)

(*) Time per request, mean across all concurrent requests

## Events

You can trigger, bind or unbind events using pure::trigger, pure::on and
pure::off respectively.

Those events are triggered by the framework (usually in this order):

* request.before_populate, request.populate
* app.before_start, app.before_dispatch
* router.next
* tpl.before_load, tpl.load
* request.before_send, request.send
* app.dispatch, app.start

