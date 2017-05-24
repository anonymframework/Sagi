<?php
namespace Sagi\Database\Driver\Connection\Interfaces;

use Sagi\Database\Repositories\ParameterRepository;

/**
 * Interface ExecuteInterface
 * @package Sagi\Database\Connection\Interfaces
 */
interface ExecuteInterface
{

    /**
     * @param $prepare
     * @param ParameterRepository $parameterRepository
     * @return array
     */
    public function execute($prepare, ParameterRepository $parameterRepository);

}
