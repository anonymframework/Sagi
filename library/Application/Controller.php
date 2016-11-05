<?php
/**
 *  SAGI DATABASE ORM FILE
 *
 */

namespace Sagi\Application;


use Sagi\Database\ConfigManager;
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
        static::$view = new View(ConfigManager::get('mvc.view'));
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
     * @return View
     */
    public function view($file)
    {
        if (SessionManager::has('errors')) {
            $this->with('errors', SessionManager::get('errors'));
        }

        SessionManager::delete('errors');

        return static::$view->render($file);

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
     * @param null $value
     * @return mixed
     */
    public function crypt($value = null){
        return CryptManager::encode($value);
    }

    /**
     * @param null $value
     * @return mixed
     */
    public function decrypt($value = null){
        return CryptManager::decode($value);
    }
}