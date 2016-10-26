<?php
/**
 * Bu Dosya AnonymFramework'e ait bir dosyadır.
 *
 * @author vahitserifsaglam <vahit.serif119@gmail.com>
 * @see http://gemframework.com
 *
 */

namespace Anonym\Http;

/**
 * Class Redirect
 * @package Anonym\Http
 */
class RedirectResponse extends Response implements ResponseInterface
{

    /**
     * Yönlendirme için beklenecek zaman
     *
     * @var integer
     */
    private $time;
    /**
     * Yönlendirilecek url i atar
     *
     * @var string
     */
    private $target;

    /**
     * Yönlendirme işlemi yapar
     *
     * @param string  $adress
     * @param integer $time
     * @throws RedirectUrlEmptyException
     */
    public function __construct($adress = '', $time = 0)
    {
        $this->setTarget($adress);
        $this->setTime($time);

        parent::__construct();

    }
    /**
     *  Eski sayfaya geri döndürür
     */
    public function back()
    {
        self::create($_SERVER['HTTP_REFERER']);
    }

    /**
     * @return string
     */
    public function getTarget()
    {
        return $this->target;
    }

    /**
     * @param string $target
     * @return Redirect
     */
    public function setTarget($target)
    {
        $this->target = $target;

        return $this;
    }

    /**
     * @return int
     */
    public function getTime()
    {
        return $this->time;
    }

    /**
     * @param int $time
     * @return Redirect
     */
    public function setTime($time)
    {
        $this->time = $time;

        return $this;
    }


    /**
     * Yönlendirme işlemini yapar
     *
     * @throws HttpResponseException
     */
    public function send(){

        $time = $this->getTime();
        $target = $this->getTarget();


        if($target === ''){
            throw new RedirectUrlEmptyException('Yönlendirilecek url boş olamaz');
        }

        if($time === 0)
        {
            $this->header("Location", $target);
        }else{
            $this->header(sprintf('Refresh:%d, url=%s', $time, $target));
        }

        parent::send();
    }


    /**
     * Static olarak içeriği oluşturur
     *
     * @param string $target
     * @param int $time
     * @return static
     */
    public static function create($target = '', $time = 0)
    {
        return new static($target, $time);
    }
}
