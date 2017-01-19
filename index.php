<?php
include 'vendor/autoload.php';

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
$old = memory_get_usage();
$posts = \Models\Posts::findAll();



foreach ($posts as $post) {
    echo "<br />" . $post->title . "<br />";

    if ($post->comments()->exists()) {
        foreach ($post->comments()->all() as $comment) {
            echo "<pre>" . $comment->text . "</pre>";
        }
    } else {
        echo "<br /> yorum yok <br />";
    }
}

echo "<pre>" . convert(memory_get_usage() - $old) . "</pre>";

function convert($size)
{
    if ($size == 0) {
        return;
    }
    $unit = array('b', 'kb', 'mb', 'gb', 'tr');
    return @round($size / pow(1024,
                ($i = floor(log($size, 1024))))) . ' ' . $unit[$i];
}

