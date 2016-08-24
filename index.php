<?php

include "vendor/autoload.php";
/*
 *
 *  $ composer dump-autoload
 *
 *  include "vendor/autoload.php"
 *
 */

ini_set('display_errors', 'On');


$row = new \Sagi\Database\Row();

$row->pk('id');
$row->varchar('username')->notNull();
$row->int('type')->defaultValue('id');

var_dump($row->prepareRow());