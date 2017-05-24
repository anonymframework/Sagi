<?php
namespace Sagi\Database\Driver\Expectation;

/**
 * Interface ExpectationInterface
 * @package Sagi\Database\Driver\Expectation
 */
interface ExpectationInterface
{

    /**
     *
     * @param mixed $driver
     * @return bool
     */
    public function expect($driver);
}