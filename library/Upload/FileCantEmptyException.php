<?php
/**
 * Bu Dosya AnonymFramework'e ait bir dosyadır.
 *
 * @author vahitserifsaglam <vahit.serif119@gmail.com>
 * @see http://gemframework.com
 *
 */

namespace Sagi\Upload;

use Exception;

/**
 * Class FileCantEmptyException
 * @package Sagi\Upload
 */
class FileCantEmptyException extends Exception
{

    /**
     * Sınıfı başlatır ve hatayı ekrana basar
     *
     * @param string $message
     */
    public function __construct($message = '')
    {
        $this->message = $message;
    }
}
