<?php
/**
 *  SAGI DATABASE ORM FILE
 *
 */

namespace Sagi\Database;

/**
 * Class SoftDelete
 * @package Sagi\Database
 */
trait SoftDelete
{

    /**
     * @return $this
     */
    public function trash()
    {
        $this->setAttribute(Model::DELETED_AT, date($this->timestampFormat()));

        return $this->save();
    }

    /**
     * @return mixed
     */
    public function onlyTrashed()
    {
        return $this->where(Model::DELETED_AT, '!=', '');
    }

    /**
     * @return $this
     */
    public function restore()
    {
        $this->setAttribute(Model::DELETED_AT, '');

        return $this->save();
    }
}
