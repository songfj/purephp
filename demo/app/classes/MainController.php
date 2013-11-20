<?php

class MainController {

    /**
     * 
     * @param pure_http_request $req
     * @param pure_http_response $resp
     * @param pure_http_route $route
     * @param app $app
     */
    public static function index($req, $resp, $route, $app) {
        pure::flash()->write('success', 'This is a flash message test.');
        pure::send('home');
    }

    /**
     * 
     * @param pure_http_request $req
     * @param pure_http_response $resp
     * @param pure_http_route $route
     * @param app $app
     */
    public static function handle($req, $resp, $route, $app) {
        pure::send('404', array(), array(), 404);
    }

}