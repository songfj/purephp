<?php

/**
 * 
 * @todo Method chaining
 * @todo Implement type()
 * @todo Implement format()
 * @todo Implement attachment()
 * @todo Implement jsonp()
 * @todo Implement location() ?
 * @todo Implement sendfile()
 * @todo Implement download()
 * @todo Implement links()
 * @todo Implement locals
 */
class pure_http_response {

    /**
     * Current server protocol
     * @var string 
     */
    public $serverProtocol = "HTTP/1.1";

    /**
     * Status to be sent
     * @var int
     */
    public $status = 200;

    /**
     * Headers to be sent
     * @var array
     */
    public $headers = array(
        "Content-Type" => "text/plain; charset=utf-8"
    );

    /**
     * Cookies to be added
     * @var array
     */
    public $cookies = array();

    /**
     * Cookies to be removed
     * @var array
     */
    public $clearCookies = array();

    /**
     * Body content
     * @var string|mixed
     */
    public $body = "";

    /**
     * Message texts
     * @var array 
     */
    public static $messages = array(
        //Informational 1xx
        100 => 'Continue',
        101 => 'Switching Protocols',
        //Successful 2xx
        200 => 'OK',
        201 => 'Created',
        202 => 'Accepted',
        203 => 'Non-Authoritative Information',
        204 => 'No Content',
        205 => 'Reset Content',
        206 => 'Partial Content',
        //Redirection 3xx
        300 => 'Multiple Choices',
        301 => 'Moved Permanently',
        302 => 'Found',
        303 => 'See Other',
        304 => 'Not Modified',
        305 => 'Use Proxy',
        306 => '(Unused)',
        307 => 'Temporary Redirect',
        //Client Error 4xx
        400 => 'Bad Request',
        401 => 'Unauthorized',
        402 => 'Payment Required',
        403 => 'Forbidden',
        404 => 'Not Found',
        405 => 'Method Not Allowed',
        406 => 'Not Acceptable',
        407 => 'Proxy Authentication Required',
        408 => 'Request Timeout',
        409 => 'Conflict',
        410 => 'Gone',
        411 => 'Length Required',
        412 => 'Precondition Failed',
        413 => 'Request Entity Too Large',
        414 => 'Request-URI Too Long',
        415 => 'Unsupported Media Type',
        416 => 'Requested Range Not Satisfiable',
        417 => 'Expectation Failed',
        422 => 'Unprocessable Entity',
        423 => 'Locked',
        //Server Error 5xx
        500 => 'Internal Server Error',
        501 => 'Not Implemented',
        502 => 'Bad Gateway',
        503 => 'Service Unavailable',
        504 => 'Gateway Timeout',
        505 => 'HTTP Version Not Supported'
    );
    protected static $instance;

    public function __construct($serverProtocol = null) {
        $this->serverProtocol = !empty($serverProtocol) ? $serverProtocol : (isset($_SERVER["SERVER_PROTOCOL"]) ? $_SERVER["SERVER_PROTOCOL"] : 'HTTP/1.1');
    }

