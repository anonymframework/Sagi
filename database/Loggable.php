<?php
/**
 *  SAGI DATABASE ORM FILE
 *
 */

namespace Sagi\Database;


class Loggable implements LoggerAwareInterface
{

    /**
     * logger instance
     *
     * @var LoggerInterface
     */
    protected $logger;

    /**
     * Loggable constructor.
     */
    public function __construct()
    {
        $this->setLogger(new Logger());
    }

    /**
     * sets a logger interface on the object
     *
     * @param LoggerInterface $logger
     * @return mixed
     */
    public function setLogger(LoggerInterface $logger)
    {
        $this->logger = $logger;
    }




}