<?php

/**
 * Facade class. If not changed using pure_app::setInstance,
 * all the facade methods references the default app instance
 */
class pure {

    /**
     * 
     * @param string $instanceName
     * @return pure_app
     */
    public static function app($instanceName = 'default') {
        return pure_app::getInstance($instanceName);
    }

    /**
     * 
     * @return pure_http_request
     */
    public static function req() {
        return self::app()->request();
    }

    /**
     * 
     * @return pure_http_response
     */
    public static function resp() {
        return self::app()->response();
    }

    /**
     * 
     * @return pure_http_router
     */
    public static function router() {
        return self::app()->router();
    }

    /**
     * 
     * @return pure_imail|pure_mail
     */
    public static function mail() {
        return self::app()->mail();
    }

    /**
     * Listens to an event
     * 
     * @param string $event The event name
     * @param callable $handler Callable function to be triggered when the event is emited
     * @param int $priority Handler priority
     * @param mixed $emitter Object to listen to
     */
    public static function on($event, $handler, $priority = 0, $emitter = null) {
        return pure_dispatcher::getInstance()->on($event, $handler, $priority, $emitter);
    }

    /**
     * 
     * @param string $event Event to be unlistened
     * @param mixed $emitter Object that emits the event, if null a global event is unlistened
     * @return int Total of unregistered handlers
     */
    public static function off($event, $emitter = null) {
        return pure_dispatcher::getInstance()->off($event, $emitter);
    }

    /**
     * 
     * @param string $event Event name
     * @param array $context
     * @param mixed $emitter Object that emits the event, if null a global event is emitted
     * @return int The total handlers that listened to the event
     */
    public static function trigger($event, array $context = array(), $emitter = null) {
        return pure_dispatcher::getInstance()->trigger($event, $context, $emitter);
    }

    /**
     * 
     * @return string|pure_http_response
     */
    public static function load($tpl, $locals = array(), $options = array(), $asResponse = false, $status = 200, $contentType = 'text/html') {
        $content = self::app()->templating()->load($tpl, $locals, $options);
        if ($asResponse === true) {
            self::app()->response()->body = $content;
            self::app()->response()->status($status);
            self::app()->response()->contentType($contentType);
            return self::app()->response();
        }
        return $content;
    }

    /**
     * 
     * @return pure_http_response
     */
    public static function send($tpl, $locals = array(), $options = array(), $status = 200, $contentType = 'text/html') {
        return self::app()->response()->send(self::load($tpl, $locals, $options), $status, $contentType);
    }

    /**
     * 
     * @return pure_http_response
     */
    public static function send404($contentType = 'text/plain') {
        return self::app()->response()->send('', 404, $contentType);
    }

    /**
     * @param array|string $message JSON message as an associated array or JSON string
     * @return pure_http_response
     */
    public static function sendJson($message = array(), $status = 200) {
        return self::app()->response()->send(is_array($message) ? json_encode($message) : $message, $status, 'application/json');
    }

    public static function config($name, $value = null) {
        if (func_num_args() > 1) {
            return self::app()->config($name, $value);
        } else {
            return self::app()->config($name);
        }
    }

    /**
     * Predefined urls: domain, base, baserw (default), content, assets, uploads, views, current, current_query (with query string), previous, ...
     * @param string $name
     * @param string $value
     * @return string
     */
    public static function url($name = 'baserw', $value = null) {
        if (func_num_args() > 1) {
            return self::app()->url($name, $value);
        } else {
            return self::app()->url($name);
        }
    }

    public static function urlIs($path = "") {
        if (self::router()->currentRoute->path == '*') {
            return false;
        }
        if (is_object(self::router()->currentRoute)) {
            return (preg_match(self::router()->currentRoute->regexp, ltrim($path, "/ ")) > 0);
        }
        return false;
    }

    public static function urlTo($path = "", $query = false, $queryExclude = array(), $escape = true) {
        $path = trim($path, '/');
        $q = '';
        if ($query === true) {
            $query = array();
        }
        if (is_array($query)) {
            $q = self::req()->query($query, $queryExclude, $escape);
        }
        return self::url('baserw') . (empty($path) ? '' : ($path . '/')) . $q;
    }

    public static function linkTo($path = '', $content = '', $attributes = array(), $query = false, $queryExclude = array(), $escape = true) {
        $html = '<a href="' . self::urlTo($path, $query, $queryExclude, $escape) . '" ';

        $p = explode('?', $path);
        if (self::urlIs($p[0])) {
            $attributes['class'] = isset($attributes['class']) ? ($attributes['class'] . ' active') : 'active';
        }

        foreach ($attributes as $k => $v) {
            $html.=' ' . $k . '="' . $v . '"';
        }
        return $html . '>' . $content . '</a>';
    }

    /**
     * Predefined paths: root, app, vendor, data, logs, content, assets, uploads, views, ...
     * @param string $name
     * @param string $value
     * @return string
     */
    public static function path($name = 'root', $value = null) {
        if (func_num_args() > 1) {
            return self::app()->path($name, $value);
        } else {
            return self::app()->path($name);
        }
    }

