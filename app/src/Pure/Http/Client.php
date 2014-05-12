å<?php

/**
 * Volt RESTFul HTTP Client
 */
class Pure_Http_Client extends Pure_Injectable {

    protected $baseUrl;

    public function __construct($baseUrl = '') {
        $this->setBaseUrl($baseUrl);
    }

    /**
     * 
     * @param string $uri
     * @param string $method
     * @param array $data
     * @param array $headers
     * @return Pure_Http_Response
     */
    public function call($uri, $method = "GET", array $data = array(), $headers = array()) {
        $startmtime = microtime(true);
        $result = false;

        try {
            $uri = $this->parseUri($uri);
            $method = strtoupper($method);
            $headers = $this->parseHeaders($headers);

            $ch = curl_init();

            if ($method == 'POST') {
                curl_setopt($ch, CURLOPT_POST, true);
                curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
            } elseif ($method == 'PUT') {
                $data_str = stripslashes(http_build_query($data));

                // Put string into a temporary file
                $putData = tmpfile();
                // Write the string to the temporary file
                fwrite($putData, $data_str);
                // Move back to the beginning of the file
                fseek($putData, 0);
                // Binary transfer i.e. --data-BINARY
                curl_setopt($ch, CURLOPT_BINARYTRANSFER, true);
                // Using a PUT method i.e. -XPUT
                curl_setopt($ch, CURLOPT_PUT, true);
                // Instead of POST fields use these settings
                curl_setopt($ch, CURLOPT_INFILE, $putData);
                curl_setopt($ch, CURLOPT_INFILESIZE, strlen($data_str));
            } else {
                $query_string = http_build_query($data);

                if (!empty($query_string)) {
                    if (strpos($uri, "?") !== false) {
                        $uri .= "&" . $query_string;
                    } else {
                        $uri .= "/?" . $query_string;
                    }
                }
                if ($method != "GET") {
                    curl_setopt($ch, CURLOPT_CUSTOMREQUEST, $method);
                }
            }

            curl_setopt($ch, CURLOPT_URL, $uri);
            // Headers
            curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_VERBOSE, 1);
            curl_setopt($ch, CURLOPT_HEADER, 1); // get response headers

            $response = curl_exec($ch);

            $header_size = curl_getinfo($ch, CURLINFO_HEADER_SIZE);
            $header = substr($response, 0, $header_size);
            $body = substr($response, $header_size);
            $http_status = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            curl_close($ch);

            if (isset($putData)) {
                // Close the file
                fclose($putData);
            }

            $result = new Pure_Http_Response();
            $result->body = $body;
            $result->status = intval($http_status);
            $result->headers = $this->parseHeaderStr($header);
        } catch (Exception $exc) {
            error_log($exc->getMessage());
        }

        return $result;
    }

    /**
     * The same as call but the response body will be an object or associative array
     * @param string $uri
     * @param string $method
     * @param array $data
     * @param array $headers
     * @param boolean $assoc
     * @return Pure_Http_Response
     */
    public function json($uri, $method = "GET", array $data = array(), $headers = array(), $assoc = true) {
        $result = $this->call($uri, $method, $data, $headers);
        $jsondata = @json_decode($result->body, $assoc);

        if (is_array($jsondata) || is_object($jsondata)) {
            $result->body = $jsondata;
        }

        return $result;
    }

    /**
     * 
     * @return array
     */
    public function getLastCallHeaders() {
        return $this->lastCallHeaders;
    }

    /**
     * 
     * @param string $uri
     * @param string $method
     * @param array $data
     * @param array $headers
     * @return null No response is expected
     */
    public function callAsync($uri, $method = "GET", array $data = array(), $headers = array()) {
        $startmtime = microtime(true);

        try {
            $uri = $this->parseUri($uri);
            $method = strtoupper($method);
            $headers = $this->parseHeaders($headers);

            $data_str = http_build_query($data);

            $parts = parse_url($uri);

            $fp = fsockopen($parts['host'], isset($parts['port']) ? $parts['port'] : 80, $errno, $errstr, 30);

            // Data goes in the path for a GET request
            if ('GET' == $method) {
                $parts['path'] .= '/?' . $data_str;
            }
            $out = "$method " . $parts['path'] . " HTTP/1.1\r\n";
            $out.= "Host: " . $parts['host'] . "\r\n";
            $out.= "Content-Type: application/x-www-form-urlencoded\r\n";
            $out.= "Content-Length: " . strlen($data_str) . "\r\n";

            foreach ($headers as $h) {
                $out.= "{$h}\r\n";
            }

            $out.= "Connection: Close\r\n\r\n";

            // Data goes in the request body for a POST request
            if ('POST' == $method && isset($data_str)) {
                $out.= $data_str;
            }
            fwrite($fp, $out);
            fclose($fp);
        } catch (Exception $exc) {
            error_log($exc->getMessage());
        }

        return null;
    }

    protected function parseUri($uri) {
        return rtrim(filter_var($uri, FILTER_VALIDATE_URL) ? trim($uri, "/ ") : $this->baseUrl . "/" . trim($uri, "/ "), "/ ") . "/";
    }

    protected function parseHeaders(array $headers) {

        $headers2 = array();
        if (!isset($headers["User-Agent"])) {
            $headers["User-Agent"] = "PUREPHP Framework";
        }

        foreach ($headers as $k => $v) {
            $headers2[] = $k . ":" . $v;
        }

        return $headers2;
    }

    public function __call($name, $arguments) {
        preg_match('/^(get|post|put|delete|head|options|patch|trace)/i', $name, $method);

        // set method
        array_unshift($arguments, (count($method) > 1) ? strtoupper($method[1]) : "GET");
        // set uri
        array_unshift($arguments, strtolower(humanize(preg_replace('/^(get|post|put|delete|head|options|patch|trace)/i', '', $name), "/")));

        // call
        return call_user_func_array(array($this, "call"), $arguments);
    }

    public function getBaseUrl() {
        return $this->baseUrl;
    }

    public function setBaseUrl($baseUrl) {
        $this->baseUrl = rtrim($baseUrl, "/ ");
    }

    /**
     * 
     * @param string $headerStr
     * @return array
     */
    public function parseHeaderStr($headerStr) {
        $retVal = array();
        $fields = explode("\r\n", preg_replace('/\x0D\x0A[\x09\x20]+/', ' ', $headerStr));
        foreach ($fields as $field) {
            if (preg_match('/([^:]+): (.+)/m', $field, $match)) {
                $match[1] = preg_replace('/(?<=^|[\x09\x20\x2D])./e', 'strtoupper("\0")', strtolower(trim($match[1])));
                if (isset($retVal[$match[1]])) {
                    $retVal[$match[1]] = array($retVal[$match[1]], $match[2]);
                } else {
                    $retVal[$match[1]] = trim($match[2]);
                }
            }
        }
        return $retVal;
    }

}
