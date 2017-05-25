<?php
namespace Sagi\Database\Driver\Connection;
use Sagi\Database\Driver\Connection\Interfaces\DriverInterface;
use Sagi\Database\Driver\Connection\Interfaces\ExecuteInterface;
use Sagi\Database\Driver\Connection\Interfaces\PrepareInterface;
use Sagi\Database\Repositories\ParameterRepository;
use Sagi\Database\Interfaces\ConnectorInterface;

class PdoDriver extends Driver implements DriverInterface, ExecuteInterface, PrepareInterface
{


    /**
     * @return mixed
     */
    public function prepare($query)
    {
        return $this->connection->prepare($query);
    }


    /**
     * @param $prepare
     * @param ParameterRepository $parameterRepository
     * @return array
     */
    public function execute($prepare, ParameterRepository $parameterRepository)
    {
        $exed = $prepare->execute($parameterRepository->getParameters());

        return array($prepare, $exed);
    }

}
