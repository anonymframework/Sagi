<?php

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
     * @return QueryBuilder
     */
    public static function findOne($id)
    {
        return static::getInstance()->where(['id' => $id])->one();
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

        $link[] = 'many';

        if (!static::findRelative($name)) {
            $this->relation($table, $link);
        }
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

        if (!static::findRelative($name)) {
            $this->relation($table, $link);
        }
    }

    /**
     * @param $name
     * @return mixed
     */
    public function __get($name)
    {
        if (method_exists($this, $n = "get" . ucfirst($name))) {
            call_user_func_array([$this, $n], []);
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

        if (isset($this->attr[0])) {
            foreach ($this->attr[0] as $key => $value) {
                $this->where($key, $value);
                $this->update($datas);
            }
        } else {
            $this->create($datas);
            $this->attr[0] = $datas;
        }

        return $this;
    }

    /**
     * @return QueryBuilder
     */
    public static function getInstance()
    {
        if (!static::$instance) {
            static::$instance = new static();
        }

        return static::$instance;
    }

    /**
     * @return string|array
     */
    public static function getTableName()
    {
        return '';
    }

    /**
     * @param string $name
     * @param mixed $value
     */
    public function __set($name, $value)
    {
        $this->cAttr[$name] = $value;
    }

}