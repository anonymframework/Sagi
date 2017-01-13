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
     * @var \Memcached
     */
    private static $memcache;


    public function bootCache()
    {
        $configs = ConfigManager::get('cache');

        static::$memcache = new Memcached();

        static::$memcache->addServer($configs['host'], $configs['port']);
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
        $limit = (array)$this->getLimit();
        $order = (array)$this->getOrder();

        $merged = array_merge($this->getWhere(), $this->getOrWhere(), $limit, $order);


        return substr(md5(json_encode($this->getTable() . serialize($merged))), 0, 22);
    }


    /**
     * @param $key
     * @return mixed
     */
    public function getCache($key)
    {
        return static::$memcache->get($key);
    }

    /**
     * @param $key
     * @param $value
     * @return bool
     */
    public function setCache($key, $value)
    {
        return static::$memcache->set($key, $value, $this->expiration);
    }

    public function serializeResults()
    {
        $class = get_called_class();

        if ($this->cacheMode == Model::FULL_CACHE) {
            $result =  $this->get()->fetchAll(\PDO::FETCH_CLASS, $class);
        }else{
            $result = $this->get()->fetchAll(\PDO::FETCH_OBJ);
        }

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
     * @return array
     */
    public function getCacheMode()
    {
        return $this->cacheMode;
    }

    /**
     * @param array $cacheMode
     * @return Cache
     */
    public function setCacheMode($cacheMode)
    {
        $this->cacheMode = $cacheMode;
        return $this;
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
