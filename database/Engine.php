<?php

namespace Sagi\Database;

use Exception;
use PDO;
use Sagi\Database\Drivers\Driver;
use Sagi\Database\Drivers\MysqlDriver;
use Sagi\Database\Mapping\Entity;
use Sagi\Database\Mapping\Where;

/**
 * Class Engine
 * @package Sagi\Database
 */
class Engine
{

    /**
     * @var PDO
     */
    public $pdo;
    /**
     * @var select query
     */
    private $select;

    /**
     *  table name
     *
     * @var string
     *
     */
    protected $table;

    /**
     *
     * @var array
     */
    private $limit;

    /**
     * @var string
     */
    private $groupBy;

    /**
     * where query
     *
     * @var array
     */
    private $where = [];

    /**
     * or where query
     *
     * @var array
     */
    private $orWhere = [];

    /**
     * @var array
     */
    private $order;

    /**
     * @var array
     */
    private $join = [];

    /**
     * @var string
     */
    private $having = '';

    /**
     * @var array
     */
    private $args = [];


    /**
     * @var string
     */
    private $as;

    /**
     * @var bool
     */
    private $prepareValues = true;

    /**
     * @var array
     */
    private $drivers = [
        'mysql' => 'Sagi\Database\Drivers\MysqlDriver',
        'sqlite' => 'Sagi\Database\Drivers\SqliteDriver',
        'pqsql' => 'Sagi\Database\Drivers\PorteqsqlDriver',
    ];

    /**
     * @var Driver
     */
    protected $driver;


    /**
     * Engine constructor.
     * @param array $configs
     * @param string $table
     * @throws Exception
     */
    public function __construct()
    {

        $this->prepareDriver();


        Connector::madeConnection();
        $this->pdo = Connector::getConnection();

    }


    protected function prepareDriver()
    {
        if ($driver = ConfigManager::get('driver')) {
            if (isset($this->drivers[$driver])) {
                $driver = $this->drivers[$driver];

                $this->driver = new $driver;

            } else {
                throw new Exception(sprintf('%s driver not found', $driver));
            }

        } else {
            throw new Exception('We need to your host,dbname,username and password informations for make a successfull connection ');
        }
    }


    /**
     * @return PDOStatement
     */
    protected function prepareDelete()
    {
        $pattern = 'DELETE FROM :from :where';

        $handled = $this->handlePattern($pattern, array(
            ':from' => $this->getTable(),
            ':where' => $this->prepareWhereQuery($this->getWhere())
        ));

        return $handled;
    }

    /**
     * @param array $sets
     * @return PDOStatement
     */
    protected function prepareUpdate($sets = [])
    {
        $pattern = 'UPDATE :from SET :update :where';


        $setted = $this->databaseSetBuilder($sets);
        $this->args = array_merge($this->args, $setted['args']);

        $handled = $this->handlePattern($pattern, [
            ':from' => $this->getTable(),
            ':update' => $setted['content'],
            ':where' => $this->prepareWhereQuery($this->getWhere())
        ]);

        return $handled;
    }

    /**
     * @param Entity $entity
     * @return PDOStatement
     */
    protected function prepareCreate($entity)
    {
        $pattern = 'INSERT INTO :from :insert';

        $setted = $this->prepareInsertQuery($entity);

        $this->args = array_merge($this->args, $setted['args']);

        $handled = $this->handlePattern($pattern, [
            ':from' => $this->getTable(),
            ':insert' => $setted['content'],
        ]);


        return $handled;
    }

    protected function prepareInsertQuery(Entity $entity)
    {
        $s = '(';


        $count = count($entity->datas);

        foreach (array_keys($entity->datas[0]) as $key => $value) {
            $s .= $key . ",";
        }

        $s = rtrim($s, ",");

        $s .= ") VALUES  ";


        if ($entity->multipile === false) {
            $s .= $this->handleInsertValue($count);
            $args = array_values($entity->datas);
        } else {
            foreach ($entity->datas as $data) {
                $values = array_values($data);

                $s .= $this->handleInsertValue(count($values)) . ",";
                $args = array_merge($args, $values);
            }

            $s = rtrim($s, ",");
        }


        return ['args' => $args, 'content' => $s];
    }

    /**
     * @param string $count
     * @return string
     */
    private function handleInsertValue($count)
    {
        $s = "(";

        $s .= join(",", array_fill(0, $count, '?'));

        $s = rtrim($s, ",");

        $s .= ")";

        return $s;
    }

