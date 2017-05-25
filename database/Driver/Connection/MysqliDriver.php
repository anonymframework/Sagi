<?php
namespace Sagi\Database\Driver\Connection;


use Sagi\Database\Driver\Connection\Interfaces\DriverInterface;
use Sagi\Database\Driver\Connection\Interfaces\ExecuteInterface;
use Sagi\Database\Driver\Connection\Interfaces\PrepareInterface;
use Sagi\Database\Repositories\ParameterRepository;
use Sagi\Database\Interfaces\ConnectionInterface;

class MysqliDriver extends Driver implements DriverInterface, ExecuteInterface, PrepareInterface
{


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
        $prepare = $this->connection->prepare($query);

        return $prepare;
    }


}
