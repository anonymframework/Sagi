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
     * @var mixed
     */
    protected $policy;

    /**
     * @var array
     */
    private $protected = [];

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

        if ($policy = ConfigManager::get('policies.' . get_called_class())) {
            $this->policy(new $policy);
        }
    }

    /**
     *
     */
    private function prepareRules()
    {
        if ($this->isValidationUsed()) {

            if (method_exists($this, 'rules')) {
                $this->setRules($this->rules());
            }


            if (method_exists($this, 'filters')) {
                $this->setFilters($this->filters());
            }

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
     * @return bool
     */
    public function isCacheUsed()
    {
        return $this->isModuleUsed('Sagi\Database\Cache');
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
     * @param PolicyInterface $policy
     * @return $this
     */
    public function policy(PolicyInterface $policy)
    {
        $this->policy = $policy;

        return $this;
    }

    /**
     * @param string $method
     * @param array $args
     * @return bool
     */
    public function can($method = 'get', array $args = [])
    {
        if (!$this->policy instanceof PolicyInterface) {
            return true;
        }

        array_unshift($args, $this);


        return call_user_func_array([$this->policy, $method], $args) !== false ? true : false;
    }

    /**
     * @return mixed
     */
    public function all()
    {
        $class = get_called_class();

        if ($this->isCacheUsed()) {
            $this->makeCacheConnection();

            if ($result = $this->getCache($key = $this->prepareCacheKey())) {
                $result = $this->setAttributes(unserialize($result));
            } else {
                $this->setCache(
                    $key, serialize(
                    $get = $this->get()->fetchAll(PDO::FETCH_CLASS, $class)
                ));

                $result = $this->setAttributes($get);
            }


            return $result;
        } else {

            return $this->get()->fetchAll(PDO::FETCH_CLASS, get_called_class());
        }
    }

    /**
     * @return mixed
     */
    public function one()
    {
        $class = get_called_class();

        if ($this->isCacheUsed()) {
            $this->makeCacheConnection();

            if ($result = $this->getCache($key = $this->prepareCacheKey())) {
                $result = $this->setAttributes(unserialize($result));
            } else {
                $this->setCache(
                    $key,
                    serialize($get = $this->get()->fetch(PDO::FETCH_ASSOC))
                );

                $result = $this->setAttributes($get);
            }


            return $result;
        } else {
            $get = $this->get();

            return $get->fetchObject($class);
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
     * @return Model
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
     * @param $a
     * @param null $b
     * @param null $c
     * @param bool $prepare
     * @return $this
     */
    public function where($a, $b = null, $c = null, $prepare = false)
    {
        $name = is_array($a) ? $a[0] : $a;

        $value = is_array($a) ? $a[2] : $b;

        if (

        $this->can(
            $name . 'Where',
            array(
                $value
            ))
        ) {
            parent::where($a, $b, $c, $prepare);

            return $this;
        } else {
            $this->throwPolicyException('where');
        }
    }

    /**
     * @param $a
     * @param null $b
     * @param null $c
     * @param bool $prepare
     * @return $this
     */
    public function orWhere($a, $b = null, $c = null, $prepare = false)
    {
        if ($this->can('orWhere')) {
            parent::orWhere($a, $b, $c, $prepare);

            return $this;
        } else {
            $this->throwPolicyException('where');
        }

    }

    /**
     * @param null $conditions
     * @return $this
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

        $attributes = $this->getAttributes();

        if (!empty($this->getWhere()) or !empty($this->getOrWhere())) {

            if ($this->can('update')) {
                $this->setUpdatedAt()->update($attributes);


            } else {
                $this->throwPolicyException('create');
            }

        } else {
            if ($this->can('create')) {
                $this->setCreatedAt()->create($attributes);

                if (!empty($this->primaryKey)) {
                    $created = static::findOne($this->getPdo()->lastInsertId($this->primaryKey));
                }

                if ($this - $this->isAuthorizationUsed()) {
                    $this->createUserAuth();
                }

                return $created;
            } else {
                $this->throwPolicyException('create');
            }
        }


        return $this;
    }

    /**
     * @param $template
     * @return string
     */
    public function display($template)
    {
        $view = new View($template);

        $view->with('this', $this);

        return $view->render();
    }

    /**
     * @return Model
     */
    public function delete()
    {
        if ($this->isAuthorizationUsed()) {
            $this->deleteAuthRow();
        }

        return parent::delete();
    }

    /**
     * @param $method
     * @throws \Exception
     */
    private function throwPolicyException($method)
    {
        throw new \Exception(sprintf('You cannot use %s method', $method));
    }

    /**
     * @param $datas
     * @return QueryBuilder
     */
    public static function set($datas)
    {
        return static::createNewInstance()->setAttributes($datas);
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
        return in_array($field, $this->fields) && !$this->isProtected($field);
    }

    /**
     * @param $name
     * @return bool
     */
    private function isProtected($name)
    {
        return isset($this->protected[$name]);
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
     * @return string
     */
    public function __sleep()
    {
        $this->pdo = null;

        return array('attributes');
    }

    /**
     *
     */
    public function __wakeup()
    {
        $this->pdo = Connector::getConnection();
    }

    /**
     * @param $fields
     * @return array
     */
    public function getAttributesByFields($fields)
    {
        $attrs = $this->getAttributes();

        return array_intersect_key($attrs, array_flip($fields));
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