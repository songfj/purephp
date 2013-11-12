<?php

/**
 * A single route match
 */
class pure_http_route {

    /**
     *
     * @var string 
     */
    public $path = '';

    /**
     *
     * @var string 
     */
    public $method = 'GET';

    /**
     *
     * @var array
     */
    public $callbacks = array();

    /**
     *
     * @var array
     */
    public $keys = array();

    /**
     * This property is an array containing properties mapped to the named route
     * "parameters". For example if you have the route /user/:name, then the
     * "name" property is available to you as $req->params["name"].
     * 
     * When a regular expression is used for the route definition, capture groups
     * are provided in the array using $req->params[N], where N is the nth capture
     * group. This rule is applied to unnamed wild-card matches with string
     * routes such as `/file/*`
     *
     * @var array
     */
    public $params = array();

    /**
     *
     * @var string 
     */
    public $regexp = '';

    /**
     *
     * @var array
     */
    public $options = array('sensitive' => false, 'strict' => false);

    public function __construct(array $data = array()) {
        $data = pure_arr::merge(array(
                    'path' => false,
                    'method' => false,
                    'callbacks' => array(),
                    'keys' => array(),
                    'regexp' => false,
                    'options' => array('sensitive' => false, 'strict' => false)
                        ), $data);

        $this->path = $data['path'];
        $this->method = $data['method'];
        $this->callbacks = $data['callbacks'];
        $this->keys = $data['keys'];
        $this->regexp = $data['regexp'];
        $this->options = $data['options'];
    }

    /**
     * 
     * @param string $name
     * @return string
     */
    public function param($name) {
        return isset($this->params[$name]) ? $this->params[$name] : false;
    }

    /**
     * Is the path case sensitive?
     * @return boolean
     */
    public function isCaseSensitive() {
        return isset($this->options['sensitive']) and ($this->options['sensitive'] == true);
    }

    /**
     * If strict, paths begining with or without slash / would be different,
     * if not, will match the same.
     * @return boolean
     */
    public function isStrictMatch() {
        return isset($this->options['strict']) and ($this->options['strict'] == true);
    }

}