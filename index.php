<?php

include "vendor/autoload.php";

$request = new \Sagi\Http\Request();

var_dump($request->getUri());