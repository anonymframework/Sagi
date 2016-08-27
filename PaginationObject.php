<?php

namespace Sagi\Database;

/**
 * Class PaginationObject
 * @package Sagi\Database
 */
class PaginationObject implements \Iterator
{
    /**
     * @var array
     */
    private $datas;

    /**
     * @var int
     */
    private $totalCount;

    /**
     * @var int
     */
    private $currentPage;


    /**
     * PaginationObject constructor.
     * @param $datas
     * @param $totalCount
     * @param $currentPage
     */
    public function __construct($datas, $totalCount, $currentPage)
    {
        $this->datas = $datas;
        $this->totalCount = $totalCount;
        $this->currentPage = $currentPage;
    }


    /**
     * Return the current element
     * @link http://php.net/manual/en/iterator.current.php
     * @return mixed Can return any type.
     * @since 5.0.0
     */
    public function current()
    {
        return current($this->datas);
    }

    /**
     * Move forward to next element
     * @link http://php.net/manual/en/iterator.next.php
     * @return void Any returned value is ignored.
     * @since 5.0.0
     */
    public function next()
    {
        return next($this->datas);
    }

    /**
     * Return the key of the current element
     * @link http://php.net/manual/en/iterator.key.php
     * @return mixed scalar on success, or null on failure.
     * @since 5.0.0
     */
    public function key()
    {
        return key($this->datas);
    }

    /**
     * Checks if current position is valid
     * @link http://php.net/manual/en/iterator.valid.php
     * @return boolean The return value will be casted to boolean and then evaluated.
     * Returns true on success or false on failure.
     * @since 5.0.0
     */
    public function valid()
    {
        $key = key($this->datas);
        $var = ($key !== NULL && $key !== FALSE);
        return $var;
    }

    /**
     * Rewind the Iterator to the first element
     * @link http://php.net/manual/en/iterator.rewind.php
     * @return void Any returned value is ignored.
     * @since 5.0.0
     */
    public function rewind()
    {
        reset($this->datas);
    }
}
