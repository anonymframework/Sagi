<?php
/**
 * Created by PhpStorm.
 * User: sagi
 * Date: 30.08.2016
 * Time: 17:12
 */

namespace Sagi\Database;

/**
 * Class Authorization
 * @package Sagi\Database
 */
trait Authorization
{
    public function checkAuthorizationTable()
    {
        return $this->tableExists('auth');
    }
}