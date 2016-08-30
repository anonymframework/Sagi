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
    protected $usedModules = [];
    /**
     * @var string
     */
    public $primaryKey = 'id';

    /**
     * @var array
     */
    protected $timestamps = ['created_at', 'updated_at'];

    /**
     * @var string
     */
    protected $updateKey = 'id';


    /**
     * @var bool
     */
    protected $autoValidation = false;

    /**
     * @var array
     */
    protected $fields = [];

    /**
     * @var array
     */
    protected $expects = [];

    /**
     * Model constructor.
     */
    public function __construct()
    {

        parent::__construct();
        $table = static::getTableName();
        $this->setTable($table);

        $this->prepareRules();
        $this->usedModules = class_uses(static::className());
    }

    /**
     *
     */
    private function prepareRules()
    {
        if ($this->isValidationUsed()) {
            $rules = $this->rules();

            $this->setRules($rules);
        }
    }

    /**
     * @return mixed
     */
    public function isValidationUsed()
    {
        return $this->isModuleUsed('Sagi\Database\Validation');
    }

    /**
     * @return bool
     */
    public function isAuthorizationUsed()
    {
        return $this->isModuleUsed('Sagi\Database\Authorization');
    }

    /**
     * @param $module
     * @return bool
     */
    public function isModuleUsed($module)
    {
        return in_array($module, $this->usedModules);
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
        $instance = static::createNewInstance();

        return $instance->where($instance->primaryKey, $id);
    }

    /**
     * @return bool
     */
    public function hasPrimaryKey()
    {
        return !empty($this->primaryKey);
    }

    /**
     * @param int $id
     * @return mixed
     */
    public static function findOne($id)
    {
        $instance = static::createNewInstance();

        return $instance->where($instance->primaryKey, $id)->one();
    }

    /**
     * @param null $conditions
     * @return array
     */
    public static function findAll($conditions = null)
    {
        $instance = static::createNewInstance();

        if (is_array($conditions)) {
            foreach ($conditions as $key => $value) {
                $instance->where($key, $value);
            }
        }

        return $instance;
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
     * @return Model|false
     */
    public function save()
    {
        if ($this->autoValidation === true && $this->isValidationUsed()) {

            if (!$this->validate()) {
                return false;
            }
        }

        if (!empty($this->where) or !empty($this->orWhere)) {
            $updateKey = $this->updateKey;

            $this->where($updateKey, $this->attribute($updateKey));
            $this->setUpdatedAt()->update($this->getAttributes());
        } else {
            $this->setCreatedAt()->create($this->attributes);

            $created = static::createNewInstance()->setAttributes($this->attributes);

            if ($this->isValidationUsed()) {
                $this->createUserAuth($created->id, isset($this->role) ? $this->role : 'user');
            }

            return $created;

        }

        return $this;
    }

    /**
     * @return Model
     */
    private function setUpdatedAt()
    {
        if ($this->hasTimestamp($updated = 'created_at')) {
            $this->attributes[$updated] = $this->getCurrentTime();
        }

        return $this;
    }

    /**
     * @return Model
     */
    private function setCreatedAt()
    {
        if ($this->hasTimestamp($created = 'created_at')) {
            $this->attributes[$created] = $this->getCurrentTime();
        }

        return $this;
    }

    /**
     * @param string $field
     * @return bool
     */
    private function isField($field)
    {
        return in_array($field, $this->fields);
    }

    /**
     * @return int
     */
    public function getCurrentTime()
    {
        return time();
    }

    /**
     * @param $value
     * @return bool|mixed
     */
    private function hasTimestamp($value)
    {
        return (is_array($this->timestamps)) ? array_search($value, $this->timestamps) : false;
    }

    /**
     * @return string
     */
    public function __toString()
    {
        $fields = array_diff($this->fields, $this->expects);

        return json_encode($this->getAttributesByFields($fields));
    }

    /**
     * @param $fields
     * @return array
     */
    public function getAttributesByFields($fields)
    {
        $attrs = $this->getAttributes();

        return array_intersect_key($attrs,array_flip($fields));
    }

    /**
     * @return string|array
     */
    public
    static function getTableName()
    {
        return '';
    }

    /**
     * @param $name
     * @param $value
     */
    public
    function __set($name, $value)
    {
        if ($this->isField($name)) {
            $this->attributes[$name] = $value;
        } else {
            $this->$name = $value;
        }
    }
}