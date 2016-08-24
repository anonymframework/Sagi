<?php
/**
 * Created by PhpStorm.
 * User: sagi
 * Date: 23.08.2016
 * Time: 23:04
 */

namespace Sagi\Database;


class TableSchema
{
    protected $commands = [
        'create' => 'CREATE TABLE :table(',
        'end' => ');',
    ];

    public function __construct()
    {

    }
}