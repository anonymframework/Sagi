<?php
namespace Sagi\Database;

use ArrayAccess;
use Iterator;
use PDO;

/**
 * Class QueryBuilder
 */
class QueryBuilder extends Engine implements Iterator, ArrayAccess
{


    /**
     * @var string
     */
    private $lastQueryString;

    /**
     * @var array
     */
    public $attributes;


    /**
     * @return array
     */
    public function error()
    {
        return $this->pdo->errorInfo();
    }

    /**
     * @param $query
     * @param bool $ex
     * @return \PDOStatement|bool
     */
    private function returnPreparedResults($query, $ex = false)
    {
        $prepared = $this->prepare($query, $this->getArgs(), $ex);

        $this->setArgs([])->setLastQueryString($query);

        return $prepared;
    }

    /**
     * @param $query
     * @param $args
     * @param bool $execute
     * @return bool|\PDOStatement
     */
    public function prepare($query, $args, $execute = false)
    {
        $prepared = $this->pdo->prepare($query);

        $exed = $prepared->execute($args);

        return $execute ? $exed : $prepared;
    }


    /**
     * @param $query
     * @return \PDOStatement
     */
    public function query($query)
    {
        return $this->pdo->query($query);
    }


    /**
     * @param string $table
     * @return static
     */
    public static function createNewInstance($table = null)
    {
        $insantce = new static();

        if ($table !== null && is_string($table)) {
            $insantce->setTable($table);
        }

        return $insantce;
    }


    /**
     * @return \PDOStatement
     */
    public function get()
    {
        $handled = $this->prepareGetQuery();

        return $this->returnPreparedResults($handled);
    }

    /**
     * @return PDOStatement
     */
    public function delete()
    {
        return $this->returnPreparedResults($this->prepareDelete(), true);

    }

    /**
     * @param array $datas
     * @return PDOStatement
     */
    public function update($datas = [])
    {
        return $this->returnPreparedResults($this->prepareUpdate($datas), true);
    }

    /**
     * @param array $datas
     * @return PDOStatement
     */
    public function create($datas = [])
    {
        $create = $this->returnPreparedResults($this->prepareCreate($datas), true);

        $this->setArgs([]);

        return $create;
    }


    /**
     * @return int
     */
    public function count()
    {
        $handled = $this->prepare($this->prepareGetQuery(), $this->getArgs());
        $this->setArgs([]);

        return $handled->rowCount();
    }

    /**
     * @return bool
     */
    public function exists()
    {
        return ($this->count() > 0);
    }

    /**
     * @param string|null $table
     * @return bool|int
     */
    public function tableExists($table = null)
    {
        $table = $table === null ? $this->table : $table;

        $inst = $this->pdo->query("SHOW TABLES LIKE '{$table}'");


        return $inst ? $inst->rowCount() : false;
    }

    /**
     * @param $column
     * @return bool|int
     */
    public function columnExists($column)
    {
        $ins = $this->pdo->query("SHOW COLUMNS FROM `{$this->table}` LIKE '$column';");

        return $ins ? $ins->rowCount() : false;
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
        $var = ($key !== NULL && $key !== FALSE);
        return $var;
    }


    /**
     * @param $name
     * @return mixed
     */
    public function __get($name)
    {

        if (empty($this->attributes)) {
            $this->one();
        }

        if (false === $this->attributes) {
            throw new \PDOException('your query is failed');
        }

        if ($this->hasAttribute($name)) {
            $value = $this->attribute($name);

            return $value;
        } else {
            throw new \Exception(sprintf('%s attribute could not found', $name));
        }


    }


    /**
     * @param $name
     * @return bool
     */
    public function hasAttribute($name)
    {
        return !empty($this->attributes[$name]);
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
     * @return PDO
     */
    public function getPdo()
    {
        return $this->pdo;
    }

    /**
     * @param PDO $pdo
     * @return Engine
     */
    public function setPdo($pdo)
    {
        $this->pdo = $pdo;
        return $this;
    }

    /**
     * @return string
     */
    public function getLastQueryString()
    {
        return $this->lastQueryString;
    }

    /**
     * @param string $lastQueryString
     * @return QueryBuilder
     */
    public function setLastQueryString($lastQueryString)
    {
        $this->lastQueryString = $lastQueryString;
        return $this;
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
