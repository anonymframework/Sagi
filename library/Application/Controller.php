<?php
/**
 *  SAGI DATABASE ORM FILE
 *
 */

namespace Sagi\Application;


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
}