<?php
namespace Sagi\Database\Interfaces;

use Sagi\Database\Driver\Connection\Interfaces\DriverInterface;

/**
 * Interface ConnectionInterface
 * @package Sagi\Database\Interfaces
 */
interface ConnectionInterface
{




    /**
     * @return DriverInterface
     */
    public function executor();
}
