<?php
namespace Sagi\Database\Interfaces;

/**
 * Interface ConnectorInterface
 * @package Sagi\Database\Interfaces
 */
interface ConnectorInterface
{

    /**
     * @return ConnectionInterface
     */
    public function connect();
}
