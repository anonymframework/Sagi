<?php

include "vendor/autoload.php";

$users = new \Models\Users();

var_dump($users);

throw new Exception();