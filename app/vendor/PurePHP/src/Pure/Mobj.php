<?php

/**
 * Magic Object
 * A wrapper object where you can define magic getters and setters for
 * individual properties
 */
class Pure_Mobj extends Pure_Obj {

    public function __get($name) {
        $fn = 'get_' . $name;
        if (method_exists($this, $fn)) {
            return $this->$fn();
        } else {
            return $this->getProp($name);
        }
    }

    public function __set($name, $value) {
        $fn = 'set_' . $name;
        if (method_exists($this, $fn)) {
            $this->$fn($value);
        } else {
            $this->setProp($name, $value);
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
     * Returns the real property value without triggering the magic getter
     * @param string $name
     * @return mixed
     */
    public function getProp($name) {
        return parent::__get($name);
    }

    /**
     * Sets a property value without triggering the magic setter
     * @param string $name
     * @return static|this
     */
    public function setProp($name, $value) {
        parent::__set($name, $value);
        return $this;
    }

    /**
     * Imports properties using magic getter functions (if exists)
     * @param array $properties
     * @return static|this
     */
    public function import(array $properties) {
        foreach ($properties as $k => $v) {
            $this->$k = $v;
        }
        return $this;
    }

    /**
     * Exports properties using magic getter functions (if exists)
     * @return array
     */
    public function export() {
        $props = array();
        foreach ($this->props as $k => $v) {
            $props[$k] = $this->$k;
        }
        return $props;
    }

    /**
     * Exports properties without using magic getter functions
     * @return array
     */
    public function exportProps() {
        return parent::export();
    }

    /**
     * Imports properties without using magic setter functions
     * @param array $properties
     * @return MagicObject
     */
    public function importProps(array $properties) {
        return parent::import($properties);
    }

}