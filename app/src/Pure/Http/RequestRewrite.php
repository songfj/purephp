<?php

class Pure_Http_RequestRewrite {

    protected $backup = array();

    public function __construct($method = 'GET', $basepath = '', $uri = '/', array $query = array(), array $body = array(), array $files = array(), array $cookies = array(), $ip = '::1', $userAgent = 'PurePHP') {
        $this->backup = array(
            'server' => $_SERVER,
            'get' => $_GET,
            'post' => $_POST,
            'files' => $_FILES,
            'cookie' => $_COOKIE
        );

        $basepath = trim($basepath, '/ \\');
        $_SERVER['SCRIPT_NAME'] = strlen($basepath) > 0 ? ('/' . $basepath . '/index.php') : 'index.php';

        $_SERVER['REQUEST_METHOD'] = strtoupper($method);

        $_SERVER['REQUEST_URI'] = $uri;

        $GLOBALS['_GET'] = $query;
        $GLOBALS['_POST'] = $body;
        $GLOBALS['_FILES'] = $files;
        $GLOBALS['_COOKIE'] = $cookies;

        $_SERVER['REMOTE_ADDR'] = $ip;

        $_SERVER['HTTP_USER_AGENT'] = $userAgent;
    }

    public function restore() {
        foreach ($this->backup as $k => $v) {
            $GLOBALS["_" . strtoupper($k)] = $v;
        }
    }

}
