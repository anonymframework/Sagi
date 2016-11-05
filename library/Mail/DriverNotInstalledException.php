<?php
/**
 * This file belongs to the AnoynmFramework
 *
 * @author vahitserifsaglam <vahit.serif119@gmail.com>
 * @see http://gemframework.com
 *
 * Thanks for using
 */

namespace Sagi\Mail;

/**
 * throw the exception
 *
 * Class DriverNotInstalledException
 * @package Sagi\Mail
 */
class DriverNotInstalledException extends DriverException
{

    /**
     * throw the exception
     *
     * @param string $message the message of exception
     */
    public function __construct($message = '')
    {
        parent::__construct($message);
    }

}
