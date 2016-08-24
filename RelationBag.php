<?php
namespace Sagi\Database;

/**
 * Created by PhpStorm.
 * User: sagi
 * Date: 16.08.2016
 * Time: 20:46
 */
class RelationBag
{
    /**
     * @var array
     */
    public static $relations = [
        'one' => [],
        'many' => []
    ];

    /**
     * @var array
     */
    private static $preparedRelatives;

    /**
     * @return array
     */
    public static function getRelations()
    {
        return self::$relations;
    }

    /**
     * @param array $relations
     */
    public static function setRelations($relations)
    {
        self::$relations = $relations;
    }

    /**
     * @param string $name
     * @param Model $model
     */
    public static function addRelative($name, Model $model, $type = 'one')
    {
        static::$relations[$type][$name] = $model;
    }

    /**
     * @param $name
     * @param $type
     * @return Model
     */
    public static function getRelation($name, $type)
    {
        if (!static::isPreparedBefore($name)) {
            static::prepareRelation($name, $type);
        }

        return static::$preparedRelatives[static::getPreparedName($name, $type)];

    }

    /**
     * @param $name
     * @param $type
     * @return string
     */
    public static function getPreparedName($name, $type)
    {
        return $name . "::" . $type;
    }

    /**
     * @param $name
     * @param string $type
     * @return bool
     */
    public static function isPreparedBefore($name, $type = 'one')
    {
        return isset(static::$preparedRelatives[static::getPreparedName($name, $type)]);
    }

    public static function prepareRelation()
    {

    }

}