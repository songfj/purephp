<?php

class MainController extends Pure_Controller {

    /**
     * 
     * @param Pure_Http_Request $req
     * @param Pure_Http_Response $resp
     * @param Pure_Http_Route $route
     * @param app $app
     */
    public function index($req, $resp, $route, $app) {
        //App::flash()->write('success', 'This is a flash message test.');
        App::send('home');
    }

    /**
     * 
     * @param Pure_Http_Request $req
     * @param Pure_Http_Response $resp
     * @param Pure_Http_Route $route
     * @param app $app
     */
    public function handle($req, $resp, $route, $app) {
        App::send('404', array(), 404);
    }

}
