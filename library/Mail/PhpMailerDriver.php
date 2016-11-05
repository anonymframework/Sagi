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

use Sagi\Support\Arr;
use PHPMailer;

/**
 * the driver of phpmailer for AnonymFramework mail component
 *
 * Class PhpMailerDriver
 * @package Sagi\Mail
 */
class PhpMailerDriver implements DriverInterface
{

    /**
     * the instance of mail driver
     *
     * @var PHPMailer
     */
    private $mailer;

    /**
     * create a new instance and register the configs
     *
     * @param array $configs the configs for phpmailer driver
     */
    public function __construct(array $configs = [])
    {
        $host = Arr::get($configs, 'host', '');
        $username = Arr::get($configs, 'username', '');
        $password = Arr::get($configs, 'password', '');
        $port = Arr::get($configs, 'port', 25);
        $secure = Arr::get($configs, 'secure', 'tsl');

        $phpmailer = new PHPMailer(false);
        $phpmailer->isSMTP();
        $phpmailer->Host = $host;
        $phpmailer->Port = $port;
        $phpmailer->Username = $username;
        $phpmailer->Password = $password;
        $phpmailer->SMTPSecure = $secure;
        $phpmailer->isHTML(true);

        if(Arr::has($configs, 'from.mail')){
            $phpmailer->setFrom(Arr::get($configs, 'from.mail'), Arr::get($configs, 'from.name', null));
        }

        $this->mailer = $phpmailer;
    }

    /**
     * send the prepared mail content.
     *
     * @return bool
     */
    public function send()
    {
        return $this->mailer->send();
    }

    /**
     * register the message subject
     *
     * @param string $subject the subject of message
     * @return $this
     */
    public function subject($subject = '')
    {
        $this->mailer->Subject = $subject;
        return $this;
    }

    /**
     * Set the address information sent by mail.
     *
     * @param string $mail the address of mail
     * @param string $name the real name of mail sender
     * @return $this
     */
    public function from($mail, $name = null)
    {
        $this->mailer->setFrom($mail, $name);
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
        $this->mailer->addAddress($mail, $name);
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
        $this->mailer->Body = $body;
        $this->mailer->ContentType = $contentType;
        return $this;
    }

    /**
     * add a attachment to message
     *
     * @param  PhpMailerAttachment|string|array $attachment the attachment
     * @return $this
     */
    public function attach($attachment)
    {

        // if string
        if (is_string($attachment)) {
            $attachment = [$attachment, ''];
        }

        // if array
        if (is_array($attachment)) {
            list($name, $type) = $attachment;
        }

        if ($attachment instanceof PhpMailerAttachmentInterface) {
            $filename = $attachment->getFile();
            $name = ($attachment->getNewName()) ? $attachment->getNewName() : $filename;
            $type = $attachment->getType();
        }


        $this->mailer->addAttachment($filename, $name, 'base64', $type);
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
        $this->mailer->addBCC($mail, $name);
        return $this;
    }

    /**
     * register the address to send a reply message
     *
     * @param string $address
     * @param string name
     * @return $this
     */
    public function returnPath($address = '', $name = '')
    {
        $this->mailer->addReplyTo($address, $name);
        return $this;

    }

    /**
     * call the all methods in mailer variable
     *
     * @param string $method
     * @param array $args
     * @return $this
     */
    public function __call($method, $args)
    {
        call_user_func_array([$this->mailer, $method], $args);
        return $this;
    }
}