    /**
     * @return string
     */
    public function prepareGetQuery()
    {


        $pattern = 'SELECT :select FROM :from :join :group :having :where :order :limit';

        $handled = $this->handlePattern($pattern, [
            ':select' => $this->driver->prepareSelectQuery($this->getSelect()),
            ':from' => $this->getTable(),
            ':join' => $this->driver->prepareJoinQuery($this->getJoin()),
            ':group' => $this->driver->prepareGroupQuery($this->getGroupBy()),
            ':having' => $this->driver->prepareHavingQuery($this->getHaving()),
            ':where' => $this->prepareWhereQuery($this->getWhere()),
            ':order' => $this->driver->prepareOrderQuery($this->getOrder()),
            ':limit' => $this->driver->prepareLimitQuery($this->getLimit())
        ]);

        return $handled;
    }

    protected function prepareWhereQuery($where)
    {
        $string = '';

        if (!empty($where)) {
            $string .= $this->handleWhereQuery($where);
        }


        if ($string !== '') {
            $string = 'WHERE ' . $string;
        }

        return $string;
    }

    /**
     * @return string
     */
    private function handleWhereQuery($where)
    {

        $args = [];
        $s = '';

        foreach ($where as $item) {

            /**
             * @var Where $item
             */
            $this->checkWhereItem($item);

            if (is_callable($item->query) || is_array($item->query)) {
                $prepared = $this->prepareInQuery($item->query);

                $preparedQuery = $prepared[0];

                $args = array_merge($args, $prepared[1]);
            }


            if ($item->clean === true) {
                $query = '?';
                $args[] = $item->query;
            } else {
                $query = isset($preparedQuery) ? $preparedQuery : $item->query;
            }

            $field = $item->field;

            $type = $item->type;

            $backed = $item->backet;

            if ($s !== '') {
                $s .= "$type {$field} $backed $query ";
            } else {
                $s .= "$field $backed $query ";
            }
        }

        $s = rtrim($s, $type);

        $this->args = array_merge($this->args, $args);


        return $s;
    }

    /**
     * @param $callback
     * @return string
     */
    private function prepareSubQuery($callback, $instance)
    {
        /**
         * @var $builder QueryBuilder
         */
        $builder = call_user_func_array($callback, [$instance]);

        $query = '(' . $builder->prepareGetQuery() . ')';


        if ($builder->hasAs()) {
            $query .= ' AS ' . $builder->getAs();
        }

        return [$query, $builder->getArgs()];
    }


    /**
     * @return mixeds|string
     */
    private function prepareInQuery($datas)
    {
        if (is_array($datas)) {
            $inQuery = '[' . implode(',', $datas) . ']';
        } elseif (is_callable($datas)) {
            $inQuery = $this->prepareSubQuery($datas, static::createNewInstance());
        }

        return is_array($inQuery) ? $inQuery : [$inQuery, []];
    }

    /**
     * @param $item
     * @throws WhereException
     */
    private function checkWhereItem($item)
    {
        if (!$item instanceof Where) {
            throw new WhereException(sprintf('Wrong where query'));
        }
    }

    /**
     * @param $pattern
     * @param $args
     * @return mixeds
     */
    private function handlePattern($pattern, $args)
    {
        foreach ($args as $key => $arg) {
            $pattern = str_replace($key, $arg, $pattern);
        }

        $exploded = array_filter(explode(' ', $pattern), function ($value) {
            return !empty($value);
        });

        return join(" ", $exploded);
    }


    /**
     * @param array $select
     * @return QueryBuilder
     */
    public function select($select = [])
    {
        if (is_string($select)) {
            $select = explode(",", $select);
        }

        $table = $this->getTable();

        $select = array_map(function ($value) use ($table) {
            if (is_string($value) && strpos($value, '.') === false) {
                return $table . '.' . $value;
            }

            return $value;
        }, $select);


        return $this->setSelect($select);
    }

    /**
     * @param $column
     * @param string $type
     * @return QueryBuilder
     */
    public function order($column, $type = 'DESC')
    {
        return $this->setOrder([$column, $type]);
    }

    /**
     * @param $limit
     * @return $this
     */
    public function limit($limit)
    {
        if (is_string($limit) or is_numeric($limit)) {
            $this->setLimit([$limit]);
        } else {
            $this->setLimit($limit);
        }

        return $this;
    }


    /**
     * @param $group
     * @return QueryBuilder
     */
    public function group($group)
    {
        return $this->setGroupBy($group);
    }

    /**
     * @param $column
     * @param $datas
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
            $like = ' NOT' . $like;
        }


        return $this->where([$column, $like, $type]);
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
            $like = ' NOT' . $like;
        }

        return $this->orWhere([$column, $like, $type]);
    }

    /**
     * @param $bool
     * @return $this
     */
    public function prepareValues($bool)
    {
        $this->driver->prepareValues = $bool;
        return $this;
    }

    /**
     * @param string $column
     * @param array|callable|string $datas
     * @param bool $not
     * @return Model
     */
    public function in($column, $datas, $not = false)
    {
        $in = ' IN ';

        if ($not) {
            $in = ' NOT' . $in;
        }

        return $this->where([$column, $in, $datas], null, null, 'AND', false);
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
            $in = ' NOT' . $in;
        }

