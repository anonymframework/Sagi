<?php

/**
 * Class QueryBuilder
 */
class QueryBuilder implements Iterator
{

    /**
     * @var array
     */
    private $configs;

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
    private $table;

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
     * full query string
     *
     * @var string
     */
    private $query;
    /**
     * @var array
     */
    private $args = [];

    /**
     * @var QueryBuilder
     */
    public static $instance;

    /**
     * @var array
     */
    public $relations;

    /**
     * @var bool
     */
    private $prepareValues = true;


    /**
     * @var array
     */
    public $attr;

    /**
     * @var string
     */
    private $as;

    /**
     * QueryBuilder constructor.
     * @param array $configs
     * @param string $table
     * @throws Exception
     */
    public function __construct($configs = [], $table = null)
    {
        if ($configs instanceof PDO) {
            $this->pdo = $configs;
        } else {
            if (isset($configs['host']) && isset($configs['dbname']) && $configs['username'] && $configs['password']) {
                $this->setConfigs($configs);
                $this->startConnection();
            } else {
                throw new Exception('We need to your host,dbname,username and password informations for make a successfull connection ');
            }

        }

        $this->setTable($table);

        static::$instance = $this;
    }


    /**
     * start the mysql connection
     */
    public function startConnection()
    {
        $configs = $this->getConfigs();

        try {
            $driver = isset($configs['driver']) ? $configs['driver'] : 'mysql';

            $this->pdo = new PDO("$driver:host={$configs['host']};dbname={$configs['dbname']}", $configs['username'], $configs['password']);
            $this->pdo->query(sprintf("SET CHARACTER SET %s", isset($configs['charset']) ? $configs['charset'] : 'utf-8'));
        } catch (PDOException $p) {
            throw new PDOException("Something went wrong, message: " . $p->getMessage());
        }
    }


    /**
     * @param $query
     * @return PDOStatement
     */
    private function returnPreparedResults($query)
    {
        $query = trim($query);
        $prepared = $this->pdo->prepare($query);
        $this->args = array_slice($this->args, 0, count($this->where));
        $prepared->execute($this->args);
        return $prepared;

    }

    /**
     * @return $this
     */
    public function one()
    {
        $attrs = $this->fetch();

        $this->attr[0] = $attrs;
        return $this;
    }

    /**
     * @param int $id
     * @return QueryBuilder
     */
    public function find($id)
    {
        return $this->where(['id' => $id])->one();
    }

    /**
     * @return array
     */
    public function all()
    {
        if (empty($this->attr)) {
            $this->fetchAll();
        }

        return $this->attr;
    }

    /**
     * @return mixed
     */
    public function first()
    {
        if (isset($this->attr[0]) === false) {
            $this->one();
        }

        return $this->attr[0];
    }

    /**
     * @return array
     */
    public function attrs()
    {
        return $this->attr;
    }


    /**
     * @param $table
     * @return static
     */
    public function newInstance($table)
    {
        return new static($this->pdo, $table);
    }

    /**
     * @return mixed
     */
    public function fetch()
    {
        $get = $this->get();

        return $get->fetchObject('Results', ['table' => $this->getTable(), 'database' => $this]);
    }

    /**
     * @return array
     */
    public function fetchAll()
    {
        return $this->get()->fetchAll(PDO::FETCH_CLASS, 'Results', ['table' => $this->getTable(), 'database' => $this]);
    }

    /**
     * @return mixeds
     */
    public function prepareGetQuery()
    {
        $pattern = 'SELECT :select FROM :from :join :group :having :where :order :limit';

        $handled = $this->handlePattern($pattern, [
            ':select' => $this->prepareSelectQuery(),
            ':from' => $this->getTable(),
            ':join' => $this->prepareJoinQuery(),
            ':group' => $this->prepareGroupQuery(),
            ':having' => $this->prepareHavingQuery(),
            ':where' => $this->prepareWhereQuery(),
            ':order' => $this->prepareOrderQuery(),
            ':limit' => $this->prepareLimitQuery()
        ]);

        return $handled;
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
        $pattern = 'DELETE FROM :from :where';

        $handled = $this->handlePattern($pattern, array(
            ':from' => $this->getTable(),
            ':where' => $this->prepareWhereQuery()
        ));

        return $this->returnPreparedResults($handled);
    }

    /**
     * @param array $sets
     * @return PDOStatement
     */
    public function update($sets = [])
    {
        $pattern = 'UPDATE :from SET :update :where';

        $setted = $this->databaseSetBuilder($sets);
        $this->args = array_merge($this->args, $setted['args']);

        $handled = $this->handlePattern($pattern, [
            ':from' => $this->getTable(),
            ':update' => $setted['content'],
            ':where' => $this->prepareWhereQuery()
        ]);

        return $this->returnPreparedResults($handled);
    }

