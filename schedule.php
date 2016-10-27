<?php

/**
 * @var \Sagi\Cron\Cron $schedule
 */

$schedule->event(function (){
    $task =  new \Sagi\Cron\Task\ClosureTask(function (){
          echo 'hello world';
    });

    $task->everyMinute();

    return $task;
});