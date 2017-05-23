<?php
/**
 * Created by PhpStorm.
 * User: My
 * Date: 05/06/2017
 * Time: 21:29
 */

namespace Sagi\Database\Builder\Grammers;


interface GrammerInterface
{

    /**
     * @return string
     */
    public function getReadQuery();

    /**
     * @return string
     */
    public function getInsertQuery();

    /**
     * @return string
     */
    public function getUpdateQuery();

    /**
     * @return string
     */
    public function getDeleteQuery();
}
