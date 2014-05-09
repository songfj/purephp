<?php

/**
 * Wrapper object for arrays.
 */
class Pure_Obj implements ArrayAccess {

    /**
     * Object properties
     * @var array 
     */
    protected $props = array();

    public function __construct(array $properties = array()) {
        $this->props = $properties;
    }

    public function __call($name, $arguments) {
        if (method_exists($this, $name)) {
            return call_user_func_array(array($this, $name), $arguments);
        } else if (isset($this->props[$name]) and is_callable($this->props[$name])) {
            return call_user_func_array($this->props[$name], $arguments);
        }
    }

    public function __isset($name) {
        return isset($this->props[$name]);
    }

    public function __get($name) {
        return $this->props[$name];
    }

    public function __set($name, $value) {
        $this->props[$name] = $value;
    }

    public function __unset($name) {
        unset($this->props[$name]);
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
        return $this->__unset($offset);
    }

    public function export() {
        return $this->props;
    }

    /**
     * 
     * @param array $properties
     * @return static|this
     */
    public function import(array $properties) {
        $this->props = array_merge($this->props, $properties);
        return $this;
    }

    public function __toString() {
        return print_r($this->props, true);
    }

    public static function forge(array $ctorArgs = array(), $className = null, $callback = null, array $callbackArgs = array()) {
        if (empty($className)) {
            $className = get_called_class();
        }

        $rClass = new ReflectionClass($className);
        $obj = $rClass->newInstanceArgs($ctorArgs);

        if (!empty($callback) and is_callable($callback)) {
            array_push($callbackArgs, $obj, $rClass, $className, $ctorArgs);
            return call_user_func_array($callback, $callbackArgs);
        } else {
            return $obj;
        }
    }

    /**
     * Takes a classname and returns the actual classname for an alias or just the classname
     * if it's a normal class.
     *
     * @param   string  classname to check
     * @return  string  real classname
     */
    public static function getRealClass($class) {
        static $classes = array();

        if (!array_key_exists($class, $classes)) {
            $reflect = new ReflectionClass($class);
            $classes[$class] = $reflect->getName();
        }

        return $classes[$class];
    }

    /**
     * Gets all the public vars for an object.  Use this if you need to get all the
     * public vars of $this inside an object.
     *
     * @return	array
     */
    public static function getPublicVars($obj) {
        return get_object_vars($obj);
    }

    /**
     * Retrieves all constants (or the specified one) from a class using Reflection
     * 
     * @param string $class_name
     * @param string $constant_name specific constant value
     * @return mixed 
     */
    public static function getConstants($class_name = null, $constant_name = null) {
        if (empty($class_name)) {
            $class_name = get_called_class();
        }
        $reflect = new ReflectionClass($class_name);
        $constants = $reflect->getConstants();

        if (!empty($constant_name)) {
            return $constants[$constant_name];
        } else {
            return $constants;
        }
    }

    /**
     * 
     * @param mixed $var
     * @return string primitive type or class name
     */
    public static function getType($var) {
        return is_object($var) ? get_class($var) : gettype($var);
    }

}