        return $this->orWhere([$column, $in, $datas], null, null, 'OR', false);
    }


    /**
     * @param $join
     * @return Model
     */
    public function join($table, array $columns = [], $type = 'INNER JOIN')
    {
        $indexs = array_keys($columns);
        $values = array_values($columns);
        $this->join[] = [$type, $table, $values[0], $indexs[0]];
        return $this;
    }

    /**
     * @param $a
     * @param null $b
     * @param null $c
     * @param string $type
     * @param bool $clean
     * @return Model
     */
    public function where($a, $b = null, $c = null, $type = 'AND', $clean = true)
    {

        if (is_null($b) && is_null($c)) {

            $field = $a[0];
            $backet = $a[1];
            $query = $a[2];

        } elseif (is_null($c)) {
            $field = $a;
            $backet = '=';
            $query = $b;
        } else {
            $field = $a;
            $backet = $b;
            $query = $c;
        }

        $where = new Where();

        $where->field = $field;
        $where->backet = $backet;
        $where->clean = $clean;
        $where->query = $query;
        $where->type = $type;

        $this->where[] = $where;

        return $this;
    }

    /**
     * @param $a
     * @param null $b
     * @param null $c
     * @param bool $clean
     * @return Model
     */
    public function orWhere($a, $b = null, $c = null, $clean = false)
    {
        return $this->where($a, $b, $c, 'OR', $clean);
    }


    /**
     * Set verisi oluşturur
     *
     * @param mixed $set
     * @return array
     */
    private function databaseSetBuilder($set)
    {
        $s = '';
        $arr = [];
        foreach ($set as $key => $value) {
            $s .= "$key = ?,";
            $arr[] = $value;
        }
        return [
            'content' => rtrim($s, ","),
            'args' => $arr,
        ];
    }


    /**
     * @return string
     */
    public function getSelect()
    {
        return $this->select;
    }

    /**
     * @param select $select
     * @return Model
     */
    public function setSelect($select)
    {
        $this->select = $select;
        return $this;
    }

    /**
     * @return string
     */
    public function getTable()
    {
        return $this->table;
    }

    /**
     * @param string $table
     * @return Model
     */
    public function setTable($table)
    {
        $this->table = $table;
        return $this;
    }

    /**
     * @return array
     */
    public function getLimit()
    {
        return $this->limit;
    }

    /**
     * @param array $limit
     * @return Model
     */
    public function setLimit($limit)
    {
        $this->limit = $limit;
        return $this;
    }

    /**
     * @return string
     */
    public function getGroupBy()
    {
        return $this->groupBy;
    }

    /**
     * @param string $groupBy
     * @return Model
     */
    public function setGroupBy($groupBy)
    {
        $this->groupBy = $groupBy;
        return $this;
    }

    /**
     * @return array
     */
    public function getWhere()
    {
        return $this->where;
    }

    /**
     * @param array $where
     * @return Model
     */
    public function setWhere($where)
    {
        $this->where = $where;
        return $this;
    }

    /**
     * @return string
     */
    public function getHaving()
    {
        return $this->having;
    }

    /**
     * @param string $having
     * @return Model
     */
    public function setHaving($having)
    {
        $this->having = $having;
        return $this;
    }

    /**
     * @param array $order
     * @return Model
     */
    public function setOrder($order)
    {
        $this->order = $order;
        return $this;
    }

    /**
     * @return array
     */
    public function getJoin()
    {
        return $this->join;
    }

    /**
     * @param array $join
     * @return QueryBuilder
     */
    public function setJoin($join)
    {
        $this->join = $join;
        return $this;
    }

    /**
     * @return array
     */
    public function getOrWhere()
    {
        return $this->orWhere;
    }

    /**
     * @param array $orWhere
     * @return QueryBuilder
     */
    public function setOrWhere($orWhere)
    {
        $this->orWhere = $orWhere;
        return $this;
    }

    /**
     * @return array
     */
    public function getArgs()
    {
        return $this->args;
    }

    /**
     * @param array $args
     * @return Model
     */
    public function setArgs($args)
    {
        $this->args = $args;
        return $this;
    }

    /**
     * @return string
     */
    public function getAs()
    {
        return $this->as;
    }

    /**
     * @param string $as
     * @return QueryBuilder
     */
    public function setAs($as)
    {
        $this->as = $as;
        return $this;
    }

    public function hasAs()
    {
        return !empty($this->as);
    }

    /**
     * @return array
     */
    public function getOrder()
    {
        return $this->order;
    }

    /**
     * @return array
     */
    public function __sleep()
    {
        return ['driver'];
    }

}