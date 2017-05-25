<?php
/**
 * Created by PhpStorm.
 * User: My
 * Date: 05/08/2017
 * Time: 01:37
 */

namespace Sagi\Database\Driver\Traits;


trait GuardCable
{
    /**
     * @var array
     */
    protected $guarded = [];

    /**
     * @var bool
     */
    protected $totallyGuarded = false;

    /**
     * @var string|array
     */
    protected $fillable = '*';

    /**
     * @param string $key
     * @return bool
     */
    public function isFillable($key)
    {
        if ($this->isTotallyGuarded()) {
            return false;
        }

        if (is_array($this->guarded) && in_array($key, $this->guarded)) {
            return false;
        }


        if ((is_array($this->fillable) && in_array($key, $this->fillable)) || $this->fillable === '*') {
            return true;
        }
    }

    /*
    *
    * @return boolean
    */
    public function isTotallyGuarded()
    {
        return $this->totallyGuarded;
    }

    /**
     * @param boolean $totallyGuarded
     * @return Model
     */
    public function setTotallyGuarded($totallyGuarded)
    {
        $this->totallyGuarded = $totallyGuarded;

        return $this;
    }


    /**
     * @return Model
     */
    public function totallyGuarded()
    {
        return $this->setTotallyGuarded(true);
    }

}