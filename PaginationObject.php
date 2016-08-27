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
     * @var bool
     */
    private $hasMore;
    /**
     * PaginationObject constructor.
     * @param $data
     * @param $isCurrentPage
     * @param bool $hasMore
     */
    public function __construct($data, $isCurrentPage, $hasMore)
    {
        $this->data = $data;
        $this->isCurrentPage = $isCurrentPage;
        $this->hasMore = $hasMore;
    }

    /**
     * @return mixed
     */
    public function getData(){
        return $this->getData();
    }

    /**
     * @return bool
     */
    public function isCurrentPage(){
        return $this->isCurrentPage;
    }

    /**
     * @return mixed
     */
    public function hasMore()
    {
        return $this->hasMore();
    }

    /**
     * @return string
     */
    public function __toString()
    {
        return strval($this->data);
    }
}
