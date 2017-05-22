<?php

namespace Sagi\Database;

use League\Pipeline\Pipeline;
use PDO;
use Sagi\Database\Exceptions\AttributeNotFoundException;
use Sagi\Database\Exceptions\NotFoundException;
use Sagi\Database\Exceptions\QueryException;
use Iterator;
use Countable;
use ArrayAccess;
use Carbon\Carbon;
use Sagi\Database\Mapping\Raw;
use Sagi\Database\Mapping\Entity;
use Sagi\Database\Builder\Traits\GuardCable;
use Sagi\Database\Builder\Traits\EventCable;
use Sagi\Database\Builder\Traits\PolicyCable;
use Sagi\Database\Builder\Traits\ModuleCable;

/**
 * Created by PhpStorm.
 * User: sagi
 * Date: 23.08.2016
 * Time: 17:23
 */
class Model extends QueryBuilder implements Iterator, Countable, ArrayAccess
{
    const CREATED_AT = 'created_at';
    const UPDATED_AT = 'updated_at';
    const DELETED_AT = 'deleted_at';


    use ModuleCable, GuardCable, EventCable, PolicyCable;
    /**
     * @var array
     */
    protected $casts;

    /**
     * @var int
     */
    protected $expiration = 600;


    /**
     * @var array
     */
    protected $with = [];
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
     *
     */
    protected $hide = [];


    /**
     * @var array
     */
    protected $saveBefore = [];

    /**
     * @var Pipeline
     */
    protected $pipeline;

    /**
     * Model constructor.
     * @param array $attributes
     * @param bool $single
     * @throws \Exception
     */
    public function __construct(array $attributes = [])
    {
        $this->bootTraits();
        $this->select('*');

        if (!empty($attributes)) {
            if (!is_array($attributes)) {
                $attributes = (array)$attributes;
            }
            $this->fill($attributes);
        }

        $this->bootPipeline();
        parent::__construct();
    }


    private function bootPipeline(){
        $this->pipeline = new Pipeline();

    }

    /**
     * @return array
     */
    public function arrayAll()
    {
        return parent::all();
    }

    /**
     * @param array $attributes
     * @return $this
     * @throws \Exception
     */
    public function fill(array $attributes)
    {
        foreach ($attributes as $key => $value) {
            if ($this->isFillable($key)) {
                $this->setAttribute($key, $value);
            } else {
                throw new Exceptions\ProtectedAttributeException(sprintf('You cannot set any value on %s attributes', $key));
            }
        }


        return $this;
    }


    /**
     * @param  $useCache bool if caching is enable, result will come from cache, if is not, results will come from db
     * @return Model
     */
    public function all($useCache = true)
    {
        if ($useCache === true && $this->isCacheUsed()) {
            return $this->cacheAll();
        } else {
            $fetched = $this->get()->fetchAll(PDO::FETCH_ASSOC);


            $this->setAttributes($fetched);
            return $this;
        }
    }

    /**
     * @return $this
     * @throws Exceptions\NotFoundException
     */
    public function allOrFail()
    {
        $this->all();

        if (empty($this->attributes)) {
            throw new NotFoundException(sprintf('Your query returned empty response, table : %s', $this->table));
        }

        return $this;
    }

    /**
     * @param  $useCache bool if caching is enable, result will come from cache, if is not, results will come from db
     * @return Model
     */
    public function one($useCache = true)
    {

        if ($useCache === true && $this->isCacheUsed()) {
            $this->cacheOne();
        } else {
            $get = $this->get();

            $this->setAttributes($get->fetch(PDO::FETCH_ASSOC));
        }

        return $this;
    }

