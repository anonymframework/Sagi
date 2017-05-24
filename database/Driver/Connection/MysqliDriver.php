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

    /**
     * @param string $name
     * @param array $arguments
     * @return mixed
     */
    public function __call($name, array $arguments)
    {
        if (!is_callable([$this->getConnection(), $name])) {
            throw new \BadMethodCallException(sprintf(
                '%s class does not exists in mysqli', $name
            ));
        }

        return call_user_func_array(
            [$this->getConnection(), $name], $arguments
        );
    }
}
