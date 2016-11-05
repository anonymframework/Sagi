<?php

namespace Sagi\Application;

use Sagi\Http\Request;
use Sagi\Http\Response;

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

    /**
     * @var string
     */
    private static $uri;

    /**
     * @var
     */
    protected $controller;

    /**
     * @var Request
     */
    protected $request;

    /**
     * App constructor.
     * @param array $configs
     * @param Request $request
     */
    public function __construct(array $configs = [], Request $request)
    {
        $this->configs = $configs;
        $this->url = $request->query('path');
        static::$uri = $request->query('path');
        $this->request = $request;
    }

    public function handleRequest()
    {

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

        $content = call_user_func_array(array($this->controller, $method), $args);


        if (!$content instanceof Response) {

            if($content instanceof View){
                 $content->show();
            }else{
                $this->request->getResponse()->setContent($content)->send();
            }


        } else {
            $content->send();
        }
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
}