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
     * MigrationManager constructor.
     */
    public function __construct()
    {
        parent::__construct();
    }


    public function migrate()
    {
        if (!$this->checkMigrationTable()) {
            $this->createMigrationsTable();
        }

        $glob = glob(static::$migrationDir . '/*');

        $files = [];
        foreach ($glob as $file) {
            $class = explode('__', $file);

            if (!isset($class[1])) {
                continue;
            }


            $class = str_replace(".php", "", $class[1]);

            $prepared = static::prepareClassName($class);


            if ($this->checkMigrated($file, $prepared)) {
                continue;
            }


            include $file;


            $migration = new $prepared;

            if ($migration instanceof MigrationInterface) {
                $migration->up();
            }

            QueryBuilder::createNewInstance()->setTable('migrations')->create([
                'filename' => $prepared,
                'path' => $file
            ]);

            $files[] = $file;
        }

        return $files;
    }

    public function down()
    {
        if (!$this->checkMigrationTable()) {
            throw new \Exception('You did not migrate anything yet');
        }

        $migration = QueryBuilder::createNewInstance()->setTable($this->migrationTable)->all();

        $ids = [];
        $files = [];

        foreach ($migration as $migrate) {
            $path = $migrate->path;
            $name = $migrate->filename;


            include $path;

            $class = new $name;

            if ($class instanceof MigrationInterface) {
                $class->down();

                $ids[] = $migrate->id;
                $files[] = $path;
            }
        }

        if (!empty($ids)) {
            QueryBuilder::createNewInstance()->setTable('migrations')->in('id', $ids)->delete();
        }


        return $files;
    }

    /**
     * @param $file
     * @param $class
     * @return bool
     */
    public function checkMigrated($file, $class)
    {
        $builder = QueryBuilder::createNewInstance()->setTable($this->migrationTable)->where('filename', $class)->where('path', $file);

        return $builder->exists();
    }

    /**
     * @param $name
     * @return string
     */
    public static function prepareClassName($name)
    {
        if (strpos($name, "_")) {
            $exp = explode("_", $name);

            $exp = array_map(function ($value) {
                return ucfirst($value);
            }, $exp);

            return join('', $exp);
        }

        return ucfirst($name);
    }


    /**
     * @return bool
     */
    public function checkMigrationTable()
    {
        return QueryBuilder::createNewInstance('migrations')->tableExists();
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
