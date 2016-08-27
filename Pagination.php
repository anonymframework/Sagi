<?php

namespace Sagi\Database;

/**
 * Class Pagination
 * @package Sagi\Database
 */
trait Pagination
{

    /**
     * @var int
     */
    protected $itemPerPage;

    /**
     * @var int
     */
    protected $currentPage;

    /**
     * @var int
     */
    protected $totalCount;


    /**
     * @param int $currentPage
     * @param int $itemPerPage
     * @return mixed
     */
    public function paginate($currentPage = 0, $itemPerPage = 15)
    {
        if ($currentPage === 1) {
            $currentPage = 0;
        }

        $this->setCurrentPage($currentPage)->setİtemPerPage($itemPerPage);

        $this->prepareModal();


        return $this;
    }


    public function displayPagination()
    {
        $view = !empty($this->template) ? new View($this->template) : View::createContentWithFile('pagination');

        if ($this->currentPage !== 0) {
            $before = range($this->currentPage, 1);
        } else {
            $before = [];
        }

        $plusFive = $this->currentPage + 5;

        $start = $this->currentPage === 0 ? 1: $this->currentPage;
        if ($plusFive !== $this->totalCount && !$plusFive > $this->totalCount) {
            $after = range($start, 5);
        } else {
            $after = range($start, $this->totalCount);
        }


        $datas = array_merge($before, $after);

        $currentPage = $this->currentPage;
        $totalCount = $this->totalCount;

        $datas = array_map(function ($value) use ($currentPage, $totalCount) {

            $is = $currentPage === $value ? true:false;

            $class = new PaginationObject($value, $is, $totalCount);

            return $class;

        }, $datas);

        $hasMore = $currentPage < $totalCount ? true:false;

        $base = new PaginationBase($currentPage, $hasMore, $totalCount, $datas);

        $view->with('pagination', $base);

        echo $view->show();
    }


    /**
     * prepares modal
     */
    private function prepareModal()
    {
        $this->totalCount = $this->count() / $this->getİtemPerPage();

        $start = $this->getCurrentPage() * $this->getİtemPerPage();

        $this->limit([$start, $this->getİtemPerPage()]);
    }

    /**
     * @return int
     */
    public function getİtemPerPage()
    {
        return $this->itemPerPage;
    }

    /**
     * @param int $itemPerPage
     * @return Pagination
     */
    public function setİtemPerPage($itemPerPage)
    {
        $this->itemPerPage = $itemPerPage;
        return $this;
    }

    /**
     * @return int
     */
    public function getCurrentPage()
    {
        return $this->currentPage;
    }

    /**
     * @param int $currentPage
     * @return Pagination
     */
    public function setCurrentPage($currentPage)
    {
        $this->currentPage = $currentPage;
        return $this;
    }
}

