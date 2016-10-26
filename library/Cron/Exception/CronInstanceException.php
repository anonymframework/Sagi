<?php
/**
 * This file belongs to the AnoynmFramework
 *
 * @author vahitserifsaglam <vahit.serif119@gmail.com>
 * @see http://gemframework.com
 *
 * Thanks for using
 */


namespace Sagi\Cron\Exception;
use Exception;


/**
 * Class CronInstanceException
 * @package Sagi\Cron\Exception
 */
class CronInstanceException extends Exception
{

    /**
     * throw the exception
     *
     * @param string $message
     */
    public function __construct($message = '')
    {
        $this->message = $message;
    }
}
