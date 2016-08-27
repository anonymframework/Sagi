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
     * @var string
     */
    protected $template;


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

    /**
     * prepares modal
     */
    private function prepareModal()
    {
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