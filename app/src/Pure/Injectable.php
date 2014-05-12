<?php

abstract class Pure_Injectable implements Pure_IInjectable {

    /**
     *
     * @var Pure_App
     */
    protected $app;

    public function setApp(Pure_App $app) {
        $this->app = $app;
    }

    /**
     * @return Pure_App
     */
    public function getApp() {
        return $this->app;
    }

}
