<?php

/**
 * @todo Basic admin login
 *
 */
class Pure_App {

    /**
     * App registry
     * @var array
     */
    protected $registry = array(
        'paths' => array(),
        'urls' => array(),
        'engines' => array(),
        'middleware' => array(),
        'data' => array(
            'messages' => array()
        ),
        'flags' => array()
    );

    /**
     * Array containing config. for: paths, urls, engines and more
     * @var array
     */
    protected $config = false;

    /**
     *
     * @var array[Pure_App]
     */
    protected static $instances = array();

    /**
     *
     * @var string
     */
    protected static $currentInstance = false;

    public function __construct(\Composer\Autoload\ClassLoader $loader, array $paths, $name = 'default', array $config = array()) {
        if (isset(self::$instances[$name])) {
            throw new Exception('Pure_App: There is another Pure_App instance with the same name.');
        } else {
            self::$instances[$name] = $this;
        }

        if (self::$currentInstance === false) {
            self::$currentInstance = $name;
        }

        $this->registry['engines']['loader'] = $loader;

        $this->config = $config;

        // (Optional) Environment name
        if (!isset($this->config['APP_ENV'])) {
            $this->config['APP_ENV'] = getenv('APP_ENV');
        }

        if (empty($this->config['APP_ENV']) or ( $this->config['APP_ENV'] == false)) {
            $this->config['APP_ENV'] = 'develop';
        }

        $this->config['APP_ENV'] = strtolower($this->config['APP_ENV']);

        $this->_initPaths($paths);
        $this->_initHttp();
        $this->_initConfig();
        $this->_initVendors();

        // (Optional) Init file
        if (is_readable($this->registry['paths']['app'] . 'init.php')) {
            include $this->registry['paths']['app'] . 'init.php';
        }
    }

    private function _initPaths(array $paths) {
        $ds = DIRECTORY_SEPARATOR;
        $rootpath = realpath(dirname($_SERVER['SCRIPT_FILENAME'])) . $ds;
        $libpath = realpath(dirname(__FILE__) . '/../') . $ds;

        // Merge default paths with project ones
        $paths = array_merge(array(
            'root' => $rootpath,
            'public' => $rootpath,
            'app' => $rootpath . "app{$ds}",
            'config' => $rootpath . "app{$ds}config{$ds}",
            'vendor' => $rootpath . "app{$ds}vendor{$ds}",
            'data' => $rootpath . "app{$ds}data{$ds}",
            'logs' => $rootpath . "app{$ds}data{$ds}logs{$ds}",
            'content' => $rootpath . "content{$ds}",
            'uploads' => $rootpath . "content{$ds}uploads{$ds}",
            'views' => $rootpath . "content{$ds}views{$ds}",
            'purephp' => $libpath
                ), $paths);

        // Paths
        if (!file_exists($paths['data'] . '.setup')) {
            foreach ($paths as $n => $p) {
                if (!is_dir($p)) {
                    mkdir($p, 0755, true);
                }
            }
            file_put_contents($paths['data'] . '.setup', time());
        }
        $this->registry['paths'] = $paths;
    }

    private function _initHttp() {

        if (!isset($this->config['hasModRewrite'])) {
            $this->config['hasModRewrite'] = (isset($_SERVER['APPLICATION_REWRITE_ENGINE']) && ($_SERVER['APPLICATION_REWRITE_ENGINE'] == 'on'));
        }

        if (!isset($this->config['useIndexFile'])) {
            $this->config['useIndexFile'] = ($this->config['hasModRewrite'] === false);
        }

        $this->registry['engines']['request'] = Pure_Http_Request::getInstance();
        $this->registry['engines']['response'] = Pure_Http_Response::getInstance();
        $this->registry['engines']['router'] = new Pure_Http_Router();
        $this->registry['engines']['view'] = new Pure_View($this->path('views'));

        // URLs
        $this->registry['urls']['domain'] = $this->request()->protocol . '://' . $this->request()->host . '/';
        $this->registry['urls']['base'] = $this->registry['urls']['root'] = trim($this->registry['urls']['domain'] . ltrim($this->request()->basePath, '/'), '/') . '/';

        // Rewrite engine base URL
        if ($this->config('useIndexFile') === true) {
            $this->registry['urls']['rewrite_base'] = $this->registry['urls']['base'] . 'index.php/';
        } else {
            $this->registry['urls']['rewrite_base'] = $this->registry['urls']['base'];
        }
        $this->registry['urls']['content'] = $this->registry['urls']['base'] . 'content/';
        $this->registry['urls']['current'] = trim($this->registry['urls']['base'] . $this->request()->path, '/') . '/';
        $this->registry['urls']['current_query'] = $this->registry['urls']['current'] .
                (!empty($this->request()->query) ? '?' . http_build_query($this->request()->query) : '');
        $this->registry['urls']['previous'] = $this->request()->previousUrl();
    }

    private function _initConfig() {

        // (Optional) Config file based on environment
        $user_config = array();

        if (is_readable($this->registry['paths']['config'] . $this->config['APP_ENV'] . '.php')) {
            $user_config = include $this->registry['paths']['config'] . $this->config['APP_ENV'] . '.php';
        } elseif (is_readable($this->registry['paths']['config'] . 'default.php')) {
            $user_config = include $this->registry['paths']['config'] . 'default.php';
        }
        if (!is_array($user_config)) {
            $user_config = array();
        }

        $this->config = Pure_Arr::merge($this->config, $user_config);
    }

