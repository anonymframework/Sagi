<?php
/**
 * Created by PhpStorm.
 * User: My
 * Date: 04/27/2017
 * Time: 17:33
 */

namespace Sagi\Database;


use Sagi\Database\Builder\SubQuery;
use Sagi\Database\Builder\WhereBuilder;
use Sagi\Database\Exceptions\ErrorException;
use Sagi\Database\Exceptions\WhereException;
use Sagi\Database\Mapping\Join;
use Sagi\Database\Mapping\Raw;
use Sagi\Database\Mapping\SubWhere;
use Sagi\Database\Mapping\Where;

class Builder
{
    /**
     * @var select query
     */
    private $select;

    /**
     * @var string
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

    /**
     * @var array
     */
    protected static $operators = [
        '=' => 'equal',
        '>' => 'bigger',
        '<' => 'smaller',
        '!=' => 'diffrent',
        '>=' => 'ebigger',
        '=<' => 'esmaller',
        'IN' => 'in',
        'NOT IN' => 'notin',
    ];

    /**
     * @var GrammerInterface
     */
    private $grammer;

    public function __construct($table)
    {
        $this->setTable($table);

        $this->grammer = $this->handleDefaultGrammer();
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
     * @return Builder
     */
    public function setTable($table)
    {
        $this->table = $table;

        return $this;
    }


    /**
     * @return GrammerInterface
     */
    private function handleDefaultGrammer()
    {
        $configs = ConfigManager::returnDefaultConnection();

        $class = mb_convert_case($configs['driver'], MB_CASE_TITLE);

        $namespace = __NAMESPACE__.'\Builder\Grammers\\'.$class.'Grammer';

        return new $namespace;
    }

    /**
     * @return mixed
     */
    public function handleDelete()
    {
        $pattern = $this->grammer->getDeleteQuery();

        $handled = $this->handlePattern(
            $pattern,
            array(
                ':from' => $this->getTable(),
                ':where' => $this->handleWhereQuery(),
            )
        );

        return $handled;
    }

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
    public function handleGetQuery()
    {
        if ($this->getGroupBy() instanceof Group) {
            $this->grammer->setGroup(true);
        }

        $pattern = $this->grammer->getReadQuery();

        $handled = $this->handlePattern(
            $pattern,
            [
                ':select' => $this->handleSelectQuery($this->getSelect()),
                ':from' => $this->handleFromQuery(),
                ':join' => $this->handleJoinQuery($this->getJoin(), $this->getTable()),
                ':group' => $this->compileGroupQuery(),
                ':having' => $this->handleHavingQuery(),
                ':where' => $this->buildWhereQuery(),
                ':order' => $this->compileOrderQuery(),
                ':limit' => $this->compileLimitQuery(),
            ]
        );

        return $handled;
    }

    private function handleFromQuery()
    {
        $table = $this->getTable();

        if ( ! is_callable($table)) {
            return $table;
        } else {
            list($query, $args) = $this->handleSubQuery($table, new QueryBuilder());
        }

        $this->setArgs(
            array_merge(
                $this->getArgs(),
                $args
            )
        );




        return $query;
    }

    /**
     * @return string
     */
    protected function compileLimitQuery()
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
     * @return string
     */
    protected function compileOrderQuery()
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
    protected function compileGroupQuery()
    {
        $group = $this->groupBy;

        if (null === $group) {
            return '';
        }

        $group = implode(',', $group->group);

        return "GROUP BY $group";
    }

    /**
     * @return mixed
     */
    protected function handleHavingQuery()
    {
        return $this->having;
    }

    /**
     * @param $where
     * @return mixed|string
     */
    protected function handleWhereQuery(array $where)
    {
        $s = '';
        $first = array_values($where)[0]->type;

        foreach ($where as $item) {
            $s .= $this->determineWhereType($item);
        }

        $s = ltrim($s, " $first");

        return $s;
    }

    /**
     * @param $item
     * @return mixed
     */
    private function determineWhereType($item)
    {
        if ($item instanceof Where) {
            $s = $this->buildStandartWhereQuery($item);
        } elseif ($item instanceof Match) {
            $s = $this->buildMatchWhere($item);
        } elseif ($item instanceof SubWhere) {
            $s = $this->buildSubWhere($item);
        } else {
            throw new WhereException(sprintf('Wrong where query'));
        }

        return $s;
    }

    /**
     * @param SubWhere $item
     * @return string
     * @throws ErrorException
     */
    private function buildSubWhere(SubWhere $item)
    {
        if ( ! is_callable($item->getQuery())) {
            return $item->getQuery();
        }

        $instance = new QueryBuilder();
        $callback = $item->getQuery();
        $returned = $callback($instance);

        if ( ! $returned instanceof QueryBuilder) {
            throw new ErrorException(
                sprintf(
                    'your callback must return have an %s intance',
                    QueryBuilder::getClassName()
                )
            );
        }

        $builder = $returned->getBuilder();
        $where = $builder->getWhere();

        /**
         * @var QueryBuilder $returned
         */
        if (empty($where)) {
            throw new WhereException('You did not make any where query');
        }

        $query = sprintf(
            ' %s (%s)',
            $item->type,
            $builder->handleWhereQuery($where)
        );

        $this->setArgs(
            array_merge($this->getArgs(), $builder->getArgs())
        );

        return $query;
    }

    /**
     * @param Match $item
     * @return string
     */
    private function buildMatchWhere(Match $item)
    {
        $columns = array_map(
            function ($column) {
                return "`$column`";
            }
        );
        $columns = implode(',', $columns);

        $values = array_map(
            [$this, 'quote'],
            $item->values
        );

        $type = $item->type;

        $s = sprintf(
            '%s MATCH(%s) AGAINST(%s IN %s) ',
            $type,
            $columns,
            implode(',', $values),
            $item->mode
        );

        return $s;
    }

    /**
     * @param Where $item
     * @return string
     */
    private function handleStandartQuery(Where $item)
    {
        $type = $item->type;
        $this->args[] = $item->query;

        $query = $this->cleanIsNeeded($item->query);


        return sprintf(
            ' %s %s %s %s',
            $type,
            $item->field,
            $item->operator,
            $query
        );
    }

    /**
     * @param $query
     * @return string
     */
    private function cleanIsNeeded($query)
    {
        if ( ! $query instanceof Raw && false === is_array($query) && ! is_callable($query)) {
            $query = '?';
        }

        if ($query instanceof Raw) {
            $query = $query->getQuery();
        }

        return $query;
    }

    /**
     * @param Where $item
     * @param $args
     * @return string
     */
    private function buildStandartWhereQuery(Where $item)
    {

        if (is_array($item->query) || is_callable($item->query)) {
            $handledQuery = $this->handleInQuery($item);
        } else {
            $handledQuery = $this->handleStandartQuery($item);
        }

        return $handledQuery;
    }

    /**
     * @param $item
     * @return string
     */
    private function handleInQuery($item)
    {
        $datas = $item->query;

        if (is_array($datas)) {
            $query = $this->buildInArrayQuery($datas);
        } else {
            list($query, $args) = $this->handleSubQuery($datas, new QueryBuilder());

            $this->setArgs(
                array_merge($this->getArgs(), $args)
            );
        }

        return sprintf(' %s %s IN (%s)', $item->type, $item->field, $query);
    }

    /**
     * @param array $datas
     * @return string
     */
    private function buildInArrayQuery(array $datas)
    {
        $this->setArgs(
            array_merge($this->getArgs(), $datas)
        );

        return '['.
            implode(',', array_fill(0, count($datas), '?'))
            .']';
    }

    /**
     * @param $callback
     * @return array
     */
    public function handleSubQuery($callback, $instance)
    {
        /**
         *  returns a new subquery
         */
        $builder = new SubQuery($callback, $instance);

        return $builder->build();
    }


    /**
     * @param Entity $sets
     * @return mixed
     */
    public function handleUpdate(Entity $sets)
    {
        $pattern = $this->grammer->getUpdateQuery();
        $setted = $this->databaseSetBuilder($sets);
        $this->setArgs(
            array_merge($this->getArgs(), $setted['args'])
        );

        $handled = $this->handlePattern(
            $pattern,
            [
                ':from' => $this->getTable(),
                ':update' => $setted['content'],
                ':where' => $this->buildWhereQuery(),
            ]
        );

        return $handled;
    }


    /**
     * @param Entity $entity
     * @return PDOStatement
     */
    public function handleCreate(Entity $entity)
    {
        $pattern = $this->grammer->getInsertQuery();

        $setted = $this->handleInsertQuery(
            $entity
        );

        $this->setArgs(
            array_merge($this->getArgs(), $setted['args'])
        );
        $handled = $this->handlePattern(
            $pattern,
            [
                ':from' => $this->getTable(),
                ':insert' => $setted['content'],
            ]
        );

        return $handled;
    }

    /**
     * @param Entity $entity
     * @return array
     */
    protected function handleInsertQuery(Entity $entity)
    {
        $count = count($entity->datas);
        $keys = array_keys($entity->datas);

        $s = '('.rtrim(
                implode($keys, ','),
                ','
            ).') VALUES ';

        $s .= $this->handleInsertValue($count);
        $args = array_values($entity->datas);


        return ['args' => $args, 'content' => $s];
    }



    /**
     * @param string $pattern
     * @param array $args
     * @return mixeds
     */
    private function handlePattern($pattern, array $args)
    {
        foreach ($args as $key => $arg) {
            $pattern = str_replace($key, $arg, $pattern);
        }

        $exploded = array_filter(
            explode(' ', $pattern),
            function ($value) {
                return ! empty($value);
            }
        );

        return implode(' ', $exploded);
    }


    /**
     * @return string        $app = Singleton::load(get_called_class());
     */
    protected function handleJoinQuery()
    {
        $joins = $this->join;
        $table = $this->getTable();

        if (empty($joins)) {
            return '';
        }

        $string = '';

        $new = Singleton::load(get_called_class());

        $builder = new WhereBuilder($this, $this->getArgs());
        foreach ($joins as $join) {
            /**
             * @var Join $join
             */

            if (is_callable($join->target)) {

                $builder->setİnstance($new)->setArgs($this->getArgs());

                $handleCol = $this->handleSubQuery($join->target, $new);


                $tCol = $handleCol[0];
                $this->setArgs($handleCol[1]);
            } else {
                $tCol = $join->table.'.'.$join->target;
            }


            $string .= sprintf(
                "%s %s ON %s.%s = %s ",
                $join->type,
                $join->table,
                $table,
                $join->home,
                $tCol
            );
        }

        return $string;
    }

    /**
     * @return string
     */
    protected function handleSelectQuery()
    {
        $select = $this->select;
        if (empty($select)) {
            $select = ['*'];
        }

        $builder = new QueryBuilder();
        $values = [];

        foreach ($select as $value) {
            if ($value instanceof Raw) {
                $value = $value->getQuery();
            }

            if (is_callable($value)) {
                $value = $builder->getBuilder()->handleSubQuery($value, $builder);

                $builder->getBuilder()->setArgs(
                    array_merge(
                        $builder->getBuilder()->getArgs(),
                        $value[1]
                    )
                );

                $value = $value[0];
            }

            $values[] = $value;
        }


        $this->setArgs($builder->getBuilder()->getArgs());

        $builder = null;

        return implode(',', $values);
    }


    /**
     * @return string
     */
    private function buildWhereQuery()
    {
        $where = $this->getWhere();

        if (empty($where)) {
            return '';
        }

        $string = 'WHERE ';


        $string .= $this->handleWhereQuery($where);

        return $string;
    }

    /**
     * @param Where|SubWhere $where
     * @param string|null $mark
     * @return $this
     */
    public function addWhere($where, $mark = null)
    {

        if (null === $mark) {
            $this->where[] = $where;
        } else {
            $this->where[$mark] = $where;
        }

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
     * @return Builder
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
     * @return Builder
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
     * @return Builder
     */
    public function setWhere($where)
    {
        $this->where = $where;

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
     * @return Builder
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
     * @return Builder
     */
    public function setJoin($join)
    {
        $this->join = $join;

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
     * @param Join $join
     * @return $this
     */
    public function addJoin(Join $join)
    {
        $this->join[] = $join;

        return $this;
    }

    /**
     * @param string $having
     * @return Builder
     */
    public function setHaving($having)
    {
        $this->having = $having;

        return $this;
    }


    public function hasAs()
    {
        return ! empty($this->as);
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
     * @return Builder
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
     * @return Builder
     */
    public function setAs($as)
    {
        $this->as = $as;

        return $this;
    }

    /**
     * @return array
     */
    public static function getOperators()
    {
        return self::$operators;
    }

    /**
     * @param array $operators
     */
    public static function setOperators($operators)
    {
        self::$operators = $operators;
    }

    /**
     * @return string
     */
    public static function getClassName()
    {
        return get_called_class();
    }

}

