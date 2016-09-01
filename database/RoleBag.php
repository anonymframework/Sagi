<?php

namespace Sagi\Database;


class RoleBag
{

    /**
     * @var array
     */
    public static $roles = [
        'superadmin' => ['admin', 'user', 'editor'],
        'admin' => ['user', 'premium'],
        'user' => ['premium'],
        'premium' => []
    ];

    /**
     * @param $role
     * @param $minRole
     * @return bool
     */
    public static function hasPermission($role, $minRole)
    {
        return $role === $minRole ? true : isset(static::$roles[$role][$minRole]);
    }
}