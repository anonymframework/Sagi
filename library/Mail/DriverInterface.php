<?php
/**
 * This file belongs to the AnoynmFramework
 *
 * @author vahitserifsaglam <vahit.serif119@gmail.com>
 * @see http://gemframework.com
 *
 * Thanks for using
 */

namespace Sagi\Mail;

/**
 * the interface of driver
 *
 * Interface DriverInterface
 * @package Sagi\Mail
 */
interface DriverInterface
{

    /**
     * send the prepared mail content.
     *
     * @return bool
     */
    public function send();

    /**
     * register the message subject
     *
     * @param string $subject the subject of message
     * @return $this
     */
    public function subject($subject = '');

    /**
     * Set the address information sent by mail.
     *
     * @param string $mail the address of mail
     * @param string $name the real name of mail sender
     * @return $this
     */
    public function from($mail, $name);

    /**
     * set the address information to be send
     *
     * @param string|array $mail the address of mail
     * @param null|string $name the real name of mail receiver
     * @return $this
     */
    public function to($mail, $name = null);

    /**
     * register the message body
     *
     * @param string $body the message body
     * @param string $contentType the type of message content
     * @return $this
     */
    public function body($body = '', $contentType = 'text/html');

    /**
     * add a attachment to message
     *
     * @param  $attachment the attachment
     * @return $this
     */
    public function attach($attachment);

    /**
     * register a new bcc
     *
     * @param string|array $mail the address of mail
     * @param null $name the realname
     * @return $this
     */
    public function bcc($mail, $name = null);

    /**
     * register the address to send a reply message
     *
     * @param string $address
     * @return $this
     */
    public function returnPath($address = '');
}
