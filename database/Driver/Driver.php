<?php
namespace Sagi\Database\Driver;


use Sagi\Database\Driver\Expectation\ExpectationInterface;

class Driver
{

    /**
     * @var string
     */
    private $to;

    /**
     * @var string
     */
    private $name;

    /**
     * @var callable|object
     */
    private $callback;


    public function __construct($to = '')
    {
        $this->to = $to;
    }

    /**
     * @return string
     */
    public function getTo()
    {
        return $this->to;
    }

    /**
     * @param string $to
     * @return Driver
     */
    public function setTo($to)
    {
        $this->to = $to;
        return $this;
    }

    /**
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * @param string $name
     * @return Driver
     */
    public function setName($name)
    {
        $this->name = $name;
        return $this;
    }

    /**
     * @return callable|object
     */
    public function getCallback()
    {
        return $this->callback;
    }

    /**
     * @param callable|object $callback
     * @return Driver
     */
    public function setCallback($callback)
    {
        $this->callback = $callback;
        return $this;
    }
}