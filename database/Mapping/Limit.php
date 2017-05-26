<?php
namespace Sagi\Database\Mapping;


class Limit
{
    /**
     * @var integer
     */
    public $startFrom;

    /**
     * @var integer
     */
    public $offset;

    /**
     * Limit constructor.
     * @param int $startFrom
     * @param null $offset
     */
    public function __construct($startFrom = 0, $offset = null)
    {
        $this->startFrom = (int) $startFrom;
        $this->offset = (int) $offset;
    }
}