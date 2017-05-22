<?php
/**
 * Created by PhpStorm.
 * User: My
 * Date: 05/07/2017
 * Time: 15:32
 */

namespace Sagi\Database;


class ModelManager
{

    private static $namespace = 'Models';

    /**
     * @return string
     */
    public static function getNamespace()
    {
        return self::$namespace;
    }

    /**
     * @param string $namespace
     */
    public static function setNamespace($namespace)
    {
        self::$namespace = $namespace;
    }

    /**
     * @param $model
     * @return string
     */
    public static function prepareModelClass($model)
    {
        return static::getNamespace() . '\\' . mb_convert_case($model, MB_CASE_TITLE);
    }

    public static function prepareModelPath($model)
    {

    }


    /**
     * @param string $model
     * @return object
     */
    public static function createModelInstance($model)
    {
        $model = static::prepareModelClass($model);

        $reflection = new \ReflectionClass($model);
        return $reflection->newInstance();
    }

    public static function checkModelIsExists($model){
        return class_exists(static::prepareModelClass($model), true);
    }
    /**
     * @param string $model
     * @return Model|QueryBuilder
     */
    public static function createModelInstanceIfExists($model)
    {
        if (static::checkModelIsExists($model)) {
            return self::createModelInstance($model);
        }

        return new QueryBuilder();
    }

}
