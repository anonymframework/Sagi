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
     * @param int $totalCount
     */
    public function __construct($data, $isCurrentPage, $hasMore, $totalCount)
    {
        $this->data = $data;
        $this->isCurrentPage = $isCurrentPage;
        $this->hasMore = $hasMore;
        $this->totalCount = $totalCount;
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
     * @return int
     */
    public function getNext(){
        return $this->data++;
    }

    /**
     * @return int
     */
    public function getBefore(){
        return $this->data--;
    }
    /**
     * @return mixed
     */
    public function totalCount()
    {
        return $this->totalCount;
    }

    /**
     * @return string
     */
    public function __toString()
    {
        return strval($this->data);
    }
}
