<?php
/**
 * Created by PhpStorm.
 * User: My
 * Date: 05/06/2017
 * Time: 22:27
 */

namespace Sagi\Database\Mapping;


class Raw
{

    /**
     * @var string
     */
    private $query;

    /**
     * Raw constructor.
     * @param $query
     */
    public function __construct($query)
    {
         $this->setQuery($query);
    }

    /**
     * @return mixed
     */
    public function getQuery()
    {
        return $this->query;
    }

    /**
     * @param mixed $query
     * @return Raw
     */
    public function setQuery($query)
    {
        $this->query = $query;
        return $this;
    }

}