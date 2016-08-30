<?php
/**
 * Created by PhpStorm.
 * User: sagi
 * Date: 30.08.2016
 * Time: 17:12
 */

namespace Sagi\Database;

use Models\Auth;

/**
 * Class Authorization
 * @package Sagi\Database
 */
trait Authorization
{
    /**
     * @return mixed
     */
    public function getAuth()
    {
        return $this->hasOne(Auth::class, ['user_id', 'id']);
    }

    /**
     * @return mixed
     */
    public function checkAuthorizationTable()
    {
        return $this->tableExists('auth');
    }

    /**
     * @return bool
     */
    public function isSuperAdmin()
    {
        return $this->is('superadmin');
    }

    /**
     * @return bool
     */
    public function isAdmin()
    {
        return $this->is('admin');
    }

    /**
     * @param string $role
     * @return bool
     */
    public function is($role)
    {
        return $this->getAuth()->role === $role;
    }
}