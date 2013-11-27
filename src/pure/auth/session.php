<?php

/**
 * Auth session manager
 * 
 * This class manages auth sessions.
 * Login detection, validation and token generation must be done before.
 * 
 */
class pure_auth_session {

    protected $config = array(
        'session_var' => '_pureauth',
        'cookie_name' => '_pureauth',
        'cookie_lifetime' => 604800, // 7 days
        'cookie_path' => '/',
        'cookie_domain' => null,
        'cookie_secure' => false,
        'cookie_httponly' => false
    );

    /**
     *
     * @var pure_session 
     */
    protected $session;

    /**
     *
     * @var string 
     */
    protected $sessionValue = false;

    public function __construct($config = array(), pure_session $session = null) {
        $this->config = pure_arr::merge($this->config, $config);
        if (empty($session)) {
            $session = pure_session::getInstance();
        }
        if (!$session->isStarted()) {
            $session->start();
        }
        $this->session = $session;
    }

    public function config($name = null) {
        if (empty($name)) {
            return $this->config;
        }
        return isset($this->config[$name]) ? $this->config[$name] : false;
    }

    public function login($sessionValue, $cookieToken = false) {
        $this->session->set($this->config('session_var'), $sessionValue);
        $this->sessionValue = $sessionValue;
        if (!empty($cookieToken)) {
            $this->setCookieToken($cookieToken);
        }
        pure_dispatcher::getInstance()->trigger('auth.session_login', array('sessionValue' => $sessionValue, 'cookieToken' => $cookieToken), $this);
    }

    public function logout() {
        if ($this->isLoggedIn()) {
            $this->session->remove($this->config('session_var'));
            $tokens = array('session' => $this->sessionValue, 'cookie' => $this->removeCookieToken());
            $this->removeCookieToken();
            $this->sessionValue = false;
            pure_dispatcher::getInstance()->trigger('auth.session_logout', array('tokens' => $tokens), $this);
            return $tokens;
        }
        pure_dispatcher::getInstance()->trigger('auth.session_logout', array('tokens' => false), $this);
        return false;
    }

    public function isLoggedIn() {
        return ($this->sessionValue !== false) && $this->session->has($this->config('session_var')) && ($this->session->get($this->config('session_var')) === $this->sessionValue);
    }

    public function getSessionValue() {
        if ($this->isLoggedIn()) {
            return $this->session->get($this->config('session_var'));
        }
        return false;
    }

    public function getCookieToken() {
        if (isset($_COOKIE[$this->config('cookie_name')])) {
            return $_COOKIE[$this->config('cookie_name')];
        }
        return false;
    }

    public function setCookieToken($cookieToken) {
        if (empty($cookieToken)) {
            return false;
        }
        return setcookie($this->config('cookie_name'), $cookieToken, $this->config('cookie_lifetime') + time(), $this->config('cookie_path'), $this->config('cookie_domain'), $this->config('cookie_secure'), $this->config('cookie_httponly'));
    }

    public function removeCookieToken() {
        return setcookie($this->config('cookie_name'), '-', time() - 36000, $this->config('cookie_path'), $this->config('cookie_domain'), $this->config('cookie_secure'), $this->config('cookie_httponly'));
    }

}