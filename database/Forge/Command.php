<?php

namespace Sagi\Database\Forge;

/**
 * Class Command
 * @package Sagi\Database\Forge
 */
class Command
{

    /**
     * @var Column
     */
    private $column;

    /**
     * @var array
     */
    private $parameters;

    /**
     * @var string
     */
    private $operator;

    /**
     * @var array
     */
    private $commands;


    /**
     * Command constructor.
     * @param $operator
     * @param array $parameters
     * @param Column $column
     */
    public function __construct($operator, array $parameters, Column $column)
    {
        $this->operator = $operator;
        $this->parameters = $parameters;
        $this->column = $column;
    }

    /**
     * @return $this
     */
    public function null(){
        $this->addCommand('null');

        return $this;
    }

    /**
     * @return $this
     */
    public function notNull(){
       return $this->addCommand('not_null');
    }

    /**
     *
     * @param string $value
     * @return $this
     */
    public function defaultValue($value){
        return $this->addCommand('null', array($value));
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
     * @param $operator
     * @param array $parameters
     * @return $this
     */
    private function addCommand($operator,array $parameters = [])
    {
        $this->commands[] = [$operator, $parameters];

        return $this;
    }
    /**
     * @param string $name
     * @param array $arguments
     * @return mixed
     */
    public function __call($name,array $arguments)
    {
        return call_user_func_array(
            array(
                $this->column,
                $name,
            ),

            $arguments
        );
    }

}