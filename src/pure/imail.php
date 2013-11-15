<?php

/**
 * Basic mailing engine interface
 */
interface pure_imail {

    /**
     * 
     * @return pure_imail
     */
    public function markAsHtml();

    /**
     * 
     * @return pure_imail
     */
    public function markAsPlainText();

    public function isHtml();

    /**
     * 
     * @param string $value
     * @return pure_imail
     */
    public function setFrom($value);

    public function getFrom();

    /**
     * 
     * @param string $value
     * @return pure_imail
     */
    public function setSender($value);

    public function getSender();

    /**
     * 
     * @param string $value
     * @return pure_imail
     */
    public function setCharset($value);

    public function getCharset();

    /**
     * 
     * @param string $value
     * @return pure_imail
     */
    public function setCc($value);

    public function getCc();

    /**
     * 
     * @param string $value
     * @return pure_imail
     */
    public function setBcc($value);

    public function getBcc();

    /**
     * 
     * @param string $value
     * @return pure_imail
     */
    public function setReplyTo($value);

    public function getReplyTo();

    /**
     * 
     * @param string $name
     * @param string $value
     * @return pure_imail
     */
    public function setHeader($name, $value);

    /**
     * 
     * @param array $headers
     * @return pure_imail
     */
    public function setHeaders(array $headers = array());

    public function getHeader($name);

    public function getHeaders();

    public function send($to, $subject, $body, array $parameters = array());
}