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
    protected static  $instance;

    /**
     * @param $class
     * @param array $parameters
     * @return mixed
     */
    public static function load($class, $parameters = [])
    {
        if (!isset(static::$instance[$class]) || static::$instance[$class] instanceof $class) {
            $class = new \ReflectionClass($class);

            static::$instance[$class] = $class->newInstance($parameters);
        }

        return static::$instance[$class];
    }
}