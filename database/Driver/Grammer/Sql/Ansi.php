<?php

namespace Sagi\Database\Driver\Grammer\Sql;


use Sagi\Database\Builder;
use Sagi\Database\Mapping\Entity;
use Sagi\Database\Mapping\Group;
use Sagi\Database\Mapping\Limit;
use Sagi\Database\Mapping\Match;
use Sagi\Database\Mapping\SubWhere;
use Sagi\Database\Mapping\Where;
use Sagi\Database\QueryBuilder;

class Ansi implements SqlReaderGrammerInterface
{

    protected $patterns = [
        'create' => /** @lang text */
            'INSERT INTO :from :insert',
        'update' => /** @lang text */
            'UPDATE :from SET :update :where',
        'delete' => /** @lang text */
            'DELETE FROM :from :where',
        'read' => /** @lang text */
            'SELECT :select FROM :from :join :where :group :having :order :limit',
        'read_without_group' => /** @lang text */
            'SELECT :select FROM :from :join :group :having :where :order :limit',
    ];

    /**
     * @param string $table
     * @param string $type
     * @return array|string
     */
    protected function handleFromQuery($table, $type)
    {
        if ( ! is_callable($table)) {
            return $table;
        }

        if ($type !== 'read') {
            throw new \UnexpectedValueException(
                sprintf(
                    'in create command you cant use %s type of table',
                    gettype($table)
                )
            );
        }

        return $this->handleSubQuery($table, new QueryBuilder());

    }

    /**
     * @param $where
     * @return mixed|string
     */
    public function handleWhereQuery(array $where, $subwhere = false)
    {
        $s = '';
        $values = array_values($where);
        $first = $values[0]->type;
        $args = [];


        foreach ($where as $item) {
            $returned = $this->determineWhereType($item, $args);

            $s .= $returned;

        }

        $s = ltrim($s, " $first");

        if (false === $subwhere) {
            $s = 'WHERE '.$s;
        }

        return array($s, $args);
    }

    /**
     * @param Where|Match|SubWhere $item
     * @param array $args
     * @return mixed|string
     */
    private function determineWhereType($item, &$args)
    {
        if ($item instanceof Where) {
            $returned = $this->buildStandartWhereQuery($item);
        } elseif ($item instanceof Match) {
            $returned = $this->buildMatchWhere($item);
        } elseif ($item instanceof SubWhere) {
            $returned = $this->buildSubWhere($item);
        } else {
            throw new WhereException(sprintf('Wrong where query'));
        }

        if (is_array($returned)) {
            list($returned, $returnedArgs) = $returned;

            if ( ! is_array($returnedArgs)) {
                $returnedArgs = [$returnedArgs];
            }
        }


        $args = array_merge($args, $returnedArgs);

        return $returned;
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
            $builder->getGrammer()->handleWhereQuery($where)
        );

        return array($query, $builder->getArgs());
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

        $query = $this->cleanIsNeeded($item->query);


        return array(
            sprintf(
                ' %s %s %s %s',
                $type,
                $item->field,
                $item->operator,
                $query
            ),
            $item->query,
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
            return $this->buildInArrayQuery($datas);
        }

        list($query, $args) = $this->handleSubQuery(
            $datas,
            new QueryBuilder()
        );


        return array(
            sprintf(
                ' %s %s IN (%s)',
                $item->type,
                $item->field,
                $query
            ),
            $args,
        );
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

    protected function handleSetQuery(Entity $entity)
    {
        $s = '';
        $arr = [];

        foreach ($entity->datas as $key => $value) {
            $s .= "$key = ?,";
            $arr[] = $value;
        }

        return [
            rtrim($s, ','),
            $arr,
        ];
    }

    /**
     * @param string $pattern
     * @param array $args
     * @return string
     */
    protected function handlePatternQuery($pattern, array $args)
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
     * @param Entity $entity
     * @param string $from
     * @return array
     */
    public function create(Entity $entity, $from)
    {
        $setted = $this->handleInsertQuery(
            $entity
        );

        list($content, $args) = $setted;

        $from = $this->handleFromQuery($from, 'create');

        $parameters = [
            ':from' => $from,
            ':insert' => $content,
        ];

        return array(
            $this->handlePatternQuery(
                $this->patterns['create'],
                $parameters
            ),
            $args,
        );
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
                implode(
                    $keys,
                    ','
                ),
                ','
            ).') VALUES ';

