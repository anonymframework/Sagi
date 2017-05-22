<?php
/**
 * Created by PhpStorm.
 * User: My
 * Date: 05/08/2017
 * Time: 16:21
 */

namespace Sagi\Database\Interfaces;


use PDO;
/**
 * Interface ConnectionInferface
 * @package Sagi\Database\Interfaces
 */
interface ConnectionInferface
{
    /**
     * @return PDO
     */
    public function getConnection();

    /**
     * @param PDO $pdo
     * @return ConnectionInferface
     */
    public function setConnection(PDO $pdo);
}
