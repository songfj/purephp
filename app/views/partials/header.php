<!doctype html>
<html lang="en" class="sticky-footer-container">
    <head>
        <meta charset="utf-8">
        <!--<meta http-equiv="X-UA-Compatible" content="IE=edge">-->
        <meta name="viewport" content="width=device-width, user-scalable=no, initial-scale=1.0, maximum-scale=1">

        <title>Bootstrap Quickstart</title>

        <meta name="description" content="">
        <!--<meta name="keywords" content="">-->
        <meta name="author" content="">
        <meta name="robots" content="INDEX, FOLLOW">

        <!--<link rel="canonical" href="<?php echo pure_url() ?>">-->
        <link rel="shortcut icon" href="<?php echo pure_asset('img/favicon.png'); ?>">

        <style type="text/css" id="relativecss">html,body{position:static} body * {position: relative}</style>
        
        <link href='http://fonts.googleapis.com/css?family=Noto+Sans:400,700,400italic,700italic' rel='stylesheet' type='text/css'>
        <link href="<?php echo pure_asset('css/app.min.css') ?>" rel="stylesheet">

        <!-- Cross-browser compatibility scripts: -->
        <script type='text/javascript' src="<?php echo pure_asset('vendor/js/compat.min.js') ?>"></script>

        <!-- HTML5 Shim and Respond.js IE8 support of HTML5 elements and media queries -->
        <!-- WARNING: Respond.js doesn't work if you view the page via file:// -->
        <!--[if lt IE 9]>
        <script src="https://oss.maxcdn.com/libs/html5shiv/3.7.0/html5shiv.js"></script>
        <script src="https://oss.maxcdn.com/libs/respond.js/1.4.2/respond.min.js"></script>
        <![endif]-->
    </head>
    <body class="sticky-footer-wrapper">

        <!--[if lt IE 8]>
        <div class="browsehappy"
             style="padding:10px 20px; font-style: italic; text-align:center; background:#fff7bb; font-size:14px; font-family:Arial,sans-serif; color:#000;">
            ﻿It looks like you’re using an <strong>outdated and insecure</strong> version of Internet Explorer. Please <a
            target="_blank" style="color:#000 !important; text-decoration:underline !important;" href="http://browsehappy.com/">upgrade
            your browser</a> to improve your security and experience.
        </div>
        <![endif]-->

        <header class="main-header" role="banner">
            <!-- Static navbar -->
            <div class="main-navbar navbar navbar-default navbar-static-top">
                <div class="main-nav-right">
                    <div class="container">
                        <div class="navbar-header">
                            <button type="button" class="navbar-toggle" data-toggle="collapse" data-target=".navbar-collapse">
                                <span class="sr-only">Toggle navigation</span>
                                <span class="icon-bar"></span>
                                <span class="icon-bar"></span>
                                <span class="icon-bar"></span>
                            </button>
                            <a class="navbar-brand logo" href="<?php echo pure_url() ?>">
                                <img height="50" width="50" src="<?php echo pure_asset('img/logo.png'); ?>" alt=""/>
                            </a>
                        </div>
                        <nav role="navigation" class="navbar-collapse collapse">
                            <ul class="main-menu nav navbar-nav">
                                <li class="menu-item menu-item-3  active ">
                                    <a href="<?php echo pure_url() ?>">Home</a>
                                </li>
                                <li class="menu-item menu-item-4 ">
                                    <a href="#">Contact</a>
                                </li>
                            </ul>
                            <ul class="nav navbar-nav navbar-right">
                                <li class="dropdown">
                                    <a href="#" class="dropdown-toggle" data-toggle="dropdown">ES <b class="caret"></b></a>
                                    <ul class="dropdown-menu lang-list">
                                        <li class=" active lang-es">
                                            <a title="Español" rel="alternate" hreflang="es" href="#">Español</a>
                                        </li>
                                        <li class=" lang-en">
                                            <a title="English" rel="alternate" hreflang="en" href="#">English</a>
                                        </li>
                                    </ul>
                                </li>
                            </ul>
                        </nav>
                        <!--/.nav-collapse -->
                    </div>
                </div>
            </div>
        </header>
        <?php include 'notifications.php'; ?>