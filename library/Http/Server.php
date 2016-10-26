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
 * Class Server
 * @package Anonym\Http
 */
class Server
{

    /**
     * the http headers list
     *
     * @var array
     */
    private $references = [
        'useragent' => 'HTTP_USER_AGENT',
        'referer'   => 'HTTP_REFERER',
        'host'      => 'HTTP_HOST',
        'reditect'  => 'REDIRECT_URL',
        'serverip'  => 'SERVER_ADDR',
        'userip'    => 'REMOTE_ADDR',
        'uri'       => 'REQUEST_URI',
        'method'    => 'REQUEST_METHOD',
        'protocol'  => 'SERVER_PROTOCOL',
        'port'      => 'SERVER_PORT',
        'scheme'    => 'REQUEST_SCHEME',
        'root'      => 'DOCUMENT_ROOT'
    ];

    /**
     * get the variable in server
     *
     * @param string $name
     * @return string
     */
    public function get($name = 'HTTP_HOST')
    {
        $name = isset($this->references[$name]) ? $this->references[$name]: $this->resolveCase($name);

        return $this->has($name) ? $_SERVER[$name] : false;
    }

    /**
     * resolve the case type
     *
     * @param string $name
     * @return string
     */
    private function resolveCase($name)
    {
        return mb_convert_case($name, MB_CASE_UPPER, 'UTF-8');
    }

    /**
     * check the variable
     *
     * @param string $name
     * @return bool
     */
    public function has($name)
    {
        return isset($_SERVER[$name]);
    }

    /**
     * remova a server variable
     *
     * @param string $name
     * @return $this
     */
    public function remove($name = '')
    {
        if(isset($_SERVER[$name])){
            unset($_SERVER[$name]);
        }

        return $this;
    }

    /**
     * add a new variable
     *
     * @param string $name
     * @param string $value
     * @return $this
     */
    public function add($name = '', $value = '')
    {
        $_SERVER[$name] = $value;
        return $this;
    }
}