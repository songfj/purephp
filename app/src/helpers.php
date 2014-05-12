<?php

/*
 * Helpers based on Laravel ones
 */

/**
 * Get the default app instance
 * @return Pure_App
 */
function app() {
    return Pure_Facade::app();
}

/**
 * Get the default DB
 * @return Redbean_Driver
 */
function db() {
    return Pure_Facade::db();
}

/**
 * Return the value of param name when present, otherwise return false.
 *
 * Lookup is performed in the following order: $req->body, $req->query
 *
 * Implements the expressjs req.param(name) method
 *
 * @param array $arr Associated array of values
 * @param string $key Array key name
 * @param mixed $default Default value if the variable is not set or regexp is false
 * @param mixed $validation FILTER_* constant value, regular expression or callable method/function (that returns a boolean i.e. is_string)
 * @return mixed The variable value
 */
function input($key, $default = null, $validation = null) {
    return Pure_Facade::input($key, $default, $validation);
}

/**
 * Get the fully qualified path to the app directory.
 * @param string $to
 */
function app_path($to = '') {
    return Pure_Facade::path('app') . ltrim($to, '\\/');
}

/**
 * Get the fully qualified path to the root of the application installation.
 * @param string $to
 * @return string
 */
function base_path($to = '') {
    return Pure_Facade::path('root') . ltrim($to, '\\/');
}

/**
 * Get the fully qualified path to the public directory.
 * @param string $to
 * @return string
 */
function public_path($to = '') {
    return Pure_Facade::path('public') . ltrim($to, '\\/');
}

/**
 * Get the fully qualified path to the app/data directory.
 * @param string $to
 */
function storage_path($to = '') {
    return Pure_Facade::path('data') . ltrim($to, '\\/');
}

/**
 * Generate a URL for an asset, using the content url as the base url
 * @param string $path
 */
function asset($path = '') {
    return Pure_Facade::url('content') . ltrim($path, '/');
}

/**
 * Generate a fully qualified URL to the given path.
 * @param string $path
 * @param array $parameters
 * @param boolean $is_secure
 * @param boolean $use_rewrite_engine
 * @return string
 */
function url($path = '', $parameters = array(), $is_secure = null, $use_rewrite_engine = true) {
    $baseUrl = $use_rewrite_engine ? Pure_Facade::url('rewrite_base') : Pure_Facade::url('base');
    $query = empty($parameters) ? '' : ('?' . http_build_query($parameters));
    return $is_secure ? (preg_replace('/^https?/', 'https', $baseUrl)) : ($baseUrl . ltrim($path, '/') . $query);
}

/**
 * HTML Element
 * @param string $tagname
 * @param array $attr
 * @param string|array $content String or array (for selects, uls, tables, navs, audio or video sources, ...
 * @return string
 */
function el($tagname, $attr = array(), $content = null) {
    return Pure_Facade::html()->_tag($tagname, $attr, $content);
}

/**
 * HTML Element Open
 * @param string $tagname
 * @param array $attr
 * @return string
 */
function el_open($tagname, $attr = array()) {
    return Pure_Facade::html()->_tagOpen($tagname, $attr);
}

/**
 * HTML Element Close
 * @param string $tagname
 * @return string
 */
function el_close($tagname) {
    return Pure_Facade::html()->_tagClose($tagname);
}

function link_to($path = '', $content = '', $attributes = array(), $query = false, $queryExclude = array(), $queryEscape = true) {
    return Pure_Facade::linkTo($path, $content, $attributes, $query, $queryExclude, $queryEscape);
}

/**
 * Humanizes a camelized string, separating words (if not using a word separator)
 * 
 * @param string $str Camelized string
 * @param string $glue Delimiter that will be used for separating words
 * @return string 
 */
function humanize($str, $glue = ' ') {
    preg_match_all('!([A-Z][A-Z0-9]*(?=$|[A-Z][a-z0-9])|[A-Za-z][a-z0-9]+)!', $str, $matches);
    return implode($glue, (isset($matches[0]) and is_array($matches[0])) ? $matches[0] : array());
}

/**
 * Merges arrays recursively with the same behaviour as array_merge,
 * not converting a non-array value in an array with the multiple different values
 * @param mixed $array_1 $array_2, $array_3, ...
 * @return type 
 */
function array_merge_recursive_replace() {
    if (func_num_args() < 2) {
        trigger_error(__METHOD__ . ' needs two or more array arguments', E_USER_WARNING);
        return;
    }
    $arrays = func_get_args();
    $merged = array();
    while ($arrays) {
        $array = array_shift($arrays);
        if (!is_array($array)) {
            trigger_error(__METHOD__ . ' encountered a non array argument', E_USER_WARNING);
            return;
        }
        if (!$array)
            continue;
        foreach ($array as $key => $value)
            if (is_string($key))
                if (is_array($value) && array_key_exists($key, $merged) && is_array($merged[$key]))
                    $merged[$key] = call_user_func(__METHOD__, $merged[$key], $value);
                else
                    $merged[$key] = $value;
            else
                $merged[] = $value;
    }
    return $merged;
}
