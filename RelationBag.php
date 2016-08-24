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
    public static $relations;

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

    public static function prepareRelatives()
    {

    }

}