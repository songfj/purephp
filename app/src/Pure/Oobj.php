<?php

/**
 * Wrapper object that can have overloaded magic getters, setters, issets and unsets
 * for each individual property.
 */
class Pure_Oobj extends Pure_Obj {

    public function __get($name) {
        $fn = 'get_' . $name;
        if (method_exists($this, $fn)) {
            return $this->$fn();
        } else {
            return $this->getProperty($name);
        }
    }

    public function __set($name, $value) {
        $fn = 'set_' . $name;
        if (method_exists($this, $fn)) {
            $this->$fn($value);
        } else {
            $this->setProperty($name, $value);
        }
    }

    public function __isset($name) {
        if (method_exists($this, 'get_' . $name)) {
            return true;
        }
        $fn = 'isset_' . $name;
        if (method_exists($this, $fn)) {
            return $this->$fn();
        } else {
            return parent::__isset($name);
        }
    }

    public function __unset($name) {
        $fn = 'unset_' . $name;
        if (method_exists($this, $fn)) {
            return $this->$fn();
        } else {
            return parent::__unset($name);
        }
    }

    /**
     * Imports properties using magic getter functions (if exists)
     * @param array $properties
     * @return static|this
     */
    public function box(array $properties) {
        foreach ($properties as $k => $v) {
            $this->$k = $v;
        }
        return $this;
    }

    /**
     * Exports properties using magic getter functions (if exists)
     * @return array
     */
    public function unbox() {
        $props = array();
        foreach ($this->props as $k => $v) {
            $props[$k] = $this->$k;
        }
        return $props;
    }

}
