<?php
include 'vendor/autoload.php';

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
$old = memory_get_usage(true);


$posts = \Models\Posts::findAll();




echo convert(memory_get_usage(true)-$old);

function convert($size){
    if ($size == 0) {
        return ;
    }
    $unit = array('b', 'kb', 'mb', 'gb', 'tr');
    return @round($size/pow(1024,
            ($i = floor(log($size, 1024))))).' '. $unit[$i];
}

