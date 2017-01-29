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
     * @param string|null $connection
     */
    public static function madeConnection($connection = null)
    {
        if ($connection === null) {
            $connection = ConfigManager::get('connections.default', 'localhost');
        }

        $configs = ConfigManager::get('connections.'.$connection, []);

        try {
            $username = isset($configs['username']) ? $configs['username'] : null;
            $password = isset($configs['password']) ? $configs['password'] : null;


            $pdo = new PDO($configs['dsn'], $username, $password, $configs['attr']);
            $pdo->query(sprintf("SET CHARACTER SET %s", isset($configs['charset']) ? $configs['charset'] : 'utf8'));
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
        if (!static::$connection) {
            static::madeConnection();
        }

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
