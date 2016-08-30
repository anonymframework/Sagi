<?php
namespace Sagi\Database;

use Iterator;
use PDO;

/**
 * Class QueryBuilder
 */
class QueryBuilder extends Engine implements Iterator
{


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
     * @return PDOStatement
     */
    private function returnPreparedResults($query, $ex = false)
    {
        $query = trim($query);
        $prepared = $this->pdo->prepare($query);

        $this->setArgs(array_slice($this->getArgs(), 0, count($this->getArgs())));
        $result = $prepared->execute($this->getArgs());

        if ($ex) {
            return $result;
        } else {
            return $prepared;
        }


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
     * @return mixed
     */
    public function first()
    {
        return $this->attributes;
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
     * @return mixed
     */
    public function one()
    {
        $get = $this->get();

        return $get->fetchObject('Sagi\Database\Results', ['table' => $this->getTable()]);
    }

    /**
     * @return array
     */
    public function all()
    {
        return $this->get()->fetchAll(PDO::FETCH_CLASS, 'Sagi\Database\Results', ['table' => $this->getTable()]);
    }


    /**
     * @return PDOStatement
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
     * @param array $sets
     * @return PDOStatement
     */
    public function update($sets = [])
    {
        return $this->returnPreparedResults($this->prepareUpdate($sets), true);
    }

    /**
     * @param array $sets
     * @return PDOStatement
     */
    public function create($sets = [])
    {
        return $this->returnPreparedResults($this->prepareCreate($sets), true);
    }


    /**
     * @return int
     */
    public function count()
    {
        $handled = $this->returnPreparedResults($this->prepareGetQuery())->rowCount();

        return $handled;
    }

    /**
     * @return bool|int
     */
    public function tableExists()
    {

        $inst = $this->pdo->query("SHOW TABLES LIKE '{$this->table}'");


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
     * @return bool
     */
    public function exists()
    {
        return ($this->count() > 0);
    }


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
        return $this->attributes[$name];
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
     * @return QueryBuilder
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
}
