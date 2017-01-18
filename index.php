<?php
include 'vendor/autoload.php';

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
$old = memory_get_usage();

$comment = new \Models\Comments(array(
    'text' => 'Comment Test :' . $rand = rand(),
    'post_id' => new \Models\Posts(array(
        'title' => 'Post test : '.$rand
    ))
));

if(!$comment->save()){
    throw new \Sagi\Database\QueryException($comment->error()[2]);
}

$posts = \Models\Posts::findAll();

foreach ($posts as $post) {
    echo "<br />" . $post->title ."<br />";

    if ($post->comments()->exists()) {
        foreach ($post->comments()->all() as $comment) {
            echo "<pre>" . $comment->text . "</pre>";
        }
    }
}
echo convert(memory_get_usage()-$old);

function convert($size){
    $unit = array('b', 'kb', 'mb', 'gb', 'tr');
    return @round($size/pow(1024,
            ($i = floor(log($size, 1024))))).' '. $unit[$i];
}

