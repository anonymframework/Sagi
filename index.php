<?php

include 'QueryBuilder.php';
include 'Results.php';
include 'Database.php';
include 'RelationBag.php';

/*
 *  or you can use composer for autoloading
 *
 *  $ composer dump-autoload
 *
 *  include "vendor/autoload.php"
 *
 */

ini_set('display_errors', 'On');

$db = new Database([
    'host' => 'localhost',
    'username' => 'root',
    'password' => 'sanane123',
    'dbname' => 'test'
], 'users');

/*
 *  or $db->setTable('users');
 *
 */


$db->select('username,id');

$db->where(['username', 'LIKE', '%a%']);

$db->one();