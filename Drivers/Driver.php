<?php

namespace Sagi\Database\Drivers;

/**
 * Class Driver
 * @package Sagi\Database\Drivers
 */
class Driver
{

    /**
     * @var bool
     */
    public $prepareValues = true;

    /**
     * @return string
     */
    public function prepareLimitQuery($limit)
    {

        if (empty($limit)) {
            return "";
        }

        $s = "LIMIT $limit[0] ";

        if (isset($limit[1])) {
            $s .= 'OFFSET ' . $limit[1];
        }

        return $s;
    }

    public function prepareOrderQuery($order)
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
    public function prepareSelectQuery($select)
    {

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
    public function prepareGroupQuery($group)
    {

        if (empty($group)) {
            return "";
        }

        return "GROUP BY $group";
    }

    public function prepareHavingQuery($having)
    {
        return $having;
    }

    /**
     * @return string
     */
    public function prepareJoinQuery($joins)
    {
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

    public function prepareWhereQuery($where)
    {
        $string = '';
        if (!empty($where)) {
            $string .= $this->prepareAllWhereQueries($where);
        }


        if ($string !== '') {
            $string = 'WHERE ' . $string;
        }

        return $string;
    }

    /**
     * @return string
     */
    private function prepareAllWhereQueries($where)
    {

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
    public function prepareInQuery($datas)
    {
        $inQuery = '';
        if (is_array($datas)) {
            $inQuery = '[' . implode(',', $datas) . ']';
        } elseif (is_callable($datas)) {
            $inQuery = $this->prepareSubQuery($datas);
        } else {
            $inQuery = '[' . $datas . ']';
        }

        return $inQuery;
    }

}