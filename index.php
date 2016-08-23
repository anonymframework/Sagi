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

$user = User::find(1);

foreach ($user->posts as $post){
    echo $post->id;
}