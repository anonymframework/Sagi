<?php

namespace Sagi\Database\Driver\Connection\Sql;


use Sagi\Database\Driver\Connection\Interfaces\DriverInterface;
use Sagi\Database\Interfaces\ConnectionInterface;
use Sagi\Database\Interfaces\ConnectorInterface;

class MysqlConnector implements ConnectorInterface
{



    /**
     *
     * @param string|null $db
     * @return ConnectionInterface
     */
    public function connect($db = null)
    {

    }
}
