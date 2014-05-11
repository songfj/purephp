<?php

/**
 * Facade class. If not changed using Pure_App::setInstance,
 * all the facade methods references the default app instance
 */
class Pure_Facade {

    /**
     *
     * @param string $instanceName
     * @return Pure_App
     */
    public static function app($instanceName = 'default') {
        return Pure_App::getInstance($instanceName);
    }

    /**
     *
     * @return Pure_Http_Request
     */
    public static function req() {
        return static::app()->request();
    }

    /**
     *
     * @return Pure_Http_Response
     */
    public static function resp() {
        return static::app()->response();
    }

    /**
     *
     * @return Pure_Http_Router
     */
    public static function router() {
        return static::app()->router();
    }

    /**
     *
     * @param string|array $to
     * @param string $subject
     * @param string $body
     * @param string|array $from
     * @param string|array $bcc
     * @return Pure_Mail A new mail message instance
     */
    public static function mail($to, $subject, $body, $from = null, $bcc = null) {
        return static::app()->mail($to, $subject, $body, $from, $bcc);
    }

    /**
     *
     * @return Redbean_Driver
     */
    public static function db() {
        return static::app()->db();
    }

    
    /**
     * 
     * @return \Illuminate\Filesystem\Filesystem
     */
    public static function filesystem() {
        return static::app()->engine('filesystem');
    }

    
    /**
     * 
     * @return \Illuminate\Events\Dispatcher
     */
    public static function dispatcher() {
        return static::app()->engine('dispatcher');
    }

    /**
     *
     * @return string|Pure_Http_Response
     */
    public static function load($view, $locals = array(), $asResponse = false, $status = 200, $contentType = 'text/html') {
        $content = static::app()->view()->load($view, $locals);
        if ($asResponse === true) {
            static::app()->response()->body = $content;
            static::app()->response()->status($status);
            static::app()->response()->contentType($contentType);
            return static::app()->response();
        }
        return $content;
    }

    /**
     *
     * @return Pure_Http_Response
     */
    public static function send($view, $locals = array(), $status = 200, $contentType = 'text/html') {
        return static::app()->response()->send(static::load($view, $locals, false), $status, $contentType);
    }

    /**
     *
     * @return Pure_Http_Response
     */
    public static function send404($contentType = 'text/plain') {
        return static::app()->response()->send('', 404, $contentType);
    }

    /**
     * @param array|string $message JSON message as an associated array or JSON string
     * @return Pure_Http_Response
     */
    public static function sendJson($message = array(), $status = 200) {
        return static::app()->response()->send(is_array($message) ? json_encode($message) : $message, $status, 'application/json');
    }

    public static function config($name, $value = null) {
        if (func_num_args() > 1) {
            return static::app()->config($name, $value);
        } else {
            return static::app()->config($name);
        }
    }

    /**
     * Predefined urls: domain (root), base (root with folder), rewrite_base (default), content, current, current_query (with query string), previous, ...
     * @param string $name
     * @param string $value
     * @return string
     */
    public static function url($name = 'rewrite_base', $value = null) {
        if (func_num_args() > 1) {
            return static::app()->url($name, $value);
        } else {
            return static::app()->url($name);
        }
    }

    public static function urlIs($path = "") {
        if (static::router()->currentRoute->path == '*') {
            return false;
        }
        if (is_object(static::router()->currentRoute)) {
            return (preg_match(static::router()->currentRoute->regexp, ltrim($path, "/ ")) > 0);
        }
        return false;
    }

    public static function urlTo($path = "", $query = false, $queryExclude = array(), $queryEscape = true) {
        $path = trim($path, '/');
        $q = '';
        if ($query === true) {
            $query = array();
        }
        if (is_array($query)) {
            $q = static::req()->query($query, $queryExclude, $queryEscape);
        }
        return static::url('rewrite_base') . (empty($path) ? '' : ($path . '/')) . $q;
    }

    public static function linkTo($path = '', $content = '', $attributes = array(), $query = false, $queryExclude = array(), $queryEscape = true) {
        $html = '<a href="' . static::urlTo($path, $query, $queryExclude, $queryEscape) . '" ';

        $p = explode('?', $path);
        if (static::urlIs($p[0])) {
            $attributes['class'] = isset($attributes['class']) ? ($attributes['class'] . ' active') : 'active';
        }

        foreach ($attributes as $k => $v) {
            $html .= ' ' . $k . '="' . $v . '"';
        }
        return $html . '>' . $content . '</a>';
    }

    /**
     * Predefined paths: root, app, vendor, data, logs, content, uploads, views, ...
     * @param string $name
     * @param string $value
     * @return string
     */
    public static function path($name = 'root', $value = null) {
        if (func_num_args() > 1) {
            return static::app()->path($name, $value);
        } else {
            return static::app()->path($name);
        }
    }

    public static function engine($name, $value = null) {
        if (func_num_args() > 1) {
            return static::app()->engine($name, $value);
        } else {
            return static::app()->engine($name);
        }
    }

    public static function flag($name, $enable = null) {
        return static::app()->flag($name, $enable);
    }

    /**
     * Environment variable getter/setter
     *
     * @param string $name
     * @param string $strValue
     * @return string|false
     */
    public static function env($name, $strValue = null) {
        if (func_num_args() > 1) {
            $_ENV[$name] = $strValue;
            putenv($name . '=' . $strValue);
        } else {
            return getenv($name);
        }
    }

    /**
     * Gets or sets application environment variables (instances, flags, etc),
     *
     * @param string $name
     * @param mixed $value
     * @return mixed
     */
    public static function data($name, $value = null) {
        if (func_num_args() > 1) {
            return static::app()->data($name, $value);
        } else {
            return static::app()->data($name);
        }
    }

