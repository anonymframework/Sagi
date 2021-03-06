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
     * @param string $to
     * @param mixed $expect
     * @return $this
     */
    public function expect($to, $expect)
    {
        static::$expects[$to][] = $expect;

        return $this;
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

        static::$drivers[$to][$name] = $callback;

        return $this;
    }

    /**
     * @param string $to the name of module
     * @param string $name the name of driver
     * @param ParameterRepository|null $parameterRepository parameters will given the instance
     * @return mixed
     * @throws DriverNotFoundException
     */
    public function resolve($to, $name, ParameterRepository $parameterRepository = null)
    {

        if ( ! isset(static::$drivers[$to][$name])) {
            throw new DriverNotFoundException(
                sprintf(
                    '%s.%s could not found',
                    $to,
                    $name
                )
            );
        }

        $driver = static::$drivers[$to][$name];
        $this->checkExpectation($to, $driver);

        if (is_callable($driver)) {
            return $driver(
                $parameterRepository->getParameters()
            );
        }elseif (is_string($driver)) {
            $driver =  new $driver(
                $parameterRepository->getParameters()
            );
        }


        if(is_object($driver)){
            return $driver;
        }

    }

    /**
     * @param string $to
     * @param string $driver
     * @return bool
     * @throws DriverIsNotExpectedException
     */
    private function checkExpectation($to, $driver)
    {
        if ( ! isset(static::$expects[$to])) {
            return true;
        }

        $expects = static::$expects[$to];

        foreach ($expects as $expect) {
            /**
             * @var ExpectationInterface $expect
             */

            if ( ! $expect->expect($driver)) {
                throw new DriverIsNotExpectedException(
                    sprintf(
                        '%s driver is not expected, please give a proper one',
                        $to
                    )
                );
            }
        }
    }
}

