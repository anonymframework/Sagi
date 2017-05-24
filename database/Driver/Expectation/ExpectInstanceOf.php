<?php
/**
 * Created by PhpStorm.
 * User: vahit
 * Date: 24.05.2017
 * Time: 15:27
 */

namespace Sagi\Database\Driver\Expectation;


class ExpectInstanceOf extends Expect implements ExpectationInterface
{

    /**
     *
     * @param mixed $driver
     * @return bool
     */
    public function expect($driver){
        $expectation = $this->getExpectation();

        return $driver instanceof $expectation;
    }
}