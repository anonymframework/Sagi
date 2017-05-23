<?php
/**
 * Created by PhpStorm.
 * User: My
 * Date: 04/27/2017
 * Time: 17:46
 */

namespace Sagi\Database\Builder;


use Sagi\Database\Builder\Grammers\GrammerInterface;

abstract class Builder
{


    /**
     * @return mixed
     */
    abstract public function build();
}
