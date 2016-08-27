<?php

namespace Sagi\Database;

/**
 * Class PaginationBase
 * @package Sagi\Database
 */
class PaginationBase implements \Iterator
{
    /**
     * @var array
     */
    private $datas;


    /**
     * @var int
     */
    private $currentPage;

    /**
     * @var int
     */
    private $totalCount;

    /**
     * PaginationBase constructor.
     * @param $currentPage
     * @param int $totalCount
     * @param array $datas
     */
    public function __construct($currentPage, $totalCount, $datas)
    {
        $this->currentPage = $currentPage;
        $this->totalCount = $totalCount;
        $this->datas = $datas;
    }

    /**
     * @return mixed
     */
    public function hasMore()
    {
        return $this->currentPage < $this->totalCount  ? true:false;
    }

    /**
     * @return bool
     */
    public function hasLess()
    {
        return $this->currentPage > 1 ? true : false;
    }

    /**
     * @return int
     */
    public function getNext()
    {
        $current = $this->currentPage;
        return ++$current;
    }


    /**
     * @return int
     */
    public function getBefore()
    {
        $current = $this->currentPage;
        return --$current;
    }

    /**
     * @return mixed
     */
    public function totalCount()
    {
        return $this->totalCount;
    }

    /**
     *
     */
    public function rewind()
    {
        reset($this->datas);
    }

    /**
     * @return mixed
     */
    public function current()
    {
        $var = current($this->datas);
        return $var;
    }

    /**
     * @return mixed
     */
    public function key()
    {

        $var = key($this->datas);
        return $var;
    }

    /**
     * @return mixed
     */
    public function next()
    {
        $var = next($this->datas);
        return $var;
    }

    /**
     * @return bool
     */
    public function valid()
    {
        $key = key($this->datas);
        $var = ($key !== NULL && $key !== FALSE);
        return $var;
    }

}