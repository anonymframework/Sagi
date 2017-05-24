<?php
/**
 * Created by PhpStorm.
 * User: My
 * Date: 05/23/2017
 * Time: 20:20
 */

namespace Sagi\Database\Connection\Drivers;

use Sagi\Database\Executor\Interfaces\DriverInterface;
use Sagi\Database\Executor\Interfaces\ExecuteInterface;
use Sagi\Database\Executor\Interfaces\PrepareInterface;
use Sagi\Database\Repositories\ParameterRepository;
use Sagi\Database\Interfaces\ConnectorInterface;

class PdoDriver extends Driver implements DriverInterface, ExecuteInterface, PrepareInterface
{


    /**
     * PdoDriver constructor.
     * @param ConnectorInterface $connection
     */
    public function __construct(
        ConnectorInterface $connection
    ) {
        $this->setConnection($connection);
    }



    /**
     * @return mixed
     */
    public function prepare($query)
    {
        return $this->getConnection()->prepare($query);
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
