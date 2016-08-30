<?php

/**
 *  Created by Sagi Database Console
 *
 */

use Sagi\Database\Schema;
use Sagi\Database\Row as Table;
use Sagi\Database\MigrationInterface;

/**
 * @class CreateAuthTable
 */
class CreateAuthTable extends Schema implements MigrationInterface{

    /**
     * includes createTable functions
     *
     */
    public function up(){
        $this->createTableIfNotExists('auth', function (Table $row){

        });
    }

    /**
     * includes dropTable function
     *
     */
    public function down(){


    }
}
