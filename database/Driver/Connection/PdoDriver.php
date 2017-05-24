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
     * PdoDriver constructor.
     * @param ConnectorInterface $connection
     */
    public function __construct($connection)
    {


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

    /**
     * @param string $name
     * @param array $arguments
     * @return mixed
     */
    public function __call($name, array $arguments)
    {
        if (!is_callable([$this->getConnection(), $name])) {
            throw new \BadMethodCallException(sprintf(
                '%s class does not exists in pdo', $name
            ));
        }

        return call_user_func_array(
            [$this->getConnection(), $name], $arguments
        );
    }
}
