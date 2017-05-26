<?php

namespace Sagi\Database;

use PDO;
use Sagi\Database\Exceptions\WhereException;
use Sagi\Database\Mapping\Entity;
use Sagi\Database\Mapping\Group;
use Sagi\Database\Mapping\Join;
use Sagi\Database\Mapping\Limit;
use Sagi\Database\Mapping\Match;
use Sagi\Database\Mapping\SubWhere;
use Sagi\Database\Mapping\Where;

/**
 * Class QueryBuilder
 */
class QueryBuilder
{
    /**
     * @var string
     */
    protected $database;

    /**
     * @var array
     */
    protected $error;

    /**
     * @var string
     */
    private $lastQueryString;

    /**
     * @var Builder
     */
    private $builder;

    /**
     *  table name
     *
     * @var string|callable
     *
     */
    protected $table;

    /**
     * @return array
     */
    public function error()
    {
        return $this->error;
    }

    /**
     * @param string $database configs.php dosyasında `connections` alıntdaki anahtar ismi
     * @return $this
     */
    public function on($database)
    {
        $this->database = $database;

        return $this;
    }

    /**
     *
     */
    public function prepareConnection()
    {
        $connection = ! empty($this->database) ? $this->database : null;

        if (false === $this->getBuilder()->isConnected()) {
            $this->getBuilder()->connect($connection);
        }

        return $this
            ->getBuilder()
            ->getDriver();
    }


    /**
     * @param mixed $select
     * @return QueryBuilder
     */
    public function select($select)
    {
        if (is_string($select)) {
            $select = explode(',', $select);
        }

        if (is_callable($select)) {
            $select = [$select];
        }

        $select = array_map(
            function ($value) {
                if (is_string($value)) {
                    $value = trim($value);
                }

                return $value;
            },
            $select
        );

        $this->getBuilder()->setSelect($select);

        return $this;
    }

    /**
     *
     * @param string|callable $table
     * @return Builder
     */
    public function from($table)
    {
        return $this->setTable($table);
    }

    /**
     * @param $column
     * @param string $type
     * @return $this
     */
    public function order($column, $type = 'DESC')
    {
        $this->setOrder([$column, $type]);

        return $this;
    }

    /**
     * @param int|string|array $limit
     * @return $this
     */
    public function limit($limit)
    {
        if (is_array($limit)) {
            list($startFrom, $offset) = $limit;
        }else{
            $startFrom = 0;
            $offset = 10;
        }

        $limit = new Limit(
            $startFrom, $offset
        );

        $this->getBuilder()->setLimit($limit);

        return $this;
    }


    /**
     * @param $group
     * @return QueryBuilder
     */
    public function group($group)
    {
        $multipile = false;

        if ( ! is_array($group) && is_string($group)) {
            $group = explode(',', $group);
        }

        if (count($group) > 1) {
            $multipile = true;
        }

        $this->getBuilder()->setGroupBy(
            new Group($group, $multipile)
        );

        return $this;
    }

    /**
     * @param $column
     * @param $datas
     * @param bool $not
     * @return QueryBuilder
     */
    public function like($column, $datas, $not = false)
    {
        if (is_array($datas)) {
            $type = isset($datas[1]) ? $datas[1] : $datas[0];
            $data = isset($datas[0]) ? $datas[0] : '';
        } else {
            $type = $data = $datas;
        }

        $type = str_replace('?', $data, $type);

        $like = ' LIKE ';

        if ($not) {
            $like = ' NOT'.$like;
        }


        return $this->where($column, '', $like, $type);
    }

    /**
     * @param $column
     * @param $datas
     * @return QueryBuilder
     */
    public function notLike($column, $datas)
    {
        return $this->like($column, $datas, true);
    }

    /**
     * @param $column
     * @param $datas
     * @return QueryBuilder
     */
    public function orNotLike($column, $datas)
    {
        return $this->orLike($column, $datas, true);
    }

    /**
     * @param string $column
     * @param mized $datas
     * @param bool $not
     * @return QueryBuilder
     */
    public function orLike($column, $datas, $not = false)
    {
        if (is_array($datas)) {
            $type = isset($datas[1]) ? $datas[1] : $datas[0];
            $data = isset($datas[0]) ? $datas[0] : '';
        } else {
            $type = $data = $datas;
        }

        $type = str_replace('?', $data, $type);

        $like = ' LIKE ';

        if ($not) {
            $like = ' NOT'.$like;
        }


        return $this->orWhere([$column, $like, $type]);
    }


