<?php

namespace Sagi\Database\Extension\Interfaces;


use Sagi\Database\Driver\DriverManager;

interface ExtensionInterface
{

    /**
     * @param DriverManager $manager
     * @return mixed
     */
    public function install(DriverManager $manager);

}
