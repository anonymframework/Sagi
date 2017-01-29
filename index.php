<?php
include 'vendor/autoload.php';

$user = \Models\Users::findOne(1);

var_dump($user->username);



