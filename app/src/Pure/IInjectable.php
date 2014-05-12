<?php

interface Pure_IInjectable {

    public function setApp(Pure_App $app);

    /**
     * @return Pure_App
     */
    public function getApp();
}
