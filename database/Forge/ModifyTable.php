<?php

/**
 * Created by PhpStorm.
 * User: My
 * Date: 04/25/2017
 * Time: 16:11
 */
class ModifyTable
{

    /**
     * @var string
     */
    private $sql;


    public function alterTable($table){
        $this->sql .= 'ALTER TABLE '.$table;
    }

    /**
     * @param $column
     * @param $type
     * @param array $values
     * @return $this
     */
    public function addColumn($column, $type,array $values = array()){
        if (!empty($values)) {
            $values = $this->prepareValues($values);
        }

        $this->compileType($type, $values);

        $this->sql .=  "ADD ($column $type{$values})";

        return $this;
    }


    /**
     * @param $values
     * @return string
     */
    private function prepareValues($values){
        return '('.rtrim(',', implode(',', $values)).')';
    }

    /**
     * @param $table
     * @return ModifyTable
     */
    public static function table($table){
        $self = new self();

        $self->alterTable($table);

        return $self;
    }


    /**
     * @return string
     */
    public function sql(){
        return $this->sql;
    }
}

