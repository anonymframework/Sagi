<?php
/**
 *  SAGI DATABASE ORM FILE
 *
 */

namespace Sagi\Database\Cache;


interface DriverInterface
{

    /**
     * boot cache driver
     *
     * @param array $configs
     * @return void
     */
    public function boot($configs);

    /**
     * @param string $name
     * @param mixed $value
     * @param int $expiration
     * @return mixed
     */
    public function set($name, $value, $expiration = 600);

    /**
     * @param strig $name
     * @return mixed
     */
    public function get($name);
}
