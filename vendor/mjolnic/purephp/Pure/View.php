<?php

/**
 * Basic PHP templating engine
 */
class Pure_View {

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
     * @var array Last loaded template options
     */
    public $options = array();

    /**
     *
     * @var array Last loaded template locals
     */
    public $locals = array();

    /**
     * Last loaded template content
     * @var string
     */
    public $content = null;

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
            $locals = Pure_Arr::merge($this->globals, $locals);
        } else {
            $locals = array();
        }
        $this->options = $options;
        $this->locals = $locals;
        $this->locals['tpl_name'] = $tpl;
        $this->locals['tpl_file'] = file_exists($tpl) ? $tpl : ($this->path . $tpl . $this->extension);
        $this->locals['tpl_id'] = Pure_Str::slugize($tpl);

        // Trigger event
        Pure_Dispatcher::getInstance()->trigger('tpl.before_load', array(), $this);

        extract($this->locals);

        $cwd = realpath(getcwd());

        // Change dir so the includes are always relative to this file
        chdir(realpath(dirname($this->locals['tpl_file'])));

        include $this->locals['tpl_file'];

        $this->content = ob_get_clean();

        // Trigger event
        Pure_Dispatcher::getInstance()->trigger('tpl.load', array(), $this);

        // restore current working dir
        chdir($cwd);

        return $this->content;
    }

    public function render($tpl, array $locals = array(), array $options = array()) {
        echo $this->load($tpl, $locals, $options);
    }

}