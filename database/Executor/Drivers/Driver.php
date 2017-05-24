<?php
namespace Sagi\Database\Connection\Drivers;


use Sagi\Database\Interfaces\ConnectionInterface;

class Driver
{

    /**
     * @var ConnectionInterface
     */
    private $connection;

    /**
     * @return ConnectionInterface
     */
    public function getConnection()
    {
        return $this->connection;
    }

    /**
     * @param ConnectionInterface $connection
     * @return Driver
     */
    public function setConnection(ConnectionInterface $connection)
    {
        $this->connection = $connection;

        return $this;
    }
}
