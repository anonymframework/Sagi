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
        'varchar' => '`%s` VARCHAR(%d)',
        'timestamp' => '`%s` TIMESTAMP',
        'current' => '`%s` TIMESTAMP DEFAULT CURRENT_TIMESTAMP',
        'date' => '`%s` DATE',
        'year' => '`%s` YEAR',
        'time' => '`%s` TIME',
        'datetime' => '`%s` DATETIME',
        'text' => '`%s` TEXT',
        'default' => 'DEFAULT %s'
    ];

    /**
     * @var array
     */
    protected $sqlCommands;

    /**
     * add a text string to value
     *
     * @param string $name
     * @return Chield
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
     * @return Chield
     */
    public function varchar($name, $limit = 255)
    {
        return $this->addCommand('varchar', $this->madeArray($name, $limit));
    }

    /**
     * add a new date command
     *
     * @param  string $name
     * @return Chield
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
     * @return Chield
     */
    public function int($name, $limit = 255)
    {
        return $this->addCommand('int', $this->madeArray($name, $limit));
    }

    /**
     * add a new time string
     *
     * @param string $name
     * @return Chield
     */
    public function time($name)
    {
        return $this->addCommand('time', $this->madeArray($name));
    }

    /**
     * add a new timestamp column to mysql
     *
     * @param string $name
     * @return Chield
     */
    public function timestamp($name)
    {
        return $this->addCommand('timestamp', $this->madeArray($name));
    }

    /**
     * add a new year year column to mysql
     *
     * @param string $name
     * @return Chield
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
     * @return mixed
     */
    public function pk($name, $limit = 255)
    {
        return $this->addCommand('auto_increment', $this->madeArray($name, $limit));
    }

    /**
     * add a new time stamp with CURRENT_TIMESTAMP
     *
     * @param string $name
     * @return Chield
     */
    public function current($name)
    {
        return $this->addCommand('current', $this->madeArray($name));
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
     * @return Chield
     */
    private function addCommand($type, $variables)
    {
        array_unshift($variables, $this->patterns[$type]);

        $this->sqlCommands[] = call_user_func_array('sprintf', $variables);
    }
}
