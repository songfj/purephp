<?php

/**
 * Basic PHP templating engine
 */
class pure_tpl implements pure_itpl {

    /**
     *
     * @var string Templates path
     */
    public $path = null;

    /**
     *
     * @var string Default file extension
     */
    public $extension = '.php';

    /**
     *
     * @var array Globally available template vars
     */
    public $globals = array();

    /**
     * 
     * @param string $path Default file path
     * @param string $extension Default file extension
     */
    public function __construct($path, $extension = '.php') {
        $this->path = $path;
        $this->extension = $extension;
    }

    public function __get($name) {
        return $this->globals[$name];
    }

    public function __set($name, $value = null) {
        $this->globals[$name] = $value;
    }

    public function get($name) {
        return $this->__get($name);
    }

    public function set($name, $value = null) {
        if (is_array($name)) {
            foreach ($name as $k => $v) {
                $this->__set($k, $v);
            }
        } else {
            $this->__set($name, $value);
        }
    }

    public function load($tpl, array $locals = array(), array $options = array()) {
        ob_start();
        if (($locals !== null) and is_array($locals)) {
            $locals = pure_arr::merge($this->globals, $locals);
        } else {
            $locals = array();
        }
        extract($locals);
        
        $tpl_file = file_exists($tpl) ? $tpl : ($this->path . $tpl . $this->extension);
        $tpl_id = pure_str::slugize($tpl);
        
        $cwd = realpath(getcwd());
        
        // Change dir so the includes are always relative to this file
        chdir(realpath(dirname($tpl_file)));
        
        include $tpl_file;
        $content = ob_get_clean();
        
        // restore current working dir
        chdir($cwd);
        return $content;
    }

    public function render($tpl, array $locals = array(), array $options = array()) {
        echo $this->load($tpl, $locals, $options);
    }

}