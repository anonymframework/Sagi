<?php
/**
 * Created by PhpStorm.
 * User: serif
 * Date: 15.12.2016
 * Time: 21:25
 */

namespace Sagi\Database;


trait AttachManager
{
    public function bootAttachManager()
    {

        $this->beforeAttach(function ($saved, $model){
            if(is_bool($saved)){
                throw new \Exception($model->error()[2]);
            }
        });

        $this->eventManager->listen('after_create', function ($saved,Model $model) {

            $eventMan = $model->getEventManager();


            if (count($attach = $model->getAttach())) {
                $eventMan->hasListiner('before_attach') ? $eventMan->fire('before_attach', [$saved, $model]) : null;

                foreach ($attach as $manager) {
                    $target = $manager['attach_by'][0];
                    $our = $manager['attach_by'][1];

                    $attachModel = $manager['attach_with'];

                    $attachModel->setAttribute($target, $saved->attribute($our));


                    $attachModel->save();
                }
            }
        });
    }

}