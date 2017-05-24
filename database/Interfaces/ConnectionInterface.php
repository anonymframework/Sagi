<?php
namespace Sagi\Database\Interfaces;

use Sagi\Database\Executor\Interfaces\DriverInterface;

/**
 * Interface ConnectionInterface
 * @package Sagi\Database\Interfaces
 */
interface ConnectionInterface
{

    /**
     * @param string $name
     * @param array $arguments
     * @return mixed
     */
    public function __call($name, array $arguments);


    /**
     * @return DriverInterface
     */
    public function executor();
}
