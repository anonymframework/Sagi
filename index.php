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

$db->relation(['post' ,'posts'], ['user_id', 'id']);

$db->relation(['posts.category', 'categories'], ['id', 'category_id']);;


var_dump($db->post->category->id);
