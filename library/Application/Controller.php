<?php
/**
 *  SAGI DATABASE ORM FILE
 *
 */

namespace Sagi\Application;


use Sagi\Database\CookieManager;
use Sagi\Database\CryptManager;
use Sagi\Database\SessionManager;

class Controller
{
    /**
     * @var View
     */
    protected static $view;

    /**
     * Controller constructor.
     */
    public function __construct()
    {
        static::$view = new View();
    }

    /**
     * @param $key
     * @param null $value
     * @return $this
     */
    public function with($key, $value = null)
    {
        static::$view->with($key, $value);

        return $this;
    }
    /**
     * @param $file
     * @return $this
     */
    public function view($file)
    {
        if (SessionManager::has('errors')) {
            $this->with('errors', SessionManager::get('errors'));
        }

        SessionManager::delete('errors');

        static::$view->render($file)->show();

        return $this;
    }

    /**
     * @param $name
     * @param null $value
     * @return mixed
     */
    public function session($name, $value = null){
        if($name && $value !== null){
            SessionManager::set($name, $value);
        }else{
            return SessionManager::get($name);
        }
    }

    /**
     * @param $name
     * @param null $value
     * @return mixed
     */
    public function cookie($name, $value = null){
        if($name && $value !== null){
            CookieManager::set($name, $value);
        }else{
            return CookieManager::get($name);
        }
    }

    /**
     * @param $name
     * @param null $value
     * @return mixed
     */
    public function crypt($name, $value = null){
        if($name && $value !== null){
            CryptManager::encode($name, $value);
        }else{
            return CryptManager::decode($name);
        }
    }
}