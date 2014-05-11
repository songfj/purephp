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
function asset($path) {
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

function link_to($path = '', $content = '', $attributes = array(), $query = false, $queryExclude = array(), $queryEscape = true){
    return Pure_Facade::linkTo($path, $content, $attributes, $query, $queryExclude, $queryEscape);
}