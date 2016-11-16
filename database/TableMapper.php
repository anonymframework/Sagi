<?php
/**
 *  SAGI DATABASE ORM FILE
 *
 */

namespace Sagi\Database;


use Sagi\Database\Mapping\Table;

/**
 * Class TableMapper
 * @package Sagi\Database
 */
class TableMapper implements MapperInterface
{
    /**
     * @return array
     */
    public function map()
    {
        $tables = QueryBuilder::createNewInstance()->query('SHOW TABLES')->fetchAll();

        $mapper = new ColumnMapper();

        $mapped = [];

        if (count($tables > 0)) {
            foreach ($tables as $table) {
                $instance = new Table();
                $instance->name = $table[0];

                $map = $mapper->map($table[0]);

                $instance->columns = $map;

                $mapped[] = $instance;
            }
        }

        return $mapped;
    }

    public function mapTable($table)
    {
        $instance = new Table();
        $mapper = new ColumnMapper();
        $instance->name = $table[0];

        $instance->columns = $mapper->map($table[0]);

        return $instance;
    }
}