<?php
/**
 *  SAGI DATABASE ORM FILE
 *
 */

namespace Sagi\Database;


/**
 * Interface LoggerAwareInterface
 * @package Sagi\Database
 */
interface LoggerAwareInterface
{

    /**
     * sets a logger interface on the object
     *
     * @param LoggerInterface $logger
     * @return mixed
     */
    public function setLogger(LoggerInterface $logger);
}