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

        set_error_handler([$this, 'errorHandler']);
        set_exception_handler([$this, 'expectionHandler']);
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


    /**
     * @param $code
     * @param $message
     * @param $file
     * @param $line
     * @throws ErrorException
     */
    public function errorHandler($code, $message, $file, $line)
    {
        throw new ErrorException($message, $code, $line, $file);
    }

    public function exceptionHandler(\Exception $exception)
    {

    }

}