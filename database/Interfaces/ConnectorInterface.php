<?php
namespace Sagi\Database\Interfaces;

/**
 * Interface ConnectorInterface
 * @package Sagi\Database\Interfaces
 */
interface ConnectorInterface
{

    /**
     *
     * @param string|null $db
     * @return ConnectionInterface
     */
    public function connect($db = null);
}
