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
    private function makeCacheConnection()
    {
        $configs = ConfigManager::get('cache');

        $this->memcache = new Memcached();

        $this->memcache->addServer($configs['host'], $configs['port']);
    }
}
