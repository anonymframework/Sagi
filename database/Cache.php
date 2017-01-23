<?php
namespace Sagi\Database;

use Memcached;
use Sagi\Database\Cache\DriverInterface;

/**
 * Class Cache
 * @package Sagi\Database
 */
trait Cache
{
    /**
     * @var DriverInterface
     */
    private static $driver;

    public function bootCache()
    {
        $configs = ConfigManager::get('cache');

        $driverName = isset($configs['driver']) ? $configs['driver'] : 'memcache';

        $driver = new $driverName;


        $driver->boot(ConfigManager::get('cache.'.$driverName, []));

    }

    /**
     * @return Memcached
     */
    public function getMemcache()
    {
        return static::$memcache;
    }

    /**
     * @param Memcached $memcache
     * @return Cache
     */
    public function setMemcache($memcache)
    {
        static::$memcache = $memcache;
        return $this;
    }

    /**
     * @return string
     */
    protected function prepareCacheKey()
    {
        $limit = (array) $this->limit;
        $order = (array) $this->order;

        $merged = array_merge($this->where, $limit, $order);


        return substr(md5(json_encode($this->getTable() . serialize($merged))), 0, 22);
    }


    /**
     * @param $key
     * @return mixed
     */
    public function getCache($key)
    {
        return gzuncompress(static::$driver->get($key));
    }

    /**
     * @param $key
     * @param $value
     * @return bool
     */
    public function setCache($key, $value)
    {
        return static::$driver->set($key, gzcompress($value), $this->getExpiration());
    }

    /**
     * @return mixed
     */
    public function serializeResults()
    {
        $result = $this->get()->fetchAll(\PDO::FETCH_ASSOC);

        return $result;
    }

    /**
     *
     */
    protected function cacheOne()
    {

        if ($result = $this->getCache($key = $this->prepareCacheKey())) {
            $this->setAttributes(json_decode($result));
        } else {
            $this->setCache(
                $key,
                serialize($get = $this->serializeResults())
            );

            $this->setAttributes($get);
        }
    }

    /**
     * @return mixed
     */
    protected function cacheAll()
    {

        if ($result = $this->getCache($key = $this->prepareCacheKey())) {

            $result = $this->setAttributes(unserialize($result));

        } else {
            $this->setCache(
                $key, serialize(
                $get = $this->serializeResults()
            ));

            $result = $this->setAttributes($get);
        }

        return $result;
    }

    /**
     * @return int
     */
    public function getExpiration()
    {
        return $this->expiration;
    }

    /**
     * @param int $expiration
     * @return Cache
     */
    public function setExpiration($expiration)
    {
        $this->expiration = $expiration;
        return $this;
    }
}
