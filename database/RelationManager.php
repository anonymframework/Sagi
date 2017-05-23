<?php
/**
 *  SAGI DATABASE ORM FILE
 *
 */

namespace Sagi\Database;

/**
 * Class RelationManager
 * @package Sagi\Database
 */
class RelationManager
{
    /**
     * @param $table
     * @param $ourCol
     * @param $tarCol
     * @return $this
     */
    public function makeOneRelation($table, $ourCol, $tarCol)
    {
        return $this->makeRelation('one', $table, $ourCol, $tarCol);
    }

    /**
     * @param $table
     * @param $ourCol
     * @param $tarCol
     * @return $this
     */
    public function makeManyRelation($table, $ourCol, $tarCol)
    {
        return $this->makeRelation('many', $table, $ourCol, $tarCol);
    }

    public function makeManyManyRelation($table, $our, $tar)
    {

    }

    /**
     * @param string $type
     * @param $table
     * @param $ourCol
     * @param $tarCol
     * @return $this
     */
    public function makeRelation($type = 'one', $table, $ourCol, $tarCol)
    {
        MigrationManager::$migrationRelations[$type][$this->table] = [
            $table => [$ourCol, $tarCol],
        ];

        return $this;
    }
}
