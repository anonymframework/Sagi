<?php
/**
 *  SAGI DATABASE ORM FILE
 *
 */

namespace Sagi\Database;


class ValueContainer
{
    /**
     * @var mixed|null|string
     */
    private $value;


    /**
     * ValueContainer constructor.
     * @param null|string|mixed $value
     */
    public function __construct($value = null)
    {
        $this->value = $value;
    }

    /**
     * @return mixed|null|string
     */
    public function __toString()
    {
        return $this->value;
    }

    /**
     * @param $format
     * @return string
     */
    public function format($format)
    {
        $date = new \DateTime($this->value);

        return $date->format($format);
    }
}
