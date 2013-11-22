<?php

class pure_http_request {
    /*
     * The HTTP Request Method (verb)
     * @var string
     */

    public $method = 'GET';

    /*
     * Return the protocol string "http" or "https" when requested with TLS
     * @var string
     */
    public $protocol = 'http';

    /*
     * The HTTP hostname (including port if differs from 80)
     * @var string
     */
    public $host;

    /*
     * The HTTP hostname (without port)
     * @var string
     */
    public $serverName;

    /*
     * The HTTP hostname (including port if differs from 80)
     * @var string
     */
    public $port;

    /**
     * Second Level Domain (SLD) of the HTTP hostname
     * @var string 
     */
    public $domain;

    /*
     * Return subdomains as an array (excluding SLD)
     * @var array
     */
    public $subdomains = array();

    /*
     * Base request path
     * @var string
     */
    public $basePath;

    /*
     * The request URL pathname (without the query string)
     * 
     * @var string
     */
    public $path;

    /*
     * Exploded path
     * 
     * @var array
     */
    public $segments;

    /**
     * Path extension (what goes behind the last dot)
     * @var string
     */
    public $extension;

    /**
     * This property is an associative array containing the parsed query-string
     * @var array
     */
    public $query = array();

    /**
     * This property is an associative array containing the parsed request body
     * (POST and PUT variables)
     * @var array
     */
    public $body = array();

    /**
     * 
     * This property is an array containing the files uploaded. It has the same
     * structure as the superglobal $_FILES variable
     * @var array
     */
    public $files = array();

    /**
     * Contains the cookies sent by the user-agent
     * @var array
     */
    public $cookies = array();

    /**
     * This property is an associative array containing the parsed HTTP headers
     * in a Proper-Case naming
     * @var array
     */
    public $headers = array();

    /**
     * Return the remote address
     * @var string
     */
    public $ip;

    /**
     *
     * @var pure_http_request 
     */
    protected static $instance = null;

    /**
     * 
     * @param boolean $populate Populate original request?
     */
    public function __construct($populate = true) {
        if ($populate === true) {
            $this->populate();
        }
    }

    /**
     * 
     * @return pure_http_request
     */
    public static function getInstance() {
        if (self::$instance == null) {
            self::$instance = new self(true);
        }
        return self::$instance;
    }

    public function populate() {
        // Trigger event
        pure::trigger('request.before_populate', array(), $this);

        $script_name = (isset($_SERVER["SCRIPT_NAME"]) ? $_SERVER["SCRIPT_NAME"] : '');

        $this->method = isset($_SERVER['HTTP_X_METHOD']) ? strtoupper($_SERVER['HTTP_X_METHOD']) : (
                isset($_SERVER['REQUEST_METHOD']) ? strtoupper($_SERVER['REQUEST_METHOD']) : '');

        $this->protocol = (isset($_SERVER['HTTP_X_FORWARDED_PROTO']) and ($_SERVER['HTTP_X_FORWARDED_PROTO'] == 'https')) ?
                'https' : ((isset($_SERVER['HTTPS']) and ($_SERVER['HTTPS'] != 'off')) ? 'https' : 'http');

        $this->host = isset($_SERVER["HTTP_HOST"]) ? $_SERVER["HTTP_HOST"] : "";
        $this->port = isset($_SERVER["SERVER_PORT"]) ? $_SERVER["SERVER_PORT"] : 80;
        $this->serverName = isset($_SERVER["SERVER_NAME"]) ? $_SERVER["SERVER_NAME"] : "";

        // Domain and subdomains
        $this->domain = preg_replace('/\:.+/', '', $this->host);
        $this->subdomains = array();
        $subdomains = explode('.', $this->domain);
        if (count($subdomains) > 2) {
            $this->domain = implode(".", array_slice($subdomains, -2, 2));
            $this->subdomains = array_reverse(array_slice($subdomains, 0, -2));
        }

        // Base path
        $this->basePath = '';
        if (!empty($script_name)) {
            $this->basePath = trim(str_replace('\\', '/', dirname($script_name)), '/ ');
        }

        // Path
        $this->path = explode("?", trim(isset($_SERVER["REQUEST_URI"]) ? $_SERVER["REQUEST_URI"] : '', " /"), 2);
        $this->path = $this->path[0];
        if (!empty($this->basePath)) {
            $this->path = preg_replace("/^" . str_replace('/', '\/', $this->basePath) . "/", "", $this->path);
        }

        $this->path = preg_replace("/^" . preg_quote(basename($script_name)) . "\/?/", "", trim($this->path, " /"));

        // Extension
        $ext = explode(".", $this->path);
        if (count($ext) > 1) {
            $this->extension = array_pop($ext);
            //$this->path = implode('.', $ext);
        }

        $this->query = $_GET;

        parse_str(file_get_contents("php://input"), $putVars);
        $this->body = array_merge($putVars, $_POST);

        $this->files = $_FILES;
        $this->cookies = $_COOKIE;

        //Server headers
        $this->headers = array();
        foreach ($_SERVER as $k => $v) {
            if (preg_match("/^HTTP_/", $k)) {
                $name = ucwords(strtolower(str_replace("_", " ", preg_replace("/^HTTP_/", "", $k))));
                $this->headers[str_replace(" ", "-", $name)] = $v;
            }
        }

        $this->ip = (isset($_SERVER["REMOTE_ADDR"]) ? $_SERVER["REMOTE_ADDR"] : false);
        $this->segments = explode('/', $this->path);

        // Trigger event
        pure::trigger('request.populate', array(), $this);

        return $this;
    }

