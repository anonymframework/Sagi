<?php

namespace Sagi\Database\Drivers;
use Sagi\Database\Mapping\Group;

/**
 * Class Driver
 * @package Sagi\Database\Drivers
 */
class Driver
{


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
    public function prepareGroupQuery(Group $group = null)
    {

        if (is_null($group)) {
            return "";
        }

        $group = join(',', $group->group);

        return "GROUP BY $group";
    }

    public function prepareHavingQuery($having)
    {
        return $having;
    }

    /**
     * @return string
     */
    public function prepareJoinQuery($joins, $table)
    {
        if (empty($joins)) {
            return '';
        }

        $string = '';

        foreach ($joins as $join) {
            $type = isset($join[0]) ? $join[0] : 'LEFT JOIN';
            $targetTable = isset($join[1]) ? $join[1] : '';
            $targetColumn = isset($join[2]) ? $join[2] : '';
            $ourTable = $table;
            $ourColumn = isset($join[3]) ? $join[3] : '';
            $string .= "$type $targetTable ON $ourTable.$ourColumn = $targetTable.$targetColumn";
        }
        return $string;
    }

}