<?php
/**
 *  SAGI DATABASE ORM FILE
 *
 */

namespace Sagi\Database;


use Sagi\Database\Mapping\Column;

class ColumnMapper
{
    public function map($table)
    {
        $table = QueryBuilder::createNewInstance()->query('SHOW COLUMNS FROM ' . $table);

        $columns = [];

        if ($table->rowCount() > 0) {
            foreach ($table->fetchAll() as $column) {
                $instance = new Column();

                $instance->name = $column['Field'];
                $instance->nullable = $column['Null'] === 'No' ? false : true;
                $instance->primaryKey = $column['Key'] === 'PRI' ? true : false;
                $instance->default = $column['Default'] !== '' ? $column['Default'] : null;
                $tl = $this->findTypeLength($column['Type']);

                $instance->length = is_array($tl) ? $tl[1] : null;
                $instance->type = is_array($tl) ? $tl[0] : $tl;

                $columns[] = $instance;
            }
        }

        return $columns;
    }

    /**
     * @param $type
     * @return array
     */
    private function findTypeLength($type)
    {
        if (strpos($type, "(") !== false) {
            if (preg_match("#(.*)\((.*?)\)#", $type, $match)) {
                return [$match[1], $match[2]];
            }
        } else {
            return $type;
        }
    }

}