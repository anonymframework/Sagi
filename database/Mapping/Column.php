<?php
namespace Sagi\Database\Mapping;


class Column
{

    /**
     * the name of column
     *
     * @var string
     */
    public $name;

    /**
     * @var string
     */
    public $type = 'string';

    /**
     * @var int
     */
    public $length;

    /**
     * @var bool
     */
    public $unique;

    /**
     * @var bool
     */
    public $nullable;

    /**
     * The precision for a decimal (exact numeric) column (Applies only for decimal column).
     *
     * @var integer
     */
    public $precision = 0;
    /**
     * The scale for a decimal (exact numeric) column (Applies only for decimal column).
     *
     * @var integer
     */
    public $scale = 0;

}
