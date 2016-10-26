<?php
/**
 * Bu Dosya AnonymFramework'e ait bir dosyadır.
 *
 * @author vahitserifsaglam <vahit.serif119@gmail.com>
 * @see http://gemframework.com
 *
 */

namespace Sagi\Http;

/**
 * Class RequestHeaders
 * @package Anonym\Http
 */
class RequestHeaders
{
    /**
     * Headerları depolar
     *
     * @var array|false
     */
    protected $headers;

    /**
     * server bilgilerini depolar
     *
     * @var array
     */
    private $server;

    /**
     * sınıfı başlatır
     */
    public function __construct()
    {
        $this->headers = function_exists('getallheaders') ? getallheaders() : [];
        $this->server = $_SERVER;
    }

    /**
     * return the server variables
     *
     * @return mixed
     */
    public function getServer()
    {
        return $this->server;
    }

    /**
     * Header'ları ekler
     *
     * @return mixed
     */
    public function getHeaders()
    {
        return $this->headers;
    }
}
