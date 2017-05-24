<?php
/**
 * Created by PhpStorm.
 * User: My
 * Date: 05/23/2017
 * Time: 20:50
 */

namespace Sagi\Database\Connection\Drivers;


use Sagi\Database\Executor\Interfaces\DriverInterface;
use Sagi\Database\Executor\Interfaces\ExecuteInterface;
use Sagi\Database\Executor\Interfaces\PrepareInterface;
use Sagi\Database\Repositories\ParameterRepository;
use Sagi\Database\Interfaces\ConnectionInterface;

class MysqliDriver extends Driver implements DriverInterface, ExecuteInterface, PrepareInterface
{

    /**
     * MysqliDriver constructor.
     * @param ConnectionInterface $connection
     */
    public function __construct(ConnectionInterface $connection)
    {
        $this->setConnection($connection);
    }

    /**
     * @param $prepare
     * @param ParameterRepository $parameterRepository
     * @return array
     */
    public function execute($prepare, ParameterRepository $parameterRepository)
    {
        call_user_func_array(
            array($prepare, 'bind_param'),
            $parameterRepository->getParametersWithTypeString()
        );

        $executed = $prepare->execute();

        $prepare->close();

        return array($prepare, $executed);
    }

    /**
     * @param $query string
     * @return mixed
     */
    public function prepare($query)
    {
        $prepare = $this->getConnection()->prepare($query);

        return $prepare;
    }
}
