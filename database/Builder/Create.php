<?php
/**
 * Created by PhpStorm.
 * User: My
 * Date: 05/23/2017
 * Time: 04:47
 */

namespace Sagi\Database\Builder;


use Sagi\Database\Builder\Grammers\GrammerInterface;
use Sagi\Database\Mapping\Entity;
use Sagi\Database\Repositories\BuilderReturnRepository;
use Sagi\Database\Repositories\EntityRepository;
use Sagi\Database\Repositories\ParameterRepository;
use Sagi\Database\Repositories\PatternRepository;

class Create extends Builder
{
    use EntityRepository;

    /**
     * @var GrammerInterface
     */
    protected $grammer;

    /**
     * Create constructor.
     * @param GrammerInterface $grammer
     * @param Entity $entity
     */
    public function __construct(GrammerInterface $grammer, Entity $entity)
    {
        $this->grammer = $grammer;
        $this->setEntity($entity);
    }

    /**
     * @return mixed
     */
    public function build()
    {
        $setted = $this->handleInsertQuery(
            $this->getEntity()
        );

        list($content, $args) = $setted;

        $patternRepository = new PatternRepository(
            $this->grammer->getReadQuery()
        );

        $parameterRepository = new ParameterRepository(
            [
                ':from' => $this->getTable(),
                ':insert' => $content,
            ]
        );

        $pattern = new Pattern($patternRepository, $parameterRepository);

        return new BuilderReturnRepository(
            $pattern->build(),
            $args
        );
    }

    /**
     * @param Entity $entity
     * @return array
     */
    protected function handleInsertQuery(Entity $entity)
    {
        $count = count($entity->datas);
        $keys = array_keys($entity->datas);

        $s = '('.rtrim(
                implode($keys, ','),
                ','
            ).') VALUES ';

        $s .= $this->handleInsertValue($count);
        $args = array_values($entity->datas);


        return [$args, $s];
    }
}