<?php
include 'vendor/autoload.php';

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

$post = \Models\Posts::set(array(
    'title' => 'deneme'
));

$post->attach(new \Models\PostImages(), 'post_id', 'id');
$post->save();


echo convert(memory_get_usage());

function convert($size){
    $unit = array('b', 'kb', 'mb', 'gb', 'tr');
    return @round($size/pow(1024,
            ($i = floor(log($size, 1024))))).' '. $unit[$i];
}

