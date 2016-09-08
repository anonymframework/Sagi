<?php
/**
 *  SAGI DATABASE ORM FILE
 *
 */

namespace Sagi\Database;

/**
 * Class RelationShip
 * @package Sagi\Database
 */
class RelationShip
{

    /**
     * @var Model
     */
    public $relatedWith;

    /**
     * @param string $name
     * @return mixed
     */
    public function __get($name)
    {
        return $this->relatedWith->$name;
    }

    /**
     * @param string $name
     * @param mixed $value
     */
    public function __set($name, $value)
    {
        $this->relatedWith->$name = $value;
    }

}
