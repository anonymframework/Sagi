<?php

namespace Sagi\Database;

/**
 * Class PaginationObject
 * @package Sagi\Database
 */
class PaginationObject
{
    /**
     * @var int
     */
    private $data;

    /**
     * @var bool
     */
    private $isCurrentPage;

    /**
     * PaginationObject constructor.
     * @param $data
     * @param $isCurrentPage
     */
    public function __construct($data, $isCurrentPage)
    {
        $this->data = $data;
        $this->isCurrentPage = $isCurrentPage;
    }

    /**
     * @return mixed
     */
    public function getData()
    {
        return $this->getData();
    }

    /**
     * @return bool
     */
    public function isCurrentPage()
    {
        return $this->isCurrentPage;
    }


    /**
     * @return string
     */
    public function __toString()
    {
        return strval($this->data);
    }
}