    /**
     * @param $sets
     * @return PDOStatement
     */
    public function create($sets)
    {
        $pattern = 'INSERT INTO :from SET :insert';

        $setted = $this->databaseSetBuilder($sets);
        $this->args = array_merge($this->args, $setted['args']);

        $handled = $this->handlePattern($pattern, [
            ':from' => $this->getTable(),
            ':insert' => $setted['content'],
        ]);


        return $this->returnPreparedResults($handled);
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
     * @param $callback
     * @return string
     */
    private function prepareSubQuery($callback)
    {
        /**
         * @var $builder QueryBuilder
         */
        $builder = call_user_func_array($callback, [$this->newInstance($this->table)]);

        $query = '(' . $builder->prepareGetQuery() . ')';

        if ($builder->hasAs()) {
            $query .= ' AS ' . $builder->getAs();
        }

        $this->setArgs(array_merge($this->getArgs(), $builder->getArgs()));

        return $query;
    }

    /**
     * @return mixeds|string
     */
    private function prepareInQuery($datas)
    {
        $inQuery = '';
        if (is_array($datas)) {
            $inQuery = '[' . implode(',', $datas) . ']';
        } elseif (is_callable($datas)) {
            $inQuery = $this->prepareSubQuery($datas);
        } else {
            $inQuery = '['.$datas.']';
        }

        return $inQuery;
    }

    /**
     * @param $bool
     * @return $this
     */
    public function prepareValues($bool)
    {
        $this->prepareValues = $bool;
        return $this;
    }

    /**
     * @param string $column
     * @param array|callable|string $datas
     * @return QueryBuilder
     */
    public function in($column, $datas, $not = false)
    {
        $query = $this->prepareInQuery($datas);

        $in = ' IN ';

        if ($not) {
            $in = ' NOT' . $in;
        }

        return $this->where([$column, $in, $query], null, null, true);
    }

    /**
     * @param string $column
     * @param array|callable|string $datas
     * @return QueryBuilder
     */
    public function notIn($column, $datas)
    {
        return $this->in($column, $datas, true);
    }

    /**
     * @param string $column
     * @param array|callable|string $datas
     * @return QueryBuilder
     */
    public function orNotIn($column, $datas)
    {
        return $this->orIn($column, $datas, true);
    }


    /**
     * @param string $column
     * @param array|callable|string $datas
     * @return QueryBuilder
     */
    public function orIn($column, $datas, $not = false)
    {
        $query = $this->prepareInQuery($datas);

        $in = ' IN ';

        if ($not) {
            $in = ' NOT' . $in;
        }

        return $this->orWhere([$column, $in, $query], null, null, true);
    }


    /**
     * @param $join
     * @return QueryBuilder
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
     * @param bool $prepare
     * @return $this
     */
    public function where($a, $b = null, $c = null, $prepare = false)
    {
        if (is_null($b) && is_null($c)) {
            $a[] = 'AND';

            if ($prepare) {
                $a[] = true;
            }

            $this->where[] = $a;
        } elseif (is_null($c)) {
            $this->where[] = [$a, '=', $b, 'AND'];
        } else {
            $this->where[] = [$a, $b, $c, 'AND'];
        }

        return $this;
    }

    /**
     * @param $a
     * @param null $b
     * @param null $c
     * @return $this
     */
    public function orWhere($a, $b = null, $c = null, $prepare = false)
    {
        if (is_null($b) && is_null($c)) {
            $a[] = 'OR';

            if ($prepare) {
                $a[] = true;
            }


            $this->where[] = $a;
        } elseif (is_null($c)) {
            $this->where[] = [$a, '=', $b, 'OR'];
        } else {
            $this->where[] = [$a, $b, $c, 'OR'];
        }

        return $this;
    }

    /**
     * @return string
     */
    private function prepareLimitQuery()
    {
        $limit = $this->getLimit();

        if (empty($limit)) {
            return "";
        }

        $s = "LIMIT $limit[0] ";

        if (isset($limit[1])) {
            $s .= 'OFFSET ' . $limit[1];
        }

        return $s;
    }

    private function prepareOrderQuery()
    {
        $order = $this->getOrder();

        if (empty($order)) {
            return "";
        }

        $id = isset($order[0]) ? $order[0] : 'id';
        $type = isset($order[1]) ? $order[1] : "DESC";

        return "ORDER BY {$id} {$type}";
    }

    /**
     * @return string
     */
    private function prepareSelectQuery()
    {
        $select = $this->getSelect();

        if (empty($select)) {
            $select = ["*"];
        }

        $app = $this;

        $select = array_map(function ($value) use ($app) {
            if (is_callable($value)) {
                $value = $app->prepareSubQuery($value);
            }

            return $value;
        }, $select);

        return (join(",", $select));
    }

    /**
     * @return string
     */
    private function prepareGroupQuery()
    {
        $group = $this->getGroupBy();

        if (empty($group)) {
            return "";
        }

        return "GROUP BY $group";
    }

    private function prepareHavingQuery()
    {
        $having = $this->getHaving();

        return $having;
    }

    /**
     * @return string
     */
    private function prepareJoinQuery()
    {
        $joins = $this->getJoin();

        if (empty($joins)) {
            return '';
        }

        $string = '';

        foreach ($joins as $join) {
            $type = isset($join[0]) ? $join[0] : 'LEFT JOIN';
            $targetTable = isset($join[1]) ? $join[1] : '';
            $targetColumn = isset($join[2]) ? $join[2] : '';
            $ourTable = $this->getTable();
            $ourColumn = isset($join[3]) ? $join[3] : '';
            $string .= "$type $targetTable ON $ourTable.$ourColumn = $targetTable.$targetColumn";
        }
        return $string;
    }

    private function prepareWhereQuery()
    {
        $where = $this->getWhere();

        $string = '';
        if (!empty($where)) {
            $string .= $this->prepareAllWhereQueries();
        }


        if ($string !== '') {
            $string = 'WHERE ' . $string;
        }

        return $string;
    }

    /**
     * @return string
     */
    private function prepareAllWhereQueries()
    {
        $where = $this->getWhere();

        $args = [];
        $s = '';
        foreach ($where as $item) {

            if (isset($item[4]) && $item[4] === true || $this->prepareValues === false) {
                $query = $item[2];
            } else {
                $query = '?';
                $args[] = $item[2];
            }

            if ($s !== '') {
                $s .= "$item[3] {$item[0]} {$item[1]} $query ";
            } else {
                $s .= "{$item[0]} {$item[1]} $query ";
            }
        }


        $s = rtrim($s, $item[3]);

        $this->args = array_merge($this->args, $args);

        return $s;
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
     * @return array
     */
    public function getConfigs()
    {
        return $this->configs;
    }

    /**
     * @param array $configs
     * @return QueryBuilder
     */
    public function setConfigs($configs)
    {
        $this->configs = $configs;
        return $this;
    }


    /**
     * @return select
     */
    public function getSelect()
    {
        return $this->select;
    }

    /**
     * @param select $select
     * @return QueryBuilder
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
     * @return QueryBuilder
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
     * @return QueryBuilder
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
     * @return QueryBuilder
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
     * @return QueryBuilder
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
     */
    public function setHaving($having)
    {
        $this->having = $having;
        return $this;
    }


    /**
     * @return string
     */
    public function getQuery()
    {
        return $this->query;
    }

    /**
     * @param string $query
     * @return QueryBuilder
     */
    public function setQuery($query)
    {
        $this->query = $query;
        return $this;
    }

    /**
     * @return array
     */
    public function getOrder()
    {
        return $this->order;
    }

    /**
     * @param array $order
     * @return QueryBuilder
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
     * @param $name
     * @param array $columns
     * @return $this
     */
    public function relation($prop, array $columns = [])
    {
        if (is_array($prop)) {
            $alias = $prop[0];
            $name = $prop[1];
        } else {
            $alias = $name = $prop;
        }
        $columns['table'] = $name;
        RelationBag::$relations[$alias] = [
            'propeties' => $columns];

        return $this;
    }

    /**
     * @param $name
     * @return mixed
     */
    public function __get($name)
    {
        return $this->first()->$name;
    }

    public function __set($name, $value)
    {
        $this->first()->$name = $value;
    }

    public function rewind()
    {
        reset($this->attr);
    }

    public function current()
    {
        $var = current($this->attr);
        return $var;
    }

    public function key()
    {
        $var = key($this->attr);
        echo "key: $var\n";
        return $var;
    }

    public function next()
    {
        $var = next($this->attr);
        return $var;
    }

    public function valid()
    {
        $key = key($this->attr);
        $var = ($key !== NULL && $key !== FALSE);
        return $var;
    }

    /**
     * @param $name
     * @param $arguments
     * @return mixed
     */
    public static function __callStatic($name, $arguments)
    {
        return call_user_func_array([static::$instance, $name], $arguments);
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
     * @return QueryBuilder
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


}