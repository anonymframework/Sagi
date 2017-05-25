<?php
namespace Sagi\Database\Forge;

use PDO;

class Column
{

    /**
     * @var string
     */
    protected $table;

    /**
     * @var PDO
     */
    protected $connection;

    /**
     * @var array
     */
    protected static $commands;
    /**
     * Column constructor.
     * @param $table
     * @param PDO $connection
     */
    public function __construct($table)
    {
        $this->table = $table;
    }



    public function index($name, $col)
    {
        return $this->addCommand('index', [$name, $col]);
    }

    /**
     * @var array
     */
    protected static $sqlCommands;

    /**
     * add a text string to value
     *
     * @param string $name
     * @return Command
     */
    public function text($name)
    {
        return $this->addCommand('text', $this->madeArray($name));
    }

    /**
     * add a new varchar command
     *
     * @param string $name
     * @param int $limit
     * @return Command
     */
    public function string($name, $limit = 255)
    {
        return $this->addCommand('varchar', $this->madeArray($name, $limit));
    }

    /**
     * add a new char command
     *
     * @param string $name
     * @param int $limit
     * @return Command
     */
    public function char($name, $limit = 255)
    {
        return $this->addCommand('char', $this->madeArray($name, $limit));
    }

    /**
     * add a new date command
     *
     * @param  string $name
     * @return Command
     */
    public function date($name)
    {
        return $this->addCommand('date', $this->madeArray($name));
    }

    /**
     * add a new integer command
     *
     * @param string $name
     * @param int $limit
     * @return Command
     */
    public function int($name, $limit = 255)
    {
        return $this->addCommand('int', $this->madeArray($name, $limit), 'integer');
    }

    /**
     * add a new integer command
     *
     * @param string $name
     * @param int $limit
     * @return Command
     */
    public function tinyInt($name, $limit = 255)
    {
        return $this->addCommand('tinyint', $this->madeArray($name, $limit), 'integer');
    }

    /**
     * add a new integer command
     *
     * @param string $name
     * @param int $limit
     * @return Command
     */
    public function bigInt($name, $limit = 255)
    {
        return $this->addCommand('bigint', $this->madeArray($name, $limit), 'integer');
    }

    /**
     * add a new time string
     *
     * @param string $name
     * @return Command
     */
    public function time($name)
    {
        return $this->addCommand('time', $this->madeArray($name), 'integer');
    }

    /**
     * add a new time string
     *
     * @param string $name
     * @return Command
     */
    public function bool($name)
    {
        return $this->addCommand('bool', $this->madeArray($name), 'integer');
    }


    /**
     * add a new time string
     *
     * @param string $name
     * @return Command
     */
    public function bit($name)
    {
        return $this->addCommand('bit', $this->madeArray($name));
    }

    /**
     * add a new timestamp column to mysql
     *
     * @param string $name
     * @return Command
     */
    public function timestamp($name)
    {
        return $this->addCommand('timestamp', $this->madeArray($name));
    }

    /**
     * add a new year year column to mysql
     *
     * @param string $name
     * @return Command
     */
    public function year($name)
    {
        return $this->addCommand('year', $this->madeArray($name));
    }

    /**
     * add a new auto_increment column to mysql
     *
     * @param string $name
     * @param int $limit
     * @return Command
     */
    public function pk($name, $limit = 255)
    {
        return $this->addCommand(
            'int',
            $this->madeArray($name, $limit),
            'integer'
        )->unsigned()->notNull()->autoIncrement();
    }

    /**
     * @return $this
     */
    public function timestamps()
    {
        $this->current(Model::CREATED_AT);
        $this->timestamp(Model::UPDATED_AT)->null();

        return $this;
    }

    /**
     * add a new time stamp with CURRENT_TIMESTAMP
     *
     * @param string $name
     * @return Command
     */
    public function current($name)
    {
        return $this->addCommand('current', $this->madeArray($name));
    }

    /**
     * @return Command
     */
    public function auth()
    {
        return $this->string('role')->defaultValue('user');
    }

    /**
     * @param string $name
     * @param int $precision
     * @param int $scale
     * @return Command
     */
    public function decimal($name, $precision, $scale)
    {
        return $this->addCommand('decimal', [$name, $precision, $scale], 'integer');
    }

    /**
     * @param string $name
     * @param int $precision
     * @return Command
     */
    public function float($name, $precision)
    {
        return $this->addCommand('float', [$name, $precision], 'integer');
    }

    /**
     * @param $keys
     * @return Command
     */
    public function primaryKey($keys)
    {
        if (is_string($keys)) {
            $keys = array_map(
                function ($value) {
                    return "`$value`";
                },
                explode(',', $keys)
            );
        }

        $keys = implode(',', $keys);

        return $this->addCommand('primary_key', [$keys]);
    }

    /**
     * @param $keys
     * @return Command
     */
    public function foreignKey($table, $colOur, $colTarget)
    {
        return $this->addCommand('foreign_key', [$colOur, $table, $colTarget]);
    }

    /***
     * @param $notarray
     * @return array
     */
    private function madeArray($notarray){
        if ( !is_array($notarray)) {
            return array($notarray);
        }

        return $notarray;
    }

    /**
     * @param $index
     * @param $columns
     * @return Command
     */
    public function fulltext($index, $columns)
    {
        if (is_array($columns)) {
            $columns = implode(',', $columns);
        }

        return $this->addCommand('fulltext', [$index, $columns]);
    }

    /**
     * @param string $operator
     * @param array $paramaters
     * @return $this
     */
    private function addCommand($operator,array $paramaters = []){

        static::$commands[$this->table][] = $command = new Command($operator, $paramaters, $this);

        return $command;
    }

    /**
     * @return array
     */
    public static function getCommands()
    {
        return self::$commands;
    }

    /**
     * @param array $commands
     */
    public static function setCommands($commands)
    {
        self::$commands = $commands;
    }
}
