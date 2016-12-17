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
            'notnull' => 'NOT NULL',
            'unique' => 'UNIQUE',
            'unsigned' => 'UNSIGNED',
            'signed' =>  'SIGNED',
            'constraint' => 'CONSTRAINT %s',
            'delete_cascade' => 'ON DELETE CASCADE',
            'update_cascade' => 'ON UPDATE CASCADE',
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
     * @param $name
     */
    public function constraint($name){
       return $this->addCommand('constraint', [$name], true);
    }
    /**
     * @return Command
     */
    public function null()
    {
        return $this->addCommand('null', []);
    }

    /**
     * @return Command
     */
    public function unique()
    {
        return $this->addCommand('unique', []);
    }

    /**
     * @return Command
     */
    public function signed(){
        return $this->addCommand('signed', []);
    }

    /**
     * @return Command
     */
    public function unsigned(){
        return $this->addCommand('unsigned', []);
    }
    /**
     * @return Command
     */
    public function notNull()
    {
        return $this->addCommand('notnull', []);
    }

    /**
     * @return Command
     */
    public function onUpdateCascade(){
        return $this->addCommand('update_cascade', []);
    }

    /**
     * @return Command
     */
    public function onDeleteCascade(){
        return $this->addCommand('delete_cascade', []);
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
    private function addCommand($type, $variables, $unshift = false)
    {
        if (!empty($variables)) {
            array_unshift($variables, $this->patterns[$type]);

            $command = call_user_func_array('sprintf', $variables);
        } else {
            $command = $this->patterns[$type];
        }

        if ($unshift) {
            array_unshift($this->queires, $command);
        }else{
            $this->queires[] = $command;
        }

        return $this;
    }

    /**
     * @return string
     */
    public function prepareCommand()
    {
        $query = join(' ', $this->queires) . ',';

        return rtrim($query, ',');
    }
}