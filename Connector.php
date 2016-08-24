<?php
namespace Sagi\Database;

use PDOException;
use PDO;

class Connector
{
    /**
     * @var PDO
     */
    public static $connection;

    /**
     * @param $configs
     */
    public static function madeConnection($configs)
    {
        try {
            $driver = isset($configs['driver']) ? $configs['driver'] : 'mysql';

            $pdo = new PDO("$driver:host={$configs['host']};dbname={$configs['dbname']}", $configs['username'], $configs['password']);
            $pdo->query(sprintf("SET CHARACTER SET %s", isset($configs['charset']) ? $configs['charset'] : 'utf-8'));
        } catch (PDOException $p) {
            throw new PDOException("Something went wrong, message: " . $p->getMessage());

        }

        static::$connection = $pdo;
    }

    /**
     * @return PDO
     */
    public static function getConnection()
    {
        return static::$connection;
    }

    /**
     * @param PDO $connection
     */
    public static function setConnection($connection)
    {
        static::$connection = $connection;
    }


}