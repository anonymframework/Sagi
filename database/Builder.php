<?php
/**
 * Created by PhpStorm.
 * User: My
 * Date: 04/27/2017
 * Time: 17:33
 */

namespace Sagi\Database;


use Sagi\Database\Builder\WhereBuilder;
use Sagi\Database\Mapping\Join;

class Builder
{
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

    /**
     * @var array
     */
    protected $marks = [
        '=' => 'equal',
        '>' => 'bigger',
        '<' => 'smaller',
        '!=' => 'diffrent',
        '>=' => 'ebigger',
        '=<' => 'esmaller',
        'IN' => 'in',
        'NOT IN' => 'notin',
    ];

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

        if (is_null($group)) {
            return "";
        }

        $group = implode(',', $group->group);

        return "GROUP BY $group";
    }

    /**
     * @return mixed
     */
    protected function prepareHavingQuery()
    {
        return $this->having;
    }

    /**
     * @param $where
     * @return mixed|string
     */
    protected function prepareWhereQuery()
    {
        $builder = new WhereBuilder($this, $this->getArgs());

        $string = $builder->build();

        $this->setArgs(array_merge($this->getArgs(), $builder->getArgs()));

        return $string;
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

        $builder = new WhereBuilder($this, $this->getArgs());
        foreach ($joins as $join) {
            /**
             * @var Join $join
             */

            if (is_callable($join->target)) {

                $builder->setİnstance($new)->setArgs($this->getArgs());

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
    protected function prepareSelectQuery()
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
        return !empty($this->as);
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
    public function getMarks()
    {
        return $this->marks;
    }

    /**
     * @param array $marks
     * @return Builder
     */
    public function setMarks($marks)
    {
        $this->marks = $marks;
        return $this;
    }


}

