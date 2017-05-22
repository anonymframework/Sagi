<?php
namespace Sagi\Database;

use Exception;
use Sagi\Database\Builder\Grammers\GrammerInterface;
use Sagi\Database\Exceptions\WhereException;
use Sagi\Database\Mapping\Entity;
use Sagi\Database\Mapping\Group;
use Sagi\Database\Mapping\Join;
use Sagi\Database\Mapping\Match;
use Sagi\Database\Mapping\SubWhere;
use Sagi\Database\Mapping\Where;
use PDO;

/**
 * Class QueryBuilder
 */
class QueryBuilder extends Builder
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
     * @var GrammerInterface
     */
    private $grammer;

    public function __construct()
    {
        $this->grammer = $this->prepareDefaultGrammer();
    }

    /**
     * @return GrammerInterface
     */
    private function prepareDefaultGrammer()
    {
        $configs = ConfigManager::returnDefaultConnection();

        $class = mb_convert_case($configs['driver'], MB_CASE_TITLE);

        $namespace = __NAMESPACE__ . '\Builder\Grammers\\' . $class . 'Grammer';

        return new $namespace;
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
        $pattern = $this->grammer->returnDeleteQuery();

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
        $pattern = $this->grammer->returnUpdateQuery();


        $setted = $this->databaseSetBuilder($sets);
        $this->setArgs(array_merge($this->getArgs(), $setted['args']));

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
        $pattern = $this->grammer->returnInsertQuery();

        $setted = $this->prepareInsertQuery($entity);

        $this->setArgs(array_merge($this->getArgs(), $setted['args']));

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
        $s = '(';

        $s .= implode(',',
            array_fill(0, $count, '?')
        );

        $s = rtrim($s, ',');

        $s .= ')';

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
    public function prepareGetQuery()
    {
        if ($this->getGroupBy() instanceof Group) {
            $this->grammer->setGroup(true);
        }

        $pattern = $this->grammer->returnGetQuery();

        $handled = $this->handlePattern($pattern, [
            ':select' => $this->prepareSelectQuery($this->getSelect()),
            ':from' => $this->getTable(),
            ':join' => $this->prepareJoinQuery($this->getJoin(), $this->getTable()),
            ':group' => $this->compileGroupQuery(),
            ':having' => $this->prepareHavingQuery(),
            ':where' => $this->prepareWhereQuery(),
            ':order' => $this->compileOrderQuery(),
            ':limit' => $this->compileLimitQuery()
        ]);

        return $handled;
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

        return implode(' ', $exploded);
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
        $select = array_map(function ($value) {
            if (is_string($value)) {
                $value = trim($value);
            }

            return $value;
        }, $select);


        return $this->setSelect($select);
    }

    /**
     *
     * @param string $table
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
     * @param $limit
     * @return $this
     */
    public function limit($limit)
    {
        if (is_string($limit) || is_numeric($limit)) {
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
        $multipile = false;

        if (!is_array($group) && is_string($group)) {
            $group = explode(',', $group);
        }

        if (count($group) > 1) {
            $multipile = true;
        }

        $this->setGroupBy(
            new Group($group, $multipile)
        );

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
    public function join($table, $localKey, $backet, $foreignKey, $type = 'INNER JOIN')
    {
        $this->addJoin(new Join($type, $table, $foreignKey, $localKey, $backet));

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
     * @param Raw|callable $where
     * @param string $type
     * @return $this
     */
    public function subWhere($where, $type = 'AND')
    {
        $subWhere = new SubWhere($where, $type);

        $this->where[] = $subWhere;

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
     * @param string $backet
     * @param string $value
     * @param string $type
     * @return $this
     */
    public function where($field, $backet, $value, $type = 'AND')
    {
        $where = $this->prepareWhereInstance(
            compact('field', 'backet', 'value', 'type')
        );

        $mark = $this->prepareWhereMark($backet, $type, $field);
        $this->where[$mark] = $where;

        return $this;
    }

    /**
     * @param $field
     * @param $backet
     * @param $value
     * @return QueryBuilder
     */
    public function orWhere($field, $backet, $value)
    {
        return $this->where($field, $backet, $value, 'OR');
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
        if (isset($this->marks[$mark])) {
            $mark = $this->marks[$mark];
            $name = $field . '.' . $type . '.' . $mark;

            return $name;
        } else {
            throw new WhereException(sprintf('%s could not found, you can use one of these(%s)', $mark, $this->join(',', $this->marks)));
        }
    }

    /**
     * @param $datas
     * @return Where
     */
    private function prepareWhereInstance($datas)
    {
        $where = new Where();

        $where->field = $datas['field'];
        $where->backet = $datas['backet'];
        $where->query = $datas['value'];

        $where->type = $datas['type'];

        return $where;
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
            'content' => rtrim($s, ','),
            'args' => $arr,
        ];
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

        if ($exed === false) {
            $this->error = $prepared->errorInfo();
        }

        return $execute ? $exed : $prepared;
    }


    /**
     * @param $query
     * @param array $args
     * @return \PDOStatement
     */
    public function query($query,array $args = [])
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
        return $this->returnPreparedResults(
            $this->prepareGetQuery()
        );
    }

    /**
     * @return bool|\PDOStatement
     */
    public function delete()
    {
        return $this->returnPreparedResults(
            $this->prepareDelete(), true
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


        return $this->returnPreparedResults(
            $this->prepareUpdate($datas),
            true);
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
}
