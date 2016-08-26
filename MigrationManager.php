<?php


namespace Sagi\Database;

/**
 * Class MigrationManager
 * @package Sagi\Database
 */
class MigrationManager extends Schema
{

    /**
     * @var string
     */
    public static $migrationDir = 'migrations';

    /**
     * @var string
     */
    protected $migrationTable = 'migrations';

    /**
     * @var \PDO
     */
    protected $connection;

    public function __construct()
    {
        parent::__construct();

        $this->connection = new QueryBuilder();
    }

    public function migrate()
    {
        if (!$this->checkMigrationTable()) {
            $this->createMigrationsTable();
        }

        $glob = glob(static::$migrationDir.'/*');

        var_dump($glob);
    }

    /**
     * @return bool
     */
    public function checkMigrationTable()
    {
        return $this->connection->setTable($this->migrationTable)->where('id', 1)->exists();
    }

    public function createMigrationsTable()
    {
        $this->createTable($this->migrationTable, function (Row $row) {
            $row->pk('id');
            $row->string('filename')->notNull();
            $row->string('path')->notNull();
        });
    }

    /**
     *
     */
    public function dropMigrationsTable()
    {
        $this->dropTable($this->migrationTable);
    }
}
