<?php

namespace Sagi\Database;

use Anonym\Components\Event\EventDispatcher;
use PDO;
use Sagi\Database\Mapping\Entity;

/**
 * Created by PhpStorm.
 * User: sagi
 * Date: 23.08.2016
 * Time: 17:23
 */
class Model extends QueryBuilder implements \Iterator, \ArrayAccess
{

    const CREATED_AT = 'created_at';
    const UPDATED_AT = 'updated_at';

    const FULL_CACHE = 1;
    const PRIMARY_CACHE = 2;
    /**
     * @var EventDispatcher
     */
    protected $eventManager;
    /**
     * @var array
     */
    protected $usedModules = [];
    /**
     * @var string
     */
    protected $primaryKey = 'id';

    /**
     * @var array
     */
    protected $timestamps = ['created_at', 'updated_at'];


    /**
     * @var array
     */
    protected $fields = [];


    /**
     * @var mixed
     */
    protected $policy;

    /**
     * @var array
     */
    protected $protected = [];

    /**
     * @var array
     */
    protected $json = [];

    /**
     * @var array
     */
    protected $array = [];

    /**
     * @var array
     */
    protected $guarded = [];

    /**
     * @var bool
     */
    protected $totallyGuarded = false;
    /**
     * @var mixed
     */
    public $attributes;

    /**
     * @var string
     */
    protected $alias;

    /**
     * @var Loggable
     */
    protected $logging;

    /**
     * @var array
     */
    protected $attach;

    /**
     * @var array
     *
     */
    protected $hide = [];

    /**
     * @var bool
     */
    protected $subscribedBefore = false;

    /**
     * Model constructor.
     * @param array $attributes
     */
    public function __construct(array $attributes = [])
    {

        parent::__construct();
        $this->usedModules = $traits = class_uses(static::className());

        $this->eventManager = new EventDispatcher();
        $this->addSubscribes();

        $this->bootTraits($traits);

        if ($policy = ConfigManager::get('policies.' . get_called_class())) {
            if (is_string($policy)) {
                $this->policy(new $policy);
            } else {
                throw new \Exception('Policy names must be an string');
            }
        }

        if (!empty($this->fields)) {
            $this->select($this->fields);
        }

        if (!empty($attributes)) {
            $this->fill($attributes);
        }


        $this->bootLogging();

    }

    /**
     *
     */
    private function addSubscribes()
    {
        if (is_null($this->eventManager)) {
            $this->eventManager = new EventDispatcher();
        }

        $this->eventManager->listen('before_create', function (Model $model) {
            if ($model->can('create') === false) {
                $model->throwPolicyException('create');
            }


        });

        $this->eventManager->listen('before_update', function (Model $model) {
            if (!$model->can('create')) {
                $model->throwPolicyException('update');
            }
        });

        $this->subscribedBefore = true;
    }


    private function bootLogging()
    {
        $logging = ConfigManager::get('logging', ['open' => false]);

        if ($logging['open'] === true) {
            $this->logging = Singleton::load('Sagi\Database\Loggable');
        }
    }

    public function arrayAll()
    {
        return parent::all();
    }

    /**
     * @param array $attributes
     * @return $this
     * @throws \Exception
     */
    public function fill($attributes)
    {
        foreach ($attributes as $key => $value) {
            if ($this->isFillable($key)) {
                $this->setAttribute($key, $value);
            } else {
                throw new \Exception(sprintf('You cannot set any value on %s attributes', $key));
            }
        }


        return $this;
    }

    /**
     * @param $key
     * @return bool
     */
    public function isFillable($key)
    {
        return !isset($this->guarded[$key]) or !$this->totallyGuarded;
    }

    /**
     * @param $traits
     */
    private function bootTraits($traits)
    {
        foreach ($traits as $trait) {
            if (method_exists($this, $method = 'boot' . $this->classBaseName($trait))) {
                call_user_func_array([$this, $method], []);
            }
        }
    }

