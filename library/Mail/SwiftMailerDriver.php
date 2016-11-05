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

use Swift_Message;
use Swift_Mailer;
use Swift_SmtpTransport;
use Sagi\Support\Arr;

/**
 * the driver of swift mailer
 *
 * Class SwiftMailerDriver
 * @package Sagi\Mail
 */
class SwiftMailerDriver implements DriverInterface
{

    /**
     * the instance of swift message
     *
     * @var Swift_Message
     */
    private $message;

    /**
     * the instance of swift mailer
     *
     * @var Swift_Mailer
     */
    private $mailer;

    /**
     * create a new instance
     *
     * @param array $configs the configs of driver, example : username, password vs.
     */
    public function __construct(array $configs = [])
    {
        $host = Arr::get($configs, 'host', '');
        $username = Arr::get($configs, 'username', '');
        $password = Arr::get($configs, 'password', '');
        $port = Arr::get($configs, 'port', 25);



        $transport = Swift_SmtpTransport::newInstance($host, $port)
            ->setUsername($username)
            ->setPassword($password);

        $this->mailer = Swift_Mailer::newInstance($transport);
        $this->message = Swift_Message::newInstance();

        if(Arr::has($configs, 'from.mail')){
            $this->message->addFrom(Arr::get($configs, 'from.mail'), Arr::get($configs, 'from.name', null));
        }
    }

    /**
     * send the prepared mail content.
     *
     * @return bool
     */
    public function send()
    {
        return (bool)$this->mailer->send($this->message);
    }

    /**
     * Set the address information sent by mail.
     *
     * @param string $mail the address of mail
     * @param string $name the real name of mail sender
     * @return $this
     */
    public function from($mail, $name)
    {
        $this->message->addFrom($mail, $name);
        return $this;
    }

    /**
     * set the address information to be send
     *
     * @param string|array $mail the address of mail
     * @param null|string $name the real name of mail receiver
     * @return $this
     */
    public function to($mail, $name = null)
    {
        $this->message->addTo($mail, $name);
        return $this;
    }

    /**
     * register the message body
     *
     * @param string $body the message body
     * @param string $contentType the type of message content
     * @return $this
     */
    public function body($body = '', $contentType = 'text/html')
    {
        $this->message->setBody($body, $contentType);
        return $this;
    }

    /**
     * add a attachment to message
     *
     * @param  $attachment the attachment
     * @return $this
     */
    public function attach($attachment)
    {

        if (is_string($attachment)) {
            $attachment = new SwiftAttachment($attachment);
        }

        if ($attachment instanceof SwiftAttachmentInterface) {
            $attachment = $attachment->getAttach();
        }

        $this->message->attach($attachment);
        return $this;

    }

    /**
     * register a new bcc
     *
     * @param string|array $mail the address of mail
     * @param null $name the realname
     * @return $this
     */
    public function bcc($mail, $name = null)
    {
        $this->message->addBcc($mail, $name);
        return $this;
    }

    /**
     * register the address to send a reply message
     *
     * @param string $address
     * @return $this
     */
    public function returnPath($address = '')
    {
        $this->message->setReturnPath($address);
        return $this;
    }

    /**
     * register the message subject
     *
     * @param string $subject the subject of message
     * @return $this
     */
    public function subject($subject = '')
    {
        $this->message->setSubject($subject);
        return $this;
    }

    /**
     * call the all methods in message variable
     *
     * @param string $method
     * @param array $args
     * @return $this
     */
    public function __call($method, $args)
    {
        call_user_func_array([$this->message, $method], $args);
        return $this;
    }
}