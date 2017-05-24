<?php
namespace Sagi\Database\Repositories;


class ParameterRepository
{

    const INTEGER = 'i';
    const FLOAT = 'd';
    const STRING = 's';

    /**
     * @var array
     */
    private $parameters;


    /**
     * @param mixed $item
     * @return string
     */
    private function handleType($item)
    {
        switch ($item) {
            case is_bool($item):
                return static::INTEGER;
                break;
            case is_int($item):
                return static::INTEGER;
                break;
            case is_string($item):
                return static::STRING;
                break;
            case is_float($item):
                return static::FLOAT;
                break;
            default:
                throw new UnkownTypeException(
                    sprintf('%s type is cant use in mysqli', gettype($item))
                );
                break;
        }
    }

    /**
     * ParameterRepository constructor.
     * @param array $parameters
     */
    public function __construct(array  $parameters)
    {
        $this->setParameters($parameters);
    }

    /**
     * @return array
     */
    public function getParameters()
    {
        return $this->parameters;
    }

    /**
     * @return array
     */
    public function getParametersWithTypeString(){
        $parameters = $this->getParameters();
        $typeString = '';
        foreach ($parameters as $parameter){
            $typeString .= $this->handleType($parameter);
        }

        array_unshift($parameters, $typeString);

        return $parameters;
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
