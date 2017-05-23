<?php

namespace Sagi\Database\Mapping;

/**
 * Class Where
 * @package Sagi\Database\Mapping
 */
class Where
{

    /**
     * @var string
     */
    public $field;

    /**
     * @var mixed
     */
    public $query;

    /**
     * @var string
     */
    public $operator = '=';

    /**
     * @var string
     */
    public $type = 'AND';

    public function __construct($field, $operator, $query, $type)
    {
        $this->field = $field;
        $this->operator = $operator;
        $this->query = $query;
        $this->type = $type;
    }
}
