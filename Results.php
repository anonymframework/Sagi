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
     * @var array
     */
    public $preparedRelatives;

    /**
     * Results constructor.
     * @param $table
     */
    public function __construct($table)
    {
        $this->table = $table;
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
}