<?php

/**
 * Class for generating API array messages
 * 
 * Status codes:
 * 
 * 0      OK (for successful results)
 * 1x     Informational
 * 2x     Notifications
 * 3x     Warnings
 * 4x     Errors (user errors)
 * 5x     Critical Errors (server errors)
 */
class pure_msg {
    /**
     * OK status
     */

    const STATUS_OK = 0;

    /**
     * Generic informational status
     */
    const STATUS_INFO = 10;

    /**
     * Generic notification status
     */
    const STATUS_NOTIFICATION = 20;

    /**
     * Generic warning status
     */
    const STATUS_WARNING = 30;

    /**
     * Generic invalid request error
     */
    const STATUS_ERROR_INVALID_REQUEST = 40;

    /**
     * Method not found error
     */
    const STATUS_ERROR_ACTION_NOT_FOUND = 41;

    /**
     * Invalid params error
     */
    const STATUS_ERROR_INVALID_PARAMS = 42;

    /**
     * Exception error
     */
    const STATUS_ERROR_EXCEPTION = 43;

    /**
     * Not Found error
     */
    const STATUS_ERROR_NOT_FOUND = 44;

    /**
     * Method not found error
     */
    const STATUS_ERROR_METHOD_NOT_IMPLEMENTED = 45;

    /**
     * Generic critical server error
     */
    const STATUS_CRITICAL_SERVER_ERROR = 50;

    public static function result($data = null, $statusMessage = null, $logs = null, $apiVersion = '1.0') {
        return self::message($data, 0, $statusMessage, $logs, $apiVersion);
    }

    public static function info($statusMessage = null, $statusCode = 10, $logs = null, $apiVersion = '1.0') {
        if (($statusCode < 10) || ($statusCode > 19))
            $statusCode = 10;
        return self::message(null, $statusCode, $statusMessage, $logs, $apiVersion);
    }

    public static function notification($statusMessage = null, $statusCode = 20, $logs = null, $apiVersion = '1.0') {
        if (($statusCode < 20) || ($statusCode > 29))
            $statusCode = 20;
        return self::message(null, $statusCode, $statusMessage, $logs, $apiVersion);
    }

    public static function warning($statusMessage = null, $statusCode = 30, $logs = null, $apiVersion = '1.0') {
        if (($statusCode < 30) || ($statusCode > 39))
            $statusCode = 30;
        return self::message(null, $statusCode, $statusMessage, $logs, $apiVersion);
    }

    public static function error($statusMessage = null, $statusCode = 40, $logs = null, $apiVersion = '1.0') {
        if (($statusCode < 40) || ($statusCode > 49))
            $statusCode = 40;
        return self::message(null, $statusCode, $statusMessage, $logs, $apiVersion);
    }

    public static function critical($statusMessage = null, $statusCode = 50, $logs = null, $apiVersion = '1.0') {
        if (($statusCode < 50) || ($statusCode > 59))
            $statusCode = 50;
        return self::message(null, $statusCode, $statusMessage, $logs, $apiVersion);
    }

    /**
     * Generates an API message
     * 
     * @param mixed $data
     * @param int $statusCode
     * @param string $statusMessage
     * @param string $apiVersion API Version
     * @param array $logs
     */
    public static function message($data, $statusCode = 0, $statusMessage = null, $logs = null, $apiVersion = '1.0') {
        if ($logs == null)
            $logs = array();

        return array(
            "api" => $apiVersion,
            "data" => $data,
            "status" => array("code" => $statusCode, "message" => (empty($statusMessage) ? self::getStatusMessage($statusCode) : $statusMessage), 'logs' => $logs)
        );
    }

    public static function getDefaultMessage() {

        return self::message(null, self::STATUS_OK, 'JSON API');
    }

    /**
     * Returns the status code message
     * 
     * @param type $statusCode
     * @return string|null
     */
    public static function getStatusMessage($statusCode) {
        switch ($statusCode) {
            case 0: return "OK";
            case 10: return null;
            case 20: return null;
            case 30: return "Warning";
            case 40: return "Invalid Request";
            case 41: return "Action Not Found";
            case 42: return "Invalid Parameters";
            case 43: return "Error Exception";
            case 44: return "Not Found";
            case 45: return "Method Not Implemented";
            case 50: return "Internal Server Error";
            default: return null;
        }
    }

}