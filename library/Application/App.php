<?php

namespace Sagi\Application;

use Sagi\Database\SessionManager;
use Sagi\Database\View;
use Sagi\Http\Request;

class App
{
    /**
     * @var array
     */
    protected $configs;

    /**
     * @var string
     */
    private $url;

    private static $uri;

    /**
     * @var
     */
    protected $controller;

    /**
     * App constructor.
     * @param array $configs
     */
    public function __construct(array $configs = [],Request $request)
    {
        $this->configs = $configs;
        $this->url = $request->getUri();
        static::$uri = $request->getUri();
    }

    public function handleRequest()
    {
        $this->url = substr($this->url, 1, strlen($this->url));
        $parsed = explode("/", $this->url);

        switch (count($parsed)) {

            case 1:
                if ($parsed[0] === "") {
                    $this->getDefaultController();
                } else {
                    $this->callMethodByController($parsed[0], "index", []);
                }
                break;
            case 2:
                $controller = ucfirst($parsed[0]);
                $method = $parsed[1];

                $this->callMethodByController($controller, $method);
                break;
            case 3:
                $controller = ucfirst($parsed[0]);
                $method = $parsed[1];
                $arg = $parsed[2];

                $this->callMethodByController($controller, $method, $arg);
                break;

            default:
                $controller = $parsed[0];
                $method = $parsed[1];

                unset($parsed[0]);
                unset($parsed[1]);

                $this->callMethodByController($controller, $method, $parsed);
                break;

        }
    }

    /**
     *
     */
    public function getDefaultController()
    {
        $defaultController = $this->configs['default_controller'];

        $this->callMethodByController($defaultController, 'index', []);
    }

    /**
     * @param $controller
     * @param $method
     * @param array $args
     */
    public function callMethodByController($controller, $method, $args = [])
    {
        if (!is_array($args)) {
            $args = [$args];
        }

        $this->createControllerInstance($controller);

        call_user_func_array(array($this->controller, $method), $args);
    }

    /**
     * @param $controller
     * @return mixed
     */
    public function createControllerInstance($controller)
    {
        $controller = $this->getFullControllerNamespace(ucfirst($controller));

        $this->controller = new $controller;
    }

    public function createViewInstance()
    {
        return new View($this->configs['view']);
    }

    /**
     * @param $controller
     * @return string
     */
    public function getFullControllerNamespace($controller)
    {
        return "Controllers\\" . $controller;
    }

    /**
     * @return array
     */
    public function getConfigs()
    {
        return $this->configs;
    }

    /**
     * @param array $configs
     * @return App
     */
    public function setConfigs($configs)
    {
        $this->configs = $configs;
        return $this;
    }

    /**
     * @param $uri
     * @param null $time
     * @param array $sessions
     */
    public static function redirect($uri, $time = null, $sessions = null)
    {
        if (!is_null($sessions)) {
            SessionManager::set('errors', $sessions);
        }

        $uri = static::$uri . $uri;

        if (is_null($time)) {
            header('Location: ' . $uri);
        } else {
            header('refresh: ' . $time . ';url=' . $uri);
        }


    }
}