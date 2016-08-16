<?php

/**
 * Created by PhpStorm.
 * User: sagi
 * Date: 16.08.2016
 * Time: 18:31
 */
class Results
{
    /**
     * @var static
     */
    public $table;

    /**
     * @var array
     */
    public $attr;

    /**
     * @var Database
     */
    public $database;

    /**
     * @var array
     */
    public $preparedRelatives;

    /**
     * Results constructor.
     * @param $table
     * @param Database $database
     */
    public function __construct($table, $database)
    {
        $this->table = $table;
        $this->database = $database;
    }

    /**
     * @param $name
     * @return mixed
     */
    public function __get($name)
    {
        if (isset(RelationBag::$relations[$name])) {
            return call_user_func_array([$this, $name], []);
        }

        return $this->attr[$name];
    }

    /**
     * @param $name
     * @param $value
     */
    public function __set($name, $value)
    {
        $this->attr[$name] = $value;
    }

    /**
     * @param $name
     * @param $arguments
     * @return mixed
     */
    public function __call($name, $arguments)
    {
        if (isset(RelationBag::$relations[$name])) {
            return $this->prepareRelation($name);
        } else {
            return call_user_func_array([$this->database, $name], $arguments);
        }
    }

    public function prepareRelation($name)
    {
        if (!$this->preparedRelatives[$name]) {
            $relation = RelationBag::$relations[$name];

            $targetTable = $name;
            $targetColumn = $relation[0];
            $ourColumn = $relation[1];
            $type = isset($relation[2]) ? $relation[2] : 'one';

            $query = $this->database->newInstance($this->table)->setTable($targetTable);

            $relation = $query->where($targetColumn, $this->{$ourColumn});

            if ($type == 'one') {
                $relation = $relation->limit(1)->fetch();
            } else {
                $relation = $relation->fetchAll();
            }

            $this->preparedRelatives[$name] = $relation;

        } else {
            $relation = $this->preparedRelatives[$name];
        }
        return $relation;
    }
}