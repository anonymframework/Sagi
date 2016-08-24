<?php
/**
 * Created by PhpStorm.
 * User: sagi
 * Date: 24.08.2016
 * Time: 15:22
 */

namespace Sagi\Database;

/**
 * Class Command
 * @package Sagi\Database
 */
class Command
{

    /**
     * @var array
     */
    protected $patterns =
        [
            'default' => 'DEFAULT %s',
            'null' => 'NULL',
            'notnull' => 'NOT NULL'
        ];

    /**
     * @var array
     */
    protected $queires = [];

    /**
     * Command constructor.
     * @param string $command
     */
    public function __construct($command)
    {
        $this->queires = [$command];
    }

    /**
     * @return Row
     */
    public function null()
    {
        return $this->addCommand('null', []);
    }

    /**
     * @return Row
     */
    public function notNull()
    {
        return $this->addCommand('notnull', []);
    }

    /**
     * @param $value
     * @return Command
     */
    public function defaultValue($value)
    {
        return $this->addCommand('default', ["'" . $value . "'"]);
    }

    /**
     * @param $expression
     * @return Command
     */
    public function defaultExpression($expression)
    {
        return $this->addCommand('default', [$expression]);
    }

    /**
     * @return Command
     */
    public function currentTimestamp()
    {
        return $this->defaultExpression('CURRENT_TIMESTAMP');
    }

    /**
     * @param $type
     * @param $variables
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

        $this->queires[] = $command;

        return $this;
    }

    /**
     * @return string
     */
    public function prepareCommand()
    {
        return join(' ', $this->queires) . ',';
    }
}