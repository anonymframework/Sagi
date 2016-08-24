<?php
namespace Sagi\Database;

use PDOException;
use PDO;

class Connector
{
    /**
     * @var PDO
     */
    public $connection;

    public static function madeConnection($configs)
    {
        try {
            $driver = isset($configs['driver']) ? $configs['driver'] : 'mysql';

            $pdo = new PDO("$driver:host={$configs['host']};dbname={$configs['dbname']}", $configs['username'], $configs['password']);
            $pdo->query(sprintf("SET CHARACTER SET %s", isset($configs['charset']) ? $configs['charset'] : 'utf-8'));
        } catch (PDOException $p) {
            throw new PDOException("Something went wrong, message: " . $p->getMessage());

        }
        return $pdo;
    }

    /**
     * @return PDO
     */
    public function getConnection()
    {
        return $this->connection;
    }

    /**
     * @param PDO $connection
     */
    public function setConnection($connection)
    {
        $this->connection = $connection;
    }


}