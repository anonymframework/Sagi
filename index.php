<?php
include 'vendor/autoload.php';
ini_set('display_startup_errors', 1);
ini_set('display_errors', 1);
error_reporting(E_ALL);

$user = \Models\Users::find(1);

$posts = $user->post()->all();

var_dump($posts);
