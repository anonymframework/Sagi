<?php

namespace Sagi\Database\Drivers;

use Sagi\Database\Mapping\Group;
use Sagi\Database\QueryBuilder;

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
    public function prepareGroupQuery(Group $group = null)
    {

        if (is_null($group)) {
            return "";
        }

        $group = join(',', $group->group);

        return "GROUP BY $group";
    }

    /**
     * @param $having
     * @return mixed
     */
    public function prepareHavingQuery($having)
    {
        return $having;
    }

}