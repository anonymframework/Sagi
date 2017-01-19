<?php
include 'vendor/autoload.php';

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

$post = new \Models\Posts();

$post->in('id', function (\Sagi\Database\Model $model){
    return $model->setTable('comments')->cWhere('post_id', 'posts.id')->select('post_id');
});

$post->limit([0, 50]);
foreach ($post as $item){

    echo $item->title;
}

