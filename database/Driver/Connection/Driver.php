<?php
namespace Sagi\Database\Driver\Connection;


use Sagi\Database\Interfaces\ConnectionInterface;

class Driver
{

    /**
     * @var ConnectionInterface
     */
    private $connection;

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
}
