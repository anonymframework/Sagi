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
        'pk' => 'PRIMARY KEY (:pk)',
        'inc' => 'AUTOINCREMENT',
        'notnull' => 'NOT NULL',
        'null' => 'NULL'
    ];

    public function __construct()
    {

    }
}