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
    protected $migrationTable = 'migrations';

    /**
     * @var \PDO
     */
    protected $connection;

    public function __construct()
    {
        parent::__construct();

        $this->connection = Connector::getConnection();
    }

    public function migrate()
    {

    }

    public function checkMigrationTable()
    {

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
