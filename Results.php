<?php
namespace Sagi\Database;

/**
 * Created by PhpStorm.
 * User: sagi
 * Date: 16.08.2016
 * Time: 18:31
 */
class Results
{
    /**
     * @var string
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
        if ($relation = QueryBuilder::findRelative($name)) {

            if ($prepared = $this->findPreparedRelative($relation['name'])) {
                return $prepared;
            } else {
                return $this->prepareRelation($relation['name'], $relation['relation']['propeties']);
            }
        } else {
            return $this->attr[$name];
        }

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
     * @param array $columns
     * @return $this
     */
    public function relation($prop, array $columns = [])
    {
        $this->database->relation($prop, $columns);

        return $this;
    }

    /**
     * @param $name
     * @return bool|mixed
     */
    public function findPreparedRelative($name)
    {
        $subname = $this->table . '.' . $name;

        if (isset($this->preparedRelatives[$name])) {
            return $this->preparedRelatives[$name];
        } elseif (isset($this->preparedRelatives[$subname])) {
            return $this->preparedRelatives[$subname];
        }

        return false;
    }


    /**
     * @param $name
     * @param $arguments
     * @return mixed
     */
    public function __call($name, $arguments)
    {
        return call_user_func_array([$this->database, $name], $arguments);
    }

    public function prepareRelation($name, $relation)
    {
        $targetTable = $relation['table'];
        $targetColumn = $relation[0];
        $ourColumn = $relation[1];

        $type = isset($relation[2]) ? $relation[2] : 'one';

        $query = $this->database->newInstance($this->table)->setTable($targetTable);

        $relation = $query->where($targetColumn, $this->{$ourColumn});

        if ($type == 'one') {
            $relation = $relation->limit(1);
        }

        $this->preparedRelatives[$name] = $relation;

        return $relation;
    }
}