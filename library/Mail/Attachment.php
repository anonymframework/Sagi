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
 * Class Attachment
 * @package Sagi\Mail
 */
abstract class Attachment implements AttachmentInterface
{

    /**
     * the new name of file
     *
     * @var string
     */
    private $newName;

    /**
     * @return string
     */
    public function getNewName()
    {
        return $this->newName;
    }

    /**
     * @param string $newName
     * @return Attachment
     */
    public function setNewName($newName)
    {
        $this->newName = $newName;
        return $this;
    }

    /**
     * create a new instance and register the name and type
     *
     * @param string $fileName
     * @param string|null newName
     * @param string $type
     * @return PhpMailerAttachment
     */
    public static function create($fileName = '', $newName = null, $type = '')
    {
        return new static($fileName, $newName, $type);
    }
}
