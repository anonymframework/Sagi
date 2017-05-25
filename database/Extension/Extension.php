<?php
/**
 * Created by PhpStorm.
 * User: My
 * Date: 05/25/2017
 * Time: 16:44
 */

namespace Sagi\Database\Extension;

use Sagi\Database\Driver\DriverManager;

abstract class Extension
{

    /**
     * @param DriverManager $manager
     * @return mixed
     */
    abstract public function install(DriverManager $manager);
}
