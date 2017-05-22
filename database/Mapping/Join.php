<?php
/**
 * Created by PhpStorm.
 * User: serif
 * Date: 16.12.2016
 * Time: 18:31
 */

namespace Sagi\Database\Mapping;

/**
 * Class Join
 * @package Sagi\Database\Mapping
 */
class Join
{
    public $type = 'INNER JOIN';

    /**
     * @var mixed
     */
    public $target = '';

    /**
     * @var string
     */
    public $home = '';

    /**
     * @var string
     */
    public $table;

    /**
     * @var string
     */
    public $backet;

    /**
     * Join constructor.
     * @param string $type
     * @param string $table
     * @param string $target
     * @param string $home
     */
    public function __construct($type = 'INNER JOIN', $table = '', $target = '', $home = '', $backet = '=')
    {
        $this->table = $table;
        $this->target = $target;
        $this->home = $home;
        $this->type = $type;
        $this->backet = $backet;
    }
}
