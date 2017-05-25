<?php

include __DIR__.'/vendor/autoload.php';
use Sagi\Database\Driver\Connection\Sql\MysqlConnector;
$builder = new \Sagi\Database\Builder();

$builder->getDriverManager()
    ->add(
        $builder
            ->getDriverManager()
        ->driver('connector')
        ->setName('mysql')
        ->setCallback(
            new MysqlConnector()
        )
    );


$connector = $builder->getDriverManager()->resolve('connector', 'mysql');

