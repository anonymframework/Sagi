<?php

namespace Sagi\Database\Validation;
use Exception;

/**
 * Class MethodNotExists
 * @package Anonym\Validation
 */
class MethodNotExistsException extends Exception
{

    /**
     * throws the given exception message
     *
     * the constructor of MethodNotExists .
     * @param string $message
     */
    public function __construct($message)
    {
        $this->message = $message;
    }
}
