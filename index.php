<?php
include 'vendor/autoload.php';

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);


$radar = \Models\Radar::find()
    ->select('radar.username,radar.username, radar.id, radar.follow_id, radar.profile_picture, FROM_UNIXTIME(recent_activities.timestamp) timestamp')
    ->order('radar.id', 'ASC')
    ->join('recent_activities', [
        'id' =>  function($model){
            return $model->setTable('recent_activities')
                ->select('radar_id')
                ->cWhere('radar_id', 'radar.id')
                ->order('timestamp')
                ->limit(1);
        }

    ], 'LEFT JOIN')
    ->group('radar.id')
    ->limit(5);

