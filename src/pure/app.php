<?php

/**
 * @todo Basic admin login
 * 
 */
class pure_app {

    /**
     * App registry
     * @var array 
     */
    protected $registry = array(
        'paths' => array(),
        'urls' => array(),
        'engines' => array(),
        'middleware' => array(),
        'data' => array(),
    );

    /**
     * Array containing config. for: paths, urls, engines and more
     * @var array
     */
    protected $config = false;

    /**
     *
     * @var array[pure_app]
     */
    protected static $instances = array();

    /**
     *
     * @var string
     */
    protected static $currentInstance = false;

    public function __construct(pure_loader $loader, array $paths, $name = 'default') {
        if (isset(self::$instances[$name])) {
            throw new pure_error('Instances cannot have same names');
        }

        $ds = DIRECTORY_SEPARATOR;
        $rootpath = realpath(dirname($_SERVER['SCRIPT_FILENAME'])) . $ds;

        $paths = array_merge(array(
            'root' => $rootpath,
            'app' => $rootpath . "app{$ds}",
            'vendor' => $rootpath . "app{$ds}vendor{$ds}",
            'data' => $rootpath . "app{$ds}data{$ds}",
            'logs' => $rootpath . "app{$ds}data{$ds}logs{$ds}",
            'content' => $rootpath . "content{$ds}",
            'assets' => $rootpath . "content{$ds}assets{$ds}",
            'uploads' => $rootpath . "content{$ds}uploads{$ds}",
            'views' => $rootpath . "content{$ds}views{$ds}"
                ), $paths);

        $this->registry['engines']['loader'] = $loader;

        if (!is_array($this->config)) {
            $this->config = array();
        }

        // Paths
        $this->registry['paths'] = $paths;

        try {
            foreach ($this->registry['paths'] as $p) {
                if (!is_array($p) and !is_dir($p)) {
                    mkdir($p, 0755, true);
                }
            }
        } catch (Exception $exc) {
            error_log($exc->getTraceAsString());
        }

        // Set error log file
        ini_set('error_log', $this->registry['paths']['logs'] . 'php_error.log');

        $this->registry['engines']['dispatcher'] = new pure_dispatcher();
        $this->registry['engines']['request'] = pure_http_request::getInstance();
        $this->registry['engines']['response'] = pure_http_response::getInstance();
        $this->registry['engines']['router'] = new pure_http_router();
        $this->registry['engines']['templating'] = new pure_tpl($this->path('views'));

        // urls
        $this->registry['urls']['domain'] = $this->request()->protocol . '://' . $this->request()->host . '/';
        $this->registry['urls']['base'] = $this->registry['urls']['root'] = trim($this->registry['urls']['domain'] . ltrim($this->request()->basePath, '/'), '/') . '/';
        $this->registry['urls']['content'] = $this->registry['urls']['base'] . 'content/';
        $this->registry['urls']['assets'] = $this->registry['urls']['base'] . 'content/assets/';
        $this->registry['urls']['uploads'] = $this->registry['urls']['base'] . 'content/uploads/';
        $this->registry['urls']['views'] = $this->registry['urls']['base'] . 'content/views/';
        $this->registry['urls']['current'] = trim($this->registry['urls']['base'] . $this->request()->path, '/') . '/';
        $this->registry['urls']['current_query'] = $this->registry['urls']['current'] .
                (!empty($this->request()->query) ? '?' . http_build_query($this->request()->query) : '');
        $this->registry['urls']['previous'] = $this->request()->previousUrl();


        if (!isset(self::$instances[$name])) {
            self::$instances[$name] = $this;
        }

        if (self::$currentInstance === false) {
            self::$currentInstance = $name;
        }

        if (is_readable($this->registry['paths']['app'] . 'config.php')) {
            $user_config = include $this->registry['paths']['app'] . 'config.php';
        }
        if (!is_array($user_config)) {
            $user_config = array();
        }
        $this->config = pure_arr::merge($this->config, $user_config);
    }

