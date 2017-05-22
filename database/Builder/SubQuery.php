<?php
/**
 * Created by PhpStorm.
 * User: My
 * Date: 04/27/2017
 * Time: 17:46
 */

namespace Sagi\Database\Builder;


class SubQuery extends Builder
{

    protected $callback;

    protected $instance;

    public function __construct($callback, $instance)
    {
        $this->callback = $callback;
        $this->instance = $instance;
    }

    /**
     * @return mixed
     */
    public function build()
    {
        /**
         * @var $builder QueryBuilder
         */
        $builder = call_user_func_array($this->callback, [$this->instance]);

        $query = '(' . $builder->prepareGetQuery() . ')';


        if ($builder->hasAs()) {
            $query .= ' AS ' . $builder->getAs();
        }

        return [$query, $builder->getArgs()];
    }
}
