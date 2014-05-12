<?php

abstract class Pure_TestCase extends PHPUnit_Framework_TestCase {

    public function appProvider() {
        return array(
            array($this->createApp('app1', 'get', '/purephp/', '/test1/', array('v' => 1))),
            array($this->createApp('app2', 'get', '/mjolnic/purephp/', '/test2/', array('p' => 2))),
            array($this->createApp('app3', 'get', '/mjolnic/purephp/', '/test2/', array('p' => 2))),
        );
    }

    protected function createApp($name, $method = 'GET', $basepath = '', $uri = '/', array $query = array(), array $body = array(), array $files = array(), array $cookies = array(), $ip = '::1', $userAgent = 'PurePHP') {
        global $bootstrap;

        $requestRewrite = new Pure_Http_RequestRewrite($method, $basepath, $uri, $query, $body, $files, $cookies, $ip, $userAgent);

        $config = $bootstrap->config;
        $config['name'] = $name;
        $app = new $bootstrap->appClass($bootstrap->loader, $config);
        $app->container('requestRewrite', $requestRewrite);
        return $app;
    }

}
