<?php

namespace Sagi\Database\Interfaces;

/**
 * Interface MigrationInterface
 * @package Sagi\Database
 */
interface MigrationInterface
{

    /**
     * @return mixed
     */
    public function up();

    /**
     * @return mixed
     */
    public function down();
}
