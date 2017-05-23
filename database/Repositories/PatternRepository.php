<?php
/**
 * Created by PhpStorm.
 * User: My
 * Date: 05/23/2017
 * Time: 04:59
 */

namespace Sagi\Database\Repositories;


class PatternRepository
{
    /**
     * @var string
     */
    private $pattern;

    /**
     * PatternRepository constructor.
     * @param $pattern
     */
    public function __construct($pattern)
    {
        $this->pattern = $pattern;
    }

    /**
     * @return string
     */
    public function getPattern()
    {
        return $this->pattern;
    }

    /**
     * @param string $pattern
     * @return PatternRepository
     */
    public function setPattern($pattern)
    {
        $this->pattern = $pattern;

        return $this;
    }
}
