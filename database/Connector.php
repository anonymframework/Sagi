<?php

namespace Sagi\Database;

use PDO;
use Sagi\Database\Driver\DriverManager;
use Sagi\Database\Exceptions\ConnectionException;
use Sagi\Database\Exceptions\DriverNotFoundException;
use Sagi\Database\Interfaces\ConnectorInterface;

class Connector
{
    /**
     * @var PDO
     *
     */
    protected $connection;

    /**
     * @var DriverManager
     */
    protected $driverManager;

    /**
     * Connector constructor.
     */
    public function __construct(DriverManager $driverManager)
    {
        $this->driverManager = $driverManager;
    }

    /**
     * @param string $connection
     */
    public function connect($connection){
        $configs = $this->findConnectionConfig($connection);
        $driver = $configs['driver'];

        $driver = $this->driverManager->resolve('connector', $driver);

    }

    /**
     * @param null $connection
     * @return mixed|null
     * @throws ConnectionException
     */
    public function madeConnection($connection = null)
    {
        $configs = $this->findConnectionConfig($connection);
        $driver = $configs['driver'];
        $connection = $this->callDriver($driver, $configs);

        if (!$connection instanceof ConnectorInterface) {
            throw new ConnectionException(
                sprintf(
                    '%s driver must return an instance of PDO',
                    $driver
                )
            );
        }


        $this->connection = $connection->connect();

        return $connection;
    }

    /**
     * @param $connection
     * @return array
     */
    private function findConnectionConfig($connection)
    {
        if ($connection === null) {
            $configs = ConfigManager::get('connections.default', 'localhost');
        } else {
            $configs = ConfigManager::get('connections.' . $connection, []);
        }


        return $configs;
    }

    /**
     * @param string $driver
     * @param callable $callback
     * @return Connector
     */
    public function driver($driver, $callback)
    {
        static::$callbacks[$driver] = $callback;

        return $this;
    }

    /**
     * @param $driver
     * @param array $configs
     * @return mixed
     * @throws DriverNotFoundException
     */
    private function callDriver($driver, array $configs)
    {
        $callback = static::$callbacks[$driver];


        if (is_string($callback)) {
            return static::$callback($configs);
        }

        return $callback($configs);

        throw new DriverNotFoundException(sprintf('%s driver not found', $driver));
    }

    /**
     *
     * @param string $database
     * @return PDO
     */
    public function getConnection($database = null)
    {
        if (null === $this->connection) {
            return $this->madeConnection($database);
        }

        return $this->connection;
    }
}
