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
        if (isset($this->props[$name]) and is_callable($this->props[$name])) {
            return call_user_func_array($this->props[$name], $arguments);
        }
    }

    public function __isset($name) {
        return isset($this->props[$name]);
    }

    public function __get($name) {
        return $this->getProperty($name);
    }

    public function __set($name, $value) {
        $this->setProperty($name, $value);
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

    public function getProperties() {
        return $this->props;
    }

    /**
     * 
     * @param array $properties
     * @return static|this
     */
    public function setProperties(array $properties) {
        $this->props = $properties;
        return $this;
    }

    /**
     * Returns the real property value without triggering the magic getter
     * @param string $name
     * @return mixed
     */
    public function getProperty($name) {
        return $this->props[$name];
    }

    /**
     * Sets a property value without triggering the magic setter
     * @param string $name
     * @return static|this
     */
    public function setProperty($name, $value) {
        $this->props[$name] = $value;
        return $this;
    }

    public function __toString() {
        return print_r($this->props, true);
    }

}
