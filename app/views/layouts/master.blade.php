<!doctype html>
<html lang="en" class="sticky-footer-container">
    <head>
        <meta charset="utf-8">
        <!--<meta http-equiv="X-UA-Compatible" content="IE=edge">-->
        <meta name="viewport" content="width=device-width, user-scalable=no, initial-scale=1.0, maximum-scale=1">

        <title>PurePHP</title>

        <meta name="description" content="">
        <!--<meta name="keywords" content="">-->
        <meta name="author" content="">
        <meta name="robots" content="INDEX, FOLLOW">

        <!--<link rel="canonical" href="<?php echo url() ?>">-->
        <link rel="shortcut icon" href="<?php echo asset('img/favicon.png'); ?>">

        <style type="text/css" id="relativecss">html,body{position:static} body * {position: relative}</style>

        <link href='http://fonts.googleapis.com/css?family=Noto+Sans:400,700,400italic,700italic' rel='stylesheet' type='text/css'>
        <link href="<?php echo asset('css/app.min.css') ?>" rel="stylesheet">

        <!-- Cross-browser compatibility scripts: -->
        <script type='text/javascript' src="<?php echo asset('vendor/js/compat.min.js') ?>"></script>

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
        
        @include('partials.header')
        @include('partials.gallery')
        @include('partials.notifications')
        @yield('main')
        @include('partials.footer')
        @yield('body_append')
        
        <div id="happycookies" style="display:none">
            <div class="happycookies-close"><span>OK</span></div>
            <div class="happycookies-text">
                We use cookies to enhance your experience in our website. By continuing to visit this site you agree to our use
                of cookies <a href="#" target="_blank">Learn more</a>
            </div>
        </div>
        <script src="<?php echo asset('js/app.min.js'); ?>"></script>
    </body>
</html>