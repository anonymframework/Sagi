<?php
/**
 *  SAGI DATABASE ORM FILE
 *
 */

namespace Sagi\Database\Mapping;


class Match
{

    /**
     * @var array
     */
    public $columns;

    /**
     * @var array
     */
    public $values;

    /**
     * @var string
     */
    public $mode = 'BOOLEAN_MODE';

    /**
     * @var string
     */
    public $type = 'AND';

    /**
     * Match constructor.
     * @param $columns
     * @param $values
     * @param string $mode
     */
    public function __construct($columns, $values,$mode = 'BOOLEAN_MODE', $type = 'AND')
    {
        $this->columns = $columns;
        $this->values = $values;
        $this->mode = $mode;
        $this->type = $type;
    }
}