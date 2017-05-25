<?php
namespace Sagi\Database\Driver\Connection;


use Sagi\Database\Interfaces\ConnectionInterface;

class Driver implements DriverInterface
{

    /**
     * Driver constructor.
     * @param $connection
     */
    public function __construct($connection)
    {
        $this->connection = $connection;
    }

    /**
     * @var ConnectionInterface
     */
    protected $connection;

    /**
     * @return mixed
     */
    public function getConnection()
    {
        return $this->connection;
    }

    /**
     * @param mixed $connection
     * @return Driver
     */
    public function setConnection($connection)
    {
        $this->connection = $connection;

        return $this;
    }

    /**
     * @param string $name
     * @param array $arguments
     * @return mixed
     */
    public function __call($name, array $arguments)
    {
        if (!is_callable([$this->connection, $name])) {
            throw new \BadMethodCallException(sprintf(
                '%s class does not exists in mysqli', $name
            ));
        }

        return call_user_func_array(
            [$this->getConnection(), $name], $arguments
        );
    }
}