    /**
     * @param string $column
     * @param array|callable|string $values
     * @param bool $not
     * @return $this
     */
    public function in($column, $values, $not = false)
    {
        $in = ' IN ';

        if ($not) {
            $in = ' NOT'.$in;
        }

        return $this->where($column, $in, $values, 'AND');
    }


    /**
     * @param string $column
     * @param array|callable|string $datas
     * @return Model
     */
    public function notIn($column, $datas)
    {
        return $this->in($column, $datas, true);
    }

    /**
     * @param string $column
     * @param array|callable|string $datas
     * @return Model
     */
    public function orNotIn($column, $datas)
    {
        return $this->orIn($column, $datas, true);
    }


    /**
     * @param string $column
     * @param array|callable|string $datas
     * @return Model
     */
    public function orIn($column, $datas, $not = false)
    {
        $in = ' IN ';

        if ($not) {
            $in = ' NOT'.$in;
        }

        return $this->orWhere([$column, $in, $datas], null, null, 'OR', false);
    }


    /**
     * @param string $table
     * @param string $localKey
     * @param string $backet
     * @param string $foreignKey
     * @param string $type
     * @return $this
     */
    public function join($table, $localKey, $backet, $foreignKey, $type = 'INNER JOIN')
    {
        $this->getBuilder()->addJoin(
            new Join($type, $table, $foreignKey, $localKey, $backet)
        );

        return $this;
    }

    /**
     * @return $this
     */
    public function beginTransaction()
    {
        $this->prepareConnection()->beginTransaction();

        return $this;
    }

    /**
     * @return $this
     */
    public function commit()
    {
        $this->prepareConnection()->commit();

        return $this;
    }


    /**
     * @return $this
     */
    public function rollBack()
    {
        $this->prepareConnection()->rollBack();

        return $this;
    }

    /**
     * @param string $columns
     * @param mixed $values
     * @param string $mode
     * @return $this
     */
    public function match($columns, $values, $mode = 'BOOLEAN MODE', $type = 'AND')
    {

        $this->getBuilder()->addWhere(
            new Match($columns, $values, $mode, $type)
        );

        return $this;
    }

    /**
     * @param mixed $columns
     * @param mixed $values
     * @param $mode
     * @return QueryBuilder
     */
    public function orMatch($columns, $values, $mode = 'BOOLEAN MODE')
    {
        return $this->match($columns, $values, $mode, 'OR');
    }

    /**
     * @return string|callable
     */
    public function getTable()
    {
        return $this->table;
    }

    /**
     * @param string|callable $table
     * @return $this
     */
    public function setTable($table)
    {
        $this->table = $table;
        $this->getBuilder()->setTable($table);

        return $this;
    }

    /**
     * @param Raw|callable $where
     * @param string $type
     * @return $this
     */
    public function subWhere($where, $type = 'AND')
    {
        $subWhere = new SubWhere($where, $type);

        $this->getBuilder()->addWhere($subWhere);

        return $this;
    }

    /**
     * @param Raw|callable $where
     * @return $this
     */
    public function orSubWhere($where)
    {
        return $this->subWhere($where, 'OR');
    }

    /**
     * @param string $field
     * @param string $operator
     * @param string $value
     * @param string $type
     * @return $this
     */
    public function where($field, $operator, $value, $type = 'AND')
    {
        $where = new Where(
            $field, $operator, $value, $type
        );


        $this->getBuilder()->addWhere(
            $where,
            $this->prepareWhereMark(
                $operator,
                $type,
                $field
            )
        );

        return $this;
    }

    /**
     * @param string $field
     * @param string $operator
     * @param mixed $value
     * @return $this
     */
    public function orWhere($field, $operator, $value)
    {
        return $this->where($field, $operator, $value, 'OR');
    }


    /**
     * @param $mark
     * @param $type
     * @param $field
     * @return string
     * @throws WhereException
     */
    private function prepareWhereMark($mark, $type, $field)
    {
        $operators = Builder::getOperators();

        if ( ! isset($operators[$mark])) {
            throw new WhereException(
                sprintf(
                    '%s could not found, you can use one of these(%s)',
                    $mark,
                    $this->join(',', $this->marks)
                )
            );
        }

        return $field.'.'.$type.'.'.$operators[$mark];
    }

