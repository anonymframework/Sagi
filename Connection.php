<?php
/**
 * Created by PhpStorm.
 * User: My
 * Date: 05/08/2017
 * Time: 16:24
 */

namespace Sagi\Database;


use PDO;
use Sagi\Database\Interfaces\ConnectionInferface;

class Connection implements ConnectionInferface
{

    /**
     * @var PDO
     */
    protected $connection;

    public function __construct(PDO $pdo)
    {
        $this->setConnection($pdo);
    }

    /**
     * @return PDO
     */
    public function getConnection()
    {
        return $this->connection;
    }

    /**
     * @param PDO $pdo
     * @return ConnectionInferface
     */
    public function setConnection(PDO $pdo)
    {
        $this->connection = $pdo;

        return $this;
    }
}