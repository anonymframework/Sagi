<?php

namespace Sagi\Database\Extension;

use Sagi\Database\Connector;
use Sagi\Database\Driver\Expectation\ExpectationInterface;

/**
 * Class BuilderExtension
 * @package Sagi\Database\Extension
 */
class BuilderExtension implements ExtensionInterface
{
    /**
     * @var mixed
     */
    private $connector;

    /**
     * @var ExpectationInterface
     */
    private $expectation;

    /**
     * @return mixed
     */
    public function getConnector()
    {
        return $this->connector;
    }

    /**
     * @param mixed $connector
     * @return BuilderExtension
     */
    public function setConnector($connector)
    {
        $this->connector = $connector;

        return $this;
    }

    /**
     * @return ExpectationInterface
     */
    public function getExpectation()
    {
        return $this->expectation;
    }

    /**
     * @param ExpectationInterface $expectation
     * @return BuilderExtension
     */
    public function setExpectation($expectation)
    {
        $this->expectation = $expectation;

        return $this;
    }
}
