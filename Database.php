<?php

/**
 * Created by PhpStorm.
 * User: sagi
 * Date: 16.08.2016
 * Time: 17:50
 */
class Database implements Iterator
{

    /**
     * @var QueryBuilder
     */
    public $builder;

    /**
     * @var string
     */
    public $table;

    /**
     * @var array
     */
    public $attr;



    /**
     * Database constructor.
     * @param array $configs
     * @param string $table
     */
    public function __construct(array $configs = [], $table = '')
    {
        $this->builder = new QueryBuilder($configs, $table);
        $this->table = $table;
    }


    /**
     * @return $this
     */
    public function one()
    {
        $attrs = $this->builder->fetch();

        $this->attr[0] = $attrs;
        return $this;
    }

    /**
     * @return $this
     */
    public function fetchAll()
    {
        $this->attr = $this->builder->fetchAll();

        return $this;
    }

    /**
     * @return array
     */
    public function all()
    {
        if (empty($this->attr)) {
            $this->fetchAll();
        }

        return $this->attr;
    }

    /**
     * @return mixed
     */
    public function first()
    {
        if (isset($this->attr[0]) === false) {
            $this->one();
        }

        return $this->attr[0];
    }

    /**
     * @return array
     */
    public function attrs()
    {
        return $this->attr;
    }

    /**
     * @param $name
     * @return mixed
     */
    public function __get($name)
    {
        return $this->first()->$name;
    }

    public function __set($name, $value)
    {
        $this->first()->$name = $value;
    }


    /**
     * @param $name
     * @param $arguments
     * @return QueryBuilder|mixed
     */
    public function __call($name, $arguments)
    {

        call_user_func_array([$this->builder, $name], $arguments);

        return $this;

    }

    public function rewind()
    {
        reset($this->attr);
    }

    public function current()
    {
        $var = current($this->attr);
        return $var;
    }

    public function key()
    {
        $var = key($this->attr);
        echo "key: $var\n";
        return $var;
    }

    public function next()
    {
        $var = next($this->attr);
        return $var;
    }

    public function valid()
    {
        $key = key($this->attr);
        $var = ($key !== NULL && $key !== FALSE);
        return $var;
    }

    /**
     * @param $name
     * @param $arguments
     * @return mixed
     */
    public static function __callStatic($name, $arguments)
    {
        return call_user_func_array([static::$instance, $name], $arguments);
    }
}
