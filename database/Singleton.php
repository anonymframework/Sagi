<?php
/**
 *  SAGI DATABASE ORM FILE
 *
 */

namespace Sagi\Database;


class Singleton
{
    /**
     * @var array
     */
    protected static $instance;

    /**
     * @param $class
     * @param array $parameters
     * @return mixed
     */
    public static function load($class, $parameters = [])
    {
        if ( ! isset(static::$instance[$class]) || ! static::$instance[$class] instanceof $class) {
            $reflectionClass = new \ReflectionClass($class);

            static::$instance[$class] = $reflectionClass->newInstanceArgs($parameters);
        }

        return static::$instance[$class];
    }
}
