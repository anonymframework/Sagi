<?php
namespace Sagi\Database;

use Memcached;

/**
 * Class Cache
 * @package Sagi\Database
 */
trait Cache
{

    /**
     * @var int
     */
    protected $expiration = 600;

    /**
     * @var \Memcached
     */
    private $memcache;

    /**
     *  makes memcache connection
     */
    private static function makeCacheConnection()
    {
        $configs = ConfigManager::get('cache');

        $this->memcache = new Memcached();


        $this->memcache->addServer($configs['host'], $configs['port']);
    }

    public static function bootCache()
    {
        static::makeCacheConnection();
    }

    /**
     * @return Memcached
     */
    public function getMemcache()
    {
        return $this->memcache;
    }

    /**
     * @param Memcached $memcache
     * @return Cache
     */
    public function setMemcache($memcache)
    {
        $this->memcache = $memcache;
        return $this;
    }

    /**
     * @return string
     */
    protected function prepareCacheKey()
    {
        $limit = (array)$this->getLimit();
        $order = (array)$this->getOrder();

        $merged = array_merge($this->getWhere(), $this->getOrWhere(), $limit, $order);


        return md5(serialize($this->getTable() . serialize($merged)));
    }


    /**
     * @param $key
     * @return mixed
     */
    public function getCache($key)
    {
        return $this->memcache->get($key);
    }

    /**
     * @param $key
     * @param $value
     * @return bool
     */
    public function setCache($key, $value)
    {
        return $this->memcache->set($key, $value, $this->expiration);
    }

    /**
     *
     */
    protected function cacheOne()
    {

        if ($result = $this->getCache($key = $this->prepareCacheKey())) {
            $this->setAttributes(unserialize($result));
        } else {
            $this->setCache(
                $key,
                serialize($get = $this->get()->fetch(PDO::FETCH_ASSOC))
            );

            $this->setAttributes($get);
        }
    }

    /**
     * @return mixed
     */
    protected function cacheAll()
    {
        $class = get_called_class();

        if ($result = $this->getCache($key = $this->prepareCacheKey())) {

            $result = $this->setAttributes(unserialize($result));

        } else {
            $this->setCache(
                $key, serialize(
                $get = $this->get()->fetchAll(PDO::FETCH_CLASS, $class)
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
