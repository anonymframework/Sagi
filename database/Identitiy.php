<?php

namespace Sagi\Database;

/**
 * Class Identitiy
 * @package Sagi\Database
 */
class Identitiy
{

    protected static function findLogin(){
        return SesssionManager::get('identity') ?: CookieManager::get('identity');
    }

    /**
     * @return mixed
     */
    public static function isLogined()
    {
         return static::findLogin() instanceof Model ? true : false;
    }

    /**
     * @return mixed
     */
    public static function user(){
        return static::findLogin();
    }

}
