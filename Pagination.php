<?php

namespace Sagi\Database;


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
     * @param int $currentPage
     * @param int $itemPerPage
     */
    public function paginate($currentPage = 0, $itemPerPage = 15)
    {
        if ($currentPage === 1) {
            $currentPage = 0;
        }

        $this->setCurrentPage($currentPage)->setİtemPerPage($itemPerPage);

        $this->prepareModal();
    }


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