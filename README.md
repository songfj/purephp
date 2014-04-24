# PurePHP Framework

**A lightweight RESTFul framework with legacy support for PHP 5.2+ . Based on expressjs**

## Why PHP 5.2?

The majority of my customers' hostings are not supporting PHP 5.3 yet and I needed a very simple and easy to understand framework
for small projects, a framework which you just unzip and works with zero or one-step installation.

I felt unconfortable with other PHP 5.2 frameworks out there, that wasn't giving me the freedom I needed, specially before
using other frameworks like expressjs or Slim for a while: decoupled framework and engines, facade mode, middleware, RESTful driven, events, ...

So I ported many expressjs concepts and route regular expression engine to the PHP world.

With PurePHP you can build your own framework, with the directories or engines that best fit your requirements, or just embed it
for using as a part of an existing project, like Wordpress.

## Requirements

* Apache server
* PHP 5.2 or later

## Setup

* Download or clone the source code and navigate to the folder url

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

You can trigger, bind or unbind events using Pure::trigger, Pure::on and
Pure::off respectively.

Those events are triggered by the framework (usually in this order):

* request.before_populate, request.populate
* app.before_start, app.before_dispatch
* router.next
* tpl.before_load, tpl.load
* request.before_send, request.send
* app.dispatch, app.start


Other events (outside of the default app life cycle) :

* auth.session_login, auth.session_logout


## To-Do

* Plugins system
* Port expressjs param binding engine
* CLI Mode / executable