    /**
     * @param  $useCache bool if caching is enable, result will come from cache, if is not, results will come from db
     * @return $this
     * @throws NotFoundException
     */
    public function oneOrFail($useCache = true)
    {
        $this->one($useCache);

        if (empty($this->attributes)) {
            throw new Exceptions\NotFoundException(sprintf('Your query returned empty response, table : %s', $this->table));
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
     * @return $this
     */
    public static function find($conditions = [])
    {
        $instance = static::createNewInstance();

        if (is_array($conditions) && !empty($conditions)) {
            foreach ((array) $conditions as $item) {
                if (is_array($item)) {
                    $instance->where($item[0], $item[1], isset($item[2]) ? $item[2] : null);
                }
            }

        } elseif (is_string($conditions) || is_int($conditions)) {
            $primaryKey = is_array($instance->primaryKey) ?
                $instance->primaryKey[0] :
                $instance->primaryKey;

            $instance->where($primaryKey, $conditions);
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
     * @return $this
     */
    public static function findOne($id)
    {
        return static::find($id)->one();
    }

    /**
     * @param int $id
     * @return Model
     * @throws NotFoundException
     */
    public static function findOneOrFail($id)
    {
        $finded = static::find($id)->oneOrFail();

        $attributes = $finded->getAttributes();

        if (empty($attributes)) {
            throw new Exceptions\NotFoundException(sprintf('%d %s could not found in %s', $id, $finded->getPrimaryKey(), $finded->getTable()));
        }

        return $finded;
    }


    /**
     * @param array $conditions
     * @return $this
     */
    public static function findAll(array $conditions = null)
    {
        return static::find($conditions)->all();
    }

    /**
     * @param $conditions
     * @return $this
     */
    public static function findAllOrFail($conditions)
    {
        return static::find($conditions)->allOrFail();
    }

    /**
     * @param string|Model $class
     * @param array $link
     * @param string $alias
     * @return mixed
     */
    public function hasMany($class, array $link, $alias = null)
    {
        $class = $class::createNewInstance();

        if ($alias !== null) {
            $name = $alias;
        } else {
            $name = $class->getTable();
        }


        $append = '#' . $link[0] . ':' . $this->__get($link[0]);

        $name .= $append;

        if (!RelationBag::isPreparedBefore($name, 'many')) {

            RelationBag::addRelative($name, $class, $link, 'many');
        }

        return RelationBag::getRelation($name, $this, 'many');
    }

    /**
     * @param string|Model $class
     * @param array $link
     * @param string $alias
     * @return mixed
     */
    public function hasOne($class, array $link, $alias = null)
    {
        $class = $class::createNewInstance();

        if ($alias !== null) {
            $name = $alias;
        } else {
            $name = $class->getTable();
        }

        $append = '#' . $link[0] . ':' . $this->__get($link[0]);

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
     * @param string $name
     * @return bool
     */
    public function isArray($name)
    {
        return in_array($name, $this->array);
    }

    /**
     * @return $this|bool|Model
     * @throws QueryException
     */
    public function save()
    {

        if (!empty($this->where)) {

            if ($this->update() === false) {
                throw new QueryException(sprintf('update query has been failed, error message from database1 :%s', $this->error()[2]));
            }

            return $this;
        } else {
            return $this->create();
        }
    }

    /**
     * @param null $data
     * @return bool|Model
     * @throws AttributeNotFoundException
     * @throws QueryException
     */
    public function create($data = null)
    {
        $this->callEvent('before_create', $this);

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

        if (!empty($this->saveBefore)) {
            foreach ($this->saveBefore as $item) {
                if ($this->hasAttribute($item)) {
                    $attr = $this->attribute($item);

                    $save = $attr->save();

                    $primaryValue = $save->attribute($save->getPrimaryKey());


                    $entity->datas[$item] = $primaryValue;
                } else {
                    throw new AttributeNotFoundException(sprintf('%s attribute could not found, we cant save it', $item));
                }
            }
        }

        if (parent::create($entity)) {
            if (!empty($this->primaryKey) && !is_array($this->primaryKey) && $entity->multipile === false) {
                $return = static::findOne($this->lastInsertId());
            } else {
                $return = $this;
            }
        } else {
            throw new Exceptions\QueryException(sprintf('Query has been failed, error message: %s', $this->error()[2]));
        }

        $this->callEvent('after_create', [$return, $this]);

        return $return;
    }

    /**
     * @return int
     */
    public function lastInsertId()
    {
        return $this->prepareConnection()->lastInsertId();
    }

    /**
     * @param array $datas
     * @return PDOStatement
     */
    public function update($datas = [])
    {

        $this->callEvent('before_update', [$this]);

        $this->setUpdatedAt();

        if (empty($datas)) {
            $datas = $this->getAttributes();
        }

        $return = parent::update($datas);

        $this->callEvent('after_update', [$return, $this]);
        return $return;
    }

    /**
     * @return Model
     */
    public function delete()
    {
        $this->callEvent('before_delete', [$this]);
        $return = parent::delete();
        $this->callEvent('after_delete', [$return, $this]);

        return $this;
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
            $this->attributes[$updated] = date($this->timestampFormat(), time());
        }

        return $this;
    }

    /**
     * @return string
     */
    protected function timestampFormat()
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
     * @param $value
     * @return bool|mixed
     */
    public function hasTimestamp($value)
    {
        return (is_array($this->timestamps)) ? in_array($value, $this->timestamps) : false;
    }

    /**
     * @return string
     */
    public function json()
    {
        return json_encode($this->getAttributes());
    }

    /**
     * @return string
     */
    public function __toString()
    {
        return serialize($this->getAttributes());
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
     * @return bool
     */
    public function __isset($name)
    {
        return $this->hasAttribute($name);
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
        } elseif ($value instanceof Model) {
            $this->saveBefore[] = $key;
        }
        $mutator = $this->hasMutator($key);

        if ($mutator !== false) {
            $this->$mutator($value);
        } else {
            $this->attributes[$key] = $value;
        }
    }


    /**
     * @param string $key
     * @return bool
     */
    private function hasMutator($key)
    {
        $mutator = 'set' . $this->prepareMethodName($key) . 'Attribute';

        return method_exists($this, $mutator) ? $mutator : false;
    }

    /**
     * @param $name
     * @return string
     */
    private function prepareColumnName($name)
    {
        return implode('_', array_map(function ($value) {
            return mb_convert_case($value, MB_CASE_LOWER);
        }, preg_split('/(?=[A-Z])/',
            $name, -1, PREG_SPLIT_NO_EMPTY)));
    }

    /**
     * @param string $name
     * @return mixed
     */
    private function callMagicGetMethods($name)
    {
        $column = $this->prepareColumnName($name);

        return $this->{$column};
    }

    /**
     * @param string $name
     * @param $arguments
     */
    private function callMagicSetMethod($name, $arguments)
    {
        $column = $this->prepareColumnName($name);

        if (count($arguments) > 0) {
            return $this->setAttribute($column, $arguments[0]);
        }
    }

    /**
     * @param string $name
     * @param string $arguments
     * @return mixed
     */
    private function callMagicFilterByMethods($name, $arguments)
    {
        $column = $this->prepareColumnName($name);
        array_unshift($arguments, $column);

        return call_user_func_array([$this, 'where'], $arguments);
    }

    /**
     * @param $name
     * @param $arguments
     * @return mixed
     */
    public function __call($name, $arguments)
    {
        if (substr($name, 0, 3) === "get") {
            return $this->callMagicGetMethods($name);
        } elseif (substr($name, 0, 3) === "set") {
            return $this->callMagicSetMethod($name, $arguments);
        } elseif (substr($name, 0, 8) === "filterBy") {
            $this->callMagicFilterByMethods($name, $arguments);
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
        if (empty($this->attributes)) {
            $this->one();
        }

        if (false === $this->attributes) {
            throw new \PDOException(
                sprintf(
                    'Your query has been failed, message: %s',
                    $this->error()[2])
            );
        }

        if ($this->hasAttribute($name)) {
            $value = $this->attribute($name);
        } else {
            throw new AttributeNotFoundException(
                sprintf('%s attribute could not found', $name)
            );
        }


        if ($this->isJson($name)) {
            $value = json_decode($value);
        } elseif ($this->isArray($name)) {
            $value = unserialize($value);
        }

        if (isset($this->casts[$name])) {
            settype($value, $this->casts[$name]);
        }

        if ($acc = $this->hasAccesor($name)) {
            return $this->$acc($value);
        }

        return isset($this->timestamps[$name]) ? new Carbon($value) : $value;
    }

    /**
     * @param $key
     * @return bool|string
     */
    private function hasAccesor($key)
    {
        $accesor = 'get' . $this->prepareMethodName($key) . 'Attribute';

        return method_exists($this, $accesor) ? $accesor : false;
    }

    /**
     * @param $name
     * @return mixed|string
     */
    private function prepareMethodName($name)
    {
        if (strpos($name, '_')) {
            $name = implode('', array_map(function ($value) {
                $ucfirst = mb_convert_case($value, MB_CASE_TITLE);

                return $ucfirst;
            }, explode('_', $name)));

        } else {
            $name = mb_convert_case($name, MB_CASE_TITLE);
        }
        return $name;
    }


    /*
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
        reset($this->attributes);
    }

    /**
     * @return mixed
     */
    public function current()
    {
        $var = current($this->attributes);

        if (is_array($var)) {
            return Singleton::load(static::className())->setAttributes($var);
        } else {
            return $var;
        }
    }


    public static function raw($query)
    {
        return new Raw($query);
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
