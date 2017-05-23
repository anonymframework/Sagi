<?php
namespace Sagi\Database\Builder;


use Sagi\Database\Repositories\ParameterRepository;
use Sagi\Database\Repositories\PatternRepository;

class Pattern extends Builder
{

    /**
     * @var string
     */
    private $pattern;

    /**
     * @var array
     */
    private $parameters;

    /**
     * Pattern constructor.
     * @param $pattern
     */
    public function __construct(PatternRepository $pattern, ParameterRepository $parameterRepository)
    {
        $this->pattern = $pattern->getPattern();
        $this->parameters = $parameterRepository->getParameters();
    }

    /**
     * @return mixed
     */
    public function build()
    {
        foreach ($args as $key => $arg) {
            $pattern = str_replace($key, $arg, $pattern);
        }

        $exploded = array_filter(
            explode(' ', $pattern),
            function ($value) {
                return ! empty($value);
            }
        );

        return implode(' ', $exploded);
    }
}
