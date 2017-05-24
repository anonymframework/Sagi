<?php
/**
 * Created by PhpStorm.
 * User: My
 * Date: 05/23/2017
 * Time: 20:22
 */

namespace Sagi\Database\Executor\Interfaces;

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
