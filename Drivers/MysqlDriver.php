<?php
namespace Sagi\Database\Drivers;

/**
 * Class MysqlDriver
 * @package Sagi\Database\Drivers
 */
class MysqlDriver extends Driver
{
    /**
     * @param array $limit
     * @return string
     */
    public function prepareLimitQuery($limit)
    {
        if(empty($limit)){
            return '';
        }
        $limit = "LIMIT " . join(',', $limit);

        return rtrim($limit, ",");
    }
}