    public function hostUrl() {
        return $this->protocol . '://' . $this->host . '/';
    }

    public function baseUrl() {
        return $this->protocol . '://' . $this->host . '/';
    }

    public function url() {
        return $this->protocol . '://' . $this->host . '/' . $this->basePath . $this->path . ($this->extension ? '.' . $this->extension : '');
    }

    public function query(array $params = array(), array $unsetParams = array(), $escape = true) {
        $q = $this->query;
        foreach ($unsetParams as $n) {
            if (isset($q[$n])) {
                unset($q[$n]);
            }
        }
        if ($escape) {
            return '?' . str_replace('&', '&amp;', http_build_query(array_merge($q, $params)));
        } else {
            return '?' . http_build_query(array_merge($q, $params));
        }
    }

    /**
     * Return the value of header name when present, otherwise return false.
     * @param string $name
     */
    public function header($name) {
        if (isset($this->headers[$name])) {
            return $this->headers[$name];
        }
        return false;
    }

    /**
     * Tries to find
     * @param array $avaiable_languages This array of languages can act like a filter.
     * If the language of the client matches one of this languages, that language will be
     * returned, otherwise the default language will be returned.
     * @param string $default_language Default language
     * @return string
     */
    public function findLanguage($avaiable_languages = NULL, $default_language = 'en') {
        if ($this->header('Accept-Language')) {
            $langs = explode(',', preg_replace("/\;q\=[0-9]{1,}\.[0-9]{1,}/", "", $this->header('Accept-Language')));
            //start going through each one
            foreach ($langs as $key => $choice) {
                $choice = strtolower($choice);
                if (isset($avaiable_languages)) {
                    foreach ($avaiable_languages as $avlang) {
                        if ($choice == strtolower($avlang) || str_replace("-", "_", $choice) == strtolower($avlang) //idioma_pais
                                || preg_replace("/[-_][a-zA-Z0-9_-]{0,}/", "", $choice) == strtolower($avlang) //idioma
                                || preg_replace("/[a-zA-Z0-9_-]{0,}[-_]/", "", $choice) == strtolower($avlang) //pais
                        ) {

                            return $avlang;
                        }
                    }
                } else {
                    return $choice;
                }
            }
            return $default_language;
        } else {
            return $default_language;
        }
    }

    /**
     * Check if a TLS connection is established.
     * 
     * Implementation of expressjs req.secure
     * 
     * @return boolean
     */
    public function isSecure() {
        return $this->protocol == 'https';
    }

    /**
     * Check if the request was issued with the "X-Requested-With" header field
     * set to "XMLHttpRequest".
     * 
     * Implementation of expressjs req.xhr
     * 
     * @return boolean
     */
    public function isXhr() {
        return $this->header('X-Requested-With') == 'XMLHttpRequest';
    }

    /**
     * isXhr alias
     * @return boolean
     */
    public function isAjax() {
        return $this->isXhr();
    }

    /**
     * Returns true if the REQUEST_METHOD is POST
     * @return bool
     */
    public function isPost() {
        return $this->method == "POST";
    }

    /**
     * Returns true if the REQUEST_METHOD is GET
     * @return bool
     */
    public function isGet() {
        return $this->method == "GET";
    }

    /**
     * Returns true if the REQUEST_METHOD is HEAD
     * @return bool
     */
    public function isHead() {
        return $this->method == "HEAD";
    }

    /**
     * Returns true if the REQUEST_METHOD is PUT
     * @return bool
     */
    public function isPut() {
        return $this->method == "PUT";
    }

    /**
     * Returns true if the REQUEST_METHOD is HEAD
     * @return bool
     */
    public function isDelete() {
        return $this->method == "HEAD";
    }

