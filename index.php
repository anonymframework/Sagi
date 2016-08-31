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

/*

$users = \Sagi\Database\Users::findAll();

$users->paginate($_GET['page'], 1);

$users->displayPagination();

*/


$user = \Models\Users::findOne(1);

$user->policy(new \Sagi\Policies\UsersPolicy());

$user->username  = 'superadmin';

$user->save();