    /**
     * Sets template global variables (template scope, not php globals)
     *
     * @param string $key
     * @param mixed $value
     * @return mixed
     */
    public static function share($key, $value = null) {
        return self::app()->view()->set($key, $value);
    }

    /**
     * Gets template global variables (template scope, not php globals)
     *
     * @param string $key
     * @param mixed $default
     * @return mixed
     */
    public static function shared($key, $default = null) {
        return self::app()->view()->get($key, $default);
    }

    /**
     * Adds new middleware to the stack
     * @param callable $callback
     */
    public static function bind($callback) {
        static::app()->bind($callback);
    }

    /**
     * Binds a HTTP request to a callback
     * @param string $method HTTP verb
     * @param string $path Path expression
     * @param callable $callback
     * @param array $options
     * @return \Pure_Http_Route
     * @throws InvalidArgumentException
     */
    public static function map($method, $path, $callback, array $options = array()) {
        return static::router()->map($method, $path, $callback, $options);
    }

    /**
     * Binds a GET HTTP request to a callback
     * @param string $path Path expression
     * @param callable $callback
     * @param array $options
     * @return \Pure_Http_Route
     * @throws InvalidArgumentException
     */
    public static function get($path, $callback, array $options = array()) {
        return static::map("get", $path, $callback, $options);
    }

    /**
     * Binds a POST HTTP request to a callback
     * @param string $path Path expression
     * @param callable $callback
     * @param array $options
     * @return \Pure_Http_Route
     * @throws InvalidArgumentException
     */
    public static function post($path, $callback, array $options = array()) {
        return static::map("post", $path, $callback, $options);
    }

    /**
     * Binds a PUT HTTP request to a callback
     * @param string $path Path expression
     * @param callable $callback
     * @param array $options
     * @return \Pure_Http_Route
     * @throws InvalidArgumentException
     */
    public static function put($path, $callback, array $options = array()) {
        return static::map("put", $path, $callback, $options);
    }

    /**
     * Binds a DELETE HTTP request to a callback
     * @param string $path Path expression
     * @param callable $callback
     * @param array $options
     * @return \Pure_Http_Route
     * @throws InvalidArgumentException
     */
    public static function delete($path, $callback, array $options = array()) {
        return static::map("delete", $path, $callback, $options);
    }

    /**
     * Binds an OPTIONS HTTP request to a callback
     * @param string $path Path expression
     * @param callable $callback
     * @param array $options
     * @return \Pure_Http_Route
     * @throws InvalidArgumentException
     */
    public static function options($path, $callback, array $options = array()) {
        return static::map("options", $path, $callback, $options);
    }

    /**
     * Binds a HTTP request to a callback (for any HTTP verb)
     * @param string $path Path expression
     * @param callable $callback
     * @param array $options
     * @return \Pure_Http_Route
     * @throws InvalidArgumentException
     */
    public static function any($path, $callback, array $options = array()) {
        return static::map(null, $path, $callback, $options);
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
    public static function input($key, $default = null, $validation = null) {
        return static::req()->input($key, $default, $validation);
    }

    /**
     * @return Pure_Html
     */
    public static function html() {
        return Pure_Html::getInstance();
    }

    /**
     * Adds a log record at an arbitrary level.
     *
     * This method allows for compatibility with common interfaces.
     *
     * @param  mixed   $level   The log level
     * @param  string  $message The log message
     * @param  array   $context The log context
     * @return Boolean|\Monolog\Logger Whether the record has been processed or the Monolog\Logger instance
     */
    public static function log($level = null, $message = null, array $context = array()) {
        if (empty($level)) {
            return static::app()->engine('logger');
        }
        return static::app()->engine('logger')->log($level, $message, $context);
    }

    public static function cache($key, $expire_time = 3600, $generator_fn = null, $generator_args = array()) {
        $content = false;
        $file = static::path('cache') . $key;
        $file_time = is_readable($file) ? filemtime($file) : 0;

        if (is_readable($file) && ((time() - $expire_time) < $file_time)) {
            $content = unserialize(file_get_contents($file));
        } else {
            if ($generator_fn) {
                $content = call_user_func_array($generator_fn, $generator_args);
            }
            file_put_contents($file, serialize($content));
        }
        return $content;
    }

    /**
     * @return Pure_Session
     */
    public static function session() {
        if (static::engine('session') == false) {
            static::engine('session', new Pure_Session(static::path('root')));
        }
        return static::engine('session');
    }

    /**
     * @return Pure_Flash
     */
    public static function flash() {
        if (static::engine('flash') == false) {
            static::engine('flash', new Pure_Flash(static::session()));
        }
        return static::engine('flash');
    }

    /**
     * Inmediately redirect to the given url with optional status code defaulting to 302 "Found"
     * @param string $url
     * @param int $status
     */
    public static function redirect($url, $status = 302) {
        return static::resp()->redirect($url, $status);
    }

    /**
     * Writes a flash message and inmediately redirect to the given url with optional status code defaulting to 302 "Found"
     * @param string $url
     * @param string $level
     * @param string $message
     * @param array $context
     * @param int $status
     */
    public static function flashRedirect($url, $level, $message, array $context = array(), $status = 302) {
        static::flash()->write($level, $message, $context);
        static::redirect($url, $status);
    }

    public static function messages($name = null, $value = null) {
        $messages = static::app()->data('messages');
        $numargs = func_num_args();
        if ($numargs == 1) {
            $name = mb_strtoupper($name);
            return isset($messages[$name]) ? $messages[$name] : '{' . $name . '}';
        } else {
            $name = mb_strtoupper($name);
            $messages[$name] = $value;
        }
        return $messages;
    }

}
