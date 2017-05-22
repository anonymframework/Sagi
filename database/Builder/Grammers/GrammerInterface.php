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
    public function returnGetQuery();

    /**
     * @return string
     */
    public function returnInsertQuery();

    /**
     * @return string
     */
    public function returnUpdateQuery();

    /**
     * @return string
     */
    public function returnDeleteQuery();
}