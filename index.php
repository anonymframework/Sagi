<?php

include __DIR__.'/vendor/autoload.php';

$column = new \Sagi\Database\Forge\Column('test');

$command = $column->int('test', 255)
    ->null()
    ->int('test')->notNull();

var_dump(\Sagi\Database\Forge\Column::getCommands());