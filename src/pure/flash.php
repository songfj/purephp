<?php

/**
 * Simple session flash message logging
 */
class pure_flash {

    /**
     *
     * @var pure_session
     */
    protected $session;

    /**
     *
     * @var string 
     */
    protected $channel;

    /**
     *
     * @var string 
     */
    protected $session_prefix;

    /**
     *
     * @var pure_flash 
     */
    protected static $instance = null;

    public function __construct(pure_session $session, $channel = "default") {
        $this->session = $session;
        $this->channel = $channel;
        $this->session_prefix = '_' . get_class($this) . '_';
        if(!isset($this->session[$this->session_prefix . $this->channel])){
            $this->session[$this->session_prefix . $this->channel] = array();
        }
    }

    /**
     * 
     * @param pure_session $session
     * @return pure_flash
     */
    public static function getInstance(pure_session $session) {
        if (self::$instance == null) {
            self::$instance = new self($session);
        }
        return self::$instance;
    }

    /**
     * 
     * @param string $level
     * @param string $message
     * @param array $context
     */
    public function write($level, $message, array $context = array()) {
        $messages = $this->session[$this->session_prefix . $this->channel];
        if (!is_array($messages)) {
            $messages = array();
        }
        $messages[] = array("level" => $level, "message" => $message, "context" => $context);
        $this->session[$this->session_prefix . $this->channel] = $messages;
    }

    public function getMessages($clear = true) {
        if ($clear == true) {
            return $this->session->getOnce($this->session_prefix . $this->channel);
        } else {
            return $this->session[$this->session_prefix . $this->channel];
        }
    }

    public function hasMessages() {
        return is_array($this->session[$this->session_prefix . $this->channel]) and
                (count($this->session[$this->session_prefix . $this->channel]) > 0);
    }

    public function clearMessages() {
        $this->session[$this->session_prefix . $this->channel] = array();
    }

}