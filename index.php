<?php

include __DIR__.'/vendor/autoload.php';
include  __DIR__.'/database/Helpers/functions.php';


$table = new \Sagi\Database\Forge\Table();

$table->create('test', function(\Sagi\Database\Forge\Column $column)
{

});