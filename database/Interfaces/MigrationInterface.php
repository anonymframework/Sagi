<?php

namespace Sagi\Database\Interfaces;

/**
 * Interface MigrationInterface
 * @package Sagi\Database
 */
interface MigrationInterface
{

    public function up();

    public function down();
}
