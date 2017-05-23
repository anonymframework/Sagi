<?php
/**
 *  SAGI DATABASE ORM FILE
 *
 */

namespace Sagi\Database\Cache;


use Predis\Client;

class RedisDriver implements DriverInterface
{

    /**
     * @var Client
     */
    private static $driver;

    /**
     * boot cache driver
     *
     * @param array $configs
     * @return void
     */
    public function boot($configs)
    {
        if (is_array($configs)) {
            static::$driver = new Client($configs);
        } else {
            static::$driver = $configs;

        }
    }

    /**
     * @param string $name
     * @param mixed $value
     * @param int $expiration
     * @return mixed
     */
    public function set($name, $value, $expiration = 600)
    {
        static::$driver->setex($name, $value, $expiration);
    }

    /**
     * @param strig $name
     * @return mixed
     */
    public function get($name)
    {
        static::$driver->get($name);
    }

}
