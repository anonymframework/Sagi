<?php

namespace Sagi\Database;

/**
 * Class Identitiy
 * @package Sagi\Database
 */
class Identitiy
{

    protected static function findLogin()
    {
        return SessionManager::get('identity') ?: CookieManager::get('identity');
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
    public static function user()
    {

        if (static::isLogined()) {

            $user = static::findLogin();

            /**
             * @var Model $user
             */

            return $user->where($user->primaryKey, $user->{$user->primaryKey});
        }

        return false;
    }

    /**
     * @param Model $model
     * @param $remember
     */
    public static function login(Model $model, $remember = false)
    {
        if ($remember === true) {
            CookieManager::set('identity', $model, 7200);
        } else {
            SessionManager::set('identity', $model);
        }

    }

    public static function logout()
    {
        if (SessionManager::has('identity')) {
            SessionManager::delete('identity');
        } elseif (CookieManager::has('identity')) {
            CookieManager::delete('identity');
        }
    }
}
