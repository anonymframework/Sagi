<?php
/**
 * Created by PhpStorm.
 * User: My
 * Date: 04/27/2017
 * Time: 17:58
 */

namespace Sagi\Database\Mapping;


class SubWhere
{

    /**
     * @var callable|Raw
     */
    protected $query;

    public $type;

    public function __construct($subQuery, $type){
       $this->setQuery($subQuery);
       $this->type = $type;
    }

    /**
     * @return callable|Raw
     */
    public function getQuery()
    {
        return $this->query;
    }

    /**
     * @param callable|Raw $query
     * @return SubWhere
     */
    public function setQuery($query)
    {
        $this->query = $query;
        return $this;
    }


}
