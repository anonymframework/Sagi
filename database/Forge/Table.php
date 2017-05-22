<?php
/**
 * Created by PhpStorm.
 * User: My
 * Date: 05/22/2017
 * Time: 03:28
 */

namespace Sagi\Database\Forge;


class Table
{

    /**
     * holds the patterns the class will be use
     *
     * @var array
     */
    private $patterns = [
        'create_exists' => 'CREATE TABLE IF NOT EXISTS `%s`(',
        'create' => 'CREATE TABLE `%s`(',
        'drop' => 'DROP TABLE `%s`;'
    ];

    public function create($table,callable $callback){

    }

}