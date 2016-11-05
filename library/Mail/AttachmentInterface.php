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
 * Interface AttachmentInterface
 * @package Sagi\Mail
 */
interface AttachmentInterface
{

    /**
     * create a new instance and register the name and type
     *
     * @param string $fileName
     * @param string|null newName
     * @param string $type
     * @return PhpMailerAttachment
     */
    public static function create($fileName = '', $newName = null, $type = '');
}
