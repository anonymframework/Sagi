<?php

include 'vendor/autoload.php';

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

$instagram = \Models\Instagram::findOne(1);

$instagram->data = array(
    'test' => 1
);
var_dump($instagram->data);
