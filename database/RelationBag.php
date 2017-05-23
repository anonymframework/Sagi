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
        'many' => [],
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
     * @param array $propeties
     */
    public static function addRelative($name, Model $model, $propeties, $type = 'one')
    {
        static::$relations[$type][$name] = [
            'instance' => $model,
            'propeties' => $propeties,
        ];
    }

    /**
     * @param string $name
     * @param Model $our
     * @param $type
     * @return RelationShip
     */
    public static function getRelation($name, Model $our, $type)
    {
        if ( ! static::isPreparedBefore($name)) {
            static::prepareRelation($name, $our, $type);
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
        return $name."::".$type;
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

    /**
     * @param string $name
     * @param Model $our
     * @param string $type
     */
    public static function prepareRelation($name, Model $our, $type)
    {
        $relation = static::$relations[$type][$name];

        /**
         * @var Model $model
         *
         *  Model instance
         */
        $model = $relation['instance'];

        $propeties = $relation['propeties'];

        $col = $propeties[0];

        $model->where($propeties[1], $our->$col);

        if ($type === 'one') {
            $model = $model->limit(1);
        }


        static::$preparedRelatives[static::getPreparedName($name, $type)] = $model;
    }
}
