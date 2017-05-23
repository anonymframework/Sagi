<?php
/**
 * Created by PhpStorm.
 * User: My
 * Date: 05/23/2017
 * Time: 04:58
 */

namespace Sagi\Database\Repositories;


use Sagi\Database\Mapping\Entity;

trait EntityRepository
{
    /**
     * @var Entity
     */
    private $entity;

    /**
     * @return Entity
     */
    public function getEntity()
    {
        return $this->entity;
    }

    /**
     * @param Entity $entity
     * @return EntityRepository
     */
    public function setEntity(Entity $entity)
    {
        $this->entity = $entity;

        return $this;
    }
}

