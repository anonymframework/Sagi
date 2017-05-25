<?php

namespace Sagi\Database\Forge;


use Sagi\Database\Builder;
use Sagi\Database\Connector;
use Sagi\Database\Driver\Expectation\ExpectInstanceOf;
use Sagi\Database\Exceptions\QueryException;
use Sagi\Database\Repositories\ParameterRepository;

class Table
{

    /**
     * @var Connector
     */
    private $connector;

    /**
     * holds the patterns the class will be use
     *
     * @var array
     */
    private static $patterns = [
        'create_exists' => /** @lang text */
            'CREATE TABLE IF NOT EXISTS `%s`(',
        'create' => /** @lang text */
            'CREATE TABLE `%s`(',
        'drop' => /** @lang text */
            'DROP TABLE `%s`;',
        'end' => ');',
    ];

    /**
     * @var array
     */
    protected $commands;

    /**
     * @var Builder
     */
    private $builder;


    public function __construct(Builder $builder)
    {
        $this->builder = $builder;

        $this
            ->builder
            ->getDriverManager()
            ->expect(
                'migration',
                new ExpectInstanceOf(
                    '\Sagi\Database\Forge\DriverInterface'
                )
            );
        
    }

    public function create($table, callable $callback, $database = null)
    {
        $this->addCommand('create', $table);

        $callback(
            new Column($table)
        );

        $this->addCommand('end');

        $this->runCommand(
            $this->renderCommand(),
            $database
        );


    }


    /**
     * @param $command
     * @param $database
     * @throws QueryException
     */
    private function runCommand($command, $database)
    {

        $this->builder->connect($database);

        $run = $this->builder
            ->getDriver()
            ->exec($command);

        if (false === $run) {
            throw new QueryException(
                sprintf(
                    '%s has failed, error: %s',
                    $command,
                    json_encode(
                        $this->builder
                            ->getDriver()
                            ->errorInfo()
                    )
                )
            );
        }
    }


    /**
     * @return string
     * @throws \Sagi\Database\Exceptions\DriverNotFoundException
     */
    private function renderCommand()
    {

        $driver = $this
            ->builder
            ->getDriverManager()
            ->resolve(
                'migration',
                'create',
                new ParameterRepository(
                    $this->commands
                )
            );




    }

    /**
     * @param string $pattern
     * @param array|null $variables
     * @return string
     */
    protected function addCommand($pattern, $variables = null)
    {
        if ( ! is_array($variables)) {
            $variables = [$variables];
        }

        array_unshift($variables, static::$patterns[$pattern]);

        $command = call_user_func_array('sprintf', $variables);
        $this->commands[] = $command;

        return $command;
    }
}