    /**
     * 
     * @return pure_http_response
     */
    public static function getInstance() {
        if (self::$instance == null) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    /**
     * Chainable alias of node's '`res.status=`.
     * 
     * @param int $code
     * @return pure_http_response
     */
    public function status($code) {
        $this->status = $code;
        return $this;
    }

    /**
     * Get / set the Content-Type header with type and charset
     * @param string $type
     * @param string $charset
     */
    public function contentType($type = null, $charset = "utf-8") {
        if (func_num_args() > 0) {
            $this->header("Content-Type", $type . "; charset=" . $charset);
        }
        return $this->header("Content-Type");
    }

    /**
     * Header getter/setter
     * Getter: Return the value of header name when present, otherwise return false.
     * Setter: Set header $name to $value, or pass an array to set multiple fields at once.
     * 
     * @param string|array $name
     * @param string $value
     * @return mixed Header value, null or false
     */
    public function header($name, $value = null) {
        // set various headers at once
        if (is_array($name)) {
            foreach ($name as $h => $v) {
                $this->header($h, $v);
            }
            return null;
        }
        // set one header
        if (func_num_args() > 1) {
            $this->headers[$name] = $value;
            return $this->headers[$name];
        }
        // get one header
        if (isset($this->headers[$name])) {
            return $this->headers[$name];
        }
        return false;
    }

    public function cookie($name, $value, array $options = array()) {
        $this->cookies[$name][] = array_merge(array(
            "name" => $name,
            "value" => $value,
            "expire" => 0,
            "path" => null,
            "domain" => null,
            "secure" => false,
            "httponly" => false
                ), $options);
        return $this;
    }

    public function clearCookie($name, array $options = array()) {
        $options = array_merge(array(
            "name" => $name,
            "value" => "",
            "expire" => 0,
            "path" => null,
            "domain" => null,
            "secure" => false,
            "httponly" => false
                ), $options);

        if (isset($this->cookies[$name])) {
            foreach ($this->cookies[$name] as $i => $o) {
                if (($o["path"] === $options["path"]) and ($o["domain"] === $options["domain"]) and ($o["secure"] === $options["secure"]) and ($o["httponly"] === $options["httponly"])) {
                    unset($this->cookies[$name][$i]);
                }
            }
        }
        $this->clearCookies[$name][] = array("options" => $options);
        return $this;
    }

    /**
     * Inmediately redirect to the given url with optional status code defaulting to 302 "Found"
     * @param string $url
     * @param int $status
     */
    public function redirect($url, $status = 302) {
        $this->sendStatusHeader($status);
        header("Location: " . $url);
        exit();
    }

    public function send($body = null, $status = null, $contentType = null) {
        if ($body !== null) {
            $this->body = $body;
        }

        if ($status !== null) {
            $this->status = $status;
        }

        if ($contentType !== null) {
            $this->contentType($contentType);
        }

        $this->header("Content-Length", strlen($this->body));

        // Trigger event
        pure::trigger('response.before_send', array(), $this);

        $this->sendStatusHeader($this->status)
                ->sendHeaders()
                ->sendCookieHeaders();

        if ($this->canHaveBody()) {
            print $this->body;
        }

        // Trigger event
        pure::trigger('response.send', array(), $this);

        return $this;
    }

    public function json($body = null, $status = null, $contentType = "text/json") {
        if ($body !== null) {
            $this->body = $body;
        }

        if (!is_array($this->body) and !is_object($this->body)) {
            $this->body = array('content' => $this->body);
        }

        return $this->send(json_encode($body), $status, $contentType);
    }

    public function send404() {
        $this->sendStatusHeader(404);
        exit();
    }

    protected function sendHeaders() {
        foreach ($this->headers as $h => $v) {
            if (is_array($v)) {
                foreach ($v as $vv) {
                    header($h . ": " . $vv);
                }
            } else {
                header($h . ": " . $v);
            }
        }
        return $this;
    }

    protected function sendCookieHeaders() {
        foreach ($this->cookies as $name => $cookies) {
            foreach ($cookies as $i => $cookie) {
                setcookie($cookie["name"], $cookie["value"], $cookie["expire"], $cookie["path"], $cookie["domain"], $cookie["secure"], $cookie["httponly"]);
            }
        }
        foreach ($this->clearCookies as $name => $cookies) {
            foreach ($cookies as $i => $cookie) {
                setcookie($cookie["name"], "", time() - 172800 /* -2 day */, $cookie["path"], $cookie["domain"], $cookie["secure"], $cookie["httponly"]);
            }
        }
        return $this;
    }

    protected function sendStatusHeader($status) {
        $statusText = $status . " " . self::$messages[$status];
        if (strpos(strtolower(PHP_SAPI), 'cgi') !== false) {
            header("Status: " . $statusText);
        } else {
            header($this->serverProtocol . " " . $statusText);
        }
        return $this;
    }

    /**
     * Can this HTTP response have a body?
     * @return boolean
     */
    public function canHaveBody() {
        return ( $this->status < 100 || $this->status >= 200 ) &&
                (!in_array($this->status, array(201, 204, 304)));
    }

    /**
     * Helpers: Informational?
     * @return boolean
     */
    public function isInformational() {
        return $this->status >= 100 && $this->status < 200;
    }

    /**
     * Helpers: OK?
     * @return boolean
     */
    public function isOk() {
        return $this->status === 200;
    }

    /**
     * Helpers: Successful?
     * @return boolean
     */
    public function isSuccessful() {
        return $this->status >= 200 && $this->status < 300;
    }

    /**
     * Helpers: Redirect?
     * @return boolean
     */
    public function isRedirect() {
        return in_array($this->status, array(301, 302, 303, 307));
    }

    /**
     * Helpers: Redirection?
     * @return boolean
     */
    public function isRedirection() {
        return $this->status >= 300 && $this->status < 400;
    }

    /**
     * Helpers: Forbidden?
     * @return boolean
     */
    public function isForbidden() {
        return $this->status === 403;
    }

    /**
     * Helpers: Not Found?
     * @return boolean
     */
    public function isNotFound() {
        return $this->status === 404;
    }

    /**
     * Helpers: Client error?
     * @return boolean
     */
    public function isClientError() {
        return $this->status >= 400 && $this->status < 500;
    }

    /**
     * Helpers: Server Error?
     * @return boolean
     */
    public function isServerError() {
        return $this->status >= 500 && $this->status < 600;
    }

    public function __toString() {
        return $this->body;
    }

}