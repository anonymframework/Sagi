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
        $this->getEventManager()->listen('after_delete', function ($status, Model $model) {

            if (!$status) {
                throw new \Exception($model->error()[2]);
            }

            if (count($attach = $model->getAttach())) {
                foreach ($attach as $manager) {
                    $target = $manager['attach_by'][0];
                    $our = $manager['attach_by'][1];

                    $attachModel = $manager['attach_with'];

                    $attachModel->where($target, $model->attribute($our));
                    $attachModel->delete();
                }
            }
        });

        $this->getEventManager()->listen('after_create', function ($saved, Model $model) {


            if (count($attach = $model->getAttach())) {

                if (is_bool($saved) && $saved === false) {
                    throw new \Exception($model->error()[2]);
                }

                foreach ($attach as $manager) {

                    $target = $manager['attach_by'][0];
                    $our = $manager['attach_by'][1];

                    $attachModel = $manager['attach_with'];

                    $attachModel->setAttribute($target, $saved->attribute($our));

                    if ($attachModel->save() === false) {
                        throw new \Exception($model->error()[2]);
                    }
                }

            }
        });
    }
}
