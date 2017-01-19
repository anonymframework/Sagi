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
     * @var string
     */
    public $query;

    /**
     * @var string
     */
    public $backet = '=';

    /**
     * @var string
     */
    public $type = 'AND';

    /**
     * @var bool
     */
    public $clean = true;
}
