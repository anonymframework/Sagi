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

    /**
     * @var bool
     */
    protected $useCache = true;

    public function bootCache()
    {
        $configs = ConfigManager::get('cache');

        $selectedDriver = isset($configs['driver']) ? $configs['driver'] : 'memcache';

        if ($selectedDriver === 'memcache') {
            $driverName = 'MemcacheDriver';
        } else {
            $driverName = 'RedisDriver';
        }

        $driverName = __NAMESPACE__ . '\\Cache\\' . $driverName;


        $driver = new $driverName;


        $defaultConfig = ConfigManager::get('cache.' . $selectedDriver . '.default', false);

        if ($defaultConfig) {
            $selectedConfigs = ConfigManager::get('cache.' . $selectedDriver . $defaultConfig, []);

            if (!empty($selectedConfigs)) {
                $driver->boot(ConfigManager::get('cache.' . $selectedDriver . $driverName, []));

                static::$driver = $driver;
            }
        }

        $this->getEventManager()->listen('after_update', function ($return) {

            if ($return instanceof Model) {
                $attributes = $return->getAttributes();

                $this->setCache($this->prepareCacheKey(), $attributes);
            }
        });
    }

    /**
     * @param DriverInterface $driver
     */
    public static function setCacheDriver(DriverInterface $driver)
    {
        static::$driver = $driver;
    }

    /**
     * @param bool $use
     * @return $this
     */
    public function useCache($use = true)
    {
        $this->useCache = $use;

        return $this;
    }

    /**
     * @return string
     */
    protected function prepareCacheKey()
    {
        $limit = (array)$this->limit;
        $order = (array)$this->order;

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
     * @param $expiration
     * @return bool
     */
    public function setCache($key, $value, $expiration = false)
    {
        return static::$driver->set($key, gzcompress($value), is_int($expiration) ? $expiration : $this->getExpiration());
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
