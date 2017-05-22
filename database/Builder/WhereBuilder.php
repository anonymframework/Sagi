<?php
/**
 * Created by PhpStorm.
 * User: My
 * Date: 04/27/2017
 * Time: 17:42
 */

namespace Sagi\Database\Builder;

use Sagi\Database\Mapping\Match;
use Sagi\Database\Mapping\Raw;
use Sagi\Database\Mapping\SubWhere;
use Sagi\Database\Mapping\Where;
use Sagi\Database\QueryBuilder;
use Sagi\Database\Singleton;
use Sagi\Database\Exceptions\WhereException;

class WhereBuilder extends Builder
{

    private $args;

    /**
     * @var QueryBuilder
     */
    private $instance;

    /**
     * @var bool
     */
    private $subWhere = false;

    /**
     * WhereBuilder constructor.
     * @param QueryBuilder $builder
     * @param array $args
     */
    public function __construct(QueryBuilder $builder, array $args = [], $subWhere = false)
    {
        $this->setSubWhere($subWhere);
        $this->instance = $builder;
        $this->args = $args;
    }

    /**
     * @return bool
     */
    public function isSubWhere()
    {
        return $this->subWhere;
    }

    /**
     * @param bool $subWhere
     * @return WhereBuilder
     */
    public function setSubWhere($subWhere)
    {
        $this->subWhere = $subWhere;
        return $this;
    }


    /**
     * @return array
     */
    public function getArgs()
    {
        return $this->args;
    }

    /**
     * @param array $args
     * @return WhereBuilder
     */
    public function setArgs($args)
    {
        $this->args = $args;
        return $this;
    }

    /**
     * @return QueryBuilder
     */
    public function getİnstance()
    {
        return $this->instance;
    }

    /**
     * @param QueryBuilder $instance
     * @return WhereBuilder
     */
    public function setİnstance($instance)
    {
        $this->instance = $instance;
        return $this;
    }


    public function build()
    {

        $string = '';

        if (!$this->isSubWhere()) {
            $string .= 'WHERE ';
        }

        $where = $this->instance->getWhere();

        if (!empty($where)) {
            $prepared = $this->handleWhereQuery($where);

            if ($prepared !== '') {
                $string .= $prepared;
            }
        } else {
            $string = '';
        }

        unset($this->instance);

        return $string;
    }

    /**
     * @return string
     */
    private function handleWhereQuery(array $where)
    {

        $args = [];
        $s = '';
        $first = array_values($where)[0]->type;


        foreach ($where as $item) {
            if ($item instanceof Where) {
                $s .= $this->buildStandartWhereQuery($item, $args);
            } elseif ($item instanceof Match) {
                $s .= $this->buildMatchWhere($item);
            } elseif ($item instanceof SubWhere) {
                $s .= $this->buildSubWhere($item, $args);
            } else {
                throw new WhereException(sprintf('Wrong where query'));
            }
        }


        $s = ltrim($s, " $first");
        $this->args = array_merge($this->args, $args);

        return $s;
    }

    /**
     * @param SubWhere $item
     * @param $args
     * @return mixed|string
     */
    private function buildSubWhere(SubWhere $item, &$args)
    {


        if (is_callable($item->getQuery())) {
            $instance = Singleton::load(get_class($this->instance));

            $returned = call_user_func($item->getQuery(), $instance);
            $where = $returned->getWhere();
            /**
             * @var QueryBuilder $returned
             */
            if (empty($where)) {
                throw new WhereException('You did not make any where query');
            }


            $builder = new WhereBuilder($returned, [], true);
            $query = sprintf(' %s (%s)', $item->type,  $builder->build());
            $args = array_merge($args, $builder->getArgs());

            return $query;

        } elseif ($item->getQuery() instanceof Raw) {
            return $item->getQuery();
        }


        throw new WhereException('You did not make any where query');


    }

    /**
     * @param Where $item
     * @param $args
     * @param $preparedQuery
     * @return string
     */
    private function prepareStandartQuery(Where $item, &$args, &$preparedQuery)
    {
        $type = $item->type;
        $args[] = $item->query;

        $this->cleanIsNeeded($item->query);
        $query = $item->query;


        $preparedQuery = sprintf(' %s %s %s %s', $type, $item->field, $item->backet, $query);
    }

    /**
     * @param $query
     */
    private function cleanIsNeeded(&$query)
    {
        if (!is_callable($query) && !$query instanceof Raw && !is_array($query)) {
            $query = '?';
        }

        if ($query instanceof Raw) {
            $query = $query->getQuery();
        }
    }

    /**
     * @param Where $item
     * @param $args
     * @return string
     */
    private function buildStandartWhereQuery(Where $item, &$args)
    {
        $preparedQuery = '';

        if (is_callable($item->query) || is_array($item->query)) {
            $this->prepareInQuery($item, $args, $preparedQuery);
        } else {
            $this->prepareStandartQuery($item, $args, $preparedQuery);
        }

        return $preparedQuery;
    }


    private function prepareInQuery($item, &$args, &$query)
    {
        $datas = $item->query;

        if (is_array($datas)) {
            $args = $datas;
            $query = '[' . implode(",", array_fill(0, count($datas), '?')) . ']';
        } else {
            $sub =
                $this->prepareSubQuery($datas, Singleton::load(get_class($this->instance)));
            list($query, $args) = $sub;
        }

        $query = sprintf(' %s %s IN (%s)', $item->type, $item->field, $query);
    }

    /**
     * @param Match $item
     * @return string
     */
    private function buildMatchWhere(Match $item)
    {
        $s = '';


        $columns = implode(",", array_map(function ($column) {
            return "`$column`";
        }, $item->columns));

        $values = array_map([$this->instance->prepareConnection(), 'quote'], $item->values);

        $type = $item->type;

        if ($s !== '') {
            $s .= "$type ";
        }

        $s .= sprintf('MATCH(%s) AGAINST(%s IN %s) ', $columns, implode(',', $values), $item->mode);

        return $s;
    }

    /**
     * @param $callback
     * @return string
     */
    public function prepareSubQuery($callback, $instance)
    {
        /**
         *  returns a new subquery
         */
        $builder = new SubQuery($callback, $instance);
        return $builder->build();
    }
}