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
        $link[] = 'many';

        return $this->hasOne($class, $link);
    }

    /**
     * @param string|Model $class
     * @param array $link
     * @return Model
     */
    public function hasOne($class, $link)
    {


        $table = $class::getTableName();

        if (is_array($table)) {
            $name = $table[0];
        } elseif (is_string($table)) {
            $name = $table;
        }


        if (!parent::findRelative($name)) {
            $this->relation($table, $link);
        }

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
        return '';
    }


    /**
     * @return bool
     */
    public static function getAliasName()
    {
        return '';
    }
}