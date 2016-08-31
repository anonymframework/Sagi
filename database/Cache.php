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
    private $memcache;

    /**
     *  makes memcache connection
     */
    protected function makeCacheConnection()
    {
        $configs = ConfigManager::get('cache');

        $this->memcache = new Memcached();

        $this->memcache->addServer($configs['host'], $configs['port']);
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

        return md5(serialize($this->getTable() . $merged));
    }


    /**
     * @param $key
     * @return mixed
     */
    protected function getCache($key)
    {
        return $this->memcache->get($key);
    }
}
