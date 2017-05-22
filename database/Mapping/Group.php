<?php
/**
 *  SAGI DATABASE ORM FILE
 *
 */

namespace Sagi\Database\Mapping;


class Group
{

    /**
     * @var bool
     */
    public $isMultipile = false;

    /**
     * @var array
     */
    public $group;

    public function __construct(array $group = [], $multipile = false)
    {
        $this->group = $group;
        $this->isMultipile = $multipile;
    }
}
