<?php
namespace Sagi\Database\Facade;
use Cable\Facade\Facade;

/**
 * Class Config
 * @package Sagi\Database
 */
class Config extends Facade
{

    /**
     * @return string
     */
    protected static function getFacadeClass()
    {
        return 'config';
    }
}