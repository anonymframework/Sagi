<?php
/**
 * Created by PhpStorm.
 * User: My
 * Date: 05/22/2017
 * Time: 22:55
 */

namespace Sagi\Database\Forge;

use PDO;

class Column
{

    /**
     * @var string
     */
    protected $table;

    /**
     * @var PDO
     */
    protected $connection;

    /**
     * Column constructor.
     * @param $table
     * @param PDO $connection
     */
    public function __construct($table)
    {
        $this->table = $table;
    }
}
