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
     * @var QueryBuilder
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
        return call_user_func_array([$this->database, $name], $arguments);
    }

}