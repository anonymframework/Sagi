<?php
/**
 * This file belongs to the AnoynmFramework
 *
 * @author vahitserifsaglam <vahit.serif119@gmail.com>
 * @see http://gemframework.com
 *
 * Thanks for using
 */


namespace Sagi\Http;




/**
 * Class Redirect
 * @package Anonym\Http
 */
class Redirect
{

    /**
     * the instance of session
     *
     * @var StrogeInterface
     */
    protected $session;
    /**
     * the instance of error bag
     *
     * @var ErrorBag
     */
    protected $errorBag;


    /**
     * the instance of redirect response
     *
     * @var RedirectResponse
     */
    protected $redirector;

    /**
     * determine headers is sended
     *
     * @var bool
     */
    protected $sended = false;

    /**
     * create a new instance with error bag
     *
     * @param ErrorBag $errorBag
     * @param RedirectResponse $redirect
     */
    public function __construct(ErrorBag $errorBag, RedirectResponse $redirect)
    {
        $this->errorBag = $errorBag;
        $this->redirector = $redirect;
    }


    /**
     * redirect user to somewhere else
     *
     * @param string $url
     * @param int $time
     * @param array $headers
     * @return $this
     */
    public function to($url = '', $time = 0, array $headers = [])
    {
        $this->redirector->setTarget($url)->setTime($time)->setHeaders($headers);

        return $this;
    }

    /**
     * add or set error messages
     *
     * @param array|string $message
     * @return $this
     */
    public function withError($message)
    {
        if (is_array($message)) {
            $this->errorBag->setErrors($message);
        } else {
            $this->errorBag->add($message);
        }

        return $this;
    }


    /**
     * redirect with input
     *
     * @param array|string $name
     * @param mixed $message
     * @return $this
     */
    public function withInput($name, $message = null)
    {
        if (!is_array($name)) {
            $name = [$name, $message];
        }

        foreach ($name as $key => $message) {
            Session::set($key, $message);
        }

        return $this;
    }

    public function isStarted()
    {

        if ($this->redirector->getTarget()) {
            return true;
        }

        return false;
    }

    /**
     * redirect with single or multipile cookies
     *
     * @param array|string $name
     * @param mixed $message
     * @param int $time
     * @return $this
     */
    public function withCookie($name, $message = null, $time = 3600)
    {
        if (!is_array($name)) {
            $name = [$name, $message];
        }

        foreach ($name as $key => $message) {
            $this->redirector->getCookieBase()->set($key, $message, $time);
        }

        return $this;
    }

    /**
     * redirect user to it referer url
     *
     * @param int $time
     * @return $this
     */
    public function back($time = 0)
    {
        $this->redirector->setTarget(Request::back());
        $this->redirector->setTime($time);

        return $this;
    }


    /**
     * redirect to a route
     *
     * @param string $name
     * @throws RouteNotFoundException
     */
    public function route($name = '')
    {
        $routes = AsCollector::getAs();

        if (isset($routes[$name])) {
            $this->to($routes[$name]);
        } else {
            throw new RouteNotFoundException(sprintf('%s Route Not Found'));
        }
    }

    /**
     * @return boolean
     */
    public function isSended()
    {
        return $this->sended;
    }

    /**
     * send redirect responses
     *
     * @throws HeadersAlreadySendedException
     */
    public function send()
    {
        if (!$this->sended) {
            $this->redirector->send();
        } else {
            throw new HeadersAlreadySendedException('Headers already sended, you cant send them again');
        }
    }
}
