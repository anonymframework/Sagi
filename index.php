<?php

include 'vendor/autoload.php';

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

$user = \Models\Users::set([
    'username' => json_encode(array('aaa')),
    'password' => 'sanane123'
]);

var_dump($user->save());