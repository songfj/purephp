<?php

/**
 * @todo Basic admin login
 *
 */
class Pure_App {

    /**
     * Defined paths
     * @var array
     */
    protected $paths = array();

    /**
     * Defined urls
     * @var array
     */
    protected $urls = array();

    /**
     * Middleware stack called before all routes
     * @var array
     */
    protected $middleware = array();

    /**
     * Shared data
     * @var array
     */
    protected $data = array();

    /**
     * System messages
     * @var array
     */
    protected $messages = array();

    /**
     * Application boolean flags
     * @var array
     */
    protected $flags = array();

    /**
     * Shared instances container
     * @var array
     */
    protected $container = array();

    /**
     * Array containing config. for: paths, urls, libraries and more
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
    protected static $defaultInstance = false;

    public function __construct(\Composer\Autoload\ClassLoader $loader, array $config = array('name' => 'default', 'paths' => array())) {
        $config = array_merge(array('name' => 'default', 'paths' => array()), $config);

        if (isset(self::$instances[$config['name']])) {
            throw new Exception('Pure_App: There is another Pure_App instance with the same name.');
        } else {
            self::$instances[$config['name']] = $this;
        }

        if (self::$defaultInstance === false) {
            self::$defaultInstance = $config['name'];
        }

        $this->container['loader'] = $loader;

        $this->config = $config;

        // (Optional) Environment name
        if (!isset($this->config['APP_ENV'])) {
            $this->config['APP_ENV'] = getenv('APP_ENV');
        }

        if (empty($this->config['APP_ENV']) or ( $this->config['APP_ENV'] == false)) {
            $this->config['APP_ENV'] = 'develop';
        }

        $this->config['APP_ENV'] = strtolower($this->config['APP_ENV']);

        $this->_initPaths($config['paths']);
        $this->_initRouting();
        $this->_initConfig();
        $this->_initEngines();

        // (Optional) Init file
        if (is_readable($this->paths['app'] . 'init.php') and (!$this->isTest())) {
            include $this->paths['app'] . 'init.php';
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
        $this->paths = $paths;
    }

    private function _initRouting() {

        if (!isset($this->config['hasModRewrite'])) {
            $this->config['hasModRewrite'] = (isset($_SERVER['APPLICATION_REWRITE_ENGINE']) && ($_SERVER['APPLICATION_REWRITE_ENGINE'] == 'on'));
        }

        if (!isset($this->config['useIndexFile'])) {
            $this->config['useIndexFile'] = ($this->config['hasModRewrite'] === false);
        }

        // Dispatcher
        $this->container('dispatcher', new \Illuminate\Events\Dispatcher());

        $this->container['request'] = $this->make('Pure_Http_Request', array(false));
        $this->container['request']->populate();
        $this->container['response'] = $this->make('Pure_Http_Response');
        $this->container['router'] = $this->make('Pure_Http_Router');

        // URLs
        $this->urls['domain'] = $this->request()->protocol . '://' . $this->request()->host . '/';
        $this->urls['base'] = $this->urls['root'] = trim($this->urls['domain'] . ltrim($this->request()->basePath, '/'), '/') . '/';

        // Rewrite engine base URL
        if ($this->config('useIndexFile') === true) {
            $this->urls['rewrite_base'] = $this->urls['base'] . 'index.php/';
        } else {
            $this->urls['rewrite_base'] = $this->urls['base'];
        }
        $this->urls['content'] = $this->urls['base'] . 'content/';
        $this->urls['current'] = trim($this->urls['base'] . $this->request()->path, '/') . '/';
        $this->urls['current_query'] = $this->urls['current'] .
                (!empty($this->request()->query) ? '?' . http_build_query($this->request()->query) : '');
        $this->urls['previous'] = $this->request()->previousUrl();
    }

    private function _initConfig() {

        // (Optional) Config file based on environment
        $user_config = array();

        if (is_readable($this->paths['config'] . $this->config['APP_ENV'] . '.php')) {
            $user_config = include $this->paths['config'] . $this->config['APP_ENV'] . '.php';
        } elseif (is_readable($this->paths['config'] . 'default.php')) {
            $user_config = include $this->paths['config'] . 'default.php';
        }
        if (!is_array($user_config)) {
            $user_config = array();
        }

        $this->config = array_merge_recursive_replace($this->config, $user_config);
    }

    /**
     * @todo Implement Monolog channel for user flash messages
     */
    private function _initEngines() {
        $app = $this;

        // Session
        $this->container('session', $this->make('Pure_Session', array($this->path('root'))));

        // Flash
        $this->container('flash', $this->make('Pure_Flash', array($this->container('session'))));


        // Filesystem
        $this->container('filesystem', new \Illuminate\Filesystem\Filesystem());

        // Views
        $this->container('view', $this->make('Pure_View', array($this->path('views'), $this->path('cache') . 'views/')));

        // Monolog
        $this->container('logger', new \Monolog\Logger('purephp'));
        $this->container('logger')->pushHandler(new \Monolog\Handler\StreamHandler($this->path('logs') . 'debug.log', \Monolog\Logger::DEBUG));

        // Monolog for user flash messages
        // TODO: implement
        // SwiftMailer
        if (strtolower($this->config('smtp_enabled')) == true) {
            $transport = Swift_SmtpTransport::newInstance($this->config('smtp_host'), $this->config('smtp_port'))
                    ->setUsername($this->config('smtp_user'))
                    ->setPassword($this->config('smtp_password'));
        } else {
            $transport = Swift_MailTransport::newInstance();
        }
        $this->container('mailer', Swift_Mailer::newInstance($transport));


        // RedBean
        if ($this->config('db.enabled')) {
            RedBean_Facade::setup($this->config('db.dsn'), $this->config('db.username'), $this->config('db.password'));
            $this->container('database', RedBean_Facade::getToolBox()->getDatabaseAdapter()->getDatabase());
        } else {
            $this->container('database', false);
        }

        // Whoops
        if ($this->config('debug') === true) {
            $run = new \Whoops\Run();
            $run->pushHandler(new \Whoops\Handler\PrettyPageHandler());
            $this->container('error_handler', $run);

            set_error_handler(function($errno, $errstr, $errfile, $errline) use ($app) {
                $e = new \ErrorException($errstr, $errno, 1, $errfile, $errline);
                $app->container('error_handler')->handleException($e);
            }, -1);

            set_exception_handler(function($e) use ($app) {
                $app->container('error_handler')->handleException($e);
            });

            //$run->register();
        } else {
            $this->container('error_handler', false);
        }

        // Validator
        $this->container('validator', new Pure_Validator());

        // HTML generator
        $this->container('html', new Pure_Html());
    }

