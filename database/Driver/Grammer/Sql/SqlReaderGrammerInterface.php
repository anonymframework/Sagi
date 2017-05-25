<?php
namespace Sagi\Database\Driver\Grammer\Sql;

use Sagi\Database\Builder;
use Sagi\Database\Mapping\Entity;

interface SqlReaderGrammerInterface
{
    /**
     * @param Entity $entity
     * @param string $from
     * @return array
     */
    public function create(Entity $entity, $from);

    /**
     * @param Entity $entity
     * @param $from
     * @param array $where
     * @return array
     */
    public function update(Entity $entity, $from, array $where);

    /**
     * @param string $from
     * @param array $where
     * @return array
     */
    public function delete($from, array $where);

    /**
     * @param Builder $builder
     * @return array
     */
    public function read(Builder $builder);
}