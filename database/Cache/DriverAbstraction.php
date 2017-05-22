<?php
/**
 * Created by PhpStorm.
 * User: My
 * Date: 04/20/2017
 * Time: 18:15
 */

namespace Sagi\Database\Cache;

/**
 * Class DriverAbstraction
 * @package Sagi\Database\Cache
 */
class DriverAbstraction
{
    /**
     * @return mixed
     */
    public function getDriver(){
        return static::$driver;
    }
}