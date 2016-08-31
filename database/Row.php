<?php
/**
 * Created by PhpStorm.
 * User: sagi
 * Date: 24.08.2016
 * Time: 15:05
 */

namespace Sagi\Database;


class Row
{

    protected $patterns = [
        'auto_increment' => '`%s` INT(%d) UNSIGNED AUTO_INCREMENT PRIMARY KEY',
        'int' => '`%s` INT(%d)',
        'bigint' => '`%s` BIGINT(%d)',
        'tinyint' => '`%s` TINYINT(%d)',
        'varchar' => '`%s` VARCHAR(%d)',
        'timestamp' => '`%s` TIMESTAMP',
        'current' => '`%s` TIMESTAMP DEFAULT CURRENT_TIMESTAMP',
        'date' => '`%s` DATE',
        'year' => '`%s` YEAR',
        'time' => '`%s` TIME',
        'datetime' => '`%s` DATETIME',
        'text' => '`%s` TEXT',
    ];

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
        return $this->addCommand('int', $this->madeArray($name, $limit));
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
        return $this->addCommand('tinyint', $this->madeArray($name, $limit));
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
        return $this->addCommand('bigint', $this->madeArray($name, $limit));
    }

    /**
     * add a new time string
     *
     * @param string $name
     * @return Command
     */
    public function time($name)
    {
        return $this->addCommand('time', $this->madeArray($name));
    }

    /**
     * add a new timestamp column to mysql
     *
     * @param string $name
     * @return Command
     */
    public function timestamp($name)
    {
        return $this->addCommand('current', $this->madeArray($name));
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
        return $this->addCommand('auto_increment', $this->madeArray($name, $limit));
    }

    /**
     * @return $this
     */
    public function timestamps()
    {
        $this->current('created_at');
        $this->current('updated_at');

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
     * get all args
     *
     * @param mixed $param
     * @return array
     */
    private function madeArray($param)
    {
        return func_num_args() === 1 ? [$param] : func_get_args();
    }

    /**
     * build blueprint command
     *
     * @param string $type
     * @param array $variables
     * @return Command
     */
    private function addCommand($type, $variables)
    {
        if (!empty($variables)) {
            array_unshift($variables, $this->patterns[$type]);

            $command = call_user_func_array('sprintf', $variables);
        } else {
            $command = $this->patterns[$type];
        }

        static::$sqlCommands[] = $command = new Command($command);

        return $command;
    }

    /**
     * @return string
     */
    public function prepareRow()
    {
        $query = '';

        if (is_array(static::$sqlCommands)) {
            foreach (static::$sqlCommands as $command) {
                if ($command instanceof Command) {
                    $query .= $command->prepareCommand() . ",";
                }
            }
        }

        static::$sqlCommands = [];

        return rtrim($query, ",");

    }
}
