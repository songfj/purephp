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
        'flags' => array()
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

    public function __construct(pure_loader $loader, array $paths, $name = 'default', array $config = array()) {
        if (isset(self::$instances[$name])) {
            throw new pure_error('Instances cannot have same names');
        }

        $this->config = $config;

        $ds = DIRECTORY_SEPARATOR;
        $rootpath = realpath(dirname($_SERVER['SCRIPT_FILENAME'])) . $ds;

        // Merge default paths with project ones
        $paths = array_merge(array(
            'root' => $rootpath,
            'app' => $rootpath . "app{$ds}",
            'config' => $rootpath . "app{$ds}config{$ds}",
            'vendor' => $rootpath . "app{$ds}vendor{$ds}",
            'data' => $rootpath . "app{$ds}data{$ds}",
            'logs' => $rootpath . "app{$ds}data{$ds}logs{$ds}",
            'content' => $rootpath . "content{$ds}",
            'assets' => $rootpath . "content{$ds}assets{$ds}",
            'uploads' => $rootpath . "content{$ds}uploads{$ds}",
            'views' => $rootpath . "content{$ds}views{$ds}"
                ), $paths);

        $this->registry['engines']['loader'] = $loader;

        // Paths
        $this->registry['paths'] = $paths;

        if (!isset(self::$instances[$name])) {
            self::$instances[$name] = $this;
        }

        if (self::$currentInstance === false) {
            self::$currentInstance = $name;
        }

        $this->registry['engines']['request'] = pure_http_request::getInstance();
        $this->registry['engines']['response'] = pure_http_response::getInstance();
        $this->registry['engines']['router'] = new pure_http_router();
        $this->registry['engines']['templating'] = new pure_tpl($this->path('views'));
        $this->registry['engines']['mail'] = new pure_mail();

        // URLs
        $this->registry['urls']['domain'] = $this->request()->protocol . '://' . $this->request()->host . '/';
        $this->registry['urls']['base'] = $this->registry['urls']['root'] = trim($this->registry['urls']['domain'] . ltrim($this->request()->basePath, '/'), '/') . '/';

        // Rewrite engine base URL
        if ($this->config('useIndexFile') === true) {
            $this->registry['urls']['baserw'] = $this->registry['urls']['base'] . 'index.php/';
        } else {
            $this->registry['urls']['baserw'] = $this->registry['urls']['base'];
        }

        $this->registry['urls']['content'] = $this->registry['urls']['base'] . 'content/';
        $this->registry['urls']['assets'] = $this->registry['urls']['base'] . 'content/assets/';
        $this->registry['urls']['uploads'] = $this->registry['urls']['base'] . 'content/uploads/';
        $this->registry['urls']['views'] = $this->registry['urls']['base'] . 'content/views/';
        $this->registry['urls']['current'] = trim($this->registry['urls']['base'] . $this->request()->path, '/') . '/';
        $this->registry['urls']['current_query'] = $this->registry['urls']['current'] .
                (!empty($this->request()->query) ? '?' . http_build_query($this->request()->query) : '');
        $this->registry['urls']['previous'] = $this->request()->previousUrl();

        // (Optional) Environment name
        if (!isset($this->config['APPLICATION_ENV'])) {
            $this->config['APPLICATION_ENV'] = getenv('APPLICATION_ENV');
        }

        if (empty($this->config['APPLICATION_ENV']) or ($this->config['APPLICATION_ENV'] == false)) {
            $this->config['APPLICATION_ENV'] = 'default';
        }

        // (Optional) Config file based on environment
        $user_config = array();

        if (is_readable($this->registry['paths']['config'] . $this->config['APPLICATION_ENV'] . '.php')) {
            $user_config = include $this->registry['paths']['config'] . $this->config['APPLICATION_ENV'] . '.php';
        } elseif (is_readable($this->registry['paths']['config'] . 'default.php')) {
            $user_config = include $this->registry['paths']['config'] . 'default.php';
        }
        if (!is_array($user_config)) {
            $user_config = array();
        }

        $this->config = pure_arr::merge($this->config, $user_config);

        // (Optional) Init file
        if (is_readable($this->registry['paths']['app'] . 'init.php')) {
            include $this->registry['paths']['app'] . 'init.php';
        }
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

    public function flag($name, $enable = null) {
        if (is_bool($enable)) {
            if (($enable === false) and $this->hasFlag($name)) {
                unset($this->registry['flags'][$name]);
            } elseif ($enable === true) {
                $this->registry['flags'][$name] = true;
            }
        }
        return $this->hasFlag($name) ? $this->registry['flags'][$name] : false;
    }

    public function hasFlag($name) {
        return isset($this->registry['flags'][$name]);
    }

    public function getFlags() {
        return $this->registry['flags'];
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
     * @return pure_imail|pure_mail
     */
    public function mail() {
        return $this->engine('mail');
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

    /**
     * General application data (registry of variables not considered engines nor global config)
     * @param string $name
     * @param mixed $value
     * @return mixed
     */
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
     * @return mixed|false The callable return value or false
     */
    public function next() {
        $result = false;
        if (count($this->registry['middleware']) > 0) { // First execute all middleware
            $middleware = array_shift($this->registry['middleware']);
            $result = call_user_func_array($middleware, array($this->request(), $this->response(), new pure_http_route(), $this));
        } else { // Then execute all router bindings
            $route = $this->prepareRoute();
            if ($route != false) {
                $cb = array_shift($route->callbacks);
                $result = call_user_func_array($cb, array($this->request(), $this->response(), $route, $this));
            }
        }
        return $result ? $result : false;
    }

    /**
     * Dispatches the current request against the matched routes
     * executes the route lop
     */
    public function start() {
        pure_dispatcher::getInstance()->trigger('app.before_start', array(), $this);
        pure_dispatcher::getInstance()->trigger('app.before_dispatch', array(), $this);
        $this->router()->dispatch($this->request());
        pure_dispatcher::getInstance()->trigger('app.dispatch', array(), $this);
        // start loop
        $this->next();
        pure_dispatcher::getInstance()->trigger('app.start', array(), $this);
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