        $s .= '('.$this->handleInsertValue($count).')';
        $args = array_values($entity->datas);


        return array($s, $args);
    }

    /**
     * @param $count
     * @return string
     */
    protected function handleInsertValue($count)
    {
        return implode(
            ',',
            array_fill(
                0,
                $count,
                '?'
            )
        );
    }

    /**
     * @param Entity $entity
     * @param $from
     * @param array $where
     * @return array
     */
    public function update(Entity $entity, $from, array $where)
    {
        list($content, $args) = $this->handleSetQuery($entity);
        $from = $this->handleFromQuery($from, 'update');
        list($where, $whereArgs) = $this->handleWhereQuery($where);
        $args = array_merge($args, $whereArgs);

        $parameters = [
            ':from' => $from,
            ':update' => $content,
            ':where' => $where,
        ];


        return array(
            $this->handlePatternQuery(
                $this->patterns['update'],
                $parameters
            ),

            $args,
        );
    }


    /**
     * @param string $from
     * @param array $where
     * @return array
     */
    public function delete($from, array $where)
    {
        $from = $this->handleFromQuery($from, 'delete');

        list($where, $args) = $this->handleWhereQuery($where);

        $parameters = array(
            ':from' => $from,
            ':where' => $where,
        );

        return array(
            $this->handlePatternQuery(
                $this->patterns['delete'],
                $parameters
            ),
            $args,
        );
    }

    /**
     * @param Builder $builder
     * @return array
     */
    public function read(Builder $builder)
    {
        list($from, $args) = $this->handleFromQuery(
            $builder->getTable()
        );
        list($where, $whereArhs) = $this->handleWhereQuery(
            $builder->getWhere()
        );

        list($join, $joinArgs) = $this->handleJoinQuery(
            $builder->getJoin(),
            $builder->getTable()
        );

        $parameters = array(
            ':select' => $this->handleSelectQuery($builder->getSelect()),
            ':from' => $from,
            ':join' => $join,
            ':group' => $this->handleGroupQuery($group = $builder->getGroupBy()),
            ':having' => $this->handleHavingQuery($builder->getHaving()),
            ':where' => $where,
            ':order' => $this->handleOrderQuery($builder->getOrder()),
            ':limit' => $this->handleLimitQuery($builder->getLimit()),
        );

        $query = $this->handlePatternQuery(
            $this->getReadQuery($group),
            $parameters
        );


        return array(
            $query,
            array_merge(
                $args,
                $whereArhs,
                $joinArgs
            ),
        );
    }

    /**
     * @return string
     */
    public function getReadQuery($group)
    {
        return $group instanceof Group ? $this->patterns['read'] : $this->patterns['read_without_group'];
    }

    /**
     * @return string
     */
    protected function handleLimitQuery(Limit $limit)
    {
        if (null === $limit) {
            return '';
        }

        return
            sprintf(
                'LIMIT %d OFFSET %d',
                $limit->offset,
                $limit->startFrom
            );
    }

    /**
     * @return string
     */
    protected function handleOrderQuery($order)
    {
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
    protected function handleGroupQuery($group)
    {
        if (null === $group) {
            return '';
        }

        $group = implode(',', $group->group);

        return "GROUP BY $group";
    }

    /**
     * @return mixed
     */
    protected function handleHavingQuery($having)
    {
        return $having;
    }

    /**
     * @return string        $app = Singleton::load(get_called_class());
     */
    protected function handleJoinQuery($joins, $table)
    {

        if ( ! is_string($table)) {
            throw new \UnexpectedValueException(
                sprintf(
                    'you cant use %s type of table in join',
                    gettype($table)
                )
            );
        }

        if (empty($joins)) {
            return '';
        }

        $string = '';

        $new = new QueryBuilder();

        foreach ($joins as $join) {
            /**
             * @var Join $join
             */

            if (is_callable($join->target)) {
                list($tcol, $args) = $this->handleSubQuery($join->target, $new);

            } else {
                $args = [];
                $tCol = $join->table.'.'.$join->target;
            }


            $string .= sprintf(
                '%s %s ON %s.%s = %s',
                $join->type,
                $join->table,
                $table,
                $join->home,
                $tCol
            );
        }

        return array($string, $args);
    }


}
