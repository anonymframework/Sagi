<?php
include 'vendor/autoload.php';

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

$mig = new \Sagi\Database\MigrationManager();


$mig->migrate();
