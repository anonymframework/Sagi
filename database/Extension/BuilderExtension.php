<?php

namespace Sagi\Database\Extension;

use Sagi\Database\Driver\DriverManager;

/**
 * Class BuilderExtension
 * @package Sagi\Database\Extension
 */
class BuilderExtension
{
    /**
     * @var DriverManager
     */
    private $manager;

    /**
     * BuilderExtension constructor.
     * @param DriverManager $manager
     */
    public function __construct(DriverManager $manager)
    {
        $this->manager = $manager;
    }

    /**
     * @param string $name
     * @return \Sagi\Database\Driver\Driver
     */
    public function connector($name)
    {
        return $this->manager->driver('connector')->name($name);
    }

    /**
     * @param string $name
     * @return \Sagi\Database\Driver\Driver
     */
    public function create($name)
    {
        return $this->manager->driver('migration')->name($name);
    }
}

