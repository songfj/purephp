<?php

/**
 * Basic PHP mailing engine
 */
class pure_mail implements pure_imail {

    protected $contentType = 'text/html';
    protected $charset = 'utf-8';
    protected $headers = array(
        'MIME-Version' => '1.0'
    );

    public function clear() {
        $this->contentType = 'text/html';
        $this->charset = 'utf-8';
        $this->headers = array(
            'MIME-Version' => '1.0'
        );
    }

    public function getBcc() {
        return $this->getHeader('Bcc');
    }

    public function getCc() {
        return $this->getHeader('Cc');
    }

    public function getCharset() {
        return $this->charset;
    }

    public function getFrom() {
        return $this->getHeader('From');
    }

    public function getHeader($name) {
        return isset($this->headers[$name]) ? $this->headers[$name] : false;
    }

    public function getHeaders() {
        return $this->headers;
    }

    public function getReplyTo() {
        return $this->getHeader('Reply-To');
    }

    public function getSender() {
        return $this->getHeader('Sender');
    }

    public function isHtml() {
        return ($this->contentType == 'text/html');
    }

    public function markAsHtml() {
        $this->contentType = 'text/html';
        return $this;
    }

    public function markAsPlainText() {
        $this->contentType = 'text/plain';
        return $this;
    }

    public function send($to, $subject, $body, array $parameters = array()) {
        if (!isset($this->headers['Content-Type'])) {
            $this->headers['Content-Type'] = $this->contentType . ';' . $this->charset;
        }
        $headers = array();
        foreach ($this->headers as $n => $v) {
            $headers[] = $n . ': ' . $v;
        }
        //die('headers='.implode("\r\n", $headers));
        return mail($to, $subject, $body, implode("\r\n", $headers), implode("\r\n", $parameters));
    }

    public function setBcc($value) {
        $this->setHeader('Bcc', $value);
        return $this;
    }

    public function setCc($value) {
        $this->setHeader('Cc', $value);
        return $this;
    }

    public function setCharset($value) {
        $this->charset = $value;
        return $this;
    }

    public function setFrom($value) {
        $this->setHeader('From', $value);
        return $this;
    }

    public function setHeader($name, $value) {
        $this->headers[trim($name, "\n :-.")] = $value;
        return $this;
    }

    public function setHeaders(array $headers = array()) {
        $this->headers = $headers;
        return $this;
    }

    public function setReplyTo($value) {
        $this->setHeader('Reply-To', $value);
        return $this;
    }

    public function setSender($value) {
        $this->setHeader('Sender', $value);
        return $this;
    }

}