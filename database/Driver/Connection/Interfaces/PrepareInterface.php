<?php

namespace Sagi\Database\Driver\Connection\Interfaces;

/**
 * Interface PrepareInterface
 * @package Sagi\Database\Connection\Interfaces
 */
interface PrepareInterface
{

    /**
     * @param $query string
     * @return mixed
     */
    public function prepare($query);
}
