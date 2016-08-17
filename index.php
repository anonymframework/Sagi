<?php

include 'QueryBuilder.php';
include 'Results.php';
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

$db = new QueryBuilder([
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

$db->in('id', function(QueryBuilder $builder){
    return $builder->where('username', 'admin');
});

