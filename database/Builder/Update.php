<?php
/**
 * Created by PhpStorm.
 * User: My
 * Date: 05/23/2017
 * Time: 05:11
 */

namespace Sagi\Database\Builder;

use Sagi\Database\Builder as BuilderBase;
use Sagi\Database\Builder\Grammers\GrammerInterface;
use Sagi\Database\Mapping\Entity;
use Sagi\Database\Repositories\BuilderReturnRepository;
use Sagi\Database\Repositories\EntityRepository;
use Sagi\Database\Repositories\ParameterRepository;
use Sagi\Database\Repositories\PatternRepository;

class Update extends Builder
{
    use EntityRepository;

    /**
     * @var GrammerInterface
     */
    protected $grammer;

    /**
     * @var BuilderBase
     */
    private $builder;
    /**
     * Update constructor.
     * @param GrammerInterface $grammer
     * @param Entity $entity
     * @param Builder $builder
     */
    public function __construct(GrammerInterface $grammer, Entity $entity, BuilderBase $builder)
    {
        $this->grammer = $grammer;
        $this->entity = $entity;
    }



    /**
     * @return mixed
     */
    public function build()
    {
        $setBuilder = new Set(
            $this->getEntity()
        );

        list($content, $args) = $setBuilder->build();


        $patternRepository = new PatternRepository(
            $this->grammer->getUpdateQuery()
        );

        $parameterRepository = new ParameterRepository(
            [
                ':from' => $this->getBuilder()->getTable(),
                ':update' => $content,
                ':where' => ''
            ]
        );

        $pattern = new Pattern(
            $patternRepository,
            $parameterRepository
        );

        return new BuilderReturnRepository(
            $pattern->build(),
            $args
        );
    }

    /**
     * @return GrammerInterface
     */
    public function getGrammer()
    {
        return $this->grammer;
    }

    /**
     * @param GrammerInterface $grammer
     * @return Update
     */
    public function setGrammer($grammer)
    {
        $this->grammer = $grammer;

        return $this;
    }

    /**
     * @return BuilderBase
     */
    public function getBuilder()
    {
        return $this->builder;
    }

    /**
     * @param BuilderBase $builder
     * @return Update
     */
    public function setBuilder($builder)
    {
        $this->builder = $builder;

        return $this;
    }
}