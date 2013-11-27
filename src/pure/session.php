<?php

class pure_session implements ArrayAccess {

    protected $name;
    protected $prefix;
    protected $started = false;

    /**
     *
     * @var pure_session 
     */
    protected static $instance = null;

    public function __construct($name = 'default', $prefix = 'sess_', $autostart = false) {
        $this->name = $prefix . md5(strtolower($name));
        if ($autostart) {
            $this->start();
        }
    }

    /**
     * 
     * @return pure_session
     */
    public static function getInstance() {
        if (self::$instance == null) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    public function __get($name) {
        return pure_arr::check($_SESSION, $name, false, null);
    }

    public function get($name) {
        return $this->__get($name);
    }

    public function getOnce($name) {
        $val = $_SESSION[$name];
        unset($_SESSION[$name]);
        return $val;
    }

    public function __set($name, $value) {
        $_SESSION[$name] = $value;
    }

    public function set($name, $value) {
        $this->__set($name, $value);
    }

    public function __isset($name) {
        return isset($_SESSION[$name]);
    }

    public function has($name) {
        return $this->__isset($name);
    }

    public function __unset($name) {
        unset($_SESSION[$name]);
    }

    public function remove($name) {
        $this->__unset($name);
    }

    public function offsetExists($offset) {
        return $this->__isset($offset);
    }

    public function offsetGet($offset) {
        return $this->__get($offset);
    }

    public function offsetSet($offset, $value) {
        $this->__set($offset, $value);
    }

    public function offsetUnset($offset) {
        $this->__unset($offset);
    }

    public function is($name) {
        return $this->name == ($this->prefix . md5(strtolower($name)));
    }

    public function isStarted() {
        return ($this->started === true);
    }

    public function getName() {
        return $this->name;
    }

    protected function setName($name) {
        return $this->name = ($this->prefix . md5(strtolower($name)));
    }

    public function start($name = null) {
        if (!empty($name)) {
            $this->setName($name);
        }
        if (session_id() != '') {
            session_write_close();
        }
        $oldsession = session_name($this->name); //cookie name
        session_start();
        $this->started = true;
        return $oldsession;
    }

    public function close() {
        session_write_close();
        $this->started = false;
    }

    public function destroy() {
        $this->started = false;
        return session_destroy();
    }

}