    public function config($name, $value = null) {
        if (func_num_args() > 1) {
            $this->config[$name] = $value;
        }
        return isset($this->config[$name]) ? $this->config[$name] : false;
    }

    public function engine($name, $value = null) {
        if (func_num_args() > 1) {
            $this->registry['engines'][$name] = $value;
        }
        return isset($this->registry['engines'][$name]) ? $this->registry['engines'][$name] : false;
    }

    /**
     * 
     * @return pure_dispatcher
     */
    public function dispatcher() {
        return $this->engine('dispatcher');
    }

    /**
     * 
     * @return pure_loader
     */
    public function loader() {
        return $this->engine('loader');
    }

    /**
     * Request is an object these properties: method, protocol, host, domain,
     * subdomains, basePath, path, extension, query, body, files, cookies, headers and ip
     * @return pure_http_request
     */
    public function request() {
        return $this->engine('request');
    }

    /**
     * 
     * @return pure_http_response
     */
    public function response() {
        return $this->engine('response');
    }

    /**
     * 
     * @return pure_itpl|pure_tpl
     */
    public function templating() {
        return $this->engine('templating');
    }

    /**
     * 
     * @return pure_http_router
     */
    public function router() {
        return $this->engine('router');
    }

    /**
     * Adds new middleware to the stack
     * @param callable $callback Callable middleware
     */
    public function bind($callback) {
        $this->registry['middleware'][] = $callback;
    }

    public function data($name, $value = null) {
        if (func_num_args() > 1) {
            $this->registry['data'][$name] = $value;
        }
        return isset($this->registry['data'][$name]) ? $this->registry['data'][$name] : false;
    }

    public function url($name = 'base', $value = null) {
        if (func_num_args() > 1) {
            $this->registry['urls'][$name] = $value;
        }
        return isset($this->registry['urls'][$name]) ? $this->registry['urls'][$name] : false;
    }

    public function path($name = 'root', $value = null) {
        if (func_num_args() > 1) {
            $this->registry['paths'][$name] = $value;
        }
        return isset($this->registry['paths'][$name]) ? $this->registry['paths'][$name] : false;
    }

    /**
     * Executes the given or the next middleware (first) or route
     * @param pure_http_route $route
     */
    public function next($route = null) {
        $args = array($this->request(), $this->response(), $route, $this);

        if (count($this->registry['middleware']) > 0) { // First execute all middleware
            $middleware = array_shift($this->registry['middleware']);
            call_user_func_array($middleware, $args);
        } else {
            // Then execute route matchings
            if ($route == null) {
                $route = $this->prepareRoute();
            }
            if ($route != false) {
                if (count($route->callbacks) == 0) { // No callbacks? so, next
                    $route = $this->prepareRoute();
                }
                $cb = array_shift($route->callbacks);
                call_user_func_array($cb, $args);
            }
        }
    }

    /**
     * Dispatches the current request against the matched routes
     * executes the route lop
     */
    public function start() {
        $this->dispatcher()->trigger('app:beforeStart', array(), $this);
        $this->dispatcher()->trigger('app:beforeDispatch', array(), $this);
        $route = $this->router()->dispatch($this->request());
        $this->prepareRoute($route);
        $this->dispatcher()->trigger('app:dispatch', array(), $this);
        // start loop
        $this->next($route);
        $this->dispatcher()->trigger('app:start', array(), $this);
    }

    protected function prepareRoute($route = null) {
        if ($route === null) {
            $route = $this->router()->next();
        }

        if ($route instanceof pure_http_route) {
            $path = trim($route->options['basepath'], " /");
            $this->url('route', $this->url('base') . (empty($path) ? '' : ($path . '/')));
        }
        return $route;
    }

    /**
     * 
     * @param type $instanceName
     * @return string
     */
    public static function getInstance($instanceName = 'default') {
        return self::$instances[$instanceName];
    }

    /**
     * Sets the current instance (must be created before)
     * @param string $name
     */
    public static function setInstance($instanceName) {
        self::$currentInstance = $instanceName;
    }

}