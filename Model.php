<?php

namespace Sagi\Database;

use PDO;

/**
 * Created by PhpStorm.
 * User: sagi
 * Date: 23.08.2016
 * Time: 17:23
 */
class Model extends QueryBuilder
{
    /**
     * @var array
     */
    protected $relations;


    /**
     * @var array
     */
    protected $timestamps;

    /**
     * @var array
     */
    private $cAttr = [];

    /**
     * Model constructor.
     * @param null $configs
     * @param null $table
     */
    public function __construct($configs = null, $table = null)
    {
        if (!$table) {
            $table = static::getTableName();
        }

        parent::__construct($configs, $table);

        if (isset($this->relations)) {
            $this->prepareRelations();
        }
    }

    /**
     * @return mixed
     */
    public function all()
    {
        return $this->get()->fetchAll(PDO::FETCH_CLASS, get_called_class());
    }

    /**
     * @return mixed
     */
    public function one()
    {
        $get = $this->get();

        return $get->fetchObject(get_called_class());
    }


    /**
     *  prepares relations
     */
    private function prepareRelations()
    {
        foreach ($this->relations as $relation) {
            $this->relation($relation[0], $relation[1]);
        }
    }

    /**
     * @return string
     */
    public static function className()
    {
        return get_called_class();
    }


    /**
     * @param int $id
     * @return QueryBuilder
     */
    public static function find($id)
    {
        return static::getInstance()->where('id', $id);
    }

    /**
     * @param int $id
     * @return mixed
     */
    public static function findOne($id)
    {
        return static::getInstance()->where('id', $id)->one();
    }

    /**
     * @param null $conditions
     * @return array
     */
    public static function findAll($conditions = null)
    {
        $instance = static::getInstance();

        if (is_array($conditions)) {
            foreach ($conditions as $key => $value) {
                $instance->where($key, $value);
            }
        }

        return $instance->all();
    }

    /**
     * @param string|Model $class
     * @param array $link
     * @return Model
     */
    public function hasMany($class, $link)
    {
        $table = $class::getTableName();

        if (is_array($table)) {
            $name = $table[1];
        } else {
            $name = $table;
        }

        if (!RelationBag::isPreparedBefore($name, 'many')) {
            $class = $class::createNewInstance();

            RelationBag::addRelative($name, $class, $link, 'many');
        }

        return RelationBag::getRelation($name, $this, 'many');
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
            $name = $table[1];
        } else {
            $name = $table;
        }

        if (!RelationBag::isPreparedBefore($name, 'one')) {
            $class = $class::createNewInstance();

            RelationBag::addRelative($name, $class, $link, 'one');
        }

        return RelationBag::getRelation($name, $this, 'one');
    }

    /**
     * @param $name
     * @return mixed
     */
    public function __get($name)
    {
        if (method_exists($this, $n = "get" . ucfirst($name))) {
            return call_user_func_array([$this, $n], []);
        }

        return parent::__get($name);
    }

    /**
     * @param array $datas
     * @return $this
     */
    public function save($datas = [])
    {
        $datas = array_merge($this->cAttr, $datas);

        if (isset($this->attributes[0])) {
            foreach ($this->attributes[0] as $key => $value) {
                $this->where($key, $value);
                $this->update($datas);
            }
        } else {
            $this->create($datas);

            return static::createNewInstance()->setAttributes($datas);
        }

        return $this;
    }


    /**
     * @return string|array
     */
    public static function getTableName()
    {
        return '';
    }

    /**
     * @param $name
     * @param $value
     */
    public function __set($name, $value)
    {
        $this->attributes[$name] = $value;
    }
}