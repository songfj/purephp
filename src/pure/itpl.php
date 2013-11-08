<?php

/**
 * Basic templating engine interface
 */
interface pure_itpl {

    public function load($tpl, array $locals = array(), array $options = array());

    public function render($tpl, array $locals = array(), array $options = array());
}