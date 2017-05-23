<?php
/**
 * Created by PhpStorm.
 * User: My
 * Date: 05/23/2017
 * Time: 05:11
 */

namespace Sagi\Database\Builder;


use Sagi\Database\Builder\Grammers\GrammerInterface;
use Sagi\Database\Mapping\Entity;
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
     * Update constructor.
     * @param GrammerInterface $grammer
     * @param Entity $entity
     */
    public function __construct(GrammerInterface $grammer, Entity $entity)
    {
        $this->grammer = $grammer;
        $this->entity = $entity;
    }

    /**
     * @return mixed
     */
    public function build()
    {
        $setted = $this->databaseSetBuilder($sets);


        $patternRepository = new PatternRepository(
            $this->grammer->getUpdateQuery()
        );

        $parameterRepository = new ParameterRepository(
            [
                ':from' => $this->getTable(),
                ':update' => $content,
                ':where' => $this->buildWhereQuery(),
            ]
        );

        $pattern = new Pattern(
            $patternRepository,
            $parameterRepository
        );

        return $pattern->build();
    }
}