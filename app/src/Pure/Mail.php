<?php

/**
 * Basic PHP mailing engine
 */
class Pure_Mail extends Swift_Message implements Pure_IInjectable {

    /**
     *
     * @var Pure_App
     */
    protected $app;

    public function setApp(Pure_App $app) {
        $this->app = $app;
    }

    /**
     * @return Pure_App
     */
    public function getApp() {
        return $this->app;
    }

    /**
     * Create a new Message.
     *
     * @param string $subject
     * @param string $body
     * @param string $contentType
     * @param string $charset
     *
     * @return Pure_Mail
     */
    public static function newInstance($subject = null, $body = null, $contentType = null, $charset = null) {
        return new self($subject, $body, $contentType, $charset);
    }

    /**
     * Send the given Message like it would be sent in a mail client.
     *
     * All recipients (with the exception of Bcc) will be able to see the other
     * recipients this message was sent to.
     *
     * Recipient/sender data will be retrieved from the Message object.
     *
     * The return value is the number of recipients who were accepted for
     * delivery.
     *
     * @param Swift_Mime_Message $message
     * @param array              $failedRecipients An array of failures by-reference
     *
     * @return int
     */
    public function send(&$failedRecipients = null) {
        return $this->app->mailer()->send($this, $failedRecipients);
    }

}
