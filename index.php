<?php
include 'vendor/autoload.php';
/*
$auth = new \Models\Auth(array(
    'user_id' => new \Models\Users(array(
        'username' => 'admin',
        'password' => 'test',
        'email'    => 'admin@test.com'
    ))
));

$auth->save();

$role = new \Models\Roles(array(
    'text' => 'editor'
));

$saved = $role->save();


$auth->role_id = $saved->id;

$subrole = new \Models\SubRoles(array(
    'role_id' => $saved->id,
    'rgb_id' => $saved->id
));

$subrole->save();
*/


$role = new \Models\Roles(array(
    'text' => 'editor'
));

$subrole = new \Models\SubRoles(array(
    'role_id' => $role,
    'rgb_id' => \Models\Roles::find([
        ['text', 'admin']
    ])->one()->id
));

$subrole->save();