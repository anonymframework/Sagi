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

$user = \Sagi\Database\User::findOne(1);

foreach ($user->posts as $post){
    echo $post->id;
}

*/

/*
$db =  \Sagi\Database\QueryBuilder::createNewInstance('migrations');


var_dump($db->tableExists());

*/

$view = \Sagi\Database\View::createContentWithFile('pagination');
$view->with('datas', [1,2,3,4,5]);

$view->show();