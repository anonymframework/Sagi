<?php

namespace Sagi\Database\Driver\Connection\Interfaces;
interface DriverInterface{

    /**
     * @param string $name
     * @param array $arguments
     * @return mixed
     */
    public function __call($name, array $arguments);

}