    public static function engine($name, $value = null) {
        if (func_num_args() > 1) {
            return self::app()->engine($name, $value);
        } else {
            return self::app()->engine($name);
        }
    }

    public static function flag($name, $enable = null) {
        return self::app()->flag($name, $enable);
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
            return self::app()->data($name, $value);
        } else {
            return self::app()->data($name);
        }
    }

    /**
     * Gets or sets template global variables (template scope, not php globals)
     * 
     * @param string $name
     * @param mixed $value
     * @return mixed
     */
    public static function globals($name, $value = null) {
        if (is_array($name) or (func_num_args() > 1)) {
            return self::app()->templating()->set($name, $value);
        } else {
            return self::app()->templating()->get($name);
        }
    }

    /**
     * Adds new middleware to the stack
     * @param callable $callback
     */
    public static function bind($callback) {
        self::app()->bind($callback);
    }

    /**
     * Binds a HTTP request to a callback
     * @param string $method HTTP verb
     * @param string $path Path expression
     * @param callable $callback
     * @param array $options
     * @return \pure_http_route
     * @throws InvalidArgumentException
     */
    public static function map($method, $path, $callback, array $options = array()) {
        return self::router()->map($method, $path, $callback, $options);
    }

    /**
     * Binds a GET HTTP request to a callback
     * @param string $path Path expression
     * @param callable $callback
     * @param array $options
     * @return \pure_http_route
     * @throws InvalidArgumentException
     */
    public static function get($path, $callback, array $options = array()) {
        return self::map("get", $path, $callback, $options);
    }

    /**
     * Binds a POST HTTP request to a callback
     * @param string $path Path expression
     * @param callable $callback
     * @param array $options
     * @return \pure_http_route
     * @throws InvalidArgumentException
     */
    public static function post($path, $callback, array $options = array()) {
        return self::map("post", $path, $callback, $options);
    }

    /**
     * Binds a PUT HTTP request to a callback
     * @param string $path Path expression
     * @param callable $callback
     * @param array $options
     * @return \pure_http_route
     * @throws InvalidArgumentException
     */
    public static function put($path, $callback, array $options = array()) {
        return self::map("put", $path, $callback, $options);
    }

    /**
     * Binds a DELETE HTTP request to a callback
     * @param string $path Path expression
     * @param callable $callback
     * @param array $options
     * @return \pure_http_route
     * @throws InvalidArgumentException
     */
    public static function delete($path, $callback, array $options = array()) {
        return self::map("delete", $path, $callback, $options);
    }

    /**
     * Binds an OPTIONS HTTP request to a callback
     * @param string $path Path expression
     * @param callable $callback
     * @param array $options
     * @return \pure_http_route
     * @throws InvalidArgumentException
     */
    public static function options($path, $callback, array $options = array()) {
        return self::map("options", $path, $callback, $options);
    }

    /**
     * Binds a HTTP request to a callback (for any HTTP verb)
     * @param string $path Path expression
     * @param callable $callback
     * @param array $options
     * @return \pure_http_route
     * @throws InvalidArgumentException
     */
    public static function any($path, $callback, array $options = array()) {
        return self::map(null, $path, $callback, $options);
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
    public static function param($key, $default = null, $validation = null) {
        return self::req()->param($key, $default, $validation);
    }

    /**
     * @return pure_html
     */
    public static function html() {
        return pure_html::getInstance();
    }

    public static function log($message, $level = 'DEBUG', $filename = 'app.log', $add_date = true) {
        $message = $level . ': ' . $message;
        if ($add_date == true) {
            $message = '[' . date('Y-m-d H:i:s') . '] ' . $message;
        }
        return file_put_contents(self::path('logs') . $filename, $message . "\n", FILE_APPEND);
    }

    public static function cache($key, $expire_time = 3600, $generator_fn = null, $generator_args = array()) {
        $content = false;
        $file = self::path('cache') . $key;
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
     * @return pure_session
     */
    public static function session() {
        if (self::engine('session') == false) {
            self::engine('session', new pure_session(pure::path('root')));
        }
        return self::engine('session');
    }

    /**
     * @return pure_flash
     */
    public static function flash() {
        if (self::engine('flash') == false) {
            self::engine('flash', new pure_flash(self::session()));
        }
        return self::engine('flash');
    }

    /**
     * Inmediately redirect to the given url with optional status code defaulting to 302 "Found"
     * @param string $url
     * @param int $status
     */
    public static function redirect($url, $status = 302) {
        return self::resp()->redirect($url, $status);
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
        self::flash()->write($level, $message, $context);
        self::redirect($url, $status);
    }

    public static function messages($name = null, $value = null) {
        $messages = self::app()->data('messages');
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

    public static function halt($message = '500 Internal Server Error', $status = '500 Internal Server Error') {
        if (strpos(strtolower(PHP_SAPI), 'cgi') !== false) {
            header("Status: " . $status);
        } else {
            header($_SERVER['SERVER_PROTOCOL'] . " " . $status);
        }
        die('<html><head></head><body><h1>' . $message . '</h1></body></html>');
    }

}
