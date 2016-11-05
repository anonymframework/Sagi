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

use Swift_Attachment;
/**
 * the class of swift attachment
 *
 * Class SwiftAttachment
 * @package Sagi\Mail
 */
class SwiftAttachment extends Attachment implements SwiftAttachmentInterface
{

    /**
     * the instance of attachment manager
     *
     * @var \Swift_Mime_Attachment
     */
    private $attach;

    /**
     * create a new instance with path and file type information
     *
     * @param string $path the path of file
     * @param string $newName the new name of file
     * @param null $filetype the type of file
     */
    public function __construct($path = '', $newName = null, $filetype = null)
    {
        $this->attach = Swift_Attachment::fromPath($path, $filetype);
        $this->attach->setFilename($newName);
    }

    /**
     * register the file path
     *
     * @param string $path the path of file
     * @return $this
     */
    public function path($path = '')
    {
        $this->attach->fromPath($path);
        return $this;
    }

    /**
     * register the content type
     *
     * @param string $type
     * @return $this
     */
    public function contentType($type = '')
    {
        $this->attach->setContentType($type);
        return $this;
    }

    /**
     * register the new file name
     *
     * @param string $newName
     * @return $this
     */
    public function name($newName = '')
    {
        $this->attach->setFilename($newName);
        return $this;
    }

    /**
     * get the created attachment
     *
     * @return \Swift_Mime_Attachment
     */
    public function getAttach()
    {
        return $this->attach;
    }
}