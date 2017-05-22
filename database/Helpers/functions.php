<?php
use Sagi\Database\Model;
use Sagi\Database\ModelManager;
use Sagi\Database\QueryBuilder;
/**
 * @param string $table
 * @return Model|QueryBuilder
 */
function from($table)
{
    $model = ModelManager::createModelInstanceIfExists($table);

    return $model->from($model);
}

/**
 * @param string $table
 * @param string $field
 * @param string $backet
 * @param mixed $value
 * @return Model|QueryBuilder
 */
function where($table, $field, $backet, $value)
{
    $model = ModelManager::createModelInstanceIfExists($table);

    return $model->where($field, $backet, $value);
}

/**
 * @param string $table
 * @param string $field
 * @param string $backet
 * @param mixed $value
 * @return Model|QueryBuilder
 */
function orWhere($table, $field, $backet, $value)
{
    $model = ModelManager::createModelInstanceIfExists($table);

    return $model->orWhere($field, $backet, $value);
}

/**
 * @param string $query
 * @return \Sagi\Database\Mapping\Raw
 */
function raw($query)
{
 return Model::raw($query);
}

/**
 * @param string $select
 * @param string $from
 * @return QueryBuilder
 */
function select($select, $from = ''){
    return ModelManager::createModelInstanceIfExists($from)
        ->select($select);
}