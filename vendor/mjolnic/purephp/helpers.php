<?php

/**
 * Dump the given variable and end execution of the script.
 *
 * @param mixed $var
 */
function dd($var)
{
    var_dump($var);
    die();
}

/*
 * Helpers based on Laravel ones
 */

/**
 * Get the default app instance
 * @return Pure_App
 */
function app()
{
    return Pure::app();
}

/**
 * Get the default DB
 * @return Redbean_Driver
 */
function db()
{
    return Pure::db();
}

/**
 * Get the fully qualified path to the app directory.
 * @param string $to
 */
function app_path($to = '')
{
    return Pure::path('app') . ltrim($to, '\\/');
}

/**
 * Get the fully qualified path to the root of the application installation.
 * @param string $to
 * @return string
 */
function base_path($to = '')
{
    return Pure::path('root') . ltrim($to, '\\/');
}

/**
 * Get the fully qualified path to the public directory.
 * @param string $to
 * @return string
 */
function public_path($to = '')
{
    return Pure::path('public') . ltrim($to, '\\/');
}

/**
 * Get the fully qualified path to the app/data directory.
 * @param string $to
 */
function storage_path($to = '')
{
    return Pure::path('data') . ltrim($to, '\\/');
}

/**
 * Generate a URL for an asset, using the content url as the base url
 * @param string $path
 */
function asset($path)
{
    return Pure::url('content') . ltrim($path, '/');
}


/**
 * Generate a fully qualified URL to the given path.
 * @param string $path
 * @param array $parameters
 * @param boolean $is_secure
 * @param boolean $use_rewrite_engine
 * @return string
 */
function url($path = '', $parameters = array(), $is_secure = null, $use_rewrite_engine = true)
{
    $baseUrl = $use_rewrite_engine ? Pure::url('rewrite_base') : Pure::url('base');
    $query = empty($parameters) ? '' : ('?' . http_build_query($parameters));
    return $is_secure ? (preg_replace('/^https?/', 'https', $baseUrl)) : ($baseUrl . ltrim($path, '/') . $query);
}