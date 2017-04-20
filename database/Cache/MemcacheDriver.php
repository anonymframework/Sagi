<?php
/**
 *  SAGI DATABASE ORM FILE
 *
 */

namespace Sagi\Database\Cache;
use Memcached;

class MemcacheDriver implements DriverInterface
{

    /**
     * @var Memcached
     */
    private static $memcache;

    /**
     * boot cache driver
     *
     * @param array $configs
     * @throws \Exception
     * @return void
     */
    public function boot($configs)
    {

        if (is_array($configs)) {
            if (class_exists('Memcached') === false) {
                throw new \Exception('Memcache extension could not found');
            }

            static::$memcache = new Memcached();

            static::$memcache->addServer($configs['host'], $configs['port']);
        }elseif($configs instanceof Memcached){
            static::$memcache = $configs;
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
        return static::$memcache->set($name, $value, $expiration);
    }

    /**
     * @param string $name
     * @return mixed
     */
    public function get($name)
    {
        return static::$memcache->get($name);
    }
}
