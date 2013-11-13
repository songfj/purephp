# PURE Framework

** A lightweight PHP 5.2 RESTFul framework based on expressjs **

## Features

* Ultra lightweight: the core is basically a HTTP routing engine and utility classes
* Ultra Fast (see benchmarks)
* Decoupled engines (you can replace them easily)
* Event listener / dispatcher
* Flash messaging
* Basic templating engine (pure php)
* Composer class loader port compatible with PHP 5.2

## Benchmarks

Apache Bench is used in all tests (ab -n 2000 -c 10 {url}) in a 2011 iMac with 4GB of DDR3 RAM

* PHP 5.4.4 :  ~300 req/sec (~3.2ms*)
* PHP 5.4.4 + APC :  ~900 req/sec (~1.1ms*)
* PHP 5.2.13 :  ~275 req/sec (~3.6ms*)
* PHP 5.2.13 + APC :  ~850 req/sec (~1.1ms*)

(*) Time per request, mean across all concurrent requests