<?php

class QueryBuilder
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
            $this->pdo = new PDO("mysql:host={$configs['host']};dbname={$configs['dbname']}", $configs['username'], $configs['password']);
            $this->pdo->query(sprintf("SET CHARACTER SET %s", isset($configs['charset']) ? $configs['charset'] : 'utf-8'));
        } catch (PDOException $p) {
            throw new PDOException("Something went wrong, message: " . $p->getMessage());
        }
    }


    private function returnPreparedResults($query)
    {
        $query = trim($query);
        $prepared = $this->pdo->prepare($query);
        $this->args = array_slice($this->args, 0, count($this->where));
        $prepared->execute($this->args);
        return $prepared;

    }

    public function newInstance($table)
    {
        return new static($this->configs, $table);
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
     * @return PDOStatement
     */
    public function get()
    {
        $pattern = 'SELECT :select FROM :from :join :group :where :order :limit';

        $handled = $this->handlePattern($pattern, [
            ':select' => $this->prepareSelectQuery(),
            ':from' => $this->getTable(),
            ':join' => $this->prepareJoinQuery(),
            ':group' => $this->prepareGroupQuery(),
            ':where' => $this->prepareWhereQuery(),
            ':order' => $this->prepareOrderQuery(),
            ':limit' => $this->prepareLimitQuery()
        ]);

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

        return $pattern;
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
            if (strpos($value, '.') === false) {
                return $table . '.' . $value;
            }
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

    public function where($a, $b = null, $c = null)
    {
        if (is_null($b) && is_null($c)) {
            $a[] = 'AND';
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
    public function orWhere($a, $b = null, $c = null)
    {
        if (is_null($b) && is_null($c)) {
            $a[] = 'OR';
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
            if ($s !== '') {
                $s .= "$item[3] {$item[0]} {$item[1]} ? ";
            } else {
                $s .= "{$item[0]} {$item[1]} ?  ";
            }
            $args[] = $item[2];
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
     * @param $relations
     * @return $this
     */
    public function relations($relations)
    {
        RelationBag::setRelations($relations);

        return $this;
    }


}