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
 * Interface PhpMailerAttachmentInterface
 * @package Sagi\Mail
 */
interface PhpMailerAttachmentInterface
{

    /**
     * create a new instance and register the name and type
     *
     * @param string $fileName
     * @param string $type
     * @return PhpMailerAttachment
     */
    public static function create($fileName = '', $type = '');

    /**
     * return the registered file path
     *
     * @return string
     */
    public function getFile();


    /**
     * return the registered types
     *
     * @return string
     */
    public function getType();

    /**
     * return the registered new file name
     *
     * @return string
     */
    public function getNewName();

}
