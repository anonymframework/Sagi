<?php
include 'vendor/autoload.php';

$user = \Models\Users::find(1);

var_dump($user->username);



