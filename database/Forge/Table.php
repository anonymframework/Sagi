<?php
/**
 * Created by PhpStorm.
 * User: My
 * Date: 05/22/2017
 * Time: 03:28
 */

namespace Sagi\Database\Forge;


use Sagi\Database\Connector;
use Sagi\Database\Exceptions\QueryException;

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
        'create_exists' => 'CREATE TABLE IF NOT EXISTS `%s`(',
        'create' => 'CREATE TABLE `%s`(',
        'drop' => 'DROP TABLE `%s`;',
        'end' => ');',
    ];

    /**
     * @var array
     */
    protected $commands;

    public function create($table, callable $callback, $database = null)
    {
        $this->addCommand('create', $table);
        $callback(
            new Column($table)
        );
        $this->addCommand('end');
        $command = $this->renderCommand();


        $this->runCommand($command,$database);
    }

    /**
     * @param string $command
     * @param string $command
     * @return int
     */
    private function runCommand($command, $database)
    {
        $connection = $this->getConnector()->getConnection($database);

        $run = $connection->exec($command);

        if (false === $run) {
            throw new QueryException(
                sprintf(
                    '%s has failed, error: %s',
                    $command,
                    json_encode($connection->errorInfo())
                )
            );
        }
    }


    /**
     * @return string
     */
    private function renderCommand()
    {
        return implode('', $this->commands);
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

    /**
     * @return Connector
     */
    public function getConnector()
    {
        if (null === $this->connector) {
            $this->connector = new Connector();
        }

        return $this->connector;
    }

    /**
     * @param mixed $connector
     * @return Table
     */
    public function setConnector($connector)
    {
        $this->connector = $connector;

        return $this;
    }


}