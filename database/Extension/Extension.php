<?php
/**
 * Created by PhpStorm.
 * User: My
 * Date: 05/25/2017
 * Time: 16:44
 */

namespace Sagi\Database\Extension;

abstract class Extension
{

    /**
     * @return mixed
     */
    abstract public function install();
}
