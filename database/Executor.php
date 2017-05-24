<?php
/**
 * Created by PhpStorm.
 * User: My
 * Date: 05/23/2017
 * Time: 20:11
 */

namespace Sagi\Database;


use Sagi\Database\Executor\Interfaces\DriverInterface;
use Sagi\Database\Repositories\ParameterRepository;
use Sagi\Database\Interfaces\ConnectionInterface;
use Sagi\Database\Interfaces\ExecutorInterface;

class Executor implements ExecutorInterface
{

    /**
     * @var array
     */
    private $query;

    /**
     * @var ParameterRepository
     */
    private $parameterRepository;

    /**
     * @var DriverInterface
     */
    private $driver;

    /**
     * Executor constructor.
     * @param DriverInterface $driver
     * @param $query
     * @param ParameterRepository $parameterRepository
     */
    public function __construct(
        DriverInterface $driver,
        $query,
        ParameterRepository $parameterRepository
    ) {
        $this->query = $query;
        $this->driver = $driver;
        $this->parameterRepository = $parameterRepository;

    }

    /**
     * @return array
     */
    public function execute()
    {
        $prepare = $this->driver->prepare(
            $this->query
        );

        return $this->driver->execute(
            $prepare,
            $this->parameterRepository
        );
    }

    /**
     * @return array
     */
    public function getQuery()
    {
        return $this->query;
    }

    /**
     * @param array $query
     * @return Executor
     */
    public function setQuery($query)
    {
        $this->query = $query;

        return $this;
    }

    /**
     * @return ParameterRepository
     */
    public function getParameterRepository()
    {
        return $this->parameterRepository;
    }

    /**
     * @param ParameterRepository $parameterRepository
     * @return Executor
     */
    public function setParameterRepository($parameterRepository)
    {
        $this->parameterRepository = $parameterRepository;

        return $this;
    }

    /**
     * @return ConnectionInterface
     */
    public function getConnection()
    {
        return $this->connection;
    }

    /**
     * @param ConnectionInterface $connection
     * @return Executor
     */
    public function setConnection($connection)
    {
        $this->connection = $connection;

        return $this;
    }
}