    /**
     * Application environment name
     * @return string
     */
    public function envName() {
        return $this->config['APP_ENV'];
    }

    public function isTest() {
        return $this->envName() == 'test';
    }

    public function isDevelop() {
        return $this->envName() == 'develop';
    }

    public function isProduction() {
        return $this->envName() == 'production';
    }

    public function config($name, $value = null) {
        if (func_num_args() > 1) {
            $this->config[$name] = $value;
        }
        return isset($this->config[$name]) ? $this->config[$name] : false;
    }

    /**
     * Adds a existing object instance into the container array
     * @param string $name
     * @param mixed $value
     * @return mixed The container instance
     */
    public function container($name, $value = null) {
        if (func_num_args() > 1) {
            $this->container[$name] = $value;
        }
        return isset($this->container[$name]) ? $this->container[$name] : false;
    }

    /**
     * Gets or sets an application flag (status booleans)
     * @param string $name
     * @param boolean|null $enable
     * @return boolean
     */
    public function flag($name, $enable = null) {
        if (is_bool($enable)) {
            if (($enable === false) and $this->hasFlag($name)) {
                unset($this->flags[$name]);
            } elseif ($enable === true) {
                $this->flags[$name] = true;
            }
        }
        return $this->hasFlag($name) ? $this->flags[$name] : false;
    }

    public function hasFlag($name) {
        return isset($this->flags[$name]);
    }

    public function getFlags() {
        return $this->flags;
    }

    /**
     * 
     * @return Swift_Mailer
     */
    public function mailer() {
        return $this->container('mailer');
    }

    /**
     * 
     * @return \Illuminate\Events\Dispatcher
     */
    public function dispatcher() {
        return $this->container('dispatcher');
    }

    /**
     * 
     * @return \Whoops\Run
     */
    public function eventHandler() {
        return $this->container('event_handler');
    }

    /**
     * 
     * @return \Monolog\Logger
     */
    public function logger() {
        return $this->container('logger');
    }

    /**
     * 
     * @return \Illuminate\Filesystem\Filesystem
     */
    public function filesystem() {
        return $this->container('filesystem');
    }

