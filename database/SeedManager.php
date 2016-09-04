<?php

namespace Sagi\Database;


use Symfony\Component\Console\Application;

abstract class SeedManager
{

    /**
     * @var Application
     */
    protected $application;

    /**
     * @return Application
     */
    public function getApplication()
    {
        return $this->application;
    }

    /**
     * @param Application $application
     * @return SeedManager
     */
    public function setApplication($application)
    {
        $this->application = $application;
        return $this;
    }

}
