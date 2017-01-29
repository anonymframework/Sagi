<?php
namespace Sagi\Database;

use Exception;
use Sagi\Database\Mapping\Entity;
use Sagi\Database\Mapping\Group;
use Sagi\Database\Mapping\Join;
use Sagi\Database\Mapping\Match;
use Sagi\Database\Mapping\Where;
use PDO;

/**
 * Class QueryBuilder
 */
class QueryBuilder
{

    const SUBQUERY = 'sub';

    /**
     * @var string
     */
    protected $database;

    /**
     * @var array
     */
    protected $counters = [];

    /**
     * @var array
     */
    protected $error;
    /**
     * @var string
     */
    private $lastQueryString;

    /**
     * @var PDO
     */
    protected $pdo;
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
    protected $where = [];

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

    protected $marks = [
        '=' => 'equal',
        '>' => 'bigger',
        '<' => 'smaller',
        '!=' => 'diffrent',
        '>=' => 'ebigger',
        '=<' => 'esmaller',
        'IN' => 'in',
        'NOT IN' => 'notin',
        'sub' => 'sub',
    ];

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
        if (is_null($this->pdo)) {

            $connection = !empty($this->database) ? $this->database : null;

            $this->pdo = Connector::getConnection($connection);
        }

        return $this->pdo;
    }

    /**
     * @return mixed
     */
    protected function prepareDelete()
    {
        $pattern = 'DELETE FROM :from :where';

        $handled = $this->handlePattern($pattern, array(
            ':from' => $this->getTable(),
            ':where' => $this->prepareWhereQuery()
        ));

        return $handled;
    }

    /**
     * @param Entity $sets
     * @return mixed
     */
    protected function prepareUpdate(Entity $sets)
    {
        $pattern = 'UPDATE :from SET :update :where';


        $setted = $this->databaseSetBuilder($sets);
        $this->args = array_merge($this->args, $setted['args']);

        $handled = $this->handlePattern($pattern, [
            ':from' => $this->getTable(),
            ':update' => $setted['content'],
            ':where' => $this->prepareWhereQuery()
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

        if ($entity->multipile === false) {
            $keys = array_keys($entity->datas);
        } else {
            $keys = array_keys($entity->datas[0]);
        }

        foreach ($keys as $key => $value) {
            $s .= $value . ",";
        }

        $s = rtrim($s, ",");

        $s .= ") VALUES  ";

        $args = [];


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
    private function prepareLimitQuery()
    {
        $limit = $this->limit;

        if (empty($limit)) {
            return "";
        }

        if (isset($limit[1])) {
            $s = sprintf('LIMIT %d OFFSET %d', $limit[1], $limit[0]);
        } else {
            $s = sprintf('LIMIT %d', $limit[0]);
        }

        return $s;
    }

    /**
     * @param $name
     * @return $this
     */
    public static function table($name)
    {
        return static::createNewInstance($name);
    }

    /**
     * @return string
     */
    private function prepareOrderQuery()
    {
        $order = $this->order;

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
    private function prepareGroupQuery()
    {
        $group = $this->groupBy;

        if (is_null($group)) {
            return "";
        }

        $group = join(',', $group->group);

        return "GROUP BY $group";
    }

    /**
     * @return mixed
     */
    private function prepareHavingQuery()
    {
        return $this->having;
    }

    /**
     * @return string
     */
    public function prepareGetQuery()
    {

        $group = $this->groupBy;

        if ($group instanceof Group) {
            $pattern = 'SELECT :select FROM :from :join :where :group :having :order :limit';
        } else {
            $pattern = 'SELECT :select FROM :from :join :group :having :where :order :limit';
        }


        $handled = $this->handlePattern($pattern, [
            ':select' => $this->prepareSelectQuery($this->select),
            ':from' => $this->getTable(),
            ':join' => $this->prepareJoinQuery($this->join, $this->getTable()),
            ':group' => $this->prepareGroupQuery(),
            ':having' => $this->prepareHavingQuery(),
            ':where' => $this->prepareWhereQuery(),
            ':order' => $this->prepareOrderQuery(),
            ':limit' => $this->prepareLimitQuery()
        ]);

        return $handled;
    }

    /**
     * @return string
     */
    public function prepareCountQuery()
    {
        $pattern = 'SELECT :select FROM :from  :where :order :limit';


        $handled = $this->handlePattern($pattern, [
            ':select' => 'COUNT(*) as row_count',
            ':from' => $this->getTable(),
            ':where' => $this->prepareWhereQuery(),
            ':order' => $this->prepareOrderQuery(),
            ':limit' => $this->prepareLimitQuery()
        ]);
        return $handled;
    }

    protected function prepareWhereQuery()
    {
        $string = 'WHERE ';

        if (!empty($this->where)) {
            $prepared = $this->handleWhereQuery();

            if ($prepared !== '') {
                $string .= $prepared;
            }
        } else {
            $string = '';
        }

        return $string;
    }

    /**
     * @return string
     */
    private function handleWhereQuery()
    {

        $args = [];
        $s = '';

        foreach ($this->where as $item) {

            /**
             * @var Where $item
             */


            if ($item instanceof Where) {
                $backed = $item->backet;

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
                if ($s !== '') {
                    $s .= "$type {$field} $backed $query ";
                } else {
                    $s .= "$field $backed $query ";
                }

            } elseif ($item instanceof Match) {
                $columns = join(",", array_map(function ($column) {
                    return "`$column`";
                }, $item->columns));

                $values = array_map([$this->prepareConnection(), 'quote'], $item->values);

                $type = $item->type;

                if ($s !== '') {
                    $s .= "$type ";
                }

                $s .= sprintf('MATCH(%s) AGAINST(%s IN %s)', $columns, join(',', $values), $item->mode);
            } else {
                throw new WhereException(sprintf('Wrong where query'));
            }
        }

        $s = rtrim($s, $type);

        $this->args = array_merge($this->args, $args);

        return $s;
    }

    /**
     * @return string        $app = Singleton::load(get_called_class());
     */
    protected function prepareJoinQuery()
    {
        $joins = $this->join;
        $table = $this->getTable();

        if (empty($joins)) {
            return '';
        }

        $string = '';

        $new = Singleton::load(get_called_class());

        foreach ($joins as $join) {
            /**
             * @var Join $join
             */

            if (is_callable($join->target)) {
                $prepareCol = $this->prepareSubQuery($join->target, $new);

                $tCol = $prepareCol[0];
                $this->setArgs($prepareCol[1]);
            } else {
                $tCol = $join->table . '.' . $join->target;
            }


            $string .= sprintf("%s %s ON %s.%s = %s ",
                $join->type,
                $join->table,
                $table, $join->home, $tCol);
        }
        return $string;
    }

    /**
     * @return string
     */
    private function prepareSelectQuery()
    {
        $select = $this->select;

        if (empty($select)) {
            $select = ["*"];
        }

        $app = Singleton::load(get_called_class());

        $builder = &$app;

        $select = array_map(function ($value) use ($builder) {
            if (is_callable($value)) {
                $value = $builder->prepareSubQuery($value, $builder);

                $builder->setArgs(array_merge($value[1]));

                $value = $value[0];
            }

            return $value;
        }, $select);

        $this->setArgs($app->getArgs());

        $builder = null;
        $app = null;

        return implode(',', $select);
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

            $this->args = array_merge($this->args, $datas);
            $inQuery = '[' . implode(",", array_fill(0, count($datas), '?')) . ']';

        } elseif (is_callable($datas)) {

            $inQuery = $this->prepareSubQuery($datas, Singleton::load(get_called_class()));
        }

        return is_array($inQuery) ? $inQuery : [$inQuery, []];
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
            if (is_string($value)) {
                $value = trim($value);
            }

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
     * @return $this
     */
    public function order($column, $type = 'DESC')
    {
        $this->order = [$column, $type];

        return $this;
    }

    /**
     * @param $limit
     * @return $this
     */
    public function limit($limit)
    {
        if (is_string($limit) || is_numeric($limit)) {
            $this->limit = [$limit];
        } else {
            $this->limit = $limit;
        }

        return $this;
    }


    /**
     * @param $group
     * @return QueryBuilder
     */
    public function group($group)
    {
        $multipile = false;

        if (!is_array($group) && is_string($group)) {
            $group = explode(",", $group);
        }

        if (count($group) > 1) {
            $multipile = true;
        }

        $groupBy = new Group();

        $groupBy->group = $group;
        $groupBy->isMultipile = $multipile;

        $this->groupBy = $groupBy;

        return $this;
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

        $this->join[] = new Join($type, $table, $values[0], $indexs[0]);

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
     * @param $a
     * @param null $b
     * @param null $c
     * @return Model
     */
    public function cWhere($a, $b = null, $c = null)
    {
        return $this->where($a, $b, $c, 'AND', false);
    }

    /**
     * @param $a
     * @param null $b
     * @param null $c
     * @return Model
     */
    public function cOrWhere($a, $b = null, $c = null)
    {
        return $this->orWhere($a, $b, $c, 'OR', false);
    }


    /**
     * @param callable $callback
     * @return QueryBuilder
     */
    public function subWhere(callable $callback)
    {

    }


    /**
     * @param callable $callback
     * @return QueryBuilder
     */
    public function subOrWhere(callable $callback)
    {

    }

    /**
     * @param $columns
     * @param $values
     * @param string $mode
     * @return $this
     */
    public function match($columns, $values, $mode = 'BOOLEAN MODE', $type = 'AND')
    {
        $match = new Match($columns, $values, $mode, $type);

        $this->where[] = $match;

        return $this;
    }

    /**
     * @param $columns
     * @param $values
     * @param $mode
     * @return QueryBuilder
     */
    public function orMatch($columns, $values, $mode = 'BOOLEAN MODE')
    {
        return $this->match($columns, $values, $mode, 'OR');
    }

    /**
     * @param $a
     * @param null $b
     * @param null $c
     * @param string $type
     * @param bool $clean
     * @param bool $spec
     * @throws Exception
     * @return QueryBuilder
     */
    public function where($a, $b = null, $c = null, $type = 'AND', $clean = true, $spec = false)
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

        if (strpos($field, ".") === false && $type === static::SUBQUERY) {
            $field = $this->getTable() . '.' . $field;
        }

        $where->field = $field;
        $where->backet = $backet;
        $where->clean = $clean;
        $where->query = $query;
        $where->type = $type;

        $mark = trim($backet);

        if ($spec === true) {
            $this->where[$field] = $where;
        } elseif ($mark !== static::SUBQUERY) {
            if (isset($this->marks[$mark])) {
                $mark = $this->marks[$mark];
                $name = $field . '.' . $type . '.' . $mark;

                $this->where[$name] = $where;
            } else {
                throw new Exception(sprintf('%s could not found, you can use one of these(%s)', $mark, $this->join(',', $this->marks)));
            }
        } else {
            $this->where[] = $where;
        }

        return $this;
    }

    /**
     * @param $a
     * @param null $b
     * @param null $c
     * @param string $type
     * @param bool $clean
     * @return QueryBuilder
     */
    public function specWhere($a, $b = null, $c = null, $type = 'AND', $clean = true)
    {
        return $this->where($a, $b, $c, $type, $clean, true);
    }

    public function orSpecWhere($a, $b = null, $c = nulL, $clean = true)
    {
        return $this->orWhere($a, $b, $c, $clean, true);
    }

    /**
     * @param $a
     * @param null $b
     * @param null $c
     * @param bool $clean
     * @param bool $spec
     * @return QueryBuilder
     */
    public function orWhere($a, $b = null, $c = null, $clean = false, $spec = false)
    {
        return $this->where($a, $b, $c, 'OR', $clean, $spec);
    }


    /**
     * Set verisi oluşturur
     *
     * @param mixed $set
     * @return array
     */
    private function databaseSetBuilder(Entity $set)
    {
        $s = '';
        $arr = [];
        foreach ($set->datas as $key => $value) {

            if (!isset($this->counters[$key])) {
                $s .= "$key = ?,";
                $arr[] = $value;
            } else {
                $value = $this->counters[$value];

                $s = "$key = $key + $value";
            }

        }
        return [
            'content' => rtrim($s, ","),
            'args' => $arr,
        ];
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
     * @return $this
     */
    public function setTable($table)
    {
        $this->table = $table;
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
     *
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
    public function __sleep()
    {
        return ['driver', 'where', 'orWhere'];
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
        if (!$this->pdo instanceof PDO) {
            $this->prepareConnection();
        }


        $prepared = $this->pdo->prepare($query);

        $exed = $prepared->execute($args);

        $this->error = $prepared->errorInfo();

        return $execute ? $exed : $prepared;
    }


    /**
     * @param $query
     * @param array $args
     * @return \PDOStatement
     */
    public function query($query, $args = [])
    {
        return $this->prepare($query, $args);
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
     * @return bool|PDOStatement
     */
    public function update($datas = [])
    {
        if (is_array($datas)) {
            $datas = new Entity($datas);
        }


        return $this->returnPreparedResults($this->prepareUpdate($datas), true);
    }

    /**
     * @param array|Entity $data
     * @return PDOStatement
     */
    public function create($data = null)
    {
        if (is_array($data)) {
            $data = new Entity($data);
        }

        $create = $this->returnPreparedResults(
            $this->prepareCreate($data),
            true
        );

        $this->setArgs([]);

        return $create;
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

    public function all()
    {
        return $this->get()->fetchAll(PDO::FETCH_OBJ);
    }

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
}
