<?php
/**
 *  SAGI DATABASE ORM FILE
 *
 */

namespace Sagi\Database\Mapping;


class Entity
{

    /**
     * @var string
     */
    public $datas;

    /**
     * @var bool
     */
    public $multipile = false;

    /**
     * Entity constructor.
     * @param array $datas
     */
    public function __construct($datas = [])
    {
        if (is_array($datas)) {
            $this->datas = $datas;
        }
    }

}