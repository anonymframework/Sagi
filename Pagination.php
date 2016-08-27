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

    public function template($template){
        $this->template = $template;

        return $this;
    }

    public function displayPagination()
    {
        $view = !empty($this->template) ? new View($this->template) : View::createContentWithFile('pagination');


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