<?php

include "vendor/autoload.php";

$user = new \Models\Users();

var_dump($user->login([
    'username' => 'vserifsaglam',
    'password' => 'sanane123'
]));

