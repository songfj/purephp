<?php

/**
 * 
 * @todo Implement expressjs-like bindParam
 */
class pure_http_router {

    /**
     * Route binding stack
     * @var pure_http_route[] 
     */
    public $routes = array();

    /**
     * Param callbacks
     * @var array
     */
    public $params = array();

    /**
     * The currently matched Route containing several properties such as the
     * route's original path string, the regexp generated, and so on.
     * 
     * @var pure_http_route 
     */
    public $currentRoute = false;

    /**
     * The Router matched routes
     * @var pure_http_route[]
     */
    public $matchedRoutes = array();

    /**
     *
     * @var type 
     */
    protected static $tmpRegexpData = array();

    public function __construct() {
        //leave this for reflection instantiation
    }

    public function match($method, $uri) {
        $method = $this->formatMethod($method);
        $matchedRoutes = array();
        foreach ($this->routes as $i => $route) {
            if (($route->method == $method) or ($route->method == 'all')) {
                $num_matches = preg_match_all($route->regexp, $uri, $matches);
                if ($num_matches > 0) {
                    array_shift($matches);
                    $i = 0;
                    foreach ($route->keys as $k => $param) {
                        $route->keys[$k]['value'] = (isset($matches[$i][0]) and !empty($matches[$i][0])) ? $matches[$i][0] : false;
                        $i++;
                    }
                    $matchedRoutes[] = $route;
                }
            }
        }
        return $matchedRoutes;
    }

    /**
     * 
     * @param pure_http_request $req
     */
    public function dispatch($req) {
        $this->matchedRoutes = $this->match($req->method, $req->path);
    }

    /**
     * Returns the next matched route
     * @return pure_http_route|false
     */
    public function next() {
        if (count($this->matchedRoutes) > 0) {
            $this->currentRoute = array_shift($this->matchedRoutes);
            foreach ($this->currentRoute->keys as $param) {
                $this->currentRoute->params[$param["name"]] = $param["value"];
            }
            return $this->currentRoute;
        }
        return false;
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
    public function map($method, $path, $callback, array $options = array()) {
        if (!is_callable($callback)) {
            throw new InvalidArgumentException('The argument is not a callable function: '.print_r($callback, true));
        }
        $method = $this->formatMethod($method);
        $basepath = explode(':', $path, 2);
        $basepath = empty($basepath[0]) ? '/' : $basepath[0];
        $route = array(
            'path' => $path,
            'method' => $method,
            'callbacks' => array($callback),
            'keys' => array(),
            'regexp' => '',
            'options' => array_merge(array('sensitive' => false, 'strict' => false, 'basepath' => $basepath), $options)
        );
        $route['regexp'] = self::pathRegexp($path, $route['keys'], $route['options']['sensitive'], $route['options']['strict']);

        $route_instance = new pure_http_route($route);
        $this->routes[] = $route_instance;
        return $route_instance;
    }

    protected function formatMethod($method) {
        $method = strtoupper($method);
        if (empty($method) || ($method == '*')) {
            $method = 'all';
        }
        return $method;
    }

    public static function pathRegexp($path, array &$keys, $sensitive = false, $strict = false) {
        $pathhash = md5($path . strval($sensitive) . strval($strict));
        if (is_array($path)) {
            $path = '(' . implode('|', $path) . ')';
        }

        $path = (($strict === true) ? $path : (trim($path, '/ ') . '/?'));
        $path = preg_replace('/\/\(/', '(?:/', $path);
        self::$tmpRegexpData = $keys;
        $path = preg_replace_callback('/(\/)?(\.)?:(\w+)(?:(\(.*?\)))?(\?)?(\*)?/', array('pure_http_router', 'pathRegexpPregReplaceCallback'), $path);
        $keys = self::$tmpRegexpData;
        self::$tmpRegexpData = array();
        $path = preg_replace('/([\/.])/', '\\/', $path);
        $path = preg_replace('/\*/', '(.*)', $path);
        return '/^' . $path . '$/' . ($sensitive ? '' : 'i');
    }

    public static function pathRegexpPregReplaceCallback($matches) {
        $slash = (isset($matches[1]) ? $matches[1] : '');
        $format = (isset($matches[2]) ? $matches[2] : false);
        $key = (isset($matches[3]) ? $matches[3] : null);
        $capture = (isset($matches[4]) ? $matches[4] : false);
        $optional = (isset($matches[5]) ? $matches[5] : false);
        $star = (isset($matches[6]) ? $matches[6] : false);

        array_push(self::$tmpRegexpData, array('name' => $key, 'value' => false, 'optional' => (!!$optional)));

        return ''
                . ($optional ? '' : $slash)
                . '(?:'
                . ($optional ? $slash : '')
                . ($format ? $format : '')
                . ($capture ? $capture : ($format ? '([^/.]+?)' : '([^/]+?)')) . ')'
                . ($optional ? $optional : '')
                . ($star ? '(/*)?' : '');
    }

}