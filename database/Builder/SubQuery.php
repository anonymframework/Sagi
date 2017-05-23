<?php
/**
 * Created by PhpStorm.
 * User: My
 * Date: 04/27/2017
 * Time: 17:46
 */

namespace Sagi\Database\Builder;


use Sagi\Database\Exceptions\ErrorException;
use Sagi\Database\QueryBuilder;

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
     * @return array
     * @throws ErrorException
     */
    public function build()
    {
        /**
         * @var $builder QueryBuilder
         */
        $builder = call_user_func($this->callback, $this->instance);

        if ( ! $builder instanceof QueryBuilder) {
            throw new ErrorException(
                sprintf(
                    'your class must return an instance of QueryBuilder, returned: %s',
                    gettype($builder)
                )
            );
        }

        $builder = $builder->getBuilder();
        $query = '('.$builder->handleGetQuery().')';

        if ($builder->hasAs()) {
            $query .= ' AS '.$builder->getAs();
        }

        return [$query, $builder->getArgs()];
    }
}
