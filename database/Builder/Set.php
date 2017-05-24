<?php
/**
 * Created by PhpStorm.
 * User: My
 * Date: 05/23/2017
 * Time: 05:18
 */

namespace Sagi\Database\Builder;


use Sagi\Database\Mapping\Entity;
use Sagi\Database\Repositories\EntityRepository;

class Set extends Builder
{
    use EntityRepository;

    /**
     * Set constructor.
     * @param Entity $entity
     */
    public function __construct(Entity $entity)
    {
        $this->entity = $entity;
    }

    /**
     * @return mixed
     */
    public function build()
    {
        $s = '';
        $arr = [];

        $entity = $this->getEntity();

        foreach ($entity->datas as $key => $value) {
            $s .= "$key = ?,";
            $arr[] = $value;
        }

        return [
            rtrim($s, ','),
            $arr,
        ];
    }
}