<?php

/**
 * Created by PhpStorm.
 * User: sagi
 * Date: 23.08.2016
 * Time: 01:12
 */
class Model
{

    protected $table;

    /**
     * @var QueryBuilder
     */
    private $builder;

    /**
     * Model constructor.
     * @throws Exception
     */
    public function __construct()
    {
        $instance = QueryBuilder::$instance;

        if (!$instance instanceof QueryBuilder) {
            throw new Exception('QueryBuilder hasn\t started yet');
        }

        $this->builder = $instance;
    }


}