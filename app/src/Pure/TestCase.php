<?php

class Pure_TestCase extends PHPUnit_Framework_TestCase {

    public function testAppCreation() {
        global $bootstrap;
        //dd($bootstrap);
        //$appClass = $bootstrap->appClass;
        $app = new $bootstrap->appClass($bootstrap->loader, $bootstrap->config);
        $this->assertInstanceOf('Pure_App', $app);
        return $app;
    }

}