    /**
     * @param string $class
     * @return string
     */
    private function classBaseName($class)
    {
        $class = is_object($class) ? get_class($class) : $class;
        return basename(str_replace('\\', '/', $class));
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
     * @param callable $callable
     * @return $this
     */
    public function beforeAttach(callable $callable)
    {
        $this->eventManager->listen('before_attach', $callable);

        return $this;
    }


    public function attach(Model $model, $alias = false)
    {
        if (!$model->hasPrimaryKey()) {
            throw new \Exception('Your model class have a primary key to use attach method');
        }

        $table = $this->primaryKey;

        if ($alias == true) {
            $table = $alias;
        }

        $this->attach[Model::className()] = [
            'attach_by' => [$model->primaryKey, $table],
            'attach_with' => $model
        ];

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
        $this->eventManager = null;


        $class = get_called_class();

        if ($this->isCacheUsed()) {
            return $this->cacheAll();
        } else {
            return static::set($this->get()->fetchAll(PDO::FETCH_CLASS, $class));
        }
    }

    /**
     * @return Model
     */
    public function one()
    {

        $this->eventManager = null;

        if ($this->isCacheUsed()) {
            $this->cacheOne();
        } else {
            $get = $this->get();

            $this->setAttributes($get->fetch(PDO::FETCH_ASSOC));
        }

        return $this;
    }


    /**
     * @return string
     */
    public static function className()
    {
        return get_called_class();
    }


    /**
     * @param array|int $conditions
     * @return Model
     */
    public static function find($conditions = [])
    {
        $instance = static::createNewInstance();

        if (is_array($conditions) && !empty($conditions)) {
            foreach ($conditions as $item) {
                $instance->where($item[0], $item[1], isset($item[2]) ? $item[2] : null);
            }

        } elseif (is_string($conditions) || is_integer($conditions)) {
            $primaryKey = is_array($instance->primaryKey) ?
                $instance->primaryKey[0] :
                $instance->primaryKey;

            $instance->where($primaryKey, $conditions);
        } elseif (func_num_args() > 1 && is_array($instance->primaryKey)) {

            foreach ($instance->primaryKey as $index => $primaryKey) {
                $instance->where($primaryKey, func_get_arg($index));
            }
        }

        return $instance;

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
     * @return Model
     */
    public static function findOne($id)
    {
        return static::find($id)->one();
    }

    /**
     * @param $a
     * @param null $b
     * @param null $c
     * @param string $type
     * @param bool $clean
     * @return $this
     */
    public function where($a, $b = null, $c = null, $type = 'AND', $clean = true)
    {
        $name = is_array($a) ? $a[0] : $a;

        $value = is_array($a) ? $a[2] : $c;

        if (

        $this->can(
            $name . 'Where',
            array(
                $value
            ))
        ) {
            parent::where($a, $b, $c, $type, $clean);

            return $this;
        } else {
            $this->throwPolicyException('where');
        }
    }

    /**
     * @param $a
     * @param null $b
     * @param null $c
     * @param bool $clean
     * @return $this
     */
    public function orWhere($a, $b = null, $c = null, $clean = true)
    {
        if ($this->can('orWhere')) {
            parent::orWhere($a, $b, $c, $clean);

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
        return static::find($conditions)->all();
    }

    /**
     * @param string|Model $class
     * @param array $link
     * @param string $alias
     * @return RelationShip
     */
    public function hasMany($class, $link, $alias = null)
    {
        $class = $class::createNewInstance();

        if ($alias !== null) {
            $name = $alias;
        } else {
            $name = $class->getTable();
        }


        $append = '#' . $link[1] . ':' . $this->__get($link[1]);

        $name .= $append;

        if (!RelationBag::isPreparedBefore($name, 'many')) {

            RelationBag::addRelative($name, $class, $link, 'many');
        }

        return RelationBag::getRelation($name, $this, 'many');
    }

    /**
     * @param string|Model $class
     * @param array $link
     * @param array $alias
     * @return RelationShip
     */
    public function hasOne($class, $link, $alias = null)
    {
        $class = $class::createNewInstance();

        if ($alias !== null) {
            $name = $alias;
        } else {
            $name = $class->getTable();
        }

        $append = '#' . $link[1] . ':' . $this->__get($link[1]);

        $name .= $append;

        if (!RelationBag::isPreparedBefore($name, 'one')) {

            RelationBag::addRelative($name, $class, $link, 'one');
        }

        return RelationBag::getRelation($name, $this, 'one');
    }

    /**
     * @param string $json
     * @return bool
     */
    public function isJson($json)
    {
        return in_array($json, $this->json);
    }


    /**
     * @param $name
     * @return bool
     */
    public function isArray($name)
    {
        return in_array($name, $this->array);
    }


    /**
     * @return Model|false
     */
    public function save()
    {

        $this->eventManager->hasListiner('before_save') ?
            $this->eventManager->fire('before_save', [$this]) : null;

        if (!empty($this->getWhere()) or !empty($this->getOrWhere())) {

            $return = $this->update() ? $this : false;

            return $return;
        } else {
            $return = $this->create();
        }

        $this->eventManager->hasListiner('after_save')
            ? $this->eventManager->fire('after_save', [$return]) : null;

        return $return;
    }

    /**
     * @param Entity $data
     * @return Model|bool
     */
    public function create($data = null)
    {
        $this->eventManager->hasListiner('before_create')
            ? $this->eventManager->fire('before_create', [$this, $data]) : null;

        if (empty($data)) {
            $data = $this->getAttributes();
        }


        if (!$data instanceof Entity) {
            $entity = new Entity();

            $entity->datas = $data;

            if (isset($data[0]) && is_array($data[0])) {
                $entity->multipile = true;
            }
        } else {
            $entity = $data;
        }


        if ($created = parent::create($entity)) {
            if (!empty($this->primaryKey) && !is_array($this->primaryKey) && $entity->multipile === false) {
                $created = static::findOne($this->getPdo()->lastInsertId());
            } elseif (empty($this->primaryKey) || is_array($this->primaryKey)) {
                $created = static::find();

                foreach ($this->primaryKey as $key) {
                    $created->where($key, $data[$key]);
                }

                return $created->one();
            }

            $return = $created;
        } else {
            $return = false;
        }


        $this->eventManager->hasListiner('after_create')
            ? $this->eventManager->fire('after_create', [$return, $this]) : null;

        return $return;
    }


    /**
     * @param array $datas
     * @return PDOStatement
     */
    public function update($datas = [])
    {
        $this->eventManager->hasListiner('before_update')
            ? $this->eventManager->fire('before_update', [$this, $datas]) : null;
        if (empty($datas)) {
            $datas = $this->getAttributes();
        }

        $this->setUpdatedAt();

        $return = parent::update($datas);

        $this->eventManager->hasListiner('after_update')
            ? $this->eventManager->fire('after_update', [$return, $this]) : null;

        return $return;
    }

    /**
     * @return Model
     */
    public function delete()
    {
        $this->eventManager->hasListiner('before_delete')
            ? $this->eventManager->fire('before_delete', [$this]) : null;

        $return = parent::delete();

        $this->eventManager->hasListiner('after_delete')
            ? $this->eventManager->fire('after_delete', [$return, $this]) : null;

        $this->eventManager = null;
        return $this;
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
     * @return Model
     */
    public static function set($datas)
    {
        return new static($datas);
    }


    /**
     * @return Model
     */
    private function setUpdatedAt()
    {

        if ($this->hasTimestamp($updated = static::UPDATED_AT)) {
            $this->attributes[$updated] = date($this->timestampFormat(), $this->getCurrentTime());
        }

        return $this;
    }

    /**
     * @return string
     */
    public function timestampFormat()
    {
        return 'Y-m-d H:i:s';
    }


    /**
     * @param $name
     * @return bool
     */
    public function isProtected($name)
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
    public function hasTimestamp($value)
    {
        return (is_array($this->timestamps)) ? in_array($value, $this->timestamps) : false;
    }

    public function json()
    {
        return json_encode($this->getAttributesWithoutHide());
    }

    /**
     * @return array
     */
    private function getAttributesWithoutHide()
    {
        $attributes = $this->getAttributes();

        if (!empty($this->hide)) {
            foreach ($this->hide as $key) {

                if (isset($attributes[$key])) {
                    unset($attributes[$key]);
                }
            }
        }

        return $attributes;
    }

    /**
     * @return string
     */
    public function __toString()
    {
        return serialize($this->getAt);
    }

    /**
     * @return array
     */
    public function __sleep()
    {
        $arr = parent::__sleep();

        return array_merge($arr,
            [
                'table',
                'attributes',
                'eventManager',
                'primaryKey',
                'usedModules',
                'policy',
                'protected',
                'fields',
                'json',
                'array',
                'guarded',
                'totallyGuarded',
                'alias',
                'hide',
                'subscribedBefore'
            ]);
    }


    /**
     *
     */
    public function __wakeup()
    {
        $this->pdo = Connector::getConnection();
        $this->prepareDriver();
    }


    /**
     * @return string|array
     */
    public static function getTableName()
    {
        return '';
    }

    /**
     * @return bool|mixed
     */
    public function getPrimaryValue()
    {
        return $this->hasPrimaryKey() ?
            is_array($this->primaryKey) ?
                $this->attribute($this->primaryKey[0])
                : $this->attribute($this->primaryKey)
            : false;
    }

    /**
     * @param $name
     * @param $value
     */
    public function __set($name, $value)
    {
        $this->setAttribute($name, $value);
    }

    /**
     * @param $name
     * @return bool
     */
    public function hasAttribute($name)
    {

        return isset($this->attributes[$name]);
    }

    /**
     * @return array
     */
    public function getAttributes()
    {
        return $this->attributes;
    }

    /**
     * @param array $attributes
     * @return Model
     */
    public function setAttributes($attributes)
    {
        $this->attributes = $attributes;

        return $this;
    }


    /**
     * @param $key
     * @param $value
     */
    public function setAttribute($key, $value)
    {
        if ($this->isJson($key) && is_object($value) || is_array($value)) {
            $value = json_encode($value);
        } elseif ($this->isArray($key) && is_object($value) || is_array($value)) {
            $value = serialize($value);
        }

        $this->attributes[$key] = $value;
    }

    /**
     * @param $name
     * @param $arguments
     * @return mixed
     */
    public function __call($name, $arguments)
    {
        if (substr($name, 0, 3) === "get") {
            $name = lcfirst(substr($name, 2, strlen($name) - 3));

            if (method_exists($this, $name)) {
                return call_user_func_array([$this, $name], $arguments);
            }
        } else {
            throw new \BadMethodCallException(sprintf('%s method not found', $name));
        }
    }


    /**
     * @param $name
     * @return mixed
     * @throws \Exception
     */
    public function __get($name)
    {
        if (method_exists($this, $n = "get" . ucfirst($name))) {
            return call_user_func_array([$this, $n], []);
        }


        if (empty($this->attributes)) {
            $this->one();
        }


        if (false === $this->attributes) {
            throw new \PDOException(sprintf('Your query has been failed, message: %s', $this->error()[2]));
        }

        if ($this->hasAttribute($name)) {
            $value = $this->attribute($name);
        } else {
            throw new \Exception(sprintf('%s attribute could not found', $name));
        }


        if ($this->isJson($name)) {
            $value = json_decode($value);
        } elseif ($this->isArray($name)) {
            $value = unserialize($value);
        }

        return isset($this->timestamps[$name]) ? new ValueContainer($value) : $value;
    }

    /**
     * @param $callback
     * @return $this
     */
    public function beforeSave($callback)
    {
        if (!$this->eventManager instanceof EventDispatcher) {
            $this->eventManager = new EventDispatcher();
        }

        $this->eventManager->listen('before_save', $callback);

        return $this;
    }

    /**
     * @param $callback
     * @return $this
     */
    public function beforeCreate($callback)
    {
        if (!$this->eventManager instanceof EventDispatcher) {
            $this->eventManager = new EventDispatcher();
        }

        $this->eventManager->listen('before_create', $callback);

        return $this;
    }

    /**
     * @param $callback
     * @return $this
     */
    public function beforeUpdate($callback)
    {
        if (!$this->eventManager instanceof EventDispatcher) {
            $this->eventManager = new EventDispatcher();
        }

        $this->eventManager->listen('before_update', $callback);

        return $this;
    }

    /**
     * @param $callback
     * @return $this
     */
    public function beforeDelete($callback)
    {
        if (!$this->eventManager instanceof EventDispatcher) {
            $this->eventManager = new EventDispatcher();
        }

        $this->eventManager->listen('before_delete', $callback);

        return $this;
    }

    /**
     * @param $callback
     * @return $this
     */
    public function afterSave($callback)
    {
        if (!$this->eventManager instanceof EventDispatcher) {
            $this->eventManager = new EventDispatcher();
        }

        $this->eventManager->listen('after_save', $callback);

        return $this;
    }

    /**
     * @param $callback
     * @return $this
     */
    public function afterUpdate($callback)
    {
        if (!$this->eventManager instanceof EventDispatcher) {
            $this->eventManager = new EventDispatcher();
        }

        $this->eventManager->listen('after_update', $callback);

        return $this;
    }

    /**
     * @param $callback
     * @return $this
     */
    public function afterCreate($callback)
    {
        if (!$this->eventManager instanceof EventDispatcher) {
            $this->eventManager = new EventDispatcher();
        }

        $this->eventManager->listen('after_create', $callback);

        return $this;
    }

    /**
     * @param $callback
     * @return $this
     */
    public function afterDelete($callback)
    {
        if (!$this->eventManager instanceof EventDispatcher) {
            $this->eventManager = new EventDispatcher();
        }

        $this->eventManager->listen('after_delete', $callback);

        return $this;
    }


    /**
     * @param $value
     * @return ValueContainer
     */
    public function getValueContainer($value)
    {
        return new ValueContainer($value);
    }

    /**
     * @return boolean
     */
    public function isTotallyGuarded()
    {
        return $this->totallyGuarded;
    }

    /**
     * @param boolean $totallyGuarded
     * @return Model
     */
    public function setTotallyGuarded($totallyGuarded)
    {
        $this->totallyGuarded = $totallyGuarded;
        return $this;
    }


    /**
     * @return Model
     */
    public function totallyGuarded()
    {
        return $this->setTotallyGuarded(true);
    }

    /**
     * @return array
     */
    public function getAttach()
    {
        return $this->attach;
    }

    /**
     * @return EventDispatcher
     */
    public function getEventManager()
    {
        return $this->eventManager;
    }

    /**
     * @return bool
     */
    public function isSubscribedBefore()
    {
        return $this->subscribedBefore;
    }

    /**
     * @return string
     */
    public function getPrimaryKey()
    {
        return $this->primaryKey;
    }

    /**
     * @param string $primaryKey
     * @return Model
     */
    public function setPrimaryKey($primaryKey)
    {
        $this->primaryKey = $primaryKey;
        return $this;
    }

    /**
     * @param string $name
     * @return mixed
     */
    public function attribute($name)
    {
        return $this->attributes[$name];
    }


    /**
     *
     */
    public function rewind()
    {
        if (empty($this->attributes)) {
            $this->attributes = $this->all();
        }

        reset($this->attributes);
    }

    /**
     * @return mixed
     */
    public function current()
    {
        $var = current($this->attributes);
        return $var;
    }

    /**
     * @return mixed
     */
    public function key()
    {

        $var = key($this->attributes);
        return $var;
    }

    /**
     * @return mixed
     */
    public function next()
    {
        $var = next($this->attributes);
        return $var;
    }

    /**
     * @return bool
     */
    public function valid()
    {
        $key = key($this->attributes);
        $var = ($key !== null && $key !== false);
        return $var;
    }

    /**
     * Whether a offset exists
     * @link http://php.net/manual/en/arrayaccess.offsetexists.php
     * @param mixed $offset <p>
     * An offset to check for.
     * </p>
     * @return boolean true on success or false on failure.
     * </p>
     * <p>
     * The return value will be casted to boolean if non-boolean was returned.
     * @since 5.0.0
     */
    public function offsetExists($offset)
    {
        return $this->hasAttribute($offset);
    }

    /**
     * Offset to retrieve
     * @link http://php.net/manual/en/arrayaccess.offsetget.php
     * @param mixed $offset <p>
     * The offset to retrieve.
     * </p>
     * @return mixed Can return all value types.
     * @since 5.0.0
     */
    public function offsetGet($offset)
    {
        return $this->attribute($offset);
    }

    /**
     * Offset to set
     * @link http://php.net/manual/en/arrayaccess.offsetset.php
     * @param mixed $offset <p>
     * The offset to assign the value to.
     * </p>
     * @param mixed $value <p>
     * The value to set.
     * </p>
     * @return void
     * @since 5.0.0
     */
    public function offsetSet($offset, $value)
    {
        $this->attributes[$offset] = $value;
    }

    /**
     * Offset to unset
     * @link http://php.net/manual/en/arrayaccess.offsetunset.php
     * @param mixed $offset <p>
     * The offset to unset.
     * </p>
     * @return void
     * @since 5.0.0
     */
    public function offsetUnset($offset)
    {
        unset($this->attributes[$offset]);
    }
}