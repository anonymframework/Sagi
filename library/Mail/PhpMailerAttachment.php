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
 * the attachment class for phpmailer driver
 *
 * Class PhpMailerAttachment
 * @package Sagi\Mail
 */
class PhpMailerAttachment extends Attachment
{

    /**
     * the name of file
     *
     * @var string
     */
    private $file = '';

    /**
     * the type of file
     *
     * @var string
     */
    private $type = '';

    /**
     * create a new instance and register the name and type
     *
     * @param string $fileName
     * @param string|null $newName
     * @param string $type
     */
    public function __construct($fileName = '', $newName = null, $type = '')
    {
        $this->setFile($fileName);
        $this->setType($type);

        if ($newName !== null) {
            $this->setNewName($newName);
        }
    }

    /**
     * @return string
     */
    public function getFile()
    {
        return $this->file;
    }

    /**
     * @param string $file
     * @return PhpMailerAttachment
     */
    public function setFile($file)
    {
        $this->file = $file;
        return $this;
    }

    /**
     * @return string
     */
    public function getType()
    {
        return $this->type;
    }

    /**
     * @param string $type
     * @return PhpMailerAttachment
     */
    public function setType($type)
    {
        $this->type = $type;
        return $this;
    }



}