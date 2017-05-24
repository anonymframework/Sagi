<?php
/**
 * Created by PhpStorm.
 * User: vahit
 * Date: 24.05.2017
 * Time: 15:37
 */

namespace Sagi\Database\Driver\Expectation;


class Expect
{

    private  $expectation;

    /**
     * Expect constructor.
     * @param $expectation
     */
    public function __construct($expectation)
    {
        $this->expectation = $expectation;
    }

    /**
     * @return mixed
     */
    public function getExpectation()
    {
        return $this->expectation;
    }

    /**
     * @param mixed $expectation
     * @return $this
     */
    public function setExpectation($expectation)
    {
        $this->expectation = $expectation;
        return $this;
    }


}