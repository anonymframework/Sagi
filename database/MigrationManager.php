<?php


namespace Sagi\Database;

use Sagi\Database\Mapping\Entity;

/**
 * Class MigrationManager
 * @package Sagi\Database
 */
class MigrationManager extends Schema
{

    public static $systemMigrations = [
        'auth', 'migrations'
    ];

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
                $files[] = [
                    'name' => $file,
                    'status' => 2
                ];
                continue;
            }

            if (!class_exists($prepared)) {
                include $file;
            }


            $migration = new $prepared;

            if ($migration instanceof MigrationInterface) {
                $migration->up();
            } else {
                throw new \Exception(get_class($migration) . 'is not a instance of MigrationInterface');
            }

            QueryBuilder::createNewInstance()->setTable('migrations')->create(new Entity([
                'filename' => $prepared,
                'path' => $file
            ]));

            $files[] = [
                'name' => $file,
                'status' => 1
            ];
        }

        return $files;
    }

    public function down()
    {
        if (!$this->checkMigrationTable()) {
            throw new \Exception('You did not migrate anything yet');
        }

        $migration = QueryBuilder::createNewInstance()->setTable($this->migrationTable)->all();
        $files = [];


        foreach ($migration as $migrate) {
            $path = $migrate->path;
            $name = $migrate->filename;


            if (!file_exists($path)) {
                continue;
            }
            include $path;

            $class = new $name;

            if ($class instanceof MigrationInterface) {
                $class->down();

                $builder = QueryBuilder::createNewInstance($this->migrationTable)->where('path', $path)->where('filename', $name);

                if($builder->exists()){
                    $builder->delete();
                }

                $files[] = $path;
            }
        }

        $schema = new Schema();

        $schema->dropTable($this->migrationTable);


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
                $ucfirst = mb_convert_case($value, MB_CASE_TITLE);

                return $ucfirst;
            }, $exp);


            $name = join('', $exp);
        } else {
            $name = mb_convert_case($name, MB_CASE_TITLE);

        }


        return $name;
    }

    /**
     * @param $value
     * @return mixed
     */
    public static function cleanTurkishChars($value)
    {
        $find = array('ç', 'Ç', 'ı', 'İ', 'ğ', 'Ğ', 'ü', 'ö', 'Ş', 'ş', 'Ö', 'Ü', ',', ' ', '(', ')', '[', ']');
        $replace = array('c', 'C', 'i', 'I', 'g', 'G', 'u', 'o', 'S', 's', 'O', 'U', '', '_', '', '', '', '');

        return str_replace($find, $replace, $value);
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

    /**
     * @param $name
     * @return string
     */
    public static function migrationPath($name)
    {
        return static::$migrationDir . '/migration_file' . date('y_m_d_h_m') . '__' . $name . '.php';

    }
}
