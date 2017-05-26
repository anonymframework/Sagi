<?php
namespace Sagi\Database\Interfaces;

use Sagi\Database\Driver\Grammer\Sql\SqlStandartGrammerInterface;
use Sagi\Database\Forge\DriverInterface;

/**
 * Interface ConnectorInterface
 * @package Sagi\Database\Interfaces
 */
interface ConnectorInterface
{

    /**
     *
     * @return ConnectionInterface
     */
    public function connect();

}
