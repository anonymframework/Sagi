<?php

namespace Sagi\Database;

/**
 * Interface MigrationInterface
 * @package Sagi\Database
 */
interface MigrationInterface
{

    public function up();

    public function down();
}