    /**
     * @param string $query
     * @return bool|\PDOStatement
     */
    public function prepare($query)
    {
        if ( ! $this->connection instanceof DriverInterface) {
            $this->prepareConnection();
        }

        return $this
            ->getBuilder()
            ->getDriver()
            ->prepare($query);
    }

    /**
     * @param mixed $prepare
     * @param array $args
     * @return mixed
     */
    public function execute($prepare, $args)
    {
        return $this
            ->getBuilder()
            ->getDriver()
            ->execute($prepare, $args);
    }

    /**
     * @param string $query
     * @return \PDOStatement
     */
    public function query($query)
    {
        return $this
            ->getBuilder()
            ->getDriver()
            ->query($query);
    }


    /**
     * @param string $table
     * @return $this
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
        return $this->handleResults(
            $this
                ->getBuilder()
                ->getGrammer()
                ->read($this->getBuilder())
        );
    }

    /**
     * @return bool|\PDOStatement
     */
    public function delete()
    {
        return $this->handleResults(
            $this
                ->getBuilder()
                ->getGrammer()
                ->delete(
                    $this->getTable(),
                    $this
                        ->getBuilder()
                        ->getWhere()
                ),
            true
        );

    }

    /**
     * @param array $datas
     * @return bool|\PDOStatement
     */
    public function update($datas = [])
    {
        if (is_array($datas)) {
            $datas = new Entity($datas);
        }


        return $this->handleResults(
            $this
                ->getBuilder()
                ->getGrammer()
                ->update(
                    $datas,
                    $this
                        ->getTable(),
                    $this
                        ->getBuilder()
                        ->getWhere()
                ),
            true
        );
    }


    /**
     * @param null|array $data
     * @return bool|\PDOStatement
     */
    public function create($data = null)
    {
        if (is_array($data)) {
            $data = new Entity($data);
        }

        $create = $this->handleResults(
            $this
                ->getBuilder()
                ->getGrammer()
                ->create(
                    $data,
                    $this
                        ->getTable()
                ),
            true
        );

        $this->getBuilder()->setArgs([]);

        return $create;
    }

    /**
     * @param array $result
     * @param bool $returnOnlyExecute
     * @return array
     */
    private function handleResults(array $result, $returnOnlyExecute = false)
    {
        list($query, $args) = $result;

        $this->getBuilder()->setArgs($args);

        $prepare = $this->prepare($query);

        list($prepareResult, $executeResult) = $this->execute($prepare, $args);

        if (true === $returnOnlyExecute) {
            return $executeResult;
        }

        return array($prepareResult, $executeResult);
    }

    /**
     * @return int
     */
    public function count()
    {
        $handled = $this->prepare($this->prepareCountQuery(), $this->getArgs());

        $this->setArgs([]);

        $fetched = $handled->fetch(PDO::FETCH_OBJ);

        return $fetched->row_count;
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
        $inst = $this->prepare("SHOW TABLES LIKE '{$table}'", []);


        return $inst ? $inst->rowCount() : false;
    }

    /**
     * @param $column
     * @return bool|int
     */
    public function columnExists($column)
    {
        $ins = $this->prepare("SHOW COLUMNS FROM `{$this->table}` LIKE '$column';", []);

        return $ins ? $ins->rowCount() : false;
    }

    /**
     * @return array
     */
    public function all()
    {
        return $this->get()->fetchAll(PDO::FETCH_OBJ);
    }

    /**
     * @return mixed
     */
    public function one()
    {
        return $this->get()->fetch(PDO::FETCH_OBJ);
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
     * @return Builder
     */
    public function getBuilder()
    {
        if (null === $this->builder) {
            $this->builder = new Builder($this->getTable());
        }

        return $this->builder;
    }

    /**
     * @param Builder $builder
     * @return QueryBuilder
     */
    public function setBuilder($builder)
    {
        $this->builder = $builder;

        return $this;
    }

    /**
     * @return string
     */
    public static function getClassName()
    {
        return get_called_class();
    }

    /**
     * @param string $as
     * @return $this
     */
    public function setAs($as)
    {
        $this->getBuilder()->setAs($as);

        return $this;
    }

    /**
     * @return string
     */
    public function getAs()
    {
        return $this->getBuilder()->getAs();
    }
}

