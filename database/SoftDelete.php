<?php
/**
 *  SAGI DATABASE ORM FILE
 *
 */

namespace Sagi\Database;


use Carbon\Carbon;

trait SoftDelete
{

    public function trash()
    {
        if ($this->hasTimestamp($updated = Model::DELETED_AT)) {
            $this->attributes[$updated] = date($this->timestampFormat(), time());
        }

    }
}