<?php
/**
 * Created by PhpStorm.
 * User: sagi
 * Date: 26.08.2016
 * Time: 17:18
 */

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