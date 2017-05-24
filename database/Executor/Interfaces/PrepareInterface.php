<?php

namespace Sagi\Database\Executor\Interfaces;

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
