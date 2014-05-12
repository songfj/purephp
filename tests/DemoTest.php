<?php

class DemoTest extends Pure_TestCase {
    
    /**
     * @dataProvider appProvider
     */
    public function testRequestMethod(Pure_App $app) {
        $this->assertEquals('GET', $app->request()->method);
    }

}
