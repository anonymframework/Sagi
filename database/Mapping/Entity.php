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
    public function __construct(array $datas = [])
    {
        $this->datas = $datas;

        if (isset($datas[0]) && is_array($datas[0])) {
            $this->multipile = true;
        }
    }

}