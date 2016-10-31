<?php

include 'vendor/autoload.php';

$configs = \Sagi\Database\ConfigManager::get('mvc');
$request = new \Sagi\Http\Request();
$app = new \Sagi\Application\App($configs, $request);

$app->handleRequest();
