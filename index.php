<?php

include "vendor/autoload.php";
/*
 *  or you can use composer for autoloading
 *
 *  $ composer dump-autoload
 *
 *  include "vendor/autoload.php"
 *
 */

ini_set('display_errors', 'On');

$users = new User();

var_dump($users->posts);