    /**
     * Returns true if some file is being uploaded and method is POST
     * @return bool
     */
    public function isUpload() {
        return ($this->method == "POST") && (!empty($this->files));
    }

    public function isWebFile($exts = 'css|js|jpg|png|gif|swf|svg|otf|eot|woff|ttf|avi|mp3|mp4|mpg|mov|mpeg|mkv|ogg|ogv|oga|aac|wmv|wma|rm|webm|webp|pdf|zip|gz|tar|rar|7z') {
        return (preg_match("/\.({$exts})$/", $this->extension) != false);
    }

    public function previousUrl() {
        return $this->header('Referer');
    }

    /**
     * Returns a GET parameter
     * 
     * @param array $arr Associated array of values
     * @param string $key Array key name
     * @param mixed $default Default value if the variable is not set or regexp is false
     * @param mixed $validation FILTER_* constant value, regular expression or callable method/function (that returns a boolean i.e. is_string)
     * @return mixed The variable value
     */
    public function get($key, $default = NULL, $validation = NULL) {
        return pure_arr::check($this->query, $key, $default, $validation);
    }

    /**
     * Returns a POST/PUT parameter
     * 
     * @param array $arr Associated array of values
     * @param string $key Array key name
     * @param mixed $default Default value if the variable is not set or regexp is false
     * @param mixed $validation FILTER_* constant value, regular expression or callable method/function (that returns a boolean i.e. is_string)
     * @return mixed The variable value
     */
    public function post($key, $default = NULL, $validation = NULL) {
        return pure_arr::check($this->body, $key, $default, $validation);
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
    public function param($key, $default = NULL, $validation = NULL) {
        if (isset($this->body[$key])) {
            return $this->post($key, $default, $validation);
        }
        if (isset($this->query[$key])) {
            return $this->get($key, $default, $validation);
        }
        return $default;
    }

    /**
     * Returns a COOKIE parameter
     * 
     * @param array $arr Associated array of values
     * @param string $key Array key name
     * @param mixed $default Default value if the variable is not set or regexp is false
     * @param mixed $validation FILTER_* constant value, regular expression or callable method/function (that returns a boolean i.e. is_string)
     * @return mixed The variable value
     */
    public function cookie($key, $default = NULL, $validation = NULL) {
        return pure_arr::check($this->cookies, $key, $default, $validation);
    }

    /**
     * Returns a route segment using the full route (including language, controller and action parts)
     * 
     * @param array $arr Associated array of values
     * @param string $key Segment index
     * @param mixed $default Default value if the variable is not set or regexp is false
     * @param mixed $validation FILTER_* constant value, regular expression or callable method/function (that returns a boolean i.e. is_string)
     * @return mixed The variable value
     */
    public function segment($key, $default = NULL, $validation = NULL) {
        return pure_arr::check($this->segments, $key, $default, $validation);
    }

    public function userAgent() {
        return $this->header('User-Agent');
    }

    /**
     * Returns the detected version of Internet Explorer
     * @return float|false Version number as float, or false if it's not IE
     */
    public function uaIeVersion() {
        $match = preg_match('/MSIE ([0-9]{1,}\.[0-9]{1,})/', $this->userAgent(), $reg);
        if ($match == 0) {
            return false;
        } else {
            $v = floatval($reg[1]);
            return ($v > 0) ? $v : false;
        }
    }

    /**
     * Checks if the current User Agent is Internet Explorer
     * @return boolean
     */
    public function uaIsIe() {
        $v = $this->uaIeVersion();
        return ($v !== false) and ($v > 0);
    }

    /**
     * Generates CSS classes for IE targeting
     * E.g. if IE7 is detected it will return: "ie ie7 ielt12 ielt11 ielt10 ielt9 ielt8 iegt6"
     * @param int $minVer Minimum version to generate classes for
     * @param int $maxVer Maximum version to generate classes for
     * @param string $classPrefix
     * @param int|null $ieVer (Optional) This will override the detected IE version
     * @return string
     */
    public function uaIeSelectors($minVer = 6, $maxVer = 12, $classPrefix = '', $ieVer = null) {
        $classes = array();
        if ($ieVer === null) {
            $ieVer = $this->uaIeVersion();
        }
        if ($ieVer === false) {
            $classes = array("no-ie");
        } else {
            $ieVer = intval($ieVer);
            $classes = array('ie');

            for ($i = $minVer; $i < $ieVer; $i++) {
                $classes[] = 'iegt' . $i;
            }

            $classes[] = 'ie' . $ieVer;

            for ($i = ($ieVer + 1); $i <= $maxVer; $i++) {
                $classes[] = 'ielt' . $i;
            }
        }

        return implode(' ' . $classPrefix, $classes);
    }

}