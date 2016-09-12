<?php
/**
 *  SAGI DATABASE ORM FILE
 *
 */

namespace Sagi\Database;


class ErrorException extends \Exception
{
    /**
     * ErrorException constructor.
     * @param string $message
     * @param int $code
     * @param int $line
     * @param $file
     */
    public function __construct($message, $code, $line, $file)
    {
        $this->message = $message;
        $this->code = $code;
        $this->line = $line;
        $this->file = $file;
    }
}