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
class CreateAuthTable extends Schema implements MigrationInterface
{

    /**
     * includes createTable functions
     *
     */
    public function up()
    {
        $this->createTableIfNotExists('auth', function (Table $table) {
            $table->pk('user_id');
            $table->string('role');
            $table->timestamps();
        });
    }

    /**
     * includes dropTable function
     *
     */
    public function down()
    {
        $this->dropTable('auth');
    }
}
