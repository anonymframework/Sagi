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

/*

$schema = new \Sagi\Database\Schema();

$schema->createTable('users', function (\Sagi\Database\Row $row) {

    $row->int('id');
});

*/

$user = \Sagi\Database\User::findAll();

var_dump($user);


