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
 * Class FileTypeException
 * @package Sagi\Upload
 */
class FileTypeException extends Exception
{
    /**
     *
     *
     * @param string $message gönderilecek mesaj
     */
    public function __construct($message = '')
    {
        $this->message = $message;
    }
}
