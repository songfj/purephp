<?php

/**
 * Validator and sanitizer
 */
class Pure_V {

    /**
     * Checks if a variable exists inside an array and matches the given php filter or regular expression.
     * If it matches returns the variable value, otherwise returns $default
     * 
     * @param array $arr Associated array of values
     * @param string $key Array key name
     * @param mixed $default Default value if the variable is not set or regexp is false
     * @param mixed $validation FILTER_* constant value, regular expression or callable method/function (that returns a boolean i.e. is_string)
     * @return mixed The variable value
     */
    public static function check(array $arr, $key, $default = null, $validation = null) {
        if ($validation === true) {
            // has
            return isset($arr[$key]);
        }
        if (isset($arr[$key])) {
            $value = $arr[$key];
            if ($validation != null) {
                if (is_string($validation) && ($validation{0} == '/')) {
                    //regexp
                    return (preg_match($validation, $value) > 0) ? $value : $default;
                } elseif (is_int($validation)) {
                    // FILTER_* constant
                    return filter_var($value, $validation) ? $value : $default;
                } elseif (is_callable($validation)) {
                    // validation function
                    return $validation($value) ? $value : $default;
                } else {
                    // exact equal comparison
                    return ($validation === $value) ? $value : $default;
                }
            } else {
                return empty($value) ? $default : $value;
            }
        } else {
            return $default;
        }
    }

    public static function sanitize(array $arr, $removeHtml = true, $replacement = ' ', $replaceChars = array('>', '<', '\\', '/', '"', '\''), $trimChars = ' .,-_') {
        if ($removeHtml) {
            foreach ($arr as $k => $v) {
                $arr[$k] = trim(str_replace($replaceChars, $replacement, strip_tags($v)), $trimChars);
            }
        } else {
            foreach ($arr as $k => $v) {
                $arr[$k] = trim(str_replace($replaceChars, $replacement, $v), $trimChars);
            }
        }
        return $arr;
    }

    public static function validate(array $arr, array $validations) {
        $errors = array();

        foreach ($validations as $field => $validation) {
            if (empty($validation)) {
                continue;
            }
            if (!isset($arr[$field])) {
                $errors[] = $field;
                continue;
            }
            if (($validation == 'notempty') and empty($arr[$field])) {
                $errors[] = $field;
                continue;
            }
            if (is_callable($validation) and ( call_user_func($arr[$field]) == false)) {
                $errors[] = $field;
                continue;
            }
            if (is_numeric($validation) and ( !filter_var($arr[$field], $validation))) {
                $errors[] = $field;
                continue;
            }
            //die($validation);
            if (is_string($validation) and ( $validation{0} == '/') and ( preg_match($validation, $arr[$field]) == false)) {
                $errors[] = $field;
                continue;
            }
        }

        if (empty($errors)) {
            return true;
        }
        return $errors;
    }

}
