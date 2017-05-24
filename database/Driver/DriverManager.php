<?php

namespace Sagi\Database\Driver;

use Sagi\Database\Driver\Expectation\ExpectationInterface;
use Sagi\Database\Exceptions\DriverNotFoundException;
use Sagi\Database\Exceptions\DriverIsNotExpectedException;
use Sagi\Database\Repositories\ParameterRepository;

/**
 * Class DriverManager
 * @package Sagi\Database
 */
class DriverManager
{

    /**
     * @var array
     */
    protected static $drivers;

    /**
     * @var array
     */
    protected static $expects;

    /**
     * @param string $name
     * @return Driver
     */
    public function driver($name)
    {
        $driver = new Driver($name);

        return $driver;
    }

    /**
     * @param Driver $driver
     * @return $this
     */
    public function add(Driver $driver)
    {

        $to = $driver->getTo();
        $name = $driver->getName();
        $callback = $driver->getCallback();
        $expect = $driver->getExpect();

        static::$drivers[$to][$name] = $callback;
        static::$expects[$to][$name] = $expect;

        return $this;
    }

    public function resolve($to, $name, ParameterRepository $parameterRepository)
    {

        if (!isset(static::$drivers[$to][$name])) {
            throw new DriverNotFoundException(sprintf(
                '%s.%s could not found',
                $to,
                $name
            ));
        }

        $driver = static::$drivers[$to][$name];

        $this->checkExpectation($to, $name, $driver);


        if (is_object($driver)) {
            return $driver;
        }

        if (is_string($driver)) {
            return new $driver($parameterRepository->getParameters());
        }

        if (is_callable($driver)) {
            return $driver($parameterRepository->getParameters());
        }
    }

    /**
     * @param string $to
     * @param string $name
     * @param string $driver
     * @return bool
     * @throws DriverIsNotExpectedException
     */
    private function checkExpectation($to, $name, $driver)
    {
        if (!isset(static::$expects[$to][$name])) {
            return true;
        }

        $expects = static::$expects[$to][$name];

        if (!is_array($expects)) {
            $expects = [$expects];
        }

        foreach ($expects as $expect) {
            /**
             * @var ExpectationInterface $expect
             */

            if (!$expect->expect($driver)) {
                throw new DriverIsNotExpectedException(sprintf(
                    '%s.%s driver is not expected', $to, $name
                ));
            }
        }
    }
}