    private function _initVendors() {
        // Monolog
        $this->engine('logger', new \Monolog\Logger('purephp'));
        $this->engine('logger')->pushHandler(new \Monolog\Handler\StreamHandler($this->path('logs') . 'debug.log', \Monolog\Logger::DEBUG));

        // SwiftMailer
        if (strtolower($this->config('smtp_enabled')) == true) {
            $transport = Swift_SmtpTransport::newInstance($this->config('smtp_host'), $this->config('smtp_port'))
                    ->setUsername($this->config('smtp_user'))
                    ->setPassword($this->config('smtp_password'));
        } else {
            $transport = Swift_MailTransport::newInstance();
        }
        $this->engine('mailer', Swift_Mailer::newInstance($transport));


        // RedBean
        if ($this->config('db.enabled')) {
            RedBean_Facade::setup($this->config('db.dsn'), $this->config('db.username'), $this->config('db.password'));
            $this->engine('database', RedBean_Facade::getToolBox()->getDatabaseAdapter()->getDatabase());
        } else {
            $this->engine('database', false);
        }

        // Whoops
        if ($this->config('debug') === true) {
            $this->engine('error_handler', new \Whoops\Run())->pushHandler(new \Whoops\Handler\PrettyPageHandler);
            $this->engine('error_handler')->register();
        } else {
            $this->engine('error_handler', false);
        }
    }

    /**
     * Application environment name
     * @return string
     */
    public function env() {
        return $this->config['APP_ENV'];
    }

    public function isDevelop() {
        return $this->env() == 'develop';
    }

    public function isProduction() {
        return $this->env() == 'production';
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
     * @return Swift_Mailer
     */
    public function mailer() {
        return $this->engine('mailer');
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
    public function mail($to, $subject, $body, $from = null, $bcc = null) {
        // Create a message
        $message = Pure_Mail::newInstance($subject)
                ->setFrom($from ? $from : $this->config('smtp_from'))
                ->setTo($to)
                ->setBody($body, 'text/html', 'utf-8');

        if (!empty($bcc)) {
            $message->setBcc($bcc);
        }

        // Send the message
        return $message;
    }

    /**
     *
     * @return \Composer\Autoload\ClassLoader
     */
    public function loader() {
        return $this->engine('loader');
    }

    /**
     * Request is an object these properties: method, protocol, host, domain,
     * subdomains, basePath, path, extension, query, body, files, cookies, headers and ip
     * @return Pure_Http_Request
     */
    public function request() {
        return $this->engine('request');
    }

    /**
     *
     * @return Pure_Http_Response
     */
    public function response() {
        return $this->engine('response');
    }

    /**
     *
     * @return Pure_View
     */
    public function view() {
        return $this->engine('view');
    }

    /**
     *
     * @return Pure_Http_Router
     */
    public function router() {
        return $this->engine('router');
    }

    /**
     *
     * @return Redbean_Driver
     */
    public function db() {
        return $this->engine('database');
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

    public function url($name = 'rewrite_base', $value = null) {
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
     * @param Pure_Http_Route $route
     * @return mixed|false The callable return value or false
     */
    public function next() {
        $result = false;
        if (count($this->registry['middleware']) > 0) { // First execute all middleware
            $middleware = array_shift($this->registry['middleware']);
            $result = call_user_func_array($middleware, array($this->request(), $this->response(), new Pure_Http_Route(), $this));
        } else { // Then execute all router bindings
            $route = $this->prepareRoute();
            if ($route != false) {
                $cb = $this->getCallback(array_shift($route->callbacks));
                $result = call_user_func_array($cb, array($this->request(), $this->response(), $route, $this));
            }
        }
        return $result ? $result : false;
    }

    protected function getCallback($callback) {
        $invalidMsg = 'The argument is not a callable function: ' . print_r($callback, true);
        if (is_string($callback) and preg_match('/\@/', $callback)) {
            $cb = explode('@', $callback);
            if ((count($cb) == 2) and ( class_exists($cb[0]))) {
                return array(new $cb[0](), $cb[1]);
            } else {
                throw new InvalidArgumentException($invalidMsg);
            }
        } elseif (!is_callable($callback)) {
            throw new InvalidArgumentException($invalidMsg);
        }
        return $callback;
    }

    /**
     * Dispatches the current request against the matched routes
     * executes the route lop
     */
    public function start() {
        Pure_Dispatcher::getInstance()->trigger('app.before_start', array(), $this);
        Pure_Dispatcher::getInstance()->trigger('app.before_dispatch', array(), $this);
        $this->router()->dispatch($this->request());
        Pure_Dispatcher::getInstance()->trigger('app.dispatch', array(), $this);
        // start loop
        $this->next();
        Pure_Dispatcher::getInstance()->trigger('app.start', array(), $this);
    }

    protected function prepareRoute($route = null) {
        if ($route === null) {
            $route = $this->router()->next();
        }

        if ($route instanceof Pure_Http_Route) {
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

    public function halt($message = '500 Internal Server Error', $status = '500 Internal Server Error') {
        if (strpos(strtolower(PHP_SAPI), 'cgi') !== false) {
            header("Status: " . $status);
        } else {
            header($_SERVER['SERVER_PROTOCOL'] . " " . $status);
        }
        die('<html><head></head><body><h1>' . $message . '</h1></body></html>');
    }

}
