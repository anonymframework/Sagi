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

    /**
     * @param Model $model
     * @param $remember
     */
    public static function login(Model $model, $remember){
        if ($remember === true) {
            CookieManager::set('identity', $model, 7200);
        } else {
            SesssionManager::set('identity', $model);
        }

    }

}
