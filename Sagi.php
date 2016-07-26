<?php

class Sagi
{

    /**
     * @var array
     */
    private $configs;

    /**
     * @var PDO
     */
    private $pdo;
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
     * Sagi constructor.
     * @param array $configs
     * @param string $table
     * @throws Exception
     */
    public function __construct(array $configs = [], $table = null)
    {
        if (isset($configs['host']) && isset($configs['dbname']) && $configs['username'] && $configs['password']) {
            $this->setConfigs($configs);
        } else {
            throw new Exception('We need to your host,dbname,username and password informations for make a successfull connection ');
        }

        $this->setTable($table);

        $this->startConnection();
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
        if ($prepared->execute($this->args)) {
            return $prepared;
        } else {
            throw new Exception('Your query has been failed, message:' . $this->pdo->errorInfo()[0]);
        }


    }

    /**
     * @return mixed
     */
    public function fetch()
    {
        $get = $this->get();

        return $get->fetchObject();
    }

    /**
     * @return array
     */
    public function fetchAll()
    {
        return $this->get()->fetchAll();
    }

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
     * @return Sagi
     */
    public function select($select = [])
    {
        if (is_string($select)) {
            $select = explode(",", $select);
        }

        return $this->setSelect($select);
    }

    /**
     * @param $column
     * @param string $type
     * @return Sagi
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
     * @return Sagi
     */
    public function group($group)
    {
        return $this->setGroupBy($group);
    }

    /**
     * @param $join
     * @return Sagi
     */
    public function join($join)
    {
        $this->join[] = $join;
        return $this;
    }

    public function where($a, $b = null, $c = null)
    {
        if (is_null($b) && is_null($c)) {
            $this->where[] = $a;
        } elseif (is_null($c)) {
            $this->where[] = [$a, '=', $b];
        } else {
            $this->where[] = [$a, $b, $c];
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
            $this->orWhere[] = $a;
        } elseif (is_null($c)) {
            $this->orWhere[] = [$a, '=', $b];
        } else {
            $this->orWhere[] = [$a, $b, $c];
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

        return "LIMIT " . join(",", $limit);
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
        $join = $this->getJoin();

        if (empty($join)) {
            return '';
        }

        $string = '';
        foreach ($join as $type => $value) {
            $string .= sprintf("%s %s ON %s.%s = %s.%s", $type, $value[0], $value[0], $value[1], $this->getTable(), $value[2]);
        }

        return $string;
    }

    private function prepareWhereQuery()
    {
        $where = $this->getWhere();
        $orWhere = $this->getOrWhere();

        $string = '';
        if (!empty($where)) {
            $string .= $this->prepareAndWhereQuery();
        }

        if (!empty($orWhere)) {
            $string .= $this->prepare0rWhereQuery();
        }

        if ($string !== '') {
            $string = 'WHERE ' . $string;
        }

        return $string;
    }

    /**
     * @return string
     */
    private function prepareAndWhereQuery()
    {
        $where = $this->getWhere();

        $prepared = $this->databaseStringBuilderWithStart($where, "AND");

        $this->args = array_merge($this->args, $prepared['args']);

        return $prepared['content'];
    }


    /**
     * @return string
     */
    private function prepare0rWhereQuery()
    {
        $where = $this->getOrWhere();


        $prepared = $this->databaseStringBuilderWithStart($where, "Or");

        $this->args = array_merge($this->args, $prepared['args']);

        return $prepared['content'];
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
     *
     * @param array $args
     * @param string $start
     * @return mixed
     */
    private function databaseStringBuilderWithStart(array $args, $start)
    {
        $s = '';
        $arr = [];
        foreach ($args as $arg) {
            $s .= " {$arg[0]} {$arg[1]} ? $start";
            $arr[] = $arg[2];
        }
        if (!count($args) === 1) {
            $s = $start . $s;
        }
        $s = rtrim($s, $start);
        return [
            'content' => $s,
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
     * @return Sagi
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
     * @return Sagi
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
     * @return Sagi
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
     * @return Sagi
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
     * @return Sagi
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
     * @return Sagi
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
     * @return Sagi
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
     * @return Sagi
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
     * @return Sagi
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
     * @return Sagi
     */
    public function setOrWhere($orWhere)
    {
        $this->orWhere = $orWhere;
        return $this;
    }


}