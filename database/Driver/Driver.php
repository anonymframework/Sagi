<?php

namespace Sagi\Database\Driver;


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
    public function name($name)
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
    public function callback($callback)
    {
        $this->callback = $callback;

        return $this;
    }
}