    /**
     * 
     * @return Pure_Validator
     */
    public function validator() {
        return $this->container('validator');
    }

    /**
     * @return Pure_Session
     */
    public function session() {
        return $this->container('session');
    }

    /**
     * @return Pure_Flash
     */
    public function flash() {
        return $this->container('flash');
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
        $message = Pure_Mail::newInstance($subject);
        $message->setFrom($from ? $from : $this->config('smtp_from'))
                ->setTo($to)
                ->setBody($body, 'text/html', 'utf-8');

        if (!empty($bcc)) {
            $message->setBcc($bcc);
        }

        $message->setApp($this);

        // Send the message
        return $message;
    }

    /**
     *
     * @return \Composer\Autoload\ClassLoader
     */
    public function loader() {
        return $this->container('loader');
    }

    /**
     * Request is an object these properties: method, protocol, host, domain,
     * subdomains, basePath, path, extension, query, body, files, cookies, headers and ip
     * @return Pure_Http_Request
     */
    public function request() {
        return $this->container('request');
    }

    /**
     *
     * @return Pure_Http_Response
     */
    public function response() {
        return $this->container('response');
    }

    /**
     *
     * @return Pure_View
     */
    public function view() {
        return $this->container('view');
    }

    /**
     *
     * @return Pure_Http_Router
     */
    public function router() {
        return $this->container('router');
    }

    /**
     *
     * @return Redbean_Driver
     */
    public function db() {
        return $this->container('database');
    }

    /**
     * Creates a new object, setting the app instance if the class implements Pure_IInjectable
     * @param string $className The class name
     * @param array $args Constructor arguments
     * @return mixed The new instance
     */
    public function make($className, array $args = array()) {
        $rclass = new ReflectionClass($className);
        $obj = $rclass->newInstanceArgs($args);
        if (in_array('Pure_IInjectable', class_implements($className))) {
            $obj->setApp($this);
        }
        return $obj;
    }

    /**
     * Adds new middleware to the stack, that is called before the defined routes
     * @param callable $callback Callable middleware
     */
    public function before($callback) {
        $this->middleware[] = $callback;
    }

    /**
     * General application data (registry of variables not considered shared instances nor global config)
     * @param string $name
     * @param mixed $value
     * @return mixed
     */
    public function data($name, $value = null) {
        if (func_num_args() > 1) {
            $this->data[$name] = $value;
        }
        return isset($this->data[$name]) ? $this->data[$name] : false;
    }

    public function url($name = 'rewrite_base', $value = null) {
        if (func_num_args() > 1) {
            $this->urls[$name] = $value;
        }
        return isset($this->urls[$name]) ? $this->urls[$name] : false;
    }

    public function path($name = 'root', $value = null) {
        if (func_num_args() > 1) {
            $this->paths[$name] = $value;
        }
        return isset($this->paths[$name]) ? $this->paths[$name] : false;
    }

    /**
     *
     * @param type $instanceName
     * @return string
     */
    public static function getInstance($instanceName = null) {
        return self::$instances[$instanceName ? $instanceName : self::$defaultInstance];
    }

    /**
     * Sets the current instance (must be created before)
     * @param string $name
     */
    public static function setDefaultInstance($instanceName) {
        self::$defaultInstance = $instanceName;
    }

    public function halt($message = '500 Internal Server Error', $status = '500 Internal Server Error') {
        if (strpos(strtolower(PHP_SAPI), 'cgi') !== false) {
            header("Status: " . $status);
        } else {
            header($_SERVER['SERVER_PROTOCOL'] . " " . $status);
        }
        die('<html><head></head><body><h1>' . $message . '</h1></body></html>');
    }

    /**
     * Executes the given or the next middleware (first) or route
     * @param Pure_Http_Route $route
     * @return mixed|false The callable return value or false
     */
    public function next() {
        $result = false;
        if (count($this->middleware) > 0) { // First execute all middleware
            $middleware = array_shift($this->middleware);
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
        $this->container('dispatcher')->fire('app.before_start', array('sender' => $this));
        $this->container('dispatcher')->fire('app.before_dispatch', array('sender' => $this));
        $this->router()->dispatch($this->request());
        $this->container('dispatcher')->fire('app.dispatch', array('sender' => $this));
        // start loop
        $this->next();
        $this->container('dispatcher')->fire('app.start', array('sender' => $this));
    }

}
