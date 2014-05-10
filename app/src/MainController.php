<?php

class MainController {

    /**
     * 
     * @param Pure_Http_Request $req
     * @param Pure_Http_Response $resp
     * @param Pure_Http_Route $route
     * @param app $app
     */
    public static function index($req, $resp, $route, $app) {
        Pure::flash()->write('success', 'This is a flash message test.');
        Pure::send('home');
    }

    /**
     * 
     * @param Pure_Http_Request $req
     * @param Pure_Http_Response $resp
     * @param Pure_Http_Route $route
     * @param app $app
     */
    public static function handle($req, $resp, $route, $app) {
        Pure::send('404', array(), array(), 404);
    }

}