<?php
namespace Sagi\Database\Repositories;


class ParameterRepository
{

    /**
     * @var array
     */
    private $parameters;

    /**
     * ParameterRepository constructor.
     * @param array $parameters
     */
    public function __construct(array  $parameters)
    {
        $this->parameters = $parameters;
    }

    /**
     * @return array
     */
    public function getParameters()
    {
        return $this->parameters;
    }

    /**
     * @param array $parameters
     * @return ParameterRepository
     */
    public function setParameters($parameters)
    {
        $this->parameters = $parameters;

        return $this;
    }
}
