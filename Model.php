<?php

/**
 * Created by PhpStorm.
 * User: sagi
 * Date: 23.08.2016
 * Time: 17:23
 */
class Model extends QueryBuilder
{

    public function __construct($configs = null, $table = null)
    {
        parent::__construct($configs, $table);
    }

    /**
     * @return string
     */
    public static function className()
    {
        return get_called_class();
    }


    /**
     * @param string|Model $class
     * @param array $link
     * @return Model
     */
    public function hasMany($class, $link)
    {
        if ($class::hasAliasName()) {
            $class = [$class::getAliasName(), $class::getTableName()];

            $name = $class::getAliasName();
        } else {
            $name = $class::getTableName();
        }

        $link[] = 'many';

        $this->relation($class, $link);

        return $this->$name;
    }

    /**
     * @param string|Model $class
     * @param array $link
     * @return Model
     */
    public function hasOne($class, $link)
    {
        if ($class::hasAliasName()) {
            $class = [$class::getAliasName(), $class::getTableName()];

            $name = $class::getAliasName();
        } else {
            $name = $class::getTableName();
        }
        $this->relation($class, $link);

        return $this->$name;
    }

    /**
     * @param string $name
     * @return mixed
     */
    public function __get($name)
    {
        if (method_exists($this, $name = "get" . ucfirst($name))) {
            return call_user_func_array([$this, $name], []);
        } else {
            parent::__get($name);
        }
    }

    public static function getTableName()
    {
        return static::$instance->getTable();
    }

    /**
     * @return bool
     */
    public static function hasAliasName()
    {
        return isset(static::$instance->alias);
    }

    /**
     * @return bool
     */
    public static function getAliasName()
    {
        return static::$instance->alias;
    }
}