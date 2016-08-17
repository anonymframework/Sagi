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

/**
 * @var $query Database
 */
$query = $db->relations([
    'posts' => ['user_id', 'id']
]);

$query->select('username,id');


$data = $db->first();

$posts = $data->posts;

$posts->relation('categories', ['id', 